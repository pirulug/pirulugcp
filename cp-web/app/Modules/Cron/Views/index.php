<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <div class="d-flex align-items-center gap-2">
      <h1 class="h4 mb-0">Programador de Tareas Cron</h1>
      <?php if (!empty($cronActive)): ?>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 font-monospace">
          <i class="bi bi-check-circle-fill me-1"></i>DAEMON ACTIVO
        </span>
      <?php else: ?>
        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 font-monospace">
          <i class="bi bi-exclamation-triangle-fill me-1"></i>DAEMON INACTIVO
        </span>
      <?php endif; ?>
    </div>
    <span class="text-muted small">Automatiza la ejecución periódica de comandos del sistema, scripts PHP, tareas de Laravel y llamadas a URLs.</span>
  </div>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-primary text-uppercase fw-bold" data-bs-toggle="modal" data-bs-target="#createCronModal">
      <i class="bi bi-plus-lg me-1"></i> Nueva Tarea Cron
    </button>
  </div>
</div>

<!-- Tabla de Tareas Cron -->
<div class="bg-body p-3 rounded mb-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle table-sm m-0">
      <thead>
        <tr>
          <th class="ps-3">Expresion Cron</th>
          <th>Descripcion</th>
          <th>Comando Programado</th>
          <th>Dominio</th>
          <th>Estado</th>
          <th class="text-end pe-3 text-nowrap">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($cronJobs)): ?>
          <tr>
            <td colspan="6" class="text-center py-4 text-muted">
              No hay tareas cron programadas en el sistema. Puedes <a href="#" data-bs-toggle="modal" data-bs-target="#createCronModal" class="text-primary fw-bold text-uppercase">crear una nueva tarea</a>.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($cronJobs as $job): ?>
            <?php
              $cronExpr = $job["minute"] . " " . $job["hour"] . " " . $job["day"] . " " . $job["month"] . " " . $job["weekday"];
              $isActive = ($job["status"] === "active");
            ?>
            <tr>
              <td class="ps-3 text-nowrap">
                <span class="badge bg-body-tertiary text-body border font-monospace px-2 py-1 fs-6">
                  <?= $cronExpr ?>
                </span>
              </td>
              <td>
                <span class="fw-semibold text-body">
                  <?= !empty($job["description"]) ? $job["description"] : "Sin descripción" ?>
                </span>
              </td>
              <td style="max-width: 320px;" class="text-truncate">
                <code class="user-select-all small" title="<?= $job["command"] ?>"><?= $job["command"] ?></code>
              </td>
              <td>
                <?php if (!empty($job["domain"])): ?>
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                    <i class="bi bi-globe me-1"></i><?= $job["domain"] ?>
                  </span>
                <?php else: ?>
                  <span class="badge bg-secondary-subtle text-secondary border">Global</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($isActive): ?>
                  <a href="/cron/toggle/<?= $job["id"] ?>" class="badge bg-success-subtle text-success border border-success-subtle text-decoration-none px-2 py-1" title="Clic para pausar">
                    <i class="bi bi-play-circle-fill me-1"></i>ACTIVO
                  </a>
                <?php else: ?>
                  <a href="/cron/toggle/<?= $job["id"] ?>" class="badge bg-warning-subtle text-warning border border-warning-subtle text-decoration-none px-2 py-1" title="Clic para activar">
                    <i class="bi bi-pause-circle-fill me-1"></i>PAUSADO
                  </a>
                <?php endif; ?>
              </td>
              <td class="text-end pe-3 text-nowrap">
                <div class="d-flex justify-content-end gap-1">
                  <!-- Boton Ejecutar Ahora -->
                  <button type="button" class="btn btn-sm btn-outline-info text-uppercase fw-bold text-nowrap" onclick="runCronNow(<?= $job["id"] ?>, '<?= $job["description"] ?>')" title="Probar y Ejecutar Comando Ahora">
                    <i class="bi bi-play-fill me-1"></i> Ejecutar
                  </button>

                  <!-- Boton Editar -->
                  <button type="button" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap" onclick='openEditModal(<?= json_encode($job) ?>)' title="Editar Tarea">
                    <i class="bi bi-pencil me-1"></i> Editar
                  </button>

                  <!-- Boton Eliminar -->
                  <button type="button" class="btn btn-sm btn-outline-danger text-uppercase fw-bold text-nowrap" onclick="openDeleteModal(<?= $job["id"] ?>, '<?= $job["description"] ?>')" title="Eliminar Tarea">
                    <i class="bi bi-trash me-1"></i> Eliminar
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Nueva Tarea Cron -->
<div class="modal fade" id="createCronModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-clock-history text-primary me-2"></i>Nueva Tarea Programada (Cron)
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="/cron/store" method="POST">
        <div class="modal-body">
          <!-- Presets Rapidos -->
          <div class="mb-3">
            <label class="form-label">Plantillas Frecuentes (Presets Rapidos)</label>
            <select class="form-select" id="createPresetSelect" onchange="applyPreset('create', this.value)">
              <option value="">-- Seleccionar plantilla predefinida --</option>
              <option value="* * * * *">Cada minuto (* * * * *)</option>
              <option value="*/5 * * * *">Cada 5 minutos (*/5 * * * *)</option>
              <option value="*/15 * * * *">Cada 15 minutos (*/15 * * * *)</option>
              <option value="*/30 * * * *">Cada 30 minutos (*/30 * * * *)</option>
              <option value="0 * * * *">Cada hora en punto (0 * * * *)</option>
              <option value="0 0 * * *">Todos los días a medianoche (0 0 * * *)</option>
              <option value="0 3 * * *">Todos los días a las 03:00 AM (0 3 * * *)</option>
              <option value="0 0 * * 0">Todos los domingos a medianoche (0 0 * * 0)</option>
              <option value="0 0 1 * *">El primer día de cada mes (0 0 1 * *)</option>
            </select>
          </div>

          <!-- Campos Cron Detallados -->
          <div class="row g-2 mb-3">
            <div class="col">
              <label for="create_minute" class="form-label font-monospace small">Minuto</label>
              <input type="text" class="form-control font-monospace text-center" id="create_minute" name="minute" value="*" required>
              <div class="form-text small text-center">0-59 o *</div>
            </div>
            <div class="col">
              <label for="create_hour" class="form-label font-monospace small">Hora</label>
              <input type="text" class="form-control font-monospace text-center" id="create_hour" name="hour" value="*" required>
              <div class="form-text small text-center">0-23 o *</div>
            </div>
            <div class="col">
              <label for="create_day" class="form-label font-monospace small">Dia</label>
              <input type="text" class="form-control font-monospace text-center" id="create_day" name="day" value="*" required>
              <div class="form-text small text-center">1-31 o *</div>
            </div>
            <div class="col">
              <label for="create_month" class="form-label font-monospace small">Mes</label>
              <input type="text" class="form-control font-monospace text-center" id="create_month" name="month" value="*" required>
              <div class="form-text small text-center">1-12 o *</div>
            </div>
            <div class="col">
              <label for="create_weekday" class="form-label font-monospace small">Dia Semana</label>
              <input type="text" class="form-control font-monospace text-center" id="create_weekday" name="weekday" value="*" required>
              <div class="form-text small text-center">0-7 o *</div>
            </div>
          </div>

          <!-- Descripcion -->
          <div class="mb-3">
            <label for="create_description" class="form-label">Descripcion o Nombre de la Tarea</label>
            <input type="text" class="form-control" id="create_description" name="description" placeholder="ej. Laravel Scheduler o Backup diario">
          </div>

          <!-- Dominio Asociado (Opcional) -->
          <div class="mb-3">
            <label for="create_domain_id" class="form-label">Dominio Asociado (Opcional)</label>
            <select class="form-select" id="create_domain_id" name="domain_id">
              <option value="">-- Sin dominio especifico (Global) --</option>
              <?php foreach ($domains as $dom): ?>
                <option value="<?= $dom["id"] ?>"><?= $dom["domain"] ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Comando -->
          <div class="mb-3">
            <label for="create_command" class="form-label">Comando a Ejecutar <span class="text-danger">*</span></label>
            <input type="text" class="form-control font-monospace" id="create_command" name="command" placeholder="ej. php /home/admin/web/dominio.com/public_html/artisan schedule:run" required>
            <div class="form-text small mt-1">
              Ejemplos: <code>php /ruta/al/script.php</code> o <code>curl -s https://dominio.com/cron.php</code>
            </div>
          </div>

          <!-- Manejo de Salida -->
          <div class="mb-3">
            <label class="form-label">Redireccion de Salida</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="output_mode" id="output_mute" value="mute" checked>
              <label class="form-check-label" for="output_mute">
                Silenciar salida por defecto (<code>&gt; /dev/null 2&gt;&amp;1</code>)
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="output_mode" id="output_custom" value="custom">
              <label class="form-check-label" for="output_custom">
                Personalizado (tal como se ingrese en el comando)
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary text-uppercase fw-bold">
            <i class="bi bi-check2-circle me-1"></i> Guardar Tarea Cron
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Editar Tarea Cron -->
<div class="modal fade" id="editCronModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-pencil-square text-primary me-2"></i>Editar Tarea Programada
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="editCronForm" action="" method="POST">
        <div class="modal-body">
          <!-- Presets Rapidos -->
          <div class="mb-3">
            <label class="form-label">Plantillas Frecuentes</label>
            <select class="form-select" id="editPresetSelect" onchange="applyPreset('edit', this.value)">
              <option value="">-- Cambiar por plantilla predefinida --</option>
              <option value="* * * * *">Cada minuto (* * * * *)</option>
              <option value="*/5 * * * *">Cada 5 minutos (*/5 * * * *)</option>
              <option value="*/15 * * * *">Cada 15 minutos (*/15 * * * *)</option>
              <option value="*/30 * * * *">Cada 30 minutos (*/30 * * * *)</option>
              <option value="0 * * * *">Cada hora en punto (0 * * * *)</option>
              <option value="0 0 * * *">Todos los días a medianoche (0 0 * * *)</option>
              <option value="0 3 * * *">Todos los días a las 03:00 AM (0 3 * * *)</option>
              <option value="0 0 * * 0">Todos los domingos a medianoche (0 0 * * 0)</option>
              <option value="0 0 1 * *">El primer día de cada mes (0 0 1 * *)</option>
            </select>
          </div>

          <!-- Campos Cron Detallados -->
          <div class="row g-2 mb-3">
            <div class="col">
              <label for="edit_minute" class="form-label font-monospace small">Minuto</label>
              <input type="text" class="form-control font-monospace text-center" id="edit_minute" name="minute" required>
            </div>
            <div class="col">
              <label for="edit_hour" class="form-label font-monospace small">Hora</label>
              <input type="text" class="form-control font-monospace text-center" id="edit_hour" name="hour" required>
            </div>
            <div class="col">
              <label for="edit_day" class="form-label font-monospace small">Dia</label>
              <input type="text" class="form-control font-monospace text-center" id="edit_day" name="day" required>
            </div>
            <div class="col">
              <label for="edit_month" class="form-label font-monospace small">Mes</label>
              <input type="text" class="form-control font-monospace text-center" id="edit_month" name="month" required>
            </div>
            <div class="col">
              <label for="edit_weekday" class="form-label font-monospace small">Dia Semana</label>
              <input type="text" class="form-control font-monospace text-center" id="edit_weekday" name="weekday" required>
            </div>
          </div>

          <!-- Descripcion -->
          <div class="mb-3">
            <label for="edit_description" class="form-label">Descripcion</label>
            <input type="text" class="form-control" id="edit_description" name="description">
          </div>

          <!-- Dominio Asociado -->
          <div class="mb-3">
            <label for="edit_domain_id" class="form-label">Dominio Asociado</label>
            <select class="form-select" id="edit_domain_id" name="domain_id">
              <option value="">-- Sin dominio especifico (Global) --</option>
              <?php foreach ($domains as $dom): ?>
                <option value="<?= $dom["id"] ?>"><?= $dom["domain"] ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Comando -->
          <div class="mb-3">
            <label for="edit_command" class="form-label">Comando a Ejecutar <span class="text-danger">*</span></label>
            <input type="text" class="form-control font-monospace" id="edit_command" name="command" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary text-uppercase fw-bold">
            <i class="bi bi-check2-circle me-1"></i> Guardar Cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Salida de Ejecucion en Vivo -->
<div class="modal fade" id="runOutputModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title font-monospace" id="runOutputTitle">
          <i class="bi bi-terminal text-info me-2"></i>Ejecutando Tarea Cron
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <div class="p-3 bg-dark text-light font-monospace" style="min-height: 250px; max-height: 60vh; overflow: auto;">
          <div id="runOutputStatus" class="mb-2 text-info">
            <span class="spinner-border spinner-border-sm me-2"></span>Ejecutando comando en el servidor...
          </div>
          <pre id="runOutputText" class="text-light m-0 font-monospace" style="white-space: pre-wrap; word-break: break-all; font-size: 0.88rem;"></pre>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <span id="runExecutionTime" class="text-muted small"></span>
        <button type="button" class="btn btn-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Eliminar Tarea Cron -->
<div class="modal fade" id="deleteCronModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Eliminar Tarea Cron</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">¿Estás seguro de eliminar la tarea <strong id="deleteCronDisplay" class="text-danger"></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
        <a id="deleteCronConfirmBtn" href="#" class="btn btn-danger btn-sm text-uppercase fw-bold">
          <i class="bi bi-trash me-1"></i> Eliminar
        </a>
      </div>
    </div>
  </div>
</div>

<script>
function applyPreset(prefix, expr) {
  if (!expr) return;
  const parts = expr.split(" ");
  if (parts.length === 5) {
    document.getElementById(prefix + "_minute").value = parts[0];
    document.getElementById(prefix + "_hour").value = parts[1];
    document.getElementById(prefix + "_day").value = parts[2];
    document.getElementById(prefix + "_month").value = parts[3];
    document.getElementById(prefix + "_weekday").value = parts[4];
  }
}

function openEditModal(job) {
  document.getElementById("editCronForm").action = "/cron/update/" + job.id;
  document.getElementById("edit_minute").value = job.minute;
  document.getElementById("edit_hour").value = job.hour;
  document.getElementById("edit_day").value = job.day;
  document.getElementById("edit_month").value = job.month;
  document.getElementById("edit_weekday").value = job.weekday;
  document.getElementById("edit_description").value = job.description ? job.description : "";
  document.getElementById("edit_domain_id").value = job.domain_id ? job.domain_id : "";
  document.getElementById("edit_command").value = job.command;

  const modal = new bootstrap.Modal(document.getElementById("editCronModal"));
  modal.show();
}

function openDeleteModal(id, description) {
  document.getElementById("deleteCronDisplay").textContent = description ? description : ("#" + id);
  document.getElementById("deleteCronConfirmBtn").href = "/cron/delete/" + id;
  const modal = new bootstrap.Modal(document.getElementById("deleteCronModal"));
  modal.show();
}

function runCronNow(id, description) {
  document.getElementById("runOutputTitle").textContent = "Ejecutando: " + (description ? description : ("Tarea #" + id));
  document.getElementById("runOutputStatus").innerHTML = "<span class='spinner-border spinner-border-sm me-2'></span>Ejecutando comando en el servidor...";
  document.getElementById("runOutputText").textContent = "";
  document.getElementById("runExecutionTime").textContent = "";

  const modal = new bootstrap.Modal(document.getElementById("runOutputModal"));
  modal.show();

  fetch("/cron/run/" + id, { method: "POST" })
    .then(res => res.json())
    .then(data => {
      if (data.status === "success") {
        const exitBadge = data.exit_code === 0 
          ? "<span class='badge bg-success me-2'>EXIT CODE: 0 (EXITOSO)</span>" 
          : "<span class='badge bg-danger me-2'>EXIT CODE: " + data.exit_code + " (ERROR)</span>";
        document.getElementById("runOutputStatus").innerHTML = exitBadge;
        document.getElementById("runOutputText").textContent = data.output ? data.output : "(Comando ejecutado sin salida estándar)";
        document.getElementById("runExecutionTime").textContent = "Tiempo de ejecución: " + (data.duration_sec ? data.duration_sec : 0) + "s";
      } else {
        document.getElementById("runOutputStatus").innerHTML = "<span class='badge bg-danger'>ERROR</span>";
        document.getElementById("runOutputText").textContent = data.message ? data.message : "Error al ejecutar tarea";
      }
    })
    .catch(err => {
      document.getElementById("runOutputStatus").innerHTML = "<span class='badge bg-danger'>ERROR DE CONEXIÓN</span>";
      document.getElementById("runOutputText").textContent = "No se pudo conectar con el servidor.";
    });
}
</script>
