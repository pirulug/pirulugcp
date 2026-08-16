<?php

namespace Pirulu\Modules\Mail\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Database;
use Pirulu\Core\Engine;
use Pirulu\Core\View;

class MailController {
    public function index(): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $query = "
            SELECT 
                d.id, 
                d.domain, 
                d.domain as domain_name,
                d.php_version, 
                d.created_at as domain_created,
                md.id as mail_domain_id,
                md.status as mail_status,
                md.dkim_selector,
                md.created_at as mail_created,
                (SELECT COUNT(*) FROM mail_accounts ma WHERE ma.mail_domain_id = md.id) as total_accounts,
                (SELECT COUNT(*) FROM mail_forwarders mf WHERE mf.mail_domain_id = md.id) as total_forwarders
            FROM domains d
            LEFT JOIN mail_domains md ON d.id = md.domain_id
            ORDER BY d.domain ASC
        ";
        $domains = $db->query($query)->fetchAll();

        $mailServiceStatus = Engine::execute("pirulu-mail", ["status"]);

        View::render("Modules/Mail/Views/index", [
            "pageTitle"         => "Servidor de Correo y Webmail - PiruluGCP",
            "domains"           => $domains,
            "mailServiceStatus" => $mailServiceStatus
        ]);
    }

    public function enable(int $domainId): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM domains WHERE id = ?");
        $stmt->execute([$domainId]);
        $domain = $stmt->fetch();

        if (!$domain) {
            View::setFlash("danger", "Dominio web no encontrado.");
            header("Location: /mail");
            exit();
        }

        $domainName = $domain["domain"] ?? ($domain["domain_name"] ?? "");

        // Comprobar si ya esta habilitado
        $stmt = $db->prepare("SELECT id FROM mail_domains WHERE domain_id = ?");
        $stmt->execute([$domainId]);
        if ($stmt->fetch()) {
            header("Location: /mail/domain/" . $domainId);
            exit();
        }

        $res = Engine::execute("pirulu-mail", ["domain-add", $domainName, "admin"]);

        if (isset($res["status"]) && $res["status"] === "success") {
            $stmt = $db->prepare("
                INSERT INTO mail_domains (domain_id, domain_name, dkim_selector, dkim_record, spf_record, status)
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([
                $domainId,
                $domainName,
                $res["dkim_selector"] ?? "default",
                $res["dkim_record"] ?? "",
                $res["spf_record"] ?? ""
            ]);

            View::setFlash("success", "Servicio de correo y Webmail habilitados para " . $domainName . " exitosamente.");
            header("Location: /mail/domain/" . $domainId);
            exit();
        } else {
            View::setFlash("danger", "Error al habilitar servicio de correo: " . ($res["message"] ?? "Fallo"));
            header("Location: /mail");
            exit();
        }
    }

    public function disable(int $domainId): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM domains WHERE id = ?");
        $stmt->execute([$domainId]);
        $domain = $stmt->fetch();

        if ($domain) {
            $domainName = $domain["domain"] ?? ($domain["domain_name"] ?? "");
            Engine::execute("pirulu-mail", ["domain-del", $domainName]);
            $stmt = $db->prepare("DELETE FROM mail_domains WHERE domain_id = ?");
            $stmt->execute([$domainId]);

            View::setFlash("info", "Servicio de correo deshabilitado para " . $domainName . ".");
        }

        header("Location: /mail");
        exit();
    }

    public function domain(int $domainId): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT d.id, d.domain, d.domain as domain_name, d.php_version, md.id as mail_domain_id, md.dkim_selector, md.dkim_record, md.spf_record, md.status as mail_status
            FROM domains d
            JOIN mail_domains md ON d.id = md.domain_id
            WHERE d.id = ?
        ");
        $stmt->execute([$domainId]);
        $mailDomain = $stmt->fetch();

        if (!$mailDomain) {
            View::setFlash("warning", "El servicio de correo no esta habilitado para este dominio.");
            header("Location: /mail");
            exit();
        }

        $domainName = $mailDomain["domain"] ?? ($mailDomain["domain_name"] ?? "");

        // Cuentas de correo del dominio
        $stmt = $db->prepare("SELECT * FROM mail_accounts WHERE mail_domain_id = ? ORDER BY account_user ASC");
        $stmt->execute([$mailDomain["mail_domain_id"]]);
        $accounts = $stmt->fetchAll();

        // Reenvios del dominio
        $stmt = $db->prepare("SELECT * FROM mail_forwarders WHERE mail_domain_id = ? ORDER BY source_email ASC");
        $stmt->execute([$mailDomain["mail_domain_id"]]);
        $forwarders = $stmt->fetchAll();

        // Obtener registros DNS actualizados
        $dnsInfo = Engine::execute("pirulu-mail", ["dkim-get", $domainName]);

        $activeTab = $_GET["tab"] ?? "accounts";

        View::render("Modules/Mail/Views/domain", [
            "pageTitle"   => "Gestion de Correo - " . $domainName,
            "domain"      => $mailDomain,
            "accounts"    => $accounts,
            "forwarders"  => $forwarders,
            "dnsInfo"     => $dnsInfo,
            "activeTab"   => $activeTab
        ]);
    }

    public function createAccount(int $domainId): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $user     = trim($_POST["account_user"] ?? "");
        $password = trim($_POST["account_password"] ?? "");
        $quotaMb  = (int)($_POST["quota_mb"] ?? 1024);

        if (empty($user) || empty($password)) {
            View::setFlash("danger", "El nombre de cuenta y la contraseña son obligatorios.");
            header("Location: /mail/domain/" . $domainId . "?tab=accounts");
            exit();
        }

        // Limpiar nombre de usuario (solo caracteres validos)
        $user = preg_replace("/[^a-zA-Z0-9._-]/", "", strtolower($user));

        $stmt = $db->prepare("SELECT d.domain as domain_name, md.id as mail_domain_id FROM domains d JOIN mail_domains md ON d.id = md.domain_id WHERE d.id = ?");
        $stmt->execute([$domainId]);
        $domain = $stmt->fetch();

        if (!$domain) {
            View::setFlash("danger", "Dominio no valido.");
            header("Location: /mail");
            exit();
        }

        $email = $user . "@" . $domain["domain_name"];

        // Verificar duplicidad
        $stmt = $db->prepare("SELECT id FROM mail_accounts WHERE account_email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            View::setFlash("danger", "La cuenta de correo " . $email . " ya existe.");
            header("Location: /mail/domain/" . $domainId . "?tab=accounts");
            exit();
        }

        $res = Engine::execute("pirulu-mail", ["account-add", $email, $password, "admin", (string)$quotaMb]);

        if (isset($res["status"]) && $res["status"] === "success") {
            $stmt = $db->prepare("
                INSERT INTO mail_accounts (mail_domain_id, account_user, account_email, quota_mb, status)
                VALUES (?, ?, ?, ?, 'active')
            ");
            $stmt->execute([$domain["mail_domain_id"], $user, $email, $quotaMb]);

            View::setFlash("success", "Cuenta de correo " . $email . " creada exitosamente.");
        } else {
            View::setFlash("danger", "Error al crear cuenta: " . ($res["message"] ?? "Fallo"));
        }

        header("Location: /mail/domain/" . $domainId . "?tab=accounts");
        exit();
    }

    public function updatePassword(): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $accountId   = (int)($_POST["account_id"] ?? 0);
        $newPassword = trim($_POST["new_password"] ?? "");
        $domainId    = (int)($_POST["domain_id"] ?? 0);

        if (empty($newPassword) || $accountId <= 0) {
            View::setFlash("danger", "Por favor ingresa una contraseña valida.");
            header("Location: /mail/domain/" . $domainId . "?tab=accounts");
            exit();
        }

        $stmt = $db->prepare("SELECT * FROM mail_accounts WHERE id = ?");
        $stmt->execute([$accountId]);
        $account = $stmt->fetch();

        if ($account) {
            $res = Engine::execute("pirulu-mail", ["account-passwd", $account["account_email"], $newPassword]);
            if (isset($res["status"]) && $res["status"] === "success") {
                View::setFlash("success", "Contraseña de " . $account["account_email"] . " actualizada exitosamente.");
            } else {
                View::setFlash("danger", "Error al actualizar contraseña: " . ($res["message"] ?? "Fallo"));
            }
        }

        header("Location: /mail/domain/" . $domainId . "?tab=accounts");
        exit();
    }

    public function updateQuota(): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $accountId = (int)($_POST["account_id"] ?? 0);
        $quotaMb   = (int)($_POST["quota_mb"] ?? 1024);
        $domainId  = (int)($_POST["domain_id"] ?? 0);

        if ($accountId <= 0 || $quotaMb <= 0) {
            header("Location: /mail/domain/" . $domainId . "?tab=accounts");
            exit();
        }

        $stmt = $db->prepare("SELECT * FROM mail_accounts WHERE id = ?");
        $stmt->execute([$accountId]);
        $account = $stmt->fetch();

        if ($account) {
            $res = Engine::execute("pirulu-mail", ["account-quota", $account["account_email"], (string)$quotaMb]);
            if (isset($res["status"]) && $res["status"] === "success") {
                $stmt = $db->prepare("UPDATE mail_accounts SET quota_mb = ? WHERE id = ?");
                $stmt->execute([$quotaMb, $accountId]);
                View::setFlash("success", "Cuota de " . $account["account_email"] . " actualizada a " . $quotaMb . " MB.");
            } else {
                View::setFlash("danger", "Error al actualizar cuota: " . ($res["message"] ?? "Fallo"));
            }
        }

        header("Location: /mail/domain/" . $domainId . "?tab=accounts");
        exit();
    }

    public function deleteAccount(int $accountId): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT ma.*, md.domain_id FROM mail_accounts ma JOIN mail_domains md ON ma.mail_domain_id = md.id WHERE ma.id = ?");
        $stmt->execute([$accountId]);
        $account = $stmt->fetch();

        if ($account) {
            Engine::execute("pirulu-mail", ["account-del", $account["account_email"], "admin"]);
            $stmt = $db->prepare("DELETE FROM mail_accounts WHERE id = ?");
            $stmt->execute([$accountId]);

            View::setFlash("success", "Cuenta " . $account["account_email"] . " eliminada exitosamente.");
            header("Location: /mail/domain/" . $account["domain_id"] . "?tab=accounts");
            exit();
        }

        header("Location: /mail");
        exit();
    }

    public function createForwarder(int $domainId): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $sourceUser  = trim($_POST["source_user"] ?? "");
        $destination = trim($_POST["destination_email"] ?? "");

        if (empty($sourceUser) || empty($destination) || !filter_var($destination, FILTER_VALIDATE_EMAIL)) {
            View::setFlash("danger", "Por favor ingresa un correo de origen y un correo de destino valido.");
            header("Location: /mail/domain/" . $domainId . "?tab=forwarders");
            exit();
        }

        $sourceUser = preg_replace("/[^a-zA-Z0-9._-]/", "", strtolower($sourceUser));

        $stmt = $db->prepare("SELECT d.domain as domain_name, md.id as mail_domain_id FROM domains d JOIN mail_domains md ON d.id = md.domain_id WHERE d.id = ?");
        $stmt->execute([$domainId]);
        $domain = $stmt->fetch();

        if (!$domain) {
            header("Location: /mail");
            exit();
        }

        $emailDomain = $domain["domain_name"] ?? ($domain["domain"] ?? "");
        $sourceEmail = $sourceUser . "@" . $emailDomain;

        $res = Engine::execute("pirulu-mail", ["forwarder-add", $sourceEmail, $destination]);

        if (isset($res["status"]) && $res["status"] === "success") {
            $stmt = $db->prepare("
                INSERT INTO mail_forwarders (mail_domain_id, source_email, destination_email)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$domain["mail_domain_id"], $sourceEmail, $destination]);

            View::setFlash("success", "Reenvio de " . $sourceEmail . " a " . $destination . " configurado exitosamente.");
        } else {
            View::setFlash("danger", "Error al configurar reenvio: " . ($res["message"] ?? "Fallo"));
        }

        header("Location: /mail/domain/" . $domainId . "?tab=forwarders");
        exit();
    }

    public function deleteForwarder(int $forwarderId): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT mf.*, md.domain_id FROM mail_forwarders mf JOIN mail_domains md ON mf.mail_domain_id = md.id WHERE mf.id = ?");
        $stmt->execute([$forwarderId]);
        $forwarder = $stmt->fetch();

        if ($forwarder) {
            Engine::execute("pirulu-mail", ["forwarder-del", $forwarder["source_email"], $forwarder["destination_email"]]);
            $stmt = $db->prepare("DELETE FROM mail_forwarders WHERE id = ?");
            $stmt->execute([$forwarderId]);

            View::setFlash("success", "Reenvio eliminado exitosamente.");
            header("Location: /mail/domain/" . $forwarder["domain_id"] . "?tab=forwarders");
            exit();
        }

        header("Location: /mail");
        exit();
    }
}
