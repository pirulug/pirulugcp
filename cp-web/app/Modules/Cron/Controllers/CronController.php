<?php

namespace Pirulu\Modules\Cron\Controllers;

use PDO;
use Pirulu\Core\Auth;
use Pirulu\Core\Database;
use Pirulu\Core\Engine;
use Pirulu\Core\View;

class CronController {
  /**
   * Muestra la lista de tareas programadas y el estado del servicio cron.
   */
  public function index() {
    Auth::requireAuth();
    $connect = Database::getConnection();

    // Estado del daemon cron
    $cron_data = Engine::execute("pirulu-cron", ["status"]);
    $cron_active = ($cron_data["cron_service"] ?? "") === "active";

    // Obtener lista de tareas cron
    $query = "SELECT c.*, d.domain FROM cron_jobs c LEFT JOIN domains d ON c.domain_id = d.id ORDER BY c.id DESC";
    $stmt  = $connect->prepare($query);
    $stmt->execute();
    $cron_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener dominios para asociar rutas
    $query_dom = "SELECT id, domain FROM domains ORDER BY domain ASC";
    $stmt_dom  = $connect->prepare($query_dom);
    $stmt_dom->execute();
    $domains   = $stmt_dom->fetchAll(PDO::FETCH_ASSOC);

    View::render("Modules/Cron/Views/index", [
      "pageTitle"   => "Programador de Tareas Cron - PiruluGCP",
      "cronJobs"    => $cron_jobs,
      "domains"     => $domains,
      "cronActive"  => $cron_active
    ]);
  }

  /**
   * Registra una nueva tarea cron y sincroniza el crontab del sistema.
   */
  public function store() {
    Auth::requireAuth();
    $connect = Database::getConnection();

    $command     = trim($_POST["command"] ?? "");
    $minute      = trim($_POST["minute"] ?? "*");
    $hour        = trim($_POST["hour"] ?? "*");
    $day         = trim($_POST["day"] ?? "*");
    $month       = trim($_POST["month"] ?? "*");
    $weekday     = trim($_POST["weekday"] ?? "*");
    $description = trim($_POST["description"] ?? "");
    $domain_id   = !empty($_POST["domain_id"]) ? (int)$_POST["domain_id"] : null;
    $output_mode = $_POST["output_mode"] ?? "mute";

    if (empty($command)) {
      View::setFlash("danger", "El comando a ejecutar es obligatorio.");
      header("Location: /cron");
      exit();
    }

    // Formatear redireccion de salida si aplica
    if ($output_mode === "mute" && strpos($command, ">") === false) {
      $command = $command . " > /dev/null 2>&1";
    }

    $query = "INSERT INTO cron_jobs (user_id, domain_id, command, minute, hour, day, month, weekday, description, status) 
              VALUES (1, :domain_id, :command, :minute, :hour, :day, :month, :weekday, :description, 'active')";
    $stmt  = $connect->prepare($query);
    $stmt->bindParam(":domain_id", $domain_id);
    $stmt->bindParam(":command", $command);
    $stmt->bindParam(":minute", $minute);
    $stmt->bindParam(":hour", $hour);
    $stmt->bindParam(":day", $day);
    $stmt->bindParam(":month", $month);
    $stmt->bindParam(":weekday", $weekday);
    $stmt->bindParam(":description", $description);
    $stmt->execute();

    self::sync_system_crontab();

    View::setFlash("success", "Tarea Cron creada y programada exitosamente.");
    header("Location: /cron");
    exit();
  }

  /**
   * Actualiza una tarea cron existente.
   */
  public function update($id) {
    Auth::requireAuth();
    $connect = Database::getConnection();
    $id = (int)$id;

    $command     = trim($_POST["command"] ?? "");
    $minute      = trim($_POST["minute"] ?? "*");
    $hour        = trim($_POST["hour"] ?? "*");
    $day         = trim($_POST["day"] ?? "*");
    $month       = trim($_POST["month"] ?? "*");
    $weekday     = trim($_POST["weekday"] ?? "*");
    $description = trim($_POST["description"] ?? "");
    $domain_id   = !empty($_POST["domain_id"]) ? (int)$_POST["domain_id"] : null;

    if (empty($command)) {
      View::setFlash("danger", "El comando a ejecutar es obligatorio.");
      header("Location: /cron");
      exit();
    }

    $query = "UPDATE cron_jobs SET domain_id = :domain_id, command = :command, minute = :minute, 
              hour = :hour, day = :day, month = :month, weekday = :weekday, description = :description, 
              updated_at = CURRENT_TIMESTAMP WHERE id = :id";
    $stmt  = $connect->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":domain_id", $domain_id);
    $stmt->bindParam(":command", $command);
    $stmt->bindParam(":minute", $minute);
    $stmt->bindParam(":hour", $hour);
    $stmt->bindParam(":day", $day);
    $stmt->bindParam(":month", $month);
    $stmt->bindParam(":weekday", $weekday);
    $stmt->bindParam(":description", $description);
    $stmt->execute();

    self::sync_system_crontab();

    View::setFlash("success", "Tarea Cron actualizada correctamente.");
    header("Location: /cron");
    exit();
  }

  /**
   * Alterna el estado activo/pausado de una tarea.
   */
  public function toggle($id) {
    Auth::requireAuth();
    $connect = Database::getConnection();
    $id = (int)$id;

    $query = "SELECT status FROM cron_jobs WHERE id = :id LIMIT 1";
    $stmt  = $connect->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($job) {
      $new_status = ($job["status"] === "active") ? "paused" : "active";
      $query_up = "UPDATE cron_jobs SET status = :status WHERE id = :id";
      $stmt_up  = $connect->prepare($query_up);
      $stmt_up->bindParam(":status", $new_status);
      $stmt_up->bindParam(":id", $id);
      $stmt_up->execute();

      self::sync_system_crontab();
      View::setFlash("success", "Estado de la tarea cambiado a " . strtoupper($new_status) . ".");
    }

    header("Location: /cron");
    exit();
  }

  /**
   * Elimina una tarea cron.
   */
  public function delete($id) {
    Auth::requireAuth();
    $connect = Database::getConnection();
    $id = (int)$id;

    $query = "DELETE FROM cron_jobs WHERE id = :id";
    $stmt  = $connect->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->execute();

    self::sync_system_crontab();

    View::setFlash("success", "Tarea Cron eliminada correctamente.");
    header("Location: /cron");
    exit();
  }

  /**
   * Ejecuta inmediatamente el comando de una tarea y retorna el log vía JSON.
   */
  public function run_now($id) {
    Auth::requireAuth();
    $connect = Database::getConnection();
    $id = (int)$id;

    header("Content-Type: application/json");

    $query = "SELECT * FROM cron_jobs WHERE id = :id LIMIT 1";
    $stmt  = $connect->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
      echo json_encode(["status" => "error", "message" => "Tarea no encontrada"]);
      exit();
    }

    // Ejecutar mediante pirulu-cron run
    $clean_cmd = preg_replace("/>\s*\/dev\/null.*$/", "", $job["command"]);
    $res = Engine::execute("pirulu-cron", ["run", "admin", $clean_cmd]);

    echo json_encode($res);
    exit();
  }

  /**
   * Sincroniza todas las tareas activas de la base de datos con el crontab de Linux.
   */
  private static function sync_system_crontab() {
    $connect = Database::getConnection();

    $query = "SELECT * FROM cron_jobs WHERE status = 'active' ORDER BY id ASC";
    $stmt  = $connect->prepare($query);
    $stmt->execute();
    $active_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $crontab_lines = [];
    $crontab_lines[] = "# =========================================================";
    $crontab_lines[] = "# PIRULUGCP CRONTAB AUTOMATIC GENERATION - DO NOT EDIT MANUALLY";
    $crontab_lines[] = "# =========================================================";
    $crontab_lines[] = "SHELL=/bin/bash";
    $crontab_lines[] = "PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin";
    $crontab_lines[] = "";

    foreach ($active_jobs as $job) {
      $desc = !empty($job["description"]) ? "# Tarea ID #" . $job["id"] . ": " . $job["description"] : "# Tarea ID #" . $job["id"];
      $crontab_lines[] = $desc;
      $crontab_lines[] = $job["minute"] . " " . $job["hour"] . " " . $job["day"] . " " . $job["month"] . " " . $job["weekday"] . " " . $job["command"];
      $crontab_lines[] = "";
    }

    $crontab_content = implode("\n", $crontab_lines) . "\n";
    $b64_crontab     = base64_encode($crontab_content);

    Engine::execute("pirulu-cron", ["sync", "admin", $b64_crontab]);
  }
}
