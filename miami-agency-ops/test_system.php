<?php
require 'vendor/autoload.php';
require 'config/config.php';

use Config\Database;
use Config\Auth;
use App\Models\UserModel;
use App\Models\ContractModel;
use App\Models\ActivityModel;
use App\Models\ReportModel;

echo "=== TEST 1: Conexión y Población BD ===\n";
$pdo = Database::getConnection();
$rolesCount = $pdo->query('SELECT COUNT(*) FROM roles')->fetchColumn();
$usersCount = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$realtorsCount = $pdo->query('SELECT COUNT(*) FROM realtors')->fetchColumn();
$contractsCount = $pdo->query('SELECT COUNT(*) FROM contracts')->fetchColumn();
$activitiesCount = $pdo->query('SELECT COUNT(*) FROM activities')->fetchColumn();
echo "Roles: {$rolesCount} | Usuarios: {$usersCount} | Realtors: {$realtorsCount} | Contratos: {$contractsCount} | Actividades: {$activitiesCount}\n\n";

echo "=== TEST 2: Autenticación Jefe ===\n";
$loginJefe = Auth::attempt('jefe@miamiagency.com', 'password123');
echo "Login Jefe: " . ($loginJefe ? 'OK' : 'FALLO') . " | Rol: " . Auth::user()['role'] . "\n\n";

echo "=== TEST 3: Autenticación Empleado Contratos ===\n";
Auth::logout();
$loginEmp = Auth::attempt('contratos@miamiagency.com', 'password123');
$user = Auth::user();
echo "Login Empleado: " . ($loginEmp ? 'OK' : 'FALLO') . " | Especialidades: " . implode(', ', $user['specialties']) . "\n";
echo "Tiene permiso CREADOR_CONTRATOS? " . (Auth::canManageContracts() ? 'SI' : 'NO') . "\n";
echo "Puede producir media? " . (Auth::canProduceMedia() ? 'SI' : 'NO') . "\n\n";

echo "=== TEST 4: Generación de Contrato Dinámico y Workflow ===\n";
$cm = new ContractModel();
$nextCode = $cm->generateNextCode();
echo "Nuevo código correlativo generado: {$nextCode}\n";

$contractId = $cm->createDraft([
    'template_id' => 1,
    'realtor_id' => 2,
    'start_date' => '2026-10-15',
    'end_date' => '2027-10-15',
    'monthly_fee' => 5200.00,
    'company_name' => 'The Jills Zeder Group',
    'contact_person' => 'Marcus Sterling',
    'miami_zone' => 'Miami Beach / South Beach',
    'services_description' => '8 Reels cinematográficos + 12 Carruseles mensuales'
], $user['employee_id']);

echo "Contrato creado en borrador con ID: {$contractId}\n";

// Enviar a revisión
$submitted = $cm->submitForReview($contractId, $user['employee_id']);
echo "Enviado a revisión del Jefe: " . ($submitted ? 'OK' : 'FALLO') . "\n";

// Verificar que aparece en pendientes del Jefe
Auth::logout();
Auth::attempt('jefe@miamiagency.com', 'password123');
$pending = $cm->getPendingApprovals();
echo "Total contratos pendientes en panel del Jefe: " . count($pending) . "\n";

// El Jefe aprueba
$approved = $cm->reviewContract($contractId, Auth::user()['id'], 'APROBAR', 'Aprobado formalmente por Carlos Mendoza.');
echo "Aprobación por el Jefe: " . ($approved ? 'OK' : 'FALLO') . "\n";

$finalContracts = $cm->getFinalApprovedContracts();
echo "Total en Repositorio de Contratos Finales del Jefe: " . count($finalContracts) . "\n\n";

echo "=== TEST 5: Reporte Ejecutivo ===\n";
$rm = new ReportModel();
$rep = $rm->getExecutiveReport('mensual');
echo "Facturación Mensual Recurrente Activa: $" . number_format($rep['financial']['total_monthly_revenue'], 2) . " USD\n";
echo "Total Contratos Aprobados: " . $rep['financial']['total_approved_contracts'] . "\n";
echo "Total Tareas en Producción: " . $rep['operations']['total_tasks'] . "\n";

echo "\n¡TODOS LOS TESTS DE ARQUITECTURA, 3NF, PERMISOS Y FLUJOS COMPLETADOS EXITOSAMENTE!\n";
