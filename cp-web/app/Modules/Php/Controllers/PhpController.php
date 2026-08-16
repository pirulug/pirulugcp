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

    $installedVersions = [];
    $availableToInstall = [];

    foreach ($versions as $v) {
      if (!empty($v["installed"])) {
        $installedVersions[] = $v;
      } else {
        $availableToInstall[] = $v;
      }
    }

    // Version activa seleccionada
    $activeVer = $_GET["ver"] ?? "";
    if (empty($activeVer) || !in_array($activeVer, array_column($installedVersions, "version"))) {
      if (!empty($installedVersions)) {
        // Preferir 8.5 si esta instalada, o la primera instalada
        $found85 = false;
        foreach ($installedVersions as $iv) {
          if ($iv["version"] === "8.5") {
            $activeVer = "8.5";
            $found85 = true;
            break;
          }
        }
        if (!$found85) {
          $activeVer = $installedVersions[0]["version"];
        }
      } else {
        $activeVer = "8.5";
      }
    }

    // Pestana activa
    $activeTab = $_GET["tab"] ?? "logs";
    if (!in_array($activeTab, ["logs", "ini", "ports", "extensions"])) {
      $activeTab = "logs";
    }

    // Dominios asignados a todas las versiones
    $usageStats = [];
    $stmt = $db->query("SELECT php_version, COUNT(*) as total FROM domains GROUP BY php_version");
    while ($row = $stmt->fetch()) {
      $usageStats[$row["php_version"]] = (int)$row["total"];
    }

    // Dominios de la version activa
    $activeDomains = [];
    $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.php_version = ? ORDER BY d.id DESC");
    $stmt->execute([$activeVer]);
    $activeDomains = $stmt->fetchAll();

    // Obtener datos segun la version activa
    $logsData = Engine::execute("pirulu-php", ["logs", $activeVer, "80"]);
    $rawLogs = "";
    if (!empty($logsData["raw_base64"])) {
      $rawLogs = base64_decode($logsData["raw_base64"]);
    }

    $iniData = Engine::execute("pirulu-php", ["get-ini", $activeVer]);
    $rawIni = "";
    if (!empty($iniData["raw_base64"])) {
      $rawIni = base64_decode($iniData["raw_base64"]);
    }

    $extData = Engine::execute("pirulu-php", ["extensions", $activeVer]);
    $extensions = $extData["extensions"] ?? [];

    View::render("Modules/Php/Views/index", [
      "pageTitle"          => "Gestor de PHP Multi-Version - PiruluGCP",
      "versions"           => $versions,
      "installedVersions"  => $installedVersions,
      "availableToInstall" => $availableToInstall,
      "activeVer"          => $activeVer,
      "activeTab"          => $activeTab,
      "usageStats"         => $usageStats,
      "activeDomains"      => $activeDomains,
      "rawLogs"            => $rawLogs,
      "ini"                => $iniData,
      "rawIni"             => $rawIni,
      "extensions"         => $extensions
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

    header("Location: /php?ver=" . $version);
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
      header("Location: /php?ver=" . $version);
      exit();
    }

    // Validar que ningún dominio esté utilizando esta versión
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM domains WHERE php_version = ?");
    $stmt->execute([$version]);
    $countRow = $stmt->fetch();
    $domainCount = (int)($countRow["total"] ?? 0);

    if ($domainCount > 0) {
      View::setFlash("danger", "No se puede desinstalar PHP " . $version . " porque está asignada a " . $domainCount . " dominio(s). Cambia la versión en dichos dominios antes de desinstalar.");
      header("Location: /php?ver=" . $version);
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
    header("Location: /php?ver=" . $version . "&tab=ini");
    exit();
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

    header("Location: /php?ver=" . $version . "&tab=ini");
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

    header("Location: /php?ver=" . $version . "&tab=ini");
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

    header("Location: /php?ver=" . $version);
    exit();
  }
}
