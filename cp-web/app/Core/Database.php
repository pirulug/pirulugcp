<?php

namespace Pirulu\Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
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
                die("Error al conectar con la base de datos del panel: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    private static function initSchema(PDO $db): void {
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
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (domain_id) REFERENCES domains (id) ON DELETE CASCADE
            );
        ");

        try {
            $db->exec("ALTER TABLE domains ADD COLUMN doc_root_suffix TEXT NOT NULL DEFAULT 'public_html'");
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
