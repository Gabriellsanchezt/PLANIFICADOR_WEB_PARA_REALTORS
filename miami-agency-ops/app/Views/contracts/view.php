<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Documento Legal de Contrato') ?></title>
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
        }
        .document-wrapper {
            max-width: 900px;
            margin: 30px auto;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-radius: 8px;
            padding: 50px 60px;
        }
        @media print {
            body {
                background: #ffffff;
            }
            .document-wrapper {
                box-shadow: none;
                margin: 0;
                padding: 20px;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<!-- Barra de Controles (No imprimible) -->
<div class="no-print bg-dark text-white py-3 px-4 shadow-sm mb-3">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <span class="badge bg-primary me-2 font-monospace"><?= htmlspecialchars($contract['contract_code']) ?></span>
            <span>Estado: <strong><?= htmlspecialchars($contract['status']) ?></strong></span>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-light btn-sm fw-bold">
                <i class="bi bi-printer-fill me-1"></i> Imprimir / Guardar en PDF
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary btn-sm">
                Cerrar
            </button>
        </div>
    </div>
</div>

<div class="document-wrapper">
    <!-- Encabezado de Documento -->
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-dark">MIAMI LUXURY MEDIA AGENCY LLC</h3>
            <div class="text-muted small">Contrato Oficial de Servicios Audiovisuales & Marketing Inmobiliario</div>
            <div class="small text-muted font-monospace mt-1">CÓDIGO: <?= htmlspecialchars($contract['contract_code']) ?></div>
        </div>
        <div class="text-end">
            <?php
                $statusColor = match($contract['status']) {
                    'APROBADO' => 'bg-success',
                    'EN_REVISION' => 'bg-warning text-dark',
                    'RECHAZADO' => 'bg-danger',
                    default => 'bg-secondary'
                };
            ?>
            <span class="badge <?= $statusColor ?> fs-6 text-uppercase px-3 py-2"><?= htmlspecialchars($contract['status']) ?></span>
            <div class="small text-muted mt-1">Miami, FL &bull; <?= htmlspecialchars($contract['start_date']) ?></div>
        </div>
    </div>

    <!-- Metadatos del Cliente -->
    <div class="row g-3 bg-light p-3 rounded border mb-4">
        <div class="col-md-6">
            <small class="text-uppercase text-muted fw-bold d-block" style="font-size: 0.7rem;">Inmobiliaria / Brokerage</small>
            <div class="fw-bold"><?= htmlspecialchars($contract['company_name']) ?></div>
            <small class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($contract['contact_person']) ?></small>
        </div>
        <div class="col-md-3">
            <small class="text-uppercase text-muted fw-bold d-block" style="font-size: 0.7rem;">Zona de Cobertura</small>
            <div class="fw-bold text-primary"><?= htmlspecialchars($contract['miami_zone']) ?></div>
        </div>
        <div class="col-md-3">
            <small class="text-uppercase text-muted fw-bold d-block" style="font-size: 0.7rem;">Honorarios Mensuales</small>
            <div class="fw-bold text-success fs-5">$<?= number_format($contract['monthly_fee'], 2) ?> USD</div>
        </div>
    </div>

    <!-- Contenido Compilado del Contrato -->
    <div class="contract-body mb-5">
        <?= $contract['compiled_body_html'] ?>
    </div>

    <!-- Historial de Revisiones / Auditoría del Jefe -->
    <?php if (!empty($contract['reviews'])): ?>
        <div class="mt-5 pt-3 border-top">
            <h6 class="fw-bold text-secondary text-uppercase small"><i class="bi bi-clock-history me-1"></i>Trazabilidad de Revisiones de Dirección General:</h6>
            <div class="list-group list-group-flush small">
                <?php foreach ($contract['reviews'] as $rev): ?>
                    <div class="list-group-item px-0 py-2">
                        <div class="d-flex justify-content-between">
                            <strong>Decisión: <span class="badge <?= $rev['status_assigned'] === 'APROBADO' ? 'bg-success' : 'bg-danger' ?>"><?= htmlspecialchars($rev['status_assigned']) ?></span></strong>
                            <span class="text-muted"><?= htmlspecialchars($rev['created_at']) ?></span>
                        </div>
                        <div class="text-muted">Revisado por: <?= htmlspecialchars($rev['reviewer_name'] ?? $rev['reviewer_email']) ?></div>
                        <?php if (!empty($rev['feedback_notes'])): ?>
                            <div class="fst-italic text-dark mt-1 bg-light p-2 rounded border">"<?= htmlspecialchars($rev['feedback_notes']) ?>"</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (isset($_GET['print'])): ?>
<script>
window.addEventListener('DOMContentLoaded', () => {
    window.print();
});
</script>
<?php endif; ?>

</body>
</html>
