<?php

namespace Pirulu\Core;

class Engine {
    private static string $engineBinDir = "/usr/local/pirulugcp/engine/bin";

    public static function execute(string $binary, array $args = []): array {
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

    public static function executeRaw(string $binary, array $args = []): string {
        $binPath = self::$engineBinDir . "/" . $binary;

        if (!file_exists($binPath) || strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
            return "[Modo Simulacion] Registros de prueba para " . ($args[1] ?? $binary) . "\n" . date("Y-m-d H:i:s") . " [INFO] Servicio operando con normalidad en modo desarrollo.";
        }

        $escapedArgs = array_map("escapeshellarg", $args);
        $command = "sudo " . escapeshellcmd($binPath) . " " . implode(" ", $escapedArgs) . " 2>&1";

        $output = shell_exec($command);
        return $output ?? "No se encontraron registros.";
    }

    private static function simulate(string $binary, array $args): array {
        $action = $args[0] ?? "";

        if ($binary === "pirulu-system") {
            if ($action === "status") {
                return [
                    "status" => "success",
                    "services" => [
                        "apache" => "active",
                        "mariadb" => "active",
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

        if ($binary === "pirulu-php" && $action === "versions") {
            return [
                "status" => "success",
                "versions" => [
                    ["version" => "7.4", "installed" => true, "status" => "inactive"],
                    ["version" => "8.0", "installed" => false, "status" => "inactive"],
                    ["version" => "8.1", "installed" => true, "status" => "active"],
                    ["version" => "8.2", "installed" => true, "status" => "active"],
                    ["version" => "8.3", "installed" => true, "status" => "active"],
                    ["version" => "8.4", "installed" => true, "status" => "active"],
                    ["version" => "8.5", "installed" => false, "status" => "inactive"]
                ]
            ];
        }

        return [
            "status" => "success",
            "message" => "Operacion completada exitosamente (Modo desarrollo)"
        ];
    }
}
