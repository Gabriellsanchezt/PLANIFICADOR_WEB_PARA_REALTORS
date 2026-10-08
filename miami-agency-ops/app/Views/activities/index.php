<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-kanban text-primary me-2"></i>Tablero de Producción Audiovisual</h2>
        <p class="text-muted mb-0">Supervisión, canalización de rodajes y revisión de entregables creativos</p>
    </div>
    <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalNewActivity">
        <i class="bi bi-plus-lg me-1"></i> Asignar Nueva Tarea
    </button>
</div>

<!-- Filtros Rápidos por Estado -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="/activities" class="btn btn-sm <?= empty($activeFilter) ? 'btn-dark' : 'btn-outline-dark' ?>">
        Todas (<?= $counts['total'] ?>)
    </a>
    <a href="/activities?status=NUEVA" class="btn btn-sm <?= ($activeFilter === 'NUEVA') ? 'btn-primary' : 'btn-outline-primary' ?>">
        Nuevas (<?= $counts['nuevas'] ?>)
    </a>
    <a href="/activities?status=EN_PROCESO" class="btn btn-sm <?= ($activeFilter === 'EN_PROCESO') ? 'btn-info' : 'btn-outline-info' ?>">
        En Proceso (<?= $counts['canalizadas'] ?>)
    </a>
    <a href="/activities?status=ENTREGADA_REVISION" class="btn btn-sm <?= ($activeFilter === 'ENTREGADA_REVISION') ? 'btn-warning' : 'btn-outline-warning' ?>">
        Por Aprobar (<?= $counts['por_aprobar'] ?>)
    </a>
    <a href="/activities?status=CORRECCION" class="btn btn-sm <?= ($activeFilter === 'CORRECCION') ? 'btn-danger' : 'btn-outline-danger' ?>">
        En Corrección (<?= $counts['correccion'] ?>)
    </a>
    <a href="/activities?status=APROBADA" class="btn btn-sm <?= ($activeFilter === 'APROBADA') ? 'btn-success' : 'btn-outline-success' ?>">
        Culminadas (<?= $counts['culminadas'] ?>)
    </a>
</div>

<!-- Tabla del Tablero Audiovisual -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($activities)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-camera-reels display-5 d-block mb-2"></i>
                No se encontraron actividades con el filtro seleccionado.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Prioridad</th>
                            <th>Título de la Tarea</th>
                            <th>Cliente Realtor</th>
                            <th>Especialista Asignado</th>
                            <th>Fecha Límite</th>
                            <th>Estado Actual</th>
                            <th>Entregables</th>
                            <th class="text-end">Acción de Supervisión</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activities as $act): ?>
                            <tr>
                                <td>
                                    <?php
                                        $prioBadge = match($act['priority']) {
                                            'URGENTE' => 'bg-danger',
                                            'ALTA' => 'bg-warning text-dark',
                                            default => 'bg-secondary'
                                        };
                                    ?>
                                    <span class="badge <?= $prioBadge ?>"><?= htmlspecialchars($act['priority']) ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($act['title']) ?></strong>
                                    <div class="small text-muted"><i class="bi bi-tag me-1"></i><?= htmlspecialchars($act['activity_type']) ?></div>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($act['company_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($act['miami_zone']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><i class="bi bi-person me-1"></i><?= htmlspecialchars($act['employee_name']) ?></span>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($act['scheduled_date']) ?></small>
                                </td>
                                <td>
                                    <?php
                                        $stBadge = match($act['current_status']) {
                                            'NUEVA' => 'bg-primary',
                                            'EN_PROCESO' => 'bg-info text-dark',
                                            'ENTREGADA_REVISION' => 'bg-warning text-dark',
                                            'APROBADA' => 'bg-success',
                                            'CORRECCION' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    ?>
                                    <span class="badge <?= $stBadge ?>"><?= htmlspecialchars($act['current_status']) ?></span>
                                </td>
                                <td>
                                    <?php if ($act['deliverables_count'] > 0): ?>
                                        <span class="badge bg-dark"><i class="bi bi-link-45deg me-1"></i><?= $act['deliverables_count'] ?> subidos</span>
                                    <?php else: ?>
                                        <small class="text-muted">Ninguno</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="inspectActivity(<?= $act['id'] ?>)">
                                        <i class="bi bi-eye me-1"></i> Inspeccionar / Evaluar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL ASIGNAR NUEVA TAREA -->
<div class="modal fade" id="modalNewActivity" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/activities/store" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Asignar Nueva Tarea Audiovisual</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Título de la Tarea *</label>
                        <input type="text" name="title" class="form-control" required placeholder="Ej: Rodaje Dron Mansión Sunny Isles o Edición Reel Walkthrough Brickell">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Cliente Realtor *</label>
                            <select name="realtor_id" class="form-select" required>
                                <option value="">-- Seleccionar Realtor --</option>
                                <?php foreach ($realtors as $r): ?>
                                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['company_name']) ?> (<?= htmlspecialchars($r['miami_zone']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Empleado Creativo Asignado *</label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">-- Seleccionar Empleado --</option>
                                <?php foreach ($employees as $emp): ?>
                                    <?php if ($emp['role_name'] === 'EMPLEADO'): ?>
                                        <option value="<?= $emp['employee_id'] ?>">
                                            <?= htmlspecialchars($emp['full_name']) ?>
                                            (<?= implode(', ', array_column($emp['specialties'], 'code')) ?>)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tipo de Actividad *</label>
                            <select name="activity_type" class="form-select" required>
                                <option value="EDICION_VIDEO">Edición de Video</option>
                                <option value="CARRUSEL_FOTO">Diseño de Carrusel / Fotos</option>
                                <option value="RODAJE_CAMARA">Rodaje en Locación / Dron</option>
                                <option value="COMMUNITY_MANAGEMENT">Community Management / Redes</option>
                                <option value="OTRO">Otro Servicio</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Prioridad *</label>
                            <select name="priority" class="form-select" required>
                                <option value="BAJA">Baja</option>
                                <option value="MEDIA" selected>Media</option>
                                <option value="ALTA">Alta</option>
                                <option value="URGENTE">Urgente (Entrega 24h)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Fecha de Entrega Programada *</label>
                            <input type="date" name="scheduled_date" class="form-control" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Vincular a Contrato Aprobado (Opcional)</label>
                        <select name="contract_id" class="form-select">
                            <option value="">-- Sin contrato vinculado --</option>
                            <?php foreach ($contracts as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['contract_code']) ?> - <?= htmlspecialchars($c['company_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Instrucciones y Brief Creativo</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Detalles de edición, enlaces de audio de referencia, horarios de rodaje..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Crear y Canalizar Tarea</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EVALUAR ENTREGABLES & DETALLES -->
<div class="modal fade" id="modalInspectActivity" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="inspectTitle"><i class="bi bi-camera-video me-2"></i>Inspección de Tarea</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="inspectBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function inspectActivity(id) {
    const modal = new bootstrap.Modal(document.getElementById('modalInspectActivity'));
    modal.show();

    fetch('/activities/details-ajax?id=' + id)
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            document.getElementById('inspectBody').innerHTML = '<div class="alert alert-danger">Error al cargar actividad</div>';
            return;
        }

        const act = data.activity;
        document.getElementById('inspectTitle').innerText = act.title;

        let deliverablesHtml = '';
        if (act.deliverables && act.deliverables.length > 0) {
            deliverablesHtml = '<h6 class="fw-bold mt-4 mb-2">Entregables Subidos por el Creativo:</h6>';
            act.deliverables.forEach(deliv => {
                deliverablesHtml += `
                    <div class="p-3 bg-light rounded border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary">${deliv.deliverable_type}</span>
                            <small class="text-muted">${deliv.submitted_at}</small>
                        </div>
                        <div class="mb-2">
                            <strong>URL Entregable:</strong>
                            <a href="${deliv.deliverable_url}" target="_blank" class="fw-bold text-decoration-none">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Abrir en Frame.io / Drive
                            </a>
                        </div>
                        <div class="small text-muted mb-2"><strong>Notas del Creativo:</strong> ${deliv.notes || 'Sin notas'}</div>

                        <!-- Formulario de Evaluación para el Jefe -->
                        <form action="/activities/review-deliverable" method="POST" class="mt-3 p-2 bg-white rounded border">
                            <input type="hidden" name="deliverable_id" value="${deliv.id}">
                            <input type="hidden" name="activity_id" value="${act.id}">

                            <label class="form-label small fw-semibold">Decisión del Jefe:</label>
                            <div class="d-flex gap-2 mb-2">
                                <select name="review_status" class="form-select form-select-sm" style="max-width: 200px;">
                                    <option value="APROBADO">Aprobar Entregable</option>
                                    <option value="REQUIERE_CAMBIOS">Solicitar Correcciones</option>
                                </select>
                                <input type="text" name="review_notes" class="form-control form-control-sm" required placeholder="Observaciones / Feedback...">
                                <button type="submit" class="btn btn-sm btn-dark">Guardar Calificación</button>
                            </div>
                        </form>
                    </div>
                `;
            });
        } else {
            deliverablesHtml = '<div class="alert alert-light border mt-3 text-muted">Aún no se han subido links de entregables para esta tarea.</div>';
        }

        document.getElementById('inspectBody').innerHTML = `
            <div class="row g-2 mb-3">
                <div class="col-md-6"><strong>Cliente Realtor:</strong> ${act.company_name} (${act.miami_zone})</div>
                <div class="col-md-6"><strong>Especialista:</strong> ${act.employee_name} (${act.employee_phone || ''})</div>
                <div class="col-md-6"><strong>Prioridad:</strong> <span class="badge bg-secondary">${act.priority}</span></div>
                <div class="col-md-6"><strong>Estado Actual:</strong> <span class="badge bg-dark">${act.current_status}</span></div>
            </div>
            <div class="p-3 bg-light rounded border mb-2">
                <strong>Descripción / Brief:</strong><br>
                ${act.description || 'Sin instrucciones adicionales'}
            </div>
            ${deliverablesHtml}
        `;
    });
}
</script>
