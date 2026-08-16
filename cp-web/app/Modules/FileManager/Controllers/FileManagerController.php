<?php

namespace Pirulu\Modules\FileManager\Controllers;

use Pirulu\Core\Auth;
use Pirulu\Core\Database;
use Pirulu\Core\Engine;
use Pirulu\Core\View;

class FileManagerController {
    public function index(): void {
        Auth::requireAuth();
        $db = Database::getConnection();

        $domains = $db->query("
            SELECT d.*, u.username 
            FROM domains d 
            LEFT JOIN users u ON d.user_id = u.id 
            ORDER BY d.domain ASC
        ")->fetchAll();

        if (empty($domains)) {
            View::setFlash("info", "Primero debes crear al menos un dominio web para gestionar archivos.");
            header("Location: /web");
            exit;
        }

        $selectedDomainName = trim($_GET["domain"] ?? ($domains[0]["domain"] ?? ""));
        $currentDomain = null;
        foreach ($domains as $d) {
            if ($d["domain"] === $selectedDomainName) {
                $currentDomain = $d;
                break;
            }
        }
        if (!$currentDomain) {
            $currentDomain = $domains[0];
            $selectedDomainName = $currentDomain["domain"];
        }

        $username = $currentDomain["username"] ?? "admin";
        $baseDir = "/home/" . $username . "/web/" . $selectedDomainName;

        // En desarrollo local (Windows), si no existe la ruta en el sistema host, usar una ruta temporal o simular
        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN" && !is_dir($baseDir)) {
            $baseDir = dirname(__DIR__, 4) . "/scratch/web/" . $selectedDomainName;
            if (!is_dir($baseDir)) {
                mkdir($baseDir . "/public_html", 0777, true);
                file_put_contents($baseDir . "/public_html/index.php", "<?php\necho 'Hola desde " . $selectedDomainName . "';\n");
            }
        }

        $reqPath = trim($_GET["path"] ?? "");
        $reqPath = str_replace(["..", "\\"], "", $reqPath);
        $reqPath = trim($reqPath, "/");

        $currentFullPath = empty($reqPath) ? $baseDir : ($baseDir . "/" . $reqPath);

        // Crear directorio base si no existiera
        if (!is_dir($currentFullPath)) {
            Engine::execute("pirulu-files", ["mkdir", $currentFullPath, $username]);
        }

        // Listar contenidos
        $items = [];
        if (is_dir($currentFullPath)) {
            $scanned = scandir($currentFullPath);
            if ($scanned !== false) {
                foreach ($scanned as $file) {
                    if ($file === "." || $file === "..") {
                        continue;
                    }
                    $filePath = $currentFullPath . "/" . $file;
                    $isDir = is_dir($filePath);
                    $size = $isDir ? "-" : self::formatBytes(@filesize($filePath) ?: 0);
                    $mtime = date("Y-m-d H:i:s", @filemtime($filePath) ?: time());
                    $perms = substr(sprintf("%o", @fileperms($filePath) ?: 0), -4);

                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                    $items[] = [
                        "name" => $file,
                        "is_dir" => $isDir,
                        "size" => $size,
                        "raw_size" => $isDir ? 0 : (@filesize($filePath) ?: 0),
                        "mtime" => $mtime,
                        "perms" => $perms,
                        "ext" => $ext,
                        "rel_path" => empty($reqPath) ? $file : ($reqPath . "/" . $file)
                    ];
                }
            }
        }

        // Ordenar carpetas primero y luego archivos alfabeticamente
        usort($items, function ($a, $b) {
            if ($a["is_dir"] !== $b["is_dir"]) {
                return $a["is_dir"] ? -1 : 1;
            }
            return strcasecmp($a["name"], $b["name"]);
        });

        // Breadcrumbs
        $breadcrumbs = [];
        $breadcrumbs[] = ["name" => $selectedDomainName, "path" => ""];
        if (!empty($reqPath)) {
            $parts = explode("/", $reqPath);
            $accum = "";
            foreach ($parts as $p) {
                $accum = empty($accum) ? $p : ($accum . "/" . $p);
                $breadcrumbs[] = ["name" => $p, "path" => $accum];
            }
        }

        View::render("Modules/FileManager/Views/index", [
            "pageTitle" => "Gestor de Archivos - PiruluGCP",
            "domains" => $domains,
            "currentDomain" => $currentDomain,
            "selectedDomain" => $selectedDomainName,
            "reqPath" => $reqPath,
            "items" => $items,
            "breadcrumbs" => $breadcrumbs,
            "baseDir" => $baseDir
        ]);
    }

    public function upload(): void {
        Auth::requireAuth();
        $domain = trim($_POST["domain"] ?? "");
        $path = trim($_POST["path"] ?? "");
        $username = trim($_POST["username"] ?? "admin");

        $baseDir = "/home/" . $username . "/web/" . $domain;
        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN" && !is_dir($baseDir)) {
            $baseDir = dirname(__DIR__, 4) . "/scratch/web/" . $domain;
        }

        $targetDir = empty($path) ? $baseDir : ($baseDir . "/" . trim($path, "/"));

        if (!empty($_FILES["files"]["name"][0])) {
            $count = count($_FILES["files"]["name"]);
            for ($i = 0; $i < $count; $i++) {
                $fileName = basename($_FILES["files"]["name"][$i]);
                $tmpName = $_FILES["files"]["tmp_name"][$i];
                $dest = $targetDir . "/" . $fileName;

                if (move_uploaded_file($tmpName, $dest)) {
                    Engine::execute("pirulu-files", ["chown", $dest, $username]);
                    Engine::execute("pirulu-files", ["chmod", $dest, "0644"]);
                }
            }
            View::setFlash("success", "Archivos subidos correctamente.");
        }

        header("Location: /files?domain=" . urlencode($domain) . "&path=" . urlencode($path));
        exit;
    }

    public function createFolder(): void {
        Auth::requireAuth();
        $domain = trim($_POST["domain"] ?? "");
        $path = trim($_POST["path"] ?? "");
        $username = trim($_POST["username"] ?? "admin");
        $folderName = trim($_POST["folder_name"] ?? "");

        if (!empty($folderName)) {
            $folderName = str_replace(["/", "\\", ".."], "", $folderName);
            $baseDir = "/home/" . $username . "/web/" . $domain;
            if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN" && !is_dir($baseDir)) {
                $baseDir = dirname(__DIR__, 4) . "/scratch/web/" . $domain;
            }

            $targetDir = empty($path) ? ($baseDir . "/" . $folderName) : ($baseDir . "/" . trim($path, "/") . "/" . $folderName);
            Engine::execute("pirulu-files", ["mkdir", $targetDir, $username]);
            View::setFlash("success", "Carpeta " . htmlspecialchars($folderName) . " creada.");
        }

        header("Location: /files?domain=" . urlencode($domain) . "&path=" . urlencode($path));
        exit;
    }

    public function createFile(): void {
        Auth::requireAuth();
        $domain = trim($_POST["domain"] ?? "");
        $path = trim($_POST["path"] ?? "");
        $username = trim($_POST["username"] ?? "admin");
        $fileName = trim($_POST["file_name"] ?? "");

        if (!empty($fileName)) {
            $fileName = str_replace(["/", "\\", ".."], "", $fileName);
            $baseDir = "/home/" . $username . "/web/" . $domain;
            if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN" && !is_dir($baseDir)) {
                $baseDir = dirname(__DIR__, 4) . "/scratch/web/" . $domain;
            }

            $targetFile = empty($path) ? ($baseDir . "/" . $fileName) : ($baseDir . "/" . trim($path, "/") . "/" . $fileName);
            Engine::execute("pirulu-files", ["touch", $targetFile, $username]);
            View::setFlash("success", "Archivo " . htmlspecialchars($fileName) . " creado.");
        }

        header("Location: /files?domain=" . urlencode($domain) . "&path=" . urlencode($path));
        exit;
    }

    public function readFile(): void {
        Auth::requireAuth();
        $domain = trim($_GET["domain"] ?? "");
        $path = trim($_GET["path"] ?? "");
        $username = trim($_GET["username"] ?? "admin");

        $baseDir = "/home/" . $username . "/web/" . $domain;
        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN" && !is_dir($baseDir)) {
            $baseDir = dirname(__DIR__, 4) . "/scratch/web/" . $domain;
        }

        $filePath = $baseDir . "/" . ltrim($path, "/");

        if (file_exists($filePath) && is_file($filePath)) {
            $content = file_get_contents($filePath);
            header("Content-Type: application/json");
            echo json_encode(["status" => "success", "content" => $content]);
        } else {
            header("Content-Type: application/json");
            echo json_encode(["status" => "error", "message" => "Archivo no encontrado"]);
        }
        exit;
    }

    public function saveFile(): void {
        Auth::requireAuth();
        $domain = trim($_POST["domain"] ?? "");
        $path = trim($_POST["path"] ?? "");
        $username = trim($_POST["username"] ?? "admin");
        $content = $_POST["content"] ?? "";

        $baseDir = "/home/" . $username . "/web/" . $domain;
        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN" && !is_dir($baseDir)) {
            $baseDir = dirname(__DIR__, 4) . "/scratch/web/" . $domain;
        }

        $filePath = $baseDir . "/" . ltrim($path, "/");

        if (file_exists($filePath) && is_file($filePath)) {
            file_put_contents($filePath, $content);
            Engine::execute("pirulu-files", ["chown", $filePath, $username]);
            header("Content-Type: application/json");
            echo json_encode(["status" => "success", "message" => "Archivo guardado exitosamente"]);
        } else {
            header("Content-Type: application/json");
            echo json_encode(["status" => "error", "message" => "No se pudo guardar el archivo"]);
        }
        exit;
    }

    public function deleteItem(): void {
        Auth::requireAuth();
        $domain = trim($_POST["domain"] ?? "");
        $path = trim($_POST["path"] ?? "");
        $username = trim($_POST["username"] ?? "admin");
        $item = trim($_POST["item"] ?? "");

        $baseDir = "/home/" . $username . "/web/" . $domain;
        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN" && !is_dir($baseDir)) {
            $baseDir = dirname(__DIR__, 4) . "/scratch/web/" . $domain;
        }

        $target = empty($path) ? ($baseDir . "/" . $item) : ($baseDir . "/" . trim($path, "/") . "/" . $item);
        Engine::execute("pirulu-files", ["delete", $target]);
        View::setFlash("success", "Elemento eliminado correctamente.");

        header("Location: /files?domain=" . urlencode($domain) . "&path=" . urlencode($path));
        exit;
    }

    public function extractZip(): void {
        Auth::requireAuth();
        $domain = trim($_POST["domain"] ?? "");
        $path = trim($_POST["path"] ?? "");
        $username = trim($_POST["username"] ?? "admin");
        $zipFile = trim($_POST["zip_file"] ?? "");

        $baseDir = "/home/" . $username . "/web/" . $domain;
        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN" && !is_dir($baseDir)) {
            $baseDir = dirname(__DIR__, 4) . "/scratch/web/" . $domain;
        }

        $zipFullPath = empty($path) ? ($baseDir . "/" . $zipFile) : ($baseDir . "/" . trim($path, "/") . "/" . $zipFile);
        $destDir = empty($path) ? $baseDir : ($baseDir . "/" . trim($path, "/"));

        $res = Engine::execute("pirulu-files", ["unzip", $zipFullPath, $destDir, $username]);

        if (isset($res["status"]) && $res["status"] === "success") {
            View::setFlash("success", "Archivo ZIP extraido correctamente.");
        } else {
            View::setFlash("danger", "Error al extraer archivo ZIP: " . htmlspecialchars($res["raw_output"] ?? "Error"));
        }

        header("Location: /files?domain=" . urlencode($domain) . "&path=" . urlencode($path));
        exit;
    }

    public function download(): void {
        Auth::requireAuth();
        $domain = trim($_GET["domain"] ?? "");
        $path = trim($_GET["path"] ?? "");
        $username = trim($_GET["username"] ?? "admin");

        $baseDir = "/home/" . $username . "/web/" . $domain;
        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN" && !is_dir($baseDir)) {
            $baseDir = dirname(__DIR__, 4) . "/scratch/web/" . $domain;
        }

        $filePath = $baseDir . "/" . ltrim($path, "/");

        if (file_exists($filePath) && is_file($filePath)) {
            header("Content-Description: File Transfer");
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"" . basename($filePath) . "\"");
            header("Expires: 0");
            header("Cache-Control: must-revalidate");
            header("Pragma: public");
            header("Content-Length: " . filesize($filePath));
            readfile($filePath);
            exit;
        }

        View::setFlash("danger", "Archivo no encontrado para descarga.");
        header("Location: /files?domain=" . urlencode($domain));
        exit;
    }

    public function chmod(): void {
        Auth::requireAuth();
        $domain = trim($_POST["domain"] ?? "");
        $path = trim($_POST["path"] ?? "");
        $username = trim($_POST["username"] ?? "admin");
        $item = trim($_POST["item"] ?? "");
        $mode = trim($_POST["mode"] ?? "0644");

        $baseDir = "/home/" . $username . "/web/" . $domain;
        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN" && !is_dir($baseDir)) {
            $baseDir = dirname(__DIR__, 4) . "/scratch/web/" . $domain;
        }

        $target = empty($path) ? ($baseDir . "/" . $item) : ($baseDir . "/" . trim($path, "/") . "/" . $item);
        Engine::execute("pirulu-files", ["chmod", $target, $mode]);
        View::setFlash("success", "Permisos actualizados a " . htmlspecialchars($mode) . ".");

        header("Location: /files?domain=" . urlencode($domain) . "&path=" . urlencode($path));
        exit;
    }

    private static function formatBytes(int $bytes): string {
        $units = ["B", "KB", "MB", "GB", "TB"];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . " " . $units[$pow];
    }
}
