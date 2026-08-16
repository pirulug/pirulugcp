<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
  <div>
    <h1 class="h4 mb-0">Configuracion del Servidor</h1>
    <span class="text-muted small">Gestion de nombre de host, zona horaria y actualizaciones del panel mediante GitHub.</span>
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

<?php if ($activeTab === "identity"): ?>
<!-- ======================================================================= -->
<!-- SECCION: IDENTIDAD Y SUBDOMINIO DEL PANEL -->
<!-- ======================================================================= -->
<?php $currentPanelDomain = $serverConfig["panel_domain"] ?? ($settings["panel_domain"] ?? ""); ?>
<div class="row g-3">
  <!-- Subdominio de acceso al panel -->
  <div class="col-md-7">
    <div class="bg-body p-3 rounded mb-3">
      <h6 class="fw-bold mb-3">
        <i class="bi bi-globe me-1"></i> Subdominio de Acceso al Panel (CP)
      </h6>

      <form action="/server/panel-domain" method="POST">
        <div class="mb-3">
          <label for="panel_domain" class="form-label">Subdominio o Dominio del Panel <span class="text-danger">*</span></label>
          <input type="text" class="form-control font-monospace" id="panel_domain" name="panel_domain" value="<?= $currentPanelDomain ?>" placeholder="ej. cp.midominio.com o panel.midominio.com o cp.test" required>
          <div class="form-text small">
            Configura el subdominio por el cual deseas ingresar al panel (ej. <code>cp.midominio.com</code>).
          </div>
        </div>

        <div class="alert alert-info py-2 px-3 small mb-3">
          <i class="bi bi-check-circle me-1"></i>
          <strong>Configuracion Automatica en Nginx:</strong> Al guardar, el servidor web Nginx habilitara el acceso directo tanto en el puerto estandar <strong>80 (HTTP)</strong> como en el puerto <strong>8083</strong> para tu subdominio.
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
          <a href="http://<?= $currentPanelDomain ?>" target="_blank" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
              <strong class="text-primary"><i class="bi bi-box-arrow-up-right me-1"></i> http://<?= $currentPanelDomain ?></strong>
              <span class="badge bg-success-subtle text-success border border-success-subtle">Recomendado</span>
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
          <small class="text-muted">Acceso directo por direccion IP</small>
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
              <option value="<?= $tzKey ?>" <?= ($tzKey === ($serverConfig["timezone"] ?? "UTC")) ? "selected" : "" ?>>
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

      <div class="bg-dark p-3 rounded text-light text-center mb-3">
        <div class="text-muted small text-uppercase">Hora Actual del Servidor</div>
        <h4 class="font-monospace my-1 text-info"><?= $serverConfig["current_time"] ?></h4>
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
                <i class="bi bi-git me-1"></i> <?= $settings["panel_git_branch"] ?>
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

        <label class="form-label small">URL del Webhook del Panel:</label>
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
  <pre class="bg-dark text-light p-3 rounded small mb-0 font-monospace" style="max-height: 250px; overflow-y: auto;"><code><?= $settings["panel_last_update_log"] ?></code></pre>
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
          <input type="text" class="form-control font-monospace" id="panel_git_repo" name="panel_git_repo" placeholder="git@github.com:usuario/pirulugcp.git o https://github.com/..." value="<?= $settings["panel_git_repo"] ?>" required>
          <div class="form-text small">
            Para repositorios privados, usa el formato SSH <code>git@github.com:usuario/repo.git</code>.
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="panel_git_branch" class="form-label">Rama (Branch) <span class="text-danger">*</span></label>
            <input type="text" class="form-control font-monospace" id="panel_git_branch" name="panel_git_branch" value="<?= $settings["panel_git_branch"] ?>" required>
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
        <a href="/server/git/generate-key" class="btn btn-xs btn-outline-warning text-uppercase fw-bold" onclick="return confirm('Generar una nueva clave SSH invalidara la anterior en GitHub. Deseas continuar?')">
          <i class="bi bi-arrow-repeat me-1"></i> Regenerar Clave
        </a>
      </div>

      <p class="text-muted small mb-2">
        Agrega esta clave publica en tu repositorio privado de GitHub para permitir que el servidor descargue las actualizaciones de forma segura sin pedir contraseñas.
      </p>

      <div class="mb-3">
        <label class="form-label small">Clave Publica SSH (Ed25519):</label>
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
