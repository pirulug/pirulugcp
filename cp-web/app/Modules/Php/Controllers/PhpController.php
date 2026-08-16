<?php

namespace Pirulu\Modules\Php\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Database;
use Pirulu\Core\Engine;
use Pirulu\Core\View;

class PhpController {
  public function index() {
    Auth::requireAuth();
    $db = Database::getConnection();

    $phpData = Engine::execute("pirulu-php", ["versions"]);
    $versions = $phpData["versions"] ?? [];

    foreach ($versions as &$v) {
      if (!isset($v["service"])) {
        $v["service"] = "php" . $v["version"] . "-fpm";
      }
    }
    unset($v);

    $usageStats = [];
    $stmt = $db->query("SELECT php_version, COUNT(*) as total FROM domains GROUP BY php_version");
    while ($row = $stmt->fetch()) {
      $usageStats[$row["php_version"]] = $row["total"];
    }

    View::render("Modules/Php/Views/index", [
      "pageTitle"   => "Gestor de PHP Multi-Version - PiruluGCP",
      "versions"    => $versions,
      "phpVersions" => $versions,
      "usageStats"  => $usageStats
    ]);
  }

  public function install($version) {
    Auth::requireAuth();

    $version = preg_replace("/[^0-9.]/", "", (string)$version);
    if (empty($version)) {
      View::setFlash("danger", "Versión de PHP no válida.");
      header("Location: /php");
      exit();
    }

    $res = Engine::execute("pirulu-php", ["install", $version]);

    if (isset($res["status"]) && $res["status"] === "success") {
      View::setFlash("success", "PHP " . $version . " y sus extensiones se han instalado y activado correctamente.");
    } else {
      View::setFlash("danger", "Error al instalar PHP " . $version . ": " . ($res["message"] ?? "Fallo en instalación"));
    }

    header("Location: /php");
    exit();
  }

  public function uninstall($version) {
    Auth::requireAuth();
    $db = Database::getConnection();

    $version = preg_replace("/[^0-9.]/", "", (string)$version);
    if (empty($version)) {
      View::setFlash("danger", "Versión de PHP no válida.");
      header("Location: /php");
      exit();
    }

    if ($version === "8.5") {
      View::setFlash("warning", "No se permite desinstalar la versión base PHP 8.5 utilizada por el sistema.");
      header("Location: /php");
      exit();
    }

    // Validar que ningún dominio esté utilizando esta versión
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM domains WHERE php_version = ?");
    $stmt->execute([$version]);
    $countRow = $stmt->fetch();
    $domainCount = (int)($countRow["total"] ?? 0);

    if ($domainCount > 0) {
      View::setFlash("danger", "No se puede desinstalar PHP " . $version . " porque está asignada a " . $domainCount . " dominio(s). Cambia la versión en dichos dominios antes de desinstalar.");
      header("Location: /php");
      exit();
    }

    $res = Engine::execute("pirulu-php", ["remove", $version]);

    if (isset($res["status"]) && $res["status"] === "success") {
      View::setFlash("success", "PHP " . $version . " se ha desinstalado exitosamente del servidor.");
    } else {
      View::setFlash("danger", "Error al desinstalar PHP " . $version . ": " . ($res["message"] ?? "Fallo"));
    }

    header("Location: /php");
    exit();
  }

  public function config($version) {
    Auth::requireAuth();

    $version = preg_replace("/[^0-9.]/", "", (string)$version);
    $iniData = Engine::execute("pirulu-php", ["get-ini", $version]);

    if (!isset($iniData["status"]) || $iniData["status"] !== "success") {
      View::setFlash("danger", "No se pudo obtener la configuración de PHP " . $version . ".");
      header("Location: /php");
      exit();
    }

    $rawIni = "";
    if (!empty($iniData["raw_base64"])) {
      $rawIni = base64_decode($iniData["raw_base64"]);
    }

    $activeTab = $_GET["tab"] ?? "basic";

    View::render("Modules/Php/Views/config", [
      "pageTitle" => "Configurar Servidor: PHP " . $version . " - PiruluGCP",
      "version"   => $version,
      "ini"       => $iniData,
      "rawIni"    => $rawIni,
      "activeTab" => $activeTab
    ]);
  }

  public function saveConfig($version) {
    Auth::requireAuth();

    $version   = preg_replace("/[^0-9.]/", "", (string)$version);
    $maxExec   = trim($_POST["max_execution_time"] ?? "60");
    $maxInput  = trim($_POST["max_input_time"] ?? "60");
    $memLimit  = trim($_POST["memory_limit"] ?? "128M");
    $errRep    = trim($_POST["error_reporting"] ?? "E_ALL & ~E_DEPRECATED");
    $dispErr   = trim($_POST["display_errors"] ?? "Off");
    $postMax   = trim($_POST["post_max_size"] ?? "200M");
    $uploadMax = trim($_POST["upload_max_filesize"] ?? "200M");

    $res = Engine::execute("pirulu-php", [
      "set-ini",
      $version,
      $maxExec,
      $maxInput,
      $memLimit,
      $errRep,
      $dispErr,
      $postMax,
      $uploadMax
    ]);

    if (isset($res["status"]) && $res["status"] === "success") {
      View::setFlash("success", "Configuración de PHP " . $version . " guardada y servicio reiniciado exitosamente.");
    } else {
      View::setFlash("danger", "Error al guardar configuración de PHP " . $version . ": " . ($res["message"] ?? "Fallo"));
    }

    header("Location: /php/config/" . $version . "?tab=basic");
    exit();
  }

  public function saveRawIni($version) {
    Auth::requireAuth();

    $version    = preg_replace("/[^0-9.]/", "", (string)$version);
    $rawContent = $_POST["raw_ini_content"] ?? "";
    $b64Content = base64_encode($rawContent);

    $res = Engine::execute("pirulu-php", ["save-raw-ini", $version, $b64Content]);

    if (isset($res["status"]) && $res["status"] === "success") {
      View::setFlash("success", "Archivo php.ini guardado y PHP " . $version . " reiniciado exitosamente.");
    } else {
      View::setFlash("danger", "Error al guardar archivo php.ini de PHP " . $version . ": " . ($res["message"] ?? "Fallo"));
    }

    header("Location: /php/config/" . $version . "?tab=advanced");
    exit();
  }

  public function restart($version) {
    Auth::requireAuth();

    $version = preg_replace("/[^0-9.]/", "", (string)$version);
    $res = Engine::execute("pirulu-php", ["restart", $version]);
    if (isset($res["status"]) && $res["status"] === "success") {
      View::setFlash("success", "Servicio PHP-FPM " . $version . " reiniciado correctamente.");
    } else {
      View::setFlash("danger", "Error al reiniciar PHP-FPM " . $version . ".");
    }

    header("Location: /php");
    exit();
  }
}
