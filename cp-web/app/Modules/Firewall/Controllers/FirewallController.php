<?php

namespace Pirulu\Modules\Firewall\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Engine;
use Pirulu\Core\View;

class FirewallController {

    public function index(): void {
        Auth::requireAuth();

        $statusData = Engine::execute("pirulu-firewall", ["status"]);
        $jailsData  = Engine::execute("pirulu-firewall", ["f2b-jails"]);
        $iptData    = Engine::execute("pirulu-firewall", ["ipt-list"]);

        View::render("Modules/Firewall/Views/index", [
            "pageTitle"  => "Firewall - PiruluGCP",
            "f2bStatus"  => $statusData["fail2ban"]  ?? ["status" => "not-found", "banned_count" => 0],
            "iptStatus"  => $statusData["iptables"]  ?? ["available" => false, "drop_rules" => 0],
            "jails"      => $jailsData["jails"]      ?? [],
            "iptRules"   => $iptData["rules"]        ?? []
        ]);
    }

    public function banIp(): void {
        Auth::requireAuth();

        $ip     = trim($_POST["ip"]     ?? "");
        $method = trim($_POST["method"] ?? "f2b");

        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            View::setFlash("danger", "La IP proporcionada no es valida.");
            header("Location: /firewall");
            exit();
        }

        if ($method === "ipt") {
            $res = Engine::execute("pirulu-firewall", ["ipt-ban", $ip]);
        } else {
            $res = Engine::execute("pirulu-firewall", ["f2b-ban", $ip]);
        }

        if (isset($res["status"]) && $res["status"] === "success") {
            View::setFlash("success", $res["message"] ?? "IP " . $ip . " baneada correctamente.");
        } else {
            View::setFlash("danger", "Error: " . ($res["message"] ?? "No se pudo banear la IP."));
        }

        header("Location: /firewall");
        exit();
    }

    public function unbanIp(): void {
        Auth::requireAuth();

        $ip     = trim($_POST["ip"]     ?? "");
        $method = trim($_POST["method"] ?? "f2b");

        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            View::setFlash("danger", "La IP proporcionada no es valida.");
            header("Location: /firewall");
            exit();
        }

        if ($method === "ipt") {
            $res = Engine::execute("pirulu-firewall", ["ipt-unban", $ip]);
        } else {
            $res = Engine::execute("pirulu-firewall", ["f2b-unban", $ip]);
        }

        if (isset($res["status"]) && $res["status"] === "success") {
            View::setFlash("success", $res["message"] ?? "IP " . $ip . " desbaneada correctamente.");
        } else {
            View::setFlash("danger", "Error: " . ($res["message"] ?? "No se pudo desbanear la IP."));
        }

        header("Location: /firewall");
        exit();
    }
}
