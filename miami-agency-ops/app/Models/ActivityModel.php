<?php
/**
 * Modelo de Flujo de Trabajo Audiovisual y Entregables
 * Manejo de Tareas: NUEVA | EN_PROCESO | ENTREGADA_REVISION | APROBADA | CORRECCION
 */

namespace App\Models;

use PDO;

class ActivityModel extends Model {
    /**
     * Obtener listado general de tareas con filtros opcionales
     */
    public function getAll(array $filters = []): array {
        $sql = "
            SELECT a.*, r.company_name, r.contact_person, r.miami_zone,
                   e.full_name AS employee_name,
                   c.contract_code,
                   (SELECT COUNT(*) FROM activity_deliverables ad WHERE ad.activity_id = a.id) AS deliverables_count
            FROM activities a
            JOIN realtors r ON a.realtor_id = r.id
            JOIN employees e ON a.employee_id = e.id
            LEFT JOIN contracts c ON a.contract_id = c.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND a.current_status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['employee_id'])) {
            $sql .= " AND a.employee_id = ?";
            $params[] = (int)$filters['employee_id'];
        }

        if (!empty($filters['realtor_id'])) {
            $sql .= " AND a.realtor_id = ?";
            $params[] = (int)$filters['realtor_id'];
        }

        $sql .= " ORDER BY CASE a.priority
                    WHEN 'URGENTE' THEN 1
                    WHEN 'ALTA' THEN 2
                    WHEN 'MEDIA' THEN 3
                    ELSE 4 END, a.scheduled_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Tareas asignadas a un empleado creativo específico ("Mis Tareas Audiovisuales")
     */
    public function getByEmployee(int $employeeId): array {
        $stmt = $this->db->prepare("
            SELECT a.*, r.company_name, r.contact_person, r.miami_zone, r.phone AS realtor_phone,
                   c.contract_code
            FROM activities a
            JOIN realtors r ON a.realtor_id = r.id
            LEFT JOIN contracts c ON a.contract_id = c.id
            WHERE a.employee_id = ?
            ORDER BY CASE a.current_status
                    WHEN 'CORRECCION' THEN 1
                    WHEN 'EN_PROCESO' THEN 2
                    WHEN 'NUEVA' THEN 3
                    WHEN 'ENTREGADA_REVISION' THEN 4
                    ELSE 5 END, a.scheduled_date ASC
        ");
        $stmt->execute([$employeeId]);
        $tasks = $stmt->fetchAll();

        // Enlazar el último entregable y revisión para cada tarea
        foreach ($tasks as &$task) {
            $delivStmt = $this->db->prepare("
                SELECT ad.*,
                       ar.review_status, ar.review_notes, ar.reviewed_at
                FROM activity_deliverables ad
                LEFT JOIN activity_reviews ar ON ar.deliverable_id = ad.id
                WHERE ad.activity_id = ?
                ORDER BY ad.submitted_at DESC
                LIMIT 1
            ");
            $delivStmt->execute([$task['id']]);
            $task['last_deliverable'] = $delivStmt->fetch() ?: null;
        }

        return $tasks;
    }

    /**
     * Conteo para el Pipeline del Jefe: Tareas Nuevas | Canalizadas | Por Aprobar | Culminadas
     */
    public function getDashboardCounts(): array {
        $stmt = $this->db->query("
            SELECT
                SUM(CASE WHEN current_status = 'NUEVA' THEN 1 ELSE 0 END) AS nuevas,
                SUM(CASE WHEN current_status = 'EN_PROCESO' THEN 1 ELSE 0 END) AS canalizadas,
                SUM(CASE WHEN current_status = 'ENTREGADA_REVISION' THEN 1 ELSE 0 END) AS por_aprobar,
                SUM(CASE WHEN current_status = 'APROBADA' THEN 1 ELSE 0 END) AS culminadas,
                SUM(CASE WHEN current_status = 'CORRECCION' THEN 1 ELSE 0 END) AS correccion,
                COUNT(*) AS total
            FROM activities
        ");
        $counts = $stmt->fetch();
        return [
            'nuevas' => (int)($counts['nuevas'] ?? 0),
            'canalizadas' => (int)($counts['canalizadas'] ?? 0),
            'por_aprobar' => (int)($counts['por_aprobar'] ?? 0),
            'culminadas' => (int)($counts['culminadas'] ?? 0),
            'correccion' => (int)($counts['correccion'] ?? 0),
            'total' => (int)($counts['total'] ?? 0)
        ];
    }

    /**
     * Crear y asignar una nueva actividad (Jefe)
     */
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO activities (
                title, description, realtor_id, employee_id, contract_id,
                activity_type, priority, scheduled_date, current_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'NUEVA')
        ");
        $stmt->execute([
            trim($data['title']),
            trim($data['description'] ?? ''),
            (int)$data['realtor_id'],
            (int)$data['employee_id'],
            !empty($data['contract_id']) ? (int)$data['contract_id'] : null,
            $data['activity_type'],
            $data['priority'] ?? 'MEDIA',
            $data['scheduled_date'] ?: date('Y-m-d')
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Actualizar estado básico de la actividad (ej. marcar como 'EN_PROCESO')
     */
    public function updateStatus(int $activityId, string $status): bool {
        $stmt = $this->db->prepare("
            UPDATE activities
            SET current_status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        return $stmt->execute([$status, $activityId]);
    }

    /**
     * Subir entregable audiovisual (Empleado: Frame.io / Drive)
     * Automáticamente promueve el estado de la tarea a 'ENTREGADA_REVISION'
     */
    public function addDeliverable(int $activityId, string $url, string $type, ?string $notes): int {
        $this->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_deliverables (activity_id, deliverable_url, deliverable_type, notes)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $activityId,
                trim($url),
                $type,
                trim($notes ?? '')
            ]);
            $deliverableId = (int)$this->db->lastInsertId();

            // Cambiar estado a ENTREGADA_REVISION
            $upStmt = $this->db->prepare("
                UPDATE activities
                SET current_status = 'ENTREGADA_REVISION', updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $upStmt->execute([$activityId]);

            $this->commit();
            return $deliverableId;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Revisión y Feedback del Jefe sobre el entregable
     */
    public function reviewDeliverable(int $deliverableId, int $activityId, int $reviewerUserId, string $status, string $notes): bool {
        $activityStatus = ($status === 'APROBADO') ? 'APROBADA' : 'CORRECCION';

        $this->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_reviews (deliverable_id, reviewer_user_id, review_status, review_notes)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $deliverableId,
                $reviewerUserId,
                $status,
                trim($notes)
            ]);

            $upStmt = $this->db->prepare("
                UPDATE activities
                SET current_status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $upStmt->execute([$activityStatus, $activityId]);

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Obtener detalle completo de actividad con historial de entregables y correcciones
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT a.*, r.company_name, r.contact_person, r.miami_zone, r.phone AS realtor_phone,
                   e.full_name AS employee_name, e.phone AS employee_phone,
                   c.contract_code
            FROM activities a
            JOIN realtors r ON a.realtor_id = r.id
            JOIN employees e ON a.employee_id = e.id
            LEFT JOIN contracts c ON a.contract_id = c.id
            WHERE a.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $activity = $stmt->fetch();
        if (!$activity) return null;

        $delivStmt = $this->db->prepare("
            SELECT ad.*,
                   ar.review_status, ar.review_notes, ar.reviewed_at,
                   u.email AS reviewer_email
            FROM activity_deliverables ad
            LEFT JOIN activity_reviews ar ON ar.deliverable_id = ad.id
            LEFT JOIN users u ON ar.reviewer_user_id = u.id
            WHERE ad.activity_id = ?
            ORDER BY ad.submitted_at DESC
        ");
        $delivStmt->execute([$id]);
        $activity['deliverables'] = $delivStmt->fetchAll();

        return $activity;
    }
}
