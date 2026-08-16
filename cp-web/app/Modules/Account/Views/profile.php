<?php
$u = $user ?? [];
$activeTab = $activeTab ?? "profile";
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

<form action="/account/profile" method="POST">
  <div class="row">
    <div class="col-md-4">
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0">Detalles de Usuario</h5>
        </div>
        <div class="card-body text-center">
          <div class="avatar bg-primary-subtle text-primary border border-primary-subtle d-inline-flex align-items-center justify-content-center rounded-circle fw-bold mb-3" style="width: 72px; height: 72px; font-size: 1.8rem;">
            <?= strtoupper(substr($u["username"] ?? "A", 0, 1)) ?>
          </div>
          <h5 class="fw-bold mb-1"><?= $u["name"] ?? "Administrador" ?></h5>
          <p class="text-muted small mb-3"><?= $u["email"] ?? "" ?></p>

          <div class="text-start border-top pt-3 font-monospace small">
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted">Usuario:</span>
              <strong class="text-body"><?= $u["username"] ?? "admin" ?></strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted">Rol:</span>
              <span class="badge bg-body-tertiary text-body border"><?= $u["role"] ?? "admin" ?></span>
            </div>
            <?php if (!empty($u["created_at"])): ?>
              <div class="d-flex justify-content-between">
                <span class="text-muted">Miembro desde:</span>
                <span class="text-body"><?= date("d/m/Y", strtotime($u["created_at"])) ?></span>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0">Editar Información Personal</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="user_login_name" class="form-label">Nombre de Usuario</label>
            <input type="text" class="form-control font-monospace" id="user_login_name" value="<?= $u["username"] ?? "admin" ?>" readonly disabled>
            <div class="form-text small text-muted">El identificador de usuario en el sistema Linux es inmutable.</div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="user_name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="user_name" name="name" value="<?= $u["name"] ?? "Administrador" ?>" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="user_email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="user_email" name="email" value="<?= $u["email"] ?? "admin@pirulugcp.local" ?>" required>
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
          <i class="bi bi-floppy me-2"></i> Guardar Cambios
        </button>
      </div>
    </div>
  </div>
</form>
