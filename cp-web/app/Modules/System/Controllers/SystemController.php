<?php

namespace Pirulu\Modules\System\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Engine;
use Pirulu\Core\View;

class SystemController {
    public function index(): void {
        Auth::requireAuth();

        $statusData = Engine::execute("pirulu-system", ["status"]);
        $metricsData = Engine::execute("pirulu-system", ["metrics"]);

        View::render("Modules/System/Views/index", [
            "pageTitle" => "Servicios y Estado del Sistema - PiruluGCP",
            "services" => $statusData["services"] ?? [],
            "metrics" => $metricsData["metrics"] ?? []
        ]);
    }

    public function serviceAction(): void {
        Auth::requireAuth();

        $service = trim($_POST["service"] ?? "");
        $action = trim($_POST["action"] ?? "");

        if (!empty($service) && in_array($action, ["start", "stop", "restart", "reload"])) {
            $res = Engine::execute("pirulu-system", ["service-action", $service, $action]);
            if (isset($res["status"]) && $res["status"] === "success") {
                View::setFlash("success", "Accion " . htmlspecialchars($action) . " aplicada a " . htmlspecialchars($service) . ".");
            } else {
                View::setFlash("danger", "Error al aplicar accion al servicio.");
            }
        }

        header("Location: /system");
        exit;
    }
}
