<?php

namespace Pirulu\Modules\Database\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Database;
use Pirulu\Core\Engine;
use Pirulu\Core\View;

class DatabaseController {
  private static string $encryptionKey = "pirulugcp_pma_sso_key_2026";

  public function index(): void {
    Auth::requireAuth();
    $db = Database::getConnection();

    $databases = $db->query("
      SELECT d.*, u.username 
      FROM databases d 
      LEFT JOIN users u ON d.user_id = u.id 
      ORDER BY d.id DESC
    ")->fetchAll();

    View::render("Modules/Database/Views/index", [
      "pageTitle" => "Bases de Datos MariaDB - PiruluGCP",
      "databases" => $databases
    ]);
  }

  public function create(): void {
    Auth::requireAuth();
    $db = Database::getConnection();
    $users = $db->query("SELECT id, username FROM users ORDER BY username ASC")->fetchAll();

    View::render("Modules/Database/Views/create", [
      "pageTitle" => "Anadir Base de Datos - PiruluGCP",
      "users" => $users
    ]);
  }

  public function store(): void {
    Auth::requireAuth();
    $db = Database::getConnection();

    $dbName = strtolower(trim($_POST["db_name"] ?? ""));
    $dbUser = strtolower(trim($_POST["db_user"] ?? ""));
    $dbPass = trim($_POST["db_password"] ?? "");
    $userId = (int)($_POST["user_id"] ?? 0);

    if (empty($dbName) || empty($dbUser) || empty($dbPass)) {
      View::setFlash("danger", "Todos los campos son obligatorios.");
      header("Location: /database/create");
      exit();
    }

    if (strlen($dbPass) < 8) {
      View::setFlash("danger", "La contrasena debe contener al menos 8 caracteres.");
      header("Location: /database/create");
      exit();
    }

    // Obtener usuario del sistema para prefijo obligatorio
    $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userRow = $stmt->fetch();
    $prefix = ($userRow["username"] ?? "admin") . "_";

    $fullDbName = (strpos($dbName, $prefix) === 0) ? $dbName : $prefix . $dbName;
    $fullDbUser = (strpos($dbUser, $prefix) === 0) ? $dbUser : $prefix . $dbUser;

    $res = Engine::execute("pirulu-db", ["add", $fullDbName, $fullDbUser, $dbPass]);

    if (isset($res["status"]) && $res["status"] === "success") {
      $encPass = self::encryptPassword($dbPass);
      $stmt = $db->prepare("INSERT INTO databases (db_name, db_user, db_password_enc, user_id) VALUES (?, ?, ?, ?)");
      $stmt->execute([$fullDbName, $fullDbUser, $encPass, $userId]);
      View::setFlash("success", "Base de datos " . $fullDbName . " creada exitosamente.");
    } else {
      View::setFlash("danger", "Error al crear la base de datos en MariaDB.");
    }

    header("Location: /database");
    exit();
  }

  public function edit(string $id): void {
    Auth::requireAuth();
    $db = Database::getConnection();

    $stmt = $db->prepare("SELECT d.*, u.username FROM databases d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
    $stmt->execute([(int)$id]);
    $database = $stmt->fetch();

    if (!$database) {
      View::setFlash("danger", "Base de datos no encontrada.");
      header("Location: /database");
      exit();
    }

    $username = $database["username"] ?? "admin";
    $prefix = $username . "_";

    $shortDbName = $database["db_name"];
    if (strpos($shortDbName, $prefix) === 0) {
      $shortDbName = substr($shortDbName, strlen($prefix));
    }

    $shortDbUser = $database["db_user"];
    if (strpos($shortDbUser, $prefix) === 0) {
      $shortDbUser = substr($shortDbUser, strlen($prefix));
    }

    View::render("Modules/Database/Views/edit", [
      "pageTitle" => "Editar Bases de Datos - PiruluGCP",
      "database" => $database,
      "prefix" => $prefix,
      "shortDbName" => $shortDbName,
      "shortDbUser" => $shortDbUser
    ]);
  }

  public function update(string $id): void {
    Auth::requireAuth();
    $db = Database::getConnection();

    $stmt = $db->prepare("SELECT * FROM databases WHERE id = ?");
    $stmt->execute([(int)$id]);
    $dbRow = $stmt->fetch();

    if (!$dbRow) {
      View::setFlash("danger", "Base de datos no encontrada.");
      header("Location: /database");
      exit();
    }

    $dbPass = trim($_POST["db_password"] ?? "");

    if (!empty($dbPass)) {
      if (strlen($dbPass) < 8) {
        View::setFlash("danger", "La contrasena debe contener al menos 8 caracteres.");
        header("Location: /database/edit/" . (int)$id);
        exit();
      }

      $res = Engine::execute("pirulu-db", ["passwd", $dbRow["db_user"], $dbPass]);
      if (isset($res["status"]) && $res["status"] === "success") {
        $encPass = self::encryptPassword($dbPass);
        $stmt = $db->prepare("UPDATE databases SET db_password_enc = ? WHERE id = ?");
        $stmt->execute([$encPass, (int)$id]);
        View::setFlash("success", "Contrasena de la base de datos " . $dbRow["db_name"] . " actualizada exitosamente.");
      } else {
        View::setFlash("danger", "Error al actualizar la contrasena en MariaDB.");
      }
    } else {
      View::setFlash("info", "No se realizaron cambios en la base de datos.");
    }

    header("Location: /database");
    exit();
  }

  public function autologin(string $id): void {
    Auth::requireAuth();
    $db = Database::getConnection();

    $stmt = $db->prepare("SELECT * FROM databases WHERE id = ?");
    $stmt->execute([(int)$id]);
    $dbRow = $stmt->fetch();

    if (!$dbRow) {
      View::setFlash("danger", "Base de datos no encontrada.");
      header("Location: /database");
      exit();
    }

    $plainPass = self::decryptPassword($dbRow["db_password_enc"] ?? "");

    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    // Establecer credenciales de conexion para phpMyAdmin Signon SSO
    $_SESSION["PMA_single_signon_user"] = $dbRow["db_user"];
    $_SESSION["PMA_single_signon_password"] = $plainPass;
    $_SESSION["PMA_single_signon_host"] = "localhost";
    $_SESSION["PMA_single_signon_port"] = 3306;

    // Redirigir directamente a phpMyAdmin con la base de datos seleccionada
    header("Location: /phpmyadmin/index.php?route=/database/structure&db=" . urlencode($dbRow["db_name"]));
    exit();
  }

  public function pmaRedirect(): void {
    Auth::requireAuth();
    $currentUser = Auth::user();
    $db = Database::getConnection();

    // Buscar primera base de datos del usuario autenticado
    $stmt = $db->prepare("SELECT * FROM databases WHERE user_id = ? ORDER BY id ASC LIMIT 1");
    $stmt->execute([(int)($currentUser["id"] ?? 0)]);
    $dbRow = $stmt->fetch();

    if (!$dbRow && ($currentUser["role"] ?? "") === "admin") {
      $stmt = $db->query("SELECT * FROM databases ORDER BY id ASC LIMIT 1");
      $dbRow = $stmt->fetch();
    }

    if ($dbRow) {
      if (session_status() === PHP_SESSION_NONE) {
        session_start();
      }
      $plainPass = self::decryptPassword($dbRow["db_password_enc"] ?? "");
      $_SESSION["PMA_single_signon_user"] = $dbRow["db_user"];
      $_SESSION["PMA_single_signon_password"] = $plainPass;
      $_SESSION["PMA_single_signon_host"] = "localhost";
      $_SESSION["PMA_single_signon_port"] = 3306;

      header("Location: /phpmyadmin/index.php?route=/database/structure&db=" . urlencode($dbRow["db_name"]));
      exit();
    }

    header("Location: /phpmyadmin/login.php");
    exit();
  }

  public function delete(string $id): void {
    Auth::requireAuth();
    $db = Database::getConnection();

    $stmt = $db->prepare("SELECT * FROM databases WHERE id = ?");
    $stmt->execute([(int)$id]);
    $dbRow = $stmt->fetch();

    if ($dbRow) {
      Engine::execute("pirulu-db", ["delete", $dbRow["db_name"], $dbRow["db_user"]]);

      $stmt = $db->prepare("DELETE FROM databases WHERE id = ?");
      $stmt->execute([(int)$id]);

      View::setFlash("success", "Base de datos " . $dbRow["db_name"] . " eliminada.");
    }

    header("Location: /database");
    exit();
  }

  private static function encryptPassword(string $password): string {
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt($password, "aes-256-cbc", self::$encryptionKey, 0, $iv);
    return base64_encode($iv . $encrypted);
  }

  private static function decryptPassword(string $encryptedData): string {
    if (empty($encryptedData)) {
      return "";
    }
    $raw = base64_decode($encryptedData);
    $iv = substr($raw, 0, 16);
    $ciphertext = substr($raw, 16);
    return openssl_decrypt($ciphertext, "aes-256-cbc", self::$encryptionKey, 0, $iv) ?: "";
  }
}
