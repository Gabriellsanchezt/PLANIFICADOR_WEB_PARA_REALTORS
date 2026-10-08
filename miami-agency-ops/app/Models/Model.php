<?php
/**
 * Clase Base Model
 * Proporciona acceso a PDO y utilidades de consultas preparadas
 */

namespace App\Models;

use Config\Database;
use PDO;

abstract class Model {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Iniciar transacción
     */
    public function beginTransaction(): bool {
        return $this->db->beginTransaction();
    }

    /**
     * Confirmar transacción
     */
    public function commit(): bool {
        return $this->db->commit();
    }

    /**
     * Revertir transacción
     */
    public function rollBack(): bool {
        return $this->db->rollBack();
    }
}
