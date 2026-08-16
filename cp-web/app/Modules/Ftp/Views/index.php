<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <div class="d-flex align-items-center gap-2">
      <h1 class="h4 mb-0">Servidor FTP y Cuentas de Acceso</h1>
      <?php if (!empty($ftpServiceActive)): ?>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 font-monospace">
          <i class="bi bi-check-circle-fill me-1"></i>VSFTPD ACTIVO
        </span>
      <?php else: ?>
        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 font-monospace">
          <i class="bi bi-exclamation-triangle-fill me-1"></i>VSFTPD INACTIVO
        </span>
      <?php endif; ?>
    </div>
    <span class="text-muted small">Crea y gestiona cuentas de acceso FTP virtuales con enjaulado chroot restringido por dominio y directorio.</span>
  </div>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-primary text-uppercase fw-bold" data-bs-toggle="modal" data-bs-target="#createFtpModal">
      <i class="bi bi-plus-lg me-1"></i> Nueva Cuenta FTP
    </button>
  </div>
</div>

<!-- Filtro por Dominio y Datos de Conexion -->
<div class="row g-3 mb-3">
  <div class="col-md-7">
    <div class="bg-body p-3 rounded h-100 d-flex flex-column justify-content-between">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0">
          <i class="bi bi-funnel me-1"></i> Filtrar Cuentas por Dominio Web
        </h6>
        <span class="badge bg-secondary-subtle text-secondary border font-monospace">
          <?= count($accounts) ?> <?= (count($accounts) == 1) ? "cuenta" : "cuentas" ?>
        </span>
      </div>

      <form method="GET" action="/ftp" class="row g-2 align-items-center mt-1">
        <div class="col-sm-9">
          <select name="domain_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">-- Ver todos los dominios --</option>
            <?php foreach ($domains as $d): ?>
              <option value="<?= $d["id"] ?>" <?= ($selectedDomainId === (int)$d["id"]) ? "selected" : "" ?>>
                <?= $d["domain"] ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-3">
          <a href="/ftp" class="btn btn-sm btn-outline-secondary w-100 text-uppercase fw-bold text-nowrap">
            <i class="bi bi-x-circle me-1"></i> Limpiar
          </a>
        </div>
      </form>

      <div class="text-muted small mt-2">
        Selecciona un dominio especifico para ver unicamente sus cuentas asociadas o gestionar sus accesos de desarrollo.
      </div>
    </div>
  </div>

  <!-- Tarjeta de Parametros de Conexion FTP -->
  <div class="col-md-5">
    <div class="bg-body p-3 rounded h-100">
      <h6 class="fw-bold mb-2">
        <i class="bi bi-hdd-network me-1"></i> Parametros de Conexion (FileZilla / WinSCP)
      </h6>

      <table class="table table-sm table-borderless small mb-0">
        <tbody>
          <tr>
            <td class="text-muted" style="width: 130px;">Servidor / Host:</td>
            <td><code class="text-primary font-monospace"><?= $serverIp ?></code> <span class="text-muted">(o tu dominio)</span></td>
          </tr>
          <tr>
            <td class="text-muted">Protocolo:</td>
            <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">FTP / FTPS (Explicit)</span></td>
          </tr>
          <tr>
            <td class="text-muted">Puerto:</td>
            <td><span class="badge bg-secondary font-monospace">21</span> <span class="text-muted small ms-1">(Modo Pasivo: 40000-50000)</span></td>
          </tr>
          <tr>
            <td class="text-muted">Cifrado:</td>
            <td><span class="text-body">Usar FTP explicito sobre TLS si esta disponible</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Tabla de Cuentas FTP -->
<div class="bg-body p-3 rounded mb-3">
  <?php if (empty($accounts)): ?>
    <div class="text-center py-4">
      <i class="bi bi-folder-symlink text-muted fs-1 mb-2 d-block"></i>
      <h6 class="text-muted">No hay cuentas FTP registradas <?= ($selectedDomainId > 0) ? "para este dominio" : "" ?></h6>
      <p class="small text-muted mb-3">Crea una cuenta FTP virtual para otorgar acceso seguro a diseñadores, desarrolladores o clientes.</p>
      <button type="button" class="btn btn-sm btn-primary text-uppercase fw-bold" data-bs-toggle="modal" data-bs-target="#createFtpModal">
        <i class="bi bi-plus-lg me-1"></i> Crear Cuenta FTP
      </button>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle table-sm m-0">
        <thead>
          <tr>
            <th class="ps-3">Usuario FTP</th>
            <th>Dominio Web</th>
            <th>Ruta Enjaulada (Chroot)</th>
            <th>Estado</th>
            <th class="text-end pe-3 text-nowrap">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($accounts as $acc): ?>
            <tr>
              <td class="ps-3 fw-bold">
                <span class="d-inline-flex align-items-center">
                  <i class="bi bi-person-badge text-primary me-2 fs-5"></i>
                  <code><?= $acc["ftp_user"] ?></code>
                </span>
              </td>
              <td>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                  <i class="bi bi-globe me-1"></i><?= $acc["domain"] ?>
                </span>
              </td>
              <td>
                <code class="text-body small user-select-all">/home/<?= $acc["sys_user"] ?? "admin" ?>/web/<?= $acc["domain"] ?>/<?= $acc["ftp_path"] ?></code>
              </td>
              <td>
                <span class="badge bg-success-subtle text-success border border-success-subtle">
                  ACTIVO
                </span>
              </td>
              <td class="text-end pe-3 text-nowrap">
                <div class="d-flex justify-content-end gap-1">
                  <!-- Cambiar Clave -->
                  <button type="button" class="btn btn-sm btn-outline-warning text-uppercase fw-bold text-nowrap" data-bs-toggle="modal" data-bs-target="#changePassModal" onclick="openPassModal('<?= $acc["id"] ?>', '<?= $acc["ftp_user"] ?>')">
                    <i class="bi bi-key me-1"></i> Contraseña
                  </button>

                  <!-- Modificar Ruta -->
                  <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap" data-bs-toggle="modal" data-bs-target="#editPathModal" onclick="openPathModal('<?= $acc["id"] ?>', '<?= $acc["ftp_user"] ?>', '<?= $acc["ftp_path"] ?>')">
                    <i class="bi bi-folder me-1"></i> Ruta
                  </button>

                  <!-- Eliminar -->
                  <a href="/ftp/delete/<?= $acc["id"] ?>" class="btn btn-sm btn-outline-danger text-uppercase fw-bold text-nowrap" onclick="return confirm('Seguro que deseas eliminar la cuenta FTP <?= $acc["ftp_user"] ?>?');">
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

<!-- Modal: Crear Cuenta FTP -->
<div class="modal fade" id="createFtpModal" tabindex="-1" aria-labelledby="createFtpModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/ftp/store" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="createFtpModalLabel">
            <i class="bi bi-folder-plus text-primary me-2"></i>Nueva Cuenta FTP Virtual
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="create_domain_id" class="form-label">Dominio Web Asociado <span class="text-danger">*</span></label>
            <select class="form-select" id="create_domain_id" name="domain_id" onchange="updateUserPrefixHint()" required>
              <option value="">-- Seleccionar Dominio --</option>
              <?php foreach ($domains as $d): ?>
                <option value="<?= $d["id"] ?>" data-domain="<?= $d["domain"] ?>" <?= ($selectedDomainId === (int)$d["id"]) ? "selected" : "" ?>>
                  <?= $d["domain"] ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="create_ftp_user" class="form-label">Nombre de Usuario FTP <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text font-monospace" id="userPrefixAddon">user_</span>
              <input type="text" class="form-control font-monospace" id="create_ftp_user" name="ftp_user" placeholder="ej. dev, upload, admin" required>
            </div>
            <div class="form-text small">Solo letras, números o guiones bajos. El prefijo del dominio se añade automáticamente para evitar colisiones.</div>
          </div>

          <div class="mb-3">
            <label for="create_ftp_password" class="form-label">Contraseña <span class="text-danger">*</span></label>
            <input type="password" class="form-control font-monospace" id="create_ftp_password" name="ftp_password" placeholder="Ingresar contraseña segura" data-pr-toggle-password required>
          </div>

          <div class="mb-3">
            <label for="create_ftp_path" class="form-label">Directorio de Acceso (Ruta Relativa al Dominio) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text font-monospace">/web/dominio/</span>
              <input type="text" class="form-control font-monospace" id="create_ftp_path" name="ftp_path" value="public_html" required>
            </div>
            <div class="form-text small">
              Usa <code>public_html</code> para la carpeta web principal, déjalo vacío o <code>/</code> para la raíz del dominio, o subcarpetas como <code>public_html/uploads</code>.
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary text-uppercase fw-bold">
            <i class="bi bi-check2-circle me-1"></i> Crear Cuenta FTP
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
      <form action="/ftp/password" method="POST">
        <input type="hidden" id="change_pass_account_id" name="account_id" value="">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-key text-warning me-2"></i>Cambiar Contraseña FTP
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p class="small text-muted mb-3">Actualizando contraseña para el usuario: <strong id="change_pass_user_label" class="text-primary font-monospace"></strong></p>
          <div class="mb-3">
            <label for="new_password" class="form-label">Nueva Contraseña <span class="text-danger">*</span></label>
            <input type="password" class="form-control font-monospace" id="new_password" name="new_password" placeholder="Nueva contraseña segura" data-pr-toggle-password required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary text-uppercase fw-bold">
            <i class="bi bi-check2-circle me-1"></i> Guardar Nueva Contraseña
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Editar Ruta -->
<div class="modal fade" id="editPathModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/ftp/path" method="POST">
        <input type="hidden" id="edit_path_account_id" name="account_id" value="">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-folder text-primary me-2"></i>Modificar Ruta Enjaulada Chroot
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p class="small text-muted mb-3">Ajustando directorio de acceso para: <strong id="edit_path_user_label" class="text-primary font-monospace"></strong></p>
          <div class="mb-3">
            <label for="edit_new_path" class="form-label">Nueva Ruta Relativa al Dominio <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text font-monospace">/web/dominio/</span>
              <input type="text" class="form-control font-monospace" id="edit_new_path" name="new_path" required>
            </div>
            <div class="form-text small">Ejemplos: <code>public_html</code>, <code>public_html/assets</code> o déjalo vacío para la raíz.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary text-uppercase fw-bold">
            <i class="bi bi-check2-circle me-1"></i> Guardar Cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function updateUserPrefixHint() {
  const select = document.getElementById("create_domain_id");
  const addon = document.getElementById("userPrefixAddon");
  if (!select || !addon) return;

  const selectedOpt = select.options[select.selectedIndex];
  const domName = selectedOpt.getAttribute("data-domain");
  if (domName) {
    const prefix = domName.split(".")[0].replace(/[^a-zA-Z0-9]/g, "");
    addon.textContent = prefix + "_";
  } else {
    addon.textContent = "ftp_";
  }
}

function openPassModal(id, user) {
  document.getElementById("change_pass_account_id").value = id;
  document.getElementById("change_pass_user_label").textContent = user;
}

function openPathModal(id, user, path) {
  document.getElementById("edit_path_account_id").value = id;
  document.getElementById("edit_path_user_label").textContent = user;
  document.getElementById("edit_new_path").value = path;
}

document.addEventListener("DOMContentLoaded", function () {
  updateUserPrefixHint();
});
</script>
