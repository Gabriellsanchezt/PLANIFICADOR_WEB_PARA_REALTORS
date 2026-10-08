<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-building text-primary me-2"></i>Gestión de Clientes Realtors (Miami)</h2>
        <p class="text-muted mb-0">Directorio de agentes inmobiliarios, agencias y brokerages asociados en el Condado de Miami-Dade</p>
    </div>
    <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalNewRealtor">
        <i class="bi bi-plus-lg me-1"></i> + Nuevo Realtor
    </button>
</div>

<!-- Tabla de Realtors -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($realtors)): ?>
            <div class="text-center py-5 text-muted">
                No hay clientes realtors registrados actualmente.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Inmobiliaria / Brokerage</th>
                            <th>Persona de Contacto</th>
                            <th>Zona de Miami</th>
                            <th>Contacto Directo</th>
                            <th>Redes Sociales</th>
                            <th>Contratos Activos</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($realtors as $r): ?>
                            <?php
                                $social = json_decode($r['social_handles'] ?? '{}', true) ?: [];
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($r['company_name']) ?></strong>
                                    <div class="small text-muted font-monospace">ID: #<?= $r['id'] ?></div>
                                </td>
                                <td><?= htmlspecialchars($r['contact_person']) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($r['miami_zone']) ?></span>
                                </td>
                                <td>
                                    <div class="small"><i class="bi bi-telephone me-1 text-muted"></i><?= htmlspecialchars($r['phone']) ?></div>
                                    <div class="small"><i class="bi bi-envelope me-1 text-muted"></i><?= htmlspecialchars($r['email']) ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($social['instagram'])): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-instagram me-1"></i><?= htmlspecialchars($social['instagram']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($social['tiktok'])): ?>
                                        <span class="badge bg-dark-subtle text-dark border"><i class="bi bi-tiktok me-1"></i><?= htmlspecialchars($social['tiktok']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <?= (int)$r['active_contracts_count'] ?> Aprobados
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalEditRealtor<?= $r['id'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- MODAL EDITAR REALTOR -->
                            <div class="modal fade" id="modalEditRealtor<?= $r['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="/realtors/update" method="POST">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">

                                            <div class="modal-header bg-dark text-white">
                                                <h5 class="modal-title fw-bold">Editar Cliente Realtor</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Nombre de la Inmobiliaria / Brokerage *</label>
                                                    <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($r['company_name']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Persona de Contacto *</label>
                                                    <input type="text" name="contact_person" class="form-control" value="<?= htmlspecialchars($r['contact_person']) ?>" required>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold">Teléfono *</label>
                                                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($r['phone']) ?>" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold">Email *</label>
                                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($r['email']) ?>" required>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Zona Principal de Miami *</label>
                                                    <select name="miami_zone" class="form-select" required>
                                                        <?php foreach ($zones as $z): ?>
                                                            <option value="<?= htmlspecialchars($z) ?>" <?= ($r['miami_zone'] === $z) ? 'selected' : '' ?>><?= htmlspecialchars($z) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold">Instagram Handle</label>
                                                        <input type="text" name="instagram" class="form-control" value="<?= htmlspecialchars($social['instagram'] ?? '') ?>">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold">TikTok Handle</label>
                                                        <input type="text" name="tiktok" class="form-control" value="<?= htmlspecialchars($social['tiktok'] ?? '') ?>">
                                                    </div>
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label small fw-semibold">Estado de la Cuenta</label>
                                                    <select name="status" class="form-select">
                                                        <option value="ACTIVO" <?= ($r['status'] === 'ACTIVO') ? 'selected' : '' ?>>Activo</option>
                                                        <option value="INACTIVO" <?= ($r['status'] === 'INACTIVO') ? 'selected' : '' ?>>Inactivo</option>
                                                        <option value="PROSPECTO" <?= ($r['status'] === 'PROSPECTO') ? 'selected' : '' ?>>Prospecto</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary fw-semibold">Guardar Cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL NUEVO REALTOR -->
<div class="modal fade" id="modalNewRealtor" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/realtors/store" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-building-add me-2"></i>Registrar Nuevo Cliente Realtor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Inmobiliaria / Brokerage *</label>
                        <input type="text" name="company_name" class="form-control" required placeholder="Ej: Douglas Elliman Real Estate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nombre del Agente / Contacto *</label>
                        <input type="text" name="contact_person" class="form-control" required placeholder="Ej: Jonathan Meyer">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Teléfono *</label>
                            <input type="text" name="phone" class="form-control" required placeholder="+1 (305) 555-0100">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Email Corporativo *</label>
                            <input type="email" name="email" class="form-control" required placeholder="agente@elliman.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Zona Primaria en Miami *</label>
                        <select name="miami_zone" class="form-select" required>
                            <?php foreach ($zones as $z): ?>
                                <option value="<?= htmlspecialchars($z) ?>"><?= htmlspecialchars($z) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-0">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Instagram</label>
                            <input type="text" name="instagram" class="form-control" placeholder="@realtor_miami">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">TikTok</label>
                            <input type="text" name="tiktok" class="form-control" placeholder="@realtor_miami">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Registrar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>
