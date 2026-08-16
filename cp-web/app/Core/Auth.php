<?php

namespace Pirulu\Core;

class Auth {
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function check() {
        self::init();
        return isset($_SESSION["user_id"]);
    }

    public static function user() {
        self::init();
        if (!self::check()) {
            return null;
        }

        $userId = $_SESSION["user_id"] ?? 0;
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, username, name, email, role, created_at FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $u = $stmt->fetch();

        if ($u) {
            return [
                "id"                 => (int)$u["id"],
                "username"           => (string)$u["username"],
                "name"               => !empty($u["name"]) ? (string)$u["name"] : (string)$u["username"],
                "email"              => !empty($u["email"]) ? (string)$u["email"] : ($u["username"] . "@pirulugcp.local"),
                "role"               => (string)$u["role"],
                "two_factor_enabled" => !empty($u["two_factor_enabled"]),
                "two_factor_secret"  => (string)($u["two_factor_secret"] ?? ""),
                "created_at"         => (string)($u["created_at"] ?? "")
            ];
        }

        return [
            "id"                 => (int)$userId,
            "username"           => (string)($_SESSION["username"] ?? ""),
            "name"               => (string)($_SESSION["username"] ?? "Administrador"),
            "email"              => ($_SESSION["username"] ?? "admin") . "@pirulugcp.local",
            "role"               => (string)($_SESSION["role"] ?? "user"),
            "two_factor_enabled" => false,
            "two_factor_secret"  => "",
            "created_at"         => ""
        ];
    }

    public static function login($username, $password) {
        self::init();
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password_hash"])) {
            if (!empty($user["two_factor_enabled"]) && !empty($user["two_factor_secret"])) {
                $_SESSION["2fa_pending_user_id"] = (int)$user["id"];
                $_SESSION["2fa_pending_username"] = (string)$user["username"];
                return "2fa_required";
            }

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["name"] = $user["name"] ?? $user["username"];
            $_SESSION["email"] = $user["email"] ?? ($user["username"] . "@pirulugcp.local");
            $_SESSION["role"] = $user["role"];
            return true;
        }

        return false;
    }

    public static function verifyAndComplete2fa($code) {
        self::init();
        $pendingUserId = $_SESSION["2fa_pending_user_id"] ?? 0;
        if (!$pendingUserId) {
            return false;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$pendingUserId]);
        $user = $stmt->fetch();

        if (!$user || empty($user["two_factor_secret"])) {
            return false;
        }

        if (Totp::verifyCode($user["two_factor_secret"], $code)) {
            unset($_SESSION["2fa_pending_user_id"], $_SESSION["2fa_pending_username"]);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["name"] = $user["name"] ?? $user["username"];
            $_SESSION["email"] = $user["email"] ?? ($user["username"] . "@pirulugcp.local");
            $_SESSION["role"] = $user["role"];
            return true;
        }

        return false;
    }

    public static function logout() {
        self::init();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                "",
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function requireAuth() {
        if (!self::check()) {
            header("Location: /login");
            exit();
        }
    }
}
