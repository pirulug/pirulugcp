<?php
$u = $user ?? [];
$s = $settings ?? [];
$activeTab = $activeTab ?? "security";
$cfEnabled = !empty($s["cf_turnstile_enabled"]);
$is2faActive = !empty($is2faEnabled);
$secret = $tempSecret ?? "";
$otpUrl = $otpAuthUrl ?? "";
?>

<div class="bg-body p-3 rounded my-3">
  <ul class="nav nav-pills nav-justified">
    <li class="nav-item">
      <a class="nav-link <?= ($activeTab === "profile") ? "active fw-bold" : "text-body" ?>" href="/account/profile">
        <i class="bi bi-person me-1"></i>
        Información de Perfil
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($activeTab === "password") ? "active fw-bold" : "text-body" ?>" href="/account/password">
        <i class="bi bi-key me-1"></i>
        Cambiar Contraseña
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($activeTab === "security") ? "active fw-bold" : "text-body" ?>" href="/account/security">
        <i class="bi bi-shield-lock me-1"></i>
        Seguridad, 2FA & CAPTCHA
      </a>
    </li>
  </ul>
</div>

<div class="row">
  <!-- Columna Izquierda: Tarjetas Informativas -->
  <div class="col-lg-4">
    <!-- Estado General de Seguridad -->
    <div class="card mb-3">
      <div class="card-header">
        <h5 class="card-title mb-0 d-flex align-items-center">
          <i class="bi bi-shield-check text-primary me-2"></i> Estado de Seguridad
        </h5>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
          <span class="small text-muted">Autenticación 2FA:</span>
          <?php if ($is2faActive): ?>
            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace">
              <i class="bi bi-check-circle-fill me-1"></i> Activado
            </span>
          <?php else: ?>
            <span class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace">
              <i class="bi bi-exclamation-triangle-fill me-1"></i> Desactivado
            </span>
          <?php endif; ?>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
          <span class="small text-muted">Cloudflare CAPTCHA:</span>
          <?php if ($cfEnabled): ?>
            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace">
              <i class="bi bi-check-circle-fill me-1"></i> Activo en Login
            </span>
          <?php else: ?>
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace">
              <i class="bi bi-dash-circle me-1"></i> Inactivo
            </span>
          <?php endif; ?>
        </div>

        <p class="small text-muted mb-0">
          La combinación de <strong>2FA</strong> y <strong>Cloudflare Turnstile</strong> garantiza máxima protección contra ataques de fuerza bruta y accesos no autorizados.
        </p>
      </div>
    </div>

    <!-- Guia de Cloudflare Turnstile -->
    <div class="card mb-3">
      <div class="card-header">
        <h5 class="card-title mb-0 d-flex align-items-center">
          <i class="bi bi-cloud-check-fill text-warning me-2"></i> Cloudflare Turnstile
        </h5>
      </div>
      <div class="card-body">
        <div class="p-2 rounded border bg-body-tertiary mb-2">
          <strong class="d-block small text-primary mb-1">Obtén tus claves gratis:</strong>
          <ol class="small text-muted ps-3 mb-0">
            <li>Accede a <a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank" class="text-decoration-none">Cloudflare Turnstile</a>.</li>
            <li>Crea un widget para tu dominio.</li>
            <li>Copia la <strong>Site Key</strong> y <strong>Secret Key</strong>.</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Columna Derecha: Formularios de 2FA y CAPTCHA -->
  <div class="col-lg-8">
    <!-- ===================================================================== -->
    <!-- 1. AUTENTICACION EN DOS PASOS (2FA - TOTP RFC 6238)                   -->
    <!-- ===================================================================== -->
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 d-flex align-items-center">
          <i class="bi bi-phone me-2 text-primary"></i> Autenticación en Dos Pasos (2FA)
        </h5>
        <span class="badge <?= $is2faActive ? "bg-success-subtle text-success border border-success-subtle" : "bg-secondary-subtle text-secondary border border-secondary-subtle" ?> font-monospace">
          <?= $is2faActive ? "2FA Habilitado" : "2FA Deshabilitado" ?>
        </span>
      </div>
      <div class="card-body">
        <?php if ($is2faActive): ?>
          <!-- Vista cuando 2FA esta Activado -->
          <div class="alert alert-success d-flex align-items-center mb-3 py-2 small" role="alert">
            <i class="bi bi-shield-check fs-5 me-2"></i>
            <div>
              <strong>Tu cuenta está protegida con 2FA.</strong> Se te solicitará un código de 6 dígitos generado por tu aplicación cada vez que inicies sesión.
            </div>
          </div>

          <form action="/account/2fa/disable" method="POST" onsubmit="return confirm('¿Estás seguro de desactivar la autenticación en dos pasos? Tu cuenta será menos segura.')">
            <div class="p-3 rounded border bg-body-tertiary mb-3">
              <label for="disable_2fa_pass" class="form-label fw-bold">Confirmar contraseña para desactivar 2FA</label>
              <div class="row g-2 align-items-center">
                <div class="col-sm-8">
                  <input type="password" class="form-control" id="disable_2fa_pass" name="password" placeholder="Ingresa tu contraseña actual" data-pr-toggle-password required>
                </div>
                <div class="col-sm-4">
                  <button type="submit" class="btn btn-outline-danger w-100 text-uppercase fw-bold text-nowrap">
                    <i class="bi bi-shield-slash me-1"></i> Desactivar 2FA
                  </button>
                </div>
              </div>
            </div>
          </form>
        <?php else: ?>
          <!-- Vista cuando 2FA esta Desactivado (Paso a paso de activacion) -->
          <p class="small text-muted mb-3">
            Escanea el código QR con cualquier aplicación autenticadora (Google Authenticator, Authy, Microsoft Authenticator, 1Password, Bitwarden) o introduce la clave secreta manualmente.
          </p>

          <div class="row g-3 align-items-center mb-3">
            <div class="col-md-5 text-center">
              <div class="p-2 rounded border d-inline-block bg-body-tertiary">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=<?= urlencode($otpUrl) ?>" alt="Código QR 2FA" style="width: 150px; height: 150px;" class="img-fluid rounded">
              </div>
            </div>
            <div class="col-md-7">
              <label class="form-label text-muted small mb-1">Clave de Configuración Manual:</label>
              <div class="input-group mb-2">
                <input type="text" class="form-control font-monospace fw-bold text-primary" id="secretKeyText" value="<?= $secret ?>" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('secretKeyText').value); alert('Clave copiada al portapapeles')">
                  <i class="bi bi-clipboard"></i> Copiar
                </button>
              </div>
              <span class="small text-muted">Tipo: <strong>TOTP basado en tiempo (30s)</strong></span>
            </div>
          </div>

          <form action="/account/2fa/enable" method="POST">
            <input type="hidden" name="secret" value="<?= $secret ?>">
            <div class="p-3 rounded border bg-body-tertiary mb-3">
              <label for="enable_2fa_code" class="form-label fw-bold">Paso 2: Ingresa el código de 6 dígitos para verificar y activar</label>
              <div class="row g-2 align-items-center">
                <div class="col-sm-8">
                  <input type="text" 
                         class="form-control font-monospace text-center fw-bold fs-5" 
                         id="enable_2fa_code" 
                         name="code" 
                         placeholder="123456" 
                         maxlength="6" 
                         pattern="[0-9]{6}" 
                         inputmode="numeric" 
                         autocomplete="one-time-code" 
                         required>
                </div>
                <div class="col-sm-4">
                  <button type="submit" class="btn btn-primary w-100 text-uppercase fw-bold text-nowrap">
                    <i class="bi bi-shield-plus me-1"></i> Activar 2FA
                  </button>
                </div>
              </div>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <!-- ===================================================================== -->
    <!-- 2. CLOUDFLARE TURNSTILE CAPTCHA PARA LOGIN                            -->
    <!-- ===================================================================== -->
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 d-flex align-items-center">
          <i class="bi bi-shield-lock text-warning me-2"></i> Cloudflare CAPTCHA en Inicio de Sesión
        </h5>
        <span class="badge bg-body-tertiary text-body border font-monospace">Turnstile v0</span>
      </div>
      <div class="card-body">
        <form action="/account/security" method="POST">
          <!-- Switch de Activacion -->
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" id="cf_turnstile_enabled" name="cf_turnstile_enabled" value="1" <?= $cfEnabled ? "checked" : "" ?> onchange="toggleTurnstileFields(this.checked)">
            <label class="form-check-label fw-bold" for="cf_turnstile_enabled">
              Activar verificación Cloudflare Turnstile CAPTCHA en el inicio de sesión
            </label>
          </div>

          <!-- Campos de Claves -->
          <div id="turnstile_fields" class="<?= $cfEnabled ? "" : "d-none" ?>">
            <div class="mb-3">
              <label for="cf_turnstile_site_key" class="form-label">Clave del Sitio (Site Key) <span class="text-danger">*</span></label>
              <input type="text" class="form-control font-monospace" id="cf_turnstile_site_key" name="cf_turnstile_site_key" value="<?= $s["cf_turnstile_site_key"] ?? "" ?>" placeholder="0x4AAAAAAAXxxxxxxxXXXXXX">
              <div class="form-text small text-muted">Clave pública para el widget de validación en el navegador.</div>
            </div>

            <div class="mb-3">
              <label for="cf_turnstile_secret_key" class="form-label">Clave Secreta (Secret Key) <span class="text-danger">*</span></label>
              <input type="password" class="form-control font-monospace" id="cf_turnstile_secret_key" name="cf_turnstile_secret_key" value="<?= $s["cf_turnstile_secret_key"] ?? "" ?>" placeholder="0x4AAAAAAAXxxxxxxxXXXXXX-XXXXXXX" data-pr-toggle-password>
              <div class="form-text small text-muted">Clave privada para validación del token con la API de Cloudflare.</div>
            </div>
          </div>

          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-4 text-uppercase fw-bold">
              <i class="bi bi-floppy me-2"></i> Guardar Configuración de CAPTCHA
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function toggleTurnstileFields(isChecked) {
  const fields = document.getElementById("turnstile_fields");
  if (!fields) return;
  if (isChecked) {
    fields.classList.remove("d-none");
  } else {
    fields.classList.add("d-none");
  }
}
</script>
