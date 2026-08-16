<?php

namespace Pirulu\Modules\Web\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Database;
use Pirulu\Core\Engine;
use Pirulu\Core\View;

class WebController {
  public function index() {
    Auth::requireAuth();
    $db = Database::getConnection();

    $activeTab = $_GET["tab"] ?? "domains";
    if (!in_array($activeTab, ["domains", "logs", "nginx", "apache", "ssl", "ports"])) {
      $activeTab = "domains";
    }

    $domains = $db->query("
      SELECT d.*, u.username, g.id as git_id, g.branch as git_branch, g.last_deploy_status as git_status
      FROM domains d 
      LEFT JOIN users u ON d.user_id = u.id 
      LEFT JOIN domain_git g ON d.id = g.domain_id
      ORDER BY d.id DESC
    ")->fetchAll();

    foreach ($domains as &$d) {
      $uName = $d["username"] ?? "admin";
      $dName = $d["domain"];
      $wRoot = "/home/" . $uName . "/web/" . $dName;
      $dRoot = $wRoot . "/" . ($d["doc_root_suffix"] ?? "public_html");
      if (file_exists($wRoot . "/artisan") || file_exists($dRoot . "/../artisan") || file_exists($dRoot . "/artisan")) {
        $d["framework"] = "Laravel 12";
        $d["framework_logo"] = "/assets/sitios/laravel.svg";
      } elseif (file_exists($dRoot . "/wp-config.php") || file_exists($dRoot . "/wp-load.php")) {
        $d["framework"] = "WordPress";
        $d["framework_logo"] = "/assets/sitios/wordpress.svg";
      } elseif (file_exists($wRoot . "/package.json")) {
        $d["framework"] = "Node.js / SPA";
        $d["framework_logo"] = "/assets/sitios/php.svg";
      } else {
        $d["framework"] = "PHP Standard";
        $d["framework_logo"] = "/assets/sitios/php.svg";
      }
    }
    unset($d);

    $phpData = Engine::execute("pirulu-php", ["versions"]);
    $phpVersions = $phpData["versions"] ?? [];
    $users = $db->query("SELECT id, username FROM users ORDER BY username ASC")->fetchAll();

    // Logs web globales
    $logData = Engine::execute("pirulu-log", ["view", "/var/log/nginx/access.log", "60"]);
    $rawLogs = !empty($logData["raw_base64"]) ? base64_decode($logData["raw_base64"]) : "";

    View::render("Modules/Web/Views/index", [
      "pageTitle"   => "Gestor de Dominios Web - PiruluGCP",
      "domains"     => $domains,
      "users"       => $users,
      "phpVersions" => $phpVersions,
      "activeTab"   => $activeTab,
      "rawLogs"     => $rawLogs
    ]);
  }

  public function create() {
    Auth::requireAuth();
    $db = Database::getConnection();
    $users = $db->query("SELECT id, username FROM users ORDER BY username ASC")->fetchAll();

    $phpData = Engine::execute("pirulu-php", ["versions"]);
    $phpVersions = $phpData["versions"] ?? [];

    View::render("Modules/Web/Views/create", [
      "pageTitle"   => "Agregar Dominio Web - PiruluGCP",
      "users"       => $users,
      "phpVersions" => $phpVersions
    ]);
  }

  public function store() {
    Auth::requireAuth();
    $db = Database::getConnection();

    $domain = strtolower(trim($_POST["domain"] ?? ""));
    $userId = (int)($_POST["user_id"] ?? 0);
    $phpVersion = trim($_POST["php_version"] ?? "8.5");
    $docRootSuffix = trim($_POST["doc_root_suffix"] ?? "public_html");
    $docRootSuffix = ltrim($docRootSuffix, "/");
    if (empty($docRootSuffix)) {
      $docRootSuffix = "public_html";
    }

    if (empty($domain)) {
      View::setFlash("danger", "El nombre de dominio es obligatorio.");
      header("Location: /web/create");
      exit();
    }

    if ($userId === 0) {
      $curr = Auth::user();
      $userId = (int)($curr["id"] ?? 1);
    }

    $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userRow = $stmt->fetch();
    $username = $userRow["username"] ?? "admin";

    $res = Engine::execute("pirulu-web", ["add", $username, $domain, $phpVersion, $docRootSuffix]);

    if (isset($res["status"]) && $res["status"] === "success") {
      $stmt = $db->prepare("INSERT INTO domains (domain, user_id, php_version, doc_root_suffix, ssl_enabled) VALUES (?, ?, ?, ?, 0)");
      $stmt->execute([$domain, $userId, $phpVersion, $docRootSuffix]);
      $newId = $db->lastInsertId();
      View::setFlash("success", "Dominio " . $domain . " creado correctamente con PHP " . $phpVersion . ".");
      header("Location: /web/domain/" . (int)$newId);
      exit();
    } else {
      View::setFlash("danger", "Error al crear el dominio: " . ($res["raw_output"] ?? "Fallo"));
      header("Location: /web");
      exit();
    }
  }

  public function show($id) {
    Auth::requireAuth();
    $db = Database::getConnection();

    $stmt = $db->prepare("
      SELECT d.*, u.username, g.id as git_id, g.repo_url as git_repo, g.branch as git_branch, g.last_deploy_status as git_status
      FROM domains d 
      LEFT JOIN users u ON d.user_id = u.id 
      LEFT JOIN domain_git g ON d.id = g.domain_id
      WHERE d.id = ?
    ");
    $stmt->execute([(int)$id]);
    $domain = $stmt->fetch();

    if (!$domain) {
      View::setFlash("danger", "Dominio web no encontrado.");
      header("Location: /web");
      exit();
    }

    $username = $domain["username"] ?? "admin";
    $domainName = $domain["domain"];
    $docRoot = "/home/" . $username . "/web/" . $domainName . "/" . ($domain["doc_root_suffix"] ?? "public_html");
    $webRoot = "/home/" . $username . "/web/" . $domainName;

    // Deteccion precisa de Framework y soporte de .env / Tinker
    $hasArtisan = file_exists($webRoot . "/artisan") || file_exists($docRoot . "/../artisan") || file_exists($docRoot . "/artisan");
    $hasWp = file_exists($docRoot . "/wp-config.php") || file_exists($docRoot . "/wp-load.php");
    $hasEnv = file_exists($webRoot . "/.env") || file_exists($docRoot . "/.env");

    if ($hasArtisan) {
      $framework = "Laravel 12";
      $frameworkLogo = "/assets/sitios/laravel.svg";
      $hasEnv = true;
    } elseif ($hasWp) {
      $framework = "WordPress";
      $frameworkLogo = "/assets/sitios/wordpress.svg";
    } elseif (file_exists($webRoot . "/package.json")) {
      $framework = "Node.js / SPA";
      $frameworkLogo = "/assets/sitios/php.svg";
    } else {
      $framework = "PHP Standard";
      $frameworkLogo = "/assets/sitios/php.svg";
    }

    // Pestana activa y periodo
    $activeTab = $_GET["tab"] ?? "summary";
    $period = $_GET["period"] ?? "1h";

    // Restricciones de pestanas
    if ($activeTab === "env" && !$hasEnv) {
      $activeTab = "summary";
    }
    if ($activeTab === "tinker" && !$hasArtisan) {
      $activeTab = "summary";
    }

    // Lectura de archivo .env
    $envPath = $webRoot . "/.env";
    if (!file_exists($envPath)) {
      $envPath = $docRoot . "/.env";
    }
    $rawEnv = "";
    if (file_exists($envPath)) {
      $rawEnv = file_get_contents($envPath);
    } elseif ($hasArtisan) {
      $rawEnv = "APP_NAME=" . ucfirst(explode(".", $domainName)[0]) . "\n" .
                "APP_ENV=production\n" .
                "APP_KEY=\n" .
                "APP_DEBUG=false\n" .
                "APP_URL=https://" . $domainName . "\n\n" .
                "DB_CONNECTION=mariadb\n" .
                "DB_HOST=127.0.0.1\n" .
                "DB_PORT=3306\n" .
                "DB_DATABASE=" . $username . "_" . explode(".", $domainName)[0] . "\n" .
                "DB_USERNAME=" . $username . "_" . explode(".", $domainName)[0] . "\n" .
                "DB_PASSWORD=\n";
    }

    // Lectura de Logs de Nginx reales y exclusivos para este dominio
    $logPath = "/var/log/nginx/" . $domainName . "_access.log";
    $accessLogs = "";
    $accessLines = [];
    if (file_exists($logPath)) {
      $accessLogs = (string)file_get_contents($logPath);
      $accessLines = array_filter(explode("\n", trim($accessLogs)));
    }

    $errLogPath = "/var/log/nginx/" . $domainName . "_error.log";
    $errorLogs = "";
    if (file_exists($errLogPath)) {
      $errorLogs = (string)file_get_contents($errLogPath);
    }

    // Calculo de periodo de corte
    $now = time();
    $cutoff = 0;
    if ($period === "15m") {
      $cutoff = $now - (15 * 60);
    } elseif ($period === "1h") {
      $cutoff = $now - 3600;
    } elseif ($period === "24h") {
      $cutoff = $now - 86400;
    } elseif ($period === "7d") {
      $cutoff = $now - (7 * 86400);
    }

    $requestsList = [];
    $uniqueIps = [];
    $totalBytes = 0;
    $statusCounts = [
      "2xx" => 0,
      "3xx" => 0,
      "4xx" => 0,
      "5xx" => 0
    ];
    $routesMap = [];

    // Parsear lineas reales del log de acceso exclusivo de este dominio
    foreach ($accessLines as $line) {
      if (preg_match('/^(\S+)\s+-\s+\S+\s+\[([^\]]+)\]\s+"([A-Z]+)\s+([^"\s]+)[^"]*"\s+(\d{3})\s+(\d+)/', $line, $m)) {
        $ip = $m[1];
        $dateStr = $m[2];
        $method = $m[3];
        $uri = explode("?", $m[4])[0];
        $status = (int)$m[5];
        $bytes = (int)$m[6];

        $ts = strtotime(str_replace("/", " ", preg_replace('/:[0-9]{2}:[0-9]{2}:[0-9]{2}/', ' \0', $dateStr, 1)));
        if (!$ts) {
          $ts = $now;
        }

        if ($cutoff > 0 && $ts < $cutoff && count($accessLines) > 50) {
          continue;
        }

        $uniqueIps[$ip] = true;
        $totalBytes += $bytes;

        if ($status >= 200 && $status < 300) {
          $statusCounts["2xx"]++;
        } elseif ($status >= 300 && $status < 400) {
          $statusCounts["3xx"]++;
        } elseif ($status >= 400 && $status < 500) {
          $statusCounts["4xx"]++;
        } elseif ($status >= 500) {
          $statusCounts["5xx"]++;
        }

        if (!isset($routesMap[$uri])) {
          $routesMap[$uri] = [
            "method"      => $method,
            "route"       => $uri,
            "count"       => 0,
            "last_status" => $status,
            "bytes"       => 0
          ];
        }
        $routesMap[$uri]["count"]++;
        $routesMap[$uri]["bytes"] += $bytes;

        $requestsList[] = [
          "ip"        => $ip,
          "date"      => $dateStr,
          "method"    => $method,
          "uri"       => $uri,
          "status"    => $status,
          "bytes"     => $bytes,
          "bytes_fmt" => ($bytes > 1048576) ? round($bytes / 1048576, 2) . " MB" : (($bytes > 1024) ? round($bytes / 1024, 1) . " KB" : $bytes . " B")
        ];
      }
    }

    $totalReq = count($requestsList);
    $errorsCount = $statusCounts["4xx"] + $statusCounts["5xx"];
    $errorRate = ($totalReq > 0) ? round(($errorsCount / $totalReq) * 100, 1) : 0.0;
    $bandwidthFmt = ($totalBytes > 1073741824) ? round($totalBytes / 1073741824, 2) . " GB" : (($totalBytes > 1048576) ? round($totalBytes / 1048576, 2) . " MB" : round($totalBytes / 1024, 1) . " KB");

    // Ordenar rutas por volumen de peticiones
    uasort($routesMap, function($a, $b) {
      return $b["count"] <=> $a["count"];
    });
    $topRoutes = array_slice($routesMap, 0, 10);

    // Calcular porcentajes de rutas
    foreach ($topRoutes as &$tr) {
      $tr["pct"] = ($totalReq > 0) ? round(($tr["count"] / $totalReq) * 100, 1) : 0;
      $tr["bytes_fmt"] = ($tr["bytes"] > 1048576) ? round($tr["bytes"] / 1048576, 2) . " MB" : (($tr["bytes"] > 1024) ? round($tr["bytes"] / 1024, 1) . " KB" : $tr["bytes"] . " B");
    }
    unset($tr);

    $recentRequests = array_slice(array_reverse($requestsList), 0, 150);

    // Espacio en disco real y exclusivo del dominio
    $diskSize = "0 KB";
    $duOut = @shell_exec("du -sh " . escapeshellarg($webRoot) . " 2>/dev/null");
    if ($duOut && preg_match('/^([^\s]+)/', trim($duOut), $dm)) {
      $diskSize = $dm[1];
    }

    $metrics = [
      "total_requests"   => $totalReq,
      "unique_visitors"  => count($uniqueIps),
      "bandwidth"        => $bandwidthFmt,
      "error_rate"       => $errorRate,
      "errors_count"     => $errorsCount,
      "disk_size"        => $diskSize,
      "status_counts"    => $statusCounts,
      "status_pct"       => [
        "2xx" => ($totalReq > 0) ? round(($statusCounts["2xx"] / $totalReq) * 100, 1) : 0,
        "3xx" => ($totalReq > 0) ? round(($statusCounts["3xx"] / $totalReq) * 100, 1) : 0,
        "4xx" => ($totalReq > 0) ? round(($statusCounts["4xx"] / $totalReq) * 100, 1) : 0,
        "5xx" => ($totalReq > 0) ? round(($statusCounts["5xx"] / $totalReq) * 100, 1) : 0
      ],
      "top_routes"       => $topRoutes,
      "recent_requests"  => $recentRequests
    ];

    // -------------------------------------------------------------------------
    // SECCION: DEPURACION Y CAPTURA DE CONSULTAS SQL EXCLUSIVAS DE ESTE DOMINIO
    // -------------------------------------------------------------------------
    $isSqlCaptureActive = false;
    try {
      $sqlCheck = Engine::execute("pirulu-db", ["status-query-log"]);
      $isSqlCaptureActive = !empty($sqlCheck["enabled"]);
    } catch (\Exception $e) {
      $isSqlCaptureActive = false;
    }

    // Detectar bases de datos y usuarios configurados exclusivamente para este dominio
    $domainDbs = [];

    // A. Buscar en wp-config.php o config.php del dominio
    $searchFiles = [
      $webRoot . "/wp-config.php",
      $docRoot . "/wp-config.php",
      $webRoot . "/config.php",
      $docRoot . "/config.php",
      $webRoot . "/config.inc.php",
      $docRoot . "/config.inc.php"
    ];
    foreach ($searchFiles as $cfgFile) {
      if (file_exists($cfgFile)) {
        $cfgContent = (string)@file_get_contents($cfgFile);
        if (preg_match('/(?:DB_NAME|DATABASE_NAME|DB_DATABASE)[\'"]?\s*(?:=|,)\s*[\'"]([^\'"]+)[\'"]/i', $cfgContent, $dbm)) {
          $domainDbs[] = trim($dbm[1]);
        }
        if (preg_match('/(?:DB_USER|DATABASE_USER|DB_USERNAME)[\'"]?\s*(?:=|,)\s*[\'"]([^\'"]+)[\'"]/i', $cfgContent, $dbu)) {
          $domainDbs[] = trim($dbu[1]);
        }
      }
    }

    // B. Buscar en .env del dominio
    foreach ([$webRoot . "/.env", $docRoot . "/.env"] as $envFile) {
      if (file_exists($envFile)) {
        $envContent = (string)@file_get_contents($envFile);
        if (preg_match('/DB_DATABASE=([^\s\r\n]+)/i', $envContent, $dbm)) {
          $domainDbs[] = trim($dbm[1], "\"'");
        }
        if (preg_match('/DB_USERNAME=([^\s\r\n]+)/i', $envContent, $dbu)) {
          $domainDbs[] = trim($dbu[1], "\"'");
        }
      }
    }

    // C. Buscar bases de datos creadas en el panel con nombre relacionado al dominio
    $domainClean = str_replace(["-", "."], "_", explode(".", $domainName)[0]);
    $stmt = $db->prepare("SELECT db_name, db_user FROM databases WHERE user_id = ?");
    $stmt->execute([(int)($domain["user_id"] ?? 1)]);
    $allUserDbs = $stmt->fetchAll();
    foreach ($allUserDbs as $udb) {
      if (stripos($udb["db_name"], $domainClean) !== false || count($allUserDbs) === 1) {
        $domainDbs[] = $udb["db_name"];
        $domainDbs[] = $udb["db_user"];
      }
    }

    $domainDbs = array_values(array_unique(array_filter($domainDbs)));

    // Lectura del log de consultas de MariaDB a traves de Engine
    $rawSlowLog = "";
    $slowData = Engine::execute("pirulu-db", ["read-slow-log", "500"]);
    if (!empty($slowData["raw_base64"])) {
      $rawSlowLog = (string)base64_decode($slowData["raw_base64"]);
    }

    $parsedDbQueries = [];
    if (!empty($rawSlowLog) && !empty($domainDbs)) {
      $blocks = preg_split('/(?=# User@Host:)/', $rawSlowLog);
      foreach ($blocks as $b) {
        if (empty(trim($b))) continue;

        $schema = "";
        $timeMs = "0.00 ms";
        $timestamp = time();
        $sqlLines = [];

        if (preg_match('/Schema:\s*([^\s]+)/', $b, $sm)) {
          $schema = $sm[1];
        }
        if (preg_match('/Query_time:\s*([\d\.]+)/', $b, $qtm)) {
          $timeMs = round(((float)$qtm[1]) * 1000, 2) . " ms";
        }
        if (preg_match('/SET timestamp=(\d+);/', $b, $tsm)) {
          $timestamp = (int)$tsm[1];
        }

        $lines = explode("\n", $b);
        foreach ($lines as $ln) {
          $lnTrim = trim($ln);
          if (empty($lnTrim) || str_starts_with($lnTrim, "#") || str_starts_with($lnTrim, "SET timestamp=") || str_starts_with($lnTrim, "use ")) {
            continue;
          }
          $sqlLines[] = $lnTrim;
        }

        if (!empty($sqlLines)) {
          $fullSql = implode(" ", $sqlLines);
          $isMatch = false;

          // 1. Coincidencia por esquema DB exacto
          if (!empty($schema) && in_array($schema, $domainDbs, true)) {
            $isMatch = true;
          }

          // 2. Coincidencia por usuario DB del dominio
          if (!$isMatch) {
            foreach ($domainDbs as $dbId) {
              if (preg_match('/User@Host:\s*' . preg_quote($dbId, '/') . '\[/', $b)) {
                $isMatch = true;
                break;
              }
            }
          }

          // 3. Coincidencia por sentencia SQL referenciando la DB del dominio
          if (!$isMatch) {
            foreach ($domainDbs as $dbId) {
              if (stripos($fullSql, "`" . $dbId . "`") !== false || stripos($fullSql, " " . $dbId . ".") !== false) {
                $isMatch = true;
                break;
              }
            }
          }

          if ($isMatch) {
            $parsedDbQueries[] = [
              "sql"       => $fullSql,
              "time_ms"   => $timeMs,
              "timestamp" => $timestamp,
              "time_fmt"  => date("h:i:s a", $timestamp),
              "schema"    => !empty($schema) ? $schema : ($domainDbs[0] ?? "")
            ];
          }
        }
      }
    }

    // Calcular duplicados para deteccion de N+1
    $sqlCounts = [];
    foreach ($parsedDbQueries as $pq) {
      $normalizedSql = preg_replace('/\'[^\']*\'|\b\d+\b/', '?', $pq["sql"]);
      $sqlCounts[$normalizedSql] = ($sqlCounts[$normalizedSql] ?? 0) + 1;
    }

    $realQueriesTraces = [];
    $recentDbQueries = array_slice(array_reverse($parsedDbQueries), 0, 40);
    if (!empty($recentDbQueries)) {
      $hasNplus = false;
      $stmtList = [];
      foreach ($recentDbQueries as $rq) {
        $normalizedSql = preg_replace('/\'[^\']*\'|\b\d+\b/', '?', $rq["sql"]);
        $cnt = $sqlCounts[$normalizedSql] ?? 1;
        if ($cnt > 1) {
          $hasNplus = true;
        }
        $stmtList[] = [
          "sql"     => $rq["sql"],
          "time_ms" => $rq["time_ms"],
          "count"   => $cnt,
          "schema"  => $rq["schema"]
        ];
      }

      $realQueriesTraces[] = [
        "method"     => "DB",
        "route"      => "MariaDB (" . (!empty($domainDbs[0]) ? $domainDbs[0] : $domainName) . ")",
        "time"       => $recentDbQueries[0]["time_fmt"] ?? date("h:i:s a"),
        "count"      => count($stmtList),
        "total_ms"   => count($stmtList) . " consultas capturadas",
        "has_nplus"  => $hasNplus,
        "statements" => $stmtList
      ];
    }

    // Telemetria personalizada de consultas si existe
    $debugFile = $webRoot . "/storage/pirulugcp_debug.json";
    $hasDebugFile = file_exists($debugFile);
    $customDebug = [];
    if ($hasDebugFile) {
      $content = file_get_contents($debugFile);
      $customDebug = json_decode($content, true) ?? [];
    }

    $finalQueries = !empty($realQueriesTraces) ? $realQueriesTraces : ($customDebug["queries"] ?? []);
    $totalQueryCount = 0;
    foreach ($finalQueries as $fq) {
      $totalQueryCount += count($fq["statements"] ?? []);
    }

    $debugData = [
      "has_data"              => !empty($finalQueries),
      "is_sql_capture_active" => $isSqlCaptureActive,
      "counts"                => [
        "queries" => $totalQueryCount
      ],
      "queries"               => $finalQueries
    ];

    $phpData = Engine::execute("pirulu-php", ["versions"]);
    $phpVersions = $phpData["versions"] ?? [];

    // -------------------------------------------------------------------------
    // SECCION: ESTADO E INSPECCION DEL CERTIFICADO SSL (LET'S ENCRYPT / CLOUDFLARE)
    // -------------------------------------------------------------------------
    $sslInfo = [
      "ssl_active"            => !empty($domain["ssl_enabled"]),
      "domain"                => $domainName,
      "issuer"                => "Sin Certificado",
      "subject"               => "CN=" . $domainName,
      "valid_from"            => null,
      "expires"               => null,
      "days_left"             => 0,
      "san"                   => "DNS:" . $domainName,
      "type"                  => "Sin SSL",
      "cloudflare_compatible" => true,
      "cert_pem"              => "",
      "key_pem"               => ""
    ];
    try {
      $sslCheck = Engine::execute("pirulu-ssl", ["details", $domainName]);
      if (!empty($sslCheck["status"]) && $sslCheck["status"] === "success") {
        $sslInfo = array_merge($sslInfo, $sslCheck);
      }
    } catch (\Exception $e) {
      // Ignorar fallback
    }

    View::render("Modules/Web/Views/show", [
      "pageTitle"     => $domainName . " - Panel y Métricas de la Aplicación",
      "domain"        => $domain,
      "framework"     => $framework,
      "frameworkLogo" => $frameworkLogo,
      "hasArtisan"    => $hasArtisan,
      "hasEnv"        => $hasEnv,
      "activeTab"     => $activeTab,
      "period"        => $period,
      "metrics"       => $metrics,
      "debugData"     => $debugData,
      "sslInfo"       => $sslInfo,
      "docRoot"       => $docRoot,
      "webRoot"       => $webRoot,
      "rawEnv"        => $rawEnv,
      "accessLogs"    => $accessLogs,
      "errorLogs"     => $errorLogs,
      "phpVersions"   => $phpVersions
    ]);
  }

  public function toggleSqlCapture($id) {
    Auth::requireAuth();

    $sqlCheck = Engine::execute("pirulu-db", ["status-query-log"]);
    $isActive = !empty($sqlCheck["enabled"]);

    if ($isActive) {
      Engine::execute("pirulu-db", ["disable-query-log"]);
      View::setFlash("success", "Captura de consultas SQL desactivada.");
    } else {
      Engine::execute("pirulu-db", ["enable-query-log"]);
      View::setFlash("success", "Captura en vivo de consultas SQL activada en MariaDB.");
    }

    header("Location: /web/domain/" . (int)$id . "?tab=debug");
    exit();
  }

  public function clearDebug($id) {
    Auth::requireAuth();
    $db = Database::getConnection();

    $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
    $stmt->execute([(int)$id]);
    $domain = $stmt->fetch();

    if ($domain) {
      $username = $domain["username"] ?? "admin";
      $domainName = $domain["domain"];
      $webRoot = "/home/" . $username . "/web/" . $domainName;
      $debugFile = $webRoot . "/storage/pirulugcp_debug.json";
      if (file_exists($debugFile)) {
        @unlink($debugFile);
      }
      Engine::execute("pirulu-db", ["clear-queries"]);
      View::setFlash("success", "Consultas SQL de depuración reiniciadas.");
    }

    header("Location: /web/domain/" . (int)$id . "?tab=debug");
    exit();
  }

  public function saveEnv($id) {
    Auth::requireAuth();
    $db = Database::getConnection();

    $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
    $stmt->execute([(int)$id]);
    $domain = $stmt->fetch();

    if (!$domain) {
      View::setFlash("danger", "Dominio no encontrado.");
      header("Location: /web");
      exit();
    }

    $username = $domain["username"] ?? "admin";
    $domainName = $domain["domain"];
    $webRoot = "/home/" . $username . "/web/" . $domainName;
    $envContent = $_POST["env_content"] ?? "";

    $envPath = $webRoot . "/.env";
    if (!is_dir($webRoot)) {
      mkdir($webRoot, 0755, true);
    }
    file_put_contents($envPath, $envContent);
    chown($envPath, $username);

    View::setFlash("success", "Variables de entorno (.env) guardadas exitosamente.");
    header("Location: /web/domain/" . (int)$id . "?tab=env");
    exit();
  }

  public function runTinker($id) {
    Auth::requireAuth();
    $db = Database::getConnection();

    $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
    $stmt->execute([(int)$id]);
    $domain = $stmt->fetch();

    if (!$domain) {
      View::setFlash("danger", "Dominio no encontrado.");
      header("Location: /web");
      exit();
    }

    $username = $domain["username"] ?? "admin";
    $domainName = $domain["domain"];
    $cmd = trim($_POST["command"] ?? "");

    if (!empty($cmd)) {
      $webRoot = "/home/" . $username . "/web/" . $domainName;
      $phpVer = $domain["php_version"] ?? "8.5";
      $phpBin = "php" . $phpVer;

      if (file_exists($webRoot . "/artisan")) {
        $fullCmd = "cd " . escapeshellarg($webRoot) . " && " . escapeshellcmd($phpBin) . " artisan " . escapeshellcmd($cmd) . " 2>&1";
      } else {
        $fullCmd = "cd " . escapeshellarg($webRoot) . " && " . escapeshellcmd($phpBin) . " -r " . escapeshellarg($cmd) . " 2>&1";
      }

      $output = shell_exec($fullCmd);
      $_SESSION["tinker_last_output"] = $output;
    }

    header("Location: /web/domain/" . (int)$id . "?tab=tinker");
    exit();
  }

  public function updateDocRoot() {
    Auth::requireAuth();
    $db = Database::getConnection();

    $domainId = (int)($_POST["domain_id"] ?? 0);
    $newSuffix = trim($_POST["doc_root_suffix"] ?? "public_html");
    $newSuffix = ltrim($newSuffix, "/");
    if (empty($newSuffix)) {
      $newSuffix = "public_html";
    }

    $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
    $stmt->execute([$domainId]);
    $domainRow = $stmt->fetch();

    if ($domainRow) {
      $username = $domainRow["username"] ?? "admin";
      $domain = $domainRow["domain"];

      $res = Engine::execute("pirulu-web", ["set-docroot", $username, $domain, $newSuffix]);

      if (isset($res["status"]) && $res["status"] === "success") {
        $stmt = $db->prepare("UPDATE domains SET doc_root_suffix = ? WHERE id = ?");
        $stmt->execute([$newSuffix, $domainId]);
        View::setFlash("success", "Carpeta raiz actualizada a " . $newSuffix . " para " . $domain . ".");
      } else {
        View::setFlash("danger", "Error al cambiar carpeta raiz: " . ($res["raw_output"] ?? "Error"));
      }
    }

    header("Location: /web");
    exit();
  }

  public function updatePhp() {
    Auth::requireAuth();
    $db = Database::getConnection();

    $domainId = (int)($_POST["domain_id"] ?? 0);
    $newPhp = trim($_POST["php_version"] ?? "");

    $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
    $stmt->execute([$domainId]);
    $domainRow = $stmt->fetch();

    if ($domainRow && !empty($newPhp)) {
      $username = $domainRow["username"] ?? "admin";
      $domain = $domainRow["domain"];

      $res = Engine::execute("pirulu-web", ["set-php", $username, $domain, $newPhp]);

      if (isset($res["status"]) && $res["status"] === "success") {
        $stmt = $db->prepare("UPDATE domains SET php_version = ? WHERE id = ?");
        $stmt->execute([$newPhp, $domainId]);
        View::setFlash("success", "Version de PHP actualizada a " . $newPhp . " para " . $domain . ".");
      } else {
        View::setFlash("danger", "Error al cambiar version de PHP.");
      }
    }

    header("Location: /web");
    exit();
  }

  public function enableSsl($id) {
    Auth::requireAuth();
    $db = Database::getConnection();

    $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
    $stmt->execute([(int)$id]);
    $domainRow = $stmt->fetch();

    if ($domainRow) {
      $username = $domainRow["username"] ?? "admin";
      $domain = $domainRow["domain"];

      $res = Engine::execute("pirulu-ssl", ["issue", $username, $domain]);

      if (isset($res["status"]) && $res["status"] === "success") {
        $stmt = $db->prepare("UPDATE domains SET ssl_enabled = 1 WHERE id = ?");
        $stmt->execute([(int)$id]);
        View::setFlash("success", "Certificado SSL Let's Encrypt instalado y activado para " . $domain . ".");
      } else {
        View::setFlash("danger", "Error al obtener certificado SSL: " . ($res["raw_output"] ?? "Verifica que el DNS apunte al servidor."));
      }
    }

    header("Location: /web");
    exit();
  }

  public function disableSsl($id) {
    Auth::requireAuth();
    $db = Database::getConnection();

    $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
    $stmt->execute([(int)$id]);
    $domainRow = $stmt->fetch();

    if ($domainRow) {
      $username = $domainRow["username"] ?? "admin";
      $domain = $domainRow["domain"];

      Engine::execute("pirulu-ssl", ["delete", $username, $domain]);

      $stmt = $db->prepare("UPDATE domains SET ssl_enabled = 0 WHERE id = ?");
      $stmt->execute([(int)$id]);

      View::setFlash("info", "Certificado SSL deshabilitado para " . $domain . ".");
    }

    header("Location: /web");
    exit();
  }

  public function delete($id) {
    Auth::requireAuth();
    $db = Database::getConnection();

    $stmt = $db->prepare("SELECT d.*, u.username FROM domains d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
    $stmt->execute([(int)$id]);
    $domainRow = $stmt->fetch();

    if ($domainRow) {
      $username = $domainRow["username"] ?? "admin";
      $domain = $domainRow["domain"];

      Engine::execute("pirulu-web", ["delete", $username, $domain, $domainRow["php_version"]]);

      $stmt = $db->prepare("DELETE FROM domains WHERE id = ?");
      $stmt->execute([(int)$id]);

      View::setFlash("success", "Dominio " . $domain . " eliminado correctamente.");
    }

    header("Location: /web");
    exit();
  }
}
