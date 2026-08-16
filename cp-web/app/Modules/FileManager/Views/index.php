<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-0">Gestor de Archivos (File Manager)</h1>
    </div>
    <div>
        <form method="GET" action="/files" class="d-inline-flex align-items-center m-0">
            <label class="form-label me-2 mb-0">Dominio:</label>
            <select name="domain" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php foreach ($domains as $dom): ?>
                    <option value="<?= htmlspecialchars($dom["domain"]) ?>" <?= ($selectedDomain === $dom["domain"]) ? "selected" : "" ?>>
                        <?= htmlspecialchars($dom["domain"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<!-- Barra de Herramientas y Breadcrumbs -->
<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <?php foreach ($breadcrumbs as $i => $bc): ?>
                <?php if ($i === count($breadcrumbs) - 1): ?>
                    <li class="breadcrumb-item active fw-bold" aria-current="page"><?= htmlspecialchars($bc["name"]) ?></li>
                <?php else: ?>
                    <li class="breadcrumb-item">
                        <a href="/files?domain=<?= urlencode($selectedDomain) ?>&path=<?= urlencode($bc["path"]) ?>" class="text-decoration-none text-primary">
                            <?= htmlspecialchars($bc["name"]) ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </nav>

    <div class="d-flex gap-1">
        <button type="button" class="btn btn-sm btn-primary text-uppercase fw-bold text-nowrap" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="bi bi-cloud-arrow-up me-1"></i> Subir Archivos
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap" data-bs-toggle="modal" data-bs-target="#newFolderModal">
            <i class="bi bi-folder-plus me-1"></i> Nueva Carpeta
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap" data-bs-toggle="modal" data-bs-target="#newFileModal">
            <i class="bi bi-file-earmark-plus me-1"></i> Nuevo Archivo
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap" onclick="location.reload()" title="Refrescar">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
    </div>
</div>

<!-- Tabla de Archivos y Carpetas -->
<div class="bg-body p-3 rounded mb-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle table-sm m-0">
            <thead>
                <tr>
                    <th class="ps-3" style="width: 45%;">Nombre</th>
                    <th>Tamano</th>
                    <th>Permisos</th>
                    <th>Ultima Modificacion</th>
                    <th class="text-end pe-3 text-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($reqPath)): ?>
                    <?php
                        $parentPath = dirname($reqPath);
                        if ($parentPath === "." || $parentPath === "/" || $parentPath === "") {
                            $parentPath = "";
                        }
                    ?>
                    <tr>
                        <td colspan="5" class="ps-3">
                            <a href="/files?domain=<?= urlencode($selectedDomain) ?>&path=<?= urlencode($parentPath) ?>" class="text-decoration-none text-body fw-bold d-inline-flex align-items-center">
                                <i class="bi bi-arrow-90deg-up me-2 text-muted"></i> .. (Subir nivel)
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            Carpeta vacia. Puedes <a href="#" data-bs-toggle="modal" data-bs-target="#uploadModal" class="text-primary fw-bold text-uppercase">subir archivos</a> o <a href="#" data-bs-toggle="modal" data-bs-target="#newFileModal" class="text-primary fw-bold text-uppercase">crear uno nuevo</a>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="ps-3">
                                <?php if ($item["is_dir"]): ?>
                                    <a href="/files?domain=<?= urlencode($selectedDomain) ?>&path=<?= urlencode($item["rel_path"]) ?>" class="text-decoration-none fw-bold text-body d-inline-flex align-items-center">
                                        <i class="bi bi-folder-fill text-warning me-2 fs-5"></i>
                                        <?= htmlspecialchars($item["name"]) ?>
                                    </a>
                                <?php else: ?>
                                    <?php
                                        $editable = in_array($item["ext"], ["php", "html", "htm", "css", "js", "json", "txt", "env", "sql", "xml", "yaml", "yml", "md", "htaccess", "conf"]);
                                        $isZip = ($item["ext"] === "zip");
                                    ?>
                                    <?php if ($editable): ?>
                                        <a href="javascript:void(0)" onclick="openEditor('<?= htmlspecialchars($item["rel_path"]) ?>')" class="text-decoration-none text-body d-inline-flex align-items-center">
                                            <i class="bi bi-file-earmark-code text-primary me-2 fs-5"></i>
                                            <?= htmlspecialchars($item["name"]) ?>
                                        </a>
                                    <?php elseif ($isZip): ?>
                                        <span class="d-inline-flex align-items-center text-body">
                                            <i class="bi bi-file-earmark-zip text-danger me-2 fs-5"></i>
                                            <?= htmlspecialchars($item["name"]) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="d-inline-flex align-items-center text-body">
                                            <i class="bi bi-file-earmark text-muted me-2 fs-5"></i>
                                            <?= htmlspecialchars($item["name"]) ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td><span class="text-muted small"><?= htmlspecialchars($item["size"]) ?></span></td>
                            <td>
                                <button type="button" class="badge bg-body-tertiary text-body border p-1 font-monospace text-decoration-none" onclick="openChmodModal('<?= htmlspecialchars($item["name"]) ?>', '<?= htmlspecialchars($item["perms"]) ?>')">
                                    <?= htmlspecialchars($item["perms"]) ?>
                                </button>
                            </td>
                            <td class="text-muted small"><?= htmlspecialchars($item["mtime"]) ?></td>
                            <td class="text-end pe-3 text-nowrap">
                                <div class="d-flex justify-content-end gap-1">
                                    <?php if (!$item["is_dir"]): ?>
                                        <?php if ($editable): ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap" onclick="openEditor('<?= htmlspecialchars($item["rel_path"]) ?>')" title="Editar Archivo">
                                                <i class="bi bi-pencil me-1"></i> Editar
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($item["ext"] === "zip"): ?>
                                            <form action="/files/extract" method="POST" class="d-inline m-0" onsubmit="return confirm('Extraer <?= htmlspecialchars($item["name"]) ?> en este directorio?')">
                                                <input type="hidden" name="domain" value="<?= htmlspecialchars($selectedDomain) ?>">
                                                <input type="hidden" name="path" value="<?= htmlspecialchars($reqPath) ?>">
                                                <input type="hidden" name="username" value="<?= htmlspecialchars($currentDomain["username"] ?? "admin") ?>">
                                                <input type="hidden" name="zip_file" value="<?= htmlspecialchars($item["name"]) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-warning text-uppercase fw-bold text-nowrap" title="Descomprimir ZIP">
                                                    <i class="bi bi-file-earmark-zip me-1"></i> Extraer
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="/files/download?domain=<?= urlencode($selectedDomain) ?>&path=<?= urlencode($item["rel_path"]) ?>&username=<?= urlencode($currentDomain["username"] ?? "admin") ?>" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap" title="Descargar">
                                            <i class="bi bi-download me-1"></i> Bajar
                                        </a>
                                    <?php endif; ?>
                                    <form action="/files/delete" method="POST" class="d-inline m-0" onsubmit="return confirm('Eliminar <?= htmlspecialchars($item["name"]) ?>?')">
                                        <input type="hidden" name="domain" value="<?= htmlspecialchars($selectedDomain) ?>">
                                        <input type="hidden" name="path" value="<?= htmlspecialchars($reqPath) ?>">
                                        <input type="hidden" name="username" value="<?= htmlspecialchars($currentDomain["username"] ?? "admin") ?>">
                                        <input type="hidden" name="item" value="<?= htmlspecialchars($item["name"]) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger text-uppercase fw-bold text-nowrap" title="Eliminar">
                                            <i class="bi bi-trash me-1"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Subir Archivos -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Subir Archivos a <?= htmlspecialchars(empty($reqPath) ? "/" : ("/" . $reqPath)) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/files/upload" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="domain" value="<?= htmlspecialchars($selectedDomain) ?>">
                <input type="hidden" name="path" value="<?= htmlspecialchars($reqPath) ?>">
                <input type="hidden" name="username" value="<?= htmlspecialchars($currentDomain["username"] ?? "admin") ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="fileUploadInput" class="form-label">Seleccionar Archivo(s) o Proyecto ZIP <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="fileUploadInput" name="files[]" multiple required>
                        <div class="form-text mt-2">Puedes seleccionar varios archivos a la vez o subir un archivo <code>.zip</code> para extraerlo inmediatamente.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary text-uppercase fw-bold">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Subir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Nueva Carpeta -->
<div class="modal fade" id="newFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Carpeta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/files/mkdir" method="POST">
                <input type="hidden" name="domain" value="<?= htmlspecialchars($selectedDomain) ?>">
                <input type="hidden" name="path" value="<?= htmlspecialchars($reqPath) ?>">
                <input type="hidden" name="username" value="<?= htmlspecialchars($currentDomain["username"] ?? "admin") ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="folderNameInput" class="form-label">Nombre de la carpeta <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="folderNameInput" name="folder_name" placeholder="ej. public o assets" required autofocus>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary text-uppercase fw-bold">
                        <i class="bi bi-folder-plus me-1"></i> Crear Carpeta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Nuevo Archivo -->
<div class="modal fade" id="newFileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Archivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/files/touch" method="POST">
                <input type="hidden" name="domain" value="<?= htmlspecialchars($selectedDomain) ?>">
                <input type="hidden" name="path" value="<?= htmlspecialchars($reqPath) ?>">
                <input type="hidden" name="username" value="<?= htmlspecialchars($currentDomain["username"] ?? "admin") ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="fileNameInput" class="form-label">Nombre del archivo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="fileNameInput" name="file_name" placeholder="ej. index.php o .env" required autofocus>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary text-uppercase fw-bold">
                        <i class="bi bi-file-earmark-plus me-1"></i> Crear Archivo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editor de Codigo en Linea -->
<div class="modal fade" id="editorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-monospace" id="editorModalTitle">Editor de Archivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <textarea id="editorContent" class="form-control border-0 font-monospace p-3" style="min-height: 520px; font-size: 0.9rem; line-height: 1.5; resize: vertical;" spellcheck="false"></textarea>
            </div>
            <div class="modal-footer justify-content-between">
                <span id="editorSaveStatus" class="small"></span>
                <div>
                    <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary text-uppercase fw-bold" onclick="saveEditorContent()">
                        <i class="bi bi-floppy me-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Modificar Permisos (Chmod) -->
<div class="modal fade" id="chmodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Permisos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/files/chmod" method="POST">
                <input type="hidden" name="domain" value="<?= htmlspecialchars($selectedDomain) ?>">
                <input type="hidden" name="path" value="<?= htmlspecialchars($reqPath) ?>">
                <input type="hidden" name="username" value="<?= htmlspecialchars($currentDomain["username"] ?? "admin") ?>">
                <input type="hidden" name="item" id="chmodItem">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="chmodMode" class="form-label">Permiso octal (ej. 0755 o 0644) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace" id="chmodMode" name="mode" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm text-uppercase fw-bold">
                        <i class="bi bi-check2 me-1"></i> Aplicar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentEditingPath = "";

function openEditor(relPath) {
    currentEditingPath = relPath;
    document.getElementById("editorModalTitle").textContent = "Editando: " + relPath;
    document.getElementById("editorContent").value = "Cargando contenido...";
    document.getElementById("editorSaveStatus").textContent = "";

    const modal = new bootstrap.Modal(document.getElementById("editorModal"));
    modal.show();

    const domain = "<?= htmlspecialchars($selectedDomain) ?>";
    const username = "<?= htmlspecialchars($currentDomain["username"] ?? "admin") ?>";

    fetch("/files/read?domain=" + encodeURIComponent(domain) + "&path=" + encodeURIComponent(relPath) + "&username=" + encodeURIComponent(username))
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                document.getElementById("editorContent").value = data.content;
            } else {
                document.getElementById("editorContent").value = "Error al leer archivo: " + (data.message || "Error");
            }
        })
        .catch(err => {
            document.getElementById("editorContent").value = "Error de conexion.";
        });
}

function saveEditorContent() {
    const content = document.getElementById("editorContent").value;
    const domain = "<?= htmlspecialchars($selectedDomain) ?>";
    const username = "<?= htmlspecialchars($currentDomain["username"] ?? "admin") ?>";
    const status = document.getElementById("editorSaveStatus");
    status.textContent = "Guardando...";

    const formData = new FormData();
    formData.append("domain", domain);
    formData.append("path", currentEditingPath);
    formData.append("username", username);
    formData.append("content", content);

    fetch("/files/save", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            status.innerHTML = "<span class='text-success fw-bold'><i class='bi bi-check2 me-1'></i>Guardado correctamente.</span>";
            setTimeout(() => { status.textContent = ""; }, 3000);
        } else {
            status.innerHTML = "<span class='text-danger fw-bold'>Error: " + (data.message || "Fallo") + "</span>";
        }
    })
    .catch(err => {
        status.innerHTML = "<span class='text-danger fw-bold'>Error al guardar.</span>";
    });
}

function openChmodModal(itemName, currentPerms) {
    document.getElementById("chmodItem").value = itemName;
    document.getElementById("chmodMode").value = currentPerms;
    const modal = new bootstrap.Modal(document.getElementById("chmodModal"));
    modal.show();
}
</script>
