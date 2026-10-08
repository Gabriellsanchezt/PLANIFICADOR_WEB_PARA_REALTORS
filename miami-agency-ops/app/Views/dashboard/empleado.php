<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1">¡Hola, <?= htmlspecialchars($user['full_name']) ?>!</h2>
            <p class="text-muted mb-0">Espacio de trabajo operativo según tus especialidades profesionales asignadas</p>
        </div>
        <div class="d-flex gap-2">
            <?php if (in_array('CREADOR_CONTRATOS', $user['specialties'], true)): ?>
                <a href="/contracts/create" class="btn btn-primary fw-semibold">
                    <i class="bi bi-file-earmark-plus-fill me-1"></i> Generador de Contratos
                </a>
            <?php endif; ?>
            <?php if (\Config\Auth::canProduceMedia()): ?>
                <a href="/activities/my-tasks" class="btn btn-success fw-semibold">
                    <i class="bi bi-play-circle-fill me-1"></i> Mis Tareas Audiovisuales
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Tarjeta de Perfil y Especialidades Activas -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="fw-bold mb-1 text-dark">Especialidades & Habilitaciones Activas</h5>
                <p class="text-muted small mb-3">Tus permisos en el sistema están configurados en base a tus roles laborales:</p>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($user['specialties_details'] as $spec): ?>
                        <div class="badge bg-light text-dark border p-2 text-start">
                            <i class="bi bi-shield-check text-success me-1"></i>
                            <strong><?= htmlspecialchars($spec['name']) ?></strong>
                            <div class="small text-muted font-monospace"><?= htmlspecialchars($spec['code']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="p-3 bg-light rounded text-center border">
                    <div class="small text-muted">Correo Corporativo</div>
                    <div class="fw-bold text-dark"><?= htmlspecialchars($user['email']) ?></div>
                    <span class="badge bg-success mt-2">USUARIO ACTIVO</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Módulo 1: Si tiene permiso CREADOR_CONTRATOS -->
<?php if (in_array('CREADOR_CONTRATOS', $user['specialties'], true)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-file-earmark-text-fill text-primary me-2"></i>Mis Borradores de Contratos en Edición
            </h5>
            <a href="/contracts/create" class="btn btn-sm btn-outline-primary">+ Crear Nuevo Borrador</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($myDrafts)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-file-earmark-check display-6 d-block mb-2 text-primary"></i>
                    No tienes borradores pendientes. Recuerda que al enviar un contrato a revisión del Jefe, pasa automáticamente a la bandeja de Dirección.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Código</th>
                                <th>Realtor</th>
                                <th>Zona Miami</th>
                                <th>Honorarios</th>
                                <th>Estado</th>
                                <th>Observaciones</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myDrafts as $d): ?>
                                <tr>
                                    <td class="fw-bold font-monospace"><?= htmlspecialchars($d['contract_code']) ?></td>
                                    <td><?= htmlspecialchars($d['company_name']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($d['miami_zone']) ?></span></td>
                                    <td class="fw-bold text-success">$<?= number_format($d['monthly_fee'], 2) ?></td>
                                    <td>
                                        <?php if ($d['status'] === 'BORRADOR'): ?>
                                            <span class="badge bg-secondary">BORRADOR</span>
                                        <?php elseif ($d['status'] === 'RECHAZADO'): ?>
                                            <span class="badge bg-danger">DEVUELTO CON CORRECCIÓN</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-danger">
                                        <?= !empty($d['feedback_notes']) ? htmlspecialchars($d['feedback_notes']) : '<span class="text-muted">-</span>' ?>
                                    </td>
                                    <td class="text-end">
                                        <form action="/contracts/submit-existing" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas enviar este borrador al Jefe? Ya no podrás editarlo.')">
                                            <input type="hidden" name="contract_id" value="<?= $d['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-send-fill me-1"></i> Enviar a Revisión
                                            </button>
                                        </form>
                                        <a href="/contracts/view?id=<?= $d['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye"></i>
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
<?php endif; ?>

<!-- Módulo 2: Si tiene especialidad audiovisual creativa -->
<?php if (\Config\Auth::canProduceMedia()): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-camera-reels-fill text-success me-2"></i>Mis Tareas Audiovisuales Asignadas
            </h5>
            <a href="/activities/my-tasks" class="btn btn-sm btn-outline-success">Ver Panel de Entregas &rarr;</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($myTasks)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-check-circle display-6 d-block mb-2 text-success"></i>
                    No tienes tareas pendientes por entregar en este momento.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Tarea Audiovisual</th>
                                <th>Realtor / Ubicación</th>
                                <th>Prioridad</th>
                                <th>Fecha Límite</th>
                                <th>Estado Actual</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myTasks as $t): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($t['title']) ?></strong><br>
                                        <small class="text-muted"><i class="bi bi-tag"></i> <?= htmlspecialchars($t['activity_type']) ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($t['company_name']) ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($t['miami_zone']) ?></small>
                                    </td>
                                    <td>
                                        <?php
                                            $prioClass = match($t['priority']) {
                                                'URGENTE' => 'bg-danger',
                                                'ALTA' => 'bg-warning text-dark',
                                                default => 'bg-info text-dark'
                                            };
                                        ?>
                                        <span class="badge <?= $prioClass ?>"><?= htmlspecialchars($t['priority']) ?></span>
                                    </td>
                                    <td><i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($t['scheduled_date']) ?></td>
                                    <td>
                                        <?php
                                            $stClass = match($t['current_status']) {
                                                'NUEVA' => 'bg-primary',
                                                'EN_PROCESO' => 'bg-info text-dark',
                                                'ENTREGADA_REVISION' => 'bg-warning text-dark',
                                                'APROBADA' => 'bg-success',
                                                'CORRECCION' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                        <span class="badge <?= $stClass ?>"><?= htmlspecialchars($t['current_status']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="/activities/my-tasks" class="btn btn-sm btn-primary">
                                            Gestionar Entrega
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
<?php endif; ?>
