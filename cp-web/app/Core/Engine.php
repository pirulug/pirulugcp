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

        return [
            "status"  => "success",
            "message" => "Operacion completada exitosamente (Modo desarrollo)"
        ];
    }
}
