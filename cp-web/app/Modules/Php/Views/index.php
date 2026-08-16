<?php
$list = $phpVersions ?? $versions ?? [];
?>
<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
  <div>
    <h1 class="h4 mb-0">Versiones PHP-FPM Multi-Version</h1>
    <span class="text-muted small">Gestiona y configura los parametros de ejecucion (php.ini) y servicios PHP-FPM del servidor.</span>
  </div>
  <div>
    <a href="/php" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
      <i class="bi bi-arrow-clockwise me-1"></i> Refrescar
    </a>
  </div>
</div>

<div class="bg-body p-3 rounded mb-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle table-sm m-0">
      <thead>
        <tr>
          <th class="ps-3">Version</th>
          <th>Nombre del Servicio</th>
          <th>Estado Actual</th>
          <th>Dominios Asignados</th>
          <th>Socket Global</th>
          <th class="text-end pe-3 text-nowrap">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($list)): ?>
          <tr>
            <td colspan="6" class="text-center py-3 text-muted">
              No se detectaron servicios PHP-FPM instalados.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($list as $php): ?>
            <?php 
              $isInstalled = !empty($php["installed"]);
              $isActive = ($php["status"] ?? "") === "active";
              $domainCount = $usageStats[$php["version"]] ?? 0;
            ?>
            <tr>
              <td class="ps-3 fw-bold">
                <span class="d-inline-flex align-items-center">
                  <i class="bi bi-filetype-php me-2 text-primary fs-5"></i>
                  PHP <?= $php["version"] ?>
                </span>
              </td>
              <td><code><?= $php["service"] ?? ("php" . $php["version"] . "-fpm") ?></code></td>
              <td>
                <?php if ($isActive): ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                    <i class="bi bi-check-circle-fill me-1"></i>ACTIVO
                  </span>
                <?php elseif ($isInstalled): ?>
                  <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1">
                    INACTIVO
                  </span>
                <?php else: ?>
                  <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1">
                    NO INSTALADO
                  </span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">
                  <?= $domainCount ?> <?= ($domainCount == 1) ? "dominio" : "dominios" ?>
                </span>
              </td>
              <td class="font-monospace small text-muted">/run/php/php<?= $php["version"] ?>-fpm.sock</td>
              <td class="text-end pe-3 text-nowrap">
                <div class="d-flex justify-content-end gap-1">
                  <?php if ($isInstalled): ?>
                    <a href="/php/config/<?= $php["version"] ?>" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap" title="Configurar php.ini">
                      <i class="bi bi-sliders me-1"></i> Configurar
                    </a>
                    <a href="/php/restart/<?= $php["version"] ?>" class="btn btn-sm btn-outline-warning text-uppercase fw-bold text-nowrap" onclick="return confirm('Reiniciar PHP <?= $php["version"] ?>-FPM?')" title="Reiniciar Servicio">
                      <i class="bi bi-arrow-clockwise me-1"></i> Reiniciar
                    </a>
                  <?php else: ?>
                    <span class="text-muted small">No disponible</span>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
