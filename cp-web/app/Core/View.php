<?php

namespace Pirulu\Core;

class View {
    public static function render(string $viewPath, array $data = [], string $layout = "layout"): void {
        extract($data);

        $viewFile = dirname(__DIR__) . "/" . $viewPath . ".php";
        if (!file_exists($viewFile)) {
            die("Vista no encontrada: " . htmlspecialchars($viewFile));
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout) {
            $layoutFile = dirname(__DIR__) . "/Views/" . $layout . ".php";
            if (file_exists($layoutFile)) {
                $user = Auth::user();
                require $layoutFile;
                return;
            }
        }

        echo $content;
    }

    public static function setFlash(string $type, string $message): void {
        Auth::init();
        $_SESSION["flash"] = [
            "type" => $type,
            "message" => $message
        ];
    }

    public static function getFlash(): ?array {
        Auth::init();
        if (isset($_SESSION["flash"])) {
            $flash = $_SESSION["flash"];
            unset($_SESSION["flash"]);
            return $flash;
        }
        return null;
    }
}
