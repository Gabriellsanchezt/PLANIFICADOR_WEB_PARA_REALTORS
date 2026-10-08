<?php
/**
 * Modelo de Usuarios, Roles, Especialidades y Empleados
 * Arquitectura 3NF estricta
 */

namespace App\Models;

use PDO;

class UserModel extends Model {
    /**
     * Obtener listado de todo el equipo de trabajo con sus roles y especialidades
     */
    public function getAllEmployeesWithDetails(): array {
        $sql = "
            SELECT e.id AS employee_id, e.user_id, e.full_name, e.phone, e.hire_date,
                   u.email, u.status AS user_status, r.name AS role_name, r.id AS role_id
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            ORDER BY e.id ASC
        ";
        $employees = $this->db->query($sql)->fetchAll();

        // Cargar especialidades para cada empleado
        foreach ($employees as &$emp) {
            $stmt = $this->db->prepare("
                SELECT s.id, s.code, s.name
                FROM user_specialties us
                JOIN specialties s ON us.specialty_id = s.id
                WHERE us.user_id = ?
            ");
            $stmt->execute([$emp['user_id']]);
            $emp['specialties'] = $stmt->fetchAll();
        }

        return $employees;
    }

    /**
     * Obtener empleados filtrados por especialidad
     */
    public function getEmployeesBySpecialty(string $specialtyCode): array {
        $stmt = $this->db->prepare("
            SELECT e.id, e.full_name, e.phone, u.email, s.code AS specialty_code
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN user_specialties us ON us.user_id = u.id
            JOIN specialties s ON us.specialty_id = s.id
            WHERE s.code = ? AND u.status = 'ACTIVO'
            ORDER BY e.full_name ASC
        ");
        $stmt->execute([$specialtyCode]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener todos los roles disponibles
     */
    public function getAllRoles(): array {
        return $this->db->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();
    }

    /**
     * Obtener todas las especialidades disponibles
     */
    public function getAllSpecialties(): array {
        return $this->db->query("SELECT * FROM specialties ORDER BY id ASC")->fetchAll();
    }

    /**
     * Crear un nuevo miembro del equipo (Transacción atómica: user + employee + specialties)
     */
    public function createTeamMember(array $data, array $specialtyIds): int {
        $this->beginTransaction();
        try {
            // 1. Crear usuario
            $passHash = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmtUser = $this->db->prepare("
                INSERT INTO users (email, password_hash, role_id, status)
                VALUES (?, ?, ?, 'ACTIVO')
            ");
            $stmtUser->execute([
                trim($data['email']),
                $passHash,
                (int)$data['role_id']
            ]);
            $userId = (int)$this->db->lastInsertId();

            // 2. Crear ficha laboral en employees
            $stmtEmp = $this->db->prepare("
                INSERT INTO employees (user_id, full_name, phone, hire_date)
                VALUES (?, ?, ?, ?)
            ");
            $stmtEmp->execute([
                $userId,
                trim($data['full_name']),
                trim($data['phone'] ?? ''),
                $data['hire_date'] ?: date('Y-m-d')
            ]);
            $employeeId = (int)$this->db->lastInsertId();

            // 3. Asociar especialidades (M:N)
            if (!empty($specialtyIds)) {
                $stmtSpec = $this->db->prepare("
                    INSERT INTO user_specialties (user_id, specialty_id)
                    VALUES (?, ?)
                ");
                foreach ($specialtyIds as $specId) {
                    $stmtSpec->execute([$userId, (int)$specId]);
                }
            }

            $this->commit();
            return $employeeId;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Actualizar especialidades asignadas a un usuario
     */
    public function updateUserSpecialties(int $userId, array $specialtyIds): void {
        $this->beginTransaction();
        try {
            $delStmt = $this->db->prepare("DELETE FROM user_specialties WHERE user_id = ?");
            $delStmt->execute([$userId]);

            if (!empty($specialtyIds)) {
                $insStmt = $this->db->prepare("INSERT INTO user_specialties (user_id, specialty_id) VALUES (?, ?)");
                foreach ($specialtyIds as $specId) {
                    $insStmt->execute([$userId, (int)$specId]);
                }
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }
}
