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

        $uploadedCount = 0;
        $failedCount = 0;

        if (!empty($_FILES["files"]["name"][0])) {
            $count = count($_FILES["files"]["name"]);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES["files"]["error"][$i] !== UPLOAD_ERR_OK) {
                    $failedCount++;
                    continue;
                }

                $fileName = basename($_FILES["files"]["name"][$i]);
                $tmpName = $_FILES["files"]["tmp_name"][$i];
                $dest = $targetDir . "/" . $fileName;

                if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
                    if (move_uploaded_file($tmpName, $dest)) {
                        $uploadedCount++;
                    } else {
                        $failedCount++;
                    }
                } else {
                    // Mover primero a una ruta temporal segura accesible por www-data
                    $tempImport = sys_get_temp_dir() . "/pirulu_up_" . uniqid() . "_" . $fileName;
                    if (move_uploaded_file($tmpName, $tempImport)) {
                        $res = Engine::execute("pirulu-files", ["import", $tempImport, $dest, $username]);
                        if (isset($res["status"]) && $res["status"] === "success") {
                            $uploadedCount++;
                        } else {
                            @unlink($tempImport);
                            $failedCount++;
                        }
                    } else {
                        $failedCount++;
                    }
                }
            }

            if ($uploadedCount > 0) {
                View::setFlash("success", $uploadedCount . " archivo(s) subido(s) correctamente.");
            } else {
                View::setFlash("danger", "Error al subir los archivos. Verifica los permisos del directorio.");
            }
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
            View::setFlash("success", "Carpeta " . $folderName . " creada.");
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
            View::setFlash("success", "Archivo " . $fileName . " creado.");
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

        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
            if (file_put_contents($filePath, $content) !== false) {
                header("Content-Type: application/json");
                echo json_encode(["status" => "success", "message" => "Archivo guardado exitosamente"]);
            } else {
                header("Content-Type: application/json");
                echo json_encode(["status" => "error", "message" => "No se pudo guardar el archivo"]);
            }
            exit;
        }

        // En Linux, guardar mediante archivo temporal y pirulu-files write para garantizar permisos
        $tempSave = sys_get_temp_dir() . "/pirulu_save_" . uniqid();
        file_put_contents($tempSave, $content);
        $res = Engine::execute("pirulu-files", ["write", $filePath, $tempSave, $username]);
        @unlink($tempSave);

        header("Content-Type: application/json");
        if (isset($res["status"]) && $res["status"] === "success") {
            echo json_encode(["status" => "success", "message" => "Archivo guardado exitosamente"]);
        } else {
            echo json_encode(["status" => "error", "message" => $res["message"] ?? "No se pudo guardar el archivo"]);
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

    public function copyItem(): void {
        Auth::requireAuth();
        $domain = trim($_POST["domain"] ?? "");
        $path = trim($_POST["path"] ?? "");
        $username = trim($_POST["username"] ?? "admin");
        $item = trim($_POST["item"] ?? "");
        $destName = trim($_POST["dest_name"] ?? "");

        $baseDir = "/home/" . $username . "/web/" . $domain;
        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN" && !is_dir($baseDir)) {
            $baseDir = dirname(__DIR__, 4) . "/scratch/web/" . $domain;
        }

        $source = empty($path) ? ($baseDir . "/" . $item) : ($baseDir . "/" . trim($path, "/") . "/" . $item);
        $destName = str_replace(["..", "\\"], "", $destName);
        if (empty($destName)) {
            $destName = $item . "_copia";
        }
        $dest = empty($path) ? ($baseDir . "/" . $destName) : ($baseDir . "/" . trim($path, "/") . "/" . $destName);

        $res = Engine::execute("pirulu-files", ["copy", $source, $dest, $username]);
        if (isset($res["status"]) && $res["status"] === "success") {
            View::setFlash("success", "Elemento copiado como " . $destName . " exitosamente.");
        } else {
            View::setFlash("danger", "Error al copiar: " . $res["message"] ?? "Error");
        }

        header("Location: /files?domain=" . urlencode($domain) . "&path=" . urlencode($path));
        exit;
    }

    public function moveItem(): void {
        Auth::requireAuth();
        $domain = trim($_POST["domain"] ?? "");
        $path = trim($_POST["path"] ?? "");
        $username = trim($_POST["username"] ?? "admin");
        $item = trim($_POST["item"] ?? "");
        $destFolder = trim($_POST["dest_folder"] ?? "");

        $baseDir = "/home/" . $username . "/web/" . $domain;
        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN" && !is_dir($baseDir)) {
            $baseDir = dirname(__DIR__, 4) . "/scratch/web/" . $domain;
        }

        $source = empty($path) ? ($baseDir . "/" . $item) : ($baseDir . "/" . trim($path, "/") . "/" . $item);
        $destFolder = str_replace(["..", "\\"], "", $destFolder);
        $destFolder = trim($destFolder, "/");
        $dest = empty($destFolder) ? ($baseDir . "/" . $item) : ($baseDir . "/" . $destFolder . "/" . $item);

        $res = Engine::execute("pirulu-files", ["rename", $source, $dest, $username]);
        if (isset($res["status"]) && $res["status"] === "success") {
            View::setFlash("success", "Elemento movido a /" . $destFolder . " exitosamente.");
        } else {
            View::setFlash("danger", "Error al mover: " . $res["message"] ?? "Error");
        }

        header("Location: /files?domain=" . urlencode($domain) . "&path=" . urlencode($path));
        exit;
    }

    public function renameItem(): void {
        Auth::requireAuth();
        $domain = trim($_POST["domain"] ?? "");
        $path = trim($_POST["path"] ?? "");
        $username = trim($_POST["username"] ?? "admin");
        $oldName = trim($_POST["old_name"] ?? "");
        $newName = trim($_POST["new_name"] ?? "");

        $newName = str_replace(["/", "\\", ".."], "", $newName);
        if (empty($newName)) {
            View::setFlash("danger", "El nuevo nombre no es valido.");
            header("Location: /files?domain=" . urlencode($domain) . "&path=" . urlencode($path));
            exit;
        }

        $baseDir = "/home/" . $username . "/web/" . $domain;
        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN" && !is_dir($baseDir)) {
            $baseDir = dirname(__DIR__, 4) . "/scratch/web/" . $domain;
        }

        $source = empty($path) ? ($baseDir . "/" . $oldName) : ($baseDir . "/" . trim($path, "/") . "/" . $oldName);
        $dest = empty($path) ? ($baseDir . "/" . $newName) : ($baseDir . "/" . trim($path, "/") . "/" . $newName);

        $res = Engine::execute("pirulu-files", ["rename", $source, $dest, $username]);
        if (isset($res["status"]) && $res["status"] === "success") {
            View::setFlash("success", "Elemento renombrado a " . $newName . " exitosamente.");
        } else {
            View::setFlash("danger", "Error al renombrar: " . $res["message"] ?? "Error");
        }

        header("Location: /files?domain=" . urlencode($domain) . "&path=" . urlencode($path));
        exit;
    }

    public function compressItem(): void {
        Auth::requireAuth();
        $domain = trim($_POST["domain"] ?? "");
        $path = trim($_POST["path"] ?? "");
        $username = trim($_POST["username"] ?? "admin");
        $item = trim($_POST["item"] ?? "");
        $zipName = trim($_POST["zip_name"] ?? "");

        $zipName = str_replace(["/", "\\", ".."], "", $zipName);
        if (empty($zipName)) {
            $zipName = $item . ".zip";
        }
        if (!str_ends_with(strtolower($zipName), ".zip")) {
            $zipName .= ".zip";
        }

        $baseDir = "/home/" . $username . "/web/" . $domain;
        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN" && !is_dir($baseDir)) {
            $baseDir = dirname(__DIR__, 4) . "/scratch/web/" . $domain;
        }

        $target = empty($path) ? ($baseDir . "/" . $item) : ($baseDir . "/" . trim($path, "/") . "/" . $item);
        $zipDest = empty($path) ? ($baseDir . "/" . $zipName) : ($baseDir . "/" . trim($path, "/") . "/" . $zipName);

        $res = Engine::execute("pirulu-files", ["zip", $target, $zipDest, $username]);
        if (isset($res["status"]) && $res["status"] === "success") {
            View::setFlash("success", "Elemento comprimido correctamente en " . $zipName . ".");
        } else {
            View::setFlash("danger", "Error al comprimir: " . $res["message"] ?? "Error");
        }

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
            View::setFlash("danger", "Error al extraer archivo ZIP: " . $res["raw_output"] ?? "Error");
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

        if (file_exists($filePath)) {
            if (is_dir($filePath)) {
                $zipTemp = sys_get_temp_dir() . "/" . basename($filePath) . "_" . uniqid() . ".zip";
                Engine::execute("pirulu-files", ["zip", $filePath, $zipTemp, $username]);
                if (file_exists($zipTemp)) {
                    header("Content-Description: File Transfer");
                    header("Content-Type: application/zip");
                    header("Content-Disposition: attachment; filename=\"" . basename($filePath) . ".zip\"");
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate");
                    header("Pragma: public");
                    header("Content-Length: " . filesize($zipTemp));
                    readfile($zipTemp);
                    @unlink($zipTemp);
                    exit;
                }
            } else {
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
        View::setFlash("success", "Permisos actualizados a " . $mode . ".");

        header("Location: /files?domain=" . urlencode($domain) . "&path=" . urlencode($path));
        exit;
    }

    public function composerAction(): void {
        Auth::requireAuth();
        $domain = trim($_POST["domain"] ?? "");
        $path = trim($_POST["path"] ?? "");
        $username = trim($_POST["username"] ?? "admin");
        $action = trim($_POST["action"] ?? "install");

        if (!in_array($action, ["install", "update", "dump-autoload"], true)) {
            $action = "install";
        }

        $res = Engine::execute("pirulu-composer", [$action, $username, $domain, $path]);

        if (isset($res["status"]) && $res["status"] === "success") {
            View::setFlash("success", "Composer " . $action . " ejecutado correctamente.");
        } else {
            View::setFlash("danger", "Error en Composer: " . $res["message"] ?? "Fallo");
        }

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
