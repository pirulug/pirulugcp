<?php

namespace Pirulu\Modules\Dashboard\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Database;
use Pirulu\Core\Engine;
use Pirulu\Core\View;

class DashboardController {
    public function index(): void {
        Auth::requireAuth();

        $db = Database::getConnection();

        // Obtener metricas de base de datos del panel
        $domainCount = $db->query("SELECT COUNT(*) as total FROM domains")->fetch()["total"] ?? 0;
        $dbCount = $db->query("SELECT COUNT(*) as total FROM databases")->fetch()["total"] ?? 0;
        $userCount = $db->query("SELECT COUNT(*) as total FROM users")->fetch()["total"] ?? 0;

        // Obtener estado del sistema y metricas del engine
        $statusData = Engine::execute("pirulu-system", ["status"]);
        $metricsData = Engine::execute("pirulu-system", ["metrics"]);

        View::render("Modules/Dashboard/Views/index", [
            "pageTitle" => "Dashboard - PiruluGCP",
            "domainCount" => $domainCount,
            "dbCount" => $dbCount,
            "userCount" => $userCount,
            "services" => $statusData["services"] ?? [],
            "metrics" => $metricsData["metrics"] ?? []
        ]);
    }
}
