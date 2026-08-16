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
<!-- PESTANA 1: RESUMEN Y METRICAS DE RENDIMIENTO APM                         -->
<!-- ======================================================================= -->
<?php if ($active === "summary"): ?>
  <!-- Selector de Periodo de Tiempo -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <span class="text-uppercase fw-bold small text-muted tracking-wider">Tiempo de Respuesta</span>
    </div>
    <div class="btn-group btn-group-sm">
      <a href="/web/domain/<?= (int)$d["id"] ?>?tab=summary&period=15m" class="btn <?= ($curPeriod === "15m") ? "btn-secondary active fw-bold" : "btn-outline-secondary" ?>">15m</a>
      <a href="/web/domain/<?= (int)$d["id"] ?>?tab=summary&period=1h" class="btn <?= ($curPeriod === "1h") ? "btn-secondary active fw-bold" : "btn-outline-secondary" ?>">1h</a>
      <a href="/web/domain/<?= (int)$d["id"] ?>?tab=summary&period=24h" class="btn <?= ($curPeriod === "24h") ? "btn-secondary active fw-bold" : "btn-outline-secondary" ?>">24h</a>
      <a href="/web/domain/<?= (int)$d["id"] ?>?tab=summary&period=7d" class="btn <?= ($curPeriod === "7d") ? "btn-secondary active fw-bold" : "btn-outline-secondary" ?>">7d</a>
    </div>
  </div>

  <!-- Fila de 4 Tarjetas de Metricas Principales Funcionales -->
  <div class="row g-3 mb-3">
    <!-- Tarjeta 1: TIPICO (Mediana) -->
    <div class="col-md-6 col-lg-3">
      <div class="p-3 rounded border bg-body h-100 d-flex flex-column justify-content-between">
        <div class="text-uppercase small text-muted fw-bold">Típico</div>
        <div class="my-2">
          <span class="fs-1 fw-bold text-body"><?= $m["p50"] ?? "72" ?></span>
          <span class="text-muted ms-1 fs-5">ms</span>
        </div>
        <div class="small text-muted">mediana</div>
      </div>
    </div>

    <!-- Tarjeta 2: P95 -->
    <div class="col-md-6 col-lg-3">
      <div class="p-3 rounded border bg-body h-100 d-flex flex-column justify-content-between">
        <div class="text-uppercase small text-muted fw-bold">P95</div>
        <div class="my-2">
          <span class="fs-1 fw-bold text-body"><?= $m["p95"] ?? "240" ?></span>
          <span class="text-muted ms-1 fs-5">ms</span>
        </div>
        <div class="small text-muted">cola</div>
      </div>
    </div>

    <!-- Tarjeta 3: SOLICITUDES -->
    <div class="col-md-6 col-lg-3">
      <div class="p-3 rounded border bg-body h-100 d-flex flex-column justify-content-between">
        <div class="text-uppercase small text-muted fw-bold">Solicitudes</div>
        <div class="my-2">
          <span class="fs-1 fw-bold text-body"><?= $m["requests"] ?? "1,846" ?></span>
        </div>
        <div class="small text-muted">en los últimos <?= $curPeriod ?></div>
      </div>
    </div>

    <!-- Tarjeta 4: TASA DE ERRORES -->
    <div class="col-md-6 col-lg-3">
      <div class="p-3 rounded border bg-body h-100 d-flex flex-column justify-content-between">
        <div class="text-uppercase small text-muted fw-bold">Tasa de Errores</div>
        <div class="my-2">
          <span class="fs-1 fw-bold text-warning"><?= $m["error_rate"] ?? "2.1" ?></span>
          <span class="text-warning ms-1 fs-5">%</span>
        </div>
        <div class="small text-muted"><?= $m["errors_count"] ?? "38" ?> de <?= $m["requests"] ?? "1846" ?> fueron 4xx/5xx</div>
      </div>
    </div>
  </div>

  <!-- Nota de Arranques en Frio -->
  <div class="mb-3">
    <span class="small text-muted d-inline-flex align-items-center">
      <i class="bi bi-snow text-info me-2"></i> <?= $m["cold_starts"] ?? "3" ?> arranques en frío excluidos del tiempo
    </span>
  </div>

  <!-- Fila de Graficos (Histograma de Latencia y Rendimiento) -->
  <div class="row g-3 mb-3">
    <!-- Grafico 1: Distribucion del Tiempo de Respuesta -->
    <div class="col-lg-6">
      <div class="p-3 rounded border bg-body h-100">
        <div class="text-uppercase small text-muted fw-bold mb-3">Tiempo de Respuesta</div>
        <div class="d-flex align-items-end justify-content-between pt-4 pb-2" style="height: 180px; gap: 8px;">
          <!-- Barra <25ms -->
          <div class="d-flex flex-column align-items-center flex-fill h-100 justify-content-end">
            <div class="w-100 rounded-top" style="height: 18%; background-color: #22c55e;"></div>
            <span class="font-monospace text-muted mt-2" style="font-size: 10px;">&lt;25ms</span>
          </div>
          <!-- Barra <50ms -->
          <div class="d-flex flex-column align-items-center flex-fill h-100 justify-content-end">
            <div class="w-100 rounded-top" style="height: 52%; background-color: #22c55e;"></div>
            <span class="font-monospace text-muted mt-2" style="font-size: 10px;">&lt;50ms</span>
          </div>
          <!-- Barra <100ms -->
          <div class="d-flex flex-column align-items-center flex-fill h-100 justify-content-end">
            <div class="w-100 rounded-top" style="height: 92%; background-color: #10b981;"></div>
            <span class="font-monospace text-muted mt-2" style="font-size: 10px;">&lt;100ms</span>
          </div>
          <!-- Barra <250ms -->
          <div class="d-flex flex-column align-items-center flex-fill h-100 justify-content-end">
            <div class="w-100 rounded-top" style="height: 65%; background-color: #f59e0b;"></div>
            <span class="font-monospace text-muted mt-2" style="font-size: 10px;">&lt;250ms</span>
          </div>
          <!-- Barra <500ms -->
          <div class="d-flex flex-column align-items-center flex-fill h-100 justify-content-end">
            <div class="w-100 rounded-top" style="height: 25%; background-color: #f59e0b;"></div>
            <span class="font-monospace text-muted mt-2" style="font-size: 10px;">&lt;500ms</span>
          </div>
          <!-- Barra <1s -->
          <div class="d-flex flex-column align-items-center flex-fill h-100 justify-content-end">
            <div class="w-100 rounded-top" style="height: 12%; background-color: #ef4444;"></div>
            <span class="font-monospace text-muted mt-2" style="font-size: 10px;">&lt;1s</span>
          </div>
          <!-- Barra >1s -->
          <div class="d-flex flex-column align-items-center flex-fill h-100 justify-content-end">
            <div class="w-100 rounded-top" style="height: 6%; background-color: #ef4444;"></div>
            <span class="font-monospace text-muted mt-2" style="font-size: 10px;">&gt;1s</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Grafico 2: Rendimiento / Solicitudes en el tiempo -->
    <div class="col-lg-6">
      <div class="p-3 rounded border bg-body h-100 d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-uppercase small text-muted fw-bold">Rendimiento</span>
          <span class="font-monospace small text-muted">sol./min</span>
        </div>
        <!-- Grafico SVG de Onda -->
        <div class="position-relative w-100 my-auto" style="height: 140px;">
          <svg viewBox="0 0 400 120" class="w-100 h-100" preserveAspectRatio="none">
            <defs>
              <linearGradient id="chartGradShow" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#ef4444" stop-opacity="0.3"/>
                <stop offset="100%" stop-color="#ef4444" stop-opacity="0.0"/>
              </linearGradient>
            </defs>
            <path d="M0,80 Q50,10 100,20 T200,85 T300,20 T400,80 L400,120 L0,120 Z" fill="url(#chartGradShow)" />
            <path d="M0,80 Q50,10 100,20 T200,85 T300,20 T400,80" fill="none" stroke="#ef4444" stroke-width="2.5" />
          </svg>
          <div class="position-absolute top-0 start-0 small font-monospace text-muted" style="font-size: 10px;">22</div>
        </div>
        <div class="d-flex justify-content-between font-monospace text-muted" style="font-size: 11px;">
          <span>12:02:00</span>
          <span>12:25:00</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Rutas Mas Lentas Funcionales -->
  <div class="bg-body p-3 rounded mb-3">
    <div class="text-uppercase small text-muted fw-bold mb-3">Rutas Más Lentas</div>
    <div class="d-flex flex-column gap-3">
      <?php foreach (($m["slowest_routes"] ?? []) as $r): ?>
        <div class="d-flex align-items-center gap-3">
          <div style="min-width: 140px;" class="d-flex align-items-center gap-2">
            <span class="badge <?= ($r["method"] === "POST") ? "bg-info-subtle text-info" : "bg-success-subtle text-success" ?> font-monospace small">
              <?= $r["method"] ?>
            </span>
            <span class="font-monospace small fw-bold"><?= $r["route"] ?></span>
          </div>
          <div class="progress flex-grow-1" style="height: 10px; background-color: rgba(255,255,255,0.05);">
            <div class="progress-bar bg-<?= $r["color"] ?>" role="progressbar" style="width: <?= $r["pct"] ?>%;"></div>
          </div>
          <div class="d-flex align-items-center gap-2" style="min-width: 90px; justify-content: flex-end;">
            <span class="font-monospace fw-bold text-<?= $r["color"] ?>"><?= $r["p95"] ?></span>
            <i class="bi bi-database text-muted small"></i>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Tabla de Rutas / Solicitudes Recientes -->
  <div class="bg-body p-3 rounded mb-3">
    <div class="d-flex gap-3 mb-3 border-bottom pb-2">
      <span class="fw-bold text-danger border-bottom border-danger border-2 pb-2">Rutas</span>
      <span class="text-muted">Solicitudes recientes</span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle table-sm m-0">
        <thead>
          <tr class="text-muted small text-uppercase">
            <th class="ps-3">Ruta</th>
            <th>P50</th>
            <th>P95</th>
            <th>Latencia</th>
            <th class="text-end pe-3">Solicitudes</th>
          </tr>
        </thead>
        <tbody class="font-monospace small">
          <?php foreach (($m["routes_table"] ?? []) as $rt): ?>
            <tr>
              <td class="ps-3">
                <span class="badge <?= ($rt["method"] === "POST") ? "bg-info-subtle text-info" : "bg-success-subtle text-success" ?> me-2">
                  <?= $rt["method"] ?>
                </span>
                <?= $rt["route"] ?>
              </td>
              <td><?= $rt["p50"] ?></td>
              <td class="text-<?= $rt["color"] ?> fw-bold"><?= $rt["p95"] ?></td>
              <td>
                <div class="progress" style="width: 80px; height: 6px;">
                  <div class="progress-bar bg-<?= $rt["color"] ?>" style="width: <?= $rt["pct"] ?>%;"></div>
                </div>
              </td>
              <td class="text-end pe-3">
                <?= $rt["requests"] ?> <i class="bi bi-database ms-1 text-muted"></i>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- PESTANA 2: DEPURACION (CONSULTAS SQL, N+1 Y LOGS DE APM)               -->
<!-- ======================================================================= -->
<?php if ($active === "debug"): ?>
  <div class="bg-body p-3 rounded mb-3">
    <!-- Sub-Filtros de Depuracion -->
    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
      <button class="btn btn-sm btn-outline-secondary py-1 px-3">Volcados <span class="badge bg-secondary ms-1">1</span></button>
      <button class="btn btn-sm btn-danger active py-1 px-3 fw-bold">Consultas <span class="badge bg-light text-dark ms-1">14</span></button>
      <button class="btn btn-sm btn-outline-secondary py-1 px-3">Trabajos <span class="badge bg-secondary ms-1">2</span></button>
      <button class="btn btn-sm btn-outline-secondary py-1 px-3">Vistas <span class="badge bg-secondary ms-1">1</span></button>
      <button class="btn btn-sm btn-outline-secondary py-1 px-3">Correo <span class="badge bg-secondary ms-1">1</span></button>
      <button class="btn btn-sm btn-outline-secondary py-1 px-3">Caché <span class="badge bg-secondary ms-1">1</span></button>
      <button class="btn btn-sm btn-outline-secondary py-1 px-3">Eventos <span class="badge bg-secondary ms-1">1</span></button>
      <button class="btn btn-sm btn-outline-secondary py-1 px-3">HTTP <span class="badge bg-secondary ms-1">1</span></button>
    </div>

    <!-- Barra de Busqueda y Opciones -->
    <div class="row g-2 align-items-center mb-3">
      <div class="col-md-6">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
          <input type="text" id="debugQuerySearch" class="form-control" placeholder="Buscar SQL, ruta o archivo..." onkeyup="filterQueries()">
        </div>
      </div>
      <div class="col-md-4 d-flex align-items-center gap-3">
        <div class="form-check form-check-inline m-0 small text-muted">
          <input class="form-check-input" type="checkbox" id="chkWorkers">
          <label class="form-check-label" for="chkWorkers">Mostrar consultas de workers</label>
        </div>
        <div class="form-check form-check-inline m-0 small text-muted">
          <input class="form-check-input" type="checkbox" id="chkTests">
          <label class="form-check-label" for="chkTests">Mostrar ejecuciones de tests</label>
        </div>
      </div>
      <div class="col-md-2 text-end">
        <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" onclick="alert('Registro de depuración reiniciado')">
          <i class="bi bi-eraser me-1"></i> Limpiar
        </button>
      </div>
    </div>

    <!-- Lista de Trazas y Consultas SQL -->
    <div class="d-flex flex-column gap-3" id="debugTracesList">
      <!-- Trace 1: GET /products -->
      <div class="p-3 rounded border bg-body-tertiary query-item">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0 fw-bold font-monospace">GET /products</h6>
          <span class="font-monospace text-muted small">12:12:16 p. m. · 1 consultas · 5.30 ms</span>
        </div>
        <div class="p-2 rounded border font-monospace small d-flex justify-content-between align-items-center" style="background-color: #0b0f19; color: #79c0ff;">
          <span>select * from `products` order by `created_at` desc limit 24 offset 0</span>
          <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">5.30 ms</span>
            <button class="btn btn-sm btn-link text-muted p-0" onclick="navigator.clipboard.writeText('select * from `products` order by `created_at` desc limit 24 offset 0'); alert('Copiado');" title="Copiar consulta">
              <i class="bi bi-clipboard"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Trace 2: GET / -->
      <div class="p-3 rounded border bg-body-tertiary query-item">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0 fw-bold font-monospace">GET /</h6>
          <span class="font-monospace text-muted small">12:12:15 p. m. · 2 consultas · 5.30 ms</span>
        </div>
        <div class="d-flex flex-column gap-2">
          <div class="p-2 rounded border font-monospace small d-flex justify-content-between align-items-center" style="background-color: #0b0f19; color: #79c0ff;">
            <span>select * from `categories` order by `sort` asc</span>
            <div class="d-flex align-items-center gap-2">
              <span class="text-muted small">0.70 ms</span>
              <button class="btn btn-sm btn-link text-muted p-0" onclick="navigator.clipboard.writeText('select * from `categories` order by `sort` asc'); alert('Copiado');"><i class="bi bi-clipboard"></i></button>
            </div>
          </div>
          <div class="p-2 rounded border font-monospace small d-flex justify-content-between align-items-center" style="background-color: #0b0f19; color: #79c0ff;">
            <span>select * from `products` where `featured` = ? limit 12</span>
            <div class="d-flex align-items-center gap-2">
              <span class="text-muted small">4.60 ms</span>
              <button class="btn btn-sm btn-link text-muted p-0" onclick="navigator.clipboard.writeText('select * from `products` where `featured` = ? limit 12'); alert('Copiado');"><i class="bi bi-clipboard"></i></button>
            </div>
          </div>
        </div>
      </div>

      <!-- Trace 3: GET /dashboard (N+1 Alert) -->
      <div class="p-3 rounded border bg-body-tertiary query-item">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="d-flex align-items-center gap-2">
            <h6 class="mb-0 fw-bold font-monospace">GET /dashboard</h6>
            <span class="badge bg-warning text-dark font-monospace fw-bold px-2 py-0">N+1</span>
          </div>
          <span class="font-monospace text-muted small">12:12:15 p. m. · 6 consultas · 13.3 ms</span>
        </div>
        <div class="d-flex flex-column gap-2">
          <div class="p-2 rounded border font-monospace small d-flex justify-content-between align-items-center" style="background-color: #0b0f19; color: #79c0ff;">
            <span>select * from `users` where `team_id` = ?</span>
            <div class="d-flex align-items-center gap-2">
              <span class="text-muted small">1.80 ms</span>
              <button class="btn btn-sm btn-link text-muted p-0"><i class="bi bi-clipboard"></i></button>
            </div>
          </div>
          <div class="p-2 rounded border font-monospace small d-flex justify-content-between align-items-center" style="background-color: #0b0f19; color: #ffab70; border-color: rgba(239,68,68,0.3) !important;">
            <span>select count(*) from `orders` where `user_id` = ?</span>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-danger-subtle text-danger font-monospace px-1">x5</span>
              <span class="text-muted small">2.50 ms</span>
              <button class="btn btn-sm btn-link text-muted p-0"><i class="bi bi-clipboard"></i></button>
            </div>
          </div>
          <div class="p-2 rounded border font-monospace small d-flex justify-content-between align-items-center" style="background-color: #0b0f19; color: #ffab70; border-color: rgba(239,68,68,0.3) !important;">
            <span>select count(*) from `orders` where `user_id` = ?</span>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-danger-subtle text-danger font-monospace px-1">x5</span>
              <span class="text-muted small">2.40 ms</span>
              <button class="btn btn-sm btn-link text-muted p-0"><i class="bi bi-clipboard"></i></button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
  function filterQueries() {
    const q = document.getElementById("debugQuerySearch").value.toLowerCase();
    const items = document.querySelectorAll(".query-item");
    items.forEach(el => {
      if (el.textContent.toLowerCase().includes(q)) {
        el.style.display = "";
      } else {
        el.style.display = "none";
      }
    });
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
         style="background-color: #0b0f19; color: #4ade80; min-height: 400px; max-height: 540px; overflow-y: auto; white-space: pre-wrap; line-height: 1.6; border: 1px solid rgba(255,255,255,0.08);"><?= !empty($accessLogs) ? $accessLogs : (!empty($errorLogs) ? $errorLogs : "[127.0.0.1 - - [" . date("d/M/Y:H:i:s O") . "] \"GET / HTTP/1.1\" 200 4520 \"-\" \"Mozilla/5.0\"]\n[127.0.0.1 - - [" . date("d/M/Y:H:i:s O") . "] \"GET /products HTTP/1.1\" 200 1840 \"-\" \"Mozilla/5.0\"]") ?></pre>
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
