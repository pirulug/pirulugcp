<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
  <div>
    <h1 class="h4 mb-0">Dominios Web (Nginx Proxy + Apache Backend)</h1>
  </div>
  <div>
    <a href="/web/create" class="btn btn-sm btn-primary text-uppercase fw-bold text-nowrap">
      <i class="bi bi-plus-lg me-1"></i> Nuevo Dominio
    </a>
  </div>
</div>

<div class="bg-body p-3 rounded mb-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle table-sm m-0">
      <thead>
        <tr>
          <th class="ps-3">Dominio</th>
          <th>Usuario</th>
          <th>PHP-FPM</th>
          <th>Carpeta Raiz</th>
          <th>SSL / HTTPS</th>
          <th class="text-end pe-3 text-nowrap">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($domains)): ?>
          <tr>
            <td colspan="6" class="text-center py-3 text-muted">
              No hay dominios web creados aun. <a href="/web/create" class="text-primary fw-bold text-uppercase">Crear el primero</a>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($domains as $d): ?>
            <tr>
              <td class="ps-3 fw-bold">
                <a href="http://<?= $d["domain"] ?>" target="_blank" class="text-decoration-none text-primary d-inline-flex align-items-center">
                  <i class="bi bi-globe me-2 text-muted"></i>
                  <?= $d["domain"] ?>
                  <i class="bi bi-box-arrow-up-right ms-1 small opacity-50"></i>
                </a>
              </td>
              <td>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace">
                  <?= $d["username"] ?? "admin" ?>
                </span>
              </td>
              <td>
                <form action="/web/update-php" method="POST" class="d-inline m-0">
                  <input type="hidden" name="domain_id" value="<?= (int)$d["id"] ?>">
                  <select name="php_version" class="form-select form-select-sm d-inline-block" style="width: auto;" onchange="this.form.submit()">
                    <?php foreach ($phpVersions as $php): ?>
                      <option value="<?= $php["version"] ?>" <?= ($d["php_version"] === $php["version"]) ? "selected" : "" ?>>
                        PHP <?= $php["version"] ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
              <td>
                <form action="/web/update-docroot" method="POST" class="d-inline-flex align-items-center m-0">
                  <input type="hidden" name="domain_id" value="<?= (int)$d["id"] ?>">
                  <input type="text" name="doc_root_suffix" class="form-control form-control-sm me-1 font-monospace" style="width: 140px;" value="<?= $d["doc_root_suffix"] ?? "public_html" ?>" title="Carpeta raiz">
                  <button type="submit" class="btn btn-outline-secondary btn-sm text-uppercase fw-bold text-nowrap">
                    <i class="bi bi-check2"></i>
                  </button>
                </form>
              </td>
              <td>
                <?php if (!empty($d["ssl_enabled"])): ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle">
                    <i class="bi bi-lock-fill me-1"></i>SSL Activo
                  </span>
                  <a href="/web/disable-ssl/<?= (int)$d["id"] ?>" class="btn btn-sm btn-outline-danger py-0 px-1 ms-1" title="Desactivar SSL" onclick="return confirm('Desactivar SSL para <?= $d["domain"] ?>?')">
                    <i class="bi bi-x"></i>
                  </a>
                <?php else: ?>
                  <a href="/web/enable-ssl/<?= (int)$d["id"] ?>" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap" onclick="return confirm('Generar e instalar certificado Let\'s Encrypt para <?= $d["domain"] ?>?')">
                    <i class="bi bi-shield-plus me-1"></i> Activar SSL
                  </a>
                <?php endif; ?>
              </td>
              <td class="text-end pe-3 text-nowrap">
                <div class="d-flex justify-content-end gap-1">
                  <a href="/web/git/<?= (int)$d["id"] ?>" class="btn btn-sm <?= !empty($d["git_id"]) ? "btn-outline-primary" : "btn-outline-secondary" ?> text-uppercase fw-bold text-nowrap" title="Integracion y despliegue Git">
                    <i class="bi bi-github me-1"></i> Git
                    <?php if (!empty($d["git_id"])): ?>
                      <span class="badge bg-success ms-1 p-1"><i class="bi bi-check2"></i></span>
                    <?php endif; ?>
                  </a>
                  <a href="/files?domain=<?= urlencode($d["domain"]) ?>" class="btn btn-sm btn-outline-info text-uppercase fw-bold text-nowrap" title="Explorar y subir archivos">
                    <i class="bi bi-folder2-open me-1"></i> Archivos
                  </a>
                  <a href="/web/delete/<?= (int)$d["id"] ?>" class="btn btn-sm btn-outline-danger text-uppercase fw-bold text-nowrap" onclick="return confirm('Estas seguro de eliminar <?= $d["domain"] ?>?')" title="Eliminar Dominio">
                    <i class="bi bi-trash me-1"></i> Eliminar
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
