<?php

namespace Pirulu\Modules\Auth\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\View;
use Pirulu\Core\Database;

class AuthController {
  public function showLogin() {
    if (Auth::check()) {
      header("Location: /dashboard");
      exit();
    }

    $db = Database::getConnection();
    $stmt = $db->query("SELECT cf_turnstile_enabled, cf_turnstile_site_key FROM server_settings WHERE id = 1 LIMIT 1");
    $settings = $stmt ? $stmt->fetch() : null;

    $cfEnabled = !empty($settings["cf_turnstile_enabled"]);
    $cfSiteKey = (string)($settings["cf_turnstile_site_key"] ?? "");

    View::render("Modules/Auth/Views/login", [
      "pageTitle"          => "Iniciar Sesión - PiruluGCP",
      "cfTurnstileEnabled" => $cfEnabled,
      "cfSiteKey"          => $cfSiteKey
    ], "");
  }

  public function login() {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $turnstileResponse = trim($_POST["cf-turnstile-response"] ?? "");

    if (empty($username) || empty($password)) {
      View::setFlash("danger", "Por favor ingresa usuario y contraseña.");
      header("Location: /login");
      exit();
    }

    // Verificacion de Cloudflare Turnstile CAPTCHA si esta habilitado
    $db = Database::getConnection();
    $stmt = $db->query("SELECT cf_turnstile_enabled, cf_turnstile_secret_key FROM server_settings WHERE id = 1 LIMIT 1");
    $settings = $stmt ? $stmt->fetch() : null;

    $cfEnabled = !empty($settings["cf_turnstile_enabled"]);
    $cfSecretKey = trim((string)($settings["cf_turnstile_secret_key"] ?? ""));

    if ($cfEnabled && !empty($cfSecretKey)) {
      if (empty($turnstileResponse)) {
        View::setFlash("danger", "Por favor completa la verificación de Cloudflare CAPTCHA.");
        header("Location: /login");
        exit();
      }

      $verifyUrl = "https://challenges.cloudflare.com/turnstile/v0/siteverify";
      $postData = http_build_query([
        "secret"   => $cfSecretKey,
        "response" => $turnstileResponse,
        "remoteip" => $_SERVER["HTTP_CF_CONNECTING_IP"] ?? ($_SERVER["REMOTE_ADDR"] ?? "")
      ]);

      $opts = [
        "http" => [
          "method"  => "POST",
          "header"  => "Content-Type: application/x-www-form-urlencoded\r\n",
          "content" => $postData,
          "timeout" => 10
        ]
      ];

      $context = stream_context_create($opts);
      $apiResponse = @file_get_contents($verifyUrl, false, $context);
      $result = json_decode((string)$apiResponse, true);

      if (empty($result["success"])) {
        View::setFlash("danger", "Verificación de seguridad de Cloudflare CAPTCHA fallida.");
        header("Location: /login");
        exit();
      }
    }

    $loginResult = Auth::login($username, $password);

    if ($loginResult === "2fa_required") {
      header("Location: /login/2fa");
      exit();
    }

    if ($loginResult === true) {
      header("Location: /dashboard");
      exit();
    }

    View::setFlash("danger", "Credenciales incorrectas.");
    header("Location: /login");
    exit();
  }

  public function show2fa() {
    if (Auth::check()) {
      header("Location: /dashboard");
      exit();
    }

    if (empty($_SESSION["2fa_pending_user_id"])) {
      header("Location: /login");
      exit();
    }

    View::render("Modules/Auth/Views/login_2fa", [
      "pageTitle" => "Verificación en Dos Pasos (2FA) - PiruluGCP",
      "username"  => (string)($_SESSION["2fa_pending_username"] ?? "admin")
    ], "");
  }

  public function verify2fa() {
    if (empty($_SESSION["2fa_pending_user_id"])) {
      header("Location: /login");
      exit();
    }

    $code = trim($_POST["code"] ?? "");

    if (empty($code)) {
      View::setFlash("danger", "Por favor ingresa el código de 6 dígitos.");
      header("Location: /login/2fa");
      exit();
    }

    if (Auth::verifyAndComplete2fa($code)) {
      header("Location: /dashboard");
      exit();
    }

    View::setFlash("danger", "Código de verificación 2FA inválido o expirado.");
    header("Location: /login/2fa");
    exit();
  }

  public function logout() {
    Auth::logout();
    header("Location: /login");
    exit();
  }
}
