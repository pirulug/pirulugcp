<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1 class="h4 mb-0">Visor de Logs del Servidor</h1>
  </div>
  <div>
    <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap me-2" onclick="location.reload()">
      <i class="bi bi-arrow-clockwise me-1"></i> Refrescar
    </button>
    <form action="/logs/clear" method="POST" class="d-inline m-0" onsubmit="return confirm('Estas seguro de vaciar este archivo de log?')">
      <input type="hidden" name="log" value="<?= $selectedLog ?>">
      <button type="submit" class="btn btn-sm btn-outline-danger text-uppercase fw-bold text-nowrap">
        <i class="bi bi-trash me-1"></i> Vaciar Log
      </button>
    </form>
  </div>
</div>

<div class="bg-body p-3 rounded mb-3">
  <form method="GET" action="/logs" class="row g-3 align-items-center mb-3">
    <div class="col-md-5">
      <label for="logSelect" class="form-label">Servicio / Archivo de Log</label>
      <select name="log" id="logSelect" class="form-select form-select-sm" onchange="this.form.submit()">
        <?php
        $groups = [];
        foreach ($availableLogs as $key => $info) {
          $groups[$info["group"]][$key] = $info["name"];
        }
        ?>
        <?php foreach ($groups as $groupName => $items): ?>
          <optgroup label="<?= $groupName ?>">
            <?php foreach ($items as $k => $name): ?>
              <option value="<?= $k ?>" <?= ($selectedLog === $k) ? "selected" : "" ?>>
                <?= $name ?>
              </option>
            <?php endforeach; ?>
          </optgroup>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <label for="linesSelect" class="form-label">Numero de Lineas</label>
      <select name="lines" id="linesSelect" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="50" <?= ($lines == 50) ? "selected" : "" ?>>Ultimas 50 lineas</option>
        <option value="100" <?= ($lines == 100) ? "selected" : "" ?>>Ultimas 100 lineas</option>
        <option value="200" <?= ($lines == 200) ? "selected" : "" ?>>Ultimas 200 lineas</option>
        <option value="500" <?= ($lines == 500) ? "selected" : "" ?>>Ultimas 500 lineas</option>
        <option value="1000" <?= ($lines == 1000) ? "selected" : "" ?>>Ultimas 1000 lineas</option>
      </select>
    </div>

    <div class="col-md-4">
      <label for="logFilter" class="form-label">Filtrar Texto en Pantalla</label>
      <input type="text" id="logFilter" class="form-control form-control-sm" placeholder="Buscar (error, 404, FastCGI)..." onkeyup="filterLogs()">
    </div>
  </form>

  <div class="p-3 rounded bg-body-tertiary border" style="min-height: 480px; max-height: 70vh; overflow-y: auto; font-family: SFMono-Regular, Consolas, 'Liberation Mono', Menlo, monospace; font-size: 0.85rem; line-height: 1.5;" id="logOutputContainer">
    <pre id="logOutput" class="mb-0 text-body" style="white-space: pre-wrap; word-break: break-all;"><?= $logContent ?></pre>
  </div>
  <div class="d-flex justify-content-between align-items-center small text-muted pt-2">
    <span>Archivo activo: <strong><?= $availableLogs[$selectedLog]["name"] ?? $selectedLog ?></strong></span>
    <span id="lineCountDisplay">Mostrando las ultimas <?= (int)$lines ?> lineas</span>
  </div>
</div>

<script>
function filterLogs() {
  const query = document.getElementById("logFilter").value.toLowerCase();
  const logOutput = document.getElementById("logOutput");
  const originalText = logOutput.dataset.original || logOutput.textContent;
  
  if (!logOutput.dataset.original) {
    logOutput.dataset.original = originalText;
  }

  if (!query) {
    logOutput.textContent = originalText;
    return;
  }

  const lines = originalText.split("\n");
  const filtered = lines.filter(function (line) {
    return line.toLowerCase().indexOf(query) !== -1;
  });

  logOutput.textContent = filtered.length > 0 ? filtered.join("\n") : "No se encontraron coincidencias para: " + query;
}

document.addEventListener("DOMContentLoaded", function () {
  const container = document.getElementById("logOutputContainer");
  if (container) {
    container.scrollTop = container.scrollHeight;
  }
});
</script>
