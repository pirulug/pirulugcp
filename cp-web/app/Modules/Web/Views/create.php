<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h4 mb-0">Agregar Nuevo Dominio Web</h1>
    </div>
    <div>
        <a href="/web" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<form action="/web/store" method="POST">
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Configuracion del Dominio</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="domain" class="form-label">Nombre de Dominio <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="domain" name="domain" placeholder="ejemplo.com o app.empresa.com" required autofocus>
                <div class="form-text">Ingresa el dominio sin http:// ni www.</div>
            </div>

            <div class="mb-3">
                <label for="user_id" class="form-label">Usuario Propietario <span class="text-danger">*</span></label>
                <select class="form-select" id="user_id" name="user_id" required>
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= (int)$user["id"] ?>"><?= htmlspecialchars($user["username"]) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="php_version" class="form-label">Version de PHP-FPM <span class="text-danger">*</span></label>
                <select class="form-select" id="php_version" name="php_version" required>
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($phpVersions as $php): ?>
                        <option value="<?= htmlspecialchars($php["version"]) ?>" <?= ($php["version"] === "8.2") ? "selected" : "" ?>>
                            PHP <?= htmlspecialchars($php["version"]) ?> <?= ($php["status"] === "active") ? "(Activo)" : "" ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="doc_root_suffix" class="form-label">Carpeta Raiz Web (DocumentRoot) <span class="text-danger">*</span></label>
                <input type="text" class="form-control font-monospace" id="doc_root_suffix" name="doc_root_suffix" value="public_html" required>
                <div class="form-text mt-2">
                    Directorio publico relativo. Presets rapidos:
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 ms-1 text-uppercase fw-bold" onclick="document.getElementById('doc_root_suffix').value='public_html'">PHP Estandar</button>
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 ms-1 text-uppercase fw-bold" onclick="document.getElementById('doc_root_suffix').value='public_html/public'">Laravel</button>
                    <button type="button" class="btn btn-sm btn-outline-info py-0 px-2 ms-1 text-uppercase fw-bold" onclick="document.getElementById('doc_root_suffix').value='public_html/dist'">SPA / Vite</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Botonera Guardar / Cancelar fuera del card con sticky-bottom -->
    <div class="bg-body p-3 rounded d-flex justify-content-end gap-2 sticky-bottom">
        <a href="/web" class="btn btn-outline-secondary px-4 text-uppercase fw-bold">
            <i class="bi bi-arrow-left me-2"></i> Cancelar
        </a>
        <button type="submit" class="btn btn-primary px-5 text-uppercase fw-bold">
            <i class="bi bi-floppy me-2"></i> Guardar Dominio
        </button>
    </div>
</form>
