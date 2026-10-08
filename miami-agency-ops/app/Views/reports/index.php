<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Reportes Ejecutivos de Dirección</h2>
        <p class="text-muted mb-0">Métricas consolidadas de producción audiovisual, facturación de contratos y rendimiento</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/reports/export-excel?period=<?= $currentPeriod ?>" class="btn btn-outline-success fw-semibold">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Exportar a Excel (CSV)
        </a>
        <button onclick="window.print()" class="btn btn-dark fw-semibold">
            <i class="bi bi-printer me-1"></i> Imprimir / Generar PDF
        </button>
    </div>
</div>

<!-- Selector de Períodos -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-2 d-flex gap-2">
        <a href="/reports?period=semanal" class="btn <?= ($currentPeriod === 'semanal') ? 'btn-primary' : 'btn-light' ?> flex-fill fw-semibold">
            <i class="bi bi-calendar-week me-1"></i> Período Semanal (Últimos 7 días)
        </a>
        <a href="/reports?period=quincenal" class="btn <?= ($currentPeriod === 'quincenal') ? 'btn-primary' : 'btn-light' ?> flex-fill fw-semibold">
            <i class="bi bi-calendar2-range me-1"></i> Período Quincenal (Últimos 15 días)
        </a>
        <a href="/reports?period=mensual" class="btn <?= ($currentPeriod === 'mensual') ? 'btn-primary' : 'btn-light' ?> flex-fill fw-semibold">
            <i class="bi bi-calendar-month me-1"></i> Período Mensual (Últimos 30 días)
        </a>
    </div>
</div>

<!-- Resumen Financiero de Contratos Aprobados -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-success border-4 h-100">
            <div class="card-body">
                <span class="text-muted small fw-semibold text-uppercase">Facturación Recurrente Aprobada</span>
                <h2 class="fw-bold mt-2 mb-0 text-success">$<?= number_format($report['financial']['total_monthly_revenue'], 2) ?> USD</h2>
                <small class="text-muted mt-1 d-block"><i class="bi bi-arrow-up-right text-success"></i> Contratos validados en el período</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-primary border-4 h-100">
            <div class="card-body">
                <span class="text-muted small fw-semibold text-uppercase">Contratos Aprobados</span>
                <h2 class="fw-bold mt-2 mb-0 text-primary"><?= $report['financial']['total_approved_contracts'] ?></h2>
                <small class="text-muted mt-1 d-block">Registrados en Repositorio Oficial</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-dark border-4 h-100">
            <div class="card-body">
                <span class="text-muted small fw-semibold text-uppercase">Ticket Promedio por Contrato</span>
                <h2 class="fw-bold mt-2 mb-0 text-dark">$<?= number_format($report['financial']['avg_contract_ticket'], 2) ?> USD</h2>
                <small class="text-muted mt-1 d-block">Promedio mensual por Realtor</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Facturación por Zona de Miami -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Facturación por Zona de Miami</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($report['financial']['by_zone'])): ?>
                    <div class="text-center py-4 text-muted">Sin datos de facturación en este período.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase">
                                <tr>
                                    <th>Zona de Miami</th>
                                    <th>Contratos</th>
                                    <th class="text-end">Ingresos Mensuales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($report['financial']['by_zone'] as $z): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($z['miami_zone']) ?></strong></td>
                                        <td><span class="badge bg-secondary"><?= $z['contract_count'] ?></span></td>
                                        <td class="text-end fw-bold text-success">$<?= number_format($z['zone_revenue'], 2) ?> USD</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Resumen del Flujo Audiovisual -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-camera-reels-fill text-primary me-2"></i>Rendimiento del Flujo Audiovisual</h5>
            </div>
            <div class="card-body p-4">
                <div class="row text-center g-3 mb-3">
                    <div class="col-4">
                        <div class="p-3 bg-light rounded border">
                            <div class="small text-muted text-uppercase">Total Tareas</div>
                            <h3 class="fw-bold mb-0 text-dark"><?= $report['operations']['total_tasks'] ?></h3>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-success-subtle rounded border border-success-subtle">
                            <div class="small text-success text-uppercase">Aprobadas</div>
                            <h3 class="fw-bold mb-0 text-success"><?= $report['operations']['completed_tasks'] ?></h3>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-danger-subtle rounded border border-danger-subtle">
                            <div class="small text-danger text-uppercase">En Corrección</div>
                            <h3 class="fw-bold mb-0 text-danger"><?= $report['operations']['correction_tasks'] ?></h3>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold small text-muted text-uppercase mb-2">Desglose por Tipo de Producción:</h6>
                <div class="list-group list-group-flush small">
                    <?php foreach ($report['operations']['by_type'] as $t): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="bi bi-circle-fill text-primary me-2" style="font-size: 0.5rem;"></i><?= htmlspecialchars($t['activity_type']) ?></span>
                            <div>
                                <span class="badge bg-light text-dark border"><?= $t['total_count'] ?> totales</span>
                                <span class="badge bg-success"><?= $t['approved_count'] ?> culminadas</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Desempeño del Personal Creativo -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill text-dark me-2"></i>Productividad por Especialista Audiovisual</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($report['operations']['staff'])): ?>
            <div class="text-center py-4 text-muted">No hay registros de tareas asignadas para el personal en este rango.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Especialista</th>
                            <th>Tareas Asignadas</th>
                            <th>Entregas Aprobadas</th>
                            <th>Solicitudes de Corrección</th>
                            <th class="text-end">Tasa de Efectividad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['operations']['staff'] as $st): ?>
                            <?php
                                $rate = $st['assigned_tasks'] > 0 ? round(($st['completed_tasks'] / $st['assigned_tasks']) * 100) : 0;
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($st['full_name']) ?></strong></td>
                                <td><?= $st['assigned_tasks'] ?></td>
                                <td><span class="badge bg-success"><?= $st['completed_tasks'] ?></span></td>
                                <td><span class="badge bg-danger"><?= $st['rework_tasks'] ?></span></td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <div class="progress" style="width: 100px; height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $rate ?>%;"></div>
                                        </div>
                                        <span class="fw-bold small"><?= $rate ?>%</span>
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
