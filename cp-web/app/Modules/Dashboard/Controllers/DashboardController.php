<?php

namespace Pirulu\Modules\Dashboard\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Database;
use Pirulu\Core\Engine;
use Pirulu\Core\View;

class DashboardController {
  /**
   * Vista principal del panel de control general (Dashboard).
   */
  public function index() {
    Auth::requireAuth();

    $db = Database::getConnection();

    // -------------------------------------------------------------------------
    // SECCION: CONTEOS Y METRICAS DE LA BASE DE DATOS
    // -------------------------------------------------------------------------
    $domainCount = (int)($db->query("SELECT COUNT(*) as total FROM domains")->fetch()["total"] ?? 0);
    $sslCount = (int)($db->query("SELECT COUNT(*) as total FROM domains WHERE ssl_enabled = 1")->fetch()["total"] ?? 0);
    $dbCount = (int)($db->query("SELECT COUNT(*) as total FROM databases")->fetch()["total"] ?? 0);
    $userCount = (int)($db->query("SELECT COUNT(*) as total FROM users")->fetch()["total"] ?? 0);

    $mailCount = 0;
    try {
      $mailCount = (int)($db->query("SELECT COUNT(*) as total FROM mail_accounts")->fetch()["total"] ?? 0);
    } catch (\Exception $e) {}

    $cronCount = 0;
    try {
      $cronCount = (int)($db->query("SELECT COUNT(*) as total FROM cron_jobs")->fetch()["total"] ?? 0);
    } catch (\Exception $e) {}

    $ftpCount = 0;
    try {
      $ftpCount = (int)($db->query("SELECT COUNT(*) as total FROM ftp_accounts")->fetch()["total"] ?? 0);
    } catch (\Exception $e) {}

    // -------------------------------------------------------------------------
    // SECCION: LISTADO DE DOMINIOS CON DETECCION DE STACK
    // -------------------------------------------------------------------------
    $stmt = $db->query("
      SELECT d.*, u.username 
      FROM domains d 
      LEFT JOIN users u ON d.user_id = u.id 
      ORDER BY d.id DESC 
      LIMIT 8
    ");
    $recentDomains = $stmt->fetchAll();

    foreach ($recentDomains as &$dom) {
      $domName = $dom["domain"];
      $uName = $dom["username"] ?? "admin";
      $wRoot = "/home/" . $uName . "/web/" . $domName;
      $dRoot = $wRoot . "/" . ($dom["doc_root_suffix"] ?? "public_html");

      $dom["stack"] = "php";
      if (file_exists($dRoot . "/wp-config.php") || file_exists($wRoot . "/wp-config.php") || file_exists($dRoot . "/wp-login.php")) {
        $dom["stack"] = "wordpress";
      } elseif (file_exists($wRoot . "/artisan") || file_exists($wRoot . "/bootstrap/app.php") || file_exists($dRoot . "/../artisan")) {
        $dom["stack"] = "laravel";
      }
    }
    unset($dom);

    // -------------------------------------------------------------------------
    // SECCION: LISTADO DE BASES DE DATOS MARIADB
    // -------------------------------------------------------------------------
    $stmt = $db->query("
      SELECT db.*, u.username 
      FROM databases db 
      LEFT JOIN users u ON db.user_id = u.id 
      ORDER BY db.id DESC 
      LIMIT 8
    ");
    $recentDatabases = $stmt->fetchAll();

    // -------------------------------------------------------------------------
    // SECCION: ESTADO DEL SISTEMA Y METRICAS DEL MOTOR
    // -------------------------------------------------------------------------
    $statusData = Engine::execute("pirulu-system", ["status"]);
    $metricsData = Engine::execute("pirulu-system", ["metrics"]);

    View::render("Modules/Dashboard/Views/index", [
      "pageTitle" => "Dashboard - PiruluGCP",
      "domainCount" => $domainCount,
      "sslCount" => $sslCount,
      "dbCount" => $dbCount,
      "userCount" => $userCount,
      "mailCount" => $mailCount,
      "cronCount" => $cronCount,
      "ftpCount" => $ftpCount,
      "recentDomains" => $recentDomains,
      "recentDatabases" => $recentDatabases,
      "services" => $statusData["services"] ?? [],
      "metrics" => $metricsData["metrics"] ?? []
    ]);
  }
}
