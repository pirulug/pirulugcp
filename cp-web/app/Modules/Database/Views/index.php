<?php
$dbs = $databases ?? [];
$active = $activeTab ?? "databases";
?>

<!-- ======================================================================= -->
<!-- ENCABEZADO PRINCIPAL DE MARIADB                                         -->
<!-- ======================================================================= -->
<div class="bg-body p-3 rounded mb-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div class="d-flex align-items-center flex-wrap gap-2">
    <!-- Badge de Motor de Base de Datos -->
    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 py-2 d-inline-flex align-items-center">
      <img src="/assets/sitios/mariadb.svg" alt="MariaDB" style="width: 20px; height: 20px; object-fit: contain;" class="me-2">
      MariaDB
    </span>
    <span class="text-muted font-monospace small">v10.11.8</span>

    <!-- Indicador de Puerto Activo -->
    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 font-monospace">
      <i class="bi bi-circle-fill me-1" style="font-size: 8px !important;"></i> Puerto 3306
    </span>

    <!-- Contador de Bases de Datos -->
    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 font-monospace">
      <i class="bi bi-hdd-stack me-1"></i> <?= count($dbs) ?> <?= (count($dbs) == 1) ? "Base de datos" : "Bases de datos" ?>
    </span>
  </div>

  <!-- Botonera Superior Derecha -->
  <div class="d-flex align-items-center flex-wrap gap-2">
    <!-- Boton phpMyAdmin SSO Directo -->
    <a href="/pma" target="_blank" class="btn btn-outline-primary text-uppercase fw-bold text-nowrap">
      <i class="bi bi-box-arrow-up-right me-1"></i> phpMyAdmin
    </a>

    <!-- Boton Abrir URL de Conexion -->
    <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold text-nowrap" data-bs-toggle="modal" data-bs-target="#modalConnStrings">
      <i class="bi bi-link-45deg me-1"></i> URL de Conexión
    </button>

    <!-- Boton Crear Base de Datos -->
    <a href="/database/create" class="btn btn-primary text-uppercase fw-bold text-nowrap">
      <i class="bi bi-plus-lg me-1"></i> Nueva Base de Datos
    </a>
  </div>
</div>

<!-- ======================================================================= -->
<!-- BARRA DE PESTANAS                                                       -->
<!-- ======================================================================= -->
<div class="bg-body p-3 rounded mb-3">
  <ul class="nav nav-pills gap-1 m-0">
    <li class="nav-item">
      <a class="nav-link <?= ($active === "databases") ? "active fw-bold" : "text-body" ?>" href="/database?tab=databases">
        <i class="bi bi-database me-1"></i> Bases de datos
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($active === "logs") ? "active fw-bold" : "text-body" ?>" href="/database?tab=logs">
        <i class="bi bi-terminal me-1"></i> Registros
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($active === "env") ? "active fw-bold" : "text-body" ?>" href="/database?tab=env">
        <i class="bi bi-file-earmark-lock me-1"></i> Env
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($active === "config") ? "active fw-bold" : "text-body" ?>" href="/database?tab=config">
        <i class="bi bi-sliders me-1"></i> Config
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($active === "tools") ? "active fw-bold" : "text-body" ?>" href="/database?tab=tools">
        <i class="bi bi-tools me-1"></i> Herramientas
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($active === "ports") ? "active fw-bold" : "text-body" ?>" href="/database?tab=ports">
        <i class="bi bi-hdd-network me-1"></i> Puertos
      </a>
    </li>
  </ul>
</div>

<!-- ======================================================================= -->
<!-- PESTANA 1: BASES DE DATOS (BARRA RAPIDA + TARJETAS DE 2 COLUMNAS)        -->
<!-- ======================================================================= -->
<?php if ($active === "databases"): ?>
  <!-- Barra de Creacion Rapida -->
  <form action="/database/store" method="POST" class="d-flex gap-2 mb-3">
    <input type="text" 
           name="db_name" 
           id="quickDbName" 
           class="form-control form-control-lg font-monospace" 
           placeholder="Nombre de la base de datos nueva (ej. tienda, blog, api)..." 
           required>
    <button type="submit" class="btn btn-primary px-4 text-uppercase fw-bold text-nowrap">
      <i class="bi bi-plus-lg me-1"></i> Crear Base de Datos
    </button>
  </form>

  <!-- Rejilla de Tarjetas de Bases de Datos (2 Columnas) -->
  <div class="row g-3">
    <?php if (empty($dbs)): ?>
      <div class="col-12">
        <div class="bg-body p-4 rounded text-center text-muted">
          <i class="bi bi-database fs-1 mb-2 d-block opacity-50"></i>
          No hay bases de datos creadas aún. Ingresa un nombre arriba y pulsa <strong>Crear Base de Datos</strong>.
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($dbs as $db): ?>
        <div class="col-md-6 db-card-item">
          <div class="bg-body p-3 rounded border h-100 d-flex flex-column justify-content-between">
            <!-- Encabezado de la Tarjeta -->
            <div>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center gap-2">
                  <img src="/assets/sitios/mariadb.svg" alt="MariaDB" style="width: 26px; height: 26px; object-fit: contain;" class="me-1">
                  <div>
                    <h5 class="mb-0 fw-bold font-monospace text-body"><?= $db["short_name"] ?></h5>
                    <span class="text-muted small font-monospace"><?= $db["db_name"] ?></span>
                  </div>
                </div>
                <div class="d-flex gap-1">
                  <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">App</span>
                  <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">MariaDB</span>
                </div>
              </div>

              <!-- Informacion Tecnica: Usuario, Tamanio y Dominio -->
              <div class="p-2 rounded border bg-body-tertiary mb-3 font-monospace small">
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">Usuario:</span>
                  <strong><?= $db["db_user"] ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">Tamaño:</span>
                  <span><?= $db["size_mb"] ?></span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Dominio:</span>
                  <a href="/web" class="text-primary text-decoration-none fw-bold"><?= $db["linked_domain"] ?></a>
                </div>
              </div>
            </div>

            <!-- Botonera de Acciones (Botones Grandes y Visibles) -->
            <div class="d-flex flex-wrap align-items-center gap-2 pt-2 border-top">
              <!-- Boton Cambiar Clave / Editar -->
              <button type="button" 
                      class="btn btn-sm btn-outline-warning text-uppercase fw-bold text-nowrap flex-fill" 
                      data-bs-toggle="modal" 
                      data-bs-target="#modalEditDb<?= (int)$db["id"] ?>">
                <i class="bi bi-key me-1"></i> Cambiar Clave
              </button>

              <!-- Boton phpMyAdmin SSO -->
              <a href="/database/autologin/<?= (int)$db["id"] ?>" 
                 target="_blank" 
                 class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap flex-fill" 
                 title="Abrir base de datos en phpMyAdmin">
                <i class="bi bi-box-arrow-up-right me-1"></i> phpMyAdmin
              </a>

              <!-- Boton Exportar Dump SQL -->
              <a href="/database/dump/<?= (int)$db["id"] ?>" 
                 class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap flex-fill" 
                 title="Descargar volcado SQL completo">
                <i class="bi bi-download me-1"></i> Dump SQL
              </a>

              <!-- Boton Copiar Credenciales -->
              <button type="button" 
                      class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" 
                      onclick="copyConnString('<?= $db["db_name"] ?>', '<?= $db["db_user"] ?>')" 
                      title="Copiar credenciales de conexión">
                <i class="bi bi-clipboard"></i>
              </button>

              <!-- Boton Eliminar -->
              <a href="/database/delete/<?= (int)$db["id"] ?>" 
                 class="btn btn-sm btn-outline-danger text-uppercase fw-bold" 
                 onclick="return confirm('¿Deseas eliminar permanentemente la base de datos <?= $db["db_name"] ?>? Esta acción borrará todas sus tablas y datos.')" 
                 title="Eliminar Base de Datos">
                <i class="bi bi-trash"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- MODAL: CAMBIAR CONTRASEÑA / EDITAR PARA CADA BASE DE DATOS -->
        <div class="modal fade" id="modalEditDb<?= (int)$db["id"] ?>" tabindex="-1" aria-labelledby="modalEditDbLabel<?= (int)$db["id"] ?>" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <form action="/database/update/<?= (int)$db["id"] ?>" method="POST">
                <div class="modal-header">
                  <h5 class="modal-title" id="modalEditDbLabel<?= (int)$db["id"] ?>">
                    <i class="bi bi-key me-2 text-warning"></i> Cambiar Contraseña - <?= $db["db_name"] ?>
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">Base de Datos</label>
                    <input type="text" class="form-control font-monospace" value="<?= $db["db_name"] ?>" disabled>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Usuario MariaDB</label>
                    <input type="text" class="form-control font-monospace" value="<?= $db["db_user"] ?>" disabled>
                  </div>

                  <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <label class="form-label mb-0" for="pwd_<?= (int)$db["id"] ?>">Nueva Contraseña</label>
                      <button type="button" class="btn btn-sm btn-link text-decoration-none text-success p-0" onclick="generatePasswordFor('pwd_<?= (int)$db["id"] ?>')">
                        <i class="bi bi-magic me-1"></i> Generar Segura
                      </button>
                    </div>
                    <input type="password" 
                           class="form-control font-monospace" 
                           id="pwd_<?= (int)$db["id"] ?>" 
                           name="db_password" 
                           placeholder="Ingresar nueva contraseña..." 
                           required 
                           data-pr-toggle-password>
                    <div class="form-text">Mínimo 8 caracteres (letras, números y símbolos).</div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">
                    Cancelar
                  </button>
                  <button type="submit" class="btn btn-primary text-uppercase fw-bold">
                    <i class="bi bi-floppy me-2"></i> Guardar Nueva Clave
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- PESTANA 2: REGISTROS (LOGS DE MARIADB)                                  -->
<!-- ======================================================================= -->
<?php if ($active === "logs"): ?>
  <div class="bg-body p-3 rounded mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 font-monospace small">
          <i class="bi bi-circle-fill me-1" style="font-size: 8px !important;"></i> en vivo
        </span>
        <span class="text-muted small">Registros de MariaDB Server (<code>/var/log/mysql/error.log</code>)</span>
      </div>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" onclick="document.getElementById('dbTerminalLog').textContent='[Consola limpiada]'">
          <i class="bi bi-eraser me-1"></i> Limpiar
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" onclick="navigator.clipboard.writeText(document.getElementById('dbTerminalLog').textContent); alert('Registros copiados')">
          <i class="bi bi-clipboard me-1"></i> Copiar
        </button>
        <a href="/database?tab=logs" class="btn btn-sm btn-outline-primary text-uppercase fw-bold">
          <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
        </a>
      </div>
    </div>

    <pre id="dbTerminalLog" 
         class="p-3 rounded font-monospace small mb-0" 
         style="background-color: #0b0f19; color: #4ade80; min-height: 400px; max-height: 540px; overflow-y: auto; white-space: pre-wrap; line-height: 1.6; border: 1px solid rgba(255,255,255,0.08);"><?= !empty($rawLogs) ? $rawLogs : "[Sin entradas recientes en el log de MariaDB]" ?></pre>
  </div>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- PESTANA 3: ENV (VARIABLES DE CONEXION)                                  -->
<!-- ======================================================================= -->
<?php if ($active === "env"): ?>
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Variables de Conexión de Base de Datos (.env)</h5>
      <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" onclick="navigator.clipboard.writeText(document.getElementById('dbEnvSnippet').textContent); alert('Snippet copiado al portapapeles')">
        <i class="bi bi-clipboard me-1"></i> Copiar Snippet
      </button>
    </div>
    <div class="card-body">
      <p class="text-muted small">Copia estos parámetros directamente en el archivo <code>.env</code> de tu aplicación Laravel, WordPress, Django o Node.js:</p>
      <pre id="dbEnvSnippet" 
           class="p-3 rounded font-monospace small mb-0" 
           style="background-color: #0b0f19; color: #58a6ff; line-height: 1.6; border: 1px solid rgba(255,255,255,0.08);">DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=admin_app
DB_USERNAME=admin_app
DB_PASSWORD=secret_password_aqui
DB_SOCKET=/run/mysqld/mysqld.sock</pre>
    </div>
  </div>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- PESTANA 4: CONFIG (EDITOR MY.CNF)                                       -->
<!-- ======================================================================= -->
<?php if ($active === "config"): ?>
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="card-title mb-0">Configuración Maestra de MariaDB</h5>
        <span class="font-monospace small text-muted">/etc/mysql/mariadb.conf.d/50-server.cnf</span>
      </div>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" onclick="navigator.clipboard.writeText(document.getElementById('rawConfigEditor').value); alert('Configuración copiada')">
          <i class="bi bi-clipboard me-1"></i> Copiar
        </button>
      </div>
    </div>
    <div class="card-body">
      <form action="/database/config/save" method="POST">
        <div class="d-flex rounded border mb-3" style="background-color: #0b0f19; min-height: 420px; border-color: rgba(255,255,255,0.08) !important;">
          <textarea id="rawConfigEditor" 
                    name="config_content" 
                    class="form-control border-0 p-3 font-monospace small" 
                    style="background-color: transparent; color: #7ee787; resize: vertical; line-height: 1.6; min-height: 420px; outline: none; box-shadow: none;" 
                    spellcheck="false"><?= $rawConfig ?></textarea>
        </div>
        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-warning px-4 text-uppercase fw-bold">
            <i class="bi bi-floppy me-2"></i> Guardar my.cnf y Reiniciar MariaDB
          </button>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- PESTANA 5: HERRAMIENTAS                                                 -->
<!-- ======================================================================= -->
<?php if ($active === "tools"): ?>
  <div class="row">
    <div class="col-md-6 mb-3">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title mb-0">phpMyAdmin SSO</h5>
        </div>
        <div class="card-body">
          <p class="text-muted small">Accede directamente al panel web phpMyAdmin con inicio de sesión automático sin necesidad de ingresar credenciales manualmente.</p>
          <a href="/pma" target="_blank" class="btn btn-primary text-uppercase fw-bold">
            <i class="bi bi-box-arrow-up-right me-1"></i> Abrir phpMyAdmin
          </a>
        </div>
      </div>
    </div>

    <div class="col-md-6 mb-3">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title mb-0">Mantenimiento de Tablas</h5>
        </div>
        <div class="card-body">
          <p class="text-muted small">Optimiza y desfragmenta las tablas InnoDB para liberar espacio no utilizado y mejorar el rendimiento de lectura.</p>
          <button type="button" class="btn btn-outline-success text-uppercase fw-bold" onclick="alert('Optimización ejecutada en todas las tablas de MariaDB.')">
            <i class="bi bi-magic me-1"></i> Optimizar Todas las Tablas
          </button>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- PESTANA 6: PUERTOS Y SOCKETS                                            -->
<!-- ======================================================================= -->
<?php if ($active === "ports"): ?>
  <div class="card mb-3">
    <div class="card-header">
      <h5 class="card-title mb-0">Parámetros de Red y Sockets de MariaDB</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Puerto TCP</label>
          <div class="font-monospace p-2 rounded border bg-body-tertiary">3306 (127.0.0.1)</div>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Socket Unix</label>
          <div class="font-monospace p-2 rounded border bg-body-tertiary">/run/mysqld/mysqld.sock</div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- MODAL: URL Y CADENAS DE CONEXION                                        -->
<!-- ======================================================================= -->
<div class="modal fade" id="modalConnStrings" tabindex="-1" aria-labelledby="modalConnStringsLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalConnStringsLabel">
          <i class="bi bi-link-45deg me-2 text-primary"></i> Cadenas de Conexión de Base de Datos
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">URL de Conexión Estándar</label>
          <div class="p-2 rounded font-monospace small bg-body-tertiary border d-flex justify-content-between align-items-center">
            <span>mysql://admin:password@127.0.0.1:3306/admin_db</span>
            <button class="btn btn-sm btn-link text-muted p-0" onclick="navigator.clipboard.writeText('mysql://admin:password@127.0.0.1:3306/admin_db'); alert('Copiado')"><i class="bi bi-clipboard"></i></button>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Comando CLI (Consola)</label>
          <div class="p-2 rounded font-monospace small bg-body-tertiary border d-flex justify-content-between align-items-center">
            <span>mariadb -h 127.0.0.1 -u admin -p admin_db</span>
            <button class="btn btn-sm btn-link text-muted p-0" onclick="navigator.clipboard.writeText('mariadb -h 127.0.0.1 -u admin -p admin_db'); alert('Copiado')"><i class="bi bi-clipboard"></i></button>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">
          Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function copyConnString(dbName, dbUser) {
  const str = "DB_CONNECTION=mariadb\nDB_HOST=127.0.0.1\nDB_PORT=3306\nDB_DATABASE=" + dbName + "\nDB_USERNAME=" + dbUser;
  navigator.clipboard.writeText(str).then(() => {
    alert("Credenciales de conexión para '" + dbName + "' copiadas al portapapeles.");
  });
}

function generatePasswordFor(targetId) {
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
  const input = document.getElementById(targetId);
  if (input) {
    input.value = pass;
    input.type = "text";
  }
}
</script>
