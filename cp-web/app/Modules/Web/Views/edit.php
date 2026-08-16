<?php
$d = $domain ?? [];
$domainName = $d["domain"] ?? "dominio.test";
$aliases = $d["aliases"] ?? "";
$redirectEnabled = !empty($d["redirect_enabled"]);
$redirectType = $d["redirect_type"] ?? "custom";
$redirectTarget = $d["redirect_target"] ?? "";
$redirectCode = (int)($d["redirect_code"] ?? 301);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h4 mb-0 fw-bold">Editar Dominio Web</h1>
    <span class="text-muted small">Configuración de alias y redirecciones para <strong><?= $domainName ?></strong></span>
  </div>
  <a href="/web" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold">
    <i class="bi bi-arrow-left me-1"></i> Volver a Dominios
  </a>
</div>

<form action="/web/domain/<?= (int)$d["id"] ?>/edit" method="POST">
  <div class="card mb-3">
    <div class="card-body">
      <!-- 1. Nombre del Dominio (Solo Lectura) -->
      <div class="mb-3">
        <label for="domain_name_field" class="form-label fw-bold">Dominio</label>
        <input type="text" class="form-control font-monospace" id="domain_name_field" value="<?= $domainName ?>" readonly disabled>
      </div>

      <!-- 2. Alias del Dominio -->
      <div class="mb-3">
        <label for="domain_aliases" class="form-label fw-bold">Alias</label>
        <textarea class="form-control font-monospace small" id="domain_aliases" name="aliases" rows="3" placeholder="www.<?= $domainName ?>"><?= $aliases ?></textarea>
      </div>

      <!-- Alerta Informativa sobre SSL de Alias -->
      <div class="alert alert-primary d-flex align-items-center py-2 px-3 mb-3 small" role="alert">
        <i class="bi bi-exclamation-circle-fill fs-5 me-2 flex-shrink-0"></i>
        <div>Si los alias cambian, se obtendrá un nuevo certificado SSL de Let's Encrypt.</div>
      </div>

      <!-- 3. Seccion de Redireccionamiento de Dominio -->
      <div class="pt-3 border-top">
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="redirect_enabled" id="redirect_enabled" value="1" <?= $redirectEnabled ? "checked" : "" ?> onchange="toggleRedirectOptions(this.checked)">
          <label class="form-check-label fw-bold" for="redirect_enabled">
            Activar redirección de dominio
          </label>
        </div>

        <div id="redirect_options_box" class="ms-3 ps-2 border-start <?= $redirectEnabled ? "" : "d-none" ?>">
          <!-- Opcion 1: Redirigir a www -->
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio" name="redirect_type" id="redirect_to_www" value="to_www" <?= ($redirectType === "to_www") ? "checked" : "" ?> onchange="handleRedirectTypeChange(this.value)">
            <label class="form-check-label" for="redirect_to_www">
              Redirigir a los visitantes a <strong>www.<?= $domainName ?></strong>
            </label>
          </div>

          <!-- Opcion 2: Redirigir a no-www (raiz) -->
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio" name="redirect_type" id="redirect_to_non_www" value="to_non_www" <?= ($redirectType === "to_non_www") ? "checked" : "" ?> onchange="handleRedirectTypeChange(this.value)">
            <label class="form-check-label" for="redirect_to_non_www">
              Redirigir a los visitantes a <strong><?= $domainName ?></strong>
            </label>
          </div>

          <!-- Opcion 3: Redirigir a dominio personalizado / URL -->
          <div class="form-check mb-3">
            <input class="form-check-input" type="radio" name="redirect_type" id="redirect_to_custom" value="custom" <?= ($redirectType === "custom") ? "checked" : "" ?> onchange="handleRedirectTypeChange(this.value)">
            <label class="form-check-label" for="redirect_to_custom">
              Redirigir a los visitantes a un dominio personalizado o dirección web
            </label>
          </div>

          <!-- Campo URL de Destino (Solo visible si es custom) -->
          <div id="custom_url_container" class="mb-3 ms-4 <?= ($redirectType === "custom") ? "" : "d-none" ?>">
            <label for="redirect_target" class="form-label fw-bold">Dominio destino o URL</label>
            <input type="text" class="form-control font-monospace" id="redirect_target" name="redirect_target" value="<?= $redirectTarget ?>" placeholder="https://ejemplo.com">
            <div class="form-text small text-muted">Ingresa la dirección URL completa a la que se reenviará el tráfico.</div>
          </div>

          <!-- Selector de Codigo de Estado HTTP -->
          <div class="mb-3 ms-4">
            <label for="redirect_code" class="form-label fw-bold">Código de estado:</label>
            <select class="form-select font-monospace" id="redirect_code" name="redirect_code" style="max-width: 320px;">
              <option value="301" <?= ($redirectCode === 301) ? "selected" : "" ?>>301 - Permanente (Moved Permanently)</option>
              <option value="302" <?= ($redirectCode === 302) ? "selected" : "" ?>>302 - Temporal (Found)</option>
              <option value="307" <?= ($redirectCode === 307) ? "selected" : "" ?>>307 - Temporal Estricto (Temporary Redirect)</option>
              <option value="308" <?= ($redirectCode === 308) ? "selected" : "" ?>>308 - Permanente Estricto (Permanent Redirect)</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Botonera de Acciones (Fuera del Card) -->
  <div class="bg-body p-3 rounded d-flex justify-content-end gap-2 sticky-bottom mt-3">
    <a href="/web" class="btn btn-outline-secondary px-4 text-uppercase fw-bold">
      <i class="bi bi-arrow-left me-2"></i> Cancelar
    </a>
    <button type="submit" class="btn btn-primary px-5 text-uppercase fw-bold">
      <i class="bi bi-floppy me-2"></i> Guardar Cambios
    </button>
  </div>
</form>

<script>
function toggleRedirectOptions(isChecked) {
  const box = document.getElementById("redirect_options_box");
  if (!box) return;
  if (isChecked) {
    box.classList.remove("d-none");
  } else {
    box.classList.add("d-none");
  }
}

function handleRedirectTypeChange(type) {
  const customContainer = document.getElementById("custom_url_container");
  if (!customContainer) return;
  if (type === "custom") {
    customContainer.classList.remove("d-none");
  } else {
    customContainer.classList.add("d-none");
  }
}
</script>
