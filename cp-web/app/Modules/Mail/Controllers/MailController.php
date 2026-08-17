<?php

namespace Pirulu\Modules\Mail\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Database;
use Pirulu\Core\Engine;
use Pirulu\Core\View;
use PDO;

/**
 * Controlador de Gestion de Servidor de Correo y Webmail.
 * Administra dominios, cuentas IMAP/POP3, reenvios y certificados SSL para Webmail.
 */
class MailController {

  /**
   * Muestra la lista de dominios y el estado del servidor de correo.
   *
   * @return void
   */
  public function index() {
    Auth::requireAuth();
    $connect = Database::getConnection();

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
        md.ssl_enabled,
        md.ssl_force_https,
        md.created_at as mail_created,
        (SELECT COUNT(*) FROM mail_accounts ma WHERE ma.mail_domain_id = md.id) as total_accounts,
        (SELECT COUNT(*) FROM mail_forwarders mf WHERE mf.mail_domain_id = md.id) as total_forwarders
      FROM domains d
      LEFT JOIN mail_domains md ON d.id = md.domain_id
      ORDER BY d.domain ASC
    ";
    $stmt = $connect->prepare($query);
    $stmt->execute();
    $domains = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $mailServiceStatus = Engine::execute("pirulu-mail", ["status"]);

    View::render("Modules/Mail/Views/index", [
      "pageTitle"         => "Servidor de Correo y Webmail - PiruluGCP",
      "domains"           => $domains,
      "mailServiceStatus" => $mailServiceStatus
    ]);
  }

  /**
   * Habilita el servicio de correo y crea el VirtualHost de Webmail para un dominio.
   *
   * @param int $domainId ID del dominio web.
   * @return void
   */
  public function enable($domainId) {
    Auth::requireAuth();
    $connect = Database::getConnection();

    $query = "SELECT * FROM domains WHERE id = :id LIMIT 1";
    $stmt = $connect->prepare($query);
    $stmt->bindParam(":id", $domainId);
    $stmt->execute();
    $domain = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$domain) {
      View::setFlash("danger", "Dominio web no encontrado.");
      header("Location: /mail");
      exit();
    }

    $domainName = $domain["domain"] ?? ($domain["domain_name"] ?? "");

    // Comprobar si ya esta habilitado
    $queryCheck = "SELECT id FROM mail_domains WHERE domain_id = :domain_id LIMIT 1";
    $stmtCheck = $connect->prepare($queryCheck);
    $stmtCheck->bindParam(":domain_id", $domainId);
    $stmtCheck->execute();
    if ($stmtCheck->fetch(PDO::FETCH_ASSOC)) {
      header("Location: /mail/domain/" . $domainId);
      exit();
    }

    $res = Engine::execute("pirulu-mail", ["domain-add", $domainName, "admin"]);

    if (isset($res["status"]) && $res["status"] === "success") {
      $queryInsert = "
        INSERT INTO mail_domains (domain_id, domain_name, dkim_selector, dkim_record, spf_record, ssl_enabled, ssl_force_https, status)
        VALUES (:domain_id, :domain_name, :dkim_selector, :dkim_record, :spf_record, 0, 0, 'active')
      ";
      $stmtInsert = $connect->prepare($queryInsert);
      $stmtInsert->bindParam(":domain_id", $domainId);
      $stmtInsert->bindParam(":domain_name", $domainName);
      $dkimSel = $res["dkim_selector"] ?? "default";
      $dkimRec = $res["dkim_record"] ?? "";
      $spfRec = $res["spf_record"] ?? "";
      $stmtInsert->bindParam(":dkim_selector", $dkimSel);
      $stmtInsert->bindParam(":dkim_record", $dkimRec);
      $stmtInsert->bindParam(":spf_record", $spfRec);
      $stmtInsert->execute();

      View::setFlash("success", "Servicio de correo y Webmail habilitados para " . $domainName . " exitosamente.");
      header("Location: /mail/domain/" . $domainId);
      exit();
    } else {
      View::setFlash("danger", "Error al habilitar servicio de correo: " . ($res["message"] ?? "Fallo"));
      header("Location: /mail");
      exit();
    }
  }

  /**
   * Deshabilita el servicio de correo y elimina configuraciones de un dominio.
   *
   * @param int $domainId ID del dominio.
   * @return void
   */
  public function disable($domainId) {
    Auth::requireAuth();
    $connect = Database::getConnection();

    $query = "SELECT * FROM domains WHERE id = :id LIMIT 1";
    $stmt = $connect->prepare($query);
    $stmt->bindParam(":id", $domainId);
    $stmt->execute();
    $domain = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($domain) {
      $domainName = $domain["domain"] ?? ($domain["domain_name"] ?? "");
      Engine::execute("pirulu-mail", ["domain-del", $domainName]);

      $queryDel = "DELETE FROM mail_domains WHERE domain_id = :domain_id";
      $stmtDel = $connect->prepare($queryDel);
      $stmtDel->bindParam(":domain_id", $domainId);
      $stmtDel->execute();

      View::setFlash("info", "Servicio de correo deshabilitado para " . $domainName . ".");
    }

    header("Location: /mail");
    exit();
  }

  /**
   * Muestra la vista detallada de administracion de correo de un dominio.
   *
   * @param int $domainId ID del dominio.
   * @return void
   */
  public function domain($domainId) {
    Auth::requireAuth();
    $connect = Database::getConnection();

    $query = "
      SELECT d.id, d.domain, d.domain as domain_name, d.php_version, 
             md.id as mail_domain_id, md.dkim_selector, md.dkim_record, md.spf_record, 
             md.ssl_enabled, md.ssl_force_https, md.status as mail_status
      FROM domains d
      JOIN mail_domains md ON d.id = md.domain_id
      WHERE d.id = :id
      LIMIT 1
    ";
    $stmt = $connect->prepare($query);
    $stmt->bindParam(":id", $domainId);
    $stmt->execute();
    $mailDomain = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mailDomain) {
      View::setFlash("warning", "El servicio de correo no esta habilitado para este dominio.");
      header("Location: /mail");
      exit();
    }

    $domainName = $mailDomain["domain"] ?? ($mailDomain["domain_name"] ?? "");

    // Cuentas de correo del dominio
    $queryAccounts = "SELECT * FROM mail_accounts WHERE mail_domain_id = :mail_domain_id ORDER BY account_user ASC";
    $stmtAccounts = $connect->prepare($queryAccounts);
    $stmtAccounts->bindParam(":mail_domain_id", $mailDomain["mail_domain_id"]);
    $stmtAccounts->execute();
    $accounts = $stmtAccounts->fetchAll(PDO::FETCH_ASSOC);

    // Reenvios del dominio
    $queryForwarders = "SELECT * FROM mail_forwarders WHERE mail_domain_id = :mail_domain_id ORDER BY source_email ASC";
    $stmtForwarders = $connect->prepare($queryForwarders);
    $stmtForwarders->bindParam(":mail_domain_id", $mailDomain["mail_domain_id"]);
    $stmtForwarders->execute();
    $forwarders = $stmtForwarders->fetchAll(PDO::FETCH_ASSOC);

    // Obtener registros DNS actualizados
    $dnsInfo = Engine::execute("pirulu-mail", ["dkim-get", $domainName]);

    // Consultar estado detallado del certificado SSL para webmail
    $webmailHost = "webmail." . $domainName;
    $webmailSslInfo = Engine::execute("pirulu-ssl", ["details", $webmailHost]);

    $activeTab = $_GET["tab"] ?? "accounts";

    View::render("Modules/Mail/Views/domain", [
      "pageTitle"      => "Gestion de Correo - " . $domainName,
      "domain"         => $mailDomain,
      "accounts"       => $accounts,
      "forwarders"     => $forwarders,
      "dnsInfo"        => $dnsInfo,
      "webmailSslInfo" => $webmailSslInfo,
      "activeTab"      => $activeTab
    ]);
  }

  /**
   * Emite e instala un certificado SSL Let's Encrypt para el subdominio webmail.DOMAIN.
   *
   * @param int $domainId ID del dominio.
   * @return void
   */
  public function issueWebmailSsl($domainId) {
    Auth::requireAuth();
    $connect = Database::getConnection();

    $query = "SELECT d.domain as domain_name, md.id as mail_domain_id, md.ssl_force_https FROM domains d JOIN mail_domains md ON d.id = md.domain_id WHERE d.id = :id LIMIT 1";
    $stmt = $connect->prepare($query);
    $stmt->bindParam(":id", $domainId);
    $stmt->execute();
    $domain = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$domain) {
      View::setFlash("danger", "Dominio no valido.");
      header("Location: /mail");
      exit();
    }

    $domainName = $domain["domain_name"];
    $email = trim($_POST["ssl_email"] ?? "");
    $forceHttps = isset($_POST["ssl_force_https"]) ? "1" : (string)($domain["ssl_force_https"] ?? "0");

    $res = Engine::execute("pirulu-mail", ["webmail-ssl-issue", $domainName, $email, $forceHttps]);

    if (isset($res["status"]) && $res["status"] === "success") {
      $forceHttpsInt = ($forceHttps === "1") ? 1 : 0;
      $queryUpdate = "UPDATE mail_domains SET ssl_enabled = 1, ssl_force_https = :force_https WHERE id = :id";
      $stmtUpdate = $connect->prepare($queryUpdate);
      $stmtUpdate->bindParam(":force_https", $forceHttpsInt);
      $stmtUpdate->bindParam(":id", $domain["mail_domain_id"]);
      $stmtUpdate->execute();

      View::setFlash("success", "Certificado SSL Let's Encrypt para webmail." . $domainName . " expedido e instalado exitosamente.");
    } else {
      $errorMsg = $res["message"] ?? "Fallo al generar el certificado Let's Encrypt para Webmail.";
      View::setFlash("danger", $errorMsg);
    }

    header("Location: /mail/domain/" . $domainId . "?tab=webmail");
    exit();
  }

  /**
   * Elimina el certificado SSL Let's Encrypt del subdominio webmail.DOMAIN.
   *
   * @param int $domainId ID del dominio.
   * @return void
   */
  public function deleteWebmailSsl($domainId) {
    Auth::requireAuth();
    $connect = Database::getConnection();

    $query = "SELECT d.domain as domain_name, md.id as mail_domain_id FROM domains d JOIN mail_domains md ON d.id = md.domain_id WHERE d.id = :id LIMIT 1";
    $stmt = $connect->prepare($query);
    $stmt->bindParam(":id", $domainId);
    $stmt->execute();
    $domain = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($domain) {
      $domainName = $domain["domain_name"];
      Engine::execute("pirulu-mail", ["webmail-ssl-delete", $domainName]);

      $queryUpdate = "UPDATE mail_domains SET ssl_enabled = 0, ssl_force_https = 0 WHERE id = :id";
      $stmtUpdate = $connect->prepare($queryUpdate);
      $stmtUpdate->bindParam(":id", $domain["mail_domain_id"]);
      $stmtUpdate->execute();

      View::setFlash("info", "Certificado SSL de webmail." . $domainName . " removido exitosamente.");
    }

    header("Location: /mail/domain/" . $domainId . "?tab=webmail");
    exit();
  }

  /**
   * Alterna la redireccion forzada HTTPS para webmail.DOMAIN.
   *
   * @param int $domainId ID del dominio.
   * @return void
   */
  public function toggleWebmailForceHttps($domainId) {
    Auth::requireAuth();
    $connect = Database::getConnection();

    $query = "SELECT d.domain as domain_name, md.id as mail_domain_id, md.ssl_force_https FROM domains d JOIN mail_domains md ON d.id = md.domain_id WHERE d.id = :id LIMIT 1";
    $stmt = $connect->prepare($query);
    $stmt->bindParam(":id", $domainId);
    $stmt->execute();
    $domain = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($domain) {
      $domainName = $domain["domain_name"];
      $newVal = ((int)($domain["ssl_force_https"] ?? 0) === 1) ? 0 : 1;

      Engine::execute("pirulu-mail", ["webmail-vhost", $domainName, (string)$newVal]);

      $queryUpdate = "UPDATE mail_domains SET ssl_force_https = :force_https WHERE id = :id";
      $stmtUpdate = $connect->prepare($queryUpdate);
      $stmtUpdate->bindParam(":force_https", $newVal);
      $stmtUpdate->bindParam(":id", $domain["mail_domain_id"]);
      $stmtUpdate->execute();

      View::setFlash("success", "Redireccion HTTPS para webmail." . $domainName . " actualizada correctamente.");
    }

    header("Location: /mail/domain/" . $domainId . "?tab=webmail");
    exit();
  }

  /**
   * Crea una nueva cuenta de correo IMAP/POP3.
   *
   * @param int $domainId ID del dominio.
   * @return void
   */
  public function createAccount($domainId) {
    Auth::requireAuth();
    $connect = Database::getConnection();

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

    $query = "SELECT d.domain as domain_name, md.id as mail_domain_id FROM domains d JOIN mail_domains md ON d.id = md.domain_id WHERE d.id = :id LIMIT 1";
    $stmt = $connect->prepare($query);
    $stmt->bindParam(":id", $domainId);
    $stmt->execute();
    $domain = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$domain) {
      View::setFlash("danger", "Dominio no valido.");
      header("Location: /mail");
      exit();
    }

    $email = $user . "@" . $domain["domain_name"];

    // Verificar duplicidad
    $queryCheck = "SELECT id FROM mail_accounts WHERE account_email = :email LIMIT 1";
    $stmtCheck = $connect->prepare($queryCheck);
    $stmtCheck->bindParam(":email", $email);
    $stmtCheck->execute();
    if ($stmtCheck->fetch(PDO::FETCH_ASSOC)) {
      View::setFlash("danger", "La cuenta de correo " . $email . " ya existe.");
      header("Location: /mail/domain/" . $domainId . "?tab=accounts");
      exit();
    }

    $res = Engine::execute("pirulu-mail", ["account-add", $email, $password, "admin", (string)$quotaMb]);

    if (isset($res["status"]) && $res["status"] === "success") {
      $queryInsert = "
        INSERT INTO mail_accounts (mail_domain_id, account_user, account_email, quota_mb, status)
        VALUES (:mail_domain_id, :account_user, :account_email, :quota_mb, 'active')
      ";
      $stmtInsert = $connect->prepare($queryInsert);
      $stmtInsert->bindParam(":mail_domain_id", $domain["mail_domain_id"]);
      $stmtInsert->bindParam(":account_user", $user);
      $stmtInsert->bindParam(":account_email", $email);
      $stmtInsert->bindParam(":quota_mb", $quotaMb);
      $stmtInsert->execute();

      View::setFlash("success", "Cuenta de correo " . $email . " creada exitosamente.");
    } else {
      View::setFlash("danger", "Error al crear cuenta: " . ($res["message"] ?? "Fallo"));
    }

    header("Location: /mail/domain/" . $domainId . "?tab=accounts");
    exit();
  }

  /**
   * Actualiza la contraseña de una cuenta de correo.
   *
   * @return void
   */
  public function updatePassword() {
    Auth::requireAuth();
    $connect = Database::getConnection();

    $accountId   = (int)($_POST["account_id"] ?? 0);
    $newPassword = trim($_POST["new_password"] ?? "");
    $domainId    = (int)($_POST["domain_id"] ?? 0);

    if (empty($newPassword) || $accountId <= 0) {
      View::setFlash("danger", "Por favor ingresa una contraseña valida.");
      header("Location: /mail/domain/" . $domainId . "?tab=accounts");
      exit();
    }

    $query = "SELECT * FROM mail_accounts WHERE id = :id LIMIT 1";
    $stmt = $connect->prepare($query);
    $stmt->bindParam(":id", $accountId);
    $stmt->execute();
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

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

  /**
   * Actualiza la cuota de almacenamiento de una cuenta de correo.
   *
   * @return void
   */
  public function updateQuota() {
    Auth::requireAuth();
    $connect = Database::getConnection();

    $accountId = (int)($_POST["account_id"] ?? 0);
    $quotaMb   = (int)($_POST["quota_mb"] ?? 1024);
    $domainId  = (int)($_POST["domain_id"] ?? 0);

    if ($accountId <= 0 || $quotaMb <= 0) {
      header("Location: /mail/domain/" . $domainId . "?tab=accounts");
      exit();
    }

    $query = "SELECT * FROM mail_accounts WHERE id = :id LIMIT 1";
    $stmt = $connect->prepare($query);
    $stmt->bindParam(":id", $accountId);
    $stmt->execute();
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($account) {
      $res = Engine::execute("pirulu-mail", ["account-quota", $account["account_email"], (string)$quotaMb]);
      if (isset($res["status"]) && $res["status"] === "success") {
        $queryUpdate = "UPDATE mail_accounts SET quota_mb = :quota_mb WHERE id = :id";
        $stmtUpdate = $connect->prepare($queryUpdate);
        $stmtUpdate->bindParam(":quota_mb", $quotaMb);
        $stmtUpdate->bindParam(":id", $accountId);
        $stmtUpdate->execute();
        View::setFlash("success", "Cuota de " . $account["account_email"] . " actualizada a " . $quotaMb . " MB.");
      } else {
        View::setFlash("danger", "Error al actualizar cuota: " . ($res["message"] ?? "Fallo"));
      }
    }

    header("Location: /mail/domain/" . $domainId . "?tab=accounts");
    exit();
  }

  /**
   * Elimina una cuenta de correo del servidor.
   *
   * @param int $accountId ID de la cuenta.
   * @return void
   */
  public function deleteAccount($accountId) {
    Auth::requireAuth();
    $connect = Database::getConnection();

    $query = "SELECT ma.*, md.domain_id FROM mail_accounts ma JOIN mail_domains md ON ma.mail_domain_id = md.id WHERE ma.id = :id LIMIT 1";
    $stmt = $connect->prepare($query);
    $stmt->bindParam(":id", $accountId);
    $stmt->execute();
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($account) {
      Engine::execute("pirulu-mail", ["account-del", $account["account_email"], "admin"]);
      $queryDel = "DELETE FROM mail_accounts WHERE id = :id";
      $stmtDel = $connect->prepare($queryDel);
      $stmtDel->bindParam(":id", $accountId);
      $stmtDel->execute();

      View::setFlash("success", "Cuenta " . $account["account_email"] . " eliminada exitosamente.");
      header("Location: /mail/domain/" . $account["domain_id"] . "?tab=accounts");
      exit();
    }

    header("Location: /mail");
    exit();
  }

  /**
   * Crea un nuevo reenvio de correo.
   *
   * @param int $domainId ID del dominio.
   * @return void
   */
  public function createForwarder($domainId) {
    Auth::requireAuth();
    $connect = Database::getConnection();

    $sourceUser  = trim($_POST["source_user"] ?? "");
    $destination = trim($_POST["destination_email"] ?? "");

    if (empty($sourceUser) || empty($destination) || !filter_var($destination, FILTER_VALIDATE_EMAIL)) {
      View::setFlash("danger", "Por favor ingresa un correo de origen y un correo de destino valido.");
      header("Location: /mail/domain/" . $domainId . "?tab=forwarders");
      exit();
    }

    $sourceUser = preg_replace("/[^a-zA-Z0-9._-]/", "", strtolower($sourceUser));

    $query = "SELECT d.domain as domain_name, md.id as mail_domain_id FROM domains d JOIN mail_domains md ON d.id = md.domain_id WHERE d.id = :id LIMIT 1";
    $stmt = $connect->prepare($query);
    $stmt->bindParam(":id", $domainId);
    $stmt->execute();
    $domain = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$domain) {
      header("Location: /mail");
      exit();
    }

    $emailDomain = $domain["domain_name"] ?? ($domain["domain"] ?? "");
    $sourceEmail = $sourceUser . "@" . $emailDomain;

    $res = Engine::execute("pirulu-mail", ["forwarder-add", $sourceEmail, $destination]);

    if (isset($res["status"]) && $res["status"] === "success") {
      $queryInsert = "
        INSERT INTO mail_forwarders (mail_domain_id, source_email, destination_email)
        VALUES (:mail_domain_id, :source_email, :destination_email)
      ";
      $stmtInsert = $connect->prepare($queryInsert);
      $stmtInsert->bindParam(":mail_domain_id", $domain["mail_domain_id"]);
      $stmtInsert->bindParam(":source_email", $sourceEmail);
      $stmtInsert->bindParam(":destination_email", $destination);
      $stmtInsert->execute();

      View::setFlash("success", "Reenvio de " . $sourceEmail . " a " . $destination . " configurado exitosamente.");
    } else {
      View::setFlash("danger", "Error al configurar reenvio: " . ($res["message"] ?? "Fallo"));
    }

    header("Location: /mail/domain/" . $domainId . "?tab=forwarders");
    exit();
  }

  /**
   * Elimina un reenvio de correo.
   *
   * @param int $forwarderId ID del reenvio.
   * @return void
   */
  public function deleteForwarder($forwarderId) {
    Auth::requireAuth();
    $connect = Database::getConnection();

    $query = "SELECT mf.*, md.domain_id FROM mail_forwarders mf JOIN mail_domains md ON mf.mail_domain_id = md.id WHERE mf.id = :id LIMIT 1";
    $stmt = $connect->prepare($query);
    $stmt->bindParam(":id", $forwarderId);
    $stmt->execute();
    $forwarder = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($forwarder) {
      Engine::execute("pirulu-mail", ["forwarder-del", $forwarder["source_email"], $forwarder["destination_email"]]);
      $queryDel = "DELETE FROM mail_forwarders WHERE id = :id";
      $stmtDel = $connect->prepare($queryDel);
      $stmtDel->bindParam(":id", $forwarderId);
      $stmtDel->execute();

      View::setFlash("success", "Reenvio eliminado exitosamente.");
      header("Location: /mail/domain/" . $forwarder["domain_id"] . "?tab=forwarders");
      exit();
    }

    header("Location: /mail");
    exit();
  }
}
