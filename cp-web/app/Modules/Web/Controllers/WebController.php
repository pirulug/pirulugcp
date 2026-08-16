<?php

namespace Pirulu\Modules\Web\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Database;
use Pirulu\Core\Engine;
use Pirulu\Core\View;

class WebController {
    public function index(): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $domains = $db->query("
            SELECT d.*, u.username, g.id as git_id, g.branch as git_branch, g.last_deploy_status as git_status
            FROM domains d 
            LEFT JOIN users u ON d.user_id = u.id 
            LEFT JOIN domain_git g ON d.id = g.domain_id
            ORDER BY d.id DESC
        ")->fetchAll();

        // Obtener versiones disponibles
        $phpData = Engine::execute("pirulu-php", ["versions"]);
        $phpVersions = $phpData["versions"] ?? [];

        View::render("Modules/Web/Views/index", [
            "pageTitle" => "Dominios Web - PiruluGCP",
            "domains" => $domains,
            "phpVersions" => $phpVersions
        ]);
    }

    public function create(): void {
        Auth::requireAuth();
        $db = Database::getConnection();
        $users = $db->query("SELECT id, username FROM users ORDER BY username ASC")->fetchAll();

        $phpData = Engine::execute("pirulu-php", ["versions"]);
        $phpVersions = $phpData["versions"] ?? [];

        View::render("Modules/Web/Views/create", [
            "pageTitle" => "Agregar Dominio Web - PiruluGCP",
            "users" => $users,
            "phpVersions" => $phpVersions
        ]);
    }

    public function store(): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $domain = strtolower(trim($_POST["domain"] ?? ""));
        $userId = (int)($_POST["user_id"] ?? 0);
        $phpVersion = trim($_POST["php_version"] ?? "8.2");
        $docRootSuffix = trim($_POST["doc_root_suffix"] ?? "public_html");
        $docRootSuffix = ltrim($docRootSuffix, "/");
        if (empty($docRootSuffix)) {
            $docRootSuffix = "public_html";
        }

        if (empty($domain)) {
            View::setFlash("danger", "El nombre de dominio es obligatorio.");
            header("Location: /web/create");
            exit;
        }

        // Obtener nombre de usuario
        $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userRow = $stmt->fetch();
        $username = $userRow["username"] ?? "admin";

        // Ejecutar creacion en el engine
        $res = Engine::execute("pirulu-web", ["add", $username, $domain, $phpVersion, $docRootSuffix]);

        if (isset($res["status"]) && $res["status"] === "success") {
            $stmt = $db->prepare("INSERT INTO domains (domain, user_id, php_version, doc_root_suffix, ssl_enabled) VALUES (?, ?, ?, ?, 0)");
            $stmt->execute([$domain, $userId, $phpVersion, $docRootSuffix]);
            View::setFlash("success", "Dominio " . htmlspecialchars($domain) . " creado correctamente con PHP " . htmlspecialchars($phpVersion) . " (Raiz: " . htmlspecialchars($docRootSuffix) . ").");
        } else {
            View::setFlash("danger", "Error al crear el dominio: " . htmlspecialchars($res["raw_output"] ?? "Fallo en el servidor web/FPM"));
        }

        header("Location: /web");
        exit;
    }

    public function updateDocRoot(): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $domainId = (int)($_POST["domain_id"] ?? 0);
        $newSuffix = trim($_POST["doc_root_suffix"] ?? "public_html");
        $newSuffix = ltrim($newSuffix, "/");
        if (empty($newSuffix)) {
            $newSuffix = "public_html";
        }

        $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
        $stmt->execute([$domainId]);
        $domainRow = $stmt->fetch();

        if ($domainRow) {
            $username = $domainRow["username"] ?? "admin";
            $domain = $domainRow["domain"];

            $res = Engine::execute("pirulu-web", ["set-docroot", $username, $domain, $newSuffix]);

            if (isset($res["status"]) && $res["status"] === "success") {
                $stmt = $db->prepare("UPDATE domains SET doc_root_suffix = ? WHERE id = ?");
                $stmt->execute([$newSuffix, $domainId]);
                View::setFlash("success", "Carpeta raiz actualizada a " . htmlspecialchars($newSuffix) . " para " . htmlspecialchars($domain) . ".");
            } else {
                View::setFlash("danger", "Error al cambiar carpeta raiz: " . htmlspecialchars($res["raw_output"] ?? "Error"));
            }
        }

        header("Location: /web");
        exit;
    }

    public function updatePhp(): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $domainId = (int)($_POST["domain_id"] ?? 0);
        $newPhp = trim($_POST["php_version"] ?? "");

        $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
        $stmt->execute([$domainId]);
        $domainRow = $stmt->fetch();

        if ($domainRow && !empty($newPhp)) {
            $username = $domainRow["username"] ?? "admin";
            $domain = $domainRow["domain"];

            $res = Engine::execute("pirulu-web", ["set-php", $username, $domain, $newPhp]);

            if (isset($res["status"]) && $res["status"] === "success") {
                $stmt = $db->prepare("UPDATE domains SET php_version = ? WHERE id = ?");
                $stmt->execute([$newPhp, $domainId]);
                View::setFlash("success", "Version de PHP actualizada a " . htmlspecialchars($newPhp) . " para " . htmlspecialchars($domain) . ".");
            } else {
                View::setFlash("danger", "Error al cambiar version de PHP.");
            }
        }

        header("Location: /web");
        exit;
    }

    public function enableSsl(string $id): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
        $stmt->execute([(int)$id]);
        $domainRow = $stmt->fetch();

        if ($domainRow) {
            $username = $domainRow["username"] ?? "admin";
            $domain = $domainRow["domain"];

            $res = Engine::execute("pirulu-ssl", ["issue", $username, $domain]);

            if (isset($res["status"]) && $res["status"] === "success") {
                $stmt = $db->prepare("UPDATE domains SET ssl_enabled = 1 WHERE id = ?");
                $stmt->execute([(int)$id]);
                View::setFlash("success", "Certificado SSL Let's Encrypt instalado y activado para " . htmlspecialchars($domain) . ".");
            } else {
                View::setFlash("danger", "Error al obtener certificado SSL: " . htmlspecialchars($res["raw_output"] ?? "Verifica que el DNS apunte al servidor."));
            }
        }

        header("Location: /web");
        exit;
    }

    public function disableSsl(string $id): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
        $stmt->execute([(int)$id]);
        $domainRow = $stmt->fetch();

        if ($domainRow) {
            $username = $domainRow["username"] ?? "admin";
            $domain = $domainRow["domain"];

            Engine::execute("pirulu-ssl", ["delete", $username, $domain]);

            $stmt = $db->prepare("UPDATE domains SET ssl_enabled = 0 WHERE id = ?");
            $stmt->execute([(int)$id]);

            View::setFlash("info", "Certificado SSL deshabilitado para " . htmlspecialchars($domain) . ".");
        }

        header("Location: /web");
        exit;
    }

    public function delete(string $id): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
        $stmt->execute([(int)$id]);
        $domainRow = $stmt->fetch();

        if ($domainRow) {
            $username = $domainRow["username"] ?? "admin";
            $domain = $domainRow["domain"];

            Engine::execute("pirulu-web", ["delete", $username, $domain, $domainRow["php_version"]]);

            $stmt = $db->prepare("DELETE FROM domains WHERE id = ?");
            $stmt->execute([(int)$id]);

            View::setFlash("success", "Dominio " . htmlspecialchars($domain) . " eliminado correctamente.");
        }

        header("Location: /web");
        exit;
    }
}
