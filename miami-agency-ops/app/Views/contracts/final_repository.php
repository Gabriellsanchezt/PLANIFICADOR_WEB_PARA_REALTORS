<?php
// Métricas calculadas para la cabecera ejecutiva
$annualProjectedRevenue = $totalRevenue * 12;
$avgTicket = !empty($contracts) ? ($totalRevenue / count($contracts)) : 0;
$uniqueZones = array_unique(array_column($contracts, 'miami_zone'));
?>

<!-- =========================================================================
     CABECERA EJECUTIVA / HERO BANNER DE LUJO (MIAMI LUXURY MEDIA)
     ========================================================================= -->
<div class="repo-hero p-4 p-md-5 mb-4 position-relative">
    <div class="position-relative z-1">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="legal-seal-badge">
                    <i class="bi bi-patch-check-fill text-warning"></i> BÓVEDA CENTRAL CERTIFICADA
                </span>
                <span class="badge bg-white bg-opacity-10 text-light border border-white border-opacity-25 px-3 py-1">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> Miami-Dade County, FL
                </span>
            </div>

            <div class="d-flex gap-2">
                <a href="/reports/export-excel?period=mensual" class="btn btn-sm btn-outline-light d-flex align-items-center gap-2 px-3">
                    <i class="bi bi-file-earmark-spreadsheet-fill text-success"></i> Exportar Auditoría (.csv)
                </a>
                <a href="/contracts/approvals" class="btn btn-sm btn-warning fw-bold d-flex align-items-center gap-2 px-3">
                    <i class="bi bi-inbox-fill"></i> Bandeja de Aprobaciones
                </a>
            </div>
        </div>

        <div class="row align-items-end g-3">
            <div class="col-lg-8">
                <h1 class="display-6 fw-bold mb-2 text-white tracking-tight">
                    Repositorio Oficial de Contratos Finales
                </h1>
                <p class="text-white-50 lead fs-6 mb-0" style="max-width: 720px;">
                    Custodia legal de acuerdos comerciales aprobados, facturación mensual recurrente (MRR) y certificaciones de servicios audiovisuales con Realtors y Brokerages en Miami.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-inline-flex flex-column align-items-lg-end bg-white bg-opacity-10 backdrop-blur p-3 rounded-3 border border-white border-opacity-20">
                    <span class="text-uppercase small text-white-50 fw-semibold" style="letter-spacing: 0.5px;">Facturación Anualizada Proyectada</span>
                    <span class="display-6 fw-bold text-warning mb-0">$<?= number_format($annualProjectedRevenue, 0) ?> <span class="fs-6 text-white-50">USD/año</span></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================
     TARJETAS KPI DE ALTO IMPACTO (DESIGN TOKENS LUXURY)
     ========================================================================= -->
<div class="row g-3 mb-4">
    <!-- Card 1: MRR Activo -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card-luxury p-3 p-md-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon-wrapper stat-glow-emerald">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">
                    <i class="bi bi-arrow-up-right me-1"></i> MRR Activo
                </span>
            </div>
            <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Ingreso Mensual Recurrente</span>
            <h2 class="fw-bold mb-1 text-dark">$<?= number_format($totalRevenue, 2) ?></h2>
            <small class="text-muted d-flex align-items-center gap-1">
                <i class="bi bi-shield-check text-success"></i> Contratos activos garantizados
            </small>
        </div>
    </div>

    <!-- Card 2: Contratos Archivados -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card-luxury p-3 p-md-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon-wrapper stat-glow-blue">
                    <i class="bi bi-file-earmark-lock2-fill"></i>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 small">
                    Norma 3NF ACID
                </span>
            </div>
            <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Total Contratos Aprobados</span>
            <h2 class="fw-bold mb-1 text-dark"><?= count($contracts) ?> <span class="fs-6 fw-normal text-muted">expedientes</span></h2>
            <small class="text-muted d-flex align-items-center gap-1">
                <i class="bi bi-lock-fill text-primary"></i> Inmutables y blindados legalmente
            </small>
        </div>
    </div>

    <!-- Card 3: Ticket Promedio -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card-luxury p-3 p-md-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon-wrapper stat-glow-gold">
                    <i class="bi bi-gem"></i>
                </div>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 small">
                    High-Ticket
                </span>
            </div>
            <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Ticket Promedio / Realtor</span>
            <h2 class="fw-bold mb-1 text-dark">$<?= number_format($avgTicket, 2) ?></h2>
            <small class="text-muted d-flex align-items-center gap-1">
                <i class="bi bi-pie-chart text-warning"></i> Promedio mensual por brokerage
            </small>
        </div>
    </div>

    <!-- Card 4: Zonas de Miami -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card-luxury p-3 p-md-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon-wrapper stat-glow-purple">
                    <i class="bi bi-buildings-fill"></i>
                </div>
                <span class="badge bg-purple-subtle text-purple border rounded-pill px-2 py-1 small" style="background:#f3e8ff; color:#7e22ce;">
                    Miami Hubs
                </span>
            </div>
            <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Cobertura Territorial</span>
            <h2 class="fw-bold mb-1 text-dark"><?= count($uniqueZones) ?> <span class="fs-6 fw-normal text-muted">Zonas clave</span></h2>
            <small class="text-muted text-truncate d-block">
                <i class="bi bi-pin-map text-danger me-1"></i> Brickell, Miami Beach, Gables...
            </small>
        </div>
    </div>
</div>

<!-- =========================================================================
     BARRA DE FILTROS, BUSCADOR INSTANTÁNEO Y SELECTOR DE VISTA
     ========================================================================= -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center justify-content-between">
            <!-- Buscador en tiempo real con icono flotante -->
            <div class="col-12 col-md-5">
                <div class="position-relative">
                    <i class="bi bi-search search-icon-pos"></i>
                    <input type="text" id="liveSearchInput" class="form-control search-box-luxury" placeholder="Buscar por inmobiliaria, broker, código (ej: MIA-CTR)..." onkeyup="filterContractsLive()">
                </div>
            </div>

            <!-- Selector de Zonas de Miami (Dropdown o Pills) -->
            <div class="col-12 col-md-4">
                <form method="GET" action="/contracts/final" id="zoneFilterForm">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-geo-alt"></i></span>
                        <select name="zone" class="form-select border-start-0" onchange="this.form.submit()">
                            <option value="">Todas las Zonas de Miami (<?= count($contracts) ?>)</option>
                            <?php foreach (\Config\MIAMI_ZONES as $zone): ?>
                                <option value="<?= htmlspecialchars($zone) ?>" <?= ($selectedZone === $zone) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($zone) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($selectedZone): ?>
                            <a href="/contracts/final" class="btn btn-outline-secondary" title="Eliminar filtro de zona">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Conmutador de Vista (Tabla vs Tarjetas / Grid) -->
            <div class="col-12 col-md-3 text-md-end">
                <div class="btn-group" role="group">
                    <button type="button" id="btnViewTable" class="btn btn-sm btn-dark fw-semibold active" onclick="switchView('table')">
                        <i class="bi bi-table me-1"></i> Tabla
                    </button>
                    <button type="button" id="btnViewGrid" class="btn btn-sm btn-outline-dark fw-semibold" onclick="switchView('grid')">
                        <i class="bi bi-grid-fill me-1"></i> Expedientes
                    </button>
                </div>
            </div>
        </div>

        <!-- Pills de Filtro Rápido por Zona -->
        <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
            <span class="small text-muted fw-semibold align-self-center me-1"><i class="bi bi-funnel"></i> Zonas:</span>
            <a href="/contracts/final" class="zone-pill-btn <?= empty($selectedZone) ? 'active' : '' ?>">
                Todas
            </a>
            <?php foreach (['Brickell / Financial District', 'Miami Beach / South Beach', 'Coral Gables', 'Sunny Isles Beach'] as $topZone): ?>
                <a href="/contracts/final?zone=<?= urlencode($topZone) ?>" class="zone-pill-btn <?= ($selectedZone === $topZone) ? 'active' : '' ?>">
                    <?= htmlspecialchars($topZone) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- =========================================================================
     VISTA 1: TABLA CORPORATIVA DETALLADA (DEFAULT)
     ========================================================================= -->
<div id="viewContainerTable" class="card border-0 shadow-sm overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold mb-0 text-dark">Expedientes Contractuales Registrados</h5>
            <span class="badge bg-dark rounded-pill" id="contractsCountBadge"><?= count($contracts) ?> contratos</span>
        </div>
        <small class="text-muted"><i class="bi bi-shield-lock-fill text-success me-1"></i>Validación Notarial Miami Media Agency LLC</small>
    </div>

    <div class="card-body p-0">
        <?php if (empty($contracts)): ?>
            <div class="text-center py-5">
                <div class="stat-icon-wrapper bg-light text-muted mx-auto mb-3" style="width: 70px; height: 70px; border-radius: 50%;">
                    <i class="bi bi-folder-x fs-1"></i>
                </div>
                <h5 class="fw-bold text-dark">No se encontraron contratos aprobados</h5>
                <p class="text-muted small">No hay acuerdos que coincidan con la zona o búsqueda especificada.</p>
                <a href="/contracts/final" class="btn btn-outline-primary btn-sm">Ver todos los contratos</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="contractsMainTable">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th style="width: 140px;">Código</th>
                            <th>Inmobiliaria / Broker</th>
                            <th>Zona Miami</th>
                            <th>Vigencia & Plazo</th>
                            <th>Honorarios (MRR)</th>
                            <th>Estado & Auditoría</th>
                            <th class="text-end" style="min-width: 190px;">Acciones Legales</th>
                        </tr>
                    </thead>
                    <tbody id="contractsTableBody">
                        <?php foreach ($contracts as $c): ?>
                            <?php
                                // Monograma para el avatar (ej: OS para One Sotheby's)
                                $words = explode(' ', $c['company_name']);
                                $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                            ?>
                            <tr class="contract-row-item"
                                data-code="<?= strtolower(htmlspecialchars($c['contract_code'])) ?>"
                                data-company="<?= strtolower(htmlspecialchars($c['company_name'])) ?>"
                                data-contact="<?= strtolower(htmlspecialchars($c['contact_person'])) ?>"
                                data-zone="<?= strtolower(htmlspecialchars($c['miami_zone'])) ?>">
                                <!-- Código de Contrato -->
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="badge bg-dark font-monospace px-2 py-1 text-start" style="letter-spacing: 0.5px;">
                                            <?= htmlspecialchars($c['contract_code']) ?>
                                        </span>
                                        <span class="text-muted" style="font-size: 0.7rem; margin-top: 4px;">
                                            <i class="bi bi-hash"></i> ID-<?= $c['id'] ?>
                                        </span>
                                    </div>
                                </td>

                                <!-- Realtor / Inmobiliaria con Monograma -->
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="realtor-avatar-badge flex-shrink-0">
                                            <?= $initials ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 260px;">
                                                <?= htmlspecialchars($c['company_name']) ?>
                                            </div>
                                            <div class="small text-muted d-flex align-items-center gap-2">
                                                <span><i class="bi bi-person-fill text-secondary"></i> <?= htmlspecialchars($c['contact_person']) ?></span>
                                                <?php if (!empty($c['realtor_phone'])): ?>
                                                    <span>&bull;</span>
                                                    <span><i class="bi bi-telephone text-secondary"></i> <?= htmlspecialchars($c['realtor_phone']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Zona de Miami -->
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-geo-alt-fill text-danger"></i> <?= htmlspecialchars($c['miami_zone']) ?>
                                    </span>
                                </td>

                                <!-- Vigencia -->
                                <td>
                                    <div class="small fw-semibold text-dark">
                                        <?= date('d M, Y', strtotime($c['start_date'])) ?> &rarr; <?= date('d M, Y', strtotime($c['end_date'])) ?>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <div class="progress flex-grow-1" style="height: 5px; width: 90px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 85%;"></div>
                                        </div>
                                        <span class="text-muted" style="font-size: 0.7rem;">12 meses</span>
                                    </div>
                                </td>

                                <!-- Tarifa Mensual -->
                                <td>
                                    <div class="fw-bold text-success fs-6">
                                        $<?= number_format($c['monthly_fee'], 2) ?> <span class="small text-muted fw-normal">USD</span>
                                    </div>
                                    <span class="text-muted small d-block" style="font-size: 0.72rem;">
                                        $<?= number_format($c['monthly_fee'] * 12, 0) ?> anualizado
                                    </span>
                                </td>

                                <!-- Estado & Auditoría -->
                                <td>
                                    <div class="pulse-indicator mb-1">
                                        <span class="pulse-dot"></span> VIGENTE
                                    </div>
                                    <div class="small text-muted" style="font-size: 0.72rem;">
                                        <i class="bi bi-check2-circle text-primary"></i> Por <?= htmlspecialchars($c['generator_employee_name']) ?>
                                    </div>
                                </td>

                                <!-- Acciones Legales -->
                                <td class="text-end">
                                    <div class="btn-group shadow-sm">
                                        <button type="button" class="btn btn-sm btn-outline-dark" onclick="openAuditModal(<?= htmlspecialchars(json_encode($c)) ?>)" title="Inspeccionar auditoría rápida">
                                            <i class="bi bi-search"></i>
                                        </button>
                                        <a href="/contracts/view?id=<?= $c['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Abrir documento legal completo">
                                            <i class="bi bi-file-earmark-text me-1"></i> Ver
                                        </a>
                                        <a href="/contracts/view?id=<?= $c['id'] ?>&print=true" target="_blank" class="btn btn-sm btn-dark" title="Imprimir o Descargar PDF formal">
                                            <i class="bi bi-printer-fill me-1"></i> PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- =========================================================================
     VISTA 2: EXPEDIENTES EN CUADRÍCULA / CARDS (OPCIÓN INTERACTIVA)
     ========================================================================= -->
<div id="viewContainerGrid" class="row g-4 mb-4 d-none">
    <?php foreach ($contracts as $c): ?>
        <?php
            $words = explode(' ', $c['company_name']);
            $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
        ?>
        <div class="col-12 col-md-6 col-xl-4 contract-card-item"
             data-code="<?= strtolower(htmlspecialchars($c['contract_code'])) ?>"
             data-company="<?= strtolower(htmlspecialchars($c['company_name'])) ?>"
             data-contact="<?= strtolower(htmlspecialchars($c['contact_person'])) ?>"
             data-zone="<?= strtolower(htmlspecialchars($c['miami_zone'])) ?>">
            <div class="contract-file-card p-4">
                <!-- Header de Tarjeta -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge bg-dark font-monospace px-2 py-1">
                        <?= htmlspecialchars($c['contract_code']) ?>
                    </span>
                    <div class="pulse-indicator">
                        <span class="pulse-dot"></span> VIGENTE
                    </div>
                </div>

                <!-- Broker y Avatar -->
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="realtor-avatar-badge fs-5">
                        <?= $initials ?>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 220px;">
                            <?= htmlspecialchars($c['company_name']) ?>
                        </h6>
                        <small class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($c['contact_person']) ?></small>
                    </div>
                </div>

                <!-- Zona y Detalles -->
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Zona de Miami:</span>
                        <span class="badge bg-white text-dark border">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($c['miami_zone']) ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Tarifa Mensual:</span>
                        <span class="fw-bold text-success fs-6">$<?= number_format($c['monthly_fee'], 2) ?> USD</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Plazo Vigente:</span>
                        <span class="small text-dark fw-semibold"><?= date('M Y', strtotime($c['start_date'])) ?> - <?= date('M Y', strtotime($c['end_date'])) ?></span>
                    </div>
                </div>

                <?php if (!empty($c['last_review_note'])): ?>
                    <div class="small fst-italic text-muted bg-white p-2 rounded border mb-3">
                        <i class="bi bi-chat-quote me-1 text-primary"></i>"<?= htmlspecialchars($c['last_review_note']) ?>"
                    </div>
                <?php endif; ?>

                <!-- Footer y Botones -->
                <div class="mt-auto pt-3 border-top d-flex gap-2 justify-content-between align-items-center">
                    <small class="text-muted" style="font-size: 0.72rem;">
                        <i class="bi bi-check2-circle text-success me-1"></i>Por <?= htmlspecialchars($c['generator_employee_name']) ?>
                    </small>
                    <div class="d-flex gap-1">
                        <a href="/contracts/view?id=<?= $c['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Ver documento">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="/contracts/view?id=<?= $c['id'] ?>&print=true" target="_blank" class="btn btn-sm btn-dark" title="PDF">
                            <i class="bi bi-printer-fill me-1"></i> PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- =========================================================================
     MODAL DE AUDITORÍA Y TRAZABILIDAD RÁPIDA DE CONTRATO
     ========================================================================= -->
<div class="modal fade" id="modalAuditContract" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white p-4">
                <div>
                    <span class="badge bg-warning text-dark font-monospace mb-1" id="auditModalCode">CÓDIGO</span>
                    <h5 class="modal-title fw-bold text-white mb-0" id="auditModalCompany">Inmobiliaria</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="legal-seal-badge"><i class="bi bi-shield-check"></i> ESTADO APROBADO</span>
                    <span class="pulse-indicator"><span class="pulse-dot"></span> REGISTRADO EN 3NF</span>
                </div>

                <div class="list-group list-group-flush border rounded-3 mb-3 small">
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Contacto Principal:</span>
                        <strong id="auditModalContact">-</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Zona de Miami:</span>
                        <strong id="auditModalZone">-</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Tarifa Mensual (USD):</span>
                        <strong class="text-success fs-6" id="auditModalFee">-</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Vigencia:</span>
                        <span id="auditModalDates">-</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Redactor Responsable:</span>
                        <span id="auditModalGenerator">-</span>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 border">
                    <h6 class="fw-bold small text-muted text-uppercase mb-1"><i class="bi bi-pen-fill text-primary me-1"></i>Dictamen del Jefe:</h6>
                    <p class="small text-dark mb-0 fst-italic" id="auditModalNote">"Sin notas adicionales registradas."</p>
                </div>
            </div>
            <div class="modal-footer bg-light p-3 border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <a href="#" id="auditModalViewBtn" target="_blank" class="btn btn-primary btn-sm fw-semibold">
                    <i class="bi bi-file-earmark-text me-1"></i> Abrir Contrato Legal
                </a>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================
     JAVASCRIPT VANILLA: FILTRADO EN VIVO Y CONMUTADOR DE VISTAS
     ========================================================================= -->
<script>
// Conmutar entre Vista Tabla y Vista Grid
function switchView(viewType) {
    const tableContainer = document.getElementById('viewContainerTable');
    const gridContainer = document.getElementById('viewContainerGrid');
    const btnTable = document.getElementById('btnViewTable');
    const btnGrid = document.getElementById('btnViewGrid');

    if (viewType === 'grid') {
        tableContainer.classList.add('d-none');
        gridContainer.classList.remove('d-none');
        btnTable.classList.remove('active', 'btn-dark');
        btnTable.classList.add('btn-outline-dark');
        btnGrid.classList.add('active', 'btn-dark');
        btnGrid.classList.remove('btn-outline-dark');
    } else {
        tableContainer.classList.remove('d-none');
        gridContainer.classList.add('d-none');
        btnGrid.classList.remove('active', 'btn-dark');
        btnGrid.classList.add('btn-outline-dark');
        btnTable.classList.add('active', 'btn-dark');
        btnTable.classList.remove('btn-outline-dark');
    }
}

// Búsqueda instantánea en vivo por texto (Filtra filas de tabla y tarjetas simultáneamente)
function filterContractsLive() {
    const query = document.getElementById('liveSearchInput').value.toLowerCase().trim();

    // Filtrar tabla
    const tableRows = document.querySelectorAll('.contract-row-item');
    let visibleCount = 0;
    tableRows.forEach(row => {
        const code = row.getAttribute('data-code') || '';
        const company = row.getAttribute('data-company') || '';
        const contact = row.getAttribute('data-contact') || '';
        const zone = row.getAttribute('data-zone') || '';

        if (code.includes(query) || company.includes(query) || contact.includes(query) || zone.includes(query)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Filtrar cards
    const gridCards = document.querySelectorAll('.contract-card-item');
    gridCards.forEach(card => {
        const code = card.getAttribute('data-code') || '';
        const company = card.getAttribute('data-company') || '';
        const contact = card.getAttribute('data-contact') || '';
        const zone = card.getAttribute('data-zone') || '';

        if (code.includes(query) || company.includes(query) || contact.includes(query) || zone.includes(query)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });

    // Actualizar badge de conteo
    const badge = document.getElementById('contractsCountBadge');
    if (badge) {
        badge.innerText = visibleCount + ' contratos';
    }
}

// Abrir modal de auditoría rápida
function openAuditModal(contract) {
    document.getElementById('auditModalCode').innerText = contract.contract_code;
    document.getElementById('auditModalCompany').innerText = contract.company_name;
    document.getElementById('auditModalContact').innerText = contract.contact_person;
    document.getElementById('auditModalZone').innerText = contract.miami_zone;
    document.getElementById('auditModalFee').innerText = '$' + Number(contract.monthly_fee).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' USD';
    document.getElementById('auditModalDates').innerText = contract.start_date + ' al ' + contract.end_date;
    document.getElementById('auditModalGenerator').innerText = contract.generator_employee_name;
    document.getElementById('auditModalNote').innerText = contract.last_review_note ? ('"' + contract.last_review_note + '"') : 'Sin observaciones adicionales registradas.';
    document.getElementById('auditModalViewBtn').href = '/contracts/view?id=' + contract.id;

    const modal = new bootstrap.Modal(document.getElementById('modalAuditContract'));
    modal.show();
}
</script>
