<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="author" content="Pirulug">
  <title>Iniciar Sesion - PiruluGCP Control Panel</title>

  <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicon/favicon-32x32.png">
  <link rel="stylesheet" href="/assets/plugins/bootstrapicons.css">
  <link rel="stylesheet" href="/assets/css/piruadmin-fonts.css">
  <link rel="stylesheet" href="/assets/css/piruadmin.css">

  <script>
    (function () {
      const storedTheme = localStorage.getItem("theme");
      const prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)").matches;
      const theme = storedTheme || (prefersDarkScheme ? "dark" : "light");
      document.documentElement.setAttribute("data-bs-theme", theme);
    })();
  </script>
</head>
<body class="d-flex align-items-center min-vh-100 py-5 bg-body-tertiary">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
        <div class="card mb-3">
          <div class="card-body">
            <div class="text-center mb-3">
              <h2 class="fw-bold mb-1">
                Pirulu<span class="text-primary">GCP</span>
              </h2>
              <p class="text-muted small">Panel de Control de Hosting y Servidores</p>
            </div>

            <?php if (!empty($error)): ?>
              <div class="alert alert-danger py-2 small" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <?= $error ?>
              </div>
            <?php endif; ?>

            <form action="/login" method="POST">
              <div class="mb-3">
                <label for="username" class="form-label">Usuario <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Ingresa tu usuario" required autofocus>
              </div>

              <div class="mb-3">
                <label for="user_password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="user_password" name="password" placeholder="Ingresa tu contraseña" data-pr-toggle-password required>
              </div>

              <?php if (!empty($cfTurnstileEnabled) && !empty($cfSiteKey)): ?>
                <div class="mb-3 d-flex justify-content-center">
                  <div class="cf-turnstile" data-sitekey="<?= $cfSiteKey ?>" data-theme="auto"></div>
                </div>
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
              <?php endif; ?>

              <div class="d-grid mt-3">
                <button type="submit" class="btn btn-primary text-uppercase fw-bold">
                  <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                </button>
              </div>
            </form>
          </div>
        </div>

        <div class="text-center">
          <span class="text-muted small">PiruluGCP &copy; <?= date("Y") ?>. Todos los derechos reservados.</span>
        </div>
      </div>
    </div>
  </div>

  <script src="/assets/js/piruadmin.js"></script>
</body>
</html>
