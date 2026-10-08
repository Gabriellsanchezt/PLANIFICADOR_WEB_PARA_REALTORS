<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema | Miami Luxury Media</title>
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('/assets/css/style.css') ?>">
    <style>
        body {
            background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 50%, #0d2847 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 480px;
            overflow: hidden;
        }
        .login-header {
            background: #0f172a;
            color: #ffffff;
            padding: 30px 25px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-card m-3">
    <div class="login-header">
        <div class="badge bg-primary px-3 py-2 mb-2 fs-6">
            <i class="bi bi-camera-reels-fill me-1"></i> MIAMI MEDIA OPS
        </div>
        <h4 class="fw-bold mb-1">Operaciones Audiovisuales & Contratos</h4>
        <p class="text-secondary small mb-0">Gestión de Realtors, Flujo de Trabajo y Roles (Miami-Dade)</p>
    </div>

    <div class="p-4">
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger py-2 px-3 small mb-3">
                <i class="bi bi-exclamation-circle-fill me-1"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_info'])): ?>
            <div class="alert alert-info py-2 px-3 small mb-3">
                <i class="bi bi-info-circle-fill me-1"></i> <?= htmlspecialchars($_SESSION['flash_info']) ?>
            </div>
            <?php unset($_SESSION['flash_info']); ?>
        <?php endif; ?>

        <form action="<?= url('/login') ?>" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold text-secondary">Correo Corporativo</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="usuario@miamiagency.com">
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label small fw-semibold text-secondary">Contraseña de Acceso</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Entrar al Sistema
            </button>
        </form>

        <div class="mt-4 pt-3 border-top">
            <div class="text-center small text-muted mb-2 fw-semibold">
                Acceso de Prueba Rápido por Perfil:
            </div>
            <div class="d-flex flex-column gap-2">
                <button type="button" class="btn btn-sm btn-outline-danger text-start d-flex justify-content-between align-items-center"
                        onclick="fillCreds('jefe@miamiagency.com', 'password123')">
                    <span><strong>1. Jefe (Admin):</strong> Carlos Mendoza</span>
                    <span class="badge bg-danger">JEFE_ADMIN</span>
                </button>

                <button type="button" class="btn btn-sm btn-outline-primary text-start d-flex justify-content-between align-items-center"
                        onclick="fillCreds('contratos@miamiagency.com', 'password123')">
                    <span><strong>2. Empleado:</strong> Valentina Rossi</span>
                    <span class="badge bg-primary">CREADOR_CONTRATOS</span>
                </button>

                <button type="button" class="btn btn-sm btn-outline-success text-start d-flex justify-content-between align-items-center"
                        onclick="fillCreds('editor@miamiagency.com', 'password123')">
                    <span><strong>3. Empleado:</strong> Mateo Silva</span>
                    <span class="badge bg-success">EDITOR_VIDEO</span>
                </button>

                <button type="button" class="btn btn-sm btn-outline-dark text-start d-flex justify-content-between align-items-center"
                        onclick="fillCreds('creativo@miamiagency.com', 'password123')">
                    <span><strong>4. Empleado:</strong> Camila Duarte</span>
                    <span class="badge bg-dark">CARRUSELES / CÁMARA</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function fillCreds(email, pass) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = pass;
}
</script>

</body>
</html>
