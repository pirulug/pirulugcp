<?php

namespace Pirulu\Modules\Ftp\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Database;
use Pirulu\Core\View;
use Pirulu\Core\Engine;
use PDO;

class FtpController {
  public function index() {
    Auth::check();
    $db = Database::getConnection();

    $stmt = $db->query("SELECT * FROM domains ORDER BY domain ASC");
    $domains = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $selectedDomainId = isset($_GET["domain_id"]) ? (int)$_GET["domain_id"] : 0;

    if ($selectedDomainId > 0) {
      $stmt = $db->prepare("
        SELECT f.*, d.domain, u.username as sys_user
        FROM ftp_accounts f
        JOIN domains d ON f.domain_id = d.id
        LEFT JOIN users u ON f.user_id = u.id
        WHERE f.domain_id = ?
        ORDER BY f.id DESC
      ");
      $stmt->execute([$selectedDomainId]);
    } else {
      $stmt = $db->query("
        SELECT f.*, d.domain, u.username as sys_user
        FROM ftp_accounts f
        JOIN domains d ON f.domain_id = d.id
        LEFT JOIN users u ON f.user_id = u.id
        ORDER BY f.id DESC
      ");
    }
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $ftpStatusRes = Engine::execute("pirulu-ftp", ["status"]);
    $ftpServiceActive = ($ftpStatusRes["vsftpd"] ?? "inactive") === "active";

    $stmtSettings = $db->query("SELECT server_hostname FROM server_settings WHERE id = 1 LIMIT 1");
    $settings = $stmtSettings->fetch(PDO::FETCH_ASSOC);
    $serverHost = !empty($settings["server_hostname"]) ? $settings["server_hostname"] : gethostname();

    $serverIp = $_SERVER["SERVER_ADDR"] ?? "127.0.0.1";
    if ($serverIp === "127.0.0.1" || $serverIp === "::1") {
      $hostIp = shell_exec("hostname -I 2>/dev/null | awk '{print $1}'");
      if (!empty(trim($hostIp ?? ""))) {
        $serverIp = trim($hostIp);
      }
    }

    View::render("Modules/Ftp/Views/index", [
      "pageTitle"         => "Servidor FTP y Cuentas de Acceso",
      "domains"           => $domains,
      "accounts"          => $accounts,
      "selectedDomainId"  => $selectedDomainId,
      "ftpServiceActive"  => $ftpServiceActive,
      "serverHost"        => $serverHost,
      "serverIp"          => $serverIp
    ], "layout");
  }

  public function store() {
    Auth::check();

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      header("Location: /ftp");
      exit();
    }

    $domainId = (int)($_POST["domain_id"] ?? 0);
    $rawUser  = trim($_POST["ftp_user"] ?? "");
    $password = trim($_POST["ftp_password"] ?? "");
    $ftpPath  = trim($_POST["ftp_path"] ?? "public_html");

    if ($domainId <= 0 || empty($rawUser) || empty($password)) {
      View::setFlash("danger", "Por favor completa todos los campos requeridos.");
      header("Location: /ftp");
      exit();
    }

    $db = Database::getConnection();

    $stmt = $db->prepare("SELECT d.*, u.username as sys_user FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ? LIMIT 1");
    $stmt->execute([$domainId]);
    $domain = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$domain) {
      View::setFlash("danger", "Dominio no encontrado.");
      header("Location: /ftp");
      exit();
    }

    $cleanUser = preg_replace("/[^a-zA-Z0-9_.-]/", "", strtolower($rawUser));
    $domainPrefix = preg_replace("/[^a-zA-Z0-9]/", "", explode(".", $domain["domain"])[0]);

    if (strpos($cleanUser, $domainPrefix . "_") === 0) {
      $fullFtpUser = $cleanUser;
    } else {
      $fullFtpUser = $domainPrefix . "_" . $cleanUser;
    }

    $stmtCheck = $db->prepare("SELECT id FROM ftp_accounts WHERE ftp_user = ? LIMIT 1");
    $stmtCheck->execute([$fullFtpUser]);
    if ($stmtCheck->fetch()) {
      View::setFlash("danger", "La cuenta FTP " . $fullFtpUser . " ya existe.");
      header("Location: /ftp?domain_id=" . $domainId);
      exit();
    }

    $sysUser = !empty($domain["sys_user"]) ? $domain["sys_user"] : "admin";
    $cleanPath = trim($ftpPath, "/ \t\n\r\0\x0B");

    $res = Engine::execute("pirulu-ftp", [
      "account-add",
      $fullFtpUser,
      $password,
      $sysUser,
      $domain["domain"],
      $cleanPath
    ]);

    if (($res["status"] ?? "error") === "success") {
      $stmtInsert = $db->prepare("
        INSERT INTO ftp_accounts (domain_id, user_id, ftp_user, ftp_path, status)
        VALUES (?, ?, ?, ?, 'active')
      ");
      $stmtInsert->execute([
        $domainId,
        $domain["user_id"] ?? 1,
        $fullFtpUser,
        $cleanPath
      ]);

      View::setFlash("success", "Cuenta FTP " . $fullFtpUser . " creada exitosamente.");
    } else {
      View::setFlash("danger", "Error al crear la cuenta FTP: " . ($res["message"] ?? ($res["raw_output"] ?? "Fallo en el servidor")));
    }

    header("Location: /ftp?domain_id=" . $domainId);
    exit();
  }

  public function updatePassword() {
    Auth::check();

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      header("Location: /ftp");
      exit();
    }

    $accountId   = (int)($_POST["account_id"] ?? 0);
    $newPassword = trim($_POST["new_password"] ?? "");

    if ($accountId <= 0 || empty($newPassword)) {
      View::setFlash("danger", "Por favor ingresa la nueva contraseña.");
      header("Location: /ftp");
      exit();
    }

    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM ftp_accounts WHERE id = ? LIMIT 1");
    $stmt->execute([$accountId]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$account) {
      View::setFlash("danger", "Cuenta FTP no encontrada.");
      header("Location: /ftp");
      exit();
    }

    $res = Engine::execute("pirulu-ftp", [
      "account-passwd",
      $account["ftp_user"],
      $newPassword
    ]);

    if (($res["status"] ?? "error") === "success") {
      View::setFlash("success", "Contraseña de la cuenta FTP " . $account["ftp_user"] . " actualizada con exito.");
    } else {
      View::setFlash("danger", "Error al cambiar la contraseña: " . ($res["message"] ?? "Fallo en el servidor"));
    }

    header("Location: /ftp?domain_id=" . $account["domain_id"]);
    exit();
  }

  public function updatePath() {
    Auth::check();

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      header("Location: /ftp");
      exit();
    }

    $accountId = (int)($_POST["account_id"] ?? 0);
    $newPath   = trim($_POST["new_path"] ?? "");

    if ($accountId <= 0) {
      View::setFlash("danger", "Datos de cuenta invalidos.");
      header("Location: /ftp");
      exit();
    }

    $db = Database::getConnection();
    $stmt = $db->prepare("
      SELECT f.*, d.domain, u.username as sys_user
      FROM ftp_accounts f
      JOIN domains d ON f.domain_id = d.id
      LEFT JOIN users u ON f.user_id = u.id
      WHERE f.id = ? LIMIT 1
    ");
    $stmt->execute([$accountId]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$account) {
      View::setFlash("danger", "Cuenta FTP no encontrada.");
      header("Location: /ftp");
      exit();
    }

    $cleanPath = trim($newPath, "/ \t\n\r\0\x0B");
    $sysUser = !empty($account["sys_user"]) ? $account["sys_user"] : "admin";

    $res = Engine::execute("pirulu-ftp", [
      "account-path",
      $account["ftp_user"],
      $sysUser,
      $account["domain"],
      $cleanPath
    ]);

    if (($res["status"] ?? "error") === "success") {
      $stmtUp = $db->prepare("UPDATE ftp_accounts SET ftp_path = ? WHERE id = ?");
      $stmtUp->execute([$cleanPath, $accountId]);
      View::setFlash("success", "Ruta de acceso para " . $account["ftp_user"] . " actualizada exitosamente.");
    } else {
      View::setFlash("danger", "Error al actualizar la ruta: " . ($res["message"] ?? "Fallo en el servidor"));
    }

    header("Location: /ftp?domain_id=" . $account["domain_id"]);
    exit();
  }

  public function delete($id) {
    Auth::check();
    $accountId = (int)$id;

    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM ftp_accounts WHERE id = ? LIMIT 1");
    $stmt->execute([$accountId]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($account) {
      Engine::execute("pirulu-ftp", [
        "account-del",
        $account["ftp_user"]
      ]);

      $stmtDel = $db->prepare("DELETE FROM ftp_accounts WHERE id = ?");
      $stmtDel->execute([$accountId]);

      View::setFlash("success", "Cuenta FTP " . $account["ftp_user"] . " eliminada exitosamente.");
      header("Location: /ftp?domain_id=" . $account["domain_id"]);
      exit();
    }

    View::setFlash("danger", "Cuenta FTP no encontrada.");
    header("Location: /ftp");
    exit();
  }
}
