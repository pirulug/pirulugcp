<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
  <div>
    <h1 class="h4 mb-0">Configurar Servidor: <span class="text-primary font-monospace">PHP <?= $version ?></span></h1>
    <span class="text-muted small">Ajuste de directivas de rendimiento, límites de memoria, subida de archivos y editor completo de <code><?= $ini["ini_file"] ?></code>.</span>
  </div>
  <div class="d-flex gap-2">
    <a href="/php" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
      <i class="bi bi-arrow-left me-1"></i> Volver a Versiones
    </a>
  </div>
</div>

<!-- Navegacion por pestañas -->
<div class="bg-body p-3 rounded my-3">
  <ul class="nav nav-pills nav-justified">
    <li class="nav-item">
      <a class="nav-link <?= ($activeTab === "basic") ? "active" : "" ?>" href="/php/config/<?= $version ?>?tab=basic">
        <i class="bi bi-sliders me-1"></i>
        Directivas Basicas de Rendimiento
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($activeTab === "advanced") ? "active" : "" ?>" href="/php/config/<?= $version ?>?tab=advanced">
        <i class="bi bi-file-earmark-code me-1"></i>
        Editor Avanzado (php.ini)
      </a>
    </li>
  </ul>
</div>

<?php if ($activeTab === "basic"): ?>
<!-- ======================================================================= -->
<!-- SECCION: DIRECTIVAS BASICAS (IMAGEN 1) -->
<!-- ======================================================================= -->
<form action="/php/config/<?= $version ?>/save" method="POST">
  <div class="bg-body p-3 rounded mb-3">
    <h6 class="fw-bold mb-3">
      <i class="bi bi-gear-fill me-1"></i> Parametros de Ejecucion de PHP <?= $version ?>
    </h6>

    <!-- max_execution_time -->
    <div class="mb-3">
      <label for="max_execution_time" class="form-label font-monospace">max_execution_time</label>
      <input type="text" class="form-control font-monospace" id="max_execution_time" name="max_execution_time" value="<?= $ini["max_execution_time"] ?>" required>
      <div class="form-text small">Tiempo maximo de ejecucion de cada script en segundos (ej. <code>30</code>, <code>60</code>, <code>120</code>, <code>300</code>).</div>
    </div>

    <!-- max_input_time -->
    <div class="mb-3">
      <label for="max_input_time" class="form-label font-monospace">max_input_time</label>
      <input type="text" class="form-control font-monospace" id="max_input_time" name="max_input_time" value="<?= $ini["max_input_time"] ?>" required>
      <div class="form-text small">Tiempo maximo que un script puede consumir analizando datos de entrada como POST o GET en segundos.</div>
    </div>

    <!-- memory_limit -->
    <div class="mb-3">
      <label for="memory_limit" class="form-label font-monospace">memory_limit</label>
      <input type="text" class="form-control font-monospace" id="memory_limit" name="memory_limit" value="<?= $ini["memory_limit"] ?>" required>
      <div class="form-text small">Cantidad maxima de memoria que un script puede asignar (ej. <code>128M</code>, <code>256M</code>, <code>512M</code>, <code>1024M</code>).</div>
    </div>

    <!-- error_reporting -->
    <div class="mb-3">
      <label for="error_reporting" class="form-label font-monospace">error_reporting</label>
      <input type="text" class="form-control font-monospace" id="error_reporting" name="error_reporting" value="<?= $ini["error_reporting"] ?>" required>
      <div class="form-text small">Nivel de reporte de errores (ej. <code>E_ALL & ~E_DEPRECATED</code>, <code>E_ALL & ~E_NOTICE</code>).</div>
    </div>

    <!-- display_errors -->
    <div class="mb-3">
      <label for="display_errors" class="form-label font-monospace">display_errors</label>
      <select class="form-select font-monospace" id="display_errors" name="display_errors" required>
        <option value="Off" <?= (strtolower($ini["display_errors"]) === "off" || $ini["display_errors"] === "0") ? "selected" : "" ?>>Off (Recomendado en Produccion)</option>
        <option value="On" <?= (strtolower($ini["display_errors"]) === "on" || $ini["display_errors"] === "1") ? "selected" : "" ?>>On (Modo Depuracion / Desarrollo)</option>
      </select>
      <div class="form-text small">Determina si los errores se imprimen en pantalla o solo se guardan en el archivo de log.</div>
    </div>

    <!-- post_max_size -->
    <div class="mb-3">
      <label for="post_max_size" class="form-label font-monospace">post_max_size</label>
      <input type="text" class="form-control font-monospace" id="post_max_size" name="post_max_size" value="<?= $ini["post_max_size"] ?>" required>
      <div class="form-text small">Tamaño maximo de datos permitidos en peticiones POST (ej. <code>64M</code>, <code>128M</code>, <code>200M</code>, <code>500M</code>).</div>
    </div>

    <!-- upload_max_filesize -->
    <div class="mb-3">
      <label for="upload_max_filesize" class="form-label font-monospace">upload_max_filesize</label>
      <input type="text" class="form-control font-monospace" id="upload_max_filesize" name="upload_max_filesize" value="<?= $ini["upload_max_filesize"] ?>" required>
      <div class="form-text small">Tamaño maximo permitido para la subida de un archivo individual (debe ser menor o igual a <code>post_max_size</code>).</div>
    </div>
  </div>

  <!-- Botonera Guardar / Cancelar -->
  <div class="bg-body p-3 rounded d-flex justify-content-end gap-2 sticky-bottom">
    <a href="/php" class="btn btn-outline-secondary px-4 text-uppercase fw-bold">
      <i class="bi bi-arrow-left me-2"></i> Cancelar
    </a>
    <button type="submit" class="btn btn-primary px-5 text-uppercase fw-bold">
      <i class="bi bi-check2-circle me-2"></i> Guardar Cambios
    </button>
  </div>
</form>

<?php elseif ($activeTab === "advanced"): ?>
<!-- ======================================================================= -->
<!-- SECCION: EDITOR AVANZADO PHP.INI (IMAGEN 2) -->
<!-- ======================================================================= -->
<form action="/php/config/<?= $version ?>/raw" method="POST">
  <div class="bg-body p-3 rounded mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h6 class="fw-bold mb-0">
          <i class="bi bi-file-earmark-code me-1"></i> Archivo de Configuracion: <code><?= $ini["ini_file"] ?></code>
        </h6>
        <span class="text-muted small">Edita directamente las directivas completas del archivo php.ini para PHP <?= $version ?>-FPM.</span>
      </div>
      <div>
        <span class="badge bg-secondary-subtle text-secondary border font-monospace">PHP <?= $version ?></span>
      </div>
    </div>

    <div class="mb-0">
      <textarea class="form-control font-monospace text-light bg-dark p-3" id="raw_ini_content" name="raw_ini_content" rows="22" style="resize: vertical; font-size: 0.88rem; line-height: 1.5; tab-size: 4;" spellcheck="false"><?= $rawIni ?></textarea>
    </div>
  </div>

  <!-- Botonera Guardar / Cancelar -->
  <div class="bg-body p-3 rounded d-flex justify-content-end gap-2 sticky-bottom">
    <a href="/php" class="btn btn-outline-secondary px-4 text-uppercase fw-bold">
      <i class="bi bi-arrow-left me-2"></i> Cancelar
    </a>
    <button type="submit" class="btn btn-primary px-5 text-uppercase fw-bold">
      <i class="bi bi-check2-circle me-2"></i> Guardar Archivo php.ini
    </button>
  </div>
</form>
<?php endif; ?>
