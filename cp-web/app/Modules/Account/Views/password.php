<?php
$u = $user ?? [];
$activeTab = $activeTab ?? "password";
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
        Cloudflare CAPTCHA & Seguridad
      </a>
    </li>
  </ul>
</div>

<form action="/account/password" method="POST">
  <div class="row">
    <div class="col-md-4">
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0">Recomendaciones de Seguridad</h5>
        </div>
        <div class="card-body">
          <div class="d-flex align-items-start gap-2 mb-3">
            <i class="bi bi-shield-check text-success fs-5"></i>
            <div>
              <strong class="d-block small">Longitud Mínima</strong>
              <span class="text-muted small">Usa al menos 8 caracteres combinando letras, números y símbolos.</span>
            </div>
          </div>

          <div class="d-flex align-items-start gap-2 mb-3">
            <i class="bi bi-cpu text-primary fs-5"></i>
            <div>
              <strong class="d-block small">Sincronización Linux</strong>
              <span class="text-muted small">Al cambiar la contraseña aquí, se sincronizará automáticamente con el usuario del sistema SSH/FTP.</span>
            </div>
          </div>

          <div class="d-flex align-items-start gap-2">
            <i class="bi bi-exclamation-octagon text-warning fs-5"></i>
            <div>
              <strong class="d-block small">No la compartas</strong>
              <span class="text-muted small">Evita reutilizar contraseñas de otros servicios o paneles.</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0">Actualizar Contraseña de Acceso</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="current_password" class="form-label">Contraseña Actual <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Ingresa tu contraseña actual" data-pr-toggle-password required>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="new_password" class="form-label">Nueva Contraseña <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Mínimo 6 caracteres" data-pr-toggle-password required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Repite la nueva contraseña" data-pr-toggle-password required>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-body p-3 rounded d-flex justify-content-end gap-2 sticky-bottom">
        <a href="/dashboard" class="btn btn-outline-secondary px-4 text-uppercase fw-bold">
          <i class="bi bi-arrow-left me-2"></i> Cancelar
        </a>
        <button type="submit" class="btn btn-primary px-5 text-uppercase fw-bold">
          <i class="bi bi-key me-2"></i> Actualizar Contraseña
        </button>
      </div>
    </div>
  </div>
</form>
