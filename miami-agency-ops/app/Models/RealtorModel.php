<?php
/**
 * Modelo de Clientes Realtors (Inmobiliarias y Brokers en Miami)
 */

namespace App\Models;

use PDO;

class RealtorModel extends Model {
    /**
     * Listar todos los realtors
     */
    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT r.*,
                   (SELECT COUNT(*) FROM contracts c WHERE c.realtor_id = r.id AND c.status = 'APROBADO') AS active_contracts_count,
                   (SELECT COUNT(*) FROM activities a WHERE a.realtor_id = r.id) AS total_activities_count
            FROM realtors r
            ORDER BY r.company_name ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Obtener un realtor por ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM realtors WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Crear un nuevo Realtor
     */
    public function create(array $data): int {
        $socialJson = is_array($data['social_handles'] ?? null)
            ? json_encode($data['social_handles'])
            : ($data['social_handles'] ?? json_encode(['instagram' => '']));

        $stmt = $this->db->prepare("
            INSERT INTO realtors (company_name, contact_person, phone, email, miami_zone, social_handles, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            trim($data['company_name']),
            trim($data['contact_person']),
            trim($data['phone']),
            trim($data['email']),
            trim($data['miami_zone']),
            $socialJson,
            $data['status'] ?? 'ACTIVO'
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Actualizar datos del Realtor
     */
    public function update(int $id, array $data): bool {
        $socialJson = is_array($data['social_handles'] ?? null)
            ? json_encode($data['social_handles'])
            : ($data['social_handles'] ?? null);

        $stmt = $this->db->prepare("
            UPDATE realtors
            SET company_name = ?,
                contact_person = ?,
                phone = ?,
                email = ?,
                miami_zone = ?,
                social_handles = COALESCE(?, social_handles),
                status = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            trim($data['company_name']),
            trim($data['contact_person']),
            trim($data['phone']),
            trim($data['email']),
            trim($data['miami_zone']),
            $socialJson,
            $data['status'] ?? 'ACTIVO',
            $id
        ]);
    }

    /**
     * Eliminar o desactivar un Realtor
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM realtors WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
