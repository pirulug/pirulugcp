<?php

namespace Pirulu\Modules\Auth\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\View;

class AuthController {
    public function showLogin(): void {
        if (Auth::check()) {
            header("Location: /dashboard");
            exit;
        }

        View::render("Modules/Auth/Views/login", [
            "pageTitle" => "Iniciar Sesion - PiruluGCP"
        ], "");
    }

    public function login(): void {
        $username = trim($_POST["username"] ?? "");
        $password = trim($_POST["password"] ?? "");

        if (empty($username) || empty($password)) {
            View::setFlash("danger", "Por favor ingresa usuario y contrasena.");
            header("Location: /login");
            exit;
        }

        if (Auth::login($username, $password)) {
            header("Location: /dashboard");
            exit;
        }

        View::setFlash("danger", "Credenciales incorrectas.");
        header("Location: /login");
        exit;
    }

    public function logout(): void {
        Auth::logout();
        header("Location: /login");
        exit;
    }
}
