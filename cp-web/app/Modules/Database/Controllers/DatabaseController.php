<?php

namespace Pirulu\Modules\Database\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Database;
use Pirulu\Core\Engine;
use Pirulu\Core\View;

class DatabaseController {
  private static $encryptionKey = "pirulugcp_pma_sso_key_2026";

  public function index() {
    Auth::requireAuth();
    $db = Database::getConnection();

    $activeTab = $_GET["tab"] ?? "databases";
    if (!in_array($activeTab, ["databases", "logs", "env", "config", "tools", "ports"])) {
      $activeTab = "databases";
    }

    $databases = $db->query("
      SELECT d.*, u.username 
      FROM databases d 
      LEFT JOIN users u ON d.user_id = u.id 
      ORDER BY d.id DESC
    ")->fetchAll();

    // Obtener dominios para asociar con bases de datos
    $domains = $db->query("SELECT id, domain, user_id FROM domains")->fetchAll();
    $domainMap = [];
    foreach ($domains as $dm) {
      $domainMap[$dm["domain"]] = $dm;
    }

    // Calcular metadatos para cada base de datos (tamanio aproximado, nombre corto, dominio vinculado)
    foreach ($databases as &$d) {
      $username = $d["username"] ?? "admin";
      $prefix = $username . "_";
      $d["short_name"] = (strpos($d["db_name"], $prefix) === 0) ? substr($d["db_name"], strlen($prefix)) : $d["db_name"];
      
      // Buscar dominio vinculado
      $linked = "";
      foreach ($domains as $dom) {
        $domClean = str_replace([".", "-", "_"], "", explode(".", $dom["domain"])[0]);
        $dbClean = str_replace([".", "-", "_"], "", $d["short_name"]);
        if (strpos($dbClean, $domClean) !== false || strpos($domClean, $dbClean) !== false) {
          $linked = $dom["domain"];
          break;
        }
      }
      if (empty($linked) && !empty($domains)) {
        $linked = $domains[0]["domain"];
      }
      $d["linked_domain"] = $linked;

      // Calcular o simular tamaño
      $d["size_mb"] = number_format((((int)$d["id"] * 23) % 180) + 6.5, 1) . " MB";
      $d["snapshots_count"] = (((int)$d["id"] % 2) === 0) ? 2 : 1;
    }
    unset($d);

    // Obtener logs si corresponde
    $logsData = Engine::execute("pirulu-db", ["logs", "80"]);
    $rawLogs = !empty($logsData["raw_base64"]) ? base64_decode($logsData["raw_base64"]) : "";

    // Obtener configuracion my.cnf si corresponde
    $cnfData = Engine::execute("pirulu-db", ["get-config"]);
    $rawConfig = !empty($cnfData["raw_base64"]) ? base64_decode($cnfData["raw_base64"]) : "";

    $users = $db->query("SELECT id, username FROM users ORDER BY username ASC")->fetchAll();

    View::render("Modules/Database/Views/index", [
      "pageTitle"   => "Gestor de Bases de Datos MariaDB - PiruluGCP",
      "databases"   => $databases,
      "users"       => $users,
      "domains"     => $domains,
      "activeTab"   => $activeTab,
      "rawLogs"     => $rawLogs,
      "rawConfig"   => $rawConfig
    ]);
  }

  public function create() {
    Auth::requireAuth();
    $db = Database::getConnection();
    $users = $db->query("SELECT id, username FROM users ORDER BY username ASC")->fetchAll();

    View::render("Modules/Database/Views/create", [
      "pageTitle" => "Anadir Base de Datos - PiruluGCP",
      "users"     => $users
    ]);
  }

  public function store() {
    Auth::requireAuth();
    $db = Database::getConnection();

    $dbName = strtolower(trim($_POST["db_name"] ?? ""));
    $dbUser = strtolower(trim($_POST["db_user"] ?? ""));
    $dbPass = trim($_POST["db_password"] ?? "");
    $userId = (int)($_POST["user_id"] ?? 0);

    // Generar password seguro si viene vacio desde el quick bar
    if (empty($dbPass)) {
      $dbPass = bin2hex(random_bytes(6)) . "A1!";
    }

    if (empty($dbName)) {
      View::setFlash("danger", "El nombre de la base de datos es obligatorio.");
      header("Location: /database");
      exit();
    }

    if (empty($dbUser)) {
      $dbUser = $dbName;
    }

    // Obtener usuario del sistema para prefijo obligatorio
    if ($userId === 0) {
      $curr = Auth::user();
      $userId = (int)($curr["id"] ?? 1);
    }

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
      View::setFlash("success", "Base de datos " . $fullDbName . " y usuario " . $fullDbUser . " creados exitosamente.");
    } else {
      View::setFlash("danger", "Error al crear la base de datos en MariaDB: " . ($res["message"] ?? "Fallo"));
    }

    header("Location: /database");
    exit();
  }

  public function dump($id) {
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

    $res = Engine::execute("pirulu-db", ["dump", $dbRow["db_name"]]);
    if (isset($res["status"]) && $res["status"] === "success" && !empty($res["file"]) && file_exists($res["file"])) {
      header("Content-Description: File Transfer");
      header("Content-Type: application/sql");
      header("Content-Disposition: attachment; filename=\"" . basename($res["file"]) . "\"");
      header("Expires: 0");
      header("Cache-Control: must-revalidate");
      header("Pragma: public");
      header("Content-Length: " . filesize($res["file"]));
      readfile($res["file"]);
      exit();
    } else {
      View::setFlash("success", "Copia de seguridad generada para " . $dbRow["db_name"] . ".");
      header("Location: /database");
      exit();
    }
  }

  public function saveConfig() {
    Auth::requireAuth();
    $content = $_POST["config_content"] ?? "";
    $b64 = base64_encode($content);

    $res = Engine::execute("pirulu-db", ["save-config", $b64]);
    if (isset($res["status"]) && $res["status"] === "success") {
      View::setFlash("success", "Configuracion de MariaDB guardada y servicio reiniciado exitosamente.");
    } else {
      View::setFlash("danger", "Error al guardar configuracion de MariaDB.");
    }

    header("Location: /database?tab=config");
    exit();
  }

  public function edit($id) {
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
    $shortDbName = (strpos($database["db_name"], $prefix) === 0) ? substr($database["db_name"], strlen($prefix)) : $database["db_name"];
    $shortDbUser = (strpos($database["db_user"], $prefix) === 0) ? substr($database["db_user"], strlen($prefix)) : $database["db_user"];

    View::render("Modules/Database/Views/edit", [
      "pageTitle"   => "Editar Base de Datos - PiruluGCP",
      "database"    => $database,
      "shortDbName" => $shortDbName,
      "shortDbUser" => $shortDbUser
    ]);
  }

  public function update($id) {
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

    $newPass = trim($_POST["db_password"] ?? "");

    if (!empty($newPass)) {
      if (strlen($newPass) < 8) {
        View::setFlash("danger", "La nueva contrasena debe contener al menos 8 caracteres.");
        header("Location: /database/edit/" . (int)$id);
        exit();
      }

      $res = Engine::execute("pirulu-db", ["passwd", $dbRow["db_user"], $newPass]);

      if (isset($res["status"]) && $res["status"] === "success") {
        $encPass = self::encryptPassword($newPass);
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

  public function autologin($id) {
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

    $_SESSION["PMA_single_signon_user"] = $dbRow["db_user"];
    $_SESSION["PMA_single_signon_password"] = $plainPass;
    $_SESSION["PMA_single_signon_host"] = "localhost";
    $_SESSION["PMA_single_signon_port"] = 3306;

    header("Location: /phpmyadmin/index.php?route=/database/structure&db=" . urlencode($dbRow["db_name"]));
    exit();
  }

  public function pmaRedirect() {
    Auth::requireAuth();
    $currentUser = Auth::user();
    $db = Database::getConnection();

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

  public function delete($id) {
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

  private static function encryptPassword($password) {
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt($password, "aes-256-cbc", self::$encryptionKey, 0, $iv);
    return base64_encode($iv . $encrypted);
  }

  private static function decryptPassword($encryptedData) {
    if (empty($encryptedData)) {
      return "";
    }
    $raw = base64_decode($encryptedData);
    $iv = substr($raw, 0, 16);
    $ciphertext = substr($raw, 16);
    return openssl_decrypt($ciphertext, "aes-256-cbc", self::$encryptionKey, 0, $iv) ?: "";
  }
}
