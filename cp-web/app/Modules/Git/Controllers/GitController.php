<?php

namespace Pirulu\Modules\Git\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Database;
use Pirulu\Core\Engine;
use Pirulu\Core\View;

class GitController {

    public function index(string $domainId): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
        $stmt->execute([(int)$domainId]);
        $domain = $stmt->fetch();

        if (!$domain) {
            View::setFlash("danger", "El dominio especificado no existe.");
            header("Location: /web");
            exit();
        }

        $stmt = $db->prepare("SELECT * FROM domain_git WHERE domain_id = ?");
        $stmt->execute([(int)$domainId]);
        $gitConfig = $stmt->fetch();

        $username = $domain["username"] ?? "admin";
        $domainName = $domain["domain"];

        // Obtener o generar clave publica SSH para el dominio si no existe
        $publicKey = $gitConfig["ssh_public_key"] ?? "";
        if (empty($publicKey)) {
            $keyRes = Engine::execute("pirulu-git", ["get-key", $username, $domainName]);
            if (isset($keyRes["status"]) && $keyRes["status"] === "success" && !empty($keyRes["public_key"])) {
                $publicKey = $keyRes["public_key"];
            } else {
                $genRes = Engine::execute("pirulu-git", ["generate-key", $username, $domainName]);
                if (isset($genRes["status"]) && $genRes["status"] === "success") {
                    $publicKey = $genRes["public_key"] ?? "";
                }
            }
        }

        // Obtener estado en vivo de Git en el servidor si esta conectado
        $liveStatus = [];
        if ($gitConfig) {
            $liveStatus = Engine::execute("pirulu-git", ["status", $username, $domainName, $domain["doc_root_suffix"] ?? "public_html"]);
        }

        // Construir URL base para el webhook
        $host = $_SERVER["HTTP_HOST"] ?? "localhost:8083";
        $scheme = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on") ? "https" : "http";
        $webhookUrl = "";
        if (!empty($gitConfig["webhook_secret"])) {
            $webhookUrl = $scheme . "://" . $host . "/api/git/webhook/" . $gitConfig["webhook_secret"];
        }

        View::render("Modules/Git/Views/index", [
            "pageTitle"   => "Gestion Git - " . $domainName,
            "domain"      => $domain,
            "git"         => $gitConfig,
            "publicKey"   => $publicKey,
            "liveStatus"  => $liveStatus,
            "webhookUrl"  => $webhookUrl
        ]);
    }

    public function connect(): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $domainId = (int)($_POST["domain_id"] ?? 0);
        $repoUrl  = trim($_POST["repo_url"] ?? "");
        $branch   = trim($_POST["branch"] ?? "main");
        $isPrivate = isset($_POST["is_private"]) ? 1 : 0;
        $autoDeploy = isset($_POST["auto_deploy"]) ? 1 : 0;

        if (empty($branch)) {
            $branch = "main";
        }

        $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
        $stmt->execute([$domainId]);
        $domain = $stmt->fetch();

        if (!$domain || empty($repoUrl)) {
            View::setFlash("danger", "Por favor completa la URL del repositorio.");
            header("Location: /web/git/" . $domainId);
            exit();
        }

        $username = $domain["username"] ?? "admin";
        $domainName = $domain["domain"];
        $docSuffix = $domain["doc_root_suffix"] ?? "public_html";

        // Asegurar clave SSH si es privado
        $keyRes = Engine::execute("pirulu-git", ["get-key", $username, $domainName]);
        $publicKey = $keyRes["public_key"] ?? "";
        $keyPath = $keyRes["key_path"] ?? "";

        if (empty($publicKey)) {
            $genRes = Engine::execute("pirulu-git", ["generate-key", $username, $domainName]);
            $publicKey = $genRes["public_key"] ?? "";
            $keyPath = $genRes["key_path"] ?? "";
        }

        // Si el repositorio es privado y se introdujo una URL HTTPS, convertirla a formato SSH
        // para que Git utilice la clave SSH Deploy Key generada en lugar de pedir usuario y clave interactiva.
        if ($isPrivate) {
            if (preg_match('#^https?://github\.com/([^/]+)/([^/]+?)(?:\.git)?$#i', $repoUrl, $m)) {
                $repoUrl = "git@github.com:" . $m[1] . "/" . $m[2] . ".git";
            } elseif (preg_match('#^https?://gitlab\.com/([^/]+)/([^/]+?)(?:\.git)?$#i', $repoUrl, $m)) {
                $repoUrl = "git@gitlab.com:" . $m[1] . "/" . $m[2] . ".git";
            } elseif (preg_match('#^https?://bitbucket\.org/([^/]+)/([^/]+?)(?:\.git)?$#i', $repoUrl, $m)) {
                $repoUrl = "git@bitbucket.org:" . $m[1] . "/" . $m[2] . ".git";
            }
        }

        // Clonar repositorio
        $cloneRes = Engine::execute("pirulu-git", ["clone", $username, $domainName, $repoUrl, $branch, $docSuffix]);

        $webhookSecret = bin2hex(random_bytes(16));

        if (isset($cloneRes["status"]) && $cloneRes["status"] === "success") {
            $commitHash   = $cloneRes["commit_hash"] ?? null;
            $commitAuthor = $cloneRes["commit_author"] ?? null;
            $commitMsg    = $cloneRes["commit_message"] ?? null;
            $log          = $cloneRes["log"] ?? "Repositorio clonado correctamente.";

            $stmt = $db->prepare("
                INSERT INTO domain_git (
                    domain_id, repo_url, branch, deploy_suffix, is_private,
                    ssh_public_key, ssh_private_key_path, webhook_secret, auto_deploy,
                    last_commit_hash, last_commit_message, last_commit_author,
                    last_deploy_at, last_deploy_status, last_deploy_log
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), 'success', ?)
                ON CONFLICT(domain_id) DO UPDATE SET
                    repo_url = excluded.repo_url,
                    branch = excluded.branch,
                    is_private = excluded.is_private,
                    ssh_public_key = excluded.ssh_public_key,
                    ssh_private_key_path = excluded.ssh_private_key_path,
                    auto_deploy = excluded.auto_deploy,
                    last_commit_hash = excluded.last_commit_hash,
                    last_commit_message = excluded.last_commit_message,
                    last_commit_author = excluded.last_commit_author,
                    last_deploy_at = excluded.last_deploy_at,
                    last_deploy_status = excluded.last_deploy_status,
                    last_deploy_log = excluded.last_deploy_log
            ");
            $stmt->execute([
                $domainId, $repoUrl, $branch, $docSuffix, $isPrivate,
                $publicKey, $keyPath, $webhookSecret, $autoDeploy,
                $commitHash, $commitMsg, $commitAuthor, $log
            ]);

            View::setFlash("success", "Repositorio vinculado y desplegado con exito.");
        } else {
            $errorLog = $cloneRes["log"] ?? ($cloneRes["message"] ?? "Error desconocido al clonar");
            View::setFlash("danger", "Fallo al clonar el repositorio: " . $errorLog);
        }

        header("Location: /web/git/" . $domainId);
        exit();
    }

    public function deploy(string $domainId): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT g.*, d.domain, d.doc_root_suffix, u.username 
            FROM domain_git g
            INNER JOIN domains d ON g.domain_id = d.id
            LEFT JOIN users u ON d.user_id = u.id
            WHERE g.domain_id = ?
        ");
        $stmt->execute([(int)$domainId]);
        $git = $stmt->fetch();

        if (!$git) {
            View::setFlash("danger", "No hay configuracion de Git para este dominio.");
            header("Location: /web");
            exit();
        }

        $username = $git["username"] ?? "admin";
        $domainName = $git["domain"];
        $branch = $git["branch"] ?? "main";
        $docSuffix = $git["doc_root_suffix"] ?? "public_html";

        $pullRes = Engine::execute("pirulu-git", ["pull", $username, $domainName, $branch, $docSuffix]);

        if (isset($pullRes["status"]) && $pullRes["status"] === "success") {
            $stmt = $db->prepare("
                UPDATE domain_git SET
                    last_commit_hash = ?,
                    last_commit_message = ?,
                    last_commit_author = ?,
                    last_deploy_at = datetime('now'),
                    last_deploy_status = 'success',
                    last_deploy_log = ?
                WHERE domain_id = ?
            ");
            $stmt->execute([
                $pullRes["commit_hash"] ?? null,
                $pullRes["commit_message"] ?? null,
                $pullRes["commit_author"] ?? null,
                $pullRes["log"] ?? "Despliegue completado",
                (int)$domainId
            ]);

            View::setFlash("success", "Despliegue (git pull) ejecutado exitosamente.");
        } else {
            $stmt = $db->prepare("
                UPDATE domain_git SET
                    last_deploy_at = datetime('now'),
                    last_deploy_status = 'error',
                    last_deploy_log = ?
                WHERE domain_id = ?
            ");
            $stmt->execute([
                $pullRes["log"] ?? ($pullRes["message"] ?? "Error en pull"),
                (int)$domainId
            ]);

            View::setFlash("danger", "Error en el despliegue: " . ($pullRes["message"] ?? "Error"));
        }

        header("Location: /web/git/" . $domainId);
        exit();
    }

    public function generateKey(string $domainId): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
        $stmt->execute([(int)$domainId]);
        $domain = $stmt->fetch();

        if ($domain) {
            $username = $domain["username"] ?? "admin";
            $domainName = $domain["domain"];

            $genRes = Engine::execute("pirulu-git", ["generate-key", $username, $domainName]);
            if (isset($genRes["status"]) && $genRes["status"] === "success") {
                $publicKey = $genRes["public_key"] ?? "";
                $keyPath = $genRes["key_path"] ?? "";

                $stmt = $db->prepare("UPDATE domain_git SET ssh_public_key = ?, ssh_private_key_path = ? WHERE domain_id = ?");
                $stmt->execute([$publicKey, $keyPath, (int)$domainId]);

                View::setFlash("success", "Nueva clave SSH generada correctamente. Recuerda agregarla a GitHub.");
            } else {
                View::setFlash("danger", "Error al generar la clave SSH.");
            }
        }

        header("Location: /web/git/" . $domainId);
        exit();
    }

    public function unlink(string $domainId): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
        $stmt->execute([(int)$domainId]);
        $domain = $stmt->fetch();

        if ($domain) {
            $username = $domain["username"] ?? "admin";
            $domainName = $domain["domain"];
            $docSuffix = $domain["doc_root_suffix"] ?? "public_html";

            Engine::execute("pirulu-git", ["unlink", $username, $domainName, $docSuffix]);

            $stmt = $db->prepare("DELETE FROM domain_git WHERE domain_id = ?");
            $stmt->execute([(int)$domainId]);

            View::setFlash("success", "Repositorio Git desvinculado del dominio.");
        }

        header("Location: /web");
        exit();
    }

    public function webhook(string $token): void {
        $token = trim($token);
        if (empty($token)) {
            http_response_code(400);
            header("Content-Type: application/json");
            echo json_encode(["status" => "error", "message" => "Token no proporcionado"]);
            exit();
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT g.*, d.domain, d.doc_root_suffix, u.username 
            FROM domain_git g
            INNER JOIN domains d ON g.domain_id = d.id
            LEFT JOIN users u ON d.user_id = u.id
            WHERE g.webhook_secret = ? AND g.auto_deploy = 1
        ");
        $stmt->execute([$token]);
        $git = $stmt->fetch();

        if (!$git) {
            http_response_code(404);
            header("Content-Type: application/json");
            echo json_encode(["status" => "error", "message" => "Webhook no encontrado o auto-deploy desactivado"]);
            exit();
        }

        // Si es una peticion GET (ej: comprobacion manual desde navegador), devolver estado activo
        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            header("Content-Type: application/json");
            echo json_encode([
                "status"  => "success",
                "message" => "Endpoint de Webhook activo para " . $git["domain"] . ". GitHub debe enviar peticiones POST al hacer push.",
                "domain"  => $git["domain"],
                "branch"  => $git["branch"]
            ]);
            exit();
        }

        $username   = $git["username"] ?? "admin";
        $domainName = $git["domain"];
        $branch     = $git["branch"] ?? "main";
        $docSuffix  = $git["doc_root_suffix"] ?? "public_html";

        $pullRes = Engine::execute("pirulu-git", ["pull", $username, $domainName, $branch, $docSuffix]);

        if (isset($pullRes["status"]) && $pullRes["status"] === "success") {
            $stmt = $db->prepare("
                UPDATE domain_git SET
                    last_commit_hash = ?,
                    last_commit_message = ?,
                    last_commit_author = ?,
                    last_deploy_at = datetime('now'),
                    last_deploy_status = 'success',
                    last_deploy_log = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $pullRes["commit_hash"] ?? null,
                $pullRes["commit_message"] ?? null,
                $pullRes["commit_author"] ?? null,
                $pullRes["log"] ?? "Auto-deploy completado",
                (int)$git["id"]
            ]);

            header("Content-Type: application/json");
            echo json_encode([
                "status"  => "success",
                "message" => "Auto-deploy completado exitosamente",
                "commit"  => $pullRes["commit_hash"] ?? ""
            ]);
        } else {
            $stmt = $db->prepare("
                UPDATE domain_git SET
                    last_deploy_at = datetime('now'),
                    last_deploy_status = 'error',
                    last_deploy_log = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $pullRes["log"] ?? "Error en auto-deploy",
                (int)$git["id"]
            ]);

            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode([
                "status"  => "error",
                "message" => "Error durante el auto-deploy"
            ]);
        }
        exit();
    }
}
