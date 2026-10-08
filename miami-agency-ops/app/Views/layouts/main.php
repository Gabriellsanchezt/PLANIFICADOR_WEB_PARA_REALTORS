<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Miami Agency Operations') ?></title>
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= url('/assets/css/style.css') ?>">
    <script>
        window.APP_BASE_URL = "<?= defined('BASE_URL') ? BASE_URL : '' ?>";
        window.appUrl = function(path) {
            path = '/' + path.replace(/^\/+/, '');
            return window.APP_BASE_URL + path;
        };
    </script>
</head>
<body class="bg-light">

    <!-- Barra de Navegación Principal -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm border-bottom border-secondary border-opacity-25">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="<?= url('/dashboard') ?>">
                <span class="badge bg-primary px-2 py-1"><i class="bi bi-camera-reels-fill"></i> MIA</span>
                <span>MIAMI LUXURY MEDIA</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/dashboard') ?>"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                    </li>

                    <?php if (\Config\Auth::isJefe()): ?>
                        <!-- MENÚ EXCLUSIVO DEL JEFE (ADMINISTRADOR) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-warning" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-file-earmark-check-fill me-1"></i> Contratos & Aprobaciones
                            </a>
                            <ul class="dropdown-menu shadow">
                                <li>
                                    <a class="dropdown-item" href="<?= url('/contracts/final') ?>">
                                        <i class="bi bi-archive-fill text-success me-2"></i> Repositorio "Contratos Finales"
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= url('/contracts/approvals') ?>">
                                        <i class="bi bi-bell-fill text-warning me-2"></i> Aprobación de Contratos
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/activities') ?>">
                                <i class="bi bi-kanban me-1"></i> Tablero Audiovisual
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/realtors') ?>">
                                <i class="bi bi-building me-1"></i> + Realtors
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/team') ?>">
                                <i class="bi bi-people me-1"></i> + Equipo & Roles
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/reports') ?>">
                                <i class="bi bi-graph-up-arrow me-1"></i> Reportes Ejecutivos
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- MENÚ SEGÚN ESPECIALIDAD DEL EMPLEADO -->
                        <?php if (\Config\Auth::hasSpecialty('CREADOR_CONTRATOS')): ?>
                            <li class="nav-item">
                                <a class="nav-link text-info fw-semibold" href="<?= url('/contracts/create') ?>">
                                    <i class="bi bi-file-earmark-text me-1"></i> Generador de Contratos
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (\Config\Auth::canProduceMedia()): ?>
                            <li class="nav-item">
                                <a class="nav-link text-success fw-semibold" href="<?= url('/activities/my-tasks') ?>">
                                    <i class="bi bi-play-circle me-1"></i> Mis Tareas Audiovisuales
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>

                <!-- Panel de Usuario y Sesión -->
                <div class="d-flex align-items-center gap-3">
                    <div class="text-end text-light">
                        <div class="fw-semibold small"><?= htmlspecialchars($currentUser['full_name'] ?? 'Usuario') ?></div>
                        <div class="d-flex gap-1 justify-content-end align-items-center">
                            <?php if (($currentUser['role'] ?? '') === 'JEFE_ADMIN'): ?>
                                <span class="badge bg-danger text-uppercase" style="font-size: 0.65rem;">JEFE ADMIN</span>
                            <?php else: ?>
                                <span class="badge bg-secondary text-uppercase" style="font-size: 0.65rem;">EMPLEADO</span>
                                <?php foreach (($currentUser['specialties'] ?? []) as $spec): ?>
                                    <span class="badge bg-info text-dark" style="font-size: 0.62rem;"><?= htmlspecialchars($spec) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <a href="<?= url('/logout') ?>" class="btn btn-outline-danger btn-sm" title="Cerrar sesión">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Alertas Flash -->
    <div class="container-fluid px-4 mt-3">
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_warning'])): ?>
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i> <?= htmlspecialchars($_SESSION['flash_warning']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_warning']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_info'])): ?>
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> <?= htmlspecialchars($_SESSION['flash_info']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_info']); ?>
        <?php endif; ?>
    </div>

    <!-- Contenido Principal -->
    <main class="container-fluid px-4 py-3">
        <?= $content ?>
    </main>

    <!-- Pie de Página -->
    <footer class="bg-white border-top py-3 mt-5 text-center text-muted small">
        <div class="container-fluid px-4">
            Miami Luxury Media Agency &copy; <?= date('Y') ?> | Operaciones Inmobiliarias, Contratos & Producción Audiovisual (Miami-Dade, FL)
        </div>
    </footer>

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Application JS -->
    <script src="<?= url('/assets/js/app.js') ?>"></script>
</body>
</html>
