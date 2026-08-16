<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h4 mb-0">Crear Base de Datos (MariaDB)</h1>
    </div>
    <div>
        <a href="/database" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<form action="/database/store" method="POST">
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Parametros de MariaDB</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="user_id" class="form-label">Usuario Propietario <span class="text-danger">*</span></label>
                <select class="form-select" id="user_id" name="user_id" required>
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= (int)$user["id"] ?>" data-username="<?= htmlspecialchars($user["username"]) ?>">
                            <?= htmlspecialchars($user["username"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="db_name" class="form-label">Nombre de la Base de Datos <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="db_name" name="db_name" placeholder="tienda o blog" required autofocus>
                <div class="form-text">El nombre final tendra el prefijo del usuario (ej. <span id="user_prefix_db"><?= htmlspecialchars($users[0]["username"] ?? "admin") ?>_</span>tienda).</div>
            </div>

            <div class="mb-3">
                <label for="db_user" class="form-label">Usuario de Base de Datos <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="db_user" name="db_user" placeholder="user_app" required>
            </div>

            <div class="mb-3">
                <label for="db_password" class="form-label">Contrasena de la Base de Datos <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control font-monospace" id="db_password" name="db_password" placeholder="Contrasena segura" required>
                    <button class="btn btn-outline-secondary text-uppercase fw-bold" type="button" onclick="generatePassword()">
                        <i class="bi bi-shuffle me-1"></i> Generar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Botonera Guardar / Cancelar fuera del card con sticky-bottom -->
    <div class="bg-body p-3 rounded d-flex justify-content-end gap-2 sticky-bottom">
        <a href="/database" class="btn btn-outline-secondary px-4 text-uppercase fw-bold">
            <i class="bi bi-arrow-left me-2"></i> Cancelar
        </a>
        <button type="submit" class="btn btn-primary px-5 text-uppercase fw-bold">
            <i class="bi bi-floppy me-2"></i> Guardar Base de Datos
        </button>
    </div>
</form>

<script>
function generatePassword() {
    const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_-";
    let pass = "";
    for (let i = 0; i < 16; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById("db_password").value = pass;
}

document.getElementById("user_id").addEventListener("change", function () {
    const selectedOption = this.options[this.selectedIndex];
    const username = selectedOption.getAttribute("data-username") || "admin";
    document.getElementById("user_prefix_db").textContent = username + "_";
});
</script>
