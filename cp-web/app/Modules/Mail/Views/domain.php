<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
  <div>
    <h1 class="h4 mb-0">Gestion de Correo: <span class="text-primary font-monospace"><?= $domain["domain_name"] ?></span></h1>
    <span class="text-muted small">Cuentas de correo, cuotas de almacenamiento, reenvios y configuracion de entregabilidad DKIM / SPF.</span>
  </div>
  <div class="d-flex gap-2">
    <a href="/mail" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
      <i class="bi bi-arrow-left me-1"></i> Volver a Dominios
    </a>
  </div>
</div>

<!-- Navegacion por pestañas -->
<div class="bg-body p-3 rounded my-3">
  <ul class="nav nav-pills nav-justified">
    <li class="nav-item">
      <a class="nav-link <?= ($activeTab === "accounts") ? "active" : "" ?>" href="/mail/domain/<?= $domain["id"] ?>?tab=accounts">
        <i class="bi bi-envelope-at me-1"></i>
        Cuentas de Correo (<?= count($accounts) ?>)
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($activeTab === "forwarders") ? "active" : "" ?>" href="/mail/domain/<?= $domain["id"] ?>?tab=forwarders">
        <i class="bi bi-arrow-right-circle me-1"></i>
        Reenvios y Alias (<?= count($forwarders) ?>)
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($activeTab === "dns") ? "active" : "" ?>" href="/mail/domain/<?= $domain["id"] ?>?tab=dns">
        <i class="bi bi-shield-check me-1"></i>
        Registros DNS (DKIM / SPF / MX)
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($activeTab === "webmail") ? "active" : "" ?>" href="/mail/domain/<?= $domain["id"] ?>?tab=webmail">
        <i class="bi bi-window me-1"></i>
        Acceso Webmail y Clientes
      </a>
    </li>
  </ul>
</div>

<?php if ($activeTab === "accounts"): ?>
<!-- ======================================================================= -->
<!-- SECCION: CUENTAS DE CORREO -->
<!-- ======================================================================= -->
<div class="bg-body p-3 rounded mb-3 text-end">
  <button type="button" class="btn btn-sm btn-primary text-uppercase fw-bold" data-bs-toggle="modal" data-bs-target="#createAccountModal">
    <i class="bi bi-plus-lg me-1"></i> Crear Cuenta de Correo
  </button>
</div>

<div class="bg-body p-3 rounded mb-3">
  <?php if (empty($accounts)): ?>
    <div class="text-center py-3">
      <i class="bi bi-inbox text-muted fs-1 mb-2 d-block"></i>
      <h6 class="text-muted">No hay cuentas de correo creadas en este dominio</h6>
      <p class="small text-muted mb-3">Crea tu primera cuenta de correo para comenzar a enviar y recibir mensajes.</p>
      <button type="button" class="btn btn-sm btn-primary text-uppercase fw-bold" data-bs-toggle="modal" data-bs-target="#createAccountModal">
        <i class="bi bi-plus-lg me-1"></i> Crear Cuenta de Correo
      </button>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle table-sm m-0">
        <thead>
          <tr>
            <th class="ps-3">Direccion de Correo</th>
            <th>Buzon Maildir</th>
            <th>Cuota Asignada</th>
            <th>Estado</th>
            <th class="text-end pe-3 text-nowrap">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($accounts as $acc): ?>
            <tr>
              <td class="ps-3 fw-bold">
                <span class="d-inline-flex align-items-center">
                  <i class="bi bi-envelope me-2 text-primary"></i>
                  <?= $acc["account_email"] ?>
                </span>
              </td>
              <td>
                <code class="text-body small">/home/admin/mail/<?= $domain["domain_name"] ?>/<?= $acc["account_user"] ?></code>
              </td>
              <td>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace">
                  <?= $acc["quota_mb"] ?> MB
                </span>
              </td>
              <td>
                <span class="badge bg-success-subtle text-success border border-success-subtle">
                  ACTIVO
                </span>
              </td>
              <td class="text-end pe-3 text-nowrap">
                <div class="d-flex justify-content-end gap-1">
                  <a href="http://webmail.<?= $domain["domain_name"] ?>" target="_blank" class="btn btn-sm btn-outline-info text-uppercase fw-bold text-nowrap" title="Entrar a Webmail">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Webmail
                  </a>
                  <button type="button" class="btn btn-sm btn-outline-warning text-uppercase fw-bold text-nowrap" data-bs-toggle="modal" data-bs-target="#changePassModal" onclick="document.getElementById('change_pass_account_id').value = '<?= $acc["id"] ?>'; document.getElementById('change_pass_email_label').textContent = '<?= $acc["account_email"] ?>';">
                    <i class="bi bi-key me-1"></i> Contraseña
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap" data-bs-toggle="modal" data-bs-target="#editQuotaModal" onclick="document.getElementById('edit_quota_account_id').value = '<?= $acc["id"] ?>'; document.getElementById('edit_quota_email_label').textContent = '<?= $acc["account_email"] ?>'; document.getElementById('edit_quota_mb').value = '<?= $acc["quota_mb"] ?>';">
                    <i class="bi bi-pie-chart me-1"></i> Cuota
                  </button>
                  <a href="/mail/account/delete/<?= $acc["id"] ?>" class="btn btn-sm btn-outline-danger text-uppercase fw-bold text-nowrap" onclick="return confirm('Seguro que deseas eliminar la cuenta <?= $acc["account_email"] ?>? Se borraran todos sus correos.');">
                    <i class="bi bi-trash me-1"></i> Eliminar
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Modal: Crear Cuenta de Correo -->
<div class="modal fade" id="createAccountModal" tabindex="-1" aria-labelledby="createAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/mail/account/create/<?= $domain["id"] ?>" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="createAccountModalLabel">
            <i class="bi bi-envelope-plus me-1"></i> Nueva Cuenta de Correo
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="account_user" class="form-label">Nombre de Usuario de Correo <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="text" class="form-control font-monospace" id="account_user" name="account_user" placeholder="ej. info, contacto, soporte" required>
              <span class="input-group-text font-monospace">@<?= $domain["domain_name"] ?></span>
            </div>
            <div class="form-text small">Solo letras minusculas, numeros, puntos o guiones.</div>
          </div>

          <div class="mb-3">
            <label for="account_password" class="form-label">Contraseña <span class="text-danger">*</span></label>
            <input type="password" class="form-control font-monospace" id="account_password" name="account_password" placeholder="Ingresar contraseña segura" data-pr-toggle-password required>
          </div>

          <div class="mb-3">
            <label for="quota_mb" class="form-label">Cuota de Almacenamiento (MB) <span class="text-danger">*</span></label>
            <select class="form-select font-monospace" id="quota_mb" name="quota_mb" required>
              <option value="512">512 MB</option>
              <option value="1024" selected>1024 MB (1 GB)</option>
              <option value="2048">2048 MB (2 GB)</option>
              <option value="5120">5120 MB (5 GB)</option>
              <option value="10240">10240 MB (10 GB)</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary text-uppercase fw-bold">
            <i class="bi bi-check2 me-1"></i> Crear Cuenta
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Cambiar Contraseña -->
<div class="modal fade" id="changePassModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/mail/account/password" method="POST">
        <input type="hidden" name="domain_id" value="<?= $domain["id"] ?>">
        <input type="hidden" id="change_pass_account_id" name="account_id" value="">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-key me-1"></i> Cambiar Contraseña
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p class="small text-muted mb-3">Actualizando contraseña para: <strong id="change_pass_email_label" class="text-primary font-monospace"></strong></p>
          <div class="mb-3">
            <label for="new_password" class="form-label">Nueva Contraseña <span class="text-danger">*</span></label>
            <input type="password" class="form-control font-monospace" id="new_password" name="new_password" placeholder="Nueva contraseña segura" data-pr-toggle-password required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary text-uppercase fw-bold">
            <i class="bi bi-check2 me-1"></i> Guardar Nueva Contraseña
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Editar Cuota -->
<div class="modal fade" id="editQuotaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/mail/account/quota" method="POST">
        <input type="hidden" name="domain_id" value="<?= $domain["id"] ?>">
        <input type="hidden" id="edit_quota_account_id" name="account_id" value="">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-pie-chart me-1"></i> Modificar Cuota de Buzon
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p class="small text-muted mb-3">Modificando cuota para: <strong id="edit_quota_email_label" class="text-primary font-monospace"></strong></p>
          <div class="mb-3">
            <label for="edit_quota_mb" class="form-label">Limite de Almacenamiento (MB) <span class="text-danger">*</span></label>
            <select class="form-select font-monospace" id="edit_quota_mb" name="quota_mb" required>
              <option value="512">512 MB</option>
              <option value="1024">1024 MB (1 GB)</option>
              <option value="2048">2048 MB (2 GB)</option>
              <option value="5120">5120 MB (5 GB)</option>
              <option value="10240">10240 MB (10 GB)</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary text-uppercase fw-bold">
            <i class="bi bi-check2 me-1"></i> Aplicar Cuota
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php elseif ($activeTab === "forwarders"): ?>
<!-- ======================================================================= -->
<!-- SECCION: REENVIOS Y ALIAS -->
<!-- ======================================================================= -->
<div class="bg-body p-3 rounded mb-3 text-end">
  <button type="button" class="btn btn-sm btn-primary text-uppercase fw-bold" data-bs-toggle="modal" data-bs-target="#createForwarderModal">
    <i class="bi bi-plus-lg me-1"></i> Crear Reenvio de Correo
  </button>
</div>

<div class="bg-body p-3 rounded mb-3">
  <?php if (empty($forwarders)): ?>
    <div class="text-center py-3">
      <i class="bi bi-arrow-right-circle text-muted fs-1 mb-2 d-block"></i>
      <h6 class="text-muted">No hay reenvios configurados</h6>
      <p class="small text-muted mb-3">Los reenvios permiten redirigir los mensajes entrantes a una direccion externa (ej. Gmail, Outlook).</p>
      <button type="button" class="btn btn-sm btn-primary text-uppercase fw-bold" data-bs-toggle="modal" data-bs-target="#createForwarderModal">
        <i class="bi bi-plus-lg me-1"></i> Crear Reenvio
      </button>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle table-sm m-0">
        <thead>
          <tr>
            <th class="ps-3">Direccion de Origen</th>
            <th></th>
            <th>Destino Redirigido</th>
            <th class="text-end pe-3 text-nowrap">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($forwarders as $fwd): ?>
            <tr>
              <td class="ps-3 fw-bold">
                <span class="d-inline-flex align-items-center">
                  <i class="bi bi-envelope-arrow-up me-2 text-primary"></i>
                  <?= $fwd["source_email"] ?>
                </span>
              </td>
              <td>
                <i class="bi bi-arrow-right text-muted"></i>
              </td>
              <td>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace">
                  <?= $fwd["destination_email"] ?>
                </span>
              </td>
              <td class="text-end pe-3 text-nowrap">
                <a href="/mail/forwarder/delete/<?= $fwd["id"] ?>" class="btn btn-sm btn-outline-danger text-uppercase fw-bold text-nowrap" onclick="return confirm('Seguro que deseas eliminar este reenvio?');">
                  <i class="bi bi-trash me-1"></i> Eliminar
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Modal: Crear Reenvio -->
<div class="modal fade" id="createForwarderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/mail/forwarder/create/<?= $domain["id"] ?>" method="POST">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-arrow-right-circle me-1"></i> Nuevo Reenvio de Correo
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="source_user" class="form-label">Correo de Origen <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="text" class="form-control font-monospace" id="source_user" name="source_user" placeholder="ej. ventas, contacto" required>
              <span class="input-group-text font-monospace">@<?= $domain["domain_name"] ?></span>
            </div>
          </div>

          <div class="mb-3">
            <label for="destination_email" class="form-label">Correo de Destino <span class="text-danger">*</span></label>
            <input type="email" class="form-control font-monospace" id="destination_email" name="destination_email" placeholder="ej. mi-correo-personal@gmail.com" required>
            <div class="form-text small">Los correos dirigidos a este origen se reenviaran a este destino.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary text-uppercase fw-bold">
            <i class="bi bi-check2 me-1"></i> Guardar Reenvio
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php elseif ($activeTab === "dns"): ?>
<!-- ======================================================================= -->
<!-- SECCION: CONFIGURACION DE REGISTROS DNS DEL DOMINIO -->
<!-- ======================================================================= -->
<?php
$domainName   = $domain["domain_name"] ?? ($domain["domain"] ?? "midominio.com");
$serverIp     = $dnsInfo["server_ip"] ?? "127.0.0.1";
$dkimSelector = $domain["dkim_selector"] ?? ($dnsInfo["selector"] ?? "mail");
$dkimRecord   = $dnsInfo["dkim_record"] ?? "";
$spfRecord    = $dnsInfo["spf_record"] ?? ("v=spf1 a mx ip4:" . $serverIp . " -all");
$dmarcRecord  = "v=DMARC1; p=quarantine; pct=100";

$dnsRecords = [
  [
    "name"     => "mail." . $domainName,
    "type"     => "A",
    "priority" => "",
    "ttl"      => "14400",
    "value"    => $serverIp
  ],
  [
    "name"     => "webmail." . $domainName,
    "type"     => "A",
    "priority" => "",
    "ttl"      => "14400",
    "value"    => $serverIp
  ],
  [
    "name"     => $domainName,
    "type"     => "MX",
    "priority" => "10",
    "ttl"      => "14400",
    "value"    => "mail." . $domainName . "."
  ],
  [
    "name"     => $domainName,
    "type"     => "TXT",
    "priority" => "",
    "ttl"      => "14400",
    "value"    => $spfRecord
  ],
  [
    "name"     => "_dmarc",
    "type"     => "TXT",
    "priority" => "",
    "ttl"      => "14400",
    "value"    => $dmarcRecord
  ],
  [
    "name"     => $dkimSelector . "._domainkey",
    "type"     => "TXT",
    "priority" => "",
    "ttl"      => "3600",
    "value"    => $dkimRecord
  ]
];
?>

<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
  <div>
    <h6 class="fw-bold mb-0">
      <i class="bi bi-diagram-3 me-1"></i> Configuracion de Registros DNS para <?= $domainName ?>
    </h6>
    <span class="text-muted small">Copia y pega estos 6 registros en la zona DNS de tu proveedor de dominio (Cloudflare, cPanel, GoDaddy, Namecheap, etc.).</span>
  </div>
  <button type="button" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap" onclick="copyAllDnsRecords();">
    <i class="bi bi-clipboard-check me-1"></i> Copiar Todos los Registros
  </button>
</div>

<!-- Tabla de Registros DNS -->
<div class="bg-body p-3 rounded mb-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle table-sm m-0">
      <thead>
        <tr class="text-secondary small">
          <th style="width: 25%;" class="ps-2">Registro</th>
          <th style="width: 8%;" class="text-center">Tipo</th>
          <th style="width: 10%;" class="text-center">Prioridad</th>
          <th style="width: 10%;" class="text-center">TTL</th>
          <th style="width: 47%;" class="pe-2">IP o Valor</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dnsRecords as $idx => $r): ?>
          <tr>
            <td class="ps-2 py-2">
              <div class="input-group input-group-sm">
                <input type="text" class="form-control font-monospace form-control-sm bg-body-tertiary text-body border" id="dns_name_<?= $idx ?>" value="<?= $r["name"] ?>" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('dns_name_<?= $idx ?>', 'Registro copiado');" title="Copiar nombre del registro">
                  <i class="bi bi-clipboard"></i>
                </button>
              </div>
            </td>
            <td class="text-center fw-bold">
              <?php if ($r["type"] === "A"): ?>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1"><?= $r["type"] ?></span>
              <?php elseif ($r["type"] === "MX"): ?>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace px-2 py-1"><?= $r["type"] ?></span>
              <?php else: ?>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace px-2 py-1"><?= $r["type"] ?></span>
              <?php endif; ?>
            </td>
            <td class="text-center font-monospace fw-bold">
              <?= !empty($r["priority"]) ? $r["priority"] : "<span class=\"text-muted fw-normal\">-</span>" ?>
            </td>
            <td class="text-center font-monospace text-muted small">
              <?= $r["ttl"] ?>
            </td>
            <td class="pe-2 py-2">
              <div class="input-group input-group-sm">
                <input type="text" class="form-control font-monospace form-control-sm bg-body-tertiary text-body border" id="dns_val_<?= $idx ?>" value="<?= $r["value"] ?>" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('dns_val_<?= $idx ?>', 'Valor DNS copiado');" title="Copiar valor">
                  <i class="bi bi-clipboard"></i>
                </button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function copyAllDnsRecords() {
  var text = "=== REGISTROS DNS PARA <?= $domainName ?> ===\n\n";
  <?php foreach ($dnsRecords as $r): ?>
    text += "Registro: <?= $r["name"] ?>\n";
    text += "Tipo: <?= $r["type"] ?>\n";
    <?php if (!empty($r["priority"])): ?>
    text += "Prioridad: <?= $r["priority"] ?>\n";
    <?php endif; ?>
    text += "TTL: <?= $r["ttl"] ?>\n";
    text += "Valor: <?= addslashes($r["value"]) ?>\n";
    text += "----------------------------------------\n";
  <?php endforeach; ?>

  var aux = document.createElement("textarea");
  aux.value = text;
  document.body.appendChild(aux);
  aux.select();
  document.execCommand("copy");
  document.body.removeChild(aux);

  if (typeof Toastify === "function") {
    Toastify({
      text: "Todos los registros DNS han sido copiados al portapapeles",
      duration: 3000,
      gravity: "top",
      position: "right",
      backgroundColor: "#198754"
    }).showToast();
  } else {
    alert("Todos los registros DNS han sido copiados al portapapeles.");
  }
}
</script>

<?php elseif ($activeTab === "webmail"): ?>
<!-- ======================================================================= -->
<!-- SECCION: ACCESO A WEBMAIL Y CONFIGURACION DE CLIENTES -->
<!-- ======================================================================= -->
<div class="row g-3">
  <div class="col-md-6">
    <div class="bg-body p-3 rounded h-100">
      <h6 class="fw-bold mb-3">
        <i class="bi bi-box-arrow-up-right me-1"></i> Acceso Webmail del Dominio
      </h6>

      <p class="small text-muted mb-3">
        Cada dominio cuenta con su propia direccion webmail aislada. Puedes ingresar desde cualquier navegador web:
      </p>

      <div class="p-3 bg-body-tertiary rounded border text-center mb-3">
        <a href="http://webmail.<?= $domain["domain_name"] ?>" target="_blank" class="h5 text-primary text-decoration-none font-monospace d-block my-2">
          <i class="bi bi-window-fullscreen me-2"></i> http://webmail.<?= $domain["domain_name"] ?>
        </a>
      </div>

      <div class="alert alert-info py-2 px-3 small mb-0">
        <i class="bi bi-info-circle me-1"></i>
        <strong>Inicio de Sesion:</strong> En el formulario de Roundcube, ingresa tu direccion de correo completa (ej. <code>info@<?= $domain["domain_name"] ?></code>) y tu contraseña asignada.
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="bg-body p-3 rounded h-100">
      <h6 class="fw-bold mb-3">
        <i class="bi bi-phone me-1"></i> Configuracion para Outlook, Thunderbird y Moviles
      </h6>

      <table class="table table-sm table-borderless small mb-0">
        <tbody>
          <tr>
            <td class="fw-bold text-primary" colspan="2">Servidor Entrante (IMAP Seguro):</td>
          </tr>
          <tr>
            <td class="text-muted ps-3">Servidor:</td>
            <td><code><?= $domain["domain_name"] ?></code> (o <code><?= $dnsInfo["server_ip"] ?></code>)</td>
          </tr>
          <tr>
            <td class="text-muted ps-3">Puerto IMAP:</td>
            <td><span class="badge bg-secondary font-monospace">993 (SSL/TLS)</span> o <span class="badge bg-secondary font-monospace">143 (STARTTLS)</span></td>
          </tr>
          <tr>
            <td class="fw-bold text-primary pt-2" colspan="2">Servidor Saliente (SMTP Seguro):</td>
          </tr>
          <tr>
            <td class="text-muted ps-3">Servidor:</td>
            <td><code><?= $domain["domain_name"] ?></code> (o <code><?= $dnsInfo["server_ip"] ?></code>)</td>
          </tr>
          <tr>
            <td class="text-muted ps-3">Puerto SMTP:</td>
            <td><span class="badge bg-secondary font-monospace">465 (SSL/TLS)</span> o <span class="badge bg-secondary font-monospace">587 (STARTTLS)</span></td>
          </tr>
          <tr>
            <td class="fw-bold text-primary pt-2" colspan="2">Autenticacion Requerida:</td>
          </tr>
          <tr>
            <td class="text-muted ps-3">Usuario:</td>
            <td><code>usuario@<?= $domain["domain_name"] ?></code></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>
