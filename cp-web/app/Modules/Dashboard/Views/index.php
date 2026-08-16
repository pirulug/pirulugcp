<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
  <div>
    <h1 class="h4 mb-0">Dashboard</h1>
  </div>
  <div>
    <a href="/web/create" class="btn btn-sm btn-primary text-uppercase fw-bold text-nowrap me-2">
      <i class="bi bi-plus-lg me-1"></i> Nuevo Dominio
    </a>
    <a href="/database/create" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap">
      <i class="bi bi-database-add me-1"></i> Nueva Base de Datos
    </a>
  </div>
</div>

<!-- Tarjetas de Estadisticas -->
<div class="row g-3 mb-3">
  <!-- Dominios Web -->
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto">
            <div class="stat stat-primary">
              <i class="bi bi-globe"></i>
            </div>
          </div>
          <div class="col text-end">
            <h6 class="card-title text-body-secondary mb-1">Dominios Web</h6>
            <h2 class="h3 fw-bold mb-0"><?= (int)$domainCount ?></h2>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bases de Datos -->
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto">
            <div class="stat stat-success">
              <i class="bi bi-database"></i>
            </div>
          </div>
          <div class="col text-end">
            <h6 class="card-title text-body-secondary mb-1">Bases de Datos</h6>
            <h2 class="h3 fw-bold mb-0"><?= (int)$dbCount ?></h2>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Usuarios Panel -->
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto">
            <div class="stat stat-info">
              <i class="bi bi-people"></i>
            </div>
          </div>
          <div class="col text-end">
            <h6 class="card-title text-body-secondary mb-1">Usuarios Panel</h6>
            <h2 class="h3 fw-bold mb-0"><?= (int)$userCount ?></h2>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Uso de Disco -->
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto">
            <div class="stat stat-danger">
              <i class="bi bi-hdd"></i>
            </div>
          </div>
          <div class="col text-end">
            <h6 class="card-title text-body-secondary mb-1">Uso Disco (/)</h6>
            <h2 class="h3 fw-bold mb-0"><?= $metrics["disk"]["percent"] ?? "0%" ?></h2>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Metricas del Servidor y Estado de Servicios -->
<div class="row g-3 mb-3">
  <!-- Informacion del Servidor -->
  <div class="col-12 col-lg-6 d-flex">
    <div class="card flex-fill w-100">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="bi bi-cpu me-2 text-primary"></i>Informacion del Servidor
        </h5>
      </div>
      <div class="card-body">
        <table class="table table-borderless align-middle table-sm mb-0">
          <tbody>
            <tr class="border-bottom">
              <td class="text-body-secondary py-2" style="width: 35%;">Hostname:</td>
              <td class="fw-semibold py-2"><?= $metrics["hostname"] ?? "servidor" ?></td>
            </tr>
            <tr class="border-bottom">
              <td class="text-body-secondary py-2">Tiempo Activo:</td>
              <td class="py-2"><?= $metrics["uptime"] ?? "N/A" ?></td>
            </tr>
            <tr class="border-bottom">
              <td class="text-body-secondary py-2">Carga CPU:</td>
              <td class="py-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">
                  <?= $metrics["load"] ?? "N/A" ?>
                </span>
              </td>
            </tr>
            <tr class="border-bottom">
              <td class="text-body-secondary py-2">Memoria RAM:</td>
              <td class="py-2">
                <?php
                  $memTotal = $metrics["memory"]["total_mb"] ?? 1;
                  $memUsed = $metrics["memory"]["used_mb"] ?? 0;
                  $memPercent = round(($memUsed / max($memTotal, 1)) * 100);
                ?>
                <div class="d-flex justify-content-between small text-muted mb-1">
                  <span><?= $memUsed ?> MB usados</span>
                  <span><?= $memTotal ?> MB totales</span>
                </div>
                <div class="progress mb-0" style="height: 8px;">
                  <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $memPercent ?>%;" aria-valuenow="<?= $memPercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
              </td>
            </tr>
            <tr>
              <td class="text-body-secondary py-2">Espacio en Disco:</td>
              <td class="py-2">
                <div class="small text-muted mb-1">
                  <?= $metrics["disk"]["used"] ?? "0" ?> usados de <?= $metrics["disk"]["total"] ?? "0" ?> (Libre: <?= $metrics["disk"]["free"] ?? "0" ?>)
                </div>
                <?php $diskPercentNum = (int)str_replace("%", "", $metrics["disk"]["percent"] ?? "0"); ?>
                <div class="progress mb-0" style="height: 8px;">
                  <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $diskPercentNum ?>%;" aria-valuenow="<?= $diskPercentNum ?>" aria-valuemin="0" aria-valuemax="100"></div>
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
          <i class="bi bi-hdd-stack me-2 text-success"></i>Estado de Servicios
        </h5>
        <a href="/system" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
          <i class="bi bi-gear me-1"></i> Gestionar
        </a>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <!-- Nginx Proxy -->
          <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom">
            <div class="d-flex align-items-center">
              <div class="stat stat-primary me-3" style="width: 36px; height: 36px; font-size: 1rem;">
                <i class="bi bi-arrow-left-right"></i>
              </div>
              <div>
                <strong class="d-block text-body">Nginx (Proxy Reverso)</strong>
                <span class="text-muted small">Frontend, estaticos y SSL (Puerto 80 / 443)</span>
              </div>
            </div>
            <?php $nginxStatus = $services["nginx"] ?? "inactive"; ?>
            <span class="badge <?= ($nginxStatus === "active") ? "bg-success-subtle text-success border border-success-subtle" : "bg-danger-subtle text-danger border border-danger-subtle" ?> px-3 py-1">
              <?= strtoupper($nginxStatus) ?>
            </span>
          </div>

          <!-- Apache Backend -->
          <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom">
            <div class="d-flex align-items-center">
              <div class="stat stat-info me-3" style="width: 36px; height: 36px; font-size: 1rem;">
                <i class="bi bi-server"></i>
              </div>
              <div>
                <strong class="d-block text-body">Apache 2 (Backend)</strong>
                <span class="text-muted small">Manejo de .htaccess y mod_rewrite (127.0.0.1:8080)</span>
              </div>
            </div>
            <?php $apacheStatus = $services["apache"] ?? "inactive"; ?>
            <span class="badge <?= ($apacheStatus === "active") ? "bg-success-subtle text-success border border-success-subtle" : "bg-danger-subtle text-danger border border-danger-subtle" ?> px-3 py-1">
              <?= strtoupper($apacheStatus) ?>
            </span>
          </div>

          <!-- MariaDB -->
          <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom">
            <div class="d-flex align-items-center">
              <div class="stat stat-success me-3" style="width: 36px; height: 36px; font-size: 1rem;">
                <i class="bi bi-database"></i>
              </div>
              <div>
                <strong class="d-block text-body">MariaDB Server</strong>
                <span class="text-muted small">Motor de base de datos relacional</span>
              </div>
            </div>
            <?php $mariaStatus = $services["mariadb"] ?? "inactive"; ?>
            <span class="badge <?= ($mariaStatus === "active") ? "bg-success-subtle text-success border border-success-subtle" : "bg-danger-subtle text-danger border border-danger-subtle" ?> px-3 py-1">
              <?= strtoupper($mariaStatus) ?>
            </span>
          </div>

          <!-- PHP-FPM Overview -->
          <div class="list-group-item d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center">
              <div class="stat stat-warning me-3" style="width: 36px; height: 36px; font-size: 1rem;">
                <i class="bi bi-code-slash"></i>
              </div>
              <div>
                <strong class="d-block text-body">PHP-FPM Multi-Version</strong>
                <span class="text-muted small">Manejadores FastCGI (7.4 a 8.5)</span>
              </div>
            </div>
            <a href="/php" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap">
              <i class="bi bi-code-slash me-1"></i> Ver Versiones
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
