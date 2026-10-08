<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard de Dirección General</h2>
        <p class="text-muted mb-0">Control integral de producción audiovisual, contratos y cartera de Realtors en Miami</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/reports" class="btn btn-outline-dark">
            <i class="bi bi-graph-up me-1"></i> Reportes Ejecutivos
        </a>
        <a href="/contracts/approvals" class="btn btn-warning position-relative fw-semibold">
            <i class="bi bi-file-earmark-check-fill me-1"></i> Revisar Contratos
            <?php if (count($pendingApprovals) > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?= count($pendingApprovals) ?>
                </span>
            <?php endif; ?>
        </a>
    </div>
</div>

<!-- Tarjetas KPI del Flujo de Trabajo (Pipeline) -->
<div class="row g-3 mb-4">
    <!-- Nuevas -->
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm border-start border-primary border-4 h-100">
            <div class="card-body">
                <span class="text-muted small fw-semibold text-uppercase">Tareas Nuevas</span>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <h3 class="fw-bold mb-0 text-primary"><?= $counts['nuevas'] ?></h3>
                    <i class="bi bi-plus-circle text-primary fs-3"></i>
                </div>
                <a href="/activities?status=NUEVA" class="stretched-link small text-decoration-none mt-2 d-inline-block">Ver detalles &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Canalizadas (En Proceso) -->
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm border-start border-info border-4 h-100">
            <div class="card-body">
                <span class="text-muted small fw-semibold text-uppercase">Canalizadas</span>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <h3 class="fw-bold mb-0 text-info"><?= $counts['canalizadas'] ?></h3>
                    <i class="bi bi-arrow-repeat text-info fs-3"></i>
                </div>
                <a href="/activities?status=EN_PROCESO" class="stretched-link small text-decoration-none mt-2 d-inline-block">En producción &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Por Aprobar (Entregadas) -->
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm border-start border-warning border-4 h-100 bg-warning-subtle">
            <div class="card-body">
                <span class="text-dark small fw-semibold text-uppercase">Por Aprobar</span>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <h3 class="fw-bold mb-0 text-warning-emphasis"><?= $counts['por_aprobar'] ?></h3>
                    <i class="bi bi-hourglass-split text-warning-emphasis fs-3"></i>
                </div>
                <a href="/activities?status=ENTREGADA_REVISION" class="stretched-link small text-decoration-none mt-2 d-inline-block text-warning-emphasis">Revisar videos &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Culminadas (Aprobadas) -->
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm border-start border-success border-4 h-100">
            <div class="card-body">
                <span class="text-muted small fw-semibold text-uppercase">Culminadas</span>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <h3 class="fw-bold mb-0 text-success"><?= $counts['culminadas'] ?></h3>
                    <i class="bi bi-check2-circle text-success fs-3"></i>
                </div>
                <a href="/activities?status=APROBADA" class="stretched-link small text-decoration-none mt-2 d-inline-block">Completadas &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Contratos Finales Activos -->
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm border-start border-dark border-4 h-100">
            <div class="card-body">
                <span class="text-muted small fw-semibold text-uppercase">Contratos Finales</span>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <h3 class="fw-bold mb-0 text-dark"><?= $finalContractsCount ?></h3>
                    <i class="bi bi-file-earmark-lock-fill text-dark fs-3"></i>
                </div>
                <a href="/contracts/final" class="stretched-link small text-decoration-none mt-2 d-inline-block">Ver archivo &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Facturación Mensual Recurrente -->
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm border-start border-success border-4 h-100 bg-success-subtle">
            <div class="card-body">
                <span class="text-success-emphasis small fw-semibold text-uppercase">MRR Contratos</span>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <h4 class="fw-bold mb-0 text-success-emphasis">$<?= number_format($totalMonthlyRevenue, 0) ?></h4>
                    <i class="bi bi-currency-dollar text-success-emphasis fs-3"></i>
                </div>
                <span class="small text-muted">Ingresos Miami-Dade</span>
            </div>
        </div>
    </div>
</div>

<!-- Sección de Acción Inmediata: Contratos Pendientes de Revisión -->
<div class="row g-4 mb-4">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-inbox-fill text-warning me-2"></i>Contratos Enviados a Revisión
                </h5>
                <span class="badge bg-warning text-dark"><?= count($pendingApprovals) ?> esperando decisión</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pendingApprovals)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-check2-all display-4 text-success d-block mb-2"></i>
                        No hay borradores de contratos pendientes de revisión en este momento.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase">
                                <tr>
                                    <th>Código</th>
                                    <th>Realtor / Inmobiliaria</th>
                                    <th>Zona Miami</th>
                                    <th>Tarifa</th>
                                    <th>Generado Por</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingApprovals as $p): ?>
                                    <tr>
                                        <td class="fw-bold font-monospace"><?= htmlspecialchars($p['contract_code']) ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($p['company_name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($p['contact_person']) ?></small>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['miami_zone']) ?></span></td>
                                        <td class="fw-bold text-success">$<?= number_format($p['monthly_fee'], 2) ?></td>
                                        <td class="small"><?= htmlspecialchars($p['generator_employee_name']) ?></td>
                                        <td class="text-end">
                                            <a href="/contracts/approvals" class="btn btn-sm btn-primary">
                                                Revisar & Decidir
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tareas Recientes del Pipeline Audiovisual -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-camera-video-fill text-primary me-2"></i>Flujo de Producción Audiovisual Reciente
                </h5>
                <a href="/activities" class="btn btn-sm btn-outline-primary">Ver Tablero Completo</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentActivities)): ?>
                    <div class="text-center py-5 text-muted">
                        No hay tareas registradas en el flujo.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase">
                                <tr>
                                    <th>Tarea</th>
                                    <th>Cliente Realtor</th>
                                    <th>Asignado A</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentActivities as $act): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-semibold"><?= htmlspecialchars($act['title']) ?></span><br>
                                            <small class="text-muted"><i class="bi bi-tag"></i> <?= htmlspecialchars($act['activity_type']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($act['company_name']) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($act['employee_name']) ?></span></td>
                                        <td>
                                            <?php
                                                $badgeClass = match($act['current_status']) {
                                                    'NUEVA' => 'bg-primary',
                                                    'EN_PROCESO' => 'bg-info text-dark',
                                                    'ENTREGADA_REVISION' => 'bg-warning text-dark',
                                                    'APROBADA' => 'bg-success',
                                                    'CORRECCION' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($act['current_status']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Accesos Rápidos de Administración -->
<div class="row g-3">
    <div class="col-md-3">
        <a href="/contracts/final" class="card text-decoration-none border-0 shadow-sm p-3 text-center h-100 hover-shadow transition">
            <i class="bi bi-archive-fill text-success fs-1 mb-2"></i>
            <h6 class="fw-bold text-dark mb-1">Contratos Finales</h6>
            <small class="text-muted">Descargar PDFs y consultar repositorio</small>
        </a>
    </div>
    <div class="col-md-3">
        <a href="/realtors" class="card text-decoration-none border-0 shadow-sm p-3 text-center h-100 hover-shadow transition">
            <i class="bi bi-building-add text-primary fs-1 mb-2"></i>
            <h6 class="fw-bold text-dark mb-1">+ Realtors Miami</h6>
            <small class="text-muted">Gestión de agentes y brokerages</small>
        </a>
    </div>
    <div class="col-md-3">
        <a href="/team" class="card text-decoration-none border-0 shadow-sm p-3 text-center h-100 hover-shadow transition">
            <i class="bi bi-person-gear text-dark fs-1 mb-2"></i>
            <h6 class="fw-bold text-dark mb-1">+ Equipo & Roles</h6>
            <small class="text-muted">Asignar roles y especialidades granulares</small>
        </a>
    </div>
    <div class="col-md-3">
        <a href="/reports" class="card text-decoration-none border-0 shadow-sm p-3 text-center h-100 hover-shadow transition">
            <i class="bi bi-file-earmark-spreadsheet-fill text-success fs-1 mb-2"></i>
            <h6 class="fw-bold text-dark mb-1">Reportes Ejecutivos</h6>
            <small class="text-muted">Exportar a Excel y PDF (Semanal/Mensual)</small>
        </a>
    </div>
</div>
