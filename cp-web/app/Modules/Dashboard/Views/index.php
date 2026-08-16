<div class="bg-body p-3 rounded mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
  <div>
    <h1 class="h4 mb-0 fw-bold d-flex align-items-center">
      <i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard
    </h1>
    <span class="text-muted small">Panel general de control y monitoreo de recursos en tiempo real.</span>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="/web/create" class="btn btn-sm btn-primary text-uppercase fw-bold text-nowrap">
      <i class="bi bi-plus-lg me-1"></i> Nuevo Dominio
    </a>
    <a href="/database/create" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap">
      <i class="bi bi-database-add me-1"></i> Nueva Base de Datos
    </a>
    <a href="/files" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
      <i class="bi bi-folder2-open me-1"></i> Archivos
    </a>
    <a href="/phpmyadmin" target="_blank" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
      <i class="bi bi-box-arrow-up-right me-1"></i> phpMyAdmin
    </a>
  </div>
</div>

<!-- Tarjetas de Metricas Generales -->
<div class="row g-3 mb-3">
  <!-- Dominios Web -->
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted small text-uppercase fw-bold">Dominios Web</span>
          <div class="p-2 rounded bg-primary-subtle text-primary">
            <i class="bi bi-globe fs-5"></i>
          </div>
        </div>
        <h2 class="h3 fw-bold mb-1"><?= (int)$domainCount ?></h2>
        <div class="small text-muted">
          <span class="text-success fw-bold"><i class="bi bi-shield-check me-1"></i><?= (int)$sslCount ?></span> con SSL activo
        </div>
      </div>
    </div>
  </div>

  <!-- Bases de Datos MariaDB -->
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted small text-uppercase fw-bold">Bases de Datos</span>
          <div class="p-2 rounded bg-success-subtle text-success">
            <i class="bi bi-database fs-5"></i>
          </div>
        </div>
        <h2 class="h3 fw-bold mb-1"><?= (int)$dbCount ?></h2>
        <div class="small text-muted">
          <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>MariaDB Server activo</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Cuentas y Servicios -->
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted small text-uppercase fw-bold">Cuentas y Tareas</span>
          <div class="p-2 rounded bg-info-subtle text-info">
            <i class="bi bi-hdd-network fs-5"></i>
          </div>
        </div>
        <h2 class="h3 fw-bold mb-1"><?= (int)($mailCount + $ftpCount + $cronCount) ?></h2>
        <div class="small text-muted">
          <span><?= (int)$mailCount ?> Correos · <?= (int)$ftpCount ?> FTP · <?= (int)$cronCount ?> Cron</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Almacenamiento en Disco -->
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted small text-uppercase fw-bold">Uso de Disco (/)</span>
          <div class="p-2 rounded bg-danger-subtle text-danger">
            <i class="bi bi-hdd fs-5"></i>
          </div>
        </div>
        <h2 class="h3 fw-bold mb-1"><?= $metrics["disk"]["percent"] ?? "0%" ?></h2>
        <div class="small text-muted">
          <span><?= $metrics["disk"]["used"] ?? "0" ?> usados de <?= $metrics["disk"]["total"] ?? "0" ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Rendimiento del Servidor y Estado de Servicios -->
<div class="row g-3 mb-3">
  <!-- Informacion del Servidor y Recursos -->
  <div class="col-12 col-lg-6 d-flex">
    <div class="card flex-fill w-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
          <i class="bi bi-cpu me-2 text-primary"></i> Recursos del Servidor
        </h5>
        <span class="badge bg-secondary-subtle text-secondary font-monospace"><?= $metrics["hostname"] ?? "servidor" ?></span>
      </div>
      <div class="card-body">
        <table class="table table-borderless align-middle table-sm mb-0">
          <tbody>
            <tr class="border-bottom">
              <td class="text-muted py-2" style="width: 35%;">Tiempo Activo:</td>
              <td class="py-2 font-monospace small"><?= $metrics["uptime"] ?? "N/A" ?></td>
            </tr>
            <tr class="border-bottom">
              <td class="text-muted py-2">Carga CPU:</td>
              <td class="py-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1">
                  <i class="bi bi-activity me-1"></i> <?= $metrics["load"] ?? "N/A" ?>
                </span>
              </td>
            </tr>
            <tr class="border-bottom">
              <td class="text-muted py-2">Memoria RAM:</td>
              <td class="py-2">
                <?php
                  $memTotal = (int)($metrics["memory"]["total_mb"] ?? 1);
                  $memUsed = (int)($metrics["memory"]["used_mb"] ?? 0);
                  $memPercent = round(($memUsed / max($memTotal, 1)) * 100);
                ?>
                <div class="d-flex justify-content-between small text-muted mb-1 font-monospace">
                  <span><?= $memUsed ?> MB usados</span>
                  <span><?= $memPercent ?>% (<?= $memTotal ?> MB)</span>
                </div>
                <div class="progress mb-0" style="height: 6px;">
                  <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $memPercent ?>%;"></div>
                </div>
              </td>
            </tr>
            <tr>
              <td class="text-muted py-2">Espacio en Disco:</td>
              <td class="py-2">
                <div class="d-flex justify-content-between small text-muted mb-1 font-monospace">
                  <span><?= $metrics["disk"]["used"] ?? "0" ?> / <?= $metrics["disk"]["total"] ?? "0" ?></span>
                  <span>Libre: <?= $metrics["disk"]["free"] ?? "0" ?></span>
                </div>
                <?php $diskPercentNum = (int)str_replace("%", "", $metrics["disk"]["percent"] ?? "0"); ?>
                <div class="progress mb-0" style="height: 6px;">
                  <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $diskPercentNum ?>%;"></div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Estado de Servicios Principales -->
  <div class="col-12 col-lg-6 d-flex">
    <div class="card flex-fill w-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
          <i class="bi bi-hdd-stack me-2 text-success"></i> Estado de Servicios
        </h5>
        <a href="/system" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
          <i class="bi bi-gear me-1"></i> Gestionar
        </a>
      </div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush">
          <!-- Nginx Proxy -->
          <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 border-bottom">
            <div class="d-flex align-items-center">
              <div class="p-2 rounded bg-primary-subtle text-primary me-3">
                <i class="bi bi-arrow-left-right"></i>
              </div>
              <div>
                <strong class="d-block text-body">Nginx (Proxy Reverso)</strong>
                <span class="text-muted small">Frontend, estáticos y SSL (Puerto 80 / 443)</span>
              </div>
            </div>
            <?php $nginxStatus = $services["nginx"] ?? "inactive"; ?>
            <span class="badge <?= ($nginxStatus === "active") ? "bg-success-subtle text-success border border-success-subtle" : "bg-danger-subtle text-danger border border-danger-subtle" ?> px-3 py-1 font-monospace">
              <?= strtoupper($nginxStatus) ?>
            </span>
          </div>

          <!-- Apache Backend -->
          <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 border-bottom">
            <div class="d-flex align-items-center">
              <div class="p-2 rounded bg-info-subtle text-info me-3">
                <i class="bi bi-server"></i>
              </div>
              <div>
                <strong class="d-block text-body">Apache 2 (Backend Web)</strong>
                <span class="text-muted small">Soporte .htaccess y mod_rewrite (127.0.0.1:8080)</span>
              </div>
            </div>
            <?php $apacheStatus = $services["apache"] ?? "inactive"; ?>
            <span class="badge <?= ($apacheStatus === "active") ? "bg-success-subtle text-success border border-success-subtle" : "bg-danger-subtle text-danger border border-danger-subtle" ?> px-3 py-1 font-monospace">
              <?= strtoupper($apacheStatus) ?>
            </span>
          </div>

          <!-- MariaDB -->
          <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 border-bottom">
            <div class="d-flex align-items-center">
              <div class="p-2 rounded bg-success-subtle text-success me-3">
                <i class="bi bi-database"></i>
              </div>
              <div>
                <strong class="d-block text-body">MariaDB Server</strong>
                <span class="text-muted small">Motor relacional de base de datos</span>
              </div>
            </div>
            <?php $mariaStatus = $services["mariadb"] ?? "inactive"; ?>
            <span class="badge <?= ($mariaStatus === "active") ? "bg-success-subtle text-success border border-success-subtle" : "bg-danger-subtle text-danger border border-danger-subtle" ?> px-3 py-1 font-monospace">
              <?= strtoupper($mariaStatus) ?>
            </span>
          </div>

          <!-- PHP-FPM Multi-Version -->
          <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
            <div class="d-flex align-items-center">
              <div class="p-2 rounded bg-warning-subtle text-warning me-3">
                <i class="bi bi-code-slash"></i>
              </div>
              <div>
                <strong class="d-block text-body">PHP-FPM Multi-Versión</strong>
                <span class="text-muted small">PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.4, 8.5</span>
              </div>
            </div>
            <a href="/php" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap">
              <i class="bi bi-code-slash me-1"></i> Versiones
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Seccion de Sitios Web y Bases de Datos Recientes -->
<div class="row g-3">
  <!-- Dominios Web -->
  <div class="col-12 col-lg-6">
    <div class="bg-body p-3 rounded mb-3 h-100 d-flex flex-column justify-content-between">
      <div>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0 fw-bold d-flex align-items-center">
            <i class="bi bi-globe me-2 text-primary"></i> Sitios Web del Servidor
          </h5>
          <a href="/web" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
            <i class="bi bi-arrow-right me-1"></i> Ver Todos
          </a>
        </div>

        <?php if (empty($recentDomains)): ?>
          <div class="text-muted small text-center py-4">No hay dominios web creados todavía.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle table-sm m-0">
              <thead>
                <tr class="text-muted small text-uppercase">
                  <th class="ps-2">Dominio</th>
                  <th>Stack / PHP</th>
                  <th>SSL</th>
                  <th class="text-end pe-2">Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentDomains as $dom): 
                  $stackIcon = "/assets/sitios/php.svg";
                  $stackTitle = "PHP";
                  if (($dom["stack"] ?? "") === "wordpress") {
                    $stackIcon = "/assets/sitios/wordpress.svg";
                    $stackTitle = "WordPress";
                  } elseif (($dom["stack"] ?? "") === "laravel") {
                    $stackIcon = "/assets/sitios/laravel.svg";
                    $stackTitle = "Laravel";
                  }
                ?>
                  <tr>
                    <td class="ps-2">
                      <div class="d-flex align-items-center">
                        <img src="<?= $stackIcon ?>" alt="<?= $stackTitle ?>" style="width: 18px; height: 18px; object-fit: contain;" class="me-2" title="<?= $stackTitle ?>">
                        <a href="/web/domain/<?= (int)$dom["id"] ?>" class="fw-bold text-decoration-none text-body">
                          <?= $dom["domain"] ?>
                        </a>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-secondary-subtle text-secondary font-monospace">PHP <?= $dom["php_version"] ?? "8.2" ?></span>
                    </td>
                    <td>
                      <?php if (!empty($dom["ssl_enabled"])): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace"><i class="bi bi-lock-fill me-1"></i>SSL</span>
                      <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary font-monospace">HTTP</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end pe-2 text-nowrap">
                      <div class="d-flex justify-content-end gap-1">
                        <a href="/web/domain/<?= (int)$dom["id"] ?>" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap" title="Gestionar Dominio">
                          <i class="bi bi-gear me-1"></i> Administrar
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
    </div>
  </div>

  <!-- Bases de Datos Recientes -->
  <div class="col-12 col-lg-6">
    <div class="bg-body p-3 rounded mb-3 h-100 d-flex flex-column justify-content-between">
      <div>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0 fw-bold d-flex align-items-center">
            <i class="bi bi-database me-2 text-success"></i> Bases de Datos MariaDB
          </h5>
          <a href="/database" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
            <i class="bi bi-arrow-right me-1"></i> Ver Todas
          </a>
        </div>

        <?php if (empty($recentDatabases)): ?>
          <div class="text-muted small text-center py-4">No hay bases de datos creadas todavía.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle table-sm m-0">
              <thead>
                <tr class="text-muted small text-uppercase">
                  <th class="ps-2">Base de Datos</th>
                  <th>Usuario</th>
                  <th class="text-end pe-2">Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentDatabases as $rdb): ?>
                  <tr>
                    <td class="ps-2">
                      <div class="d-flex align-items-center">
                        <img src="/assets/sitios/mariadb.svg" alt="MariaDB" style="width: 18px; height: 18px; object-fit: contain;" class="me-2">
                        <strong class="font-monospace text-body"><?= $rdb["db_name"] ?></strong>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-secondary-subtle text-secondary font-monospace"><?= $rdb["db_user"] ?></span>
                    </td>
                    <td class="text-end pe-2 text-nowrap">
                      <div class="d-flex justify-content-end gap-1">
                        <a href="/database/edit/<?= (int)$rdb["id"] ?>" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap">
                          <i class="bi bi-pencil me-1"></i> Editar
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
    </div>
  </div>
</div>
