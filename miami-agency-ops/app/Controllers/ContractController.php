<?php
/**
 * Controlador de Gestión y Flujo de Contratos con Realtors
 */

namespace App\Controllers;

use Config\Auth;
use App\Models\ContractModel;
use App\Models\RealtorModel;

class ContractController extends Controller {
    private ContractModel $contractModel;
    private RealtorModel $realtorModel;

    public function __construct() {
        $this->contractModel = new ContractModel();
        $this->realtorModel = new RealtorModel();
    }

    /**
     * VISTA DEL JEFE: Repositorio Exclusivo de "Contratos Finales"
     * Muestra únicamente contratos APROBADOS, historial, montos y opción PDF
     */
    public function finalContracts(): void {
        Auth::requireJefe();

        $selectedZone = $_GET['zone'] ?? null;
        $contracts = $this->contractModel->getFinalApprovedContracts($selectedZone);
        $totalRevenue = array_sum(array_column($contracts, 'monthly_fee'));

        $this->render('contracts/final_repository', [
            'pageTitle' => 'Repositorio Oficial de Contratos Finales (Aprobados)',
            'contracts' => $contracts,
            'totalRevenue' => $totalRevenue,
            'selectedZone' => $selectedZone
        ]);
    }

    /**
     * VISTA DEL JEFE: Módulo de Aprobación de Contratos
     * Panel para revisar borradores enviados con 'Aprobar y Archivar' o 'Devolver con Observaciones'
     */
    public function approvals(): void {
        Auth::requireJefe();

        $pending = $this->contractModel->getPendingApprovals();

        $this->render('contracts/approvals', [
            'pageTitle' => 'Módulo de Aprobación de Contratos Pendientes',
            'pending' => $pending
        ]);
    }

    /**
     * VISTA DEL JEFE: Procesar Aprobación o Devolución con Observaciones
     */
    public function processReview(): void {
        Auth::requireJefe();

        $contractId = (int)($_POST['contract_id'] ?? 0);
        $action = $_POST['action'] ?? ''; // 'APROBAR' o 'RECHAZAR'
        $feedback = $_POST['feedback_notes'] ?? '';

        if (!$contractId || !in_array($action, ['APROBAR', 'RECHAZAR'], true)) {
            $this->setFlash('error', 'Datos de revisión inválidos.');
            $this->redirect('/contracts/approvals');
        }

        $user = Auth::user();
        $this->contractModel->reviewContract($contractId, (int)$user['id'], $action, $feedback);

        if ($action === 'APROBAR') {
            $this->setFlash('success', 'Contrato aprobado exitosamente. Ha sido archivado en el Repositorio de Contratos Finales.');
        } else {
            $this->setFlash('warning', 'Contrato devuelto con observaciones al redactor para corrección.');
        }

        $this->redirect('/contracts/approvals');
    }

    /**
     * VISTA DEL EMPLEADO AUTORIZADO (CREADOR_CONTRATOS):
     * Formulario del Generador de Contratos y Borradores Activos
     */
    public function create(): void {
        Auth::requireContractPermission();

        $user = Auth::user();
        $templates = $this->contractModel->getActiveTemplates();
        $realtors = $this->realtorModel->getAll();
        $drafts = [];

        // Los empleados solo ven sus propios borradores no enviados o devueltos
        if (!empty($user['employee_id'])) {
            $drafts = $this->contractModel->getEmployeeDrafts((int)$user['employee_id']);
        }

        $this->render('contracts/create', [
            'pageTitle' => 'Generador Dinámico de Contratos con Realtors',
            'templates' => $templates,
            'realtors' => $realtors,
            'drafts' => $drafts
        ]);
    }

    /**
     * Guardar borrador o enviar directamente a revisión
     */
    public function store(): void {
        Auth::requireContractPermission();

        $user = Auth::user();
        $realtorId = (int)($_POST['realtor_id'] ?? 0);
        $templateId = (int)($_POST['template_id'] ?? 0);
        $monthlyFee = (float)($_POST['monthly_fee'] ?? 0);
        $startDate = $_POST['start_date'] ?? date('Y-m-d');
        $endDate = $_POST['end_date'] ?? date('Y-m-d', strtotime('+1 year'));
        $services = $_POST['services_description'] ?? '';
        $submitMode = $_POST['submit_mode'] ?? 'draft'; // 'draft' o 'send_review'

        $realtor = $this->realtorModel->getById($realtorId);
        if (!$realtor || !$templateId || $monthlyFee <= 0) {
            $this->setFlash('error', 'Por favor complete todos los parámetros obligatorios del contrato.');
            $this->redirect('/contracts/create');
        }

        $data = [
            'realtor_id' => $realtorId,
            'template_id' => $templateId,
            'monthly_fee' => $monthlyFee,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'services_description' => $services,
            'company_name' => $realtor['company_name'],
            'contact_person' => $realtor['contact_person'],
            'miami_zone' => $realtor['miami_zone']
        ];

        $contractId = $this->contractModel->createDraft($data, (int)$user['employee_id']);

        if ($submitMode === 'send_review') {
            $this->contractModel->submitForReview($contractId, (int)$user['employee_id']);
            $this->setFlash('success', "¡Contrato generado y enviado exitosamente a revisión del Jefe! Ha pasado al archivo central de Dirección.");
            $this->redirect('/dashboard');
        } else {
            $this->setFlash('info', "Borrador guardado exitosamente. Podrá editarlo o enviarlo a revisión cuando lo decida.");
            $this->redirect('/contracts/create');
        }
    }

    /**
     * Enviar un borrador existente a revisión
     */
    public function submitExistingDraft(): void {
        Auth::requireContractPermission();

        $contractId = (int)($_POST['contract_id'] ?? 0);
        $user = Auth::user();

        if ($contractId && !empty($user['employee_id'])) {
            $this->contractModel->submitForReview($contractId, (int)$user['employee_id']);
            $this->setFlash('success', 'El contrato ha sido enviado formalmente a revisión del Jefe. Ya no está disponible en su bandeja de borradores.');
        }

        $this->redirect('/contracts/create');
    }

    /**
     * Endpoint AJAX para previsualización instantánea de plantilla compilada
     */
    public function previewAjax(): void {
        Auth::requireContractPermission();

        $templateId = (int)($_POST['template_id'] ?? 0);
        $realtorId = (int)($_POST['realtor_id'] ?? 0);
        $template = $this->contractModel->getTemplateById($templateId);
        $realtor = $this->realtorModel->getById($realtorId);

        if (!$template) {
            $this->json(['success' => false, 'error' => 'Plantilla no encontrada'], 404);
        }

        $params = [
            'contract_code' => 'MIA-CTR-' . date('Y') . '-PREVIEW',
            'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
            'end_date' => $_POST['end_date'] ?? date('Y-m-d', strtotime('+1 year')),
            'company_name' => $realtor ? $realtor['company_name'] : ($_POST['company_name'] ?? 'Inmobiliaria Ejemplo'),
            'contact_person' => $realtor ? $realtor['contact_person'] : ($_POST['contact_person'] ?? 'Realtor Ejemplo'),
            'miami_zone' => $realtor ? $realtor['miami_zone'] : ($_POST['miami_zone'] ?? 'Brickell / Financial District'),
            'monthly_fee' => $_POST['monthly_fee'] ?? 3500.00,
            'services_description' => $_POST['services_description'] ?? 'Servicios audiovisuales'
        ];

        $compiledHtml = $this->contractModel->compileTemplate($template['template_body_html'], $params);
        $this->json(['success' => true, 'html' => $compiledHtml]);
    }

    /**
     * Visualización e Impresión/Descarga PDF limpia del Contrato
     */
    public function view(): void {
        Auth::requireAuth();

        $id = (int)($_GET['id'] ?? 0);
        $contract = $this->contractModel->getContractById($id);

        if (!$contract) {
            $this->setFlash('error', 'Contrato no encontrado.');
            $this->redirect('/dashboard');
        }

        // Si es empleado, solo puede ver si es de él o si es Jefe
        $user = Auth::user();
        if (!Auth::isJefe() && (int)$contract['generated_by_employee_id'] !== (int)$user['employee_id']) {
            die("Acceso denegado: No cuenta con permisos para ver este contrato.");
        }

        $this->render('contracts/view', [
            'pageTitle' => 'Contrato ' . $contract['contract_code'],
            'contract' => $contract
        ], 'none'); // Vista directa sin barras laterales para impresión y exportación PDF limpia
    }
}
