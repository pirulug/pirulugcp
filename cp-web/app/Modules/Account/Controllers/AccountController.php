<?php

namespace Pirulu\Modules\Account\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\View;
use Pirulu\Core\Database;
use Pirulu\Core\Engine;

class AccountController {
  public function profile() {
    Auth::requireAuth();
    $user = Auth::user();

    View::render("Modules/Account/Views/profile", [
      "pageTitle" => "Información de Perfil - Mi Cuenta",
      "activeTab" => "profile",
      "user"      => $user
    ]);
  }

  public function updateProfile() {
    Auth::requireAuth();
    $currentUser = Auth::user();
    $userId = (int)$currentUser["id"];

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if (empty($name) || empty($email)) {
      View::setFlash("danger", "El nombre y correo electrónico son obligatorios.");
      header("Location: /account/profile");
      exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      View::setFlash("danger", "El formato del correo electrónico no es válido.");
      header("Location: /account/profile");
      exit();
    }

    $db = Database::getConnection();
    $stmt = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->execute([$name, $email, $userId]);

    $_SESSION["name"] = $name;
    $_SESSION["email"] = $email;

    View::setFlash("success", "Información de perfil actualizada exitosamente.");
    header("Location: /account/profile");
    exit();
  }

  public function password() {
    Auth::requireAuth();
    $user = Auth::user();

    View::render("Modules/Account/Views/password", [
      "pageTitle" => "Cambiar Contraseña - Mi Cuenta",
      "activeTab" => "password",
      "user"      => $user
    ]);
  }

  public function updatePassword() {
    Auth::requireAuth();
    $currentUser = Auth::user();
    $userId = (int)$currentUser["id"];
    $username = (string)$currentUser["username"];

    $currentPass = trim($_POST["current_password"] ?? "");
    $newPass = trim($_POST["new_password"] ?? "");
    $confirmPass = trim($_POST["confirm_password"] ?? "");

    if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
      View::setFlash("danger", "Todos los campos de contraseña son requeridos.");
      header("Location: /account/password");
      exit();
    }

    if ($newPass !== $confirmPass) {
      View::setFlash("danger", "La nueva contraseña y su confirmación no coinciden.");
      header("Location: /account/password");
      exit();
    }

    if (strlen($newPass) < 6) {
      View::setFlash("danger", "La nueva contraseña debe tener al menos 6 caracteres.");
      header("Location: /account/password");
      exit();
    }

    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($currentPass, $row["password_hash"])) {
      View::setFlash("danger", "La contraseña actual es incorrecta.");
      header("Location: /account/password");
      exit();
    }

    $newHash = password_hash($newPass, PASSWORD_DEFAULT);
    $updateStmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $updateStmt->execute([$newHash, $userId]);

    // Sincronizar contraseña del usuario en el sistema Linux
    try {
      Engine::execute("pirulu-user", ["passwd", $username, $newPass]);
    } catch (\Exception $e) {
      // Continuar si es entorno local
    }

    View::setFlash("success", "Contraseña cambiada exitosamente.");
    header("Location: /account/password");
    exit();
  }

  public function security() {
    Auth::requireAuth();
    $user = Auth::user();
    $userId = (int)$user["id"];

    $db = Database::getConnection();
    $stmt = $db->query("SELECT cf_turnstile_enabled, cf_turnstile_site_key, cf_turnstile_secret_key FROM server_settings WHERE id = 1 LIMIT 1");
    $settings = $stmt ? $stmt->fetch() : null;

    $stmtUser = $db->prepare("SELECT two_factor_enabled, two_factor_secret FROM users WHERE id = ? LIMIT 1");
    $stmtUser->execute([$userId]);
    $userSecurity = $stmtUser->fetch();

    $is2faEnabled = !empty($userSecurity["two_factor_enabled"]) && !empty($userSecurity["two_factor_secret"]);
    
    // Generar clave temporal para configuracion de 2FA si aun no esta activado
    $tempSecret = "";
    $otpAuthUrl = "";
    if (!$is2faEnabled) {
      if (empty($_SESSION["pending_2fa_secret"])) {
        $_SESSION["pending_2fa_secret"] = \Pirulu\Core\Totp::generateSecret();
      }
      $tempSecret = $_SESSION["pending_2fa_secret"];
      $otpAuthUrl = \Pirulu\Core\Totp::getOtpAuthUrl($user["username"] ?? "admin", $tempSecret);
    }

    View::render("Modules/Account/Views/security", [
      "pageTitle"       => "Seguridad, 2FA y CAPTCHA - Mi Cuenta",
      "activeTab"       => "security",
      "user"            => $user,
      "settings"        => $settings ?? [],
      "is2faEnabled"    => $is2faEnabled,
      "tempSecret"      => $tempSecret,
      "otpAuthUrl"      => $otpAuthUrl
    ]);
  }

  public function updateSecurity() {
    Auth::requireAuth();

    $enabled = !empty($_POST["cf_turnstile_enabled"]) ? 1 : 0;
    $siteKey = trim($_POST["cf_turnstile_site_key"] ?? "");
    $secretKey = trim($_POST["cf_turnstile_secret_key"] ?? "");

    if ($enabled && (empty($siteKey) || empty($secretKey))) {
      View::setFlash("danger", "Para activar Cloudflare CAPTCHA debes ingresar la Clave del Sitio (Site Key) y la Clave Secreta (Secret Key).");
      header("Location: /account/security");
      exit();
    }

    $db = Database::getConnection();
    $stmt = $db->prepare("UPDATE server_settings SET cf_turnstile_enabled = ?, cf_turnstile_site_key = ?, cf_turnstile_secret_key = ? WHERE id = 1");
    $stmt->execute([$enabled, $siteKey, $secretKey]);

    View::setFlash("success", "Configuración de Cloudflare CAPTCHA actualizada exitosamente.");
    header("Location: /account/security");
    exit();
  }

  public function enable2fa() {
    Auth::requireAuth();
    $currentUser = Auth::user();
    $userId = (int)$currentUser["id"];

    $secret = trim($_POST["secret"] ?? "");
    $code = trim($_POST["code"] ?? "");

    if (empty($secret) || empty($code)) {
      View::setFlash("danger", "Por favor ingresa el código de 6 dígitos de tu aplicación autenticadora.");
      header("Location: /account/security");
      exit();
    }

    if (!\Pirulu\Core\Totp::verifyCode($secret, $code)) {
      View::setFlash("danger", "Código de verificación 2FA incorrecto. Asegúrate de que el reloj de tu dispositivo esté sincronizado.");
      header("Location: /account/security");
      exit();
    }

    $db = Database::getConnection();
    $stmt = $db->prepare("UPDATE users SET two_factor_enabled = 1, two_factor_secret = ? WHERE id = ?");
    $stmt->execute([$secret, $userId]);

    unset($_SESSION["pending_2fa_secret"]);

    View::setFlash("success", "Autenticación en Dos Pasos (2FA) activada exitosamente para tu cuenta.");
    header("Location: /account/security");
    exit();
  }

  public function disable2fa() {
    Auth::requireAuth();
    $currentUser = Auth::user();
    $userId = (int)$currentUser["id"];

    $password = trim($_POST["password"] ?? "");

    if (empty($password)) {
      View::setFlash("danger", "Debes ingresar tu contraseña actual para confirmar la desactivación del 2FA.");
      header("Location: /account/security");
      exit();
    }

    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user["password_hash"])) {
      View::setFlash("danger", "Contraseña incorrecta. No se pudo desactivar el 2FA.");
      header("Location: /account/security");
      exit();
    }

    $updateStmt = $db->prepare("UPDATE users SET two_factor_enabled = 0, two_factor_secret = '' WHERE id = ?");
    $updateStmt->execute([$userId]);

    View::setFlash("info", "Autenticación en Dos Pasos (2FA) desactivada.");
    header("Location: /account/security");
    exit();
  }
}
