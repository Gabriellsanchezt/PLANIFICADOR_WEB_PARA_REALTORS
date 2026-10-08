<div class="mb-4">
    <h2 class="fw-bold mb-1"><i class="bi bi-play-circle-fill text-success me-2"></i>Mis Tareas Audiovisuales</h2>
    <p class="text-muted mb-0">Gestión de entregables, subida de links a Frame.io o Google Drive y revisión de notas del Jefe</p>
</div>

<?php if (empty($tasks)): ?>
    <div class="card border-0 shadow-sm p-5 text-center">
        <i class="bi bi-check2-circle display-4 text-success mb-3"></i>
        <h4 class="fw-bold">No tienes tareas asignadas pendientes</h4>
        <p class="text-muted">¡Excelente trabajo! Cuando la Dirección General canalice una nueva tarea a tu perfil, aparecerá listada en este panel.</p>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($tasks as $t): ?>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100 <?= ($t['current_status'] === 'CORRECCION') ? 'border-start border-danger border-4' : '' ?>">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <span class="badge bg-secondary font-monospace"><?= htmlspecialchars($t['activity_type']) ?></span>
                        <div>
                            <?php
                                $statusBadge = match($t['current_status']) {
                                    'NUEVA' => 'bg-primary',
                                    'EN_PROCESO' => 'bg-info text-dark',
                                    'ENTREGADA_REVISION' => 'bg-warning text-dark',
                                    'APROBADA' => 'bg-success',
                                    'CORRECCION' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            ?>
                            <span class="badge <?= $statusBadge ?> px-2 py-1"><?= htmlspecialchars($t['current_status']) ?></span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($t['title']) ?></h5>

                        <div class="mb-3 p-2 bg-light rounded small border">
                            <div class="row">
                                <div class="col-6">
                                    <span class="text-muted">Realtor:</span> <strong><?= htmlspecialchars($t['company_name']) ?></strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Zona:</span> <strong><?= htmlspecialchars($t['miami_zone']) ?></strong>
                                </div>
                                <div class="col-6 mt-1">
                                    <span class="text-muted">Prioridad:</span> <span class="badge bg-secondary"><?= htmlspecialchars($t['priority']) ?></span>
                                </div>
                                <div class="col-6 mt-1">
                                    <span class="text-muted">Fecha Límite:</span> <strong><?= htmlspecialchars($t['scheduled_date']) ?></strong>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($t['description'])): ?>
                            <div class="mb-3 small">
                                <strong>Brief / Instrucciones:</strong>
                                <p class="text-secondary mb-0"><?= nl2br(htmlspecialchars($t['description'])) ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- ALERTA SI HAY CORRECCIONES DEL JEFE -->
                        <?php if ($t['current_status'] === 'CORRECCION' && !empty($t['last_deliverable']['review_notes'])): ?>
                            <div class="alert alert-danger p-3 small mb-3">
                                <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Corrección Solicitada por el Jefe:</strong>
                                <div class="mt-1 fst-italic">"<?= htmlspecialchars($t['last_deliverable']['review_notes']) ?>"</div>
                                <div class="text-muted mt-1" style="font-size: 0.75rem;">Fecha de revisión: <?= htmlspecialchars($t['last_deliverable']['reviewed_at']) ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- ÚLTIMO ENTREGABLE SUBIDO -->
                        <?php if (!empty($t['last_deliverable'])): ?>
                            <div class="p-2 border rounded bg-white small mb-3">
                                <span class="text-muted">Último entregable enviado:</span>
                                <a href="<?= htmlspecialchars($t['last_deliverable']['deliverable_url']) ?>" target="_blank" class="fw-bold d-block text-truncate">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> <?= htmlspecialchars($t['last_deliverable']['deliverable_url']) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-footer bg-light p-3 border-top d-flex gap-2 justify-content-between align-items-center">
                        <?php if ($t['current_status'] === 'NUEVA'): ?>
                            <form action="/activities/start-task" method="POST">
                                <input type="hidden" name="activity_id" value="<?= $t['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-play me-1"></i> Iniciar Tarea
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="small text-muted"><i class="bi bi-clock me-1"></i>En seguimiento</span>
                        <?php endif; ?>

                        <button type="button" class="btn btn-sm btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalDeliverable<?= $t['id'] ?>">
                            <i class="bi bi-upload me-1"></i> Subir Entregable
                        </button>
                    </div>
                </div>
            </div>

            <!-- MODAL SUBIR ENTREGABLE -->
            <div class="modal fade" id="modalDeliverable<?= $t['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="/activities/submit-deliverable" method="POST">
                            <input type="hidden" name="activity_id" value="<?= $t['id'] ?>">

                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title fw-bold"><i class="bi bi-cloud-arrow-up me-2"></i>Subir Entregable Audiovisual</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Plataforma de Almacenamiento *</label>
                                    <select name="deliverable_type" class="form-select" required>
                                        <option value="FRAME_IO">Frame.io (Revisión de Video)</option>
                                        <option value="GOOGLE_DRIVE">Google Drive</option>
                                        <option value="DROPBOX">Dropbox</option>
                                        <option value="OTRO">Otro Link Externo</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">URL del Entregable *</label>
                                    <input type="url" name="deliverable_url" class="form-control" required placeholder="https://frame.io/v/xyz o https://drive.google.com/...">
                                    <div class="form-text">Asegúrate de que los permisos de enlace permitan visualización o descarga.</div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label small fw-semibold">Notas para el Jefe (Opcional)</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Ej: Versión v2 con corrección de audio y recorte de 3 segundos en intro..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary fw-semibold">Enviar a Revisión del Jefe</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    </div>
<?php endif; ?>
