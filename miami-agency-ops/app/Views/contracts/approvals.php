<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-file-earmark-check-fill text-warning me-2"></i>Módulo de Aprobación de Contratos</h2>
        <p class="text-muted mb-0">Revisión de borradores remitidos por el personal autorizado. Apruebe y archive o devuelva con observaciones.</p>
    </div>
    <a href="/contracts/final" class="btn btn-outline-success">
        <i class="bi bi-archive-fill me-1"></i> Ir al Repositorio Final Aprobado
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Borradores en Estado "EN_REVISION"</h5>
        <span class="badge bg-warning text-dark"><?= count($pending) ?> Pendientes</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($pending)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-check-circle-fill display-5 text-success d-block mb-2"></i>
                <h5 class="fw-semibold">¡Bandeja al día!</h5>
                <p class="mb-0">No hay contratos esperando revisión en este momento.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Código</th>
                            <th>Realtor / Broker</th>
                            <th>Zona Miami</th>
                            <th>Tarifa Mensual</th>
                            <th>Vigencia</th>
                            <th>Elaborado Por</th>
                            <th class="text-end">Decisión del Jefe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending as $p): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-warning text-dark font-monospace px-2 py-1"><?= htmlspecialchars($p['contract_code']) ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($p['company_name']) ?></strong><br>
                                    <small class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($p['contact_person']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($p['miami_zone']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">$<?= number_format($p['monthly_fee'], 2) ?> USD</span>
                                </td>
                                <td>
                                    <small class="d-block"><?= htmlspecialchars($p['start_date']) ?> al <?= htmlspecialchars($p['end_date']) ?></small>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($p['generator_employee_name']) ?></small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="/contracts/view?id=<?= $p['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Inspeccionar documento">
                                            <i class="bi bi-eye-fill"></i> Ver Borrador
                                        </a>
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalApprove<?= $p['id'] ?>">
                                            <i class="bi bi-check-lg"></i> Aprobar y Archivar
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalReject<?= $p['id'] ?>">
                                            <i class="bi bi-arrow-return-left"></i> Devolver
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- MODAL APROBAR Y ARCHIVAR -->
                            <div class="modal fade" id="modalApprove<?= $p['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="/contracts/review" method="POST">
                                            <input type="hidden" name="contract_id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="action" value="APROBAR">

                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title fw-bold"><i class="bi bi-check2-circle me-2"></i>Aprobar y Archivar Contrato</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>¿Confirma la aprobación formal del contrato <strong><?= htmlspecialchars($p['contract_code']) ?></strong> para el cliente <strong><?= htmlspecialchars($p['company_name']) ?></strong>?</p>
                                                <div class="alert alert-light border small">
                                                    <strong>Tarifa Pactada:</strong> $<?= number_format($p['monthly_fee'], 2) ?> USD / mes<br>
                                                    <strong>Zona:</strong> <?= htmlspecialchars($p['miami_zone']) ?>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Notas de Aprobación (Opcional):</label>
                                                    <textarea name="feedback_notes" class="form-control" rows="2" placeholder="Ej: Aprobado según lo conversado con el broker. Proceder con plan de rodajes."></textarea>
                                                </div>
                                                <div class="text-muted small">
                                                    <i class="bi bi-info-circle me-1"></i> El contrato pasará al Repositorio Oficial y no podrá ser modificado.
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-success fw-bold">Confirmar y Aprobar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- MODAL DEVOLVER CON OBSERVACIONES -->
                            <div class="modal fade" id="modalReject<?= $p['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="/contracts/review" method="POST">
                                            <input type="hidden" name="contract_id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="action" value="RECHAZAR">

                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-octagon me-2"></i>Devolver con Observaciones</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Indique las correcciones que debe realizar el redactor (<?= htmlspecialchars($p['generator_employee_name']) ?>) antes de poder aprobar:</p>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold text-danger">Observaciones / Motivo de Devolución *:</label>
                                                    <textarea name="feedback_notes" class="form-control" rows="3" required placeholder="Ej: Ajustar cláusula de entrega de dron en Brickell y rectificar honorario a $4,200."></textarea>
                                                </div>
                                                <div class="text-muted small">
                                                    El contrato volverá a la bandeja del empleado en estado "DEVUELTO CON CORRECCIÓN".
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-danger fw-bold">Devolver para Corrección</button>
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
