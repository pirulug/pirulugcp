<?php
$domList = $domains ?? [];
$active = $activeTab ?? "domains";
$phpList = $phpVersions ?? [];
?>

<!-- ======================================================================= -->
<!-- ENCABEZADO PRINCIPAL DEL SERVIDOR WEB                                   -->
<!-- ======================================================================= -->
<div class="bg-body p-3 rounded mb-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div class="d-flex align-items-center flex-wrap gap-2">
    <!-- Badge de Servidor Web -->
    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 py-2">
      <i class="bi bi-globe2 me-1"></i> Servidor Web
    </span>
    <span class="text-muted font-monospace small">Nginx + Apache Backend</span>

    <!-- Indicador de Puertos Activos -->
    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 font-monospace">
      <i class="bi bi-circle-fill me-1" style="font-size: 8px !important;"></i> Puertos 80 / 443
    </span>

    <!-- Contador de Dominios -->
    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 font-monospace">
      <i class="bi bi-hdd-stack me-1"></i> <?= count($domList) ?> <?= (count($domList) == 1) ? "Dominio" : "Dominios" ?>
    </span>
  </div>

  <!-- Botonera Superior Derecha -->
  <div class="d-flex align-items-center flex-wrap gap-2">
    <a href="/files" class="btn btn-outline-info text-uppercase fw-bold text-nowrap">
      <i class="bi bi-folder2-open me-1"></i> Explorar Archivos
    </a>
    <a href="/ftp" class="btn btn-outline-warning text-uppercase fw-bold text-nowrap">
      <i class="bi bi-folder-symlink me-1"></i> Cuentas FTP
    </a>
    <a href="/web/create" class="btn btn-primary text-uppercase fw-bold text-nowrap">
      <i class="bi bi-plus-lg me-1"></i> Nuevo Dominio
    </a>
  </div>
</div>

<!-- ======================================================================= -->
<!-- BARRA DE PESTANAS                                                       -->
<!-- ======================================================================= -->
<div class="bg-body p-3 rounded mb-3">
  <ul class="nav nav-pills gap-1 m-0">
    <li class="nav-item">
      <a class="nav-link <?= ($active === "domains") ? "active fw-bold" : "text-body" ?>" href="/web?tab=domains">
        <i class="bi bi-globe me-1"></i> Dominios
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($active === "logs") ? "active fw-bold" : "text-body" ?>" href="/web?tab=logs">
        <i class="bi bi-terminal me-1"></i> Registros
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($active === "nginx") ? "active fw-bold" : "text-body" ?>" href="/web?tab=nginx">
        <i class="bi bi-cpu me-1"></i> Nginx Proxy
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($active === "apache") ? "active fw-bold" : "text-body" ?>" href="/web?tab=apache">
        <i class="bi bi-server me-1"></i> Apache Backend
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($active === "ssl") ? "active fw-bold" : "text-body" ?>" href="/web?tab=ssl">
        <i class="bi bi-shield-lock me-1"></i> SSL / Let's Encrypt
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($active === "ports") ? "active fw-bold" : "text-body" ?>" href="/web?tab=ports">
        <i class="bi bi-hdd-network me-1"></i> Puertos
      </a>
    </li>
  </ul>
</div>

<!-- ======================================================================= -->
<!-- PESTANA 1: DOMINIOS (BARRA RAPIDA + TARJETAS DE 2 COLUMNAS)             -->
<!-- ======================================================================= -->
<?php if ($active === "domains"): ?>
  <!-- Barra de Creacion Rapida -->
  <form action="/web/store" method="POST" class="d-flex gap-2 mb-3">
    <input type="text" 
           name="domain" 
           id="quickDomainName" 
           class="form-control form-control-lg font-monospace" 
           placeholder="Nuevo nombre de dominio (ej. miempresa.com o app.test)..." 
           required>
    <input type="hidden" name="php_version" value="8.5">
    <input type="hidden" name="doc_root_suffix" value="public_html">
    <button type="submit" class="btn btn-primary px-4 text-uppercase fw-bold text-nowrap">
      <i class="bi bi-plus-lg me-1"></i> Crear Dominio
    </button>
  </form>

  <!-- Rejilla de Tarjetas de Dominios (2 Columnas) -->
  <div class="row g-3">
    <?php if (empty($domList)): ?>
      <div class="col-12">
        <div class="bg-body p-4 rounded text-center text-muted">
          <i class="bi bi-globe fs-1 mb-2 d-block opacity-50"></i>
          No hay dominios creados aún. Ingresa un nombre arriba y pulsa <strong>Crear Dominio</strong>.
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($domList as $d): ?>
        <div class="col-md-6 domain-card-item">
          <div class="bg-body p-3 rounded border h-100 d-flex flex-column justify-content-between">
            <!-- Encabezado de la Tarjeta -->
            <div>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center gap-2">
                  <?php if (!empty($d["ssl_enabled"])): ?>
                    <i class="bi bi-shield-lock-fill text-success fs-4" title="SSL HTTPS Activo"></i>
                  <?php else: ?>
                    <i class="bi bi-globe text-primary fs-4"></i>
                  <?php endif; ?>
                  <div>
                    <a href="/web/domain/<?= (int)$d["id"] ?>" class="fs-5 fw-bold text-body font-monospace text-decoration-none" title="Abrir panel y métricas de <?= $d["domain"] ?>">
                      <?= $d["domain"] ?>
                    </a>
                    <a href="http://<?= $d["domain"] ?>" target="_blank" class="text-muted ms-1 small" title="Visitar en navegador">
                      <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                  </div>
                </div>
                <div class="d-flex gap-1">
                  <span class="badge bg-body-tertiary text-body border font-monospace px-2 py-1 d-inline-flex align-items-center">
                    <img src="<?= $d["framework_logo"] ?? "/assets/sitios/php.svg" ?>" alt="<?= $d["framework"] ?? "PHP" ?>" style="width: 14px; height: 14px; object-fit: contain;" class="me-1">
                    <?= $d["framework"] ?? "PHP Standard" ?>
                  </span>
                  <?php if (!empty($d["ssl_enabled"])): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1">
                      <i class="bi bi-lock-fill me-1"></i> SSL
                    </span>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Informacion Tecnica: Usuario, PHP-FPM, Carpeta Raiz -->
              <div class="p-2 rounded border bg-body-tertiary mb-3 font-monospace small">
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">Propietario:</span>
                  <strong><?= $d["username"] ?? "admin" ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">PHP-FPM:</span>
                  <span class="badge bg-secondary-subtle text-secondary">PHP <?= $d["php_version"] ?></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">Carpeta Raíz:</span>
                  <span>/<?= $d["doc_root_suffix"] ?? "public_html" ?></span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Ruta:</span>
                  <span class="text-truncate text-muted ms-2" style="max-width: 250px;">/home/<?= $d["username"] ?? "admin" ?>/web/<?= $d["domain"] ?>/<?= $d["doc_root_suffix"] ?? "public_html" ?></span>
                </div>
              </div>
            </div>

            <!-- Botonera de Acciones (Botones Grandes y Visibles) -->
            <div class="d-flex flex-wrap align-items-center gap-2 pt-2 border-top">
              <!-- Boton Metricas y APM -->
              <a href="/web/domain/<?= (int)$d["id"] ?>" 
                 class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap flex-fill" 
                 title="Panel de Métricas APM de la Aplicación">
                <i class="bi bi-speedometer2 me-1"></i> Métricas
              </a>

              <!-- Boton Explorar Archivos -->
              <a href="/files?domain=<?= urlencode($d["domain"]) ?>" 
                 class="btn btn-sm btn-outline-info text-uppercase fw-bold text-nowrap flex-fill" 
                 title="Gestor de Archivos del Dominio">
                <i class="bi bi-folder2-open me-1"></i> Archivos
              </a>

              <!-- Boton Editar Dominio, Alias y Redireccionamiento -->
              <a href="/web/domain/<?= (int)$d["id"] ?>/edit" 
                 class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap flex-fill" 
                 title="Editar Alias y Redirección del Dominio">
                <i class="bi bi-pencil me-1"></i> Editar
              </a>

              <!-- Boton Git -->
              <a href="/web/git/<?= (int)$d["id"] ?>" 
                 class="btn btn-sm <?= !empty($d["git_id"]) ? "btn-outline-success" : "btn-outline-secondary" ?> text-uppercase fw-bold text-nowrap" 
                 title="Despliegue Git">
                <i class="bi bi-github me-1"></i> Git
              </a>

              <!-- Boton SSL Let's Encrypt -->
              <?php if (!empty($d["ssl_enabled"])): ?>
                <a href="/web/disable-ssl/<?= (int)$d["id"] ?>" 
                   class="btn btn-sm btn-outline-success text-uppercase fw-bold text-nowrap" 
                   onclick="return confirm('¿Desactivar certificado SSL para <?= $d["domain"] ?>?')" 
                   title="Desactivar SSL">
                  <i class="bi bi-shield-check"></i>
                </a>
              <?php else: ?>
                <a href="/web/enable-ssl/<?= (int)$d["id"] ?>" 
                   class="btn btn-sm btn-outline-warning text-uppercase fw-bold text-nowrap" 
                   onclick="return confirm('¿Generar e instalar certificado Let\'s Encrypt para <?= $d["domain"] ?>?')" 
                   title="Activar Certificado SSL">
                  <i class="bi bi-shield-plus"></i>
                </a>
              <?php endif; ?>

              <!-- Boton Eliminar -->
              <a href="/web/delete/<?= (int)$d["id"] ?>" 
                 class="btn btn-sm btn-outline-danger text-uppercase fw-bold" 
                 onclick="return confirm('¿Deseas eliminar permanentemente el dominio <?= $d["domain"] ?> y sus configuraciones?')" 
                 title="Eliminar Dominio">
                <i class="bi bi-trash"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- MODAL DE AJUSTES RAPIDOS DEL DOMINIO -->
        <div class="modal fade" id="modalConfigDomain<?= (int)$d["id"] ?>" tabindex="-1" aria-labelledby="modalConfigDomainLabel<?= (int)$d["id"] ?>" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="modalConfigDomainLabel<?= (int)$d["id"] ?>">
                  <i class="bi bi-gear me-2 text-primary"></i> Ajustes de <?= $d["domain"] ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <!-- Formulario Cambio PHP -->
                <form action="/web/update-php" method="POST" class="mb-4">
                  <input type="hidden" name="domain_id" value="<?= (int)$d["id"] ?>">
                  <label class="form-label">Versión de PHP-FPM</label>
                  <div class="input-group">
                    <select name="php_version" class="form-select font-monospace">
                      <?php foreach ($phpList as $php): ?>
                        <option value="<?= $php["version"] ?>" <?= ($d["php_version"] === $php["version"]) ? "selected" : "" ?>>
                          PHP <?= $php["version"] ?> <?= (($php["status"] ?? "") === "active") ? "(Activo)" : "" ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-outline-primary text-uppercase fw-bold">
                      Cambiar PHP
                    </button>
                  </div>
                </form>

                <!-- Formulario Cambio Carpeta Raiz -->
                <form action="/web/update-docroot" method="POST">
                  <input type="hidden" name="domain_id" value="<?= (int)$d["id"] ?>">
                  <label class="form-label">Carpeta Raíz del Dominio (DocumentRoot)</label>
                  <div class="input-group">
                    <span class="input-group-text font-monospace bg-body-tertiary">/</span>
                    <input type="text" name="doc_root_suffix" class="form-control font-monospace" value="<?= $d["doc_root_suffix"] ?? "public_html" ?>" required>
                    <button type="submit" class="btn btn-outline-primary text-uppercase fw-bold">
                      Guardar Raíz
                    </button>
                  </div>
                  <div class="form-text">Ejemplo: <code>public_html</code> (estándar) o <code>public</code> (Laravel/Symfony).</div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold" data-bs-dismiss="modal">
                  Cerrar
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- PESTANA 2: REGISTROS (LOGS WEB GENERALES)                               -->
<!-- ======================================================================= -->
<?php if ($active === "logs"): ?>
  <div class="bg-body p-3 rounded mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 font-monospace small">
          <i class="bi bi-circle-fill me-1" style="font-size: 8px !important;"></i> en vivo
        </span>
        <span class="text-muted small">Registros de Acceso Global Nginx (<code>/var/log/nginx/access.log</code>)</span>
      </div>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" onclick="document.getElementById('webTerminalLog').textContent='[Consola limpiada]'">
          <i class="bi bi-eraser me-1"></i> Limpiar
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold" onclick="navigator.clipboard.writeText(document.getElementById('webTerminalLog').textContent); alert('Registros copiados')">
          <i class="bi bi-clipboard me-1"></i> Copiar
        </button>
        <a href="/web?tab=logs" class="btn btn-sm btn-outline-primary text-uppercase fw-bold">
          <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
        </a>
      </div>
    </div>

    <pre id="webTerminalLog" 
         class="p-3 rounded font-monospace small mb-0" 
         style="background-color: #0b0f19; color: #4ade80; min-height: 400px; max-height: 540px; overflow-y: auto; white-space: pre-wrap; line-height: 1.6; border: 1px solid rgba(255,255,255,0.08);"><?= !empty($rawLogs) ? $rawLogs : "[Sin entradas recientes en el log de Nginx]" ?></pre>
  </div>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- PESTANA 3: CONFIGURACION NGINX                                          -->
<!-- ======================================================================= -->
<?php if ($active === "nginx"): ?>
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="card-title mb-0">Arquitectura Nginx Reverse Proxy</h5>
        <span class="font-monospace small text-muted">/etc/nginx/conf.d/</span>
      </div>
    </div>
    <div class="card-body">
      <p class="text-muted small">Nginx actúa como proxy inverso de alto rendimiento en el puerto 80/443 gestionando la terminación SSL y sirviendo contenido estático, delegando la ejecución PHP al backend Apache / PHP-FPM.</p>
      <div class="p-3 rounded font-monospace small" style="background-color: #0b0f19; color: #38bdf8; border: 1px solid rgba(255,255,255,0.08);">
        proxy_pass http://127.0.0.1:8080;<br>
        proxy_set_header Host $host;<br>
        proxy_set_header X-Real-IP $remote_addr;<br>
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;<br>
        proxy_set_header X-Forwarded-Proto $scheme;
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- PESTANA 4: CONFIGURACION APACHE                                         -->
<!-- ======================================================================= -->
<?php if ($active === "apache"): ?>
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="card-title mb-0">Backend Apache Web Server</h5>
        <span class="font-monospace small text-muted">/etc/apache2/sites-enabled/</span>
      </div>
    </div>
    <div class="card-body">
      <p class="text-muted small">Apache ejecuta el backend en el puerto 8080 procesando reglas <code>.htaccess</code> completas y comunicándose con los sockets de PHP-FPM.</p>
      <div class="p-3 rounded font-monospace small" style="background-color: #0b0f19; color: #a78bfa; border: 1px solid rgba(255,255,255,0.08);">
        Listen 127.0.0.1:8080<br>
        SetHandler "proxy:unix:/run/php/php8.5-fpm.sock|fcgi://localhost"<br>
        AllowOverride All
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- ======================================================================= -->
<!-- PESTANA 5: SSL / CERTIFICADOS                                           -->
<!-- ======================================================================= -->
<?php if ($active === "ssl"): ?>
  <div class="card mb-3">
    <div class="card-header">
      <h5 class="card-title mb-0">Gestión de Certificados SSL (Let's Encrypt)</h5>
    </div>
    <div class="card-body">
      <p class="text-muted small">PiruluGCP automatiza la emisión y renovación de certificados TLS gratuitos con Let's Encrypt y Certbot.</p>
      <div class="table-responsive">
        <table class="table table-hover align-middle table-sm m-0">
          <thead>
            <tr>
              <th class="ps-3">Dominio</th>
              <th>Estado SSL</th>
              <th>Renovación Automática</th>
              <th class="text-end pe-3">Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($domList as $d): ?>
              <tr>
                <td class="ps-3 fw-bold font-monospace"><?= $d["domain"] ?></td>
                <td>
                  <?php if (!empty($d["ssl_enabled"])): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace"><i class="bi bi-shield-check me-1"></i> Certificado Activo</span>
                  <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary font-monospace"><i class="bi bi-shield-slash me-1"></i> Sin Certificado</span>
                  <?php endif; ?>
                </td>
                <td><span class="badge bg-info-subtle text-info font-monospace">Cada 60 días</span></td>
                <td class="text-end pe-3">
                  <?php if (!empty($d["ssl_enabled"])): ?>
                    <a href="/web/disable-ssl/<?= (int)$d["id"] ?>" class="btn btn-sm btn-outline-danger text-uppercase fw-bold" onclick="return confirm('¿Desactivar SSL?')">Desactivar</a>
                  <?php else: ?>
                    <a href="/web/enable-ssl/<?= (int)$d["id"] ?>" class="btn btn-sm btn-outline-success text-uppercase fw-bold" onclick="return confirm('¿Instalar SSL?')">Activar SSL</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
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
      <h5 class="card-title mb-0">Parámetros de Red y Puertos del Stack Web</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Nginx HTTP</label>
          <div class="font-monospace p-2 rounded border bg-body-tertiary">0.0.0.0:80</div>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Nginx HTTPS (SSL)</label>
          <div class="font-monospace p-2 rounded border bg-body-tertiary">0.0.0.0:443</div>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Apache Backend</label>
          <div class="font-monospace p-2 rounded border bg-body-tertiary">127.0.0.1:8080</div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>
