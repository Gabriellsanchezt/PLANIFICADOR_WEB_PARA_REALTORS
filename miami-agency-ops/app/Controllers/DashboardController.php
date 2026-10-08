<?php
/**
 * Controlador del Dashboard Principal
 * Despacha vistas según rol (JEFE_ADMIN vs EMPLEADO)
 */

namespace App\Controllers;

use Config\Auth;
use App\Models\ActivityModel;
use App\Models\ContractModel;
use App\Models\RealtorModel;

class DashboardController extends Controller {
    public function index(): void {
        Auth::requireAuth();

        if (Auth::isJefe()) {
            $this->showJefeDashboard();
        } else {
            $this->showEmpleadoDashboard();
        }
    }

    private function showJefeDashboard(): void {
        $activityModel = new ActivityModel();
        $contractModel = new ContractModel();
        $realtorModel = new RealtorModel();

        $activityCounts = $activityModel->getDashboardCounts();
        $pendingApprovals = $contractModel->getPendingApprovals();
        $finalContracts = $contractModel->getFinalApprovedContracts();
        $recentActivities = $activityModel->getAll(['limit' => 5]);
        $realtors = $realtorModel->getAll();

        // Calcular facturación mensual total activa
        $totalMonthlyRevenue = array_sum(array_column($finalContracts, 'monthly_fee'));

        $this->render('dashboard/jefe', [
            'pageTitle' => 'Panel Ejecutivo de Dirección (Jefe)',
            'counts' => $activityCounts,
            'pendingApprovals' => $pendingApprovals,
            'finalContractsCount' => count($finalContracts),
            'totalMonthlyRevenue' => $totalMonthlyRevenue,
            'recentActivities' => $recentActivities,
            'realtorsCount' => count($realtors)
        ]);
    }

    private function showEmpleadoDashboard(): void {
        $user = Auth::user();
        $activityModel = new ActivityModel();
        $contractModel = new ContractModel();

        $myTasks = [];
        $myDrafts = [];

        // Si es creativo (video, carruseles, cámara, CM)
        if (Auth::canProduceMedia() && !empty($user['employee_id'])) {
            $myTasks = $activityModel->getByEmployee((int)$user['employee_id']);
        }

        // Si es creador de contratos
        if (Auth::hasSpecialty('CREADOR_CONTRATOS') && !empty($user['employee_id'])) {
            $myDrafts = $contractModel->getEmployeeDrafts((int)$user['employee_id']);
        }

        $this->render('dashboard/empleado', [
            'pageTitle' => 'Panel de Trabajo - ' . $user['full_name'],
            'user' => $user,
            'myTasks' => $myTasks,
            'myDrafts' => $myDrafts
        ]);
    }
}
