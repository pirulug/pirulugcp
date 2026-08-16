<?php
$list = $phpVersions ?? $versions ?? [];
?>
<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
  <div>
    <h1 class="h4 mb-0">Versiones PHP-FPM Multi-Version</h1>
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
          <th>Socket Global</th>
          <th class="text-end pe-3 text-nowrap">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($list)): ?>
          <tr>
            <td colspan="5" class="text-center py-4 text-muted">
              No se detectaron servicios PHP-FPM instalados.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($list as $php): ?>
            <tr>
              <td class="ps-3 fw-bold">
                <span class="d-inline-flex align-items-center">
                  <i class="bi bi-filetype-php me-2 text-primary fs-5"></i>
                  PHP <?= htmlspecialchars($php["version"]) ?>
                </span>
              </td>
              <td><code><?= htmlspecialchars($php["service"] ?? ("php" . $php["version"] . "-fpm")) ?></code></td>
              <td>
                <?php if (($php["status"] ?? "") === "active"): ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                    <i class="bi bi-check-circle-fill me-1"></i>ACTIVO
                  </span>
                <?php else: ?>
                  <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1">
                    <?= strtoupper(htmlspecialchars($php["status"] ?? "inactivo")) ?>
                  </span>
                <?php endif; ?>
              </td>
              <td class="font-monospace small text-muted">/run/php/php<?= htmlspecialchars($php["version"]) ?>-fpm.sock</td>
              <td class="text-end pe-3 text-nowrap">
                <div class="d-flex justify-content-end gap-1">
                  <a href="/php/restart/<?= htmlspecialchars($php["version"]) ?>" class="btn btn-sm btn-outline-warning text-uppercase fw-bold text-nowrap" onclick="return confirm('Reiniciar PHP <?= htmlspecialchars($php["version"]) ?>-FPM?')" title="Reiniciar Servicio">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reiniciar
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
