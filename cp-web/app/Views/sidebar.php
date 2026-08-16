<?php
$currentUri = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH);
?>
<nav class="sidebar js-sidebar" id="sidebar">
  <div class="sidebar-content js-simplebar">
    <a class="sidebar-brand d-flex align-items-center" href="/dashboard">
      <span class="sidebar-brand-text align-middle fw-bold">
        Pirulu<span class="text-primary">GCP</span>
      </span>
    </a>
    <a class="sidebar-close js-sidebar-close" href="#" title="Cerrar"><i class="bi bi-x-lg"></i></a>

    <ul class="sidebar-nav">
      <li class="sidebar-header">General</li>

      <li class="sidebar-item <?= ($currentUri === "/dashboard" || $currentUri === "/") ? "active" : "" ?>">
        <a class="sidebar-link" href="/dashboard">
          <i class="align-middle bi bi-sliders"></i>
          <span class="align-middle">Dashboard</span>
        </a>
      </li>

      <li class="sidebar-header">Gestion Web</li>

      <li class="sidebar-item <?= (strpos($currentUri, "/web") === 0) ? "active" : "" ?>">
        <a class="sidebar-link" href="/web">
          <i class="align-middle bi bi-globe"></i>
          <span class="align-middle">Dominios Web</span>
        </a>
      </li>

      <li class="sidebar-item <?= (strpos($currentUri, "/files") === 0) ? "active" : "" ?>">
        <a class="sidebar-link" href="/files">
          <i class="align-middle bi bi-folder2-open"></i>
          <span class="align-middle">Gestor de Archivos</span>
        </a>
      </li>

      <li class="sidebar-item <?= (strpos($currentUri, "/ftp") === 0) ? "active" : "" ?>">
        <a class="sidebar-link" href="/ftp">
          <i class="align-middle bi bi-folder-symlink"></i>
          <span class="align-middle">Servidor FTP</span>
        </a>
      </li>

      <li class="sidebar-item <?= (strpos($currentUri, "/mail") === 0) ? "active" : "" ?>">
        <a class="sidebar-link" href="/mail">
          <i class="align-middle bi bi-envelope-at"></i>
          <span class="align-middle">Cuentas de Correo</span>
        </a>
      </li>

      <li class="sidebar-header">PHP &amp; Bases de Datos</li>

      <li class="sidebar-item <?= (strpos($currentUri, "/php") === 0) ? "active" : "" ?>">
        <a class="sidebar-link" href="/php">
          <i class="align-middle bi bi-code-slash"></i>
          <span class="align-middle">Versiones PHP-FPM</span>
        </a>
      </li>

      <li class="sidebar-item <?= (strpos($currentUri, "/database") === 0) ? "active" : "" ?>">
        <a class="sidebar-link" href="/database">
          <i class="align-middle bi bi-database"></i>
          <span class="align-middle">Bases de Datos</span>
        </a>
      </li>

      <li class="sidebar-item">
        <a class="sidebar-link" href="/pma" target="_blank">
          <i class="align-middle bi bi-box-arrow-up-right"></i>
          <span class="align-middle">phpMyAdmin</span>
        </a>
      </li>

      <li class="sidebar-header">Servidor &amp; Mantenimiento</li>

      <li class="sidebar-item <?= (strpos($currentUri, "/server") === 0) ? "active" : "" ?>">
        <a class="sidebar-link" href="/server">
          <i class="align-middle bi bi-hdd-network"></i>
          <span class="align-middle">Configuración Servidor</span>
        </a>
      </li>

      <li class="sidebar-item <?= (strpos($currentUri, "/system") === 0) ? "active" : "" ?>">
        <a class="sidebar-link" href="/system">
          <i class="align-middle bi bi-hdd-stack"></i>
          <span class="align-middle">Servicios y Sistema</span>
        </a>
      </li>

      <li class="sidebar-item <?= (strpos($currentUri, "/cron") === 0) ? "active" : "" ?>">
        <a class="sidebar-link" href="/cron">
          <i class="align-middle bi bi-clock-history"></i>
          <span class="align-middle">Tareas Cron</span>
        </a>
      </li>

      <li class="sidebar-item <?= (strpos($currentUri, "/firewall") === 0) ? "active" : "" ?>">
        <a class="sidebar-link" href="/firewall">
          <i class="align-middle bi bi-shield-lock"></i>
          <span class="align-middle">Firewall</span>
        </a>
      </li>

      <li class="sidebar-item <?= (strpos($currentUri, "/logs") === 0) ? "active" : "" ?>">
        <a class="sidebar-link" href="/logs">
          <i class="align-middle bi bi-journal-text"></i>
          <span class="align-middle">Logs del Servidor</span>
        </a>
      </li>
    </ul>

    <div class="sidebar-cta m-3 p-3 rounded bg-body-tertiary border">
      <div class="small fw-semibold text-body mb-1">Servidores Activos</div>
      <div class="small text-muted mb-2">
        <div>Frontend: <strong>Nginx Proxy</strong></div>
        <div>Backend: <strong>Apache (8080)</strong></div>
        <div>MariaDB: <strong>Activo</strong></div>
      </div>
      <div class="text-muted small">PiruluGCP v1.0.0</div>
    </div>
  </div>
</nav>
