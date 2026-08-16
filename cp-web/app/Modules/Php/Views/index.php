<?php
$installed = $installedVersions ?? [];
$available = $availableToInstall ?? [];
$active = $activeVer ?? "8.5";
$tab = $activeTab ?? "logs";
$domains = $activeDomains ?? [];
$extList = $extensions ?? [];
?>

<!-- Titulo y descripcion del modulo -->
<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
  <div>
    <h1 class="h4 mb-0">Gestor de PHP Multi-Version</h1>
    <span class="text-muted small">Supervisa procesos en vivo, edita directivas php.ini y gestiona versiones bajo demanda.</span>
  </div>
  <div>
    <button type="button" class="btn btn-sm btn-primary text-uppercase fw-bold text-nowrap" data-bs-toggle="modal" data-bs-target="#modalInstallPhp">
      <i class="bi bi-plus-lg me-1"></i> Instalar Otra Version
    </button>
  </div>
</div>

<!-- Barra Superior: Tarjetas de Versiones PHP instaladas + Boton Instalar -->
<div class="d-flex gap-3 overflow-auto pb-2 mb-3 align-items-stretch" style="scrollbar-width: thin;">
  <?php if (empty($installed)): ?>
    <div class="bg-body p-3 rounded text-muted small">No hay versiones de PHP instaladas actualmente.</div>
  <?php else: ?>
    <?php foreach ($installed as $php): ?>
      <?php 
        $isCurrent = ($php["version"] === $active);
        $isActive = (($php["status"] ?? "") === "active");
        $isBase = ($php["version"] === "8.5");
      ?>
      <a href="/php?ver=<?= $php["version"] ?>&tab=<?= $tab ?>" 
         class="bg-body p-3 rounded text-decoration-none d-flex flex-column justify-content-between transition-all"
         style="min-width: 175px; border: <?= $isCurrent ? "2px solid var(--bs-primary)" : "1px solid var(--bs-border-color)" ?> !important;">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1 d-inline-flex align-items-center">
            <img src="/assets/sitios/php.svg" alt="PHP" style="width: 16px; height: 16px; object-fit: contain;" class="me-1">
            PHP
          </span>
          <?php if ($isActive): ?>
            <span class="small fw-semibold text-success d-inline-flex align-items-center">
              <i class="bi bi-circle-fill me-1" style="font-size: 8px !important;"></i> Ejecutando
            </span>
          <?php else: ?>
            <span class="small fw-semibold text-secondary d-inline-flex align-items-center">
              <i class="bi bi-circle me-1" style="font-size: 8px !important;"></i> Detenido
            </span>
          <?php endif; ?>
        </div>
        <div class="d-flex align-items-center justify-content-between">
          <div class="fs-2 fw-bold text-body">
            <?= $php["version"] ?>
            <?php if ($isBase): ?>
              <i class="bi bi-star-fill text-danger fs-6 align-top" title="Version Base del Sistema"></i>
            <?php endif; ?>
          </div>
          <?php if (isset($usageStats[$php["version"]]) && $usageStats[$php["version"]] > 0): ?>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace small">
              <?= $usageStats[$php["version"]] ?> <?= ($usageStats[$php["version"]] == 1) ? "web" : "webs" ?>
            </span>
          <?php endif; ?>
        </div>
      </a>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- Tarjeta de Accion: Instalar PHP -->
  <div class="bg-body p-3 rounded d-flex flex-column align-items-center justify-content-center text-decoration-none text-muted"
       style="min-width: 160px; border: 2px dashed var(--bs-border-color) !important; cursor: pointer;"
       data-bs-toggle="modal" data-bs-target="#modalInstallPhp"
       title="Instalar nueva version de PHP">
    <i class="bi bi-plus-lg fs-3 mb-1 text-primary"></i>
    <span class="small fw-bold text-uppercase text-body">Instalar PHP</span>
  </div>
</div>

<!-- Barra de Herramientas y Navegacion de Pestanas para la version activa -->
<div class="bg-body p-3 rounded mb-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
  <!-- Pestanas Izquierda -->
  <ul class="nav nav-pills gap-1 m-0">
    <li class="nav-item">
      <a class="nav-link <?= ($tab === "logs") ? "active fw-bold" : "text-body" ?>" href="/php?ver=<?= $active ?>&tab=logs">
        <i class="bi bi-terminal me-1"></i> Registros
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($tab === "ini") ? "active fw-bold" : "text-body" ?>" href="/php?ver=<?= $active ?>&tab=ini">
        <i class="bi bi-sliders me-1"></i> Ini
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($tab === "ports") ? "active fw-bold" : "text-body" ?>" href="/php?ver=<?= $active ?>&tab=ports">
        <i class="bi bi-hdd-network me-1"></i> Puertos
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($tab === "extensions") ? "active fw-bold" : "text-body" ?>" href="/php?ver=<?= $active ?>&tab=extensions">
        <i class="bi bi-puzzle me-1"></i> Extensiones
      </a>
    </li>
  </ul>

  <!-- Indicadores y Botones Derecha -->
  <div class="d-flex align-items-center gap-2">
    <!-- Contador de Dominios Asignados -->
    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 font-monospace small">
      <i class="bi bi-link-45deg me-1"></i> <?= count($domains) ?> <?= (count($domains) == 1) ? "dominio" : "dominios" ?>
    </span>

    <!-- Boton Buscar Actualizaciones / Refrescar -->
    <a href="/php?ver=<?= $active ?>&tab=<?= $tab ?>" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap" title="Recargar datos de PHP <?= $active ?>">
      <i class="bi bi-arrow-clockwise me-1"></i> Buscar actualizaciones
    </a>

    <!-- Dropdown de Acciones del Servicio -->
    <div class="dropdown">
      <button class="btn btn-sm btn-outline-primary dropdown-toggle text-uppercase fw-bold" type="button" data-bs-toggle="dropdown">
        Acciones
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <a class="dropdown-item" href="/php/restart/<?= $active ?>" onclick="return confirm('¿Reiniciar el servicio PHP-FPM <?= $active ?>?')">
            <i class="bi bi-arrow-repeat me-2 text-warning"></i> Reiniciar Servicio
          </a>
        </li>
        <li>
          <a class="dropdown-item" href="/php?ver=<?= $active ?>&tab=ini">
            <i class="bi bi-file-earmark-code me-2 text-info"></i> Editar php.ini
          </a>
        </li>
        <?php if ($active !== "8.5" && count($domains) === 0): ?>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item text-danger" href="/php/uninstall/<?= $active ?>" onclick="return confirm('¿Deseas desinstalar por completo PHP <?= $active ?> del servidor?')">
              <i class="bi bi-trash me-2"></i> Desinstalar PHP <?= $active ?>
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</div>

<!-- ======================================================================= -->
<!-- CONTENIDO DE LA PESTANA: REGISTROS (LOGS)                                -->
<!-- ======================================================================= -->
<?php if ($tab === "logs"): ?>
  <div class="bg-body p-3 rounded mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 font-monospace small">
          <i class="bi bi-circle-fill me-1" style="font-size: 8px !important;"></i> en vivo
        </span>
        <span class="text-muted small">Registros de PHP-FPM <?= $active ?> (<code>/var/log/php<?= $active ?>-fpm.log</code>)</span>
      </div>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" onclick="clearTerminal()" title="Limpiar pantalla">
          <i class="bi bi-eraser me-1"></i> Limpiar
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" onclick="copyTerminal()" title="Copiar registros">
          <i class="bi bi-clipboard me-1"></i> Copiar
        </button>
        <a href="/php?ver=<?= $active ?>&tab=logs" class="btn btn-sm btn-outline-primary text-uppercase fw-bold" title="Actualizar registros">
          <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
        </a>
      </div>
    </div>

    <!-- Consola de Registros -->
    <pre id="phpTerminalLog" 
         class="p-3 rounded font-monospace small mb-0" 
         style="background-color: #0b0f19; color: #4ade80; min-height: 380px; max-height: 520px; overflow-y: auto; white-space: pre-wrap; line-height: 1.6; border: 1px solid rgba(255,255,255,0.08);"><?= !empty($rawLogs) ? $rawLogs : "[Sin entradas recientes en el registro de PHP-FPM " . $active . "]" ?></pre>
  </div>

  <script>
  function clearTerminal() {
    document.getElementById("phpTerminalLog").textContent = "[Consola limpiada]";
  }

  function copyTerminal() {
    const text = document.getElementById("phpTerminalLog").textContent;
    navigator.clipboard.writeText(text).then(() => {
      alert("Registros copiados al portapapeles.");
    });
  }

  const term = document.getElementById("phpTerminalLog");
  if (term) {
    term.scrollTop = term.scrollHeight;
  }
  </script>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- CONTENIDO DE LA PESTANA: INI (CONFIGURACION PHP.INI ESTILO 2 COLUMNAS)   -->
<!-- ======================================================================= -->
<?php if ($tab === "ini"): ?>
  <?php
    $leftIniContent = "; PiruluGCP php.ini overrides - PHP " . $active . "\n" .
                      "memory_limit = " . ($ini["memory_limit"] ?? "512M") . "\n" .
                      "max_execution_time = " . ($ini["max_execution_time"] ?? "120") . "\n" .
                      "upload_max_filesize = " . ($ini["upload_max_filesize"] ?? "64M") . "\n" .
                      "post_max_size = " . ($ini["post_max_size"] ?? "64M") . "\n" .
                      "display_errors = " . ($ini["display_errors"] ?? "Off") . "\n" .
                      "error_reporting = " . ($ini["error_reporting"] ?? "E_ALL & ~E_DEPRECATED") . "\n\n" .
                      "[opcache]\n" .
                      "opcache.enable = 1\n" .
                      "opcache.jit = tracing\n" .
                      "opcache.jit_buffer_size = 64M\n";

    $defaultLeftIni = "; PiruluGCP php.ini overrides - PHP " . $active . "\n" .
                      "memory_limit = 512M\n" .
                      "max_execution_time = 120\n" .
                      "upload_max_filesize = 64M\n" .
                      "post_max_size = 64M\n" .
                      "display_errors = Off\n" .
                      "error_reporting = E_ALL & ~E_DEPRECATED\n\n" .
                      "[opcache]\n" .
                      "opcache.enable = 1\n" .
                      "opcache.jit = tracing\n" .
                      "opcache.jit_buffer_size = 64M\n";
  ?>
  <div class="row">
    <!-- COLUMNA IZQUIERDA: Directivas de la version activa -->
    <div class="col-lg-6 mb-3">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h6 class="mb-0 fw-bold">PHP <?= $active ?> <span class="text-muted small fw-normal ms-1">solo esta versión</span></h6>
            <span class="font-monospace small text-muted">/etc/php/<?= $active ?>/fpm/php.ini</span>
          </div>
          <div class="d-flex gap-1">
            <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" onclick="copyIni('editorLeft')" title="Copiar al portapapeles">
              <i class="bi bi-clipboard me-1"></i> Copiar
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" onclick="resetIni('editorLeft')" title="Restablecer directivas por defecto">
              <i class="bi bi-arrow-counterclockwise me-1"></i> Restablecer
            </button>
          </div>
        </div>
        <div class="card-body">
          <form action="/php/config/<?= $active ?>/save" method="POST" id="formLeftIni">
            <!-- Wrapper del Editor de Codigo con Numeros de Linea -->
            <div class="d-flex rounded border mb-3" style="background-color: #0b0f19; min-height: 420px; border-color: rgba(255,255,255,0.08) !important;">
              <div class="px-2 py-3 text-end user-select-none font-monospace small" style="background-color: #070a12; color: #484f58; min-width: 40px; border-right: 1px solid rgba(255,255,255,0.08); line-height: 1.6;">
                1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br>10<br>11<br>12<br>13<br>14<br>15<br>16<br>17<br>18
              </div>
              <textarea id="editorLeft" 
                        name="custom_ini_content" 
                        class="form-control border-0 p-3 font-monospace small" 
                        style="background-color: transparent; color: #58a6ff; resize: vertical; line-height: 1.6; min-height: 420px; outline: none; box-shadow: none;" 
                        spellcheck="false"><?= $leftIniContent ?></textarea>
            </div>

            <div class="d-flex justify-content-end">
              <button type="button" onclick="submitLeftDirectives()" class="btn btn-primary px-4 text-uppercase fw-bold">
                <i class="bi bi-floppy me-2"></i> Guardar Cambios
              </button>
            </div>

            <!-- Campos ocultos para enviar parametros estructurados -->
            <input type="hidden" id="hidden_memory_limit" name="memory_limit" value="<?= $ini["memory_limit"] ?? "512M" ?>">
            <input type="hidden" id="hidden_max_execution_time" name="max_execution_time" value="<?= $ini["max_execution_time"] ?? "120" ?>">
            <input type="hidden" id="hidden_upload_max_filesize" name="upload_max_filesize" value="<?= $ini["upload_max_filesize"] ?? "64M" ?>">
            <input type="hidden" id="hidden_post_max_size" name="post_max_size" value="<?= $ini["post_max_size"] ?? "64M" ?>">
            <input type="hidden" id="hidden_display_errors" name="display_errors" value="<?= $ini["display_errors"] ?? "Off" ?>">
            <input type="hidden" id="hidden_error_reporting" name="error_reporting" value="<?= $ini["error_reporting"] ?? "E_ALL & ~E_DEPRECATED" ?>">
          </form>
        </div>
      </div>
    </div>

    <!-- COLUMNA DERECHA: Editor Maestro php.ini Completo -->
    <div class="col-lg-6 mb-3">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h6 class="mb-0 fw-bold">COMPARTIDO (TODAS LAS VERSIONES)</h6>
            <span class="font-monospace small text-muted">/usr/local/pirulugcp/config/php-fpm.conf</span>
          </div>
          <div class="d-flex gap-1">
            <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" onclick="copyIni('editorRight')" title="Copiar al portapapeles">
              <i class="bi bi-clipboard me-1"></i> Copiar
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" onclick="resetIni('editorRight')" title="Restablecer archivo">
              <i class="bi bi-arrow-counterclockwise me-1"></i> Restablecer
            </button>
          </div>
        </div>
        <div class="card-body">
          <form action="/php/config/<?= $active ?>/raw" method="POST">
            <div class="d-flex rounded border mb-3" style="background-color: #0b0f19; min-height: 420px; border-color: rgba(255,255,255,0.08) !important;">
              <textarea id="editorRight" 
                        name="raw_ini_content" 
                        class="form-control border-0 p-3 font-monospace small" 
                        style="background-color: transparent; color: #7ee787; resize: vertical; line-height: 1.6; min-height: 420px; outline: none; box-shadow: none;" 
                        spellcheck="false"><?= !empty($rawIni) ? $rawIni : $leftIniContent ?></textarea>
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-warning px-4 text-uppercase fw-bold">
                <i class="bi bi-floppy me-2"></i> Guardar Archivo Maestro
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
  function copyIni(editorId) {
    const text = document.getElementById(editorId).value;
    navigator.clipboard.writeText(text).then(() => {
      alert("Configuracion copiada al portapapeles.");
    });
  }

  function resetIni(editorId) {
    if (confirm("¿Deseas restablecer las directivas a los valores recomendados por defecto?")) {
      if (editorId === "editorLeft") {
        document.getElementById("editorLeft").value = <?= json_encode($defaultLeftIni) ?>;
      } else {
        document.getElementById("editorRight").value = <?= json_encode($rawIni) ?>;
      }
    }
  }

  function submitLeftDirectives() {
    const lines = document.getElementById("editorLeft").value.split("\n");
    lines.forEach(line => {
      const parts = line.split("=");
      if (parts.length >= 2) {
        const key = parts[0].trim();
        const val = parts.slice(1).join("=").trim();
        if (key === "memory_limit") document.getElementById("hidden_memory_limit").value = val;
        if (key === "max_execution_time") document.getElementById("hidden_max_execution_time").value = val;
        if (key === "upload_max_filesize") document.getElementById("hidden_upload_max_filesize").value = val;
        if (key === "post_max_size") document.getElementById("hidden_post_max_size").value = val;
        if (key === "display_errors") document.getElementById("hidden_display_errors").value = val;
        if (key === "error_reporting") document.getElementById("hidden_error_reporting").value = val;
      }
    });
    document.getElementById("formLeftIni").submit();
  }
  </script>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- CONTENIDO DE LA PESTANA: PUERTOS Y POOLS                                 -->
<!-- ======================================================================= -->
<?php if ($tab === "ports"): ?>
  <div class="row">
    <div class="col-md-5 mb-3">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title mb-0">Parametros de Red y Sockets (PHP <?= $active ?>)</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Socket Unix Maestro</label>
            <div class="font-monospace p-2 rounded border bg-body-tertiary">
              /run/php/php<?= $active ?>-fpm.sock
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Directorio de Pools de Clientes</label>
            <div class="font-monospace p-2 rounded border bg-body-tertiary">
              /etc/php/<?= $active ?>/fpm/pool.d/
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Servicio Systemd</label>
            <div class="font-monospace p-2 rounded border bg-body-tertiary">
              php<?= $active ?>-fpm.service
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-7 mb-3">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Dominios Asignados a PHP <?= $active ?></h5>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">
            <?= count($domains) ?> <?= (count($domains) == 1) ? "dominio" : "dominios" ?>
          </span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle table-sm m-0">
              <thead>
                <tr>
                  <th class="ps-3">Dominio</th>
                  <th>Propietario</th>
                  <th>Socket Asignado</th>
                  <th class="text-end pe-3">Gestionar</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($domains)): ?>
                  <tr>
                    <td colspan="4" class="text-center py-4 text-muted">
                      No hay ningun dominio web ejecutandose bajo PHP <?= $active ?> actualmente.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($domains as $d): ?>
                    <tr>
                      <td class="ps-3 fw-bold">
                        <i class="bi bi-globe2 me-1 text-primary"></i> <?= $d["domain"] ?>
                      </td>
                      <td><code><?= $d["username"] ?? "admin" ?></code></td>
                      <td class="font-monospace small text-muted">/run/php/<?= ($d["username"] ?? "admin") ?>_<?= $d["domain"] ?>.sock</td>
                      <td class="text-end pe-3">
                        <a href="/web" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
                          <i class="bi bi-gear me-1"></i> Ver Dominio
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- CONTENIDO DE LA PESTANA: EXTENSIONES                                     -->
<!-- ======================================================================= -->
<?php if ($tab === "extensions"): ?>
  <div class="bg-body p-3 rounded mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
      <div>
        <h5 class="mb-0">Modulos y Extensiones Cargadas (PHP <?= $active ?>)</h5>
        <span class="text-muted small">Total de <?= count($extList) ?> modulos compilados y activos en esta version.</span>
      </div>
      <div style="min-width: 250px;">
        <input type="text" id="extSearchInput" class="form-control form-control-sm" placeholder="Buscar extension (ej. curl, mysqli, zip)..." onkeyup="filterExtensions()">
      </div>
    </div>

    <div class="row g-2" id="extensionsGrid">
      <?php if (empty($extList)): ?>
        <div class="col-12 text-muted text-center py-4">No se detectaron extensiones disponibles para PHP <?= $active ?>.</div>
      <?php else: ?>
        <?php foreach ($extList as $ext): ?>
          <div class="col-6 col-md-4 col-lg-3 ext-item" data-name="<?= strtolower($ext) ?>">
            <div class="p-2 rounded border bg-body-tertiary d-flex align-items-center justify-content-between">
              <span class="font-monospace fw-bold small text-body">
                <i class="bi bi-check2 text-success me-1"></i> <?= $ext ?>
              </span>
              <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;">Activo</span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <script>
  function filterExtensions() {
    const q = document.getElementById("extSearchInput").value.toLowerCase();
    const items = document.querySelectorAll(".ext-item");
    items.forEach(el => {
      const name = el.getAttribute("data-name") || "";
      if (name.includes(q)) {
        el.style.display = "";
      } else {
        el.style.display = "none";
      }
    });
  }
  </script>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- MODAL: INSTALAR VERSION DE PHP BAJO DEMANDA                              -->
<!-- ======================================================================= -->
<div class="modal fade" id="modalInstallPhp" tabindex="-1" aria-labelledby="modalInstallPhpLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalInstallPhpLabel">
          <i class="bi bi-download me-2 text-primary"></i> Instalar Version de PHP
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">
          Selecciona una version de PHP para descargar e instalarla junto a su stack completo de extensiones en el servidor.
        </p>

        <?php if (empty($available)): ?>
          <div class="alert alert-info py-2 small mb-0">
            <i class="bi bi-info-circle me-1"></i> Todas las versiones de PHP soportadas ya se encuentran instaladas en este servidor.
          </div>
        <?php else: ?>
          <div class="list-group">
            <?php foreach ($available as $av): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                <div>
                  <h6 class="mb-0 fw-bold">PHP <?= $av["version"] ?></h6>
                  <span class="text-muted small">PHP-FPM, CLI, MySQL, Mbstring, Curl, GD, Zip, Imagick, Intl</span>
                </div>
                <div>
                  <a href="/php/install/<?= $av["version"] ?>" 
                     class="btn btn-sm btn-outline-success text-uppercase fw-bold text-nowrap"
                     onclick="return confirm('¿Deseas iniciar la instalacion de PHP <?= $av["version"] ?>? Este proceso tardara unos segundos.')">
                    <i class="bi bi-download me-1"></i> Instalar
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">
          Cerrar
        </button>
      </div>
    </div>
  </div>
</div>
