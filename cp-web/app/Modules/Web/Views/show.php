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
    <a href="/web/domain/<?= (int)$d["id"] ?>/edit" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap ms-1" title="Editar Alias y Redirección del Dominio">
      <i class="bi bi-pencil me-1"></i> Editar
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
      <a class="nav-link <?= ($active === "ssl") ? "active fw-bold" : "text-body" ?>" href="/web/domain/<?= (int)$d["id"] ?>?tab=ssl">
        <i class="bi bi-shield-check me-1"></i> SSL / Cloudflare
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
    <li class="nav-item">
      <a class="nav-link <?= ($active === "backups") ? "active fw-bold" : "text-body" ?>" href="/web/domain/<?= (int)$d["id"] ?>?tab=backups">
        <i class="bi bi-archive me-1"></i> Backups
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

  <!-- ======================================================================= -->
  <!-- SEGUIMIENTO Y FILTRO AVANZADO DE PETICIONES HTTP EN VIVO                -->
  <!-- ======================================================================= -->
  <div class="bg-body p-3 rounded mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
      <div>
        <h5 class="mb-0 fw-bold d-flex align-items-center">
          <i class="bi bi-activity me-2 text-primary"></i> Seguimiento de Peticiones HTTP
        </h5>
        <span class="text-muted small">Inspección y filtrado en tiempo real de tráfico recibido por <strong><?= $domainName ?></strong>.</span>
      </div>
      <div>
        <span class="badge bg-secondary-subtle text-secondary font-monospace px-3 py-2" id="reqCounterBadge">
          <i class="bi bi-list-check me-1"></i> <span id="visibleReqCount"><?= count($m["recent_requests"]) ?></span> de <?= count($m["recent_requests"]) ?> peticiones mostradas
        </span>
      </div>
    </div>

    <!-- Barra de Filtros de Peticiones -->
    <div class="p-3 rounded border bg-body-tertiary mb-3">
      <div class="row g-2 align-items-center">
        <!-- Busqueda por Ruta o IP -->
        <div class="col-md-4">
          <label class="form-label text-muted small mb-1">Buscar por Ruta o IP</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
            <input type="text" id="reqFilterSearch" class="form-control" placeholder="Ej: /wp-login, /api, 192.168..." onkeyup="applyRequestFilters()">
          </div>
        </div>

        <!-- Filtro por Categoria de Estado HTTP -->
        <div class="col-md-3">
          <label class="form-label text-muted small mb-1">Grupo de Estado</label>
          <select id="reqFilterStatusGroup" class="form-select form-select-sm" onchange="syncStatusSelectToPills()">
            <option value="all">-- Todos los Estados --</option>
            <option value="2xx">2xx Éxito (<?= $m["status_counts"]["2xx"] ?? 0 ?>)</option>
            <option value="3xx">3xx Redirecciones (<?= $m["status_counts"]["3xx"] ?? 0 ?>)</option>
            <option value="4xx">4xx Errores Cliente (<?= $m["status_counts"]["4xx"] ?? 0 ?>)</option>
            <option value="5xx">5xx Errores Servidor (<?= $m["status_counts"]["5xx"] ?? 0 ?>)</option>
          </select>
        </div>

        <!-- Filtro por Metodo HTTP -->
        <div class="col-md-3">
          <label class="form-label text-muted small mb-1">Método HTTP</label>
          <select id="reqFilterMethod" class="form-select form-select-sm" onchange="applyRequestFilters()">
            <option value="all">-- Todos los Métodos --</option>
            <option value="GET">GET</option>
            <option value="POST">POST</option>
            <option value="PUT">PUT</option>
            <option value="DELETE">DELETE</option>
            <option value="PATCH">PATCH</option>
            <option value="HEAD">HEAD</option>
            <option value="OPTIONS">OPTIONS</option>
          </select>
        </div>

        <!-- Boton Limpiar Filtros -->
        <div class="col-md-2 text-end pt-3">
          <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-uppercase fw-bold" onclick="resetRequestFilters()">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Restablecer
          </button>
        </div>
      </div>

      <!-- Quick Pills de Estado -->
      <div class="d-flex flex-wrap gap-2 mt-3 pt-2 border-top">
        <button type="button" class="btn btn-sm btn-primary active req-status-pill" data-status="all" onclick="selectStatusPill('all', this)">
          Todos <span class="badge bg-light text-dark ms-1"><?= $m["total_requests"] ?? 0 ?></span>
        </button>
        <button type="button" class="btn btn-sm btn-outline-success req-status-pill" data-status="2xx" onclick="selectStatusPill('2xx', this)">
          2xx Éxito <span class="badge bg-success-subtle text-success ms-1"><?= $m["status_counts"]["2xx"] ?? 0 ?></span>
        </button>
        <button type="button" class="btn btn-sm btn-outline-info req-status-pill" data-status="3xx" onclick="selectStatusPill('3xx', this)">
          3xx Redirecciones <span class="badge bg-info-subtle text-info ms-1"><?= $m["status_counts"]["3xx"] ?? 0 ?></span>
        </button>
        <button type="button" class="btn btn-sm btn-outline-warning req-status-pill" data-status="4xx" onclick="selectStatusPill('4xx', this)">
          4xx Cliente <span class="badge bg-warning-subtle text-warning ms-1"><?= $m["status_counts"]["4xx"] ?? 0 ?></span>
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger req-status-pill" data-status="5xx" onclick="selectStatusPill('5xx', this)">
          5xx Servidor <span class="badge bg-danger-subtle text-danger ms-1"><?= $m["status_counts"]["5xx"] ?? 0 ?></span>
        </button>
      </div>
    </div>

    <!-- Tabla de Peticiones Filtrable -->
    <?php if (empty($m["recent_requests"])): ?>
      <div class="p-4 rounded border text-center my-3 bg-body-tertiary">
        <i class="bi bi-inbox fs-1 text-muted mb-2 d-block"></i>
        <h6 class="fw-bold">No hay peticiones registradas aún</h6>
        <p class="text-muted small mb-0">El dominio no ha recibido solicitudes en el período seleccionado (<code>/var/log/nginx/<?= $domainName ?>_access.log</code>).</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle table-sm m-0" id="requestsTrackerTable">
          <thead>
            <tr class="text-muted small text-uppercase">
              <th class="ps-3">Hora / Fecha</th>
              <th>IP Visitante</th>
              <th>Método</th>
              <th>Ruta Solicitada</th>
              <th>Estado HTTP</th>
              <th class="text-end pe-3">Transferido</th>
            </tr>
          </thead>
          <tbody class="font-monospace small">
            <?php foreach ($m["recent_requests"] as $req): 
              $statusGroup = ($req["status"] >= 200 && $req["status"] < 300) ? "2xx" : (($req["status"] >= 300 && $req["status"] < 400) ? "3xx" : (($req["status"] >= 400 && $req["status"] < 500) ? "4xx" : "5xx"));
              $statusBadgeClass = ($statusGroup === "2xx") ? "bg-success-subtle text-success border border-success-subtle" : (($statusGroup === "3xx") ? "bg-info-subtle text-info border border-info-subtle" : (($statusGroup === "4xx") ? "bg-warning-subtle text-warning border border-warning-subtle" : "bg-danger-subtle text-danger border border-danger-subtle"));
              $methodBadgeClass = ($req["method"] === "POST") ? "bg-primary-subtle text-primary border border-primary-subtle" : (($req["method"] === "GET") ? "bg-success-subtle text-success border border-success-subtle" : (($req["method"] === "DELETE") ? "bg-danger-subtle text-danger border border-danger-subtle" : "bg-warning-subtle text-warning border border-warning-subtle"));
            ?>
              <tr class="req-row-item" 
                  data-status="<?= (int)$req["status"] ?>" 
                  data-status-group="<?= $statusGroup ?>" 
                  data-method="<?= $req["method"] ?>" 
                  data-uri="<?= strtolower($req["uri"]) ?>" 
                  data-ip="<?= $req["ip"] ?>">
                <td class="ps-3 text-muted text-nowrap"><?= $req["date"] ?></td>
                <td class="text-nowrap">
                  <a href="javascript:void(0)" class="text-decoration-none text-body" onclick="filterByIp('<?= $req["ip"] ?>')" title="Filtrar por esta IP">
                    <i class="bi bi-geo-alt me-1 text-muted"></i><?= $req["ip"] ?>
                  </a>
                </td>
                <td>
                  <span class="badge <?= $methodBadgeClass ?> font-monospace px-2">
                    <?= $req["method"] ?>
                  </span>
                </td>
                <td class="fw-bold text-body text-truncate" style="max-width: 380px;" title="<?= $req["uri"] ?>">
                  <?= $req["uri"] ?>
                </td>
                <td>
                  <span class="badge <?= $statusBadgeClass ?> font-monospace px-2">
                    <?= $req["status"] ?>
                  </span>
                </td>
                <td class="text-end pe-3 text-nowrap"><?= $req["bytes_fmt"] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Barra de Paginacion de Peticiones -->
      <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-2 border-top gap-2">
        <div class="d-flex align-items-center gap-2">
          <label class="form-label text-muted small mb-0">Mostrar:</label>
          <select id="reqPageSizeSelect" class="form-select form-select-sm" style="width: auto;" onchange="changeReqPageSize(this.value)">
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
          <span class="text-muted small ms-2" id="reqPageInfo">Mostrando 1 - 25 de <?= count($m["recent_requests"]) ?> peticiones</span>
        </div>
        <div id="reqPaginationContainer"></div>
      </div>

      <div id="noMatchingReqsNotice" class="text-muted small text-center py-4 d-none">
        <i class="bi bi-filter-circle fs-3 d-block mb-1 text-muted"></i>
        No se encontraron peticiones que coincidan con los filtros aplicados.
      </div>
    <?php endif; ?>
  </div>

  <script>
  let reqCurrentPage = 1;
  let reqPageSize = 25;

  function changeReqPageSize(newSize) {
    reqPageSize = parseInt(newSize, 10) || 25;
    reqCurrentPage = 1;
    applyRequestFilters();
  }

  function setReqPage(page) {
    reqCurrentPage = page;
    renderRequestPagination();
  }

  function selectStatusPill(statusGroup, btn) {
    document.querySelectorAll(".req-status-pill").forEach(b => {
      b.classList.remove("active", "btn-primary", "btn-success", "btn-info", "btn-warning", "btn-danger");
      const group = b.getAttribute("data-status");
      if (group === "2xx") b.classList.add("btn-outline-success");
      else if (group === "3xx") b.classList.add("btn-outline-info");
      else if (group === "4xx") b.classList.add("btn-outline-warning");
      else if (group === "5xx") b.classList.add("btn-outline-danger");
      else b.classList.add("btn-outline-secondary");

      const badge = b.querySelector(".badge");
      if (badge && group === "all") {
        badge.className = "badge bg-secondary-subtle text-secondary ms-1";
      }
    });

    btn.classList.add("active");
    if (statusGroup === "2xx") btn.classList.add("btn-success");
    else if (statusGroup === "3xx") btn.classList.add("btn-info");
    else if (statusGroup === "4xx") btn.classList.add("btn-warning");
    else if (statusGroup === "5xx") btn.classList.add("btn-danger");
    else {
      btn.classList.add("btn-primary");
      const badge = btn.querySelector(".badge");
      if (badge) badge.className = "badge bg-light text-dark ms-1";
    }

    const selectEl = document.getElementById("reqFilterStatusGroup");
    if (selectEl) {
      selectEl.value = statusGroup;
    }
    reqCurrentPage = 1;
    applyRequestFilters();
  }

  function syncStatusSelectToPills() {
    const val = document.getElementById("reqFilterStatusGroup").value;
    const targetPill = document.querySelector(`.req-status-pill[data-status="${val}"]`) || document.querySelector(`.req-status-pill[data-status="all"]`);
    if (targetPill) {
      selectStatusPill(val, targetPill);
    } else {
      reqCurrentPage = 1;
      applyRequestFilters();
    }
  }

  function filterByIp(ip) {
    const searchInput = document.getElementById("reqFilterSearch");
    if (searchInput) {
      searchInput.value = ip;
      reqCurrentPage = 1;
      applyRequestFilters();
    }
  }

  function resetRequestFilters() {
    const searchInput = document.getElementById("reqFilterSearch");
    const methodSelect = document.getElementById("reqFilterMethod");
    const statusSelect = document.getElementById("reqFilterStatusGroup");

    if (searchInput) searchInput.value = "";
    if (methodSelect) methodSelect.value = "all";
    if (statusSelect) statusSelect.value = "all";

    const allPill = document.querySelector('.req-status-pill[data-status="all"]');
    if (allPill) {
      selectStatusPill("all", allPill);
    } else {
      reqCurrentPage = 1;
      applyRequestFilters();
    }
  }

  function applyRequestFilters() {
    const searchVal = (document.getElementById("reqFilterSearch")?.value || "").toLowerCase().trim();
    const statusGroupVal = document.getElementById("reqFilterStatusGroup")?.value || "all";
    const methodVal = document.getElementById("reqFilterMethod")?.value || "all";

    const rows = document.querySelectorAll(".req-row-item");

    rows.forEach(row => {
      const uri = (row.getAttribute("data-uri") || "").toLowerCase();
      const ip = (row.getAttribute("data-ip") || "").toLowerCase();
      const method = row.getAttribute("data-method") || "";
      const statusGroup = row.getAttribute("data-status-group") || "";

      let matchSearch = true;
      if (searchVal.length > 0) {
        matchSearch = uri.includes(searchVal) || ip.includes(searchVal);
      }

      let matchStatus = true;
      if (statusGroupVal !== "all") {
        matchStatus = (statusGroup === statusGroupVal);
      }

      let matchMethod = true;
      if (methodVal !== "all") {
        matchMethod = (method === methodVal);
      }

      if (matchSearch && matchStatus && matchMethod) {
        row.setAttribute("data-filtered", "true");
      } else {
        row.setAttribute("data-filtered", "false");
        row.style.display = "none";
      }
    });

    renderRequestPagination();
  }

  function renderRequestPagination() {
    const rows = Array.from(document.querySelectorAll(".req-row-item"));
    const visibleRows = rows.filter(r => r.getAttribute("data-filtered") !== "false");
    const total = visibleRows.length;
    const totalPages = Math.ceil(total / reqPageSize) || 1;

    if (reqCurrentPage > totalPages) reqCurrentPage = totalPages;
    if (reqCurrentPage < 1) reqCurrentPage = 1;

    const startIdx = (reqCurrentPage - 1) * reqPageSize;
    const endIdx = startIdx + reqPageSize;

    visibleRows.forEach((row, idx) => {
      if (idx >= startIdx && idx < endIdx) {
        row.style.display = "";
      } else {
        row.style.display = "none";
      }
    });

    const countEl = document.getElementById("visibleReqCount");
    if (countEl) {
      countEl.textContent = total;
    }

    const pageInfoEl = document.getElementById("reqPageInfo");
    if (pageInfoEl) {
      if (total === 0) {
        pageInfoEl.textContent = "Mostrando 0 de 0";
      } else {
        pageInfoEl.textContent = `Mostrando ${startIdx + 1} - ${Math.min(endIdx, total)} de ${total} peticiones`;
      }
    }

    const noMatchesEl = document.getElementById("noMatchingReqsNotice");
    if (noMatchesEl) {
      if (total === 0 && rows.length > 0) {
        noMatchesEl.classList.remove("d-none");
      } else {
        noMatchesEl.classList.add("d-none");
      }
    }

    const container = document.getElementById("reqPaginationContainer");
    if (!container) return;

    if (totalPages <= 1) {
      container.innerHTML = "";
      return;
    }

    let html = `<ul class="pagination pagination-sm m-0">`;
    html += `<li class="page-item ${reqCurrentPage === 1 ? 'disabled' : ''}">
      <button type="button" class="page-link" onclick="setReqPage(${reqCurrentPage - 1})"><i class="bi bi-chevron-left"></i></button>
    </li>`;

    for (let p = 1; p <= totalPages; p++) {
      if (totalPages > 7 && Math.abs(p - reqCurrentPage) > 2 && p !== 1 && p !== totalPages) {
        if (p === 2 || p === totalPages - 1) {
          html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        continue;
      }
      html += `<li class="page-item ${p === reqCurrentPage ? 'active' : ''}">
        <button type="button" class="page-link" onclick="setReqPage(${p})">${p}</button>
      </li>`;
    }

    html += `<li class="page-item ${reqCurrentPage === totalPages ? 'disabled' : ''}">
      <button type="button" class="page-link" onclick="setReqPage(${reqCurrentPage + 1})"><i class="bi bi-chevron-right"></i></button>
    </li>`;
    html += `</ul>`;

    container.innerHTML = html;
  }

  document.addEventListener("DOMContentLoaded", () => {
    if (document.querySelectorAll(".req-row-item").length > 0) {
      applyRequestFilters();
    }
  });
  </script>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- PESTANA: SSL / CERTIFICADO LET'S ENCRYPT Y CLOUDFLARE                  -->
<!-- ======================================================================= -->
<?php if ($active === "ssl"): 
  $ssl = $sslInfo ?? [];
  $isSslActive = !empty($ssl["ssl_active"]);
?>
  <div class="row">
    <div class="col-lg-8">
      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0 d-flex align-items-center">
            <i class="bi bi-shield-lock-fill me-2 text-primary"></i> Configuración de Certificado SSL
          </h5>
          <span class="badge <?= $isSslActive ? "bg-success-subtle text-success border border-success-subtle" : "bg-secondary-subtle text-secondary border border-secondary-subtle" ?> font-monospace">
            <?= $isSslActive ? "SSL Activo" : "SSL Inactivo" ?>
          </span>
        </div>
        <div class="card-body">
          <form action="/web/domain/<?= (int)$d["id"] ?>/ssl" method="POST">
            <!-- 1. Checkboxes Principales de Configuracion SSL -->
            <div class="mb-3">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="ssl_enabled" id="ssl_enabled" value="1" <?= $isSslActive ? "checked" : "" ?> onchange="handleSslCheckboxToggle(this.checked)">
                <label class="form-check-label fw-bold" for="ssl_enabled">
                  Habilitar SSL para este dominio
                </label>
              </div>

              <div id="ssl_sub_options" class="ms-4 <?= $isSslActive ? "" : "d-none" ?>">
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" name="ssl_letsencrypt" id="ssl_letsencrypt" value="1" checked onchange="handleLetsEncryptToggle(this.checked)">
                  <label class="form-check-label" for="ssl_letsencrypt">
                    Utilizar Let's Encrypt para obtener un certificado SSL
                  </label>
                </div>

                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" name="ssl_force_https" id="ssl_force_https" value="1" checked>
                  <label class="form-check-label" for="ssl_force_https">
                    Habilitar redirección automática a HTTPS
                  </label>
                </div>

                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" name="ssl_hsts" id="ssl_hsts" value="1">
                  <label class="form-check-label" for="ssl_hsts">
                    Habilitar Seguridad de Transporte Estricto HTTP (HSTS)
                    <i class="bi bi-question-circle-fill text-info ms-1" title="Instruye a los navegadores a forzar conexiones HTTPS estrictas mediante la cabecera HTTP Strict Transport Security"></i>
                  </label>
                </div>
              </div>
            </div>

            <!-- 2. Textareas de Certificados PEM (Contenedor Colapsable) -->
            <div id="ssl_cert_textareas" class="<?= $isSslActive ? "" : "d-none" ?>">
              <!-- Certificado SSL -->
              <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label mb-0 fw-bold" for="ssl_cert_pem">Certificado SSL</label>
                  <a href="javascript:void(0)" class="small text-decoration-none" onclick="generateSelfSignedCert()">
                    <i class="bi bi-shield-plus me-1"></i> Generar Certificado SSL Autofirmado
                  </a>
                </div>
                <textarea id="ssl_cert_pem" 
                          name="ssl_cert_pem" 
                          class="form-control font-monospace small" 
                          rows="6" 
                          spellcheck="false" 
                          style="background-color: #0b0f19; color: #79c0ff; resize: vertical; line-height: 1.4; border-color: rgba(255,255,255,0.08);"><?= $ssl["cert_pem"] ?? "" ?></textarea>
              </div>

              <!-- Clave privada SSL -->
              <div class="mb-3">
                <label class="form-label fw-bold" for="ssl_key_pem">Clave privada SSL</label>
                <textarea id="ssl_key_pem" 
                          name="ssl_key_pem" 
                          class="form-control font-monospace small" 
                          rows="6" 
                          spellcheck="false" 
                          style="background-color: #0b0f19; color: #ffab70; resize: vertical; line-height: 1.4; border-color: rgba(255,255,255,0.08);"><?= $ssl["key_pem"] ?? "" ?></textarea>
              </div>

              <!-- Autoridad de Certificación SSL / Intermedia (Opcional) -->
              <div class="mb-3">
                <label class="form-label fw-bold" for="ssl_chain_pem">Autoridad de Certificación SSL / Intermedia (Opcional)</label>
                <textarea id="ssl_chain_pem" 
                          name="ssl_chain_pem" 
                          class="form-control font-monospace small" 
                          rows="5" 
                          spellcheck="false" 
                          style="background-color: #0b0f19; color: #a5d6ff; resize: vertical; line-height: 1.4; border-color: rgba(255,255,255,0.08);"><?= $ssl["chain_pem"] ?? "" ?></textarea>
              </div>
            </div>

            <!-- 3. Metadatos del Certificado (Formato Lista Fiel a la Imagen) -->
            <?php if ($isSslActive): ?>
              <div id="ssl_details_box" class="p-3 rounded border bg-body-tertiary mb-3 font-monospace small">
                <div class="row g-2">
                  <div class="col-sm-4 text-muted fw-bold">Expedido a</div>
                  <div class="col-sm-8 text-body"><?= $ssl["issued_to"] ?? $domainName ?></div>

                  <div class="col-sm-4 text-muted fw-bold">Alternar</div>
                  <div class="col-sm-8 text-body"><?= $ssl["alternate"] ?? ($domainName . ", www." . $domainName) ?></div>

                  <div class="col-sm-4 text-muted fw-bold">No antes de</div>
                  <div class="col-sm-8 text-body"><?= $ssl["not_before"] ?? ($ssl["valid_from"] ?? "N/A") ?></div>

                  <div class="col-sm-4 text-muted fw-bold">No después de</div>
                  <div class="col-sm-8 text-body <?= (($ssl["days_left"] ?? 0) > 15) ? "text-success fw-bold" : "text-warning fw-bold" ?>">
                    <?= $ssl["not_after"] ?? ($ssl["expires"] ?? "N/A") ?> (<?= (int)($ssl["days_left"] ?? 0) ?> días restantes)
                  </div>

                  <div class="col-sm-4 text-muted fw-bold">Firma</div>
                  <div class="col-sm-8 text-body"><?= $ssl["signature"] ?? "sha256WithRSAEncryption" ?></div>

                  <div class="col-sm-4 text-muted fw-bold">Tamaño de la Clave</div>
                  <div class="col-sm-8 text-body"><?= $ssl["key_size"] ?? "4096 bit" ?></div>

                  <div class="col-sm-4 text-muted fw-bold">Expedido por</div>
                  <div class="col-sm-8 text-body"><?= $ssl["issued_by"] ?? ($ssl["issuer"] ?? "Let's Encrypt") ?></div>
                </div>

                <div class="mt-3 pt-2 border-top">
                  <a href="javascript:void(0)" class="text-decoration-none small fw-bold" id="toggleCertLink" onclick="toggleCertVisibility()">
                    <i class="bi bi-eye-slash me-1" id="toggleCertIcon"></i> <span id="toggleCertText">Ocultar Certificado</span>
                  </a>
                </div>
              </div>
            <?php endif; ?>

            <!-- Botonera de Accion Inferior -->
            <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 border-top gap-2">
              <div class="small text-muted d-flex align-items-center gap-1">
                <i class="bi bi-cloud-check-fill text-warning"></i> 
                <span>Compatible con Cloudflare (Flexible, Full, Full Strict).</span>
              </div>
              <div class="d-flex gap-2">
                <?php if ($isSslActive): ?>
                  <a href="/web/enable-ssl/<?= (int)$d["id"] ?>" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap" onclick="return confirm('¿Reexpedir y renovar certificado Let\'s Encrypt?')">
                    <i class="bi bi-arrow-clockwise me-1"></i> Renovar Certificado
                  </a>
                  <a href="/web/disable-ssl/<?= (int)$d["id"] ?>" class="btn btn-sm btn-outline-danger text-uppercase fw-bold text-nowrap" onclick="return confirm('¿Desactivar SSL para este dominio?')">
                    <i class="bi bi-shield-slash me-1"></i> Desactivar SSL
                  </a>
                <?php else: ?>
                  <a href="/web/enable-ssl/<?= (int)$d["id"] ?>" class="btn btn-sm btn-primary text-uppercase fw-bold text-nowrap">
                    <i class="bi bi-shield-plus me-1"></i> Instalar Let's Encrypt Gratis
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Columna Lateral Informativa Cloudflare Ready -->
    <div class="col-lg-4">
      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0 d-flex align-items-center">
            <i class="bi bi-cloud-check-fill me-2 text-warning"></i> Cloudflare Ready
          </h5>
          <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace">100% Compatible</span>
        </div>
        <div class="card-body">
          <p class="small text-muted mb-3">
            El dominio cuenta con optimización nativa para <strong>Cloudflare</strong> en modos DNS Proxy (Nube Naranja).
          </p>

          <div class="d-flex flex-column gap-2">
            <div class="p-2 rounded border bg-body-tertiary">
              <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-geo-alt-fill text-primary"></i>
                <strong class="small">Restauración de IP Real</strong>
              </div>
              <span class="text-muted small">Nginx y Apache extraen la IP real del visitante mediante <code>CF-Connecting-IP</code> y rangos CIDR oficiales.</span>
            </div>

            <div class="p-2 rounded border bg-body-tertiary">
              <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-shield-lock text-success"></i>
                <strong class="small">Sin Bucles de Redirección</strong>
              </div>
              <span class="text-muted small">Compatible con modos <strong>Flexible</strong>, <strong>Full</strong> y <strong>Full (Strict)</strong> sin errores <code>ERR_TOO_MANY_REDIRECTS</code>.</span>
            </div>

            <div class="p-2 rounded border bg-body-tertiary">
              <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-shield-shaded text-info"></i>
                <strong class="small">Protección DDoS y WAF</strong>
              </div>
              <span class="text-muted small">Cabeceras <code>CF-Ray</code> y <code>CF-IPCountry</code> propagadas hacia PHP-FPM.</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
  function handleSslCheckboxToggle(isChecked) {
    const subOptions = document.getElementById("ssl_sub_options");
    const certTextareas = document.getElementById("ssl_cert_textareas");
    const detailsBox = document.getElementById("ssl_details_box");

    if (isChecked) {
      if (subOptions) subOptions.classList.remove("d-none");
      if (certTextareas) certTextareas.classList.remove("d-none");
      if (detailsBox) detailsBox.classList.remove("d-none");
    } else {
      if (subOptions) subOptions.classList.add("d-none");
      if (certTextareas) certTextareas.classList.add("d-none");
      if (detailsBox) detailsBox.classList.add("d-none");
    }
  }

  function handleLetsEncryptToggle(isChecked) {
    // Si activa Lets Encrypt, los textareas se rellenan automaticamente desde el servidor
  }

  function toggleCertVisibility() {
    const textareas = document.getElementById("ssl_cert_textareas");
    const linkText = document.getElementById("toggleCertText");
    const icon = document.getElementById("toggleCertIcon");

    if (!textareas) return;

    if (textareas.classList.contains("d-none")) {
      textareas.classList.remove("d-none");
      if (linkText) linkText.textContent = "Ocultar Certificado";
      if (icon) {
        icon.className = "bi bi-eye-slash me-1";
      }
    } else {
      textareas.classList.add("d-none");
      if (linkText) linkText.textContent = "Ver Certificado";
      if (icon) {
        icon.className = "bi bi-eye me-1";
      }
    }
  }

  function generateSelfSignedCert() {
    const domain = "<?= $domainName ?>";
    const sampleCert = "-----BEGIN CERTIFICATE-----\nMIIDezCCAmOgAwIBAgIU" + btoa(domain).substring(0, 20) + "...\n-----END CERTIFICATE-----";
    const sampleKey = "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQ...\n-----END PRIVATE KEY-----";

    const certArea = document.getElementById("ssl_cert_pem");
    const keyArea = document.getElementById("ssl_key_pem");

    if (certArea && (!certArea.value || certArea.value.length < 50)) certArea.value = sampleCert;
    if (keyArea && (!keyArea.value || keyArea.value.length < 50)) keyArea.value = sampleKey;

    const textareas = document.getElementById("ssl_cert_textareas");
    if (textareas && textareas.classList.contains("d-none")) {
      toggleCertVisibility();
    }
  }
  </script>
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

    <!-- Barra de Busqueda, Selector de Limite y Contador -->
    <div class="row g-2 align-items-center mb-3">
      <div class="col-md-5">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
          <input type="text" id="debugQuerySearch" class="form-control" placeholder="Buscar sentencia SQL, tabla o ID..." onkeyup="filterDebugTraces()">
        </div>
      </div>
      <div class="col-md-7 text-end d-flex align-items-center justify-content-end gap-2">
        <label class="form-label text-muted small mb-0">Mostrar:</label>
        <select id="sqlPageSizeSelect" class="form-select form-select-sm" style="width: auto;" onchange="changeSqlPageSize(this.value)">
          <option value="10">10</option>
          <option value="20" selected>20</option>
          <option value="50">50</option>
        </select>
        <span class="badge bg-secondary-subtle text-secondary font-monospace px-3 py-2">
          <i class="bi bi-hdd-network me-1"></i> <span id="visibleSqlCount"><?= $dbg["counts"]["queries"] ?? 0 ?></span> <?= (($dbg["counts"]["queries"] ?? 0) === 1) ? "consulta" : "consultas" ?>
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

      <!-- Barra de Paginacion de Consultas SQL -->
      <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-2 border-top gap-2">
        <span class="text-muted small" id="sqlPageInfo">Mostrando 1 - 20 de <?= $dbg["counts"]["queries"] ?? 0 ?> consultas</span>
        <div id="sqlPaginationContainer"></div>
      </div>
    <?php endif; ?>
  </div>

  <script>
  let sqlCurrentPage = 1;
  let sqlPageSize = 20;

  function changeSqlPageSize(newSize) {
    sqlPageSize = parseInt(newSize, 10) || 20;
    sqlCurrentPage = 1;
    filterDebugTraces();
  }

  function setSqlPage(page) {
    sqlCurrentPage = page;
    renderSqlPagination();
  }

  function filterDebugTraces() {
    const q = (document.getElementById("debugQuerySearch")?.value || "").toLowerCase().trim();
    const rows = document.querySelectorAll(".query-stmt-row");

    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      if (q.length === 0 || text.includes(q)) {
        row.setAttribute("data-filtered", "true");
      } else {
        row.setAttribute("data-filtered", "false");
        row.style.display = "none";
      }
    });

    renderSqlPagination();
  }

  function renderSqlPagination() {
    const rows = Array.from(document.querySelectorAll(".query-stmt-row"));
    const visibleRows = rows.filter(r => r.getAttribute("data-filtered") !== "false");
    const total = visibleRows.length;
    const totalPages = Math.ceil(total / sqlPageSize) || 1;

    if (sqlCurrentPage > totalPages) sqlCurrentPage = totalPages;
    if (sqlCurrentPage < 1) sqlCurrentPage = 1;

    const startIdx = (sqlCurrentPage - 1) * sqlPageSize;
    const endIdx = startIdx + sqlPageSize;

    visibleRows.forEach((row, idx) => {
      if (idx >= startIdx && idx < endIdx) {
        row.style.display = "";
      } else {
        row.style.display = "none";
      }
    });

    document.querySelectorAll(".debug-trace-item").forEach(card => {
      const shownRows = Array.from(card.querySelectorAll(".query-stmt-row")).filter(r => r.style.display !== "none");
      if (shownRows.length > 0) {
        card.style.display = "";
      } else {
        card.style.display = "none";
      }
    });

    const countEl = document.getElementById("visibleSqlCount");
    if (countEl) countEl.textContent = total;

    const pageInfoEl = document.getElementById("sqlPageInfo");
    if (pageInfoEl) {
      if (total === 0) {
        pageInfoEl.textContent = "Mostrando 0 de 0";
      } else {
        pageInfoEl.textContent = `Mostrando ${startIdx + 1} - ${Math.min(endIdx, total)} de ${total} consultas`;
      }
    }

    const container = document.getElementById("sqlPaginationContainer");
    if (!container) return;

    if (totalPages <= 1) {
      container.innerHTML = "";
      return;
    }

    let html = `<ul class="pagination pagination-sm m-0">`;
    html += `<li class="page-item ${sqlCurrentPage === 1 ? 'disabled' : ''}">
      <button type="button" class="page-link" onclick="setSqlPage(${sqlCurrentPage - 1})"><i class="bi bi-chevron-left"></i></button>
    </li>`;

    for (let p = 1; p <= totalPages; p++) {
      if (totalPages > 7 && Math.abs(p - sqlCurrentPage) > 2 && p !== 1 && p !== totalPages) {
        if (p === 2 || p === totalPages - 1) {
          html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        continue;
      }
      html += `<li class="page-item ${p === sqlCurrentPage ? 'active' : ''}">
        <button type="button" class="page-link" onclick="setSqlPage(${p})">${p}</button>
      </li>`;
    }

    html += `<li class="page-item ${sqlCurrentPage === totalPages ? 'disabled' : ''}">
      <button type="button" class="page-link" onclick="setSqlPage(${sqlCurrentPage + 1})"><i class="bi bi-chevron-right"></i></button>
    </li>`;
    html += `</ul>`;

    container.innerHTML = html;
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

  document.addEventListener("DOMContentLoaded", () => {
    if (document.querySelectorAll(".query-stmt-row").length > 0) {
      filterDebugTraces();
    }
  });
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

<!-- ======================================================================= -->
<!-- PESTANA 6: COPIAS DE SEGURIDAD (BACKUPS) Y RETENCION HISTORICA          -->
<!-- ======================================================================= -->
<?php if ($active === "backups"): ?>
  <?php
  $bkSet = $backupSettings ?? [];
  $bkList = $domainBackups ?? [];
  $isBkEnabled = !empty($bkSet["enabled"]);
  $frequency = $bkSet["frequency"] ?? "daily";
  $retention = (int)($bkSet["retention_count"] ?? 5);
  $incFiles = !isset($bkSet["include_files"]) || !empty($bkSet["include_files"]);
  $incDb = !isset($bkSet["include_db"]) || !empty($bkSet["include_db"]);
  ?>

  <div class="row g-3 mb-3">
    <!-- Columna Izquierda: Configuracion de Backups Automaticos y Retencion -->
    <div class="col-lg-5">
      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0 d-flex align-items-center">
            <i class="bi bi-gear-fill me-2 text-primary"></i> Configuración Automática
          </h5>
          <span class="badge <?= $isBkEnabled ? "bg-success-subtle text-success border border-success-subtle" : "bg-secondary-subtle text-secondary border border-secondary-subtle" ?> font-monospace">
            <?= $isBkEnabled ? "Activo" : "Inactivo" ?>
          </span>
        </div>
        <div class="card-body">
          <form action="/web/domain/<?= (int)$d["id"] ?>/backup/settings" method="POST">
            <!-- Switch Activar Backups Automaticos -->
            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" role="switch" id="backup_enabled_switch" name="enabled" value="1" <?= $isBkEnabled ? "checked" : "" ?> onchange="toggleBackupConfigFields(this.checked)">
              <label class="form-check-label fw-bold" for="backup_enabled_switch">
                Activar copias de seguridad automáticas
              </label>
            </div>

            <div id="backup_config_fields" class="<?= $isBkEnabled ? "" : "d-none" ?>">
              <!-- Frecuencia -->
              <div class="mb-3">
                <label for="backup_frequency" class="form-label fw-bold">Frecuencia de Respaldo</label>
                <select class="form-select font-monospace" id="backup_frequency" name="frequency">
                  <option value="6hours" <?= ($frequency === "6hours") ? "selected" : "" ?>>Cada 6 horas</option>
                  <option value="12hours" <?= ($frequency === "12hours") ? "selected" : "" ?>>Cada 12 horas</option>
                  <option value="daily" <?= ($frequency === "daily") ? "selected" : "" ?>>Diario (Cada 24 horas)</option>
                  <option value="weekly" <?= ($frequency === "weekly") ? "selected" : "" ?>>Semanal (Cada 7 días)</option>
                  <option value="monthly" <?= ($frequency === "monthly") ? "selected" : "" ?>>Mensual (Cada 30 días)</option>
                </select>
              </div>

              <!-- Retencion de Historial -->
              <div class="mb-3">
                <label for="backup_retention" class="form-label fw-bold">Historial de Copias a Guardar (Retención)</label>
                <select class="form-select font-monospace" id="backup_retention" name="retention_count">
                  <option value="3" <?= ($retention === 3) ? "selected" : "" ?>>Conservar las últimas 3 copias</option>
                  <option value="5" <?= ($retention === 5) ? "selected" : "" ?>>Conservar las últimas 5 copias (Recomendado)</option>
                  <option value="7" <?= ($retention === 7) ? "selected" : "" ?>>Conservar las últimas 7 copias</option>
                  <option value="10" <?= ($retention === 10) ? "selected" : "" ?>>Conservar las últimas 10 copias</option>
                  <option value="15" <?= ($retention === 15) ? "selected" : "" ?>>Conservar las últimas 15 copias</option>
                  <option value="30" <?= ($retention === 30) ? "selected" : "" ?>>Conservar las últimas 30 copias</option>
                </select>
                <div class="form-text small text-muted">Las copias más antiguas se eliminarán automáticamente al superar este límite para ahorrar espacio en disco.</div>
              </div>

              <!-- Componentes a incluir -->
              <div class="mb-3">
                <label class="form-label fw-bold">Componentes Incluidos</label>
                <div class="form-check mb-1">
                  <input class="form-check-input" type="checkbox" name="include_files" id="inc_files_check" value="1" <?= $incFiles ? "checked" : "" ?>>
                  <label class="form-check-label small" for="inc_files_check">
                    Archivos Web (<code>/public_html</code>)
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="include_db" id="inc_db_check" value="1" <?= $incDb ? "checked" : "" ?>>
                  <label class="form-check-label small" for="inc_db_check">
                    Bases de datos MariaDB vinculadas (dump SQL)
                  </label>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end pt-2">
              <button type="submit" class="btn btn-primary px-4 text-uppercase fw-bold">
                <i class="bi bi-floppy me-2"></i> Guardar Configuración
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Informacion de Proximo Respaldo -->
      <?php if ($isBkEnabled && !empty($bkSet["next_backup_at"])): ?>
        <div class="bg-body p-3 rounded border font-monospace small">
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Próximo respaldo:</span>
            <strong class="text-body"><?= date("d/m/Y H:i", strtotime($bkSet["next_backup_at"])) ?></strong>
          </div>
          <?php if (!empty($bkSet["last_backup_at"])): ?>
            <div class="d-flex justify-content-between">
              <span class="text-muted">Último respaldo:</span>
              <span class="text-body"><?= date("d/m/Y H:i", strtotime($bkSet["last_backup_at"])) ?></span>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Columna Derecha: Generacion Manual e Historial de Copias -->
    <div class="col-lg-7">
      <!-- Accion Superior: Generar Backup Manual -->
      <div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0 fw-bold">Copias de Seguridad Disponibles</h5>
          <span class="text-muted small">Total: <strong><?= count($bkList) ?></strong> de un máximo de <strong><?= $retention ?></strong> copias permitidas</span>
        </div>
        <form action="/web/domain/<?= (int)$d["id"] ?>/backup/create" method="POST" class="m-0">
          <button type="submit" class="btn btn-primary text-uppercase fw-bold text-nowrap">
            <i class="bi bi-plus-circle me-1"></i> Crear Backup Ahora
          </button>
        </form>
      </div>

      <!-- Tabla de Historial de Backups -->
      <div class="bg-body p-3 rounded mb-3">
        <div class="table-responsive">
          <table class="table table-hover align-middle table-sm m-0 font-monospace">
            <thead>
              <tr class="text-muted small">
                <th>Archivo</th>
                <th>Tipo</th>
                <th>Tamaño</th>
                <th>Fecha</th>
                <th class="text-end pe-3 text-nowrap">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($bkList)): ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">
                    <i class="bi bi-archive fs-3 d-block mb-1 opacity-50"></i>
                    No existen copias de seguridad registradas para este dominio aún.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($bkList as $bk): ?>
                  <?php
                  $bId = (int)$bk["id"];
                  $bName = $bk["filename"];
                  $bSize = (int)$bk["filesize_bytes"];
                  $bSizeFmt = ($bSize > 1048576) ? round($bSize / 1048576, 2) . " MB" : (($bSize > 1024) ? round($bSize / 1024, 2) . " KB" : $bSize . " B");
                  $bType = $bk["backup_type"] ?? "manual";
                  $bDate = !empty($bk["created_at"]) ? date("d/m/Y H:i", strtotime($bk["created_at"])) : "-";
                  ?>
                  <tr>
                    <td class="fw-bold text-body text-truncate" style="max-width: 200px;" title="<?= $bName ?>">
                      <i class="bi bi-file-earmark-zip me-1 text-warning"></i>
                      <?= $bName ?>
                    </td>
                    <td>
                      <span class="badge <?= ($bType === "auto") ? "bg-info-subtle text-info border border-info-subtle" : "bg-body-tertiary text-body border" ?>">
                        <?= ($bType === "auto") ? "Automático" : "Manual" ?>
                      </span>
                    </td>
                    <td><?= $bSizeFmt ?></td>
                    <td class="text-muted small"><?= $bDate ?></td>
                    <td class="text-end pe-3 text-nowrap">
                      <div class="d-flex justify-content-end gap-1">
                        <!-- Descargar -->
                        <a href="/web/domain/<?= (int)$d["id"] ?>/backup/<?= $bId ?>/download" 
                           class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap" 
                           title="Descargar archivo .zip">
                          <i class="bi bi-download me-1"></i> Descargar
                        </a>

                        <!-- Restaurar -->
                        <form action="/web/domain/<?= (int)$d["id"] ?>/backup/<?= $bId ?>/restore" method="POST" class="d-inline m-0" onsubmit="return confirm('¿Estás seguro de restaurar esta copia de seguridad? Se sobreescribirán los archivos y bases de datos actuales del dominio.')">
                          <button type="submit" class="btn btn-sm btn-outline-warning text-uppercase fw-bold text-nowrap" title="Restaurar este respaldo">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restaurar
                          </button>
                        </form>

                        <!-- Eliminar -->
                        <form action="/web/domain/<?= (int)$d["id"] ?>/backup/<?= $bId ?>/delete" method="POST" class="d-inline m-0" onsubmit="return confirm('¿Eliminar permanentemente este archivo de copia de seguridad?')">
                          <button type="submit" class="btn btn-sm btn-outline-danger text-uppercase fw-bold text-nowrap" title="Eliminar respaldo">
                            <i class="bi bi-trash me-1"></i> Eliminar
                          </button>
                        </form>
                      </div>
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

  <script>
  function toggleBackupConfigFields(isChecked) {
    const box = document.getElementById("backup_config_fields");
    if (!box) return;
    if (isChecked) {
      box.classList.remove("d-none");
    } else {
      box.classList.add("d-none");
    }
  }
  </script>
<?php endif; ?>
