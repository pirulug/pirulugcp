<?php

// Habilitar visualizacion completa de errores en el panel de control
ini_set("display_errors", "1");
ini_set("display_startup_errors", "1");
error_reporting(E_ALL);

// Manejador global de excepciones no capturadas
set_exception_handler(function (Throwable $e) {
  http_response_code(500);
  ?>
  <!DOCTYPE html>
  <html lang="es" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error PHP - PiruluGCP</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.min.css">
  </head>
  <body class="bg-body-tertiary p-4">
    <div class="container" style="max-width: 900px;">
      <div class="card border-danger mb-4">
        <div class="card-header bg-danger text-white d-flex align-items-center">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <strong>Error PHP / Excepcion no capturada</strong>
        </div>
        <div class="card-body">
          <h5 class="text-danger fw-bold mb-2"><?= $e->getMessage() ?></h5>
          <p class="text-muted small mb-3">
            Tipo: <code><?= get_class($e) ?></code> | Codigo: <code><?= $e->getCode() ?></code>
          </p>
          <div class="bg-dark p-3 rounded text-light font-monospace small mb-3">
            <strong>Archivo:</strong> <?= $e->getFile() ?><br>
            <strong>Linea:</strong> <?= $e->getLine() ?>
          </div>
          <h6 class="fw-bold">Pila de Ejecucion (Stack Trace):</h6>
          <pre class="bg-dark text-light p-3 rounded small font-monospace" style="max-height: 350px; overflow-y: auto;"><code><?= $e->getTraceAsString() ?></code></pre>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm text-uppercase fw-bold">
            <i class="bi bi-arrow-left me-1"></i> Volver
          </a>
          <a href="/" class="btn btn-primary btn-sm text-uppercase fw-bold">
            <i class="bi bi-house me-1"></i> Ir al Dashboard
          </a>
        </div>
      </div>
    </div>
  </body>
  </html>
  <?php
  exit();
});

// Autocarga de clases PSR-4 simple para PiruluGCP
spl_autoload_register(function ($class) {
  $prefix = "Pirulu\\";
  $baseDir = dirname(__DIR__) . "/app/";

  $len = strlen($prefix);
  if (strncmp($prefix, $class, $len) !== 0) {
    return;
  }

  $relativeClass = substr($class, $len);
  $file = $baseDir . str_replace("\\", "/", $relativeClass) . ".php";

  if (file_exists($file)) {
    require $file;
  }
});

use Pirulu\Core\Router;
use Pirulu\Modules\Auth\Controllers\AuthController;
use Pirulu\Modules\Dashboard\Controllers\DashboardController;
use Pirulu\Modules\Web\Controllers\WebController;
use Pirulu\Modules\Php\Controllers\PhpController;
use Pirulu\Modules\Database\Controllers\DatabaseController;
use Pirulu\Modules\System\Controllers\SystemController;
use Pirulu\Modules\Logs\Controllers\LogsController;
use Pirulu\Modules\FileManager\Controllers\FileManagerController;
use Pirulu\Modules\Firewall\Controllers\FirewallController;
use Pirulu\Modules\Git\Controllers\GitController;
use Pirulu\Modules\Server\Controllers\ServerController;
use Pirulu\Modules\Mail\Controllers\MailController;
use Pirulu\Modules\Cron\Controllers\CronController;
use Pirulu\Modules\Ftp\Controllers\FtpController;
use Pirulu\Modules\Account\Controllers\AccountController;

$router = new Router();

// Rutas de Autenticacion
$router->get("/login", [AuthController::class, "showLogin"]);
$router->post("/login", [AuthController::class, "login"]);
$router->get("/login/2fa", [AuthController::class, "show2fa"]);
$router->post("/login/2fa", [AuthController::class, "verify2fa"]);
$router->get("/logout", [AuthController::class, "logout"]);

// Rutas de Mi Cuenta / Perfil / Contraseña / Seguridad & 2FA
$router->get("/account", [AccountController::class, "profile"]);
$router->get("/account/profile", [AccountController::class, "profile"]);
$router->post("/account/profile", [AccountController::class, "updateProfile"]);
$router->get("/account/password", [AccountController::class, "password"]);
$router->post("/account/password", [AccountController::class, "updatePassword"]);
$router->get("/account/security", [AccountController::class, "security"]);
$router->post("/account/security", [AccountController::class, "updateSecurity"]);
$router->post("/account/2fa/enable", [AccountController::class, "enable2fa"]);
$router->post("/account/2fa/disable", [AccountController::class, "disable2fa"]);

// Rutas de Dashboard
$router->get("/", [DashboardController::class, "index"]);
$router->get("/dashboard", [DashboardController::class, "index"]);

// Rutas de Servidor FTP (vsftpd)
$router->get("/ftp", [FtpController::class, "index"]);
$router->post("/ftp/store", [FtpController::class, "store"]);
$router->post("/ftp/password", [FtpController::class, "updatePassword"]);
$router->post("/ftp/path", [FtpController::class, "updatePath"]);
$router->get("/ftp/delete/{id}", [FtpController::class, "delete"]);

// Rutas de Servidor de Correo (Exim4 + Dovecot + Webmail)
$router->get("/mail", [MailController::class, "index"]);
$router->get("/mail/enable/{id}", [MailController::class, "enable"]);
$router->get("/mail/disable/{id}", [MailController::class, "disable"]);
$router->get("/mail/domain/{id}", [MailController::class, "domain"]);
$router->post("/mail/account/create/{id}", [MailController::class, "createAccount"]);
$router->post("/mail/account/password", [MailController::class, "updatePassword"]);
$router->post("/mail/account/quota", [MailController::class, "updateQuota"]);
$router->get("/mail/account/delete/{id}", [MailController::class, "deleteAccount"]);
$router->post("/mail/forwarder/create/{id}", [MailController::class, "createForwarder"]);
$router->get("/mail/forwarder/delete/{id}", [MailController::class, "deleteForwarder"]);

// Rutas de Dominios Web (Nginx / Apache)
$router->get("/web", [WebController::class, "index"]);
$router->get("/web/create", [WebController::class, "create"]);
$router->post("/web/store", [WebController::class, "store"]);
$router->get("/web/domain/{id}", [WebController::class, "show"]);
$router->get("/web/domain/{id}/edit", [WebController::class, "edit"]);
$router->post("/web/domain/{id}/edit", [WebController::class, "update"]);
$router->post("/web/domain/{id}/env", [WebController::class, "saveEnv"]);
$router->post("/web/domain/{id}/tinker", [WebController::class, "runTinker"]);
$router->get("/web/domain/{id}/debug/clear", [WebController::class, "clearDebug"]);
$router->post("/web/domain/{id}/debug/clear", [WebController::class, "clearDebug"]);
$router->get("/web/domain/{id}/debug/toggle-sql", [WebController::class, "toggleSqlCapture"]);
$router->post("/web/domain/{id}/debug/toggle-sql", [WebController::class, "toggleSqlCapture"]);
$router->post("/web/update-php", [WebController::class, "updatePhp"]);
$router->post("/web/update-docroot", [WebController::class, "updateDocRoot"]);
$router->get("/web/enable-ssl/{id}", [WebController::class, "enableSsl"]);
$router->get("/web/disable-ssl/{id}", [WebController::class, "disableSsl"]);
$router->get("/web/delete/{id}", [WebController::class, "delete"]);

// Rutas de Integracion Git (GitHub / GitLab / Bitbucket)
$router->get("/web/git/{id}", [GitController::class, "index"]);
$router->post("/web/git/connect", [GitController::class, "connect"]);
$router->post("/web/git/deploy/{id}", [GitController::class, "deploy"]);
$router->post("/web/git/composer/{id}", [GitController::class, "composer"]);
$router->get("/web/git/generate-key/{id}", [GitController::class, "generateKey"]);
$router->get("/web/git/unlink/{id}", [GitController::class, "unlink"]);

// Endpoint publico para Webhooks de GitHub (Auto-Deploy)
$router->get("/api/git/webhook/{token}", [GitController::class, "webhook"]);
$router->post("/api/git/webhook/{token}", [GitController::class, "webhook"]);

// Rutas de Gestor de Archivos (File Manager)
$router->get("/files", [FileManagerController::class, "index"]);
$router->post("/files/upload", [FileManagerController::class, "upload"]);
$router->post("/files/mkdir", [FileManagerController::class, "createFolder"]);
$router->post("/files/touch", [FileManagerController::class, "createFile"]);
$router->get("/files/read", [FileManagerController::class, "readFile"]);
$router->post("/files/save", [FileManagerController::class, "saveFile"]);
$router->post("/files/delete", [FileManagerController::class, "deleteItem"]);
$router->post("/files/copy", [FileManagerController::class, "copyItem"]);
$router->post("/files/move", [FileManagerController::class, "moveItem"]);
$router->post("/files/rename", [FileManagerController::class, "renameItem"]);
$router->post("/files/compress", [FileManagerController::class, "compressItem"]);
$router->post("/files/extract", [FileManagerController::class, "extractZip"]);
$router->get("/files/download", [FileManagerController::class, "download"]);
$router->post("/files/chmod", [FileManagerController::class, "chmod"]);
$router->post("/files/composer", [FileManagerController::class, "composerAction"]);

// Rutas de PHP-FPM Multi-Version
$router->get("/php", [PhpController::class, "index"]);
$router->get("/php/install/{version}", [PhpController::class, "install"]);
$router->get("/php/uninstall/{version}", [PhpController::class, "uninstall"]);
$router->get("/php/config/{version}", [PhpController::class, "config"]);
$router->post("/php/config/{version}/save", [PhpController::class, "saveConfig"]);
$router->post("/php/config/{version}/raw", [PhpController::class, "saveRawIni"]);
$router->get("/php/restart/{version}", [PhpController::class, "restart"]);

// Rutas de Bases de Datos (MariaDB) y phpMyAdmin Auto-Login
$router->get("/database", [DatabaseController::class, "index"]);
$router->get("/database/create", [DatabaseController::class, "create"]);
$router->post("/database/store", [DatabaseController::class, "store"]);
$router->get("/database/dump/{id}", [DatabaseController::class, "dump"]);
$router->post("/database/config/save", [DatabaseController::class, "saveConfig"]);
$router->get("/database/edit/{id}", [DatabaseController::class, "edit"]);
$router->post("/database/update/{id}", [DatabaseController::class, "update"]);
$router->get("/database/autologin/{id}", [DatabaseController::class, "autologin"]);
$router->get("/pma", [DatabaseController::class, "pmaRedirect"]);
$router->get("/database/delete/{id}", [DatabaseController::class, "delete"]);

// Rutas de Sistema y Servicios
$router->get("/system", [SystemController::class, "index"]);
$router->post("/system/action", [SystemController::class, "serviceAction"]);

// Rutas de Configuracion del Servidor y Actualizaciones del Panel (GitHub)
$router->get("/server", [ServerController::class, "index"]);
$router->post("/server/hostname", [ServerController::class, "setHostname"]);
$router->post("/server/panel-domain", [ServerController::class, "setPanelDomain"]);
$router->post("/server/timezone", [ServerController::class, "setTimezone"]);
$router->post("/server/git/connect", [ServerController::class, "connectGit"]);
$router->post("/server/git/update", [ServerController::class, "updatePanel"]);
$router->get("/server/git/generate-key", [ServerController::class, "generateKey"]);
$router->get("/api/server/webhook/{token}", [ServerController::class, "webhook"]);
$router->post("/api/server/webhook/{token}", [ServerController::class, "webhook"]);

// Rutas de Visor de Logs
$router->get("/logs", [LogsController::class, "index"]);
$router->post("/logs/clear", [LogsController::class, "clear"]);

// Rutas de Firewall (Fail2Ban + IPTables)
$router->get("/firewall", [FirewallController::class, "index"]);
$router->post("/firewall/ban", [FirewallController::class, "banIp"]);
$router->post("/firewall/unban", [FirewallController::class, "unbanIp"]);

// Rutas de Programador de Tareas Cron
$router->get("/cron", [CronController::class, "index"]);
$router->post("/cron/store", [CronController::class, "store"]);
$router->post("/cron/update/{id}", [CronController::class, "update"]);
$router->get("/cron/toggle/{id}", [CronController::class, "toggle"]);
$router->get("/cron/delete/{id}", [CronController::class, "delete"]);
$router->post("/cron/run/{id}", [CronController::class, "run_now"]);

// Despachar la peticion
$router->dispatch();
