<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
  <div>
    <h1 class="h4 mb-0">Anadir Base de Datos</h1>
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
      <h5 class="card-title mb-0">Nueva Base de Datos MariaDB</h5>
    </div>
    <div class="card-body">
      <!-- Mensaje informativo de prefijo -->
      <div class="text-muted small fst-italic mb-3">
        El prefijo "<strong class="text-body" id="user_prefix_display"><?= htmlspecialchars($users[0]["username"] ?? "admin") ?>_</strong>" sera automaticamente anadido a los nombres de usuario y base de datos
      </div>

      <!-- Selector de Usuario Propietario (si hay varios) -->
      <div class="mb-3">
        <label for="user_id" class="form-label">Usuario Propietario <span class="text-danger">*</span></label>
        <select class="form-select" id="user_id" name="user_id" required>
          <?php foreach ($users as $user): ?>
            <option value="<?= (int)$user["id"] ?>" data-username="<?= htmlspecialchars($user["username"]) ?>">
              <?= htmlspecialchars($user["username"]) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Campo: Base de Datos -->
      <div class="mb-3">
        <label for="db_name" class="form-label">Base de Datos <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="db_name" name="db_name" placeholder="ej. pirupos" required autofocus oninput="updatePreviews()">
        <div class="text-muted small fst-italic mt-1" id="db_name_preview">
          <?= htmlspecialchars($users[0]["username"] ?? "admin") ?>_
        </div>
      </div>

      <!-- Campo: Tipo -->
      <div class="mb-3">
        <label for="db_type" class="form-label">Tipo</label>
        <select class="form-select" id="db_type" name="db_type">
          <option value="mysql" selected>mysql</option>
          <option value="mariadb">mariadb</option>
        </select>
      </div>

      <!-- Campo: Nombre de Usuario -->
      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <label for="db_user" class="form-label mb-0">Nombre de Usuario <span class="text-danger">*</span></label>
          <span class="text-muted small fst-italic">(El tamano maximo de caracteres es 32, incluyendo el prefijo)</span>
        </div>
        <input type="text" class="form-control" id="db_user" name="db_user" placeholder="ej. pirupos" required oninput="updatePreviews()">
        <div class="text-muted small fst-italic mt-1" id="db_user_preview">
          <?= htmlspecialchars($users[0]["username"] ?? "admin") ?>_
        </div>
      </div>

      <!-- Campo: Contrasena con icono generador -->
      <div class="mb-3">
        <div class="d-flex align-items-center gap-2 mb-1">
          <label for="db_password" class="form-label mb-0">Contrasena <span class="text-danger">*</span></label>
          <a href="javascript:void(0)" onclick="generatePassword()" class="text-success fs-5 text-decoration-none" title="Generar contrasena segura">
            <i class="bi bi-arrow-clockwise"></i>
          </a>
        </div>
        <input type="password" class="form-control" id="db_password" name="db_password" placeholder="Ingresar contrasena segura" data-pr-toggle-password required>
        
        <!-- Requisitos de Contrasena -->
        <div class="text-muted small mt-2">
          <div>La contrasena debe contener:</div>
          <ul class="mb-0 ps-3 mt-1">
            <li>Minimo 8 caracteres</li>
            <li>1 caracter en mayusculas y 1 en minusculas</li>
            <li>1 numero</li>
          </ul>
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
function getPrefix() {
  const sel = document.getElementById("user_id");
  const opt = sel.options[sel.selectedIndex];
  const user = opt ? opt.getAttribute("data-username") : "admin";
  return user + "_";
}

function updatePreviews() {
  const prefix = getPrefix();
  document.getElementById("user_prefix_display").textContent = prefix;

  const dbVal = document.getElementById("db_name").value.trim();
  document.getElementById("db_name_preview").textContent = prefix + dbVal;

  const userVal = document.getElementById("db_user").value.trim();
  document.getElementById("db_user_preview").textContent = prefix + userVal;
}

function generatePassword() {
  const upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  const lower = "abcdefghijklmnopqrstuvwxyz";
  const nums = "0123456789";
  const syms = "!@#$%&*_-";
  
  let pass = "";
  pass += upper.charAt(Math.floor(Math.random() * upper.length));
  pass += lower.charAt(Math.floor(Math.random() * lower.length));
  pass += nums.charAt(Math.floor(Math.random() * nums.length));
  pass += syms.charAt(Math.floor(Math.random() * syms.length));

  const all = upper + lower + nums + syms;
  for (let i = 4; i < 16; i++) {
    pass += all.charAt(Math.floor(Math.random() * all.length));
  }

  pass = pass.split("").sort(() => 0.5 - Math.random()).join("");

  const input = document.getElementById("db_password");
  input.value = pass;
  input.type = "text";
}

document.getElementById("user_id").addEventListener("change", updatePreviews);
</script>
