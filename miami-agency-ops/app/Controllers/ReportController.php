<?php
/**
 * Controlador de Reportes Ejecutivos Financieros y Operativos
 * Soporta Períodos Semanal, Quincenal y Mensual, y exportación a Excel / PDF
 */

namespace App\Controllers;

use Config\Auth;
use App\Models\ReportModel;

class ReportController extends Controller {
    private ReportModel $reportModel;

    public function __construct() {
        $this->reportModel = new ReportModel();
    }

    public function index(): void {
        Auth::requireJefe();

        $period = $_GET['period'] ?? 'mensual';
        if (!in_array($period, ['semanal', 'quincenal', 'mensual'], true)) {
            $period = 'mensual';
        }

        $report = $this->reportModel->getExecutiveReport($period);

        $this->render('reports/index', [
            'pageTitle' => 'Reportes Ejecutivos de Operaciones y Facturación',
            'report' => $report,
            'currentPeriod' => $period
        ]);
    }

    /**
     * Exportación de datos consolidados a formato CSV compatible con Microsoft Excel
     */
    public function exportExcel(): void {
        Auth::requireJefe();

        $period = $_GET['period'] ?? 'mensual';
        $report = $this->reportModel->getExecutiveReport($period);

        $filename = "reporte_ejecutivo_miami_agency_{$period}_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // Escribir BOM UTF-8 para compatibilidad directa con Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Cabecera del reporte
        fputcsv($output, ['MIAMI LUXURY MEDIA AGENCY - REPORTE EJECUTIVO OPERACIONAL Y FINANCIERO']);
        fputcsv($output, ['Periodo:', strtoupper($period), 'Generado:', $report['generated_at']]);
        fputcsv($output, []);

        // Métricas Financieras
        fputcsv($output, ['--- RESUMEN FINANCIERO DE CONTRATOS APROBADOS ---']);
        fputcsv($output, ['Métrica', 'Valor']);
        fputcsv($output, ['Contratos Nuevos Aprobados', $report['financial']['total_approved_contracts']]);
        fputcsv($output, ['Facturación Mensual Recurrente ($ USD)', number_format($report['financial']['total_monthly_revenue'], 2)]);
        fputcsv($output, ['Ticket Promedio por Contrato ($ USD)', number_format($report['financial']['avg_contract_ticket'], 2)]);
        fputcsv($output, []);

        // Desglose por Zona de Miami
        fputcsv($output, ['--- FACTURACIÓN POR ZONA DE MIAMI ---']);
        fputcsv($output, ['Zona de Miami', 'Contratos Activos', 'Ingreso Mensual ($ USD)']);
        foreach ($report['financial']['by_zone'] as $z) {
            fputcsv($output, [
                $z['miami_zone'],
                $z['contract_count'],
                number_format((float)$z['zone_revenue'], 2)
            ]);
        }
        fputcsv($output, []);

        // Resumen Operativo de Tareas Audiovisuales
        fputcsv($output, ['--- METRICAS DE OPERACION AUDIOVISUAL ---']);
        fputcsv($output, ['Métrica', 'Cantidad']);
        fputcsv($output, ['Total Tareas Programadas', $report['operations']['total_tasks']]);
        fputcsv($output, ['Tareas Culminadas y Aprobadas', $report['operations']['completed_tasks']]);
        fputcsv($output, ['Tareas en Revisión de Entregables', $report['operations']['in_review_tasks']]);
        fputcsv($output, ['Tareas en Proceso Activo', $report['operations']['in_progress_tasks']]);
        fputcsv($output, ['Tareas con Observaciones / Corrección', $report['operations']['correction_tasks']]);
        fputcsv($output, []);

        // Rendimiento del Personal Creativo
        fputcsv($output, ['--- DESEMPEÑO POR ESPECIALISTA CREATIVO ---']);
        fputcsv($output, ['Especialista', 'Tareas Asignadas', 'Tareas Aprobadas', 'Correcciones']);
        foreach ($report['operations']['staff'] as $st) {
            fputcsv($output, [
                $st['full_name'],
                $st['assigned_tasks'],
                $st['completed_tasks'],
                $st['rework_tasks']
            ]);
        }

        fclose($output);
        exit;
    }
}
