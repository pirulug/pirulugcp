<?php
$d = $domain ?? [];
$active = $activeTab ?? "summary";
$curPeriod = $period ?? "1h";
$sslActive = !empty($d["ssl_enabled"]);
$domainName = $d["domain"] ?? "dominio.test";
$userName = $d["username"] ?? "admin";
$proto = $sslActive ? "https://" : "http://";
$m = $metrics ?? [];
$fLogo = $frameworkLogo ?? "/assets/sitios/php.svg";
$dbg = $debugData ?? [];
?>

<!-- ======================================================================= -->
<!-- ENCABEZADO PRINCIPAL DEL DOMINIO                                        -->
<!-- ======================================================================= -->
<div class="bg-body p-3 rounded mb-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div class="d-flex align-items-center flex-wrap gap-2">
    <!-- Icono de Seguridad SSL -->
    <?php if ($sslActive): ?>
      <span class="text-success fs-5" title="Conexión Segura HTTPS (SSL Activo)">
        <i class="bi bi-shield-lock-fill"></i>
      </span>
    <?php else: ?>
      <span class="text-muted fs-5" title="Conexión HTTP estándar">
        <i class="bi bi-shield-slash"></i>
      </span>
    <?php endif; ?>

    <!-- Nombre del Dominio -->
    <span class="text-muted font-monospace small"><?= $proto ?></span>
    <h1 class="h4 mb-0 fw-bold font-monospace"><?= $domainName ?></h1>

    <!-- Badge de Framework / Stack Detectado con Logo SVG -->
    <span class="badge bg-body-tertiary text-body border font-monospace px-2 py-1 d-inline-flex align-items-center">
      <img src="<?= $fLogo ?>" alt="<?= $framework ?? "PHP" ?>" style="width: 16px; height: 16px; object-fit: contain;" class="me-1">
      <?= $framework ?? "PHP Standard" ?>
    </span>

    <!-- Link Directo a la URL -->
    <a href="<?= $proto . $domainName ?>" target="_blank" class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-decoration-none px-2 py-1">
      <i class="bi bi-globe me-1"></i> <?= $proto . $domainName ?>
      <i class="bi bi-arrow-left-right ms-1"></i>
    </a>
  </div>

  <!-- Botones de Accion Rapida Derecha -->
  <div class="d-flex align-items-center gap-1">
    <a href="<?= $proto . $domainName ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Abrir en navegador">
      <i class="bi bi-box-arrow-up-right"></i>
    </a>
    <a href="/files?domain=<?= urlencode($domainName) ?>" class="btn btn-sm btn-outline-secondary" title="Gestor de Archivos">
      <i class="bi bi-folder2-open"></i>
    </a>
    <a href="/web/git/<?= (int)$d["id"] ?>" class="btn btn-sm btn-outline-secondary" title="Integración Git">
      <i class="bi bi-github"></i>
    </a>
    <a href="/ftp?domain_id=<?= (int)$d["id"] ?>" class="btn btn-sm btn-outline-secondary" title="Cuentas FTP">
      <i class="bi bi-folder-symlink"></i>
    </a>
    <a href="/web" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap ms-1">
      <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
  </div>
</div>

<!-- ======================================================================= -->
<!-- BARRA DE PESTANAS Y SUBHEADER DE RUTA                                   -->
<!-- ======================================================================= -->
<div class="bg-body p-3 rounded mb-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
  <!-- Pestanas Izquierda -->
  <ul class="nav nav-pills gap-1 m-0">
    <li class="nav-item">
      <a class="nav-link <?= ($active === "summary") ? "active fw-bold" : "text-body" ?>" href="/web/domain/<?= (int)$d["id"] ?>?tab=summary&period=<?= $curPeriod ?>">
        <i class="bi bi-speedometer2 me-1"></i> Resumen
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($active === "logs") ? "active fw-bold" : "text-body" ?>" href="/web/domain/<?= (int)$d["id"] ?>?tab=logs">
        <i class="bi bi-terminal me-1"></i> Registros
      </a>
    </li>
    <?php if (!empty($hasEnv)): ?>
      <li class="nav-item">
        <a class="nav-link <?= ($active === "env") ? "active fw-bold" : "text-body" ?>" href="/web/domain/<?= (int)$d["id"] ?>?tab=env">
          <i class="bi bi-file-earmark-lock me-1"></i> Env
        </a>
      </li>
    <?php endif; ?>
    <?php if (!empty($hasArtisan)): ?>
      <li class="nav-item">
        <a class="nav-link <?= ($active === "tinker") ? "active fw-bold" : "text-body" ?>" href="/web/domain/<?= (int)$d["id"] ?>?tab=tinker">
          <i class="bi bi-terminal-dash me-1"></i> Tinker
        </a>
      </li>
    <?php endif; ?>
    <li class="nav-item">
      <a class="nav-link <?= ($active === "debug") ? "active fw-bold" : "text-body" ?>" href="/web/domain/<?= (int)$d["id"] ?>?tab=debug">
        <i class="bi bi-bug me-1"></i> Depuración
      </a>
    </li>
  </ul>

  <!-- Ruta Fisica Derecha -->
  <div>
    <span class="font-monospace small text-muted">
      <?= $docRoot ?? ("/home/" . $userName . "/web/" . $domainName . "/public_html") ?>
    </span>
  </div>
</div>

<!-- ======================================================================= -->
<!-- PESTANA 1: RESUMEN Y METRICAS DINAMICAS Y REALES DEL DOMINIO            -->
<!-- ======================================================================= -->
<?php if ($active === "summary"): ?>
  <!-- Selector de Periodo de Tiempo -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <span class="text-uppercase fw-bold small text-muted">Métricas de Tráfico y Servidor</span>
    </div>
    <div class="btn-group btn-group-sm">
      <a href="/web/domain/<?= (int)$d["id"] ?>?tab=summary&period=15m" class="btn <?= ($curPeriod === "15m") ? "btn-secondary active fw-bold" : "btn-outline-secondary" ?>">15m</a>
      <a href="/web/domain/<?= (int)$d["id"] ?>?tab=summary&period=1h" class="btn <?= ($curPeriod === "1h") ? "btn-secondary active fw-bold" : "btn-outline-secondary" ?>">1h</a>
      <a href="/web/domain/<?= (int)$d["id"] ?>?tab=summary&period=24h" class="btn <?= ($curPeriod === "24h") ? "btn-secondary active fw-bold" : "btn-outline-secondary" ?>">24h</a>
      <a href="/web/domain/<?= (int)$d["id"] ?>?tab=summary&period=7d" class="btn <?= ($curPeriod === "7d") ? "btn-secondary active fw-bold" : "btn-outline-secondary" ?>">7d</a>
    </div>
  </div>

  <!-- Fila de 4 Tarjetas de Metricas Reales -->
  <div class="row g-3 mb-3">
    <!-- Tarjeta 1: SOLICITUDES TOTALES -->
    <div class="col-md-6 col-lg-3">
      <div class="p-3 rounded border bg-body h-100 d-flex flex-column justify-content-between">
        <div class="text-uppercase small text-muted fw-bold">Solicitudes</div>
        <div class="my-2">
          <span class="fs-1 fw-bold text-body"><?= number_format($m["total_requests"] ?? 0) ?></span>
        </div>
        <div class="small text-muted">en los últimos <?= $curPeriod ?></div>
      </div>
    </div>

    <!-- Tarjeta 2: TASA DE ERRORES -->
    <div class="col-md-6 col-lg-3">
      <div class="p-3 rounded border bg-body h-100 d-flex flex-column justify-content-between">
        <div class="text-uppercase small text-muted fw-bold">Tasa de Errores</div>
        <div class="my-2">
          <span class="fs-1 fw-bold <?= (($m["error_rate"] ?? 0) > 0) ? "text-warning" : "text-success" ?>"><?= $m["error_rate"] ?? "0.0" ?></span>
          <span class="<?= (($m["error_rate"] ?? 0) > 0) ? "text-warning" : "text-success" ?> ms-1 fs-5">%</span>
        </div>
        <div class="small text-muted"><?= $m["errors_count"] ?? 0 ?> errores de <?= $m["total_requests"] ?? 0 ?> peticiones</div>
      </div>
    </div>

    <!-- Tarjeta 3: TRANSFERENCIA DE DATOS -->
    <div class="col-md-6 col-lg-3">
      <div class="p-3 rounded border bg-body h-100 d-flex flex-column justify-content-between">
        <div class="text-uppercase small text-muted fw-bold">Transferencia</div>
        <div class="my-2">
          <span class="fs-1 fw-bold text-body"><?= $m["bandwidth"] ?? "0 KB" ?></span>
        </div>
        <div class="small text-muted"><?= $m["unique_visitors"] ?? 0 ?> visitantes / IPs únicos</div>
      </div>
    </div>

    <!-- Tarjeta 4: ESPACIO EN DISCO -->
    <div class="col-md-6 col-lg-3">
      <div class="p-3 rounded border bg-body h-100 d-flex flex-column justify-content-between">
        <div class="text-uppercase small text-muted fw-bold">Espacio en Disco</div>
        <div class="my-2">
          <span class="fs-1 fw-bold text-primary"><?= $m["disk_size"] ?? "0 KB" ?></span>
        </div>
        <div class="small text-muted font-monospace text-truncate">/home/<?= $userName ?>/web/<?= $domainName ?></div>
      </div>
    </div>
  </div>

  <!-- Desglose de Codigos HTTP -->
  <div class="bg-body p-3 rounded mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <span class="text-uppercase small text-muted fw-bold">Distribución de Respuestas HTTP</span>
      <span class="font-monospace small text-muted">Total: <?= number_format($m["total_requests"] ?? 0) ?></span>
    </div>

    <div class="row g-3 font-monospace small">
      <!-- 2xx Exito -->
      <div class="col-md-3">
        <div class="p-2 rounded border bg-body-tertiary">
          <div class="d-flex justify-content-between mb-1">
            <span class="text-success fw-bold">2xx Éxito</span>
            <strong><?= $m["status_counts"]["2xx"] ?? 0 ?> (<?= $m["status_pct"]["2xx"] ?? 0 ?>%)</strong>
          </div>
          <div class="progress" style="height: 6px;">
            <div class="progress-bar bg-success" style="width: <?= $m["status_pct"]["2xx"] ?? 0 ?>%;"></div>
          </div>
        </div>
      </div>

      <!-- 3xx Redirecciones -->
      <div class="col-md-3">
        <div class="p-2 rounded border bg-body-tertiary">
          <div class="d-flex justify-content-between mb-1">
            <span class="text-info fw-bold">3xx Redirecciones</span>
            <strong><?= $m["status_counts"]["3xx"] ?? 0 ?> (<?= $m["status_pct"]["3xx"] ?? 0 ?>%)</strong>
          </div>
          <div class="progress" style="height: 6px;">
            <div class="progress-bar bg-info" style="width: <?= $m["status_pct"]["3xx"] ?? 0 ?>%;"></div>
          </div>
        </div>
      </div>

      <!-- 4xx Errores Cliente -->
      <div class="col-md-3">
        <div class="p-2 rounded border bg-body-tertiary">
          <div class="d-flex justify-content-between mb-1">
            <span class="text-warning fw-bold">4xx Cliente</span>
            <strong><?= $m["status_counts"]["4xx"] ?? 0 ?> (<?= $m["status_pct"]["4xx"] ?? 0 ?>%)</strong>
          </div>
          <div class="progress" style="height: 6px;">
            <div class="progress-bar bg-warning" style="width: <?= $m["status_pct"]["4xx"] ?? 0 ?>%;"></div>
          </div>
        </div>
      </div>

      <!-- 5xx Errores Servidor -->
      <div class="col-md-3">
        <div class="p-2 rounded border bg-body-tertiary">
          <div class="d-flex justify-content-between mb-1">
            <span class="text-danger fw-bold">5xx Servidor</span>
            <strong><?= $m["status_counts"]["5xx"] ?? 0 ?> (<?= $m["status_pct"]["5xx"] ?? 0 ?>%)</strong>
          </div>
          <div class="progress" style="height: 6px;">
            <div class="progress-bar bg-danger" style="width: <?= $m["status_pct"]["5xx"] ?? 0 ?>%;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Rutas Mas Solicitadas Reales -->
  <div class="bg-body p-3 rounded mb-3">
    <div class="text-uppercase small text-muted fw-bold mb-3">Rutas Más Solicitadas</div>
    <?php if (empty($m["top_routes"])): ?>
      <div class="text-muted small text-center py-3">No hay solicitudes registradas en este período.</div>
    <?php else: ?>
      <div class="d-flex flex-column gap-3">
        <?php foreach ($m["top_routes"] as $r): ?>
          <div class="d-flex align-items-center gap-3">
            <div style="min-width: 160px;" class="d-flex align-items-center gap-2">
              <span class="badge <?= ($r["method"] === "POST") ? "bg-info-subtle text-info" : "bg-success-subtle text-success" ?> font-monospace small">
                <?= $r["method"] ?>
              </span>
              <span class="font-monospace small fw-bold text-truncate" style="max-width: 220px;" title="<?= $r["route"] ?>">
                <?= $r["route"] ?>
              </span>
            </div>
            <div class="progress flex-grow-1" style="height: 8px; background-color: rgba(255,255,255,0.05);">
              <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $r["pct"] ?>%;"></div>
            </div>
            <div class="d-flex align-items-center gap-3" style="min-width: 180px; justify-content: flex-end;">
              <span class="badge bg-secondary-subtle text-secondary font-monospace"><?= $r["bytes_fmt"] ?></span>
              <span class="font-monospace small fw-bold"><?= $r["count"] ?> peticiones</span>
              <span class="badge <?= ($r["last_status"] < 400) ? "bg-success-subtle text-success" : "bg-warning-subtle text-warning" ?> font-monospace">
                <?= $r["last_status"] ?>
              </span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Tabla de Solicitudes Recientes en Vivo -->
  <div class="bg-body p-3 rounded mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <span class="text-uppercase small text-muted fw-bold">Solicitudes Recientes en Vivo</span>
      <span class="font-monospace small text-muted">Últimas 30 peticiones</span>
    </div>

    <?php if (empty($m["recent_requests"])): ?>
      <div class="text-muted small text-center py-3">No hay solicitudes recientes en el registro de acceso.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle table-sm m-0">
          <thead>
            <tr class="text-muted small text-uppercase">
              <th class="ps-3">Hora</th>
              <th>IP</th>
              <th>Método</th>
              <th>Ruta Solicitada</th>
              <th>Estado</th>
              <th class="text-end pe-3">Transferido</th>
            </tr>
          </thead>
          <tbody class="font-monospace small">
            <?php foreach ($m["recent_requests"] as $req): ?>
              <tr>
                <td class="ps-3 text-muted"><?= $req["date"] ?></td>
                <td><?= $req["ip"] ?></td>
                <td>
                  <span class="badge <?= ($req["method"] === "POST") ? "bg-info-subtle text-info" : "bg-success-subtle text-success" ?>">
                    <?= $req["method"] ?>
                  </span>
                </td>
                <td class="fw-bold text-body text-truncate" style="max-width: 320px;"><?= $req["uri"] ?></td>
                <td>
                  <span class="badge <?= ($req["status"] < 300) ? "bg-success-subtle text-success" : (($req["status"] < 400) ? "bg-info-subtle text-info" : (($req["status"] < 500) ? "bg-warning-subtle text-warning" : "bg-danger-subtle text-danger")) ?>">
                    <?= $req["status"] ?>
                  </span>
                </td>
                <td class="text-end pe-3"><?= $req["bytes_fmt"] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- PESTANA 2: DEPURACION (CONSULTAS SQL A LA BASE DE DATOS)                -->
<!-- ======================================================================= -->
<?php if ($active === "debug"): ?>
  <div class="bg-body p-3 rounded mb-3">
    <!-- Encabezado de la Seccion de Consultas SQL -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
      <div>
        <h5 class="mb-0 fw-bold d-flex align-items-center">
          <i class="bi bi-database me-2 text-primary"></i> Consultas SQL a la Base de Datos
        </h5>
        <span class="text-muted small">Captura y perfilado en tiempo real de consultas ejecutadas en MariaDB por <strong><?= $domainName ?></strong>.</span>
      </div>

      <!-- Botonera de Control de Captura -->
      <div class="d-flex align-items-center gap-2">
        <a href="/web/domain/<?= (int)$d["id"] ?>/debug/toggle-sql" class="btn btn-sm <?= !empty($dbg["is_sql_capture_active"]) ? "btn-success" : "btn-outline-primary" ?> text-uppercase fw-bold text-nowrap">
          <i class="bi <?= !empty($dbg["is_sql_capture_active"]) ? "bi-record-circle-fill text-danger" : "bi-play-circle" ?> me-1"></i>
          <?= !empty($dbg["is_sql_capture_active"]) ? "Captura SQL: Activa" : "Activar Captura SQL" ?>
        </a>
        <a href="/web/domain/<?= (int)$d["id"] ?>/debug/clear" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap" onclick="return confirm('¿Reiniciar y limpiar los registros de consultas?')">
          <i class="bi bi-eraser me-1"></i> Limpiar
        </a>
      </div>
    </div>

    <!-- Barra de Busqueda y Contador -->
    <div class="row g-2 align-items-center mb-3">
      <div class="col-md-6">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
          <input type="text" id="debugQuerySearch" class="form-control" placeholder="Buscar sentencia SQL, tabla o ID..." onkeyup="filterDebugTraces()">
        </div>
      </div>
      <div class="col-md-6 text-end">
        <span class="badge bg-secondary-subtle text-secondary font-monospace px-3 py-2">
          <i class="bi bi-hdd-network me-1"></i> <?= $dbg["counts"]["queries"] ?? 0 ?> <?= (($dbg["counts"]["queries"] ?? 0) === 1) ? "consulta capturada" : "consultas capturadas" ?>
        </span>
      </div>
    </div>

    <?php if (empty($dbg["queries"])): ?>
      <!-- Estado Limpio cuando no hay consultas capturadas -->
      <div class="p-4 rounded border text-center my-3 bg-body-tertiary">
        <i class="bi bi-database-check fs-1 text-primary mb-2 d-block"></i>
        <h5 class="fw-bold">No hay consultas SQL registradas</h5>
        <p class="text-muted small mb-3" style="max-width: 600px; margin: 0 auto;">
          Activa la captura en vivo para perfilar todas las consultas que <strong><?= $domainName ?></strong> ejecuta en MariaDB con tiempos en milisegundos y detección de consultas N+1.
        </p>
        <?php if (empty($dbg["is_sql_capture_active"])): ?>
          <div class="d-flex justify-content-center gap-2">
            <a href="/web/domain/<?= (int)$d["id"] ?>/debug/toggle-sql" class="btn btn-primary px-4 text-uppercase fw-bold">
              <i class="bi bi-play-circle me-1"></i> Activar Captura SQL en Vivo
            </a>
          </div>
        <?php else: ?>
          <span class="badge bg-success-subtle text-success px-3 py-2 font-monospace">
            <i class="bi bi-record-circle-fill text-danger me-1"></i> Captura activa esperando peticiones de <?= $domainName ?>...
          </span>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <!-- Lista de Consultas SQL Capturadas -->
      <div class="d-flex flex-column gap-3" id="debugTracesList">
        <?php foreach ($dbg["queries"] as $qTrace): ?>
          <div class="p-3 rounded border bg-body-tertiary debug-trace-item">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="d-flex align-items-center gap-2">
                <h6 class="mb-0 fw-bold font-monospace"><?= $qTrace["route"] ?? "MariaDB" ?></h6>
                <?php if (!empty($qTrace["has_nplus"])): ?>
                  <span class="badge bg-warning text-dark font-monospace fw-bold px-2 py-0">N+1</span>
                <?php endif; ?>
              </div>
              <span class="font-monospace text-muted small"><?= $qTrace["time"] ?? "" ?> · <?= $qTrace["count"] ?? 1 ?> <?= (($qTrace["count"] ?? 1) === 1) ? "consulta" : "consultas" ?></span>
            </div>
            <div class="d-flex flex-column gap-2">
              <?php foreach (($qTrace["statements"] ?? []) as $st): ?>
                <div class="p-2 rounded border font-monospace small d-flex justify-content-between align-items-center query-stmt-row" 
                     style="background-color: #0b0f19; color: <?= (($st["count"] ?? 1) > 1) ? "#ffab70" : "#79c0ff" ?>; <?= (($st["count"] ?? 1) > 1) ? "border-color: rgba(239,68,68,0.3) !important;" : "" ?>">
                  <span class="query-sql-text"><?= $st["sql"] ?></span>
                  <div class="d-flex align-items-center gap-2 text-nowrap ms-3">
                    <?php if (!empty($st["schema"])): ?>
                      <span class="badge bg-secondary-subtle text-secondary font-monospace"><?= $st["schema"] ?></span>
                    <?php endif; ?>
                    <?php if (($st["count"] ?? 1) > 1): ?>
                      <span class="badge bg-danger-subtle text-danger font-monospace px-1">x<?= $st["count"] ?></span>
                    <?php endif; ?>
                    <span class="text-muted small"><?= $st["time_ms"] ?? "" ?></span>
                    <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="copySqlStatement(this)" title="Copiar consulta SQL">
                      <i class="bi bi-clipboard"></i>
                    </button>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <script>
  function filterDebugTraces() {
    const q = document.getElementById("debugQuerySearch").value.toLowerCase();
    const items = document.querySelectorAll(".debug-trace-item");
    items.forEach(el => {
      if (el.textContent.toLowerCase().includes(q)) {
        el.style.display = "";
      } else {
        el.style.display = "none";
      }
    });
  }

  function copySqlStatement(btn) {
    const container = btn.closest(".query-stmt-row");
    if (container) {
      const txt = container.querySelector(".query-sql-text").textContent.trim();
      navigator.clipboard.writeText(txt).then(() => {
        const icon = btn.querySelector("i");
        icon.className = "bi bi-check2 text-success";
        setTimeout(() => {
          icon.className = "bi bi-clipboard";
        }, 1500);
      });
    }
  }
  </script>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- PESTANA 3: REGISTROS (LOGS NGINX / APACHE DEL DOMINIO)                  -->
<!-- ======================================================================= -->
<?php if ($active === "logs"): ?>
  <div class="bg-body p-3 rounded mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 font-monospace small">
          <i class="bi bi-circle-fill me-1" style="font-size: 8px !important;"></i> en vivo
        </span>
        <span class="text-muted small">Registros de Acceso y Errores (<code>/var/log/nginx/<?= $domainName ?>_access.log</code>)</span>
      </div>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" onclick="document.getElementById('domainLogView').textContent='[Consola limpiada]'">
          <i class="bi bi-eraser me-1"></i> Limpiar
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" onclick="navigator.clipboard.writeText(document.getElementById('domainLogView').textContent); alert('Logs copiados')">
          <i class="bi bi-clipboard me-1"></i> Copiar
        </button>
        <a href="/web/domain/<?= (int)$d["id"] ?>?tab=logs" class="btn btn-sm btn-outline-primary text-uppercase fw-bold">
          <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
        </a>
      </div>
    </div>

    <pre id="domainLogView" 
         class="p-3 rounded font-monospace small mb-0" 
         style="background-color: #0b0f19; color: #4ade80; min-height: 400px; max-height: 540px; overflow-y: auto; white-space: pre-wrap; line-height: 1.6; border: 1px solid rgba(255,255,255,0.08);"><?= !empty($accessLogs) ? $accessLogs : (!empty($errorLogs) ? $errorLogs : "[Sin registros de acceso en /var/log/nginx/" . $domainName . "_access.log]") ?></pre>
  </div>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- PESTANA 4: VARIABLES DE ENTORNO (.ENV) (SOLO SI TIENE .ENV / LARAVEL)    -->
<!-- ======================================================================= -->
<?php if ($active === "env" && !empty($hasEnv)): ?>
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="card-title mb-0">Variables de Entorno (.env)</h5>
        <span class="font-monospace small text-muted"><?= $webRoot ?? "" ?>/.env</span>
      </div>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" onclick="navigator.clipboard.writeText(document.getElementById('envEditorArea').value); alert('Contenido copiado')">
          <i class="bi bi-clipboard me-1"></i> Copiar
        </button>
      </div>
    </div>
    <div class="card-body">
      <form action="/web/domain/<?= (int)$d["id"] ?>/env" method="POST">
        <div class="d-flex rounded border mb-3" style="background-color: #0b0f19; min-height: 420px; border-color: rgba(255,255,255,0.08) !important;">
          <textarea id="envEditorArea" 
                    name="env_content" 
                    class="form-control border-0 p-3 font-monospace small" 
                    style="background-color: transparent; color: #79c0ff; resize: vertical; line-height: 1.6; min-height: 420px; outline: none; box-shadow: none;" 
                    spellcheck="false"><?= $rawEnv ?? "" ?></textarea>
        </div>
        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-primary px-4 text-uppercase fw-bold">
            <i class="bi bi-floppy me-2"></i> Guardar Variables .env
          </button>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- PESTANA 5: TINKER / TERMINAL WEB (SOLO PARA PROYECTOS LARAVEL)          -->
<!-- ======================================================================= -->
<?php if ($active === "tinker" && !empty($hasArtisan)): ?>
  <div class="bg-body p-3 rounded mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h5 class="mb-0 d-flex align-items-center">
          <img src="/assets/sitios/laravel.svg" alt="Laravel" style="width: 20px; height: 20px; object-fit: contain;" class="me-2">
          Consola Interactiva Artisan / Tinker
        </h5>
        <span class="text-muted small">Ejecuta comandos de Artisan directamente en el entorno de la aplicación Laravel.</span>
      </div>
    </div>

    <!-- Salida de Consola -->
    <pre class="p-3 rounded font-monospace small mb-3" 
         style="background-color: #0b0f19; color: #38bdf8; min-height: 280px; max-height: 400px; overflow-y: auto; white-space: pre-wrap; line-height: 1.6; border: 1px solid rgba(255,255,255,0.08);"><?= !empty($_SESSION["tinker_last_output"]) ? $_SESSION["tinker_last_output"] : "Laravel 12 Application Console - PHP " . ($d["php_version"] ?? "8.5") . "\nEscribe un comando de Artisan para ejecutar en " . $domainName . " (ej. 'route:list', 'migrate:status', 'model:show User')" ?></pre>

    <!-- Input de Ejecucion -->
    <form action="/web/domain/<?= (int)$d["id"] ?>/tinker" method="POST" class="d-flex gap-2">
      <div class="input-group">
        <span class="input-group-text font-monospace bg-body-tertiary">artisan&gt;</span>
        <input type="text" name="command" class="form-control font-monospace" placeholder="ej. route:list o config:cache" autofocus required>
      </div>
      <button type="submit" class="btn btn-primary px-4 text-uppercase fw-bold text-nowrap">
        <i class="bi bi-play-fill me-1"></i> Ejecutar
      </button>
    </form>
  </div>
<?php endif; ?>
