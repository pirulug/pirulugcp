<?php

namespace Pirulu\Modules\Logs\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Database;
use Pirulu\Core\Engine;
use Pirulu\Core\View;

class LogsController {
    private array $availableLogs = [
        "nginx_panel_access" => ["name" => "Nginx - Acceso Panel (8083)", "group" => "Panel"],
        "nginx_panel_error"  => ["name" => "Nginx - Errores Panel (8083)", "group" => "Panel"],
        "pirulu_install"     => ["name" => "PiruluGCP - Log de Instalador", "group" => "Panel"],
        "pirulu_engine"      => ["name" => "PiruluGCP - Log del Engine", "group" => "Panel"],
        "nginx_access"       => ["name" => "Nginx - Acceso General (Proxy)", "group" => "Nginx"],
        "nginx_error"        => ["name" => "Nginx - Errores General", "group" => "Nginx"],
        "apache_access"      => ["name" => "Apache - Acceso General (8080)", "group" => "Apache"],
        "apache_error"       => ["name" => "Apache - Errores General", "group" => "Apache"],
        "apache_other"       => ["name" => "Apache - Otros VHosts", "group" => "Apache"],
        "php8.2_fpm"         => ["name" => "PHP 8.2 FPM - Log de Servicio", "group" => "PHP-FPM"],
        "php8.3_fpm"         => ["name" => "PHP 8.3 FPM - Log de Servicio", "group" => "PHP-FPM"],
        "php8.4_fpm"         => ["name" => "PHP 8.4 FPM - Log de Servicio", "group" => "PHP-FPM"],
        "php8.5_fpm"         => ["name" => "PHP 8.5 FPM - Log de Servicio", "group" => "PHP-FPM"],
        "php8.1_fpm"         => ["name" => "PHP 8.1 FPM - Log de Servicio", "group" => "PHP-FPM"],
        "php8.0_fpm"         => ["name" => "PHP 8.0 FPM - Log de Servicio", "group" => "PHP-FPM"],
        "php7.4_fpm"         => ["name" => "PHP 7.4 FPM - Log de Servicio", "group" => "PHP-FPM"],
        "mariadb_error"      => ["name" => "MariaDB - Log de Errores", "group" => "MariaDB"]
    ];

    public function index(): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $domains = $db->query("SELECT domain FROM domains ORDER BY domain ASC")->fetchAll();
        $domainLogs = [];
        foreach ($domains as $d) {
            $domainLogs["domain_nginx_" . $d["domain"]] = ["name" => "Dominio: " . $d["domain"] . " (Nginx)", "group" => "Dominios"];
            $domainLogs["domain_apache_" . $d["domain"]] = ["name" => "Dominio: " . $d["domain"] . " (Apache)", "group" => "Dominios"];
        }

        $allLogs = array_merge($this->availableLogs, $domainLogs);

        $selectedLog = $_GET["log"] ?? "nginx_panel_access";
        $lines = (int)($_GET["lines"] ?? 100);
        if (!array_key_exists($selectedLog, $allLogs)) {
            $selectedLog = "nginx_panel_access";
        }

        $logContent = Engine::executeRaw("pirulu-log", ["read", $selectedLog, (string)$lines]);

        View::render("Modules/Logs/Views/index", [
            "pageTitle" => "Visor de Logs del Sistema - PiruluGCP",
            "availableLogs" => $allLogs,
            "selectedLog" => $selectedLog,
            "lines" => $lines,
            "logContent" => $logContent
        ]);
    }

    public function clear(): void {
        Auth::requireAuth();

        $selectedLog = trim($_POST["log"] ?? "");
        if (!empty($selectedLog)) {
            Engine::execute("pirulu-log", ["clear", $selectedLog]);
            View::setFlash("success", "Archivo de log vaciado exitosamente.");
        }

        header("Location: /logs?log=" . urlencode($selectedLog));
        exit;
    }
}
