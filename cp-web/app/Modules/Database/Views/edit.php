<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
  <div>
    <h1 class="h4 mb-0">Editar Bases de Datos</h1>
  </div>
  <div>
    <a href="/database" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
      <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
  </div>
</div>

<form action="/database/update/<?= (int)$database["id"] ?>" method="POST">
  <div class="card mb-3">
    <div class="card-header">
      <h5 class="card-title mb-0">Parametros de la Base de Datos</h5>
    </div>
    <div class="card-body">
      <!-- Campo: Base de Datos -->
      <div class="mb-3">
        <label class="form-label" for="db_name_display">Base de Datos</label>
        <input type="text" class="form-control" id="db_name_display" value="<?= $shortDbName ?>" disabled>
        <div class="text-muted small fst-italic mt-1">
          <?= $database["db_name"] ?>
        </div>
      </div>

      <!-- Campo: Nombre de Usuario -->
      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <label class="form-label" for="db_user_display">Nombre de Usuario</label>
          <span class="text-muted small fst-italic">(El tamano maximo de caracteres es 32, incluyendo el prefijo)</span>
        </div>
        <input type="text" class="form-control" id="db_user_display" value="<?= $shortDbUser ?>" disabled>
        <div class="text-muted small fst-italic mt-1">
          <?= $database["db_user"] ?>
        </div>
      </div>

      <!-- Campo: Contrasena con icono generador -->
      <div class="mb-3">
        <div class="d-flex align-items-center gap-2 mb-1">
          <label class="form-label" for="db_password">Contrasena</label>
          <a href="javascript:void(0)" onclick="generatePassword()" class="text-success fs-5 text-decoration-none" title="Generar contrasena segura">
            <i class="bi bi-arrow-clockwise"></i>
          </a>
        </div>
        <input type="password" class="form-control" id="db_password" name="db_password" placeholder="Dejar en blanco para no cambiar" data-pr-toggle-password>
        
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
      <i class="bi bi-floppy me-2"></i> Guardar Cambios
    </button>
  </div>
</form>

<script>
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

  // Mezclar
  pass = pass.split("").sort(() => 0.5 - Math.random()).join("");

  const input = document.getElementById("db_password");
  input.value = pass;
  input.type = "text";
}
</script>
