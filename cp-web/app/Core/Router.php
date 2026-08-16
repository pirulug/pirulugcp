<?php

namespace Pirulu\Core;

class Router {
    private array $routes = [];

    public function get(string $path, array $handler): void {
        $this->addRoute("GET", $path, $handler);
    }

    public function post(string $path, array $handler): void {
        $this->addRoute("POST", $path, $handler);
    }

    private function addRoute(string $method, string $path, array $handler): void {
        $this->routes[] = [
            "method" => $method,
            "path" => $path,
            "handler" => $handler
        ];
    }

    public function dispatch(): void {
        $requestMethod = $_SERVER["REQUEST_METHOD"] ?? "GET";
        if ($requestMethod === "HEAD") {
            $requestMethod = "GET";
        }
        $requestUri = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route["method"] !== $requestMethod) {
                continue;
            }

            $pattern = "@^" . preg_replace("/\{([a-zA-Z0-9_]+)\}/", "([^/]+)", $route["path"]) . "$@";

            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches);
                [$controllerClass, $action] = $route["handler"];

                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    if (method_exists($controller, $action)) {
                        call_user_func_array([$controller, $action], $matches);
                        return;
                    }
                }
            }
        }

        http_response_code(404);
        echo "404 - Pagina no encontrada en PiruluGCP";
    }
}
