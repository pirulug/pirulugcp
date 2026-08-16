<?php

namespace Pirulu\Core;

class Auth {
    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function check(): bool {
        self::init();
        return isset($_SESSION["user_id"]);
    }

    public static function user(): ?array {
        self::init();
        if (!self::check()) {
            return null;
        }

        return [
            "id" => $_SESSION["user_id"] ?? 0,
            "username" => $_SESSION["username"] ?? "",
            "role" => $_SESSION["role"] ?? "user"
        ];
    }

    public static function login(string $username, string $password): bool {
        self::init();
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password_hash"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];
            return true;
        }

        return false;
    }

    public static function logout(): void {
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

    public static function requireAuth(): void {
        if (!self::check()) {
            header("Location: /login");
            exit;
        }
    }
}
