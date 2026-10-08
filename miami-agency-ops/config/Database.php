<?php
/**
 * Conexión a Base de Datos (Singleton PDO)
 * Soporta MySQL, MariaDB, PostgreSQL y SQLite para máxima portabilidad y pruebas inmediatas.
 */

namespace Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Obtener instancia única de conexión PDO
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            self::connect();
        }
        return self::$instance;
    }

    private static function connect(): void {
        $driver = defined('DB_DRIVER') ? DB_DRIVER : 'sqlite';

        try {
            if ($driver === 'sqlite') {
                $dbPath = defined('DB_DATABASE') ? DB_DATABASE : (BASE_PATH . '/storage/database.sqlite');
                $isNew = !file_exists($dbPath);

                self::$instance = new PDO("sqlite:" . $dbPath);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$instance->exec("PRAGMA foreign_keys = ON;");

                if ($isNew || filesize($dbPath) === 0) {
                    self::initializeSqliteSchema(self::$instance);
                    self::seedInitialData(self::$instance);
                }
            } elseif ($driver === 'pgsql') {
                $dsn = sprintf("pgsql:host=%s;port=%s;dbname=%s", DB_HOST, DB_PORT, DB_DATABASE);
                self::$instance = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } else {
                // Default: MySQL / MariaDB
                $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4", DB_HOST, DB_PORT, DB_DATABASE);
                self::$instance = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]);
            }
        } catch (PDOException $e) {
            // Si MySQL falla y estamos en desarrollo, intentar fallback elegante a SQLite
            if ($driver !== 'sqlite') {
                error_log("Fallo conexión MySQL/PgSQL ({$e->getMessage()}), realizando fallback automático a SQLite local.");
                $sqlitePath = BASE_PATH . '/storage/database.sqlite';
                $isNew = !file_exists($sqlitePath);
                self::$instance = new PDO("sqlite:" . $sqlitePath);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$instance->exec("PRAGMA foreign_keys = ON;");
                if ($isNew || filesize($sqlitePath) === 0) {
                    self::initializeSqliteSchema(self::$instance);
                    self::seedInitialData(self::$instance);
                }
                return;
            }
            throw new PDOException("Error fatal en conexión de base de datos: " . $e->getMessage());
        }
    }

    /**
     * DDL SQLite normalizado en 3NF idéntico a la especificación de producción
     */
    private static function initializeSqliteSchema(PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS roles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                description TEXT
            );

            CREATE TABLE IF NOT EXISTS specialties (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT NOT NULL UNIQUE,
                name TEXT NOT NULL,
                description TEXT
            );

            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                role_id INTEGER NOT NULL,
                status TEXT DEFAULT 'ACTIVO',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
            );

            CREATE TABLE IF NOT EXISTS user_specialties (
                user_id INTEGER NOT NULL,
                specialty_id INTEGER NOT NULL,
                assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id, specialty_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (specialty_id) REFERENCES specialties(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS employees (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL UNIQUE,
                full_name TEXT NOT NULL,
                phone TEXT,
                hire_date DATE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS realtors (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                company_name TEXT NOT NULL,
                contact_person TEXT NOT NULL,
                phone TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                miami_zone TEXT NOT NULL,
                social_handles TEXT,
                status TEXT DEFAULT 'ACTIVO',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS contract_templates (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT,
                template_body_html TEXT NOT NULL,
                active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS contracts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                realtor_id INTEGER NOT NULL,
                template_id INTEGER NOT NULL,
                generated_by_employee_id INTEGER NOT NULL,
                contract_code TEXT NOT NULL UNIQUE,
                compiled_body_html TEXT NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                monthly_fee REAL NOT NULL,
                status TEXT DEFAULT 'BORRADOR', -- BORRADOR, EN_REVISION, APROBADO, RECHAZADO
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (realtor_id) REFERENCES realtors(id) ON DELETE RESTRICT,
                FOREIGN KEY (template_id) REFERENCES contract_templates(id) ON DELETE RESTRICT,
                FOREIGN KEY (generated_by_employee_id) REFERENCES employees(id) ON DELETE RESTRICT
            );

            CREATE TABLE IF NOT EXISTS contract_reviews (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                contract_id INTEGER NOT NULL,
                reviewer_user_id INTEGER NOT NULL,
                status_assigned TEXT NOT NULL, -- APROBADO, RECHAZADO
                feedback_notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE,
                FOREIGN KEY (reviewer_user_id) REFERENCES users(id) ON DELETE RESTRICT
            );

            CREATE TABLE IF NOT EXISTS activities (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT,
                realtor_id INTEGER NOT NULL,
                employee_id INTEGER NOT NULL,
                contract_id INTEGER,
                activity_type TEXT NOT NULL, -- EDICION_VIDEO, CARRUSEL_FOTO, RODAJE_CAMARA, COMMUNITY_MANAGEMENT, OTRO
                priority TEXT DEFAULT 'MEDIA', -- BAJA, MEDIA, ALTA, URGENTE
                scheduled_date DATE NOT NULL,
                current_status TEXT DEFAULT 'NUEVA', -- NUEVA, EN_PROCESO, ENTREGADA_REVISION, APROBADA, CORRECCION
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (realtor_id) REFERENCES realtors(id) ON DELETE CASCADE,
                FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE RESTRICT,
                FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE SET NULL
            );

            CREATE TABLE IF NOT EXISTS activity_deliverables (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                activity_id INTEGER NOT NULL,
                deliverable_url TEXT NOT NULL,
                deliverable_type TEXT NOT NULL, -- FRAME_IO, GOOGLE_DRIVE, DROPBOX, OTRO
                notes TEXT,
                submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS activity_reviews (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                deliverable_id INTEGER NOT NULL,
                reviewer_user_id INTEGER NOT NULL,
                review_status TEXT NOT NULL, -- APROBADO, REQUIERE_CAMBIOS
                review_notes TEXT NOT NULL,
                reviewed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (deliverable_id) REFERENCES activity_deliverables(id) ON DELETE CASCADE,
                FOREIGN KEY (reviewer_user_id) REFERENCES users(id) ON DELETE RESTRICT
            );
        ");
    }

    /**
     * Población inicial con datos realistas para la agencia en Miami
     */
    private static function seedInitialData(PDO $pdo): void {
        // 1. Roles
        $pdo->exec("
            INSERT INTO roles (id, name, description) VALUES
            (1, 'JEFE_ADMIN', 'Administrador General, aprobación de contratos y supervisión ejecutiva'),
            (2, 'EMPLEADO', 'Personal de planta con especialidades asignadas');
        ");

        // 2. Especialidades Granulares
        $pdo->exec("
            INSERT INTO specialties (id, code, name, description) VALUES
            (1, 'CREADOR_CONTRATOS', 'Generador de Contratos', 'Autorizado para parametrizar y emitir borradores de contratos'),
            (2, 'EDITOR_VIDEO', 'Editor de Video', 'Edición de reels y walkthroughs en Premiere / DaVinci'),
            (3, 'DISENADOR_CARRUSELES', 'Diseñador de Carruseles', 'Diseño de carruseles de propiedades e infografías inmobiliarias'),
            (4, 'CAMAROGRAFO', 'Camarógrafo / Piloto Dron', 'Rodajes en locación de propiedades en Miami'),
            (5, 'COMMUNITY_MANAGER', 'Community Manager', 'Copywriting, publicación y métricas de redes de Realtors');
        ");

        // 3. Usuarios iniciales (password: 'password123' hasheada con bcrypt)
        $passHash = password_hash('password123', PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("
            INSERT INTO users (id, email, password_hash, role_id, status) VALUES
            (1, 'jefe@miamiagency.com', ?, 1, 'ACTIVO'),
            (2, 'contratos@miamiagency.com', ?, 2, 'ACTIVO'),
            (3, 'editor@miamiagency.com', ?, 2, 'ACTIVO'),
            (4, 'creativo@miamiagency.com', ?, 2, 'ACTIVO')
        ");
        $stmt->execute([$passHash, $passHash, $passHash, $passHash]);

        // 4. Asignación de especialidades
        // Usuario 2 -> CREADOR_CONTRATOS
        // Usuario 3 -> EDITOR_VIDEO
        // Usuario 4 -> DISENADOR_CARRUSELES y CAMAROGRAFO
        $pdo->exec("
            INSERT INTO user_specialties (user_id, specialty_id) VALUES
            (2, 1),
            (3, 2),
            (4, 3),
            (4, 4);
        ");

        // 5. Perfil de Empleados
        $pdo->exec("
            INSERT INTO employees (id, user_id, full_name, phone, hire_date) VALUES
            (1, 1, 'Carlos Mendoza (Jefe)', '+1 (305) 555-0199', '2023-01-15'),
            (2, 2, 'Valentina Rossi (Account & Contracts)', '+1 (305) 555-0144', '2023-06-01'),
            (3, 3, 'Mateo Silva (Lead Video Editor)', '+1 (305) 555-0178', '2023-08-15'),
            (4, 4, 'Camila Duarte (Creative & Filmmaker)', '+1 (305) 555-0122', '2024-01-10');
        ");

        // 6. Realtors en Miami
        $pdo->exec("
            INSERT INTO realtors (id, company_name, contact_person, phone, email, miami_zone, social_handles, status) VALUES
            (1, 'One Sotheby’s International Realty', 'Elena Rostova', '+1 (305) 789-4321', 'elena.rostova@sothebysrealty.com', 'Brickell / Financial District', '{\"instagram\":\"@elena_luxury_brickell\",\"tiktok\":\"@elenarostovamiami\"}', 'ACTIVO'),
            (2, 'The Jills Zeder Group / Coldwell Banker', 'Marcus Sterling', '+1 (305) 890-1122', 'marcus.s@jillszeder.com', 'Miami Beach / South Beach', '{\"instagram\":\"@marcus_southbeach_estates\"}', 'ACTIVO'),
            (3, 'Compass Florida Luxury Division', 'Sofia Alvarez', '+1 (305) 441-9988', 'sofia.alvarez@compass.com', 'Coral Gables', '{\"instagram\":\"@sofiaalvarez_coralgables\"}', 'ACTIVO'),
            (4, 'Fortune International Group', 'David Goldstein', '+1 (305) 672-3344', 'david.g@fortuneintl.com', 'Sunny Isles Beach', '{\"instagram\":\"@goldstein_sunnyisles\"}', 'ACTIVO');
        ");

        // 7. Plantillas Dinámicas de Contratos
        $templateHtml1 = <<<'HTML'
<div class="contract-document p-4 bg-white border rounded">
    <div class="text-center mb-4 border-bottom pb-3">
        <h2 class="text-uppercase fw-bold text-dark mb-1">MIAMI LUXURY MEDIA AGENCY LLC</h2>
        <p class="text-muted small mb-0">401 Biscayne Blvd, Suite 2200, Miami, FL 33132 | Phone: +1 (305) 555-9000</p>
        <span class="badge bg-dark mt-2">CONTRATO DE PRESTACIÓN DE SERVICIOS AUDIOVISUALES & MARKETING INMOBILIARIO</span>
    </div>

    <div class="mb-4">
        <p class="lead fs-6">
            El presente acuerdo se celebra el día <strong>{{START_DATE}}</strong> entre <strong>MIAMI LUXURY MEDIA AGENCY LLC</strong> (en adelante, la "Agencia") y el cliente <strong>{{CONTACT_PERSON}}</strong> en representación de <strong>{{COMPANY_NAME}}</strong> (en adelante, el "Realtor"), con ámbito de cobertura principal en la zona de <strong>{{MIAMI_ZONE}}</strong>, Condado de Miami-Dade, Florida.
        </p>
    </div>

    <h5 class="fw-bold border-bottom pb-1">CLÁUSULA PRIMERA: OBJETO DEL SERVICIO Y ENTREGABLES</h5>
    <p>La Agencia proporcionará producción audiovisual cinematográfica y contenido de alto impacto para la cartera de propiedades del Realtor, incluyendo:</p>
    <div class="p-3 bg-light rounded border mb-3">
        <strong>Detalle del Paquete Contratado:</strong><br>
        {{SERVICES_DESCRIPTION}}
    </div>

    <h5 class="fw-bold border-bottom pb-1">CLÁUSULA SEGUNDA: HONORARIOS Y FORMA DE PAGO</h5>
    <p>El Realtor se compromete a abonar a la Agencia una tarifa mensual neta de <strong>${{MONTHLY_FEE}} USD</strong>. Los pagos se efectuarán durante los primeros cinco (5) días hábiles de cada ciclo mensual mediante transferencia ACH o Wire a la cuenta corporativa de la Agencia en Miami.</p>

    <h5 class="fw-bold border-bottom pb-1">CLÁUSULA TERCERA: VIGENCIA Y DERECHOS DE IMAGEN</h5>
    <p>El presente contrato entrará en vigor el <strong>{{START_DATE}}</strong> y mantendrá su validez hasta el <strong>{{END_DATE}}</strong>. Todos los entregables finales autorizados podrán ser difundidos en redes sociales (Instagram, TikTok, YouTube) y plataformas MLS sin restricción territorial por parte del Realtor.</p>

    <div class="row mt-5 pt-4 text-center">
        <div class="col-6">
            <div class="border-top border-dark pt-2 mx-4">
                <strong>Por: MIAMI LUXURY MEDIA AGENCY LLC</strong><br>
                <small class="text-muted">Representante Autorizado</small>
            </div>
        </div>
        <div class="col-6">
            <div class="border-top border-dark pt-2 mx-4">
                <strong>Por: {{COMPANY_NAME}}</strong><br>
                <small class="text-muted">{{CONTACT_PERSON}} - Realtor Asociado</small>
            </div>
        </div>
    </div>
</div>
HTML;

        $stmtTpl = $pdo->prepare("
            INSERT INTO contract_templates (id, title, description, template_body_html, active) VALUES
            (1, 'Contrato Estándar Audiovisual & Redes para Realtors', 'Plantilla corporativa con cláusulas para producción de reels, walkthroughs y derechos MLS en el Condado de Miami-Dade.', ?, 1)
        ");
        $stmtTpl->execute([$templateHtml1]);

        // 8. Contrato de prueba (Aprobado en Repositorio del Jefe)
        $compiledBodyDemo = str_replace(
            ['{{START_DATE}}', '{{END_DATE}}', '{{CONTACT_PERSON}}', '{{COMPANY_NAME}}', '{{MIAMI_ZONE}}', '{{SERVICES_DESCRIPTION}}', '{{MONTHLY_FEE}}'],
            ['2026-01-01', '2026-12-31', 'Elena Rostova', 'One Sotheby’s International Realty', 'Brickell / Financial District', '4 Reels cinematográficos 4K con dron + 1 Walkthrough de 2 minutos mensuales', '3,500.00'],
            $templateHtml1
        );

        $pdo->exec("
            INSERT INTO contracts (id, realtor_id, template_id, generated_by_employee_id, contract_code, compiled_body_html, start_date, end_date, monthly_fee, status) VALUES
            (1, 1, 1, 2, 'MIA-CTR-2026-001', " . $pdo->quote($compiledBodyDemo) . ", '2026-01-01', '2026-12-31', 3500.00, 'APROBADO');

            INSERT INTO contract_reviews (contract_id, reviewer_user_id, status_assigned, feedback_notes) VALUES
            (1, 1, 'APROBADO', 'Contrato inicial firmado y verificado con One Sothebys Brickell.');
        ");

        // 9. Actividades Audiovisuales iniciales
        $pdo->exec("
            INSERT INTO activities (id, title, description, realtor_id, employee_id, contract_id, activity_type, priority, scheduled_date, current_status) VALUES
            (1, 'Edición Reel Penthouse Brickell Flatiron', 'Montaje con música de tendencia y tomas de balcón al atardecer', 1, 3, 1, 'EDICION_VIDEO', 'ALTA', '2026-10-12', 'EN_PROCESO'),
            (2, 'Carrusel 5 Tips de Inversión en South Beach', 'Diseño de 7 láminas en Canva/Photoshop con paleta de colores de Coldwell', 2, 4, NULL, 'CARRUSEL_FOTO', 'MEDIA', '2026-10-14', 'NUEVA'),
            (3, 'Grabación Dron & Gimbal Mansión Coral Gables', 'Rodaje en locación con Realtor Marcus Sterling a las 9:00 AM', 3, 4, NULL, 'RODAJE_CAMARA', 'URGENTE', '2026-10-15', 'NUEVA'),
            (4, 'Edición Video Walkthrough Sunny Isles', 'Recorte de tomas y etalonaje en DaVinci Resolve', 4, 3, NULL, 'EDICION_VIDEO', 'MEDIA', '2026-10-10', 'ENTREGADA_REVISION');

            INSERT INTO activity_deliverables (id, activity_id, deliverable_url, deliverable_type, notes) VALUES
            (1, 4, 'https://frame.io/v/miami-sunny-isles-walkthrough-v1', 'FRAME_IO', 'Versión preliminar con corrección de color y música instrumental aprobada.');
        ");
    }
}
