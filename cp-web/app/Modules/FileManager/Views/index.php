<!-- PrismJS Code Syntax Highlighting Styles -->
<link rel="stylesheet" href="/assets/plugins/prismjs.css">

<style>
/* Estilos profesionales del Editor de Codigo */
.code-editor-wrapper {
  display: flex;
  flex-direction: row;
  height: 65vh;
  min-height: 520px;
  width: 100%;
  position: relative;
  background-color: var(--bs-body-bg);
  border: 1px solid var(--bs-border-color);
  border-radius: 0.375rem;
  overflow: hidden;
}

.modal-fullscreen .code-editor-wrapper {
  height: calc(100vh - 140px);
}

.code-editor-gutter {
  width: 52px;
  min-width: 52px;
  background-color: var(--bs-tertiary-bg);
  color: var(--bs-secondary-color);
  font-family: "Google Sans Code", "Victor Mono", "Fira Code", "Roboto Mono", monospace;
  font-size: 0.88rem;
  line-height: 1.5;
  text-align: right;
  padding: 12px 10px 12px 0;
  user-select: none;
  border-right: 1px solid var(--bs-border-color);
  overflow: hidden;
  white-space: pre;
  box-sizing: border-box;
  opacity: 0.75;
}

.code-editor-textarea {
  flex: 1;
  width: 100%;
  height: 100% !important;
  min-height: 100% !important;
  border: none !important;
  outline: none !important;
  box-shadow: none !important;
  resize: none;
  font-family: "Google Sans Code", "Victor Mono", "Fira Code", "Roboto Mono", monospace;
  font-size: 0.88rem;
  line-height: 1.5;
  padding: 12px 14px;
  background: transparent;
  color: var(--bs-body-color);
  tab-size: 2;
  -moz-tab-size: 2;
  overflow: auto;
  white-space: pre;
  word-wrap: normal;
  box-sizing: border-box;
}

.code-editor-textarea:focus {
  outline: none !important;
  box-shadow: none !important;
}

.prism-preview-wrapper {
  height: 65vh;
  min-height: 520px;
  width: 100%;
  overflow: auto;
  border: 1px solid var(--bs-border-color);
  border-radius: 0.375rem;
  background-color: var(--bs-body-bg);
}

.modal-fullscreen .prism-preview-wrapper {
  height: calc(100vh - 140px);
}

.prism-preview-wrapper pre[class*="language-"] {
  margin: 0 !important;
  min-height: 100% !important;
  height: auto !important;
  border: none !important;
  border-radius: 0 !important;
  font-size: 0.88rem;
  line-height: 1.5;
  padding: 12px 14px !important;
}
</style>

<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1 class="h4 mb-0">Gestor de Archivos (File Manager)</h1>
  </div>
  <div>
    <form method="GET" action="/files" class="d-inline-flex align-items-center m-0">
      <label class="form-label me-2 mb-0">Dominio:</label>
      <select name="domain" class="form-select form-select-sm" onchange="this.form.submit()">
        <?php foreach ($domains as $dom): ?>
          <option value="<?= $dom["domain"] ?>" <?= ($selectedDomain === $dom["domain"]) ? "selected" : "" ?>>
            <?= $dom["domain"] ?>
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
          <li class="breadcrumb-item active fw-bold" aria-current="page"><?= $bc["name"] ?></li>
        <?php else: ?>
          <li class="breadcrumb-item">
            <a href="/files?domain=<?= urlencode($selectedDomain) ?>&path=<?= urlencode($bc["path"]) ?>" class="text-decoration-none text-primary">
              <?= $bc["name"] ?>
            </a>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ol>
  </nav>

  <div class="d-flex gap-1">
    <?php
      $hasComposerJson = false;
      foreach ($items as $it) {
        if (!$it["is_dir"] && $it["name"] === "composer.json") {
          $hasComposerJson = true;
          break;
        }
      }
    ?>
    <?php if ($hasComposerJson): ?>
      <form action="/files/composer" method="POST" class="d-inline m-0" onsubmit="return confirm('Ejecutar composer install en este directorio?')">
        <input type="hidden" name="domain" value="<?= $selectedDomain ?>">
        <input type="hidden" name="path" value="<?= $reqPath ?>">
        <input type="hidden" name="username" value="<?= $currentDomain["username"] ?? "admin" ?>">
        <input type="hidden" name="action" value="install">
        <button type="submit" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap" title="Instalar dependencias de composer.json">
          <i class="bi bi-box-seam me-1"></i> Composer
        </button>
      </form>
    <?php endif; ?>
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
            <td colspan="5" class="text-center py-3 text-muted">
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
                    <?= $item["name"] ?>
                  </a>
                <?php else: ?>
                  <?php
                    $editable = in_array($item["ext"], ["php", "html", "htm", "css", "js", "json", "txt", "env", "sql", "xml", "yaml", "yml", "md", "htaccess", "conf", "sh", "ts"]);
                    $isZip = ($item["ext"] === "zip");
                  ?>
                  <?php if ($editable): ?>
                    <a href="javascript:void(0)" onclick="openEditor('<?= $item["rel_path"] ?>')" class="text-decoration-none text-body d-inline-flex align-items-center">
                      <i class="bi bi-file-earmark-code text-primary me-2 fs-5"></i>
                      <?= $item["name"] ?>
                    </a>
                  <?php elseif ($isZip): ?>
                    <span class="d-inline-flex align-items-center text-body">
                      <i class="bi bi-file-earmark-zip text-danger me-2 fs-5"></i>
                      <?= $item["name"] ?>
                    </span>
                  <?php else: ?>
                    <span class="d-inline-flex align-items-center text-body">
                      <i class="bi bi-file-earmark text-muted me-2 fs-5"></i>
                      <?= $item["name"] ?>
                    </span>
                  <?php endif; ?>
                <?php endif; ?>
              </td>
              <td><span class="text-muted small"><?= $item["size"] ?></span></td>
              <td>
                <button type="button" class="badge bg-body-tertiary text-body border p-1 font-monospace text-decoration-none" onclick="openChmodModal('<?= $item["name"] ?>', '<?= $item["perms"] ?>')">
                  <?= $item["perms"] ?>
                </button>
              </td>
              <td class="text-muted small"><?= $item["mtime"] ?></td>
              <td class="text-end pe-3 text-nowrap">
                <div class="d-flex justify-content-end align-items-center gap-1">
                  <?php if (!$item["is_dir"] && $editable): ?>
                    <button type="button" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap" onclick="openEditor('<?= $item["rel_path"] ?>')" title="Editar Archivo">
                      <i class="bi bi-pencil me-1"></i> Editar
                    </button>
                  <?php endif; ?>
                  <?php if (!$item["is_dir"] && $item["ext"] === "zip"): ?>
                    <form action="/files/extract" method="POST" class="d-inline m-0" onsubmit="return confirm('Extraer <?= $item["name"] ?> en este directorio?')">
                      <input type="hidden" name="domain" value="<?= $selectedDomain ?>">
                      <input type="hidden" name="path" value="<?= $reqPath ?>">
                      <input type="hidden" name="username" value="<?= $currentDomain["username"] ?? "admin" ?>">
                      <input type="hidden" name="zip_file" value="<?= $item["name"] ?>">
                      <button type="submit" class="btn btn-sm btn-outline-warning text-uppercase fw-bold text-nowrap" title="Descomprimir ZIP">
                        <i class="bi bi-file-earmark-zip me-1"></i> Extraer
                      </button>
                    </form>
                  <?php endif; ?>

                  <div class="dropdown d-inline-block">
                    <button class="btn btn-sm btn-outline-secondary text-uppercase fw-bold dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="bi bi-three-dots-vertical"></i> Opciones
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border">
                      <li>
                        <a class="dropdown-item" href="/files/download?domain=<?= urlencode($selectedDomain) ?>&path=<?= urlencode($item["rel_path"]) ?>&username=<?= urlencode($currentDomain["username"] ?? "admin") ?>">
                          <i class="bi bi-download me-2"></i> Descargar
                        </a>
                      </li>
                      <li>
                        <button type="button" class="dropdown-item" onclick="openCopyModal('<?= $item["name"] ?>')">
                          <i class="bi bi-copy me-2"></i> Copiar
                        </button>
                      </li>
                      <li>
                        <button type="button" class="dropdown-item" onclick="openMoveModal('<?= $item["name"] ?>')">
                          <i class="bi bi-box-arrow-up-right me-2"></i> Mover
                        </button>
                      </li>
                      <li>
                        <button type="button" class="dropdown-item" onclick="openRenameModal('<?= $item["name"] ?>')">
                          <i class="bi bi-pencil-square me-2"></i> Renombrar
                        </button>
                      </li>
                      <li>
                        <button type="button" class="dropdown-item" onclick="openCompressModal('<?= $item["name"] ?>')">
                          <i class="bi bi-file-earmark-zip me-2"></i> Comprimir
                        </button>
                      </li>
                      <li>
                        <button type="button" class="dropdown-item" onclick="openChmodModal('<?= $item["name"] ?>', '<?= $item["perms"] ?>')">
                          <i class="bi bi-shield-lock me-2"></i> Permisos (<?= $item["perms"] ?>)
                        </button>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <button type="button" class="dropdown-item text-danger" onclick="openDeleteModal('<?= $item["name"] ?>')">
                          <i class="bi bi-trash me-2 text-danger"></i> <span class="text-danger">Eliminar</span>
                        </button>
                      </li>
                    </ul>
                  </div>
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
        <h5 class="modal-title">Subir Archivos a <?= empty($reqPath) ? "/" : ("/" . $reqPath) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="/files/upload" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="domain" value="<?= $selectedDomain ?>">
        <input type="hidden" name="path" value="<?= $reqPath ?>">
        <input type="hidden" name="username" value="<?= $currentDomain["username"] ?? "admin" ?>">
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
        <input type="hidden" name="domain" value="<?= $selectedDomain ?>">
        <input type="hidden" name="path" value="<?= $reqPath ?>">
        <input type="hidden" name="username" value="<?= $currentDomain["username"] ?? "admin" ?>">
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
        <input type="hidden" name="domain" value="<?= $selectedDomain ?>">
        <input type="hidden" name="path" value="<?= $reqPath ?>">
        <input type="hidden" name="username" value="<?= $currentDomain["username"] ?? "admin" ?>">
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

<!-- Modal: Copiar Elemento -->
<div class="modal fade" id="copyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Copiar Elemento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="/files/copy" method="POST">
        <input type="hidden" name="domain" value="<?= $selectedDomain ?>">
        <input type="hidden" name="path" value="<?= $reqPath ?>">
        <input type="hidden" name="username" value="<?= $currentDomain["username"] ?? "admin" ?>">
        <input type="hidden" name="item" id="copyItem">
        <div class="modal-body">
          <div class="mb-3">
            <label for="copyDestName" class="form-label">Nombre de la copia <span class="text-danger">*</span></label>
            <input type="text" class="form-control font-monospace" id="copyDestName" name="dest_name" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary text-uppercase fw-bold">
            <i class="bi bi-copy me-1"></i> Copiar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Mover Elemento -->
<div class="modal fade" id="moveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Mover Elemento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="/files/move" method="POST">
        <input type="hidden" name="domain" value="<?= $selectedDomain ?>">
        <input type="hidden" name="path" value="<?= $reqPath ?>">
        <input type="hidden" name="username" value="<?= $currentDomain["username"] ?? "admin" ?>">
        <input type="hidden" name="item" id="moveItem">
        <div class="modal-body">
          <p class="small text-muted mb-2">Moviendo: <strong id="moveItemDisplay" class="text-body"></strong></p>
          <div class="mb-3">
            <label for="moveDestFolder" class="form-label">Carpeta destino (relativa a la raíz del dominio) <span class="text-danger">*</span></label>
            <input type="text" class="form-control font-monospace" id="moveDestFolder" name="dest_folder" placeholder="ej. public_html o public_html/assets" required>
            <div class="form-text small">Usa <code>/</code> o déjalo vacío para mover a la raíz del dominio.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary text-uppercase fw-bold">
            <i class="bi bi-box-arrow-up-right me-1"></i> Mover
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Renombrar Elemento -->
<div class="modal fade" id="renameModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Renombrar Elemento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="/files/rename" method="POST">
        <input type="hidden" name="domain" value="<?= $selectedDomain ?>">
        <input type="hidden" name="path" value="<?= $reqPath ?>">
        <input type="hidden" name="username" value="<?= $currentDomain["username"] ?? "admin" ?>">
        <input type="hidden" name="old_name" id="renameOldName">
        <div class="modal-body">
          <div class="mb-3">
            <label for="renameNewName" class="form-label">Nuevo nombre <span class="text-danger">*</span></label>
            <input type="text" class="form-control font-monospace" id="renameNewName" name="new_name" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary text-uppercase fw-bold">
            <i class="bi bi-pencil-square me-1"></i> Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Comprimir Elemento -->
<div class="modal fade" id="compressModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Comprimir en ZIP</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="/files/compress" method="POST">
        <input type="hidden" name="domain" value="<?= $selectedDomain ?>">
        <input type="hidden" name="path" value="<?= $reqPath ?>">
        <input type="hidden" name="username" value="<?= $currentDomain["username"] ?? "admin" ?>">
        <input type="hidden" name="item" id="compressItem">
        <div class="modal-body">
          <div class="mb-3">
            <label for="compressZipName" class="form-label">Nombre del archivo ZIP <span class="text-danger">*</span></label>
            <input type="text" class="form-control font-monospace" id="compressZipName" name="zip_name" placeholder="archivo.zip" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary text-uppercase fw-bold">
            <i class="bi bi-file-earmark-zip me-1"></i> Comprimir
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Eliminar Elemento -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Eliminar Elemento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="/files/delete" method="POST">
        <input type="hidden" name="domain" value="<?= $selectedDomain ?>">
        <input type="hidden" name="path" value="<?= $reqPath ?>">
        <input type="hidden" name="username" value="<?= $currentDomain["username"] ?? "admin" ?>">
        <input type="hidden" name="item" id="deleteItem">
        <div class="modal-body">
          <p class="mb-0">¿Estás seguro de eliminar <strong id="deleteItemDisplay" class="text-danger"></strong>? Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm text-uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger btn-sm text-uppercase fw-bold">
            <i class="bi bi-trash me-1"></i> Eliminar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Editor y Visor de Codigo con PrismJS -->
<div class="modal fade" id="editorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border">
      <div class="modal-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 px-3 border-bottom">
        <div class="d-flex align-items-center">
          <i class="bi bi-file-earmark-code text-primary fs-5 me-2"></i>
          <h5 class="modal-title font-monospace fw-bold mb-0" id="editorModalTitle">Editor de Archivo</h5>
          <span id="editorLanguageBadge" class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase ms-2">TEXT</span>
        </div>

        <div class="d-flex align-items-center gap-2">
          <!-- Selector de Lenguaje -->
          <div class="input-group input-group-sm" style="width: auto;">
            <label class="input-group-text bg-body-tertiary text-muted small" for="editorLanguageSelect">Sintaxis</label>
            <select class="form-select form-select-sm font-monospace" id="editorLanguageSelect" onchange="onLanguageChange(this.value)">
              <option value="php">PHP</option>
              <option value="javascript">JavaScript (JS)</option>
              <option value="markup">HTML / XML / SVG</option>
              <option value="css">CSS</option>
              <option value="sql">SQL</option>
              <option value="json">JSON</option>
              <option value="bash">Bash / Shell / .env</option>
              <option value="apacheconf">Apache / .htaccess / Conf</option>
              <option value="markdown">Markdown (MD)</option>
              <option value="yaml">YAML</option>
              <option value="plaintext">Texto Plano</option>
            </select>
          </div>

          <!-- Alternador Modo Editor / Modo Resaltado -->
          <div class="d-flex gap-1" role="group" aria-label="Modo de vista">
            <button type="button" id="btnModeEdit" class="btn btn-sm btn-primary text-uppercase fw-bold" onclick="switchEditorMode('edit')">
              <i class="bi bi-pencil-square me-1"></i> Editor
            </button>
            <button type="button" id="btnModePreview" class="btn btn-sm btn-outline-primary text-uppercase fw-bold" onclick="switchEditorMode('preview')">
              <i class="bi bi-code-square me-1"></i> Resaltado
            </button>
          </div>

          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyEditorCode()" title="Copiar Código al Portapapeles">
            <i class="bi bi-clipboard"></i>
          </button>

          <button type="button" class="btn btn-sm btn-outline-secondary" id="btnEditorFullscreen" onclick="toggleEditorFullscreen()" title="Pantalla Completa">
            <i class="bi bi-arrows-fullscreen"></i>
          </button>

          <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
      </div>

      <div class="modal-body p-3">
        <!-- Contenedor 1: Modo Edicion Interactivo con Numeros de Linea -->
        <div id="editorEditContainer" class="code-editor-wrapper">
          <div id="editorGutter" class="code-editor-gutter">1</div>
          <textarea id="editorContent" class="code-editor-textarea" spellcheck="false" placeholder="Contenido del archivo..."></textarea>
        </div>

        <!-- Contenedor 2: Modo Vista Previa Resaltada con PrismJS -->
        <div id="editorPreviewContainer" class="prism-preview-wrapper d-none">
          <pre id="prismPreElement" class="line-numbers language-markup"><code id="prismCodeDisplay" class="language-markup"></code></pre>
        </div>
      </div>

      <div class="modal-footer justify-content-between align-items-center py-2 px-3 border-top">
        <div class="d-flex align-items-center gap-3">
          <span id="editorFileInfo" class="text-muted small font-monospace">0 lineas | 0 caracteres</span>
          <span id="editorSaveStatus" class="small"></span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="text-muted small d-none d-md-inline me-2"><kbd class="bg-body-tertiary text-body border">Ctrl + S</kbd> para guardar</span>
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
        <input type="hidden" name="domain" value="<?= $selectedDomain ?>">
        <input type="hidden" name="path" value="<?= $reqPath ?>">
        <input type="hidden" name="username" value="<?= $currentDomain["username"] ?? "admin" ?>">
        <input type="hidden" name="item" id="chmodItem">
        <div class="modal-body">
          <div class="mb-3">
            <label for="chmodMode" class="form-label">Permiso octal (ej. 0755 o 0644) <span class="text-danger">*</span></label>
            <input type="text" class="form-control font-monospace" id="chmodMode" name="mode" required>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary flex-fill text-uppercase fw-bold" onclick="document.getElementById('chmodMode').value='0644'">0644 (Archivo)</button>
            <button type="button" class="btn btn-sm btn-outline-secondary flex-fill text-uppercase fw-bold" onclick="document.getElementById('chmodMode').value='0755'">0755 (Carpeta)</button>
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

<!-- PrismJS Script -->
<script src="/assets/plugins/prismjs.js"></script>

<script>
let currentEditingPath = "";
let currentLanguage = "plaintext";
let currentViewMode = "edit";

const languageExtensionMap = {
  "php": "php",
  "phtml": "php",
  "js": "javascript",
  "mjs": "javascript",
  "cjs": "javascript",
  "ts": "javascript",
  "json": "json",
  "css": "css",
  "scss": "css",
  "sass": "css",
  "less": "css",
  "html": "markup",
  "htm": "markup",
  "svg": "markup",
  "xml": "markup",
  "sql": "sql",
  "sh": "bash",
  "bash": "bash",
  "env": "bash",
  "ini": "bash",
  "conf": "apacheconf",
  "htaccess": "apacheconf",
  "md": "markdown",
  "yaml": "yaml",
  "yml": "yaml",
  "txt": "plaintext"
};

function detectLanguage(filePath) {
  const parts = filePath.split(".");
  if (parts.length > 1) {
    const ext = parts.pop().toLowerCase();
    if (languageExtensionMap[ext]) {
      return languageExtensionMap[ext];
    }
  }
  const basename = parts[parts.length - 1].toLowerCase();
  if (basename === ".env" || basename === ".env.example") return "bash";
  if (basename === ".htaccess") return "apacheconf";
  return "plaintext";
}

function updateGutterAndStats() {
  const textarea = document.getElementById("editorContent");
  const gutter = document.getElementById("editorGutter");
  if (!textarea) return;

  const text = textarea.value;
  const lines = text.length === 0 ? 1 : text.split("\n").length;
  const chars = text.length;
  const sizeKb = (new Blob([text]).size / 1024).toFixed(2);

  if (gutter) {
    let numbers = "";
    for (let i = 1; i <= lines; i++) {
      numbers += i + "\n";
    }
    gutter.textContent = numbers;
    gutter.scrollTop = textarea.scrollTop;
  }

  const infoEl = document.getElementById("editorFileInfo");
  if (infoEl) {
    infoEl.textContent = lines + " líneas | " + chars + " caracteres (" + sizeKb + " KB)";
  }
}

function applyPrismHighlight() {
  const codeEl = document.getElementById("prismCodeDisplay");
  const preEl = document.getElementById("prismPreElement");
  const content = document.getElementById("editorContent").value;

  codeEl.className = "language-" + currentLanguage;
  preEl.className = "line-numbers language-" + currentLanguage;
  codeEl.textContent = content;

  if (window.Prism) {
    Prism.highlightElement(codeEl);
  }
}

function switchEditorMode(mode) {
  currentViewMode = mode;
  const editContainer = document.getElementById("editorEditContainer");
  const previewContainer = document.getElementById("editorPreviewContainer");
  const btnEdit = document.getElementById("btnModeEdit");
  const btnPreview = document.getElementById("btnModePreview");

  if (mode === "preview") {
    applyPrismHighlight();
    editContainer.classList.add("d-none");
    previewContainer.classList.remove("d-none");
    btnEdit.className = "btn btn-sm btn-outline-primary text-uppercase fw-bold";
    btnPreview.className = "btn btn-sm btn-primary text-uppercase fw-bold";
  } else {
    previewContainer.classList.add("d-none");
    editContainer.classList.remove("d-none");
    btnEdit.className = "btn btn-sm btn-primary text-uppercase fw-bold";
    btnPreview.className = "btn btn-sm btn-outline-primary text-uppercase fw-bold";
    updateGutterAndStats();
    document.getElementById("editorContent").focus();
  }
}

function toggleEditorFullscreen() {
  const modalDialog = document.querySelector("#editorModal .modal-dialog");
  const btn = document.getElementById("btnEditorFullscreen");
  if (modalDialog.classList.contains("modal-fullscreen")) {
    modalDialog.classList.remove("modal-fullscreen");
    modalDialog.classList.add("modal-xl");
    btn.innerHTML = '<i class="bi bi-arrows-fullscreen"></i>';
    btn.title = "Pantalla Completa";
  } else {
    modalDialog.classList.remove("modal-xl");
    modalDialog.classList.add("modal-fullscreen");
    btn.innerHTML = '<i class="bi bi-fullscreen-exit"></i>';
    btn.title = "Restaurar Tamaño";
  }
}

function onLanguageChange(lang) {
  currentLanguage = lang;
  document.getElementById("editorLanguageBadge").textContent = lang.toUpperCase();
  if (currentViewMode === "preview") {
    applyPrismHighlight();
  }
}

function openEditor(relPath) {
  currentEditingPath = relPath;
  currentLanguage = detectLanguage(relPath);

  document.getElementById("editorModalTitle").textContent = relPath;
  document.getElementById("editorLanguageBadge").textContent = currentLanguage.toUpperCase();
  document.getElementById("editorLanguageSelect").value = currentLanguage;
  document.getElementById("editorContent").value = "Cargando contenido...";
  document.getElementById("editorSaveStatus").textContent = "";

  switchEditorMode("edit");

  const modalEl = document.getElementById("editorModal");
  const modal = new bootstrap.Modal(modalEl);
  modal.show();

  const domain = "<?= $selectedDomain ?>";
  const username = "<?= $currentDomain["username"] ?? "admin" ?>";

  fetch("/files/read?domain=" + encodeURIComponent(domain) + "&path=" + encodeURIComponent(relPath) + "&username=" + encodeURIComponent(username))
    .then(res => res.json())
    .then(data => {
      if (data.status === "success") {
        document.getElementById("editorContent").value = data.content;
        updateGutterAndStats();
      } else {
        document.getElementById("editorContent").value = "Error al leer archivo: " + (data.message ? data.message : "Error");
        updateGutterAndStats();
      }
    })
    .catch(err => {
      document.getElementById("editorContent").value = "Error de conexión al leer archivo.";
      updateGutterAndStats();
    });
}

function saveEditorContent() {
  const content = document.getElementById("editorContent").value;
  const domain = "<?= $selectedDomain ?>";
  const username = "<?= $currentDomain["username"] ?? "admin" ?>";
  const status = document.getElementById("editorSaveStatus");
  status.innerHTML = "<span class='text-muted'><i class='bi bi-hourglass-split me-1'></i>Guardando...</span>";

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
      status.innerHTML = "<span class='text-success fw-bold'><i class='bi bi-check2-circle me-1'></i>Guardado correctamente.</span>";
      if (currentViewMode === "preview") {
        applyPrismHighlight();
      }
      setTimeout(() => { status.textContent = ""; }, 3500);
    } else {
      status.innerHTML = "<span class='text-danger fw-bold'>Error: " + (data.message ? data.message : "Fallo") + "</span>";
    }
  })
  .catch(err => {
    status.innerHTML = "<span class='text-danger fw-bold'>Error de conexión al guardar.</span>";
  });
}

function copyEditorCode() {
  const content = document.getElementById("editorContent").value;
  copyToClipboard(content, "Código copiado al portapapeles");
}

// Sincronizacion de Gutter, Indentacion con Tab y atajo Ctrl+S
document.addEventListener("DOMContentLoaded", function () {
  const editorArea = document.getElementById("editorContent");
  const gutter = document.getElementById("editorGutter");

  if (editorArea) {
    editorArea.addEventListener("input", function () {
      updateGutterAndStats();
    });

    editorArea.addEventListener("scroll", function () {
      if (gutter) {
        gutter.scrollTop = editorArea.scrollTop;
      }
    });

    editorArea.addEventListener("keydown", function (e) {
      // Atajo Ctrl+S o Cmd+S
      if ((e.ctrlKey || e.metaKey) && e.key === "s") {
        e.preventDefault();
        saveEditorContent();
        return;
      }

      // Tabulacion con 2 espacios
      if (e.key === "Tab") {
        e.preventDefault();
        const start = this.selectionStart;
        const end = this.selectionEnd;
        const value = this.value;

        this.value = value.substring(0, start) + "  " + value.substring(end);
        this.selectionStart = this.selectionEnd = start + 2;
        updateGutterAndStats();
      }
    });
  }

  // Atajo global Ctrl+S mientras el modal del editor esté abierto
  document.addEventListener("keydown", function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === "s") {
      const modalEl = document.getElementById("editorModal");
      if (modalEl && modalEl.classList.contains("show")) {
        e.preventDefault();
        saveEditorContent();
      }
    }
  });
});

function openCopyModal(itemName) {
  document.getElementById("copyItem").value = itemName;
  document.getElementById("copyDestName").value = itemName + "_copia";
  const modal = new bootstrap.Modal(document.getElementById("copyModal"));
  modal.show();
}

function openMoveModal(itemName) {
  document.getElementById("moveItem").value = itemName;
  document.getElementById("moveItemDisplay").textContent = itemName;
  document.getElementById("moveDestFolder").value = "<?= $reqPath ?>";
  const modal = new bootstrap.Modal(document.getElementById("moveModal"));
  modal.show();
}

function openRenameModal(itemName) {
  document.getElementById("renameOldName").value = itemName;
  document.getElementById("renameNewName").value = itemName;
  const modal = new bootstrap.Modal(document.getElementById("renameModal"));
  modal.show();
}

function openCompressModal(itemName) {
  document.getElementById("compressItem").value = itemName;
  document.getElementById("compressZipName").value = itemName + ".zip";
  const modal = new bootstrap.Modal(document.getElementById("compressModal"));
  modal.show();
}

function openDeleteModal(itemName) {
  document.getElementById("deleteItem").value = itemName;
  document.getElementById("deleteItemDisplay").textContent = itemName;
  const modal = new bootstrap.Modal(document.getElementById("deleteModal"));
  modal.show();
}

function openChmodModal(itemName, currentPerms) {
  document.getElementById("chmodItem").value = itemName;
  document.getElementById("chmodMode").value = currentPerms;
  const modal = new bootstrap.Modal(document.getElementById("chmodModal"));
  modal.show();
}
</script>
