<?php

namespace Pirulu\Core;

use PDO;
use PDOException;

class Database {
  private static $instance = null;

  public static function getConnection() {
    if (self::$instance === null) {
      $dbPath = "/var/lib/pirulugcp/pirulugcp.sqlite";
      $dbDir = dirname($dbPath);

      if (!is_dir($dbDir)) {
        $dbPath = dirname(__DIR__, 3) . "/config/pirulugcp.sqlite";
        $dbDir = dirname($dbPath);
        if (!is_dir($dbDir)) {
          mkdir($dbDir, 0775, true);
        }
      } else {
        chmod($dbDir, 0775);
      }

      try {
        self::$instance = new PDO("sqlite:" . $dbPath);
        self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        self::initSchema(self::$instance);
      } catch (PDOException $e) {
        exit("Error al conectar con la base de datos del panel: " . $e->getMessage());
      }
    }

    return self::$instance;
  }

  private static function initSchema($db) {
    $db->exec("
      CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'admin',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
      );

      CREATE TABLE IF NOT EXISTS domains (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        domain TEXT UNIQUE NOT NULL,
        user_id INTEGER,
        php_version TEXT NOT NULL DEFAULT '8.2',
        doc_root_suffix TEXT NOT NULL DEFAULT 'public_html',
        ssl_enabled INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id)
      );

      CREATE TABLE IF NOT EXISTS databases (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        db_name TEXT UNIQUE NOT NULL,
        db_user TEXT NOT NULL,
        db_password_enc TEXT NOT NULL,
        user_id INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id)
      );

      CREATE TABLE IF NOT EXISTS domain_git (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        domain_id INTEGER UNIQUE NOT NULL,
        repo_url TEXT NOT NULL,
        branch TEXT NOT NULL DEFAULT 'main',
        deploy_suffix TEXT NOT NULL DEFAULT 'public_html',
        is_private INTEGER NOT NULL DEFAULT 0,
        ssh_public_key TEXT,
        ssh_private_key_path TEXT,
        webhook_secret TEXT UNIQUE NOT NULL,
        auto_deploy INTEGER NOT NULL DEFAULT 1,
        last_commit_hash TEXT,
        last_commit_message TEXT,
        last_commit_author TEXT,
        last_deploy_at DATETIME,
        last_deploy_status TEXT,
        last_deploy_log TEXT,
        composer_install INTEGER NOT NULL DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (domain_id) REFERENCES domains (id) ON DELETE CASCADE
      );
    ");

    try {
      $db->exec("ALTER TABLE domains ADD COLUMN doc_root_suffix TEXT NOT NULL DEFAULT 'public_html'");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("ALTER TABLE domains ADD COLUMN aliases TEXT DEFAULT ''");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("ALTER TABLE domains ADD COLUMN redirect_enabled INTEGER NOT NULL DEFAULT 0");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("ALTER TABLE domains ADD COLUMN redirect_type TEXT DEFAULT 'custom'");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("ALTER TABLE domains ADD COLUMN redirect_target TEXT DEFAULT ''");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("ALTER TABLE domains ADD COLUMN redirect_code INTEGER NOT NULL DEFAULT 301");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("ALTER TABLE domain_git ADD COLUMN composer_install INTEGER NOT NULL DEFAULT 1");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    $db->exec("
      CREATE TABLE IF NOT EXISTS domain_backup_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        domain_id INTEGER UNIQUE NOT NULL,
        enabled INTEGER NOT NULL DEFAULT 0,
        frequency TEXT NOT NULL DEFAULT 'daily',
        retention_count INTEGER NOT NULL DEFAULT 5,
        include_files INTEGER NOT NULL DEFAULT 1,
        include_db INTEGER NOT NULL DEFAULT 1,
        last_backup_at DATETIME,
        next_backup_at DATETIME,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (domain_id) REFERENCES domains (id) ON DELETE CASCADE
      );

      CREATE TABLE IF NOT EXISTS domain_backups (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        domain_id INTEGER NOT NULL,
        filename TEXT NOT NULL,
        filepath TEXT NOT NULL,
        filesize_bytes INTEGER NOT NULL DEFAULT 0,
        backup_type TEXT NOT NULL DEFAULT 'manual',
        status TEXT NOT NULL DEFAULT 'completed',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (domain_id) REFERENCES domains (id) ON DELETE CASCADE
      );
    ");

    $db->exec("
      CREATE TABLE IF NOT EXISTS server_settings (
        id INTEGER PRIMARY KEY CHECK (id = 1),
        server_hostname TEXT NOT NULL DEFAULT 'localhost',
        panel_domain TEXT DEFAULT '',
        panel_ssl_enabled INTEGER NOT NULL DEFAULT 0,
        panel_ssl_force_https INTEGER NOT NULL DEFAULT 0,
        panel_ssl_email TEXT DEFAULT '',
        server_timezone TEXT NOT NULL DEFAULT 'UTC',
        panel_git_repo TEXT,
        panel_git_branch TEXT NOT NULL DEFAULT 'main',
        panel_git_is_private INTEGER NOT NULL DEFAULT 1,
        panel_webhook_token TEXT,
        panel_auto_update INTEGER NOT NULL DEFAULT 0,
        panel_last_update_at DATETIME,
        panel_last_update_status TEXT,
        panel_last_update_log TEXT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
      );
    ");

    try {
      $db->exec("ALTER TABLE server_settings ADD COLUMN panel_domain TEXT DEFAULT ''");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("ALTER TABLE server_settings ADD COLUMN panel_ssl_enabled INTEGER NOT NULL DEFAULT 0");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("ALTER TABLE server_settings ADD COLUMN panel_ssl_force_https INTEGER NOT NULL DEFAULT 0");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("ALTER TABLE server_settings ADD COLUMN panel_ssl_email TEXT DEFAULT ''");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("ALTER TABLE server_settings ADD COLUMN cf_turnstile_enabled INTEGER NOT NULL DEFAULT 0");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("ALTER TABLE server_settings ADD COLUMN cf_turnstile_site_key TEXT DEFAULT ''");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("ALTER TABLE server_settings ADD COLUMN cf_turnstile_secret_key TEXT DEFAULT ''");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("ALTER TABLE users ADD COLUMN name TEXT DEFAULT 'Administrador'");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("ALTER TABLE users ADD COLUMN email TEXT DEFAULT 'admin@pirulugcp.local'");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("ALTER TABLE users ADD COLUMN two_factor_enabled INTEGER NOT NULL DEFAULT 0");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("ALTER TABLE users ADD COLUMN two_factor_secret TEXT DEFAULT ''");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("
        INSERT OR IGNORE INTO server_settings (id, server_hostname, panel_domain, panel_ssl_enabled, panel_ssl_force_https, panel_ssl_email, server_timezone, panel_git_branch, panel_git_is_private, panel_auto_update, cf_turnstile_enabled, cf_turnstile_site_key, cf_turnstile_secret_key)
        VALUES (1, 'localhost', '', 0, 0, '', 'UTC', 'main', 1, 0, 0, '', '');
      ");
    } catch (PDOException $e) {
      // Ya existe
    }

    $db->exec("
      CREATE TABLE IF NOT EXISTS mail_domains (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        domain_id INTEGER NOT NULL UNIQUE,
        domain_name TEXT NOT NULL UNIQUE,
        dkim_selector TEXT NOT NULL DEFAULT 'default',
        dkim_record TEXT,
        spf_record TEXT,
        ssl_enabled INTEGER NOT NULL DEFAULT 0,
        ssl_force_https INTEGER NOT NULL DEFAULT 0,
        status TEXT NOT NULL DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (domain_id) REFERENCES domains (id) ON DELETE CASCADE
      );

      CREATE TABLE IF NOT EXISTS mail_accounts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        mail_domain_id INTEGER NOT NULL,
        account_user TEXT NOT NULL,
        account_email TEXT NOT NULL UNIQUE,
        quota_mb INTEGER NOT NULL DEFAULT 1024,
        status TEXT NOT NULL DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (mail_domain_id) REFERENCES mail_domains (id) ON DELETE CASCADE
      );

      CREATE TABLE IF NOT EXISTS mail_forwarders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        mail_domain_id INTEGER NOT NULL,
        source_email TEXT NOT NULL,
        destination_email TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (mail_domain_id) REFERENCES mail_domains (id) ON DELETE CASCADE
      );

      CREATE TABLE IF NOT EXISTS cron_jobs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL DEFAULT 1,
        domain_id INTEGER,
        command TEXT NOT NULL,
        minute TEXT NOT NULL DEFAULT '*',
        hour TEXT NOT NULL DEFAULT '*',
        day TEXT NOT NULL DEFAULT '*',
        month TEXT NOT NULL DEFAULT '*',
        weekday TEXT NOT NULL DEFAULT '*',
        description TEXT,
        status TEXT NOT NULL DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (domain_id) REFERENCES domains (id) ON DELETE SET NULL
      );

      CREATE TABLE IF NOT EXISTS ftp_accounts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        domain_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL DEFAULT 1,
        ftp_user TEXT UNIQUE NOT NULL,
        ftp_path TEXT NOT NULL DEFAULT 'public_html',
        status TEXT NOT NULL DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (domain_id) REFERENCES domains (id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
      );
    ");

    // Comprobar si faltan columnas de SSL en mail_domains
    try {
      $db->exec("ALTER TABLE mail_domains ADD COLUMN ssl_enabled INTEGER NOT NULL DEFAULT 0");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    try {
      $db->exec("ALTER TABLE mail_domains ADD COLUMN ssl_force_https INTEGER NOT NULL DEFAULT 0");
    } catch (PDOException $e) {
      // Columna ya existe
    }

    // Comprobar si falta la columna db_password_enc en instalaciones existentes
    try {
      $db->query("SELECT db_password_enc FROM databases LIMIT 1");
    } catch (PDOException $e) {
      $db->exec("ALTER TABLE databases ADD COLUMN db_password_enc TEXT");
    }

    // Crear usuario admin por defecto si no existe
    $stmt = $db->query("SELECT COUNT(*) as total FROM users");
    $row = $stmt->fetch();
    if ($row["total"] == 0) {
      $adminPass = password_hash("admin123", PASSWORD_DEFAULT);
      $stmt = $db->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
      $stmt->execute(["admin", $adminPass, "admin"]);
    }
  }
}
