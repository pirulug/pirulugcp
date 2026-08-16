<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
  <div>
    <h1 class="h4 mb-0">Servidor de Correo y Webmail</h1>
    <span class="text-muted small">Administracion de cuentas de correo corporativo, buzones IMAP/POP3, reenvios y acceso a Webmail independiente.</span>
  </div>
  <div>
    <a href="/mail" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
      <i class="bi bi-arrow-clockwise me-1"></i> Refrescar
    </a>
  </div>
</div>

<!-- Estado de los Servicios de Correo -->
<div class="row g-3 mb-3">
  <div class="col-md-6">
    <div class="bg-body p-3 rounded d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <div class="rounded p-2 bg-primary-subtle text-primary me-3">
          <i class="bi bi-send-fill fs-4"></i>
        </div>
        <div>
          <h6 class="mb-0 fw-bold">Exim4 (Servidor SMTP de Salida y Recepcion)</h6>
          <small class="text-muted">Puertos 25 (SMTP), 587 (Submission) y 465 (SMTPS) con firma DKIM</small>
        </div>
      </div>
      <div>
        <?php $eximActive = ($mailServiceStatus["exim4"] ?? "inactive") === "active"; ?>
        <span class="badge <?= $eximActive ? "bg-success-subtle text-success border border-success-subtle" : "bg-danger-subtle text-danger border border-danger-subtle" ?>">
          <?= $eximActive ? "ACTIVO" : "INACTIVO" ?>
        </span>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="bg-body p-3 rounded d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <div class="rounded p-2 bg-info-subtle text-info me-3">
          <i class="bi bi-inbox-fill fs-4"></i>
        </div>
        <div>
          <h6 class="mb-0 fw-bold">Dovecot (Servidor IMAP / POP3 / LMTP)</h6>
          <small class="text-muted">Puertos 993 (IMAPS) y 995 (POP3S) con buzones Maildir aislados</small>
        </div>
      </div>
      <div>
        <?php $dovecotActive = ($mailServiceStatus["dovecot"] ?? "inactive") === "active"; ?>
        <span class="badge <?= $dovecotActive ? "bg-success-subtle text-success border border-success-subtle" : "bg-danger-subtle text-danger border border-danger-subtle" ?>">
          <?= $dovecotActive ? "ACTIVO" : "INACTIVO" ?>
        </span>
      </div>
    </div>
  </div>
</div>

<!-- Listado de Dominios -->
<div class="bg-body p-3 rounded mb-3">
  <h6 class="fw-bold mb-3">
    <i class="bi bi-globe me-1"></i> Dominios Web y Estado del Servicio de Correo
  </h6>

  <?php if (empty($domains)): ?>
    <div class="text-center py-3">
      <i class="bi bi-envelope-x text-muted fs-1 mb-2 d-block"></i>
      <h6 class="text-muted">No hay dominios creados en el panel</h6>
      <p class="small text-muted mb-3">Primero crea un dominio web en el panel para habilitar su servidor de correo y Webmail.</p>
      <a href="/web/create" class="btn btn-sm btn-primary text-uppercase fw-bold">
        <i class="bi bi-plus-lg me-1"></i> Crear Dominio Web
      </a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle table-sm m-0">
        <thead>
          <tr>
            <th class="ps-3">Dominio</th>
            <th>Estado del Correo</th>
            <th>Cuentas / Buzones</th>
            <th>Reenvios</th>
            <th>Acceso a Webmail</th>
            <th class="text-end pe-3 text-nowrap">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($domains as $d): ?>
            <?php $hasMail = !empty($d["mail_domain_id"]); ?>
            <tr>
              <td class="ps-3 fw-bold">
                <span class="d-inline-flex align-items-center">
                  <i class="bi bi-globe2 me-2 text-primary"></i>
                  <?= $d["domain_name"] ?>
                </span>
              </td>
              <td>
                <?php if ($hasMail): ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle">
                    <i class="bi bi-check-circle me-1"></i> HABILITADO
                  </span>
                <?php else: ?>
                  <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                    DESHABILITADO
                  </span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($hasMail): ?>
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">
                    <?= $d["total_accounts"] ?> <?= ($d["total_accounts"] == 1) ? "cuenta" : "cuentas" ?>
                  </span>
                <?php else: ?>
                  <span class="text-muted small">-</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($hasMail): ?>
                  <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace">
                    <?= $d["total_forwarders"] ?>
                  </span>
                <?php else: ?>
                  <span class="text-muted small">-</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($hasMail): ?>
                  <a href="http://webmail.<?= $d["domain_name"] ?>" target="_blank" class="small fw-bold text-decoration-none">
                    <i class="bi bi-box-arrow-up-right me-1"></i> webmail.<?= $d["domain_name"] ?>
                  </a>
                <?php else: ?>
                  <span class="text-muted small">-</span>
                <?php endif; ?>
              </td>
              <td class="text-end pe-3 text-nowrap">
                <div class="d-flex justify-content-end gap-1">
                  <?php if ($hasMail): ?>
                    <a href="/mail/domain/<?= $d["id"] ?>" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap">
                      <i class="bi bi-gear me-1"></i> Gestionar
                    </a>
                    <a href="/mail/disable/<?= $d["id"] ?>" class="btn btn-sm btn-outline-danger text-uppercase fw-bold text-nowrap" onclick="return confirm('Seguro que deseas deshabilitar el servicio de correo para <?= $d["domain_name"] ?>? Se eliminaran los buzones del servidor.');">
                      <i class="bi bi-power me-1"></i> Desactivar
                    </a>
                  <?php else: ?>
                    <a href="/mail/enable/<?= $d["id"] ?>" class="btn btn-sm btn-primary text-uppercase fw-bold text-nowrap">
                      <i class="bi bi-envelope-plus me-1"></i> Habilitar Correo
                    </a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
