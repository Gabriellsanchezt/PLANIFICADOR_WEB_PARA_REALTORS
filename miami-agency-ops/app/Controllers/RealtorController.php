<?php
/**
 * Controlador de Clientes Realtors en Miami
 */

namespace App\Controllers;

use Config\Auth;
use App\Models\RealtorModel;

class RealtorController extends Controller {
    private RealtorModel $realtorModel;

    public function __construct() {
        $this->realtorModel = new RealtorModel();
    }

    public function index(): void {
        Auth::requireJefe();

        $realtors = $this->realtorModel->getAll();

        $this->render('realtors/index', [
            'pageTitle' => 'Gestión de Clientes Realtors (Miami)',
            'realtors' => $realtors,
            'zones' => MIAMI_ZONES
        ]);
    }

    public function store(): void {
        Auth::requireJefe();

        $company = $_POST['company_name'] ?? '';
        $contact = $_POST['contact_person'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $zone = $_POST['miami_zone'] ?? '';
        $instagram = $_POST['instagram'] ?? '';
        $tiktok = $_POST['tiktok'] ?? '';

        if (empty($company) || empty($contact) || empty($email) || empty($phone)) {
            $this->setFlash('error', 'Todos los campos principales del Realtor son requeridos.');
            $this->redirect('/realtors');
        }

        try {
            $this->realtorModel->create([
                'company_name' => $company,
                'contact_person' => $contact,
                'phone' => $phone,
                'email' => $email,
                'miami_zone' => $zone,
                'social_handles' => [
                    'instagram' => $instagram,
                    'tiktok' => $tiktok
                ],
                'status' => 'ACTIVO'
            ]);
            $this->setFlash('success', 'Cliente Realtor registrado exitosamente.');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error al registrar: ' . $e->getMessage());
        }

        $this->redirect('/realtors');
    }

    public function update(): void {
        Auth::requireJefe();

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            $this->setFlash('error', 'Realtor no válido.');
            $this->redirect('/realtors');
        }

        $this->realtorModel->update($id, [
            'company_name' => $_POST['company_name'],
            'contact_person' => $_POST['contact_person'],
            'phone' => $_POST['phone'],
            'email' => $_POST['email'],
            'miami_zone' => $_POST['miami_zone'],
            'social_handles' => [
                'instagram' => $_POST['instagram'] ?? '',
                'tiktok' => $_POST['tiktok'] ?? ''
            ],
            'status' => $_POST['status'] ?? 'ACTIVO'
        ]);

        $this->setFlash('success', 'Datos del Realtor actualizados correctamente.');
        $this->redirect('/realtors');
    }

    public function delete(): void {
        Auth::requireJefe();

        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            try {
                $this->realtorModel->delete($id);
                $this->setFlash('success', 'Realtor eliminado del sistema.');
            } catch (\Exception $e) {
                $this->setFlash('error', 'No se puede eliminar el Realtor porque tiene contratos o tareas vinculadas.');
            }
        }
        $this->redirect('/realtors');
    }
}
