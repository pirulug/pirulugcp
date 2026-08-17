<?php

namespace Pirulu\Modules\Server\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Database;
use Pirulu\Core\Engine;
use Pirulu\Core\View;

class ServerController {
  private static $timezones = [
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

  /**
   * Muestra la vista principal de configuracion del servidor y panel.
   *
   * @return void
   */
  public function index() {
    Auth::requireAuth();
    $db = Database::getConnection();

    $stmt = $db->query("SELECT * FROM server_settings WHERE id = 1");
    $settings = $stmt->fetch();
    if (!$settings) {
      $settings = [];
    }

    $server_config = Engine::execute("pirulu-server", ["get-config"]);

    // Asegurar token de webhook si no existe
    if (empty($settings["panel_webhook_token"])) {
      $token = bin2hex(random_bytes(16));
      $stmt = $db->prepare("UPDATE server_settings SET panel_webhook_token = ? WHERE id = 1");
      $stmt->execute([$token]);
      $settings["panel_webhook_token"] = $token;
    }

    // Construir URL base para el webhook de actualizaciones
    $host = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "localhost:8083";
    $scheme = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on") ? "https" : "http";
    $webhook_url = $scheme . "://" . $host . "/api/server/webhook/" . (isset($settings["panel_webhook_token"]) ? $settings["panel_webhook_token"] : "");

    $panel_domain = !empty($settings["panel_domain"]) ? $settings["panel_domain"] : (isset($server_config["panel_domain"]) ? $server_config["panel_domain"] : "");
    $panel_ssl_info = null;

    if (!empty($panel_domain)) {
      $panel_ssl_info = Engine::execute("pirulu-ssl", ["details", $panel_domain]);
    }

    $active_tab = isset($_GET["tab"]) ? $_GET["tab"] : "identity";

    View::render("Modules/Server/Views/index", [
      "pageTitle"    => "Configuracion del Servidor - PiruluGCP",
      "settings"     => $settings,
      "serverConfig" => $server_config,
      "panelSslInfo" => $panel_ssl_info,
      "timezones"    => self::$timezones,
      "webhookUrl"   => $webhook_url,
      "activeTab"    => $active_tab
    ]);
  }

  /**
   * Configura el nombre de host del sistema en Linux (/etc/hostname).
   *
   * @return void
   */
  public function setHostname() {
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

      View::setFlash("success", "Nombre del servidor actualizado a " . $hostname . " exitosamente.");
    } else {
      $err_msg = isset($res["message"]) ? $res["message"] : "Fallo";
      View::setFlash("danger", "Error al configurar el nombre del servidor: " . $err_msg);
    }

    header("Location: /server?tab=identity");
    exit();
  }

  /**
   * Configura el subdominio o dominio de acceso al panel en el servidor web Nginx.
   *
   * @return void
   */
  public function setPanelDomain() {
    Auth::requireAuth();
    $db = Database::getConnection();

    $stmt = $db->query("SELECT * FROM server_settings WHERE id = 1");
    $settings = $stmt->fetch();

    $panel_domain = trim($_POST["panel_domain"] ?? "");
    $force_https = isset($_POST["panel_ssl_force_https"]) ? 1 : (int)($settings["panel_ssl_force_https"] ?? 0);

    $res = Engine::execute("pirulu-server", ["set-panel-domain", $panel_domain, (string)$force_https]);

    if (isset($res["status"]) && $res["status"] === "success") {
      $stmt = $db->prepare("UPDATE server_settings SET panel_domain = ?, panel_ssl_force_https = ?, updated_at = datetime('now') WHERE id = 1");
      $stmt->execute([$panel_domain, $force_https]);

      View::setFlash("success", "Dominio de acceso al panel configurado correctamente a " . (!empty($panel_domain) ? $panel_domain : "por defecto") . ".");
    } else {
      $err_msg = isset($res["message"]) ? $res["message"] : "Fallo";
      View::setFlash("danger", "Error al configurar dominio del panel: " . $err_msg);
    }

    header("Location: /server?tab=identity");
    exit();
  }

  /**
   * Emite e instala un certificado SSL Let's Encrypt para el subdominio del panel.
   *
   * @return void
   */
  public function issueSsl() {
    Auth::requireAuth();
    $db = Database::getConnection();

    $stmt = $db->query("SELECT * FROM server_settings WHERE id = 1");
    $settings = $stmt->fetch();

    $panel_domain = trim($_POST["panel_domain"] ?? "");
    if (empty($panel_domain) && !empty($settings["panel_domain"])) {
      $panel_domain = $settings["panel_domain"];
    }

    if (empty($panel_domain)) {
      View::setFlash("danger", "Debes configurar un subdominio o dominio para el panel antes de emitir el certificado SSL.");
      header("Location: /server?tab=identity");
      exit();
    }

    $email = trim($_POST["panel_ssl_email"] ?? "");
    if (empty($email) && !empty($settings["panel_ssl_email"])) {
      $email = $settings["panel_ssl_email"];
    }

    $force_https = isset($_POST["panel_ssl_force_https"]) ? 1 : (int)($settings["panel_ssl_force_https"] ?? 0);

    $res = Engine::execute("pirulu-server", ["issue-panel-ssl", $panel_domain, $email, (string)$force_https]);

    if (isset($res["status"]) && $res["status"] === "success") {
      $stmt = $db->prepare("
        UPDATE server_settings SET
          panel_domain = ?,
          panel_ssl_enabled = 1,
          panel_ssl_force_https = ?,
          panel_ssl_email = ?,
          updated_at = datetime('now')
        WHERE id = 1
      ");
      $stmt->execute([$panel_domain, $force_https, $email]);

      View::setFlash("success", "Certificado SSL Let's Encrypt instalado y activado exitosamente para " . $panel_domain . ".");
    } else {
      $err_msg = isset($res["message"]) ? $res["message"] : "No se pudo emitir el certificado Let's Encrypt.";
      if (!empty($res["log"])) {
        $err_msg .= " Detalle: " . $res["log"];
      }
      View::setFlash("danger", "Error al emitir certificado SSL Let's Encrypt: " . $err_msg);
    }

    header("Location: /server?tab=identity");
    exit();
  }

  /**
   * Elimina el certificado SSL Let's Encrypt del subdominio del panel y revierte a HTTP.
   *
   * @return void
   */
  public function deleteSsl() {
    Auth::requireAuth();
    $db = Database::getConnection();

    $stmt = $db->query("SELECT * FROM server_settings WHERE id = 1");
    $settings = $stmt->fetch();

    $panel_domain = isset($settings["panel_domain"]) ? $settings["panel_domain"] : "";
    if (empty($panel_domain)) {
      View::setFlash("danger", "No hay ningun subdominio de panel configurado.");
      header("Location: /server?tab=identity");
      exit();
    }

    $res = Engine::execute("pirulu-server", ["delete-panel-ssl", $panel_domain]);

    if (isset($res["status"]) && $res["status"] === "success") {
      $stmt = $db->prepare("
        UPDATE server_settings SET
          panel_ssl_enabled = 0,
          panel_ssl_force_https = 0,
          updated_at = datetime('now')
        WHERE id = 1
      ");
      $stmt->execute();

      View::setFlash("info", "Certificado SSL Let's Encrypt removido exitosamente para " . $panel_domain . ".");
    } else {
      $err_msg = isset($res["message"]) ? $res["message"] : "Fallo";
      View::setFlash("danger", "Error al remover certificado SSL: " . $err_msg);
    }

    header("Location: /server?tab=identity");
    exit();
  }

  /**
   * Alterna la redireccion forzada de HTTP a HTTPS para el subdominio del panel.
   *
   * @return void
   */
  public function toggleForceHttps() {
    Auth::requireAuth();
    $db = Database::getConnection();

    $stmt = $db->query("SELECT * FROM server_settings WHERE id = 1");
    $settings = $stmt->fetch();

    $panel_domain = isset($settings["panel_domain"]) ? $settings["panel_domain"] : "";
    if (empty($panel_domain)) {
      View::setFlash("danger", "Debes configurar un subdominio de acceso antes de habilitar la redireccion HTTPS.");
      header("Location: /server?tab=identity");
      exit();
    }

    $current_force = !empty($settings["panel_ssl_force_https"]) ? 1 : 0;
    $new_force = isset($_POST["force_https"]) ? (int)$_POST["force_https"] : ($current_force === 1 ? 0 : 1);

    $res = Engine::execute("pirulu-server", ["set-panel-domain", $panel_domain, (string)$new_force]);

    if (isset($res["status"]) && $res["status"] === "success") {
      $stmt = $db->prepare("
        UPDATE server_settings SET
          panel_ssl_force_https = ?,
          updated_at = datetime('now')
        WHERE id = 1
      ");
      $stmt->execute([$new_force]);

      $status_text = $new_force === 1 ? "habilitada" : "deshabilitada";
      View::setFlash("success", "Redireccion forzada a HTTPS " . $status_text . " para " . $panel_domain . ".");
    } else {
      $err_msg = isset($res["message"]) ? $res["message"] : "Fallo";
      View::setFlash("danger", "Error al actualizar redireccion HTTPS: " . $err_msg);
    }

    header("Location: /server?tab=identity");
    exit();
  }

  /**
   * Configura la zona horaria del servidor Linux y de PHP.
   *
   * @return void
   */
  public function setTimezone() {
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

      $current_time = isset($res["current_time"]) ? $res["current_time"] : "";
      View::setFlash("success", "Zona horaria actualizada a " . $timezone . " (Hora actual: " . $current_time . ").");
    } else {
      $err_msg = isset($res["message"]) ? $res["message"] : "Fallo";
      View::setFlash("danger", "Error al configurar zona horaria: " . $err_msg);
    }

    header("Location: /server?tab=timezone");
    exit();
  }

  /**
   * Genera una nueva clave SSH Ed25519 para actualizaciones del panel.
   *
   * @return void
   */
  public function generateKey() {
    Auth::requireAuth();

    $res = Engine::execute("pirulu-server", ["generate-key"]);

    if (isset($res["status"]) && $res["status"] === "success") {
      View::setFlash("success", "Nueva clave SSH de actualizacion generada exitosamente. Recuerda agregarla a GitHub.");
    } else {
      $err_msg = isset($res["message"]) ? $res["message"] : "Fallo";
      View::setFlash("danger", "Error al generar clave SSH: " . $err_msg);
    }

    header("Location: /server?tab=updates");
    exit();
  }

  /**
   * Vincula el panel PiruluGCP a un repositorio remoto de Git.
   *
   * @return void
   */
  public function connectGit() {
    Auth::requireAuth();
    $db = Database::getConnection();

    $repo_url   = trim($_POST["panel_git_repo"] ?? "");
    $branch     = trim($_POST["panel_git_branch"] ?? "main");
    $is_private = isset($_POST["panel_git_is_private"]) ? 1 : 0;
    $auto_update = isset($_POST["panel_auto_update"]) ? 1 : 0;

    if (empty($branch)) {
      $branch = "main";
    }

    if (empty($repo_url)) {
      View::setFlash("danger", "Por favor ingresa la URL del repositorio de GitHub.");
      header("Location: /server?tab=updates");
      exit();
    }

    // Si el repositorio es privado y se introdujo una URL HTTPS, convertir a formato SSH
    if ($is_private) {
      if (preg_match("#^https?://github\.com/([^/]+)/([^/]+?)(?:\.git)?$#i", $repo_url, $m)) {
        $repo_url = "git@github.com:" . $m[1] . "/" . $m[2] . ".git";
      } elseif (preg_match("#^https?://gitlab\.com/([^/]+)/([^/]+?)(?:\.git)?$#i", $repo_url, $m)) {
        $repo_url = "git@gitlab.com:" . $m[1] . "/" . $m[2] . ".git";
      } elseif (preg_match("#^https?://bitbucket\.org/([^/]+)/([^/]+?)(?:\.git)?$#i", $repo_url, $m)) {
        $repo_url = "git@bitbucket.org:" . $m[1] . "/" . $m[2] . ".git";
      }
    }

    $res = Engine::execute("pirulu-server", ["connect-git", $repo_url, $branch, (string)$is_private]);

    if (isset($res["status"]) && $res["status"] === "success") {
      $detected_branch = isset($res["branch"]) ? $res["branch"] : $branch;

      $stmt = $db->prepare("
        UPDATE server_settings SET
          panel_git_repo = ?,
          panel_git_branch = ?,
          panel_git_is_private = ?,
          panel_auto_update = ?,
          updated_at = datetime('now')
        WHERE id = 1
      ");
      $stmt->execute([$repo_url, $detected_branch, $is_private, $auto_update]);

      View::setFlash("success", "Repositorio de actualizaciones vinculado con exito (Rama: " . $detected_branch . ").");
    } else {
      $err_msg = isset($res["message"]) ? $res["message"] : "Fallo";
      View::setFlash("danger", "Error al conectar repositorio: " . $err_msg);
    }

    header("Location: /server?tab=updates");
    exit();
  }

  /**
   * Ejecuta la descarga y aplicacion de actualizaciones desde GitHub.
   *
   * @return void
   */
  public function updatePanel() {
    Auth::requireAuth();
    $db = Database::getConnection();

    $stmt = $db->query("SELECT * FROM server_settings WHERE id = 1");
    $settings = $stmt->fetch();
    if (!$settings) {
      $settings = [];
    }

    $branch = isset($settings["panel_git_branch"]) ? $settings["panel_git_branch"] : "main";

    $res = Engine::execute("pirulu-server", ["update-panel", $branch]);

    if (isset($res["status"]) && $res["status"] === "success") {
      $log = isset($res["log"]) ? $res["log"] : "Panel actualizado correctamente.";
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
      $log = isset($res["log"]) ? $res["log"] : (isset($res["message"]) ? $res["message"] : "Error al actualizar");
      $stmt = $db->prepare("
        UPDATE server_settings SET
          panel_last_update_at = datetime('now'),
          panel_last_update_status = 'error',
          panel_last_update_log = ?
        WHERE id = 1
      ");
      $stmt->execute([$log]);

      $err_msg = isset($res["message"]) ? $res["message"] : "Error";
      View::setFlash("danger", "Error al actualizar el panel: " . $err_msg);
    }

    header("Location: /server?tab=updates");
    exit();
  }

  /**
   * Procesa peticiones de webhook para auto-actualizacion del panel desde GitHub.
   *
   * @param string $token Token secreto del webhook.
   * @return void
   */
  public function webhook($token) {
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
        "repo"    => isset($settings["panel_git_repo"]) ? $settings["panel_git_repo"] : "",
        "branch"  => isset($settings["panel_git_branch"]) ? $settings["panel_git_branch"] : "main"
      ]);
      exit();
    }

    if (empty($settings["panel_auto_update"])) {
      header("Content-Type: application/json");
      echo json_encode(["status" => "ignored", "message" => "Auto-actualizacion desactivada en el panel"]);
      exit();
    }

    $branch = isset($settings["panel_git_branch"]) ? $settings["panel_git_branch"] : "main";
    $res = Engine::execute("pirulu-server", ["update-panel", $branch]);

    $log = isset($res["log"]) ? $res["log"] : (isset($res["message"]) ? $res["message"] : "Actualizacion procesada");
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

