<?php
use Pirulu\Core\Auth;
$currentUser = Auth::user();
?>
<nav class="navtop">
  <a class="sidebar-toggle js-sidebar-toggle" title="Alternar Barra Lateral">
    <i class="hamburger align-self-center"></i>
  </a>

  <div class="d-none d-sm-inline-block ms-3">
    <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-2 px-3 fw-semibold">
      Nginx (Proxy) + Apache 2 + MariaDB + PHP-FPM
    </span>
  </div>

  <div class="navbar-collapse collapse">
    <ul class="navbar-nav navbar-align align-items-center">
      <!-- Boton de Pantalla Completa -->
      <li class="nav-item">
        <a class="nav-icon" id="fullscreen-btn" href="#" title="Pantalla Completa">
          <i class="bi bi-fullscreen"></i>
        </a>
      </li>

      <!-- Selector de Modo Oscuro / Claro / Auto -->
      <li class="nav-item dropdown">
        <a class="nav-icon dropdown-toggle" id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown" aria-label="Cambiar Tema" title="Cambiar Tema">
          <span class="theme-icon-active" id="bd-theme-icon"></span>
          <span class="visually-hidden" id="bd-theme-text">Cambiar Tema</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bd-theme-text">
          <li>
            <button class="dropdown-item d-flex align-items-center" type="button" data-bs-theme-value="light" aria-pressed="false">
              <span class="theme-icon-active opacity-50 me-2" data-icon="sun"></span>
              Modo Claro
            </button>
          </li>
          <li>
            <button class="dropdown-item d-flex align-items-center" type="button" data-bs-theme-value="dark" aria-pressed="false">
              <span class="theme-icon-active opacity-50 me-2" data-icon="moon"></span>
              Modo Oscuro
            </button>
          </li>
          <li>
            <button class="dropdown-item d-flex align-items-center" type="button" data-bs-theme-value="auto" aria-pressed="true">
              <span class="theme-icon-active opacity-50 me-2" data-icon="auto"></span>
              Automatico
            </button>
          </li>
        </ul>
      </li>

      <!-- Perfil de Usuario -->
      <li class="nav-item nav-item-user dropdown ms-2">
        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
          <div class="avatar bg-primary-subtle text-primary border border-primary-subtle d-flex align-items-center justify-content-center rounded-circle fw-bold me-2" style="width: 38px; height: 38px;">
            <?= strtoupper(substr($currentUser["username"] ?? "A", 0, 1)) ?>
          </div>
          <span class="d-none d-md-inline-block fw-semibold text-body">
            <?= $currentUser["username"] ?? "admin" ?>
          </span>
        </a>
        <div class="dropdown-menu dropdown-menu-end">
          <div class="dropdown-header text-muted small">
            <div><strong><?= $currentUser["name"] ?? ($currentUser["username"] ?? "Administrador") ?></strong></div>
            <div class="font-monospace small opacity-75"><?= $currentUser["email"] ?? "" ?></div>
          </div>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="/account/profile">
            <i class="bi bi-person me-2"></i>
            Mi Perfil
          </a>
          <a class="dropdown-item" href="/account/password">
            <i class="bi bi-key me-2"></i>
            Cambiar Contraseña
          </a>
          <a class="dropdown-item" href="/account/security">
            <i class="bi bi-shield-lock me-2"></i>
            Seguridad & CAPTCHA
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="/dashboard">
            <i class="bi bi-sliders me-2"></i>
            Dashboard
          </a>
          <a class="dropdown-item" href="/system">
            <i class="bi bi-hdd-stack me-2"></i>
            Estado del Servidor
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item text-danger" href="/logout">
            <i class="bi bi-box-arrow-right me-2"></i>
            Cerrar Sesión
          </a>
        </div>
      </li>
    </ul>
  </div>
</nav>
