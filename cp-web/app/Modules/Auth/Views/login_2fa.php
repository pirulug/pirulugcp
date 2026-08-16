<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="author" content="Pirulug">
  <title>Verificación en Dos Pasos (2FA) - PiruluGCP Control Panel</title>

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
              <div class="avatar bg-primary-subtle text-primary border border-primary-subtle d-inline-flex align-items-center justify-content-center rounded-circle mb-2" style="width: 54px; height: 54px; font-size: 1.5rem;">
                <i class="bi bi-shield-lock-fill"></i>
              </div>
              <h4 class="fw-bold mb-1">Verificación en Dos Pasos</h4>
              <p class="text-muted small mb-0">Ingresa el código de 6 dígitos de tu aplicación autenticadora (Google Authenticator, Authy, etc.).</p>
            </div>

            <?php if (!empty($error)): ?>
              <div class="alert alert-danger py-2 small" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <?= $error ?>
              </div>
            <?php endif; ?>

            <form action="/login/2fa" method="POST">
              <div class="mb-3">
                <label for="two_factor_code" class="form-label text-center d-block">Código de Seguridad <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control form-control-lg font-monospace text-center fw-bold fs-3" 
                       id="two_factor_code" 
                       name="code" 
                       placeholder="123456" 
                       maxlength="6" 
                       pattern="[0-9]{6}" 
                       inputmode="numeric" 
                       autocomplete="one-time-code" 
                       required 
                       autofocus>
              </div>

              <div class="d-grid mt-3">
                <button type="submit" class="btn btn-primary text-uppercase fw-bold">
                  <i class="bi bi-check-circle me-1"></i> Verificar e Iniciar Sesión
                </button>
              </div>
            </form>

            <div class="text-center mt-3 pt-2 border-top">
              <a href="/logout" class="text-decoration-none small text-muted">
                <i class="bi bi-arrow-left me-1"></i> Volver a inicio de sesión
              </a>
            </div>
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
