<?php

namespace Pirulu\Modules\Server\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Database;
use Pirulu\Core\Engine;
use Pirulu\Core\View;

class ServerController {
    private static array $timezones = [
        "America/Lima"         => "America/Lima (UTC -5, Peru)",
        "America/Bogota"       => "America/Bogota (UTC -5, Colombia)",
        "America/Mexico_City"  => "America/Mexico_City (UTC -6, Mexico)",
        "America/Guayaquil"    => "America/Guayaquil (UTC -5, Ecuador)",
        "America/Caracas"      => "America/Caracas (UTC -4, Venezuela)",
        "America/Santiago"     => "America/Santiago (UTC -4/-3, Chile)",
        "America/Buenos_Aires" => "America/Buenos_Aires (UTC -3, Argentina)",
        "America/La_Paz"       => "America/La_Paz (UTC -4, Bolivia)",
        "America/Montevideo"   => "America/Montevideo (UTC -3, Uruguay)",
        "America/Asuncion"     => "America/Asuncion (UTC -4/-3, Paraguay)",
        "America/Panama"       => "America/Panama (UTC -5, Panama)",
        "America/Costa_Rica"   => "America/Costa_Rica (UTC -6, Costa Rica)",
        "America/Guatemala"    => "America/Guatemala (UTC -6, Guatemala)",
        "America/New_York"     => "America/New_York (UTC -5/-4, Este EEUU)",
        "America/Chicago"      => "America/Chicago (UTC -6/-5, Centro EEUU)",
        "America/Los_Angeles"  => "America/Los_Angeles (UTC -8/-7, Pacifico EEUU)",
        "Europe/Madrid"        => "Europe/Madrid (UTC +1/+2, Espana)",
        "Europe/London"        => "Europe/London (UTC +0/+1, Reino Unido)",
        "Europe/Paris"         => "Europe/Paris (UTC +1/+2, Francia)",
        "Europe/Berlin"        => "Europe/Berlin (UTC +1/+2, Alemania)",
        "UTC"                  => "UTC (Tiempo Universal Coordinado)"
    ];

    public function index(): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $stmt = $db->query("SELECT * FROM server_settings WHERE id = 1");
        $settings = $stmt->fetch() ?: [];

        $serverConfig = Engine::execute("pirulu-server", ["get-config"]);

        // Asegurar token de webhook si no existe
        if (empty($settings["panel_webhook_token"])) {
            $token = bin2hex(random_bytes(16));
            $stmt = $db->prepare("UPDATE server_settings SET panel_webhook_token = ? WHERE id = 1");
            $stmt->execute([$token]);
            $settings["panel_webhook_token"] = $token;
        }

        // Construir URL base para el webhook de actualizaciones
        $host = $_SERVER["HTTP_HOST"] ?? "localhost:8083";
        $scheme = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on") ? "https" : "http";
        $webhookUrl = $scheme . "://" . $host . "/api/server/webhook/" . ($settings["panel_webhook_token"] ?? "");

        $activeTab = $_GET["tab"] ?? "identity";

        View::render("Modules/Server/Views/index", [
            "pageTitle"    => "Configuracion del Servidor - PiruluGCP",
            "settings"     => $settings,
            "serverConfig" => $serverConfig,
            "timezones"    => self::$timezones,
            "webhookUrl"   => $webhookUrl,
            "activeTab"    => $activeTab
        ]);
    }

    public function setHostname(): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $hostname = trim($_POST["server_hostname"] ?? "");

        if (empty($hostname)) {
            View::setFlash("danger", "El nombre del servidor no puede estar vacio.");
            header("Location: /server?tab=identity");
            exit();
        }

        $res = Engine::execute("pirulu-server", ["set-hostname", $hostname]);

        if (isset($res["status"]) && $res["status"] === "success") {
            $stmt = $db->prepare("UPDATE server_settings SET server_hostname = ?, updated_at = datetime('now') WHERE id = 1");
            $stmt->execute([$hostname]);

            View::setFlash("success", "Nombre del servidor actualizado a " . htmlspecialchars($hostname) . " exitosamente.");
        } else {
            View::setFlash("danger", "Error al configurar el nombre del servidor: " . ($res["message"] ?? "Fallo"));
        }

        header("Location: /server?tab=identity");
        exit();
    }

    public function setPanelDomain(): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $panelDomain = trim($_POST["panel_domain"] ?? "");

        $res = Engine::execute("pirulu-server", ["set-panel-domain", $panelDomain]);

        if (isset($res["status"]) && $res["status"] === "success") {
            $stmt = $db->prepare("UPDATE server_settings SET panel_domain = ?, updated_at = datetime('now') WHERE id = 1");
            $stmt->execute([$panelDomain]);

            View::setFlash("success", "Dominio de acceso al panel configurado correctamente a " . htmlspecialchars($panelDomain ?: "por defecto") . ".");
        } else {
            View::setFlash("danger", "Error al configurar dominio del panel: " . ($res["message"] ?? "Fallo"));
        }

        header("Location: /server?tab=identity");
        exit();
    }

    public function setTimezone(): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $timezone = trim($_POST["server_timezone"] ?? "");

        if (empty($timezone)) {
            View::setFlash("danger", "Por favor selecciona una zona horaria valida.");
            header("Location: /server?tab=timezone");
            exit();
        }

        $res = Engine::execute("pirulu-server", ["set-timezone", $timezone]);

        if (isset($res["status"]) && $res["status"] === "success") {
            $stmt = $db->prepare("UPDATE server_settings SET server_timezone = ?, updated_at = datetime('now') WHERE id = 1");
            $stmt->execute([$timezone]);

            View::setFlash("success", "Zona horaria actualizada a " . htmlspecialchars($timezone) . " (Hora actual: " . ($res["current_time"] ?? "") . ").");
        } else {
            View::setFlash("danger", "Error al configurar zona horaria: " . ($res["message"] ?? "Fallo"));
        }

        header("Location: /server?tab=timezone");
        exit();
    }

    public function generateKey(): void {
        Auth::requireAuth();

        $res = Engine::execute("pirulu-server", ["generate-key"]);

        if (isset($res["status"]) && $res["status"] === "success") {
            View::setFlash("success", "Nueva clave SSH de actualizacion generada exitosamente. Recuerda agregarla a GitHub.");
        } else {
            View::setFlash("danger", "Error al generar clave SSH: " . ($res["message"] ?? "Fallo"));
        }

        header("Location: /server?tab=updates");
        exit();
    }

    public function connectGit(): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $repoUrl    = trim($_POST["panel_git_repo"] ?? "");
        $branch     = trim($_POST["panel_git_branch"] ?? "main");
        $isPrivate  = isset($_POST["panel_git_is_private"]) ? 1 : 0;
        $autoUpdate = isset($_POST["panel_auto_update"]) ? 1 : 0;

        if (empty($branch)) {
            $branch = "main";
        }

        if (empty($repoUrl)) {
            View::setFlash("danger", "Por favor ingresa la URL del repositorio de GitHub.");
            header("Location: /server?tab=updates");
            exit();
        }

        // Si el repositorio es privado y se introdujo una URL HTTPS, convertir a formato SSH
        if ($isPrivate) {
            if (preg_match('#^https?://github\.com/([^/]+)/([^/]+?)(?:\.git)?$#i', $repoUrl, $m)) {
                $repoUrl = "git@github.com:" . $m[1] . "/" . $m[2] . ".git";
            } elseif (preg_match('#^https?://gitlab\.com/([^/]+)/([^/]+?)(?:\.git)?$#i', $repoUrl, $m)) {
                $repoUrl = "git@gitlab.com:" . $m[1] . "/" . $m[2] . ".git";
            } elseif (preg_match('#^https?://bitbucket\.org/([^/]+)/([^/]+?)(?:\.git)?$#i', $repoUrl, $m)) {
                $repoUrl = "git@bitbucket.org:" . $m[1] . "/" . $m[2] . ".git";
            }
        }

        $res = Engine::execute("pirulu-server", ["connect-git", $repoUrl, $branch, (string)$isPrivate]);

        if (isset($res["status"]) && $res["status"] === "success") {
            $detectedBranch = $res["branch"] ?? $branch;

            $stmt = $db->prepare("
                UPDATE server_settings SET
                    panel_git_repo = ?,
                    panel_git_branch = ?,
                    panel_git_is_private = ?,
                    panel_auto_update = ?,
                    updated_at = datetime('now')
                WHERE id = 1
            ");
            $stmt->execute([$repoUrl, $detectedBranch, $isPrivate, $autoUpdate]);

            View::setFlash("success", "Repositorio de actualizaciones vinculado con exito (Rama: " . htmlspecialchars($detectedBranch) . ").");
        } else {
            View::setFlash("danger", "Error al conectar repositorio: " . ($res["message"] ?? "Fallo"));
        }

        header("Location: /server?tab=updates");
        exit();
    }

    public function updatePanel(): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $stmt = $db->query("SELECT * FROM server_settings WHERE id = 1");
        $settings = $stmt->fetch() ?: [];

        $branch = $settings["panel_git_branch"] ?? "main";

        $res = Engine::execute("pirulu-server", ["update-panel", $branch]);

        if (isset($res["status"]) && $res["status"] === "success") {
            $log = $res["log"] ?? "Panel actualizado correctamente.";
            $stmt = $db->prepare("
                UPDATE server_settings SET
                    panel_last_update_at = datetime('now'),
                    panel_last_update_status = 'success',
                    panel_last_update_log = ?
                WHERE id = 1
            ");
            $stmt->execute([$log]);

            View::setFlash("success", "Panel PiruluGCP actualizado exitosamente a la ultima version.");
        } else {
            $log = $res["log"] ?? ($res["message"] ?? "Error al actualizar");
            $stmt = $db->prepare("
                UPDATE server_settings SET
                    panel_last_update_at = datetime('now'),
                    panel_last_update_status = 'error',
                    panel_last_update_log = ?
                WHERE id = 1
            ");
            $stmt->execute([$log]);

            View::setFlash("danger", "Error al actualizar el panel: " . ($res["message"] ?? "Error"));
        }

        header("Location: /server?tab=updates");
        exit();
    }

    public function webhook(string $token): void {
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM server_settings WHERE id = 1 AND panel_webhook_token = ?");
        $stmt->execute([$token]);
        $settings = $stmt->fetch();

        if (!$settings) {
            http_response_code(404);
            header("Content-Type: application/json");
            echo json_encode(["status" => "error", "message" => "Webhook no encontrado"]);
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            header("Content-Type: application/json");
            echo json_encode([
                "status"  => "active",
                "service" => "PiruluGCP Panel Auto-Update Webhook",
                "repo"    => $settings["panel_git_repo"] ?? "",
                "branch"  => $settings["panel_git_branch"] ?? "main"
            ]);
            exit();
        }

        if (empty($settings["panel_auto_update"])) {
            header("Content-Type: application/json");
            echo json_encode(["status" => "ignored", "message" => "Auto-actualizacion desactivada en el panel"]);
            exit();
        }

        $branch = $settings["panel_git_branch"] ?? "main";
        $res = Engine::execute("pirulu-server", ["update-panel", $branch]);

        $log = $res["log"] ?? ($res["message"] ?? "Actualizacion procesada");
        $status = (isset($res["status"]) && $res["status"] === "success") ? "success" : "error";

        $stmt = $db->prepare("
            UPDATE server_settings SET
                panel_last_update_at = datetime('now'),
                panel_last_update_status = ?,
                panel_last_update_log = ?
            WHERE id = 1
        ");
        $stmt->execute([$status, $log]);

        header("Content-Type: application/json");
        echo json_encode([
            "status"  => $status,
            "message" => "Webhook de actualizacion del panel ejecutado",
            "branch"  => $branch,
            "log"     => $log
        ]);
        exit();
    }
}
