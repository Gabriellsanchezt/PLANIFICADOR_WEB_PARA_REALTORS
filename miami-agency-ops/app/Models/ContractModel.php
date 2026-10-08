<?php
/**
 * Modelo de Plantillas y Contratos Dinámicos
 * Implementa el ciclo: BORRADOR -> EN_REVISION -> APROBADO / RECHAZADO
 */

namespace App\Models;

use PDO;

class ContractModel extends Model {
    /**
     * Obtener todas las plantillas activas
     */
    public function getActiveTemplates(): array {
        $stmt = $this->db->query("SELECT * FROM contract_templates WHERE active = 1 ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    /**
     * Obtener una plantilla por ID
     */
    public function getTemplateById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM contract_templates WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Generar código correlativo de contrato (ej: MIA-CTR-2026-002)
     */
    public function generateNextCode(): string {
        $year = date('Y');
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM contracts");
        $count = (int)$stmt->fetchColumn() + 1;
        return sprintf("MIA-CTR-%s-%03d", $year, $count);
    }

    /**
     * Motor de compilación de plantillas: Reemplaza {{VARIABLES}} dinámicamente
     */
    public function compileTemplate(string $templateHtml, array $params): string {
        $replacements = [
            '{{CONTRACT_CODE}}' => htmlspecialchars($params['contract_code'] ?? ''),
            '{{START_DATE}}' => htmlspecialchars($params['start_date'] ?? date('Y-m-d')),
            '{{END_DATE}}' => htmlspecialchars($params['end_date'] ?? date('Y-m-d', strtotime('+1 year'))),
            '{{COMPANY_NAME}}' => htmlspecialchars($params['company_name'] ?? 'Inmobiliaria no especificada'),
            '{{CONTACT_PERSON}}' => htmlspecialchars($params['contact_person'] ?? 'Realtor no especificado'),
            '{{MIAMI_ZONE}}' => htmlspecialchars($params['miami_zone'] ?? 'Miami-Dade'),
            '{{MONTHLY_FEE}}' => number_format((float)($params['monthly_fee'] ?? 0), 2),
            '{{SERVICES_DESCRIPTION}}' => nl2br(htmlspecialchars($params['services_description'] ?? 'Servicios de producción audiovisual inmobiliaria'))
        ];

        return strtr($templateHtml, $replacements);
    }

    /**
     * Crear un nuevo contrato en estado BORRADOR
     */
    public function createDraft(array $data, int $employeeId): int {
        $template = $this->getTemplateById((int)$data['template_id']);
        if (!$template) {
            throw new \InvalidArgumentException("La plantilla base no existe.");
        }

        $code = $this->generateNextCode();
        $compiledHtml = $this->compileTemplate($template['template_body_html'], array_merge($data, [
            'contract_code' => $code
        ]));

        $stmt = $this->db->prepare("
            INSERT INTO contracts (
                realtor_id, template_id, generated_by_employee_id, contract_code,
                compiled_body_html, start_date, end_date, monthly_fee, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'BORRADOR')
        ");

        $stmt->execute([
            (int)$data['realtor_id'],
            (int)$data['template_id'],
            $employeeId,
            $code,
            $compiledHtml,
            $data['start_date'],
            $data['end_date'],
            (float)$data['monthly_fee']
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Actualizar borrador de contrato (solo permitido si status = 'BORRADOR' o 'RECHAZADO')
     */
    public function updateDraft(int $contractId, array $data, int $employeeId): bool {
        $contract = $this->getContractById($contractId);
        if (!$contract) {
            throw new \InvalidArgumentException("Contrato inexistente.");
        }

        if (!in_array($contract['status'], ['BORRADOR', 'RECHAZADO'], true)) {
            throw new \DomainException("El contrato no puede ser editado porque se encuentra en revisión o ya fue aprobado.");
        }

        $template = $this->getTemplateById((int)$data['template_id']);
        $compiledHtml = $this->compileTemplate($template['template_body_html'], array_merge($data, [
            'contract_code' => $contract['contract_code']
        ]));

        $stmt = $this->db->prepare("
            UPDATE contracts
            SET realtor_id = ?,
                template_id = ?,
                compiled_body_html = ?,
                start_date = ?,
                end_date = ?,
                monthly_fee = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND generated_by_employee_id = ?
        ");

        return $stmt->execute([
            (int)$data['realtor_id'],
            (int)$data['template_id'],
            $compiledHtml,
            $data['start_date'],
            $data['end_date'],
            (float)$data['monthly_fee'],
            $contractId,
            $employeeId
        ]);
    }

    /**
     * Enviar borrador a revisión del Jefe
     * Una vez enviado, el status pasa a 'EN_REVISION' y desaparece del panel del empleado
     */
    public function submitForReview(int $contractId, int $employeeId): bool {
        $stmt = $this->db->prepare("
            UPDATE contracts
            SET status = 'EN_REVISION',
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND generated_by_employee_id = ? AND status IN ('BORRADOR', 'RECHAZADO')
        ");
        return $stmt->execute([$contractId, $employeeId]);
    }

    /**
     * Obtener contratos pendientes de aprobación para la VISTA DEL JEFE
     */
    public function getPendingApprovals(): array {
        $sql = "
            SELECT c.*, r.company_name, r.contact_person, r.miami_zone, r.email AS realtor_email,
                   e.full_name AS generator_employee_name,
                   ct.title AS template_title
            FROM contracts c
            JOIN realtors r ON c.realtor_id = r.id
            JOIN employees e ON c.generated_by_employee_id = e.id
            JOIN contract_templates ct ON c.template_id = ct.id
            WHERE c.status = 'EN_REVISION'
            ORDER BY c.updated_at ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Revisión del Jefe: "Aprobar y Archivar" o "Devolver con Observaciones"
     */
    public function reviewContract(int $contractId, int $reviewerUserId, string $action, ?string $feedback): bool {
        $newStatus = ($action === 'APROBAR') ? 'APROBADO' : 'RECHAZADO';

        $this->beginTransaction();
        try {
            // Actualizar status del contrato
            $stmt = $this->db->prepare("
                UPDATE contracts
                SET status = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND status = 'EN_REVISION'
            ");
            $stmt->execute([$newStatus, $contractId]);

            // Registrar trazabilidad en contract_reviews
            $revStmt = $this->db->prepare("
                INSERT INTO contract_reviews (contract_id, reviewer_user_id, status_assigned, feedback_notes)
                VALUES (?, ?, ?, ?)
            ");
            $revStmt->execute([$contractId, $reviewerUserId, $newStatus, trim($feedback ?? '')]);

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Repositorio Exclusivo del Jefe: "Contratos Finales" (APROBADO o CULMINADO)
     */
    public function getFinalApprovedContracts(?string $zone = null): array {
        $sql = "
            SELECT c.*, r.company_name, r.contact_person, r.miami_zone, r.phone AS realtor_phone,
                   e.full_name AS generator_employee_name,
                   (SELECT cr.feedback_notes FROM contract_reviews cr WHERE cr.contract_id = c.id ORDER BY cr.created_at DESC LIMIT 1) AS last_review_note,
                   (SELECT cr.created_at FROM contract_reviews cr WHERE cr.contract_id = c.id ORDER BY cr.created_at DESC LIMIT 1) AS approved_at
            FROM contracts c
            JOIN realtors r ON c.realtor_id = r.id
            JOIN employees e ON c.generated_by_employee_id = e.id
            WHERE c.status = 'APROBADO'
        ";

        if ($zone) {
            $sql .= " AND r.miami_zone = " . $this->db->quote($zone);
        }

        $sql .= " ORDER BY c.updated_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Obtener borradores activos del empleado autenticado (Solo BORRADOR o devueltos para corrección RECHAZADO)
     */
    public function getEmployeeDrafts(int $employeeId): array {
        $stmt = $this->db->prepare("
            SELECT c.*, r.company_name, r.contact_person, r.miami_zone,
                   (SELECT cr.feedback_notes FROM contract_reviews cr WHERE cr.contract_id = c.id ORDER BY cr.created_at DESC LIMIT 1) AS feedback_notes
            FROM contracts c
            JOIN realtors r ON c.realtor_id = r.id
            WHERE c.generated_by_employee_id = ? AND c.status IN ('BORRADOR', 'RECHAZADO')
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll();
    }

    /**
     * Detalle completo de un contrato por ID
     */
    public function getContractById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT c.*, r.company_name, r.contact_person, r.miami_zone, r.phone AS realtor_phone, r.email AS realtor_email,
                   e.full_name AS generator_employee_name,
                   ct.title AS template_title
            FROM contracts c
            JOIN realtors r ON c.realtor_id = r.id
            JOIN employees e ON c.generated_by_employee_id = e.id
            JOIN contract_templates ct ON c.template_id = ct.id
            WHERE c.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $contract = $stmt->fetch();
        if (!$contract) return null;

        // Cargar historial de revisiones
        $revStmt = $this->db->prepare("
            SELECT cr.*, u.email AS reviewer_email, emp.full_name AS reviewer_name
            FROM contract_reviews cr
            JOIN users u ON cr.reviewer_user_id = u.id
            LEFT JOIN employees emp ON emp.user_id = u.id
            WHERE cr.contract_id = ?
            ORDER BY cr.created_at DESC
        ");
        $revStmt->execute([$id]);
        $contract['reviews'] = $revStmt->fetchAll();

        return $contract;
    }
}
