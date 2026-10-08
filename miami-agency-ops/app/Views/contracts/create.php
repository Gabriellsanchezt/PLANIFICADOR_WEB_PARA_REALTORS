<div class="mb-4">
    <h2 class="fw-bold mb-1"><i class="bi bi-file-earmark-text-fill text-primary me-2"></i>Generador Dinámico de Contratos</h2>
    <p class="text-muted mb-0">Herramienta autorizada para redacción, parametrización y emisión de contratos con Realtors en Miami.</p>
</div>

<!-- Advertencia de Regla de Negocio -->
<div class="alert alert-info border-info d-flex align-items-center mb-4 shadow-sm" role="alert">
    <i class="bi bi-shield-exclamation fs-3 text-info me-3"></i>
    <div>
        <strong>Regla de Flujo de Aprobación:</strong>
        Puedes guardar tu contrato como <span class="badge bg-secondary">BORRADOR</span> para continuar editándolo. Una vez presiones <strong>"Enviar a Revisión del Jefe"</strong>, el contrato pasa al estado <span class="badge bg-warning text-dark">EN_REVISION</span>, quedará bloqueado para edición y se transferirá al archivo central de Dirección para su firma definitiva.
    </div>
</div>

<div class="row g-4">
    <!-- Formulario de Parametrización -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-sliders me-2"></i>Parámetros del Contrato</h5>
            </div>
            <div class="card-body p-4">
                <form id="contractForm" action="/contracts/store" method="POST">
                    <!-- Selección de Plantilla Base -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Plantilla Base de Contrato *</label>
                        <select name="template_id" id="template_id" class="form-select" required onchange="updateLivePreview()">
                            <?php foreach ($templates as $tpl): ?>
                                <option value="<?= $tpl['id'] ?>"><?= htmlspecialchars($tpl['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Selección de Cliente Realtor -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Cliente Realtor / Brokerage *</label>
                        <select name="realtor_id" id="realtor_id" class="form-select" required onchange="onRealtorSelect(this)">
                            <option value="">-- Seleccione Realtor Registrado --</option>
                            <?php foreach ($realtors as $r): ?>
                                <option value="<?= $r['id'] ?>"
                                        data-company="<?= htmlspecialchars($r['company_name']) ?>"
                                        data-contact="<?= htmlspecialchars($r['contact_person']) ?>"
                                        data-zone="<?= htmlspecialchars($r['miami_zone']) ?>">
                                    <?= htmlspecialchars($r['company_name']) ?> (<?= htmlspecialchars($r['contact_person']) ?> - <?= htmlspecialchars($r['miami_zone']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Paquetes Audiovisuales Preconfigurados -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Paquete Audiovisual Predefinido</label>
                        <select id="presetPackage" class="form-select" onchange="applyPresetPackage(this.value)">
                            <option value="">-- Personalizado / Elegir paquete predefinido --</option>
                            <?php foreach (\Config\REALTOR_PACKAGES as $k => $pkg): ?>
                                <option value="<?= $k ?>" data-fee="<?= $pkg['default_fee'] ?>" data-desc="<?= htmlspecialchars($pkg['deliverables']) ?>">
                                    <?= htmlspecialchars($pkg['name']) ?> ($<?= number_format($pkg['default_fee'], 0) ?>/mes)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tarifa Mensual -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Tarifa Mensual ($ USD) *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" name="monthly_fee" id="monthly_fee" class="form-control" value="3500.00" required oninput="updateLivePreview()">
                            <span class="input-group-text">USD / mes</span>
                        </div>
                    </div>

                    <!-- Vigencia (Fechas) -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-secondary">Fecha de Inicio *</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required onchange="updateLivePreview()">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-secondary">Fecha de Culminación *</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+1 year')) ?>" required onchange="updateLivePreview()">
                        </div>
                    </div>

                    <!-- Descripción de Entregables / Servicios -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-secondary">Alcance y Entregables del Paquete *</label>
                        <textarea name="services_description" id="services_description" class="form-control" rows="3" required oninput="updateLivePreview()">4 Reels cinematográficos 4K con dron + 1 Walkthrough de 2 minutos mensuales en locación acordada.</textarea>
                    </div>

                    <!-- Opciones de Envío -->
                    <div class="d-flex gap-2">
                        <button type="submit" name="submit_mode" value="draft" class="btn btn-outline-secondary w-50 py-2">
                            <i class="bi bi-floppy me-1"></i> Guardar Borrador
                        </button>
                        <button type="submit" name="submit_mode" value="send_review" class="btn btn-primary w-50 py-2 fw-semibold" onclick="return confirm('¿Confirmas el envío a revisión del Jefe? El contrato dejará de ser editable.')">
                            <i class="bi bi-send-check-fill me-1"></i> Enviar a Revisión
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Previsualización en Tiempo Real -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-eye-fill me-2"></i>Previsualización en Vivo del Contrato</h5>
                <span class="badge bg-secondary">HTML / Documento</span>
            </div>
            <div class="card-body p-3 bg-light overflow-auto" style="max-height: 650px;">
                <div id="livePreviewContainer" class="p-3 bg-white border rounded shadow-sm">
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                        <p>Cargando previsualización dinámica del contrato...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mis Borradores Activos y Devueltos -->
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-folder2-open text-warning me-2"></i>Mis Borradores Activos</h5>
        <span class="badge bg-dark"><?= count($drafts) ?> En Mi Bandeja</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($drafts)): ?>
            <div class="text-center py-4 text-muted">
                No tienes contratos en borrador. Los contratos enviados a revisión pasan directamente al panel del Jefe.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Código</th>
                            <th>Realtor</th>
                            <th>Zona</th>
                            <th>Tarifa</th>
                            <th>Estado</th>
                            <th>Observaciones del Jefe</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($drafts as $d): ?>
                            <tr>
                                <td class="fw-bold font-monospace"><?= htmlspecialchars($d['contract_code']) ?></td>
                                <td><?= htmlspecialchars($d['company_name']) ?></td>
                                <td><?= htmlspecialchars($d['miami_zone']) ?></td>
                                <td class="fw-bold text-success">$<?= number_format($d['monthly_fee'], 2) ?></td>
                                <td>
                                    <?php if ($d['status'] === 'BORRADOR'): ?>
                                        <span class="badge bg-secondary">BORRADOR</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">DEVUELTO CON CORRECCIÓN</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($d['feedback_notes'])): ?>
                                        <span class="text-danger small fw-semibold"><i class="bi bi-chat-left-dots me-1"></i><?= htmlspecialchars($d['feedback_notes']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <form action="/contracts/submit-existing" method="POST" class="d-inline" onsubmit="return confirm('¿Enviar este borrador al Jefe para aprobación final?')">
                                        <input type="hidden" name="contract_id" value="<?= $d['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-send-fill me-1"></i> Enviar al Jefe
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

<script>
let currentRealtorData = {
    company: 'One Sotheby’s International Realty',
    contact: 'Elena Rostova',
    zone: 'Brickell / Financial District'
};

function onRealtorSelect(select) {
    const opt = select.options[select.selectedIndex];
    if (opt.value) {
        currentRealtorData = {
            company: opt.getAttribute('data-company'),
            contact: opt.getAttribute('data-contact'),
            zone: opt.getAttribute('data-zone')
        };
    }
    updateLivePreview();
}

function applyPresetPackage(pkgKey) {
    const select = document.getElementById('presetPackage');
    const opt = select.options[select.selectedIndex];
    if (opt && opt.value) {
        document.getElementById('monthly_fee').value = opt.getAttribute('data-fee');
        document.getElementById('services_description').value = opt.getAttribute('data-desc');
        updateLivePreview();
    }
}

function updateLivePreview() {
    const templateId = document.getElementById('template_id').value;
    const realtorId = document.getElementById('realtor_id').value;
    const monthlyFee = document.getElementById('monthly_fee').value;
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const services = document.getElementById('services_description').value;

    const payload = new FormData();
    payload.append('template_id', templateId);
    payload.append('realtor_id', realtorId);
    payload.append('monthly_fee', monthlyFee);
    payload.append('start_date', startDate);
    payload.append('end_date', endDate);
    payload.append('services_description', services);
    payload.append('company_name', currentRealtorData.company);
    payload.append('contact_person', currentRealtorData.contact);
    payload.append('miami_zone', currentRealtorData.zone);

    fetch('/contracts/preview-ajax', {
        method: 'POST',
        body: payload
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('livePreviewContainer').innerHTML = data.html;
        }
    })
    .catch(err => {
        console.error("Error al actualizar preview:", err);
    });
}

// Inicializar preview al cargar
document.addEventListener('DOMContentLoaded', () => {
    updateLivePreview();
});
</script>
