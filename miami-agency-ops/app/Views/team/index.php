<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-people-fill text-dark me-2"></i>Gestión de Equipo & Roles Granulares</h2>
        <p class="text-muted mb-0">Control de acceso basado en roles (RBAC) y asignación de especialidades operativas</p>
    </div>
    <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalNewTeam">
        <i class="bi bi-person-plus-fill me-1"></i> + Nuevo Miembro del Equipo
    </button>
</div>

<!-- Tabla de Personal -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Nombre y Cargo</th>
                        <th>Email de Acceso</th>
                        <th>Teléfono</th>
                        <th>Rol Principal</th>
                        <th>Especialidades / Módulos Permitidos</th>
                        <th>Estado</th>
                        <th class="text-end">Modificar Permisos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($emp['full_name']) ?></strong>
                                <div class="small text-muted font-monospace">Emp ID: #<?= $emp['employee_id'] ?></div>
                            </td>
                            <td><?= htmlspecialchars($emp['email']) ?></td>
                            <td><?= htmlspecialchars($emp['phone'] ?: '-') ?></td>
                            <td>
                                <?php if ($emp['role_name'] === 'JEFE_ADMIN'): ?>
                                    <span class="badge bg-danger">JEFE_ADMIN</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">EMPLEADO</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($emp['role_name'] === 'JEFE_ADMIN'): ?>
                                    <span class="badge bg-dark">ACCESO TOTAL (SUPER-ADMIN)</span>
                                <?php elseif (empty($emp['specialties'])): ?>
                                    <span class="text-muted small">Sin especialidad asignada</span>
                                <?php else: ?>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($emp['specialties'] as $sp): ?>
                                            <?php
                                                $spBadge = match($sp['code']) {
                                                    'CREADOR_CONTRATOS' => 'bg-primary',
                                                    'EDITOR_VIDEO' => 'bg-success',
                                                    'DISENADOR_CARRUSELES' => 'bg-info text-dark',
                                                    'CAMAROGRAFO' => 'bg-dark',
                                                    'COMMUNITY_MANAGER' => 'bg-warning text-dark',
                                                    default => 'bg-secondary'
                                                };
                                            ?>
                                            <span class="badge <?= $spBadge ?>" title="<?= htmlspecialchars($sp['name']) ?>">
                                                <?= htmlspecialchars($sp['code']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success-subtle"><?= htmlspecialchars($emp['user_status']) ?></span>
                            </td>
                            <td class="text-end">
                                <?php if ($emp['role_name'] !== 'JEFE_ADMIN'): ?>
                                    <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalPerms<?= $emp['user_id'] ?>">
                                        <i class="bi bi-shield-lock me-1"></i> Especialidades
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted small">Inalterable</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- MODAL EDITAR ESPECIALIDADES -->
                        <?php if ($emp['role_name'] !== 'JEFE_ADMIN'): ?>
                            <div class="modal fade" id="modalPerms<?= $emp['user_id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="/team/update-specialties" method="POST">
                                            <input type="hidden" name="user_id" value="<?= $emp['user_id'] ?>">

                                            <div class="modal-header bg-dark text-white">
                                                <h5 class="modal-title fw-bold">Especialidades de <?= htmlspecialchars($emp['full_name']) ?></h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <p class="small text-muted mb-3">Marque las especialidades que determinan a cuáles paneles y tareas tendrá acceso el colaborador:</p>

                                                <?php
                                                    $userSpecIds = array_column($emp['specialties'], 'id');
                                                ?>

                                                <?php foreach ($specialties as $spec): ?>
                                                    <div class="form-check p-3 bg-light rounded border mb-2">
                                                        <input class="form-check-input" type="checkbox" name="specialties[]" value="<?= $spec['id'] ?>" id="sp_<?= $emp['user_id'] ?>_<?= $spec['id'] ?>" <?= in_array($spec['id'], $userSpecIds) ? 'checked' : '' ?>>
                                                        <label class="form-check-label w-100" for="sp_<?= $emp['user_id'] ?>_<?= $spec['id'] ?>">
                                                            <strong><?= htmlspecialchars($spec['name']) ?></strong>
                                                            <div class="font-monospace small text-primary"><?= htmlspecialchars($spec['code']) ?></div>
                                                            <small class="text-muted d-block"><?= htmlspecialchars($spec['description']) ?></small>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary fw-semibold">Actualizar Especialidades</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL NUEVO MIEMBRO -->
<div class="modal fade" id="modalNewTeam" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/team/store" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2"></i>Registrar Miembro del Equipo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nombre Completo *</label>
                            <input type="text" name="full_name" class="form-control" required placeholder="Ej: Santiago Navarro">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Teléfono *</label>
                            <input type="text" name="phone" class="form-control" placeholder="+1 (305) 555-0188">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Correo Electrónico (Login) *</label>
                            <input type="email" name="email" class="form-control" required placeholder="santiago@miamiagency.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Contraseña Inicial *</label>
                            <input type="password" name="password" class="form-control" required placeholder="••••••••">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Rol del Sistema *</label>
                        <select name="role_id" class="form-select" required>
                            <?php foreach ($roles as $rl): ?>
                                <option value="<?= $rl['id'] ?>" <?= ($rl['name'] === 'EMPLEADO') ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($rl['name']) ?> - <?= htmlspecialchars($rl['description']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Especialidades Profesionales a Asignar</label>
                        <div class="row g-2">
                            <?php foreach ($specialties as $sp): ?>
                                <div class="col-md-6">
                                    <div class="p-2 border rounded bg-light">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="specialties[]" value="<?= $sp['id'] ?>" id="new_sp_<?= $sp['id'] ?>">
                                            <label class="form-check-label small" for="new_sp_<?= $sp['id'] ?>">
                                                <strong><?= htmlspecialchars($sp['name']) ?></strong><br>
                                                <span class="font-monospace text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($sp['code']) ?></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Crear Usuario y Ficha Laboral</button>
                </div>
            </form>
        </div>
    </div>
</div>
