<?php

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

$router = new Router();

// Rutas de Autenticacion
$router->get("/login", [AuthController::class, "showLogin"]);
$router->post("/login", [AuthController::class, "login"]);
$router->get("/logout", [AuthController::class, "logout"]);

// Rutas de Dashboard
$router->get("/", [DashboardController::class, "index"]);
$router->get("/dashboard", [DashboardController::class, "index"]);

// Rutas de Dominios Web (Nginx / Apache)
$router->get("/web", [WebController::class, "index"]);
$router->get("/web/create", [WebController::class, "create"]);
$router->post("/web/store", [WebController::class, "store"]);
$router->post("/web/update-php", [WebController::class, "updatePhp"]);
$router->post("/web/update-docroot", [WebController::class, "updateDocRoot"]);
$router->get("/web/enable-ssl/{id}", [WebController::class, "enableSsl"]);
$router->get("/web/disable-ssl/{id}", [WebController::class, "disableSsl"]);
$router->get("/web/delete/{id}", [WebController::class, "delete"]);

// Rutas de Gestor de Archivos (File Manager)
$router->get("/files", [FileManagerController::class, "index"]);
$router->post("/files/upload", [FileManagerController::class, "upload"]);
$router->post("/files/mkdir", [FileManagerController::class, "createFolder"]);
$router->post("/files/touch", [FileManagerController::class, "createFile"]);
$router->get("/files/read", [FileManagerController::class, "readFile"]);
$router->post("/files/save", [FileManagerController::class, "saveFile"]);
$router->post("/files/delete", [FileManagerController::class, "deleteItem"]);
$router->post("/files/extract", [FileManagerController::class, "extractZip"]);
$router->get("/files/download", [FileManagerController::class, "download"]);
$router->post("/files/chmod", [FileManagerController::class, "chmod"]);

// Rutas de PHP-FPM Multi-Version
$router->get("/php", [PhpController::class, "index"]);
$router->get("/php/restart/{version}", [PhpController::class, "restart"]);

// Rutas de Bases de Datos (MariaDB) y phpMyAdmin Auto-Login
$router->get("/database", [DatabaseController::class, "index"]);
$router->get("/database/create", [DatabaseController::class, "create"]);
$router->post("/database/store", [DatabaseController::class, "store"]);
$router->get("/database/autologin/{id}", [DatabaseController::class, "autologin"]);
$router->get("/pma", [DatabaseController::class, "pmaRedirect"]);
$router->get("/database/delete/{id}", [DatabaseController::class, "delete"]);

// Rutas de Sistema y Servicios
$router->get("/system", [SystemController::class, "index"]);
$router->post("/system/action", [SystemController::class, "serviceAction"]);

// Rutas de Visor de Logs
$router->get("/logs", [LogsController::class, "index"]);
$router->post("/logs/clear", [LogsController::class, "clear"]);

// Despachar la peticion
$router->dispatch();
