<?php

namespace Pirulu\Core;

class Engine {
  private static $engineBinDir = "/usr/local/pirulugcp/engine/bin";

  public static function execute($binary, $args = []) {
    $binPath = self::$engineBinDir . "/" . $binary;

    // Si estamos en entorno de desarrollo local o Windows, devolver respuesta simulada
    if (!file_exists($binPath) || strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
      return self::simulate($binary, $args);
    }

    $escapedArgs = array_map("escapeshellarg", $args);
    $command = "sudo -n " . escapeshellcmd($binPath) . " " . implode(" ", $escapedArgs) . " 2>&1";

    $output = shell_exec($command);
    $lines = explode("\n", trim($output ?? ""));

    // Buscar el ultimo objeto JSON valido en la salida
    for ($i = count($lines) - 1; $i >= 0; $i--) {
      $line = trim($lines[$i]);
      if (!empty($line) && ($line[0] === "{" || $line[0] === "[")) {
        $decoded = json_decode($line, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
          return $decoded;
        }
      }
    }

    return [
      "status" => ($output !== null && strpos($output, "error") === false) ? "success" : "error",
      "raw_output" => $output ?? "Sin respuesta del sistema"
    ];
  }

  public static function executeRaw($binary, $args = []) {
    $binPath = self::$engineBinDir . "/" . $binary;

    if (!file_exists($binPath) || strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
      return "[Modo Simulacion] Registros de prueba para " . ($args[1] ?? $binary) . "\n" . date("Y-m-d H:i:s") . " [INFO] Servicio operando con normalidad en modo desarrollo.";
    }

    $escapedArgs = array_map("escapeshellarg", $args);
    $command = "sudo " . escapeshellcmd($binPath) . " " . implode(" ", $escapedArgs) . " 2>&1";

    $output = shell_exec($command);
    return $output ?? "No se encontraron registros.";
  }

  private static function simulate($binary, $args) {
    $action = $args[0] ?? "";

    if ($binary === "pirulu-system") {
      if ($action === "status") {
        return [
          "status" => "success",
          "services" => [
            "nginx" => "active",
            "apache" => "active",
            "mariadb" => "active",
            "vsftpd" => "active",
            "fail2ban" => "active",
            "php_fpm" => [
              ["service" => "php7.4-fpm", "version" => "7.4", "status" => "inactive"],
              ["service" => "php8.1-fpm", "version" => "8.1", "status" => "active"],
              ["service" => "php8.2-fpm", "version" => "8.2", "status" => "active"],
              ["service" => "php8.3-fpm", "version" => "8.3", "status" => "active"]
            ]
          ]
        ];
      }
      if ($action === "metrics") {
        return [
          "status" => "success",
          "metrics" => [
            "hostname" => gethostname() ?: "server.pirulugcp.local",
            "uptime" => "up 3 days, 4 hours",
            "load" => "0.15, 0.22, 0.18",
            "memory" => [
              "total_mb" => 4096,
              "used_mb" => 1280,
              "free_mb" => 2816
            ],
            "disk" => [
              "total" => "50G",
              "used" => "12G",
              "free" => "38G",
              "percent" => "24%"
            ]
          ]
        ];
      }
      return [
        "status" => "success",
        "message" => "Accion " . ($args[2] ?? "") . " ejecutada en modo simulacion"
      ];
    }

    if ($binary === "pirulu-db") {
      if ($action === "status-query-log") {
        return [
          "status"  => "success",
          "enabled" => false
        ];
      }
      if ($action === "read-slow-log") {
        return [
          "status"     => "success",
          "raw_base64" => base64_encode("# User@Host: admin_wpdb[admin_wpdb] @ localhost []\n# Thread_id: 10 Schema: admin_wpdb\n# Query_time: 0.000450 Lock_time: 0.000050 Rows_sent: 1 Rows_examined: 1\nSET timestamp=" . time() . ";\nSELECT * FROM usuarios ORDER BY id DESC;")
        ];
      }
      if ($action === "enable-query-log" || $action === "disable-query-log") {
        return [
          "status"  => "success",
          "message" => "Estado de captura de consultas SQL actualizado en MariaDB"
        ];
      }
      if ($action === "clear-queries") {
        return [
          "status"  => "success",
          "message" => "Registro de consultas SQL limpiado exitosamente"
        ];
      }
      if ($action === "dump") {
        return [
          "status" => "success",
          "file"   => "/var/backups/pirulugcp/databases/" . ($args[1] ?? "db") . ".sql",
          "size"   => 204850
        ];
      }
      if ($action === "logs") {
        return [
          "status"     => "success",
          "raw_base64" => base64_encode("MariaDB 10.11.8 Server ready for connections.\nVersion: '10.11.8-MariaDB-0ubuntu0.24.04.1' socket: '/run/mysqld/mysqld.sock' port: 3306")
        ];
      }
      if ($action === "get-config") {
        return [
          "status"     => "success",
          "file"       => "/etc/mysql/mariadb.conf.d/50-server.cnf",
          "raw_base64" => base64_encode("[mysqld]\nuser = mysql\npid-file = /run/mysqld/mysqld.pid\nbasedir = /usr\ndatadir = /var/lib/mysql\ntmpdir = /tmp\nbind-address = 127.0.0.1\nmax_connections = 150\nslow_query_log = 1\nlong_query_time = 0\n")
        ];
      }
      if ($action === "save-config") {
        return [
          "status"  => "success",
          "message" => "Configuracion de MariaDB guardada y servicio reiniciado exitosamente"
        ];
      }
      return [
        "status"  => "success",
        "message" => "Operacion de base de datos ejecutada en modo simulacion"
      ];
    }

    if ($binary === "pirulu-ftp") {
      if ($action === "status") {
        return [
          "status" => "success",
          "vsftpd" => "active"
        ];
      }
      if ($action === "account-add") {
        return [
          "status"   => "success",
          "message"  => "Cuenta FTP " . ($args[1] ?? "usuario") . " creada exitosamente",
          "ftp_user" => $args[1] ?? "usuario",
          "path"     => "/home/" . ($args[3] ?? "admin") . "/web/" . ($args[4] ?? "midominio.com") . "/" . ($args[5] ?? "public_html")
        ];
      }
      if ($action === "account-del") {
        return [
          "status"  => "success",
          "message" => "Cuenta FTP eliminada exitosamente"
        ];
      }
      if ($action === "account-passwd") {
        return [
          "status"  => "success",
          "message" => "Contraseña de cuenta FTP actualizada exitosamente"
        ];
      }
      if ($action === "account-path") {
        return [
          "status"  => "success",
          "message" => "Ruta de acceso FTP actualizada exitosamente"
        ];
      }
      return [
        "status"  => "success",
        "message" => "Operacion FTP completada en modo simulacion"
      ];
    }

    if ($binary === "pirulu-php") {
      if ($action === "get-ini") {
        $ver = $args[1] ?? "8.2";
        $sampleIni = ";;;;;;;;;;;;;;;;;;;\n; About php.ini   ;\n;;;;;;;;;;;;;;;;;;;\n; PHP's initialization file, generally called php.ini, is responsible for\n; configuring many of the aspects of PHP's behavior.\n\n[PHP]\nmax_execution_time = 60\nmax_input_time = 60\nmemory_limit = 128M\nerror_reporting = E_ALL & ~E_DEPRECATED\ndisplay_errors = Off\npost_max_size = 200M\nupload_max_filesize = 200M\n";
        return [
          "status"              => "success",
          "version"             => $ver,
          "ini_file"            => "/etc/php/{$ver}/fpm/php.ini",
          "max_execution_time"  => "60",
          "max_input_time"      => "60",
          "memory_limit"        => "128M",
          "error_reporting"     => "E_ALL & ~E_DEPRECATED",
          "display_errors"      => "Off",
          "post_max_size"       => "200M",
          "upload_max_filesize" => "200M",
          "raw_base64"          => base64_encode($sampleIni)
        ];
      }
      if ($action === "set-ini" || $action === "save-raw-ini") {
        return [
          "status"  => "success",
          "message" => "Configuración de PHP actualizada y servicio reiniciado exitosamente"
        ];
      }
      if ($action === "install") {
        $ver = $args[1] ?? "8.4";
        return [
          "status"  => "success",
          "message" => "PHP {$ver} instalado y activado exitosamente"
        ];
      }
      if ($action === "remove" || $action === "uninstall") {
        $ver = $args[1] ?? "8.4";
        return [
          "status"  => "success",
          "message" => "PHP {$ver} desinstalado exitosamente del servidor"
        ];
      }
      if ($action === "restart") {
        $ver = $args[1] ?? "8.5";
        return [
          "status"  => "success",
          "message" => "PHP-FPM {$ver} reiniciado"
        ];
      }
      if ($action === "logs") {
        $ver = $args[1] ?? "8.5";
        $logs = "[2026-08-16 17:15:25] 127.0.0.1 \"GET /\" 200 138ms\n" .
                "[2026-08-16 17:15:28] 127.0.0.1 \"GET /products\" 200 70ms\n" .
                "[2026-08-16 17:15:34] 127.0.0.1 \"POST /checkout\" 302 199ms\n" .
                "[2026-08-16 17:15:37] PHP Warning: Undefined array key \"coupon\" on line 52\n" .
                "[2026-08-16 17:15:39] 127.0.0.1 \"GET /orders/42\" 200 233ms\n" .
                "[2026-08-16 17:18:00] NOTICE: ready to handle connections\n" .
                "[2026-08-16 17:18:00] NOTICE: fpm is running, pid 236";
        return [
          "status"     => "success",
          "version"    => $ver,
          "log_file"   => "/var/log/php{$ver}-fpm.log",
          "raw_base64" => base64_encode($logs)
        ];
      }
      if ($action === "extensions") {
        $ver = $args[1] ?? "8.5";
        return [
          "status"     => "success",
          "version"    => $ver,
          "extensions" => [
            "bcmath", "calendar", "Core", "ctype", "curl", "date", "dom", "exif",
            "FFI", "fileinfo", "filter", "ftp", "gd", "gettext", "hash", "iconv",
            "imagick", "intl", "json", "libxml", "mbstring", "mysqli", "mysqlnd",
            "openssl", "pcre", "PDO", "pdo_mysql", "pdo_sqlite", "Phar", "posix",
            "readline", "Reflection", "session", "shmop", "SimpleXML", "sockets",
            "sodium", "SPL", "sqlite3", "standard", "sysvmsg", "sysvsem", "sysvshm",
            "tokenizer", "xml", "xmlreader", "xmlwriter", "xsl", "zip", "zlib"
          ]
        ];
      }
      return [
        "status" => "success",
        "versions" => [
          ["version" => "7.4", "installed" => false, "status" => "inactive"],
          ["version" => "8.0", "installed" => false, "status" => "inactive"],
          ["version" => "8.1", "installed" => false, "status" => "inactive"],
          ["version" => "8.2", "installed" => false, "status" => "inactive"],
          ["version" => "8.3", "installed" => false, "status" => "inactive"],
          ["version" => "8.4", "installed" => false, "status" => "inactive"],
          ["version" => "8.5", "installed" => true,  "status" => "active"]
        ]
      ];
    }

    if ($binary === "pirulu-firewall") {
      if ($action === "status") {
        return [
          "status"   => "success",
          "fail2ban" => ["status" => "active", "banned_count" => 2],
          "iptables" => ["available" => true, "drop_rules" => 1]
        ];
      }
      if ($action === "f2b-jails") {
        return [
          "status" => "success",
          "jails"  => [
            [
              "jail"             => "sshd",
              "currently_banned" => 2,
              "total_banned"     => 5,
              "banned_ips"       => ["203.0.113.42", "198.51.100.7"]
            ],
            [
              "jail"             => "nginx-http-auth",
              "currently_banned" => 0,
              "total_banned"     => 1,
              "banned_ips"       => []
            ]
          ]
        ];
      }
      if ($action === "ipt-list") {
        return [
          "status" => "success",
          "rules"  => ["203.0.113.99"]
        ];
      }
      return [
        "status"  => "success",
        "message" => "Accion de firewall ejecutada en modo simulacion"
      ];
    }

    if ($binary === "pirulu-git") {
      if ($action === "generate-key" || $action === "get-key") {
        return [
          "status"     => "success",
          "public_key" => "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIGitDeployKeySimulatedForPiruluGCPExampleKey deploy@pirulugcp",
          "key_path"   => "/home/admin/.ssh/id_deploy_example"
        ];
      }
      if ($action === "status") {
        return [
          "status"         => "success",
          "connected"      => true,
          "remote_url"     => "https://github.com/example/repo.git",
          "branch"         => "main",
          "commit_hash"    => "a1b2c3d4e5f67890123456789abcdef012345678",
          "commit_author"  => "Developer",
          "commit_message" => "Update production code",
          "commit_date"    => date("Y-m-d H:i:s")
        ];
      }
      return [
        "status"         => "success",
        "message"        => "Operacion Git completada en modo simulacion",
        "commit_hash"    => "a1b2c3d4e5f67890123456789abcdef012345678",
        "commit_author"  => "Developer",
        "commit_message" => "Update production code",
        "log"            => "Already up to date."
      ];
    }

    if ($binary === "pirulu-composer") {
      if ($action === "status") {
        return [
          "status"             => "success",
          "composer_installed" => true,
          "composer_version"   => "Composer version 2.7.7 2024-06-10 15:43:28",
          "php_binary"         => "/usr/bin/php",
          "has_composer_json"  => true,
          "has_composer_lock"  => true,
          "has_vendor"         => true,
          "has_autoload"       => true
        ];
      }
      return [
        "status"  => "success",
        "message" => "Operacion de Composer ejecutada en modo simulacion",
        "log"     => "Loading composer repositories with package information\nInstalling dependencies from lock file\nGenerating autoload files\nGenerated autoload files"
      ];
    }

    if ($binary === "pirulu-cron") {
      if ($action === "status") {
        return [
          "status"       => "success",
          "cron_service" => "active"
        ];
      }
      if ($action === "run") {
        return [
          "status"       => "success",
          "exit_code"    => 0,
          "duration_sec" => 1,
          "output"       => "Ejecución simulada exitosa de comando Cron."
        ];
      }
      return [
        "status"  => "success",
        "message" => "Crontab sincronizado correctamente"
      ];
    }

    if ($binary === "pirulu-server") {
      if ($action === "get-config") {
        return [
          "status"        => "success",
          "hostname"      => "panel.pirulugcp.local",
          "timezone"      => "America/Lima",
          "current_time"  => date("Y-m-d H:i:s T"),
          "server_ip"     => "192.168.1.100",
          "public_key"    => "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIPanelDeployKeySimulatedForPiruluGCPExampleKey panel-deploy@pirulugcp",
          "git_connected" => true,
          "remote_url"    => "git@github.com:usuario/pirulugcp.git",
          "branch"        => "main",
          "last_commit"   => "b487976e1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d",
          "last_author"   => "Pirulug",
          "last_message"  => "feat(server): Server configuration and panel updates"
        ];
      }
      if ($action === "generate-key" || $action === "get-key") {
        return [
          "status"     => "success",
          "public_key" => "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIPanelDeployKeySimulatedForPiruluGCPExampleKey panel-deploy@pirulugcp"
        ];
      }
      return [
        "status"         => "success",
        "message"        => "Operacion de servidor ejecutada en modo simulacion",
        "commit_hash"    => "b487976e1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d",
        "commit_author"  => "Pirulug",
        "commit_message" => "Update panel code",
        "log"            => "Fetching origin\nAlready up to date.\nPermissions applied\nServices reloaded"
      ];
    }

    if ($binary === "pirulu-mail") {
      if ($action === "status") {
        return [
          "status"  => "success",
          "exim4"   => "active",
          "dovecot" => "active"
        ];
      }
      if ($action === "dkim-get") {
        return [
          "status"       => "success",
          "domain"       => $args[1] ?? "midominio.com",
          "selector"     => "default",
          "dkim_record"  => "v=DKIM1; k=rsa; p=MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsimulatedKeyExample...",
          "spf_record"   => "v=spf1 a mx ip4:192.168.1.100 ~all",
          "dmarc_record" => "v=DMARC1; p=none; sp=none; aspf=r;",
          "server_ip"    => "192.168.1.100"
        ];
      }
      return [
        "status"        => "success",
        "message"       => "Operacion de correo completada en modo simulacion",
        "webmail_url"   => "http://webmail." . ($args[1] ?? "midominio.com"),
        "spf_record"    => "v=spf1 a mx ip4:192.168.1.100 ~all",
        "dkim_selector" => "default",
        "dkim_record"   => "v=DKIM1; k=rsa; p=MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsimulatedKeyExample..."
      ];
    }

    if ($binary === "pirulu-db") {
      if ($action === "logs") {
        $logs = "[2026-08-16 12:00:01] 0 [Note] Starting MariaDB 10.11.8-MariaDB-0ubuntu0.24.04.1 source revision ...\n" .
                "[2026-08-16 12:00:01] 0 [Note] Server socket created on IP: '127.0.0.1'.\n" .
                "[2026-08-16 12:00:01] 0 [Note] Version: '10.11.8-MariaDB'  socket: '/run/mysqld/mysqld.sock'  port: 3306\n" .
                "[2026-08-16 12:00:01] 0 [Note] InnoDB: Buffer pool(s) load completed\n" .
                "[2026-08-16 12:20:15] 12 [Note] Connection 12 established to database 'acme' from user 'admin_acme'\n" .
                "[2026-08-16 12:24:30] 15 [Note] Query execution status: OK, 14 tables analyzed";
        return [
          "status"     => "success",
          "raw_base64" => base64_encode($logs)
        ];
      }
      if ($action === "get-config") {
        $cnf = "# MariaDB Server Master Configuration\n[mysqld]\nuser = mysql\npid-file = /run/mysqld/mysqld.pid\nsocket = /run/mysqld/mysqld.sock\nport = 3306\nbasedir = /usr\ndatadir = /var/lib/mysql\ntmpdir = /tmp\nlc-messages-dir = /usr/share/mysql\nbind-address = 127.0.0.1\n\n# Buffer Pool & Conexiones\nkey_buffer_size = 128M\nmax_allowed_packet = 64M\nthread_stack = 192K\nthread_cache_size = 8\nmax_connections = 150\n\n# InnoDB Tuning\ninnodb_buffer_pool_size = 256M\ninnodb_log_file_size = 64M\ninnodb_file_per_table = 1\n";
        return [
          "status"     => "success",
          "file"       => "/etc/mysql/mariadb.conf.d/50-server.cnf",
          "raw_base64" => base64_encode($cnf)
        ];
      }
      if ($action === "dump") {
        return [
          "status" => "success",
          "file"   => "/var/backups/pirulugcp/databases/" . ($args[1] ?? "db") . ".sql",
          "size"   => 48234120
        ];
      }
      return [
        "status"  => "success",
        "message" => "Operacion de base de datos ejecutada exitosamente"
      ];
    }

    return [
      "status"  => "success",
      "message" => "Operacion completada exitosamente (Modo desarrollo)"
    ];
  }
}
