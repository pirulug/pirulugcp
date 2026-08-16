<?php

namespace Pirulu\Modules\Php\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Database;
use Pirulu\Core\Engine;
use Pirulu\Core\View;

class PhpController {
  public function index(): void {
    Auth::requireAuth();
    $db = Database::getConnection();

    // Obtener versiones disponibles y su estado
    $phpData = Engine::execute("pirulu-php", ["versions"]);
    $versions = $phpData["versions"] ?? [];

    foreach ($versions as &$v) {
      if (!isset($v["service"])) {
        $v["service"] = "php" . $v["version"] . "-fpm";
      }
    }
    unset($v);

    // Contar dominios usando cada version de PHP
    $usageStats = [];
    $stmt = $db->query("SELECT php_version, COUNT(*) as total FROM domains GROUP BY php_version");
    while ($row = $stmt->fetch()) {
      $usageStats[$row["php_version"]] = $row["total"];
    }

    View::render("Modules/Php/Views/index", [
      "pageTitle" => "Gestor de PHP Multi-Version - PiruluGCP",
      "versions" => $versions,
      "phpVersions" => $versions,
      "usageStats" => $usageStats
    ]);
  }

  public function restart(string $version): void {
    Auth::requireAuth();

    $res = Engine::execute("pirulu-php", ["restart", $version]);
    if (isset($res["status"]) && $res["status"] === "success") {
      View::setFlash("success", "Servicio PHP-FPM " . htmlspecialchars($version) . " reiniciado correctamente.");
    } else {
      View::setFlash("danger", "Error al reiniciar PHP-FPM " . htmlspecialchars($version) . ".");
    }

    header("Location: /php");
    exit();
  }
}
