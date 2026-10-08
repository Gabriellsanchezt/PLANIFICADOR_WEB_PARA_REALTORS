<?php
/**
 * Servicio de Autenticación, Sesiones y Control de Acceso Granular (RBAC)
 * Gestiona Roles (JEFE_ADMIN, EMPLEADO) y Especialidades Granulares
 */

namespace Config;

use PDO;

class Auth {
    /**
     * Iniciar sesión segura con parámetros recomendados de cookies
     */
    public static function initSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent() && php_sapi_name() !== 'cli') {
                @ini_set('session.cookie_httponly', '1');
                @ini_set('session.use_only_cookies', '1');
            }
            @session_start();
        }
    }

    /**
     * Autenticar usuario por credenciales
     */
    public static function attempt(string $email, string $password): bool {
        self::initSession();
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT u.id, u.email, u.password_hash, u.status, u.role_id, r.name AS role_name,
                   e.id AS employee_id, e.full_name, e.phone
            FROM users u
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN employees e ON e.user_id = u.id
            WHERE u.email = ? AND u.status = 'ACTIVO'
            LIMIT 1
        ");
        $stmt->execute([trim($email)]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Cargar especialidades del usuario (M:N)
            $specStmt = $pdo->prepare("
                SELECT s.code, s.name
                FROM user_specialties us
                JOIN specialties s ON us.specialty_id = s.id
                WHERE us.user_id = ?
            ");
            $specStmt->execute([$user['id']]);
            $specialties = $specStmt->fetchAll();

            $specialtyCodes = array_column($specialties, 'code');

            // Guardar en sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['employee_id'] = $user['employee_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'] ?: 'Usuario';
            $_SESSION['role'] = $user['role_name'];
            $_SESSION['specialties'] = $specialtyCodes;
            $_SESSION['specialties_details'] = $specialties;

            // Regenerar ID de sesión para prevenir Session Fixation si la sesión está activa
            if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
                @session_regenerate_id(true);
            }
            return true;
        }

        return false;
    }

    /**
     * Obtener el usuario autenticado actualmente
     */
    public static function user(): ?array {
        self::initSession();
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'employee_id' => $_SESSION['employee_id'] ?? null,
            'email' => $_SESSION['email'],
            'full_name' => $_SESSION['full_name'],
            'role' => $_SESSION['role'],
            'specialties' => $_SESSION['specialties'] ?? [],
            'specialties_details' => $_SESSION['specialties_details'] ?? []
        ];
    }

    /**
     * Verificar si hay una sesión activa
     */
    public static function check(): bool {
        self::initSession();
        return isset($_SESSION['user_id']);
    }

    /**
     * Verificar si el usuario actual es el Jefe / Administrador
     */
    public static function isJefe(): bool {
        $user = self::user();
        return $user !== null && $user['role'] === 'JEFE_ADMIN';
    }

    /**
     * Verificar si el usuario actual es un empleado
     */
    public static function isEmpleado(): bool {
        $user = self::user();
        return $user !== null && $user['role'] === 'EMPLEADO';
    }

    /**
     * Verificar si el usuario cuenta con una especialidad específica
     * Nota: El Jefe posee privilegios globales para inspeccionar cualquier módulo
     */
    public static function hasSpecialty(string $code): bool {
        $user = self::user();
        if (!$user) return false;
        if ($user['role'] === 'JEFE_ADMIN') return true;

        return in_array($code, $user['specialties'], true);
    }

    /**
     * Permiso explícito para crear/gestionar borradores de contratos
     */
    public static function canManageContracts(): bool {
        return self::isJefe() || self::hasSpecialty('CREADOR_CONTRATOS');
    }

    /**
     * Permiso para ver y ejecutar tareas del flujo audiovisual
     */
    public static function canProduceMedia(): bool {
        if (self::isJefe()) return true;
        $creativeCodes = ['EDITOR_VIDEO', 'DISENADOR_CARRUSELES', 'CAMAROGRAFO', 'COMMUNITY_MANAGER'];
        $user = self::user();
        if (!$user) return false;
        return count(array_intersect($creativeCodes, $user['specialties'])) > 0;
    }

    /**
     * Middleware de Autenticación
     */
    public static function requireAuth(): void {
        if (!self::check()) {
            $_SESSION['flash_error'] = 'Debe iniciar sesión para acceder al sistema.';
            $target = function_exists('url') ? url('/login') : '/login';
            header("Location: {$target}");
            exit;
        }
    }

    /**
     * Middleware exclusivo para el Jefe
     */
    public static function requireJefe(): void {
        self::requireAuth();
        if (!self::isJefe()) {
            http_response_code(403);
            die(self::renderForbiddenView("Acceso denegado: Este módulo está reservado exclusivamente para la Dirección General (JEFE_ADMIN)."));
        }
    }

    /**
     * Middleware para Especialidad requerida
     */
    public static function requireSpecialty(string $code): void {
        self::requireAuth();
        if (!self::hasSpecialty($code)) {
            http_response_code(403);
            die(self::renderForbiddenView("Acceso denegado: Su usuario no posee la especialidad requerida ('{$code}')."));
        }
    }

    /**
     * Middleware para módulo de Contratos
     */
    public static function requireContractPermission(): void {
        self::requireAuth();
        if (!self::canManageContracts()) {
            http_response_code(403);
            die(self::renderForbiddenView("Acceso denegado: No posee permisos de 'CREADOR_CONTRATOS' ni rango administrativo."));
        }
    }

    /**
     * Cerrar sesión y limpiar cookies
     */
    public static function logout(): void {
        self::initSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies") && !headers_sent()) {
            $params = session_get_cookie_params();
            @setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
    }

    /**
     * Token CSRF
     */
    public static function csrfToken(): string {
        self::initSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrf(?string $token): bool {
        self::initSession();
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
    }

    private static function renderForbiddenView(string $message): string {
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acceso Restringido | Miami Agency</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-sm border-danger p-4 text-center" style="max-width: 500px;">
        <h1 class="display-4 text-danger fw-bold">403</h1>
        <h4 class="mb-3">Permiso Insuficiente</h4>
        <p class="text-muted">{$message}</p>
        <a href="/dashboard" class="btn btn-outline-primary mt-3">Volver al Panel Principal</a>
    </div>
</body>
</html>
HTML;
    }
}
