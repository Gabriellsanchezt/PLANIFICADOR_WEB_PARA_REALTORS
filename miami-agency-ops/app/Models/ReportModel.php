<?php
/**
 * Modelo de Reportes Ejecutivos y Métricas de Operaciones
 * Períodos: Semanal, Quincenal, Mensual
 */

namespace App\Models;

use PDO;

class ReportModel extends Model {
    /**
     * Obtener métricas consolidadas según período
     */
    public function getExecutiveReport(string $period = 'mensual'): array {
        $days = match($period) {
            'semanal' => 7,
            'quincenal' => 15,
            default => 30
        };

        // Fecha de corte
        $sinceDate = date('Y-m-d 00:00:00', strtotime("-{$days} days"));

        // 1. Métricas de Contratos Aprobados en el período
        $stmtContracts = $this->db->prepare("
            SELECT
                COUNT(c.id) AS total_contracts_approved,
                COALESCE(SUM(c.monthly_fee), 0) AS total_monthly_revenue,
                COALESCE(AVG(c.monthly_fee), 0) AS avg_contract_ticket
            FROM contracts c
            WHERE c.status = 'APROBADO' AND c.updated_at >= ?
        ");
        $stmtContracts->execute([$sinceDate]);
        $contractKpis = $stmtContracts->fetch();

        // 2. Facturación por Zona de Miami
        $stmtZones = $this->db->prepare("
            SELECT
                r.miami_zone,
                COUNT(c.id) AS contract_count,
                COALESCE(SUM(c.monthly_fee), 0) AS zone_revenue
            FROM contracts c
            JOIN realtors r ON c.realtor_id = r.id
            WHERE c.status = 'APROBADO' AND c.updated_at >= ?
            GROUP BY r.miami_zone
            ORDER BY zone_revenue DESC
        ");
        $stmtZones->execute([$sinceDate]);
        $revenueByZone = $stmtZones->fetchAll();

        // 3. Métricas de Tareas Audiovisuales
        $stmtActivities = $this->db->prepare("
            SELECT
                COUNT(id) AS total_tasks,
                SUM(CASE WHEN current_status = 'APROBADA' THEN 1 ELSE 0 END) AS completed_tasks,
                SUM(CASE WHEN current_status = 'ENTREGADA_REVISION' THEN 1 ELSE 0 END) AS in_review_tasks,
                SUM(CASE WHEN current_status = 'CORRECCION' THEN 1 ELSE 0 END) AS correction_tasks,
                SUM(CASE WHEN current_status = 'EN_PROCESO' THEN 1 ELSE 0 END) AS in_progress_tasks
            FROM activities
            WHERE created_at >= ?
        ");
        $stmtActivities->execute([$sinceDate]);
        $activityKpis = $stmtActivities->fetch();

        // 4. Desglose por Tipo de Producción
        $stmtTypes = $this->db->prepare("
            SELECT
                activity_type,
                COUNT(id) AS total_count,
                SUM(CASE WHEN current_status = 'APROBADA' THEN 1 ELSE 0 END) AS approved_count
            FROM activities
            WHERE created_at >= ?
            GROUP BY activity_type
        ");
        $stmtTypes->execute([$sinceDate]);
        $productionByType = $stmtTypes->fetchAll();

        // 5. Desempeño por Empleado
        $stmtStaff = $this->db->prepare("
            SELECT
                e.full_name,
                COUNT(a.id) AS assigned_tasks,
                SUM(CASE WHEN a.current_status = 'APROBADA' THEN 1 ELSE 0 END) AS completed_tasks,
                SUM(CASE WHEN a.current_status = 'CORRECCION' THEN 1 ELSE 0 END) AS rework_tasks
            FROM activities a
            JOIN employees e ON a.employee_id = e.id
            WHERE a.created_at >= ?
            GROUP BY e.id, e.full_name
            ORDER BY completed_tasks DESC
        ");
        $stmtStaff->execute([$sinceDate]);
        $staffPerformance = $stmtStaff->fetchAll();

        return [
            'period' => $period,
            'days' => $days,
            'since_date' => $sinceDate,
            'generated_at' => date('Y-m-d H:i:s'),
            'financial' => [
                'total_approved_contracts' => (int)($contractKpis['total_contracts_approved'] ?? 0),
                'total_monthly_revenue' => (float)($contractKpis['total_monthly_revenue'] ?? 0),
                'avg_contract_ticket' => (float)($contractKpis['avg_contract_ticket'] ?? 0),
                'by_zone' => $revenueByZone
            ],
            'operations' => [
                'total_tasks' => (int)($activityKpis['total_tasks'] ?? 0),
                'completed_tasks' => (int)($activityKpis['completed_tasks'] ?? 0),
                'in_review_tasks' => (int)($activityKpis['in_review_tasks'] ?? 0),
                'correction_tasks' => (int)($activityKpis['correction_tasks'] ?? 0),
                'in_progress_tasks' => (int)($activityKpis['in_progress_tasks'] ?? 0),
                'by_type' => $productionByType,
                'staff' => $staffPerformance
            ]
        ];
    }
}
