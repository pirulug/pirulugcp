<?php
$isConnected = !empty($git);
$domainName = $domain["domain"];
$domainId   = (int)$domain["id"];
?>

<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
  <div>
    <h1 class="h4 mb-1">
      <i class="bi bi-github me-2"></i> Integracion Git &bull; <?= $domainName ?>
    </h1>
    <p class="text-muted small mb-0">Despliegue de codigo continuo para repositorios publicos o privados de GitHub</p>
  </div>
  <div class="d-flex gap-2">
    <a href="/web" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
      <i class="bi bi-arrow-left me-1"></i> Volver a Dominios
    </a>
    <?php if ($isConnected): ?>
      <form action="/web/git/composer/<?= $domainId ?>" method="POST" class="d-inline m-0">
        <button type="submit" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap" title="Instalar dependencias de Composer">
          <i class="bi bi-box-seam me-1"></i> Composer Install
        </button>
      </form>
      <form action="/web/git/deploy/<?= $domainId ?>" method="POST" class="d-inline m-0">
        <button type="submit" class="btn btn-sm btn-primary text-uppercase fw-bold text-nowrap">
          <i class="bi bi-cloud-arrow-down me-1"></i> Desplegar Ahora
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php if ($isConnected): ?>
<!-- Estado del repositorio conectado -->
<div class="row g-3 mb-3">
  <div class="col-md-8">
    <div class="bg-body p-3 rounded h-100">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">
          <i class="bi bi-diagram-2 me-1"></i> Repositorio Conectado
        </h6>
        <span class="badge bg-success-subtle text-success border border-success-subtle">
          <i class="bi bi-check-circle me-1"></i> Conectado
        </span>
      </div>

      <table class="table table-sm table-borderless mb-0">
        <tbody>
          <tr>
            <td class="text-muted" style="width: 140px;">URL del Repo:</td>
            <td class="fw-bold">
              <code class="text-primary"><?= $git["repo_url"] ?></code>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Rama Activa:</td>
            <td>
              <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace">
                <i class="bi bi-git me-1"></i> <?= $git["branch"] ?>
              </span>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Carpeta Destino:</td>
            <td>
              <code class="text-muted">/home/<?= $domain["username"] ?>/web/<?= $domainName ?>/<?= $domain["doc_root_suffix"] ?></code>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Estado Composer:</td>
            <td>
              <?php if (!empty($composerStatus["has_composer_json"])): ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle me-1">
                  <i class="bi bi-check-circle me-1"></i> composer.json detectado
                </span>
                <?php if (!empty($composerStatus["has_autoload"])): ?>
                  <span class="badge bg-info-subtle text-info border border-info-subtle">
                    <i class="bi bi-box-seam me-1"></i> vendor instalado
                  </span>
                <?php else: ?>
                  <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                    <i class="bi bi-exclamation-circle me-1"></i> vendor pendiente
                  </span>
                <?php endif; ?>
              <?php else: ?>
                <span class="text-muted small">Sin composer.json en la raiz</span>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Ultimo Commit:</td>
            <td>
              <?php if (!empty($git["last_commit_hash"])): ?>
                <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace">
                  <?= substr($git["last_commit_hash"], 0, 7) ?>
                </span>
                <span class="ms-1 fw-bold"><?= $git["last_commit_message"] ?></span>
                <span class="text-muted small ms-1">(por <?= $git["last_commit_author"] ?>)</span>
              <?php else: ?>
                <span class="text-muted small">Sin informacion de commit</span>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Ultimo Despliegue:</td>
            <td>
              <?php if (!empty($git["last_deploy_at"])): ?>
                <span><?= $git["last_deploy_at"] ?></span>
                <span class="badge ms-2 <?= ($git["last_deploy_status"] === "success") ? "bg-success-subtle text-success border border-success-subtle" : "bg-danger-subtle text-danger border border-danger-subtle" ?>">
                  <?= strtoupper($git["last_deploy_status"]) ?>
                </span>
              <?php else: ?>
                <span class="text-muted small">Nunca</span>
              <?php endif; ?>
            </td>
          </tr>
        </tbody>
      </table>

      <hr class="my-3">

      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex gap-2">
          <form action="/web/git/deploy/<?= $domainId ?>" method="POST" class="d-inline m-0">
            <button type="submit" class="btn btn-sm btn-primary text-uppercase fw-bold text-nowrap">
              <i class="bi bi-arrow-repeat me-1"></i> Git Pull
            </button>
          </form>
          <form action="/web/git/composer/<?= $domainId ?>" method="POST" class="d-inline m-0">
            <button type="submit" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap">
              <i class="bi bi-box-seam me-1"></i> Composer Install
            </button>
          </form>
        </div>
        <a href="/web/git/unlink/<?= $domainId ?>" class="btn btn-sm btn-outline-danger text-uppercase fw-bold text-nowrap" onclick="return confirm('Desvincular repositorio de <?= $domainName ?>?')">
          <i class="bi bi-trash me-1"></i> Desvincular Git
        </a>
      </div>
    </div>
  </div>

  <!-- Webhook para Auto-Deploy -->
  <div class="col-md-4">
    <div class="bg-body p-3 rounded h-100 d-flex flex-column justify-content-between">
      <div>
        <h6 class="fw-bold mb-2">
          <i class="bi bi-lightning-charge me-1"></i> Auto-Deploy (Webhook)
        </h6>
        <p class="text-muted small mb-3">
          Configura este Webhook en GitHub para que cada <code>git push</code> despliegue automaticamente los cambios y ejecute Composer en tu servidor.
        </p>

        <label for="webhook_url_input" class="form-label">URL del Webhook de GitHub:</label>
        <div class="input-group input-group-sm mb-3">
          <input type="text" class="form-control font-monospace" id="webhook_url_input" value="<?= $webhookUrl ?>" readonly>
          <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('webhook_url_input', 'URL de Webhook copiada al portapapeles');">
            <i class="bi bi-clipboard"></i>
          </button>
        </div>

        <div class="alert alert-secondary py-2 px-3 small mb-0">
          <strong>Pasos en GitHub:</strong><br>
          1. En tu repo ve a <strong>Settings &gt; Webhooks</strong>.<br>
          2. Clic en <strong>Add webhook</strong>.<br>
          3. Pega la URL y en Content type elige <code>application/json</code>.<br>
          4. Eventos: <em>Just the push event</em>.
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Log y Consola de Resultados del Despliegue -->
<?php if (!empty($git["last_deploy_log"])): ?>
<div class="bg-body p-3 rounded mb-3">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="fw-bold mb-0">
      <i class="bi bi-terminal me-1"></i> Consola de Resultados y Log de Despliegue
    </h6>
    <?php if (!empty($git["last_deploy_status"])): ?>
      <span class="badge <?= ($git["last_deploy_status"] === "success") ? "bg-success-subtle text-success border border-success-subtle" : "bg-danger-subtle text-danger border border-danger-subtle" ?>">
        <i class="bi <?= ($git["last_deploy_status"] === "success") ? "bi-check-circle" : "bi-exclamation-triangle" ?> me-1"></i>
        <?= strtoupper($git["last_deploy_status"]) ?>
      </span>
    <?php endif; ?>
  </div>
  <pre class="bg-body-tertiary text-body p-3 rounded border small mb-0 font-monospace" style="max-height: 250px; overflow-y: auto;"><code><?= $git["last_deploy_log"] ?></code></pre>
</div>
<?php endif; ?>

<div class="row g-3">
  <!-- Formulario de conexion / configuracion -->
  <div class="col-md-6">
    <div class="bg-body p-3 rounded h-100">
      <h6 class="fw-bold mb-3">
        <i class="bi bi-gear me-1"></i> <?= $isConnected ? "Actualizar Configuracion Git" : "Conectar Repositorio Git" ?>
      </h6>

      <form action="/web/git/connect" method="POST" onsubmit="return confirm('Deseas vincular y sincronizar el repositorio Git? Los archivos locales ignorados (.gitignore como config.php o .env) se mantendran intactos.');">
        <input type="hidden" name="domain_id" value="<?= $domainId ?>">

        <div class="mb-3">
          <label for="repo_url" class="form-label">URL del Repositorio <span class="text-danger">*</span></label>
          <input type="text" class="form-control font-monospace" id="repo_url" name="repo_url" placeholder="https://github.com/usuario/repo.git o git@github.com:usuario/repo.git" value="<?= $git["repo_url"] ?? "" ?>" required>
          <div class="form-text small">
            Usa formato <code>https://...</code> para repositorios publicos o <code>git@github.com:...</code> para privados.
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="branch" class="form-label">Rama (Branch) <span class="text-danger">*</span></label>
            <input type="text" class="form-control font-monospace" id="branch" name="branch" value="<?= $git["branch"] ?? "main" ?>" required>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">Tipo de Repositorio</label>
            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" id="is_private" name="is_private" value="1" <?= (!empty($git["is_private"]) || empty($git)) ? "checked" : "" ?>>
              <label class="form-check-label" for="is_private">
                Privado (Usa Clave SSH)
              </label>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="auto_deploy" name="auto_deploy" value="1" <?= (!isset($git["auto_deploy"]) || !empty($git["auto_deploy"])) ? "checked" : "" ?>>
            <label class="form-check-label" for="auto_deploy">
              Habilitar Despliegue Automatico via Webhook
            </label>
          </div>
        </div>

        <div class="mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="composer_install" name="composer_install" value="1" <?= (!isset($git["composer_install"]) || !empty($git["composer_install"])) ? "checked" : "" ?>>
            <label class="form-check-label" for="composer_install">
              Ejecutar automaticamente <code>composer install</code> si existe <code>composer.json</code>
            </label>
          </div>
        </div>

        <div class="alert alert-info py-2 px-3 small mb-3">
          <i class="bi bi-shield-check me-1"></i>
          <strong>Preservacion y Dependencias:</strong> Los archivos ignorados por <code>.gitignore</code> (como <code>config.php</code> o <code>.env</code>) se mantendran intactos y las dependencias de <code>composer.json</code> se instalaran aisladas bajo el usuario del dominio.
        </div>

        <button type="submit" class="btn btn-primary text-uppercase fw-bold w-100">
          <i class="bi bi-cloud-arrow-up me-1"></i> <?= $isConnected ? "Guardar y Re-sincronizar" : "Clonar y Vincular Repositorio" ?>
        </button>
      </form>
    </div>
  </div>

  <!-- Clave SSH Deploy Key -->
  <div class="col-md-6">
    <div class="bg-body p-3 rounded h-100">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">
          <i class="bi bi-key me-1"></i> Clave SSH de Despliegue (Deploy Key)
        </h6>
        <a href="/web/git/generate-key/<?= $domainId ?>" class="btn btn-sm btn-outline-warning text-uppercase fw-bold text-nowrap" onclick="return confirm('Generar una nueva clave reemplazara la anterior. Continuar?')">
          <i class="bi bi-arrow-clockwise me-1"></i> Regenerar
        </a>
      </div>

      <p class="text-muted small mb-2">
        Para repositorios privados, agrega esta clave pública en GitHub con permisos de lectura:
      </p>

      <div class="mb-3">
        <textarea class="form-control font-monospace small bg-body-tertiary" id="ssh_public_key_box" rows="4" readonly><?= $publicKey ?></textarea>
      </div>

      <div class="d-grid mb-3">
        <button type="button" class="btn btn-sm btn-outline-primary text-uppercase fw-bold" onclick="copyToClipboard('ssh_public_key_box', 'Clave SSH copiada al portapapeles');">
          <i class="bi bi-clipboard me-1"></i> Copiar Clave Publica SSH
        </button>
      </div>

      <div class="alert alert-secondary py-2 px-3 small mb-0">
        <strong>Como configurar en GitHub:</strong><br>
        1. Abre tu repositorio en GitHub.<br>
        2. Ve a <strong>Settings &gt; Deploy Keys</strong>.<br>
        3. Haz clic en <strong>Add deploy key</strong>.<br>
        4. Asigna un titulo (ej: <code>PiruluGCP - <?= $domainName ?></code>) y pega la clave.<br>
        5. Guarda la clave (no es necesario marcar 'Allow write access').
      </div>
    </div>
  </div>
</div>
