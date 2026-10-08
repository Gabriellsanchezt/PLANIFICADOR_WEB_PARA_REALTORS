<?php
/**
 * Controlador de Gestión de Personal y Asignación de Roles / Especialidades
 */

namespace App\Controllers;

use Config\Auth;
use App\Models\UserModel;

class TeamController extends Controller {
    private UserModel $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function index(): void {
        Auth::requireJefe();

        $employees = $this->userModel->getAllEmployeesWithDetails();
        $roles = $this->userModel->getAllRoles();
        $specialties = $this->userModel->getAllSpecialties();

        $this->render('team/index', [
            'pageTitle' => 'Gestión de Equipo y Especialidades Granulares',
            'employees' => $employees,
            'roles' => $roles,
            'specialties' => $specialties
        ]);
    }

    public function store(): void {
        Auth::requireJefe();

        $fullName = $_POST['full_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $roleId = (int)($_POST['role_id'] ?? 2);
        $specialtyIds = $_POST['specialties'] ?? [];

        if (empty($fullName) || empty($email) || empty($password)) {
            $this->setFlash('error', 'Nombre, email y contraseña son obligatorios.');
            $this->redirect('/team');
        }

        try {
            $this->userModel->createTeamMember([
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
                'role_id' => $roleId,
                'hire_date' => date('Y-m-d')
            ], $specialtyIds);

            $this->setFlash('success', "Nuevo miembro incorporado con sus roles y especialidades asignadas.");
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error al crear usuario: ' . $e->getMessage());
        }

        $this->redirect('/team');
    }

    public function updateSpecialties(): void {
        Auth::requireJefe();

        $userId = (int)($_POST['user_id'] ?? 0);
        $specialtyIds = $_POST['specialties'] ?? [];

        if (!$userId) {
            $this->setFlash('error', 'Usuario inválido.');
            $this->redirect('/team');
        }

        try {
            $this->userModel->updateUserSpecialties($userId, $specialtyIds);
            $this->setFlash('success', 'Especialidades actualizadas correctamente.');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error al actualizar permisos: ' . $e->getMessage());
        }

        $this->redirect('/team');
    }
}
