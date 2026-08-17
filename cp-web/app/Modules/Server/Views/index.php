<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
  <div>
    <h1 class="h4 mb-0">Configuracion del Servidor</h1>
    <span class="text-muted small">Gestion de nombre de host, subdominio del panel, certificados SSL Let's Encrypt, zona horaria y actualizaciones.</span>
  </div>
  <div>
    <a href="/server?tab=<?= $activeTab ?>" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
      <i class="bi bi-arrow-clockwise me-1"></i> Refrescar
    </a>
  </div>
</div>

<!-- Navegacion por pestañas del modulo -->
<div class="bg-body p-3 rounded my-3">
  <ul class="nav nav-pills nav-justified">
    <li class="nav-item">
      <a class="nav-link <?= ($activeTab === "identity") ? "active" : "" ?>" href="/server?tab=identity">
        <i class="bi bi-hdd-network me-1"></i>
        Identidad del Servidor (Hostname / IP)
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($activeTab === "ssl") ? "active" : "" ?>" href="/server?tab=ssl">
        <i class="bi bi-shield-check me-1"></i>
        Certificado SSL (Let's Encrypt)
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($activeTab === "timezone") ? "active" : "" ?>" href="/server?tab=timezone">
        <i class="bi bi-clock-history me-1"></i>
        Fecha y Zona Horaria
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($activeTab === "updates") ? "active" : "" ?>" href="/server?tab=updates">
        <i class="bi bi-github me-1"></i>
        Actualizaciones del Panel (GitHub)
      </a>
    </li>
  </ul>
</div>

<?php 
$currentPanelDomain = isset($settings["panel_domain"]) && !empty($settings["panel_domain"]) ? $settings["panel_domain"] : (isset($serverConfig["panel_domain"]) ? $serverConfig["panel_domain"] : "");
$isSslActive = isset($panelSslInfo["ssl_active"]) && $panelSslInfo["ssl_active"] === true;
$isForceHttps = !empty($settings["panel_ssl_force_https"]);
?>

<?php if ($activeTab === "identity"): ?>
<!-- ======================================================================= -->
<!-- SECCION: IDENTIDAD Y SUBDOMINIO DEL PANEL -->
<!-- ======================================================================= -->
<div class="row g-3">
  <!-- Subdominio de acceso al panel -->
  <div class="col-md-7">
    <div class="bg-body p-3 rounded mb-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">
          <i class="bi bi-globe me-1"></i> Subdominio de Acceso al Panel (CP)
        </h6>
        <?php if (!empty($currentPanelDomain)): ?>
          <?php if ($isSslActive): ?>
            <a href="/server?tab=ssl" class="badge bg-success-subtle text-success border border-success-subtle text-decoration-none">
              <i class="bi bi-shield-check me-1"></i> SSL Let's Encrypt Activo
            </a>
          <?php else: ?>
            <a href="/server?tab=ssl" class="badge bg-warning-subtle text-warning border border-warning-subtle text-decoration-none">
              <i class="bi bi-shield-slash me-1"></i> Sin SSL Let's Encrypt
            </a>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <form action="/server/panel-domain" method="POST">
        <div class="mb-3">
          <label for="panel_domain" class="form-label">Subdominio o Dominio del Panel <span class="text-danger">*</span></label>
          <input type="text" class="form-control font-monospace" id="panel_domain" name="panel_domain" value="<?= $currentPanelDomain ?>" placeholder="ej. cp.midominio.com o panel.midominio.com" required>
          <div class="form-text small">
            Configura el subdominio por el cual deseas ingresar al panel (ej. <code>cp.midominio.com</code>).
          </div>
        </div>

        <div class="alert alert-info py-2 px-3 small mb-3">
          <i class="bi bi-check-circle me-1"></i>
          <strong>Configuracion Automatica en Nginx:</strong> Al guardar, el servidor web Nginx habilitara el acceso directo en los puertos <strong>80 (HTTP)</strong> y <strong>443 (HTTPS)</strong> para tu subdominio. Luego podras generar su certificado SSL Let's Encrypt gratuito en la pestaña correspondiente.
        </div>

        <button type="submit" class="btn btn-primary text-uppercase fw-bold">
          <i class="bi bi-globe2 me-1"></i> Guardar Subdominio del Panel
        </button>
      </form>
    </div>

    <!-- Hostname del Sistema -->
    <div class="bg-body p-3 rounded">
      <h6 class="fw-bold mb-3">
        <i class="bi bi-hdd-rack me-1"></i> Nombre de Host del Sistema (Hostname en Linux)
      </h6>

      <form action="/server/hostname" method="POST">
        <div class="mb-3">
          <label for="server_hostname" class="form-label">Hostname del Servidor <span class="text-danger">*</span></label>
          <input type="text" class="form-control font-monospace" id="server_hostname" name="server_hostname" value="<?= $serverConfig["hostname"] ?>" placeholder="ej. srv1.midominio.com o PIRULAP" required>
          <div class="form-text small">
            Nombre interno del nodo o servidor en el sistema operativo Linux (<code>/etc/hostname</code>).
          </div>
        </div>

        <button type="submit" class="btn btn-outline-secondary text-uppercase fw-bold">
          <i class="bi bi-pencil-square me-1"></i> Guardar Hostname
        </button>
      </form>
    </div>
  </div>

  <!-- Informacion de Acceso y Red -->
  <div class="col-md-5">
    <div class="bg-body p-3 rounded h-100">
      <h6 class="fw-bold mb-3">
        <i class="bi bi-link-45deg me-1"></i> URLs de Acceso al Panel
      </h6>

      <div class="list-group mb-3">
        <?php if (!empty($currentPanelDomain)): ?>
          <?php if ($isSslActive): ?>
            <a href="https://<?= $currentPanelDomain ?>" target="_blank" class="list-group-item list-group-item-action">
              <div class="d-flex w-100 justify-content-between">
                <strong class="text-success"><i class="bi bi-lock-fill me-1"></i> https://<?= $currentPanelDomain ?></strong>
                <span class="badge bg-success-subtle text-success border border-success-subtle">SSL Seguro</span>
              </div>
              <small class="text-muted">Acceso directo cifrado mediante HTTPS (Puerto 443)</small>
            </a>
          <?php endif; ?>

          <a href="http://<?= $currentPanelDomain ?>" target="_blank" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
              <strong class="text-primary"><i class="bi bi-box-arrow-up-right me-1"></i> http://<?= $currentPanelDomain ?></strong>
              <?php if (!$isSslActive): ?>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Estándar</span>
              <?php endif; ?>
            </div>
            <small class="text-muted">Acceso directo por subdominio (Puerto 80)</small>
          </a>

          <a href="http://<?= $currentPanelDomain ?>:8083" target="_blank" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
              <strong class="text-body"><i class="bi bi-box-arrow-up-right me-1"></i> http://<?= $currentPanelDomain ?>:8083</strong>
            </div>
            <small class="text-muted">Acceso directo por puerto 8083</small>
          </a>
        <?php endif; ?>

        <a href="http://<?= $serverConfig["server_ip"] ?>:8083" target="_blank" class="list-group-item list-group-item-action">
          <div class="d-flex w-100 justify-content-between">
            <strong class="text-body"><i class="bi bi-box-arrow-up-right me-1"></i> http://<?= $serverConfig["server_ip"] ?>:8083</strong>
          </div>
          <small class="text-muted">Acceso directo por direccion IP del servidor</small>
        </a>
      </div>

      <h6 class="fw-bold mb-2">
        <i class="bi bi-info-square me-1"></i> Parametros del Servidor
      </h6>
      <table class="table table-sm table-borderless mb-0">
        <tbody>
          <tr>
            <td class="text-muted" style="width: 140px;">Subdominio Panel:</td>
            <td class="fw-bold font-monospace text-primary">
              <?= !empty($currentPanelDomain) ? $currentPanelDomain : "<span class='text-muted fw-normal small'>No configurado</span>" ?>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Estado SSL:</td>
            <td>
              <?php if ($isSslActive): ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace">
                  <i class="bi bi-shield-check me-1"></i> Activo (Let's Encrypt)
                </span>
              <?php else: ?>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace">
                  <i class="bi bi-shield-slash me-1"></i> Inactivo
                </span>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Hostname SO:</td>
            <td class="font-monospace text-body"><?= $serverConfig["hostname"] ?></td>
          </tr>
          <tr>
            <td class="text-muted">IP Principal:</td>
            <td><code class="text-body"><?= $serverConfig["server_ip"] ?></code></td>
          </tr>
          <tr>
            <td class="text-muted">Zona Horaria:</td>
            <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><?= $serverConfig["timezone"] ?></span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php elseif ($activeTab === "ssl"): ?>
<!-- ======================================================================= -->
<!-- SECCION: CERTIFICADO SSL LET'S ENCRYPT PARA EL PANEL -->
<!-- ======================================================================= -->
<div class="row g-3">
  <!-- Tarjeta Principal de Administracion de SSL -->
  <div class="col-md-7">
    <div class="bg-body p-3 rounded h-100">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">
          <i class="bi bi-shield-lock me-1"></i> Certificado SSL Gratuito (Let's Encrypt)
        </h6>
        <?php if ($isSslActive): ?>
          <span class="badge bg-success-subtle text-success border border-success-subtle">
            <i class="bi bi-shield-check me-1"></i> SSL Activo (HTTPS Seguro)
          </span>
        <?php else: ?>
          <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
            <i class="bi bi-shield-slash me-1"></i> Sin Certificado SSL
          </span>
        <?php endif; ?>
      </div>

      <?php if (empty($currentPanelDomain)): ?>
        <div class="alert alert-warning py-3 px-3 mb-3">
          <div class="d-flex align-items-center mb-2">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <strong>Subdominio no configurado</strong>
          </div>
          <p class="small mb-3">
            Para emitir un certificado SSL con Let's Encrypt, primero debes definir el subdominio o dominio de acceso en la pestaña de <strong>Identidad del Servidor</strong>.
          </p>
          <a href="/server?tab=identity" class="btn btn-sm btn-warning text-uppercase fw-bold">
            <i class="bi bi-gear me-1"></i> Configurar Subdominio Ahora
          </a>
        </div>
      <?php elseif ($isSslActive && $panelSslInfo): ?>
        <!-- Certificado Emitido y Activo -->
        <div class="alert alert-success py-2 px-3 small mb-3">
          <i class="bi bi-check-circle-fill me-1"></i>
          El subdominio <strong><?= $currentPanelDomain ?></strong> cuenta con un certificado SSL válido emitido por <strong><?= $panelSslInfo["issuer"] ?></strong>.
        </div>

        <table class="table table-sm table-borderless mb-3">
          <tbody>
            <tr>
              <td class="text-muted" style="width: 150px;">Subdominio / CN:</td>
              <td><code class="text-primary fw-bold"><?= $panelSslInfo["subject"] ?></code></td>
            </tr>
            <tr>
              <td class="text-muted">Emisor / Autoridad:</td>
              <td><span class="badge bg-info-subtle text-info border border-info-subtle font-monospace"><?= $panelSslInfo["issuer"] ?></span></td>
            </tr>
            <tr>
              <td class="text-muted">Fecha de Emision:</td>
              <td class="font-monospace small"><?= $panelSslInfo["valid_from"] ?></td>
            </tr>
            <tr>
              <td class="text-muted">Fecha de Expiracion:</td>
              <td class="font-monospace small"><?= $panelSslInfo["expires"] ?></td>
            </tr>
            <tr>
              <td class="text-muted">Vigencia Restante:</td>
              <td>
                <?php if ($panelSslInfo["days_left"] > 30): ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace">
                    <i class="bi bi-calendar-check me-1"></i> <?= $panelSslInfo["days_left"] ?> dias restantes
                  </span>
                <?php else: ?>
                  <span class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace">
                    <i class="bi bi-calendar-x me-1"></i> <?= $panelSslInfo["days_left"] ?> dias restantes (Renovacion proxima)
                  </span>
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <td class="text-muted">Cifrado y Llave:</td>
              <td class="font-monospace small text-body"><?= $panelSslInfo["signature"] ?> (<?= $panelSslInfo["key_size"] ?>)</td>
            </tr>
          </tbody>
        </table>

        <!-- Opcion Forzar Redireccion HTTPS -->
        <hr class="my-3">
        <form action="/server/ssl/force-https" method="POST" class="mb-3">
          <input type="hidden" name="force_https" value="<?= $isForceHttps ? "0" : "1" ?>">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <strong class="d-block">Redireccion Forzada a HTTPS (Puerto 80 a 443)</strong>
              <span class="text-muted small">Redirige automaticamente todo el trafico no seguro (HTTP) a la version cifrada (HTTPS).</span>
            </div>
            <button type="submit" class="btn btn-sm <?= $isForceHttps ? "btn-success" : "btn-outline-secondary" ?> text-uppercase fw-bold text-nowrap ms-3">
              <i class="bi <?= $isForceHttps ? "bi-toggle-on" : "bi-toggle-off" ?> me-1"></i>
              <?= $isForceHttps ? "Habilitado" : "Deshabilitado" ?>
            </button>
          </div>
        </form>

        <!-- Botonera de Acciones para SSL -->
        <hr class="my-3">
        <div class="d-flex flex-wrap justify-content-between gap-2">
          <form action="/server/ssl/issue" method="POST" class="m-0" onsubmit="return confirm('Deseas renovar el certificado Let\'s Encrypt ahora?');">
            <input type="hidden" name="panel_domain" value="<?= $currentPanelDomain ?>">
            <button type="submit" class="btn btn-outline-primary text-uppercase fw-bold">
              <i class="bi bi-arrow-clockwise me-1"></i> Renovar Certificado SSL
            </button>
          </form>

          <form action="/server/ssl/delete" method="POST" class="m-0" onsubmit="return confirm('Seguro que deseas eliminar el certificado SSL? El panel volvera a funcionar unicamente por HTTP.');">
            <button type="submit" class="btn btn-outline-danger text-uppercase fw-bold">
              <i class="bi bi-trash3 me-1"></i> Eliminar Certificado SSL
            </button>
          </form>
        </div>

      <?php else: ?>
        <!-- Formulario para Emitir Certificado SSL Let's Encrypt -->
        <p class="text-muted small mb-3">
          Genera un certificado SSL gratuito y firmado por <strong>Let's Encrypt</strong> para habilitar conexiones cifradas <strong>HTTPS (SSL/TLS)</strong> en tu panel de control PiruluGCP.
        </p>

        <form action="/server/ssl/issue" method="POST">
          <div class="mb-3">
            <label for="ssl_panel_domain" class="form-label">Subdominio o Dominio a Proteger <span class="text-danger">*</span></label>
            <input type="text" class="form-control font-monospace" id="ssl_panel_domain" name="panel_domain" value="<?= $currentPanelDomain ?>" readonly required>
            <div class="form-text small">
              Dominio configurado para el acceso web al panel de control.
            </div>
          </div>

          <div class="mb-3">
            <label for="panel_ssl_email" class="form-label">Correo Electronico para Notificaciones (Opcional)</label>
            <input type="email" class="form-control" id="panel_ssl_email" name="panel_ssl_email" value="<?= isset($settings["panel_ssl_email"]) ? $settings["panel_ssl_email"] : "" ?>" placeholder="ej. admin@tudominio.com">
            <div class="form-text small">
              Let's Encrypt enviara recordatorios en caso de incidencias en la renovacion automatica.
            </div>
          </div>

          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="panel_ssl_force_https" name="panel_ssl_force_https" value="1" <?= $isForceHttps ? "checked" : "" ?>>
              <label class="form-check-label" for="panel_ssl_force_https">
                Forzar redireccion automatica HTTP a HTTPS tras la emision exitosa
              </label>
            </div>
          </div>

          <div class="alert alert-info py-2 px-3 small mb-3">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Requisito Previo de DNS:</strong> El registro DNS tipo <code>A</code> de <strong><?= $currentPanelDomain ?></strong> debe apuntar a la IP publica de tu servidor (<code><?= $serverConfig["server_ip"] ?></code>) y los puertos <strong>80</strong> y <strong>443</strong> deben estar abiertos en el firewall.
          </div>

          <button type="submit" class="btn btn-primary text-uppercase fw-bold">
            <i class="bi bi-shield-lock-fill me-1"></i> Generar Certificado Let's Encrypt
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <!-- Columna Informativa de Acceso y Validacion DNS -->
  <div class="col-md-5">
    <div class="bg-body p-3 rounded h-100">
      <h6 class="fw-bold mb-3">
        <i class="bi bi-globe-americas me-1"></i> Verificacion DNS y Conectividad
      </h6>

      <div class="list-group mb-3">
        <div class="list-group-item">
          <div class="d-flex justify-content-between align-items-center">
            <strong class="small">Registro DNS Requerido:</strong>
            <span class="badge bg-primary-subtle text-primary font-monospace">Tipo A</span>
          </div>
          <div class="font-monospace small mt-1">
            <?= !empty($currentPanelDomain) ? $currentPanelDomain : "cp.tudominio.com" ?> &rarr; <?= $serverConfig["server_ip"] ?>
          </div>
        </div>
        <div class="list-group-item">
          <div class="d-flex justify-content-between align-items-center">
            <strong class="small">Metodo de Validacion:</strong>
            <span class="badge bg-secondary-subtle text-secondary font-monospace">HTTP-01 (Webroot)</span>
          </div>
          <div class="small text-muted mt-1">
            Ruta ACME: <code>cp-web/public/.well-known/acme-challenge/</code>
          </div>
        </div>
        <div class="list-group-item">
          <div class="d-flex justify-content-between align-items-center">
            <strong class="small">Renovacion Automatica:</strong>
            <span class="badge bg-success-subtle text-success font-monospace">Certbot Timer Activo</span>
          </div>
          <div class="small text-muted mt-1">
            Los certificados de Let's Encrypt se renuevan automaticamente cada 60 dias.
          </div>
        </div>
      </div>

      <h6 class="fw-bold mb-2">
        <i class="bi bi-shield-check me-1"></i> Ventajas de SSL en el Panel
      </h6>
      <ul class="text-muted small mb-0 ps-3">
        <li class="mb-1">Cifrado de credenciales de inicio de sesion y cookies de autenticacion.</li>
        <li class="mb-1">Proteccion contra ataques de intermediario (Man-in-the-Middle) en redes publicas.</li>
        <li class="mb-1">Compatibilidad total con navegadores modernos eliminando avisos de sitio no seguro.</li>
        <li>Acceso directo sin necesidad de recordar puertos adicionales.</li>
      </ul>
    </div>
  </div>
</div>

<?php elseif ($activeTab === "timezone"): ?>
<!-- ======================================================================= -->
<!-- SECCION: FECHA Y ZONA HORARIA -->
<!-- ======================================================================= -->
<div class="row g-3">
  <div class="col-md-7">
    <div class="bg-body p-3 rounded h-100">
      <h6 class="fw-bold mb-3">
        <i class="bi bi-globe me-1"></i> Configurar Zona Horaria
      </h6>

      <form action="/server/timezone" method="POST">
        <div class="mb-3">
          <label for="server_timezone" class="form-label">Seleccionar Zona Horaria del Servidor <span class="text-danger">*</span></label>
          <select class="form-select font-monospace" id="server_timezone" name="server_timezone" required>
            <option value="">-- Seleccionar Zona Horaria --</option>
            <?php foreach ($timezones as $tzKey => $tzLabel): ?>
              <option value="<?= $tzKey ?>" <?= ($tzKey === (isset($serverConfig["timezone"]) ? $serverConfig["timezone"] : "UTC")) ? "selected" : "" ?>>
                <?= $tzLabel ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="form-text small">
            Define la zona horaria oficial tanto para el sistema operativo Linux como para todas las versiones de PHP (PHP-FPM y CLI).
          </div>
        </div>

        <div class="alert alert-info py-2 px-3 small mb-3">
          <i class="bi bi-clock me-1"></i>
          <strong>Sincronizacion Automatica:</strong> Al cambiar la zona horaria, se actualizan <code>/etc/localtime</code>, <code>timedatectl</code> y la directiva <code>date.timezone</code> en todos los archivos <code>php.ini</code> instalados.
        </div>

        <button type="submit" class="btn btn-primary text-uppercase fw-bold">
          <i class="bi bi-check2 me-1"></i> Aplicar Zona Horaria
        </button>
      </form>
    </div>
  </div>

  <div class="col-md-5">
    <div class="bg-body p-3 rounded h-100">
      <h6 class="fw-bold mb-3">
        <i class="bi bi-clock-history me-1"></i> Reloj y Estado del Tiempo
      </h6>

      <div class="bg-body-tertiary p-3 rounded border text-center mb-3">
        <div class="text-muted small text-uppercase">Hora Actual del Servidor</div>
        <h4 class="font-monospace my-1 text-primary"><?= $serverConfig["current_time"] ?></h4>
        <span class="badge bg-secondary font-monospace"><?= $serverConfig["timezone"] ?></span>
      </div>

      <p class="text-muted small mb-0">
        Mantener la zona horaria correctamente configurada garantiza la precision de los logs de acceso, tareas programadas (cron jobs) y marcas de tiempo en bases de datos.
      </p>
    </div>
  </div>
</div>

<?php elseif ($activeTab === "updates"): ?>
<!-- ======================================================================= -->
<!-- SECCION: ACTUALIZACIONES DEL PANEL (GITHUB) -->
<!-- ======================================================================= -->
<?php $isGitConnected = !empty($serverConfig["git_connected"]); ?>

<!-- Estado actual de la version y actualizaciones -->
<div class="row g-3 mb-3">
  <div class="col-md-8">
    <div class="bg-body p-3 rounded h-100">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">
          <i class="bi bi-diagram-2 me-1"></i> Repositorio de Actualizaciones del Panel
        </h6>
        <?php if ($isGitConnected): ?>
          <span class="badge bg-success-subtle text-success border border-success-subtle">
            <i class="bi bi-check-circle me-1"></i> Repositorio Vinculado
          </span>
        <?php else: ?>
          <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
            <i class="bi bi-exclamation-circle me-1"></i> No vinculado
          </span>
        <?php endif; ?>
      </div>

      <table class="table table-sm table-borderless mb-0">
        <tbody>
          <tr>
            <td class="text-muted" style="width: 150px;">Repositorio Remoto:</td>
            <td>
              <?php if (!empty($serverConfig["remote_url"])): ?>
                <code class="text-primary"><?= $serverConfig["remote_url"] ?></code>
              <?php else: ?>
                <span class="text-muted small">Sin repositorio configurado</span>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Rama de Despliegue:</td>
            <td>
              <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace">
                <i class="bi bi-git me-1"></i> <?= isset($settings["panel_git_branch"]) ? $settings["panel_git_branch"] : "main" ?>
              </span>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Ultimo Commit:</td>
            <td>
              <?php if (!empty($serverConfig["last_commit"])): ?>
                <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace">
                  <?= substr($serverConfig["last_commit"], 0, 7) ?>
                </span>
                <span class="ms-1 fw-bold"><?= $serverConfig["last_message"] ?></span>
                <span class="text-muted small ms-1">(por <?= $serverConfig["last_author"] ?>)</span>
              <?php else: ?>
                <span class="text-muted small">Sin informacion de version</span>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Ultima Actualizacion:</td>
            <td>
              <?php if (!empty($settings["panel_last_update_at"])): ?>
                <span><?= $settings["panel_last_update_at"] ?></span>
                <span class="badge ms-2 <?= ($settings["panel_last_update_status"] === "success") ? "bg-success-subtle text-success border border-success-subtle" : "bg-danger-subtle text-danger border border-danger-subtle" ?>">
                  <?= strtoupper($settings["panel_last_update_status"]) ?>
                </span>
              <?php else: ?>
                <span class="text-muted small">Nunca</span>
              <?php endif; ?>
            </td>
          </tr>
        </tbody>
      </table>

      <?php if ($isGitConnected): ?>
        <hr class="my-3">
        <div class="d-flex justify-content-between align-items-center">
          <form action="/server/git/update" method="POST" class="d-inline m-0" onsubmit="return confirm('Deseas descargar y aplicar las ultimas actualizaciones del panel? Los servicios se reiniciaran automaticamente.');">
            <button type="submit" class="btn btn-sm btn-primary text-uppercase fw-bold">
              <i class="bi bi-cloud-arrow-down me-1"></i> Actualizar Panel Ahora
            </button>
          </form>
          <span class="text-muted small">Descarga los cambios y reinicia el servicio del panel.</span>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Webhook para Auto-Actualizacion del Panel -->
  <div class="col-md-4">
    <div class="bg-body p-3 rounded h-100 d-flex flex-column justify-content-between">
      <div>
        <h6 class="fw-bold mb-2">
          <i class="bi bi-lightning-charge me-1"></i> Auto-Actualizacion (Webhook)
        </h6>
        <p class="text-muted small mb-3">
          Configura este Webhook en tu repositorio de GitHub para que el panel se actualice automaticamente en cada <code>git push</code>.
        </p>

        <label for="panel_webhook_url" class="form-label">URL del Webhook del Panel:</label>
        <div class="input-group input-group-sm mb-3">
          <input type="text" class="form-control font-monospace" id="panel_webhook_url" value="<?= $webhookUrl ?>" readonly>
          <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('panel_webhook_url', 'URL de Webhook copiada al portapapeles');">
            <i class="bi bi-clipboard"></i>
          </button>
        </div>

        <div class="alert alert-secondary py-2 px-3 small mb-0">
          <strong>En GitHub (Settings &gt; Webhooks):</strong><br>
          Pega la URL y en Content type selecciona <code>application/json</code>.
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Consola de Resultados y Log de Actualizacion -->
<?php if (!empty($settings["panel_last_update_log"])): ?>
<div class="bg-body p-3 rounded mb-3">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="fw-bold mb-0">
      <i class="bi bi-terminal me-1"></i> Consola de Resultados de Actualizacion del Panel
    </h6>
    <?php if (!empty($settings["panel_last_update_status"])): ?>
      <span class="badge <?= ($settings["panel_last_update_status"] === "success") ? "bg-success-subtle text-success border border-success-subtle" : "bg-danger-subtle text-danger border border-danger-subtle" ?>">
        <i class="bi <?= ($settings["panel_last_update_status"] === "success") ? "bi-check-circle" : "bi-exclamation-triangle" ?> me-1"></i>
        <?= strtoupper($settings["panel_last_update_status"]) ?>
      </span>
    <?php endif; ?>
  </div>
  <pre class="bg-body-tertiary text-body p-3 rounded border small mb-0 font-monospace" style="max-height: 250px; overflow-y: auto;"><code><?= $settings["panel_last_update_log"] ?></code></pre>
</div>
<?php endif; ?>

<div class="row g-3">
  <!-- Formulario de conexion / configuracion del repositorio -->
  <div class="col-md-6">
    <div class="bg-body p-3 rounded h-100">
      <h6 class="fw-bold mb-3">
        <i class="bi bi-gear me-1"></i> <?= $isGitConnected ? "Actualizar Repositorio del Panel" : "Vincular Repositorio del Panel" ?>
      </h6>

      <form action="/server/git/connect" method="POST">
        <div class="mb-3">
          <label for="panel_git_repo" class="form-label">URL del Repositorio de GitHub <span class="text-danger">*</span></label>
          <input type="text" class="form-control font-monospace" id="panel_git_repo" name="panel_git_repo" placeholder="git@github.com:usuario/pirulugcp.git o https://github.com/..." value="<?= isset($settings["panel_git_repo"]) ? $settings["panel_git_repo"] : "" ?>" required>
          <div class="form-text small">
            Para repositorios privados, usa el formato SSH <code>git@github.com:usuario/repo.git</code>.
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="panel_git_branch" class="form-label">Rama (Branch) <span class="text-danger">*</span></label>
            <input type="text" class="form-control font-monospace" id="panel_git_branch" name="panel_git_branch" value="<?= isset($settings["panel_git_branch"]) ? $settings["panel_git_branch"] : "main" ?>" required>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">Tipo de Repositorio</label>
            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" id="panel_git_is_private" name="panel_git_is_private" value="1" <?= (!empty($settings["panel_git_is_private"])) ? "checked" : "" ?>>
              <label class="form-check-label" for="panel_git_is_private">
                Privado (Usa Clave SSH)
              </label>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="panel_auto_update" name="panel_auto_update" value="1" <?= (!empty($settings["panel_auto_update"])) ? "checked" : "" ?>>
            <label class="form-check-label" for="panel_auto_update">
              Habilitar Auto-Actualizacion del Panel mediante Webhook
            </label>
          </div>
        </div>

        <button type="submit" class="btn btn-primary text-uppercase fw-bold w-100">
          <i class="bi bi-link-45deg me-1"></i> <?= $isGitConnected ? "Guardar Configuracion" : "Vincular y Descargar Actualizaciones" ?>
        </button>
      </form>
    </div>
  </div>

  <!-- Clave SSH Deploy Key para GitHub Privado -->
  <div class="col-md-6">
    <div class="bg-body p-3 rounded h-100">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">
          <i class="bi bi-key me-1"></i> Clave SSH de Actualizacion (Deploy Key)
        </h6>
        <a href="/server/git/generate-key" class="btn btn-sm btn-outline-warning text-uppercase fw-bold" onclick="return confirm('Generar una nueva clave SSH invalidara la anterior en GitHub. Deseas continuar?')">
          <i class="bi bi-arrow-repeat me-1"></i> Regenerar Clave
        </a>
      </div>

      <p class="text-muted small mb-2">
        Agrega esta clave publica en tu repositorio privado de GitHub para permitir que el servidor descargue las actualizaciones de forma segura sin pedir contraseñas.
      </p>

      <div class="mb-3">
        <label for="panel_ssh_key_text" class="form-label">Clave Publica SSH (Ed25519):</label>
        <textarea class="form-control font-monospace small" id="panel_ssh_key_text" rows="3" readonly><?= $serverConfig["public_key"] ?></textarea>
      </div>

      <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold w-100 mb-3" onclick="copyToClipboard('panel_ssh_key_text', 'Clave SSH publica copiada al portapapeles.');">
        <i class="bi bi-clipboard me-1"></i> Copiar Clave SSH Publica
      </button>

      <div class="alert alert-secondary py-2 px-3 small mb-0">
        <strong>Instrucciones para GitHub:</strong><br>
        1. En tu repositorio privado ve a <strong>Settings &gt; Deploy keys</strong>.<br>
        2. Clic en <strong>Add deploy key</strong>.<br>
        3. Pega la clave y titúlala <code>PiruluGCP-Server-Deploy</code>.<br>
        4. No necesitas activar permisos de escritura (solo lectura).
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

