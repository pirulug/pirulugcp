<?php
/**
 * Pantalla de inicio de sesion manual para phpMyAdmin en PiruluGCP
 */
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $user = trim($_POST["pma_user"] ?? "");
  $pass = trim($_POST["pma_password"] ?? "");

  if (!empty($user)) {
    $_SESSION["PMA_single_signon_user"] = $user;
    $_SESSION["PMA_single_signon_password"] = $pass;
    $_SESSION["PMA_single_signon_host"] = "localhost";
    $_SESSION["PMA_single_signon_port"] = 3306;
    header("Location: /phpmyadmin/index.php");
    exit();
  } else {
    $error = "Por favor ingresa un usuario de MariaDB.";
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="author" content="Pirulug">
  <title>phpMyAdmin - PiruluGCP Control Panel</title>

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
          <div class="card-body p-3">
            <div class="text-center mb-3">
              <h2 class="fw-bold mb-1">
                php<span class="text-primary">MyAdmin</span>
              </h2>
              <p class="text-muted small">Gestor de MariaDB en PiruluGCP</p>
            </div>

            <?php if (!empty($error)): ?>
              <div class="alert alert-danger py-2 small" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <?= htmlspecialchars($error) ?>
              </div>
            <?php endif; ?>

            <form action="/phpmyadmin/login.php" method="POST">
              <div class="mb-3">
                <label for="pma_user" class="form-label">Usuario MariaDB <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="pma_user" name="pma_user" required autofocus placeholder="root o usuario_db">
              </div>

              <div class="mb-3">
                <label for="pma_password" class="form-label">Contrasena <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="pma_password" name="pma_password" placeholder="Contrasena MariaDB" data-pr-toggle-password>
              </div>

              <div class="d-grid mt-3">
                <button type="submit" class="btn btn-primary text-uppercase fw-bold">
                  <i class="bi bi-box-arrow-in-right me-1"></i> Conectar a phpMyAdmin
                </button>
              </div>
            </form>

            <div class="text-center mt-3">
              <a href="/database" class="text-decoration-none small text-muted">Volver al Panel</a>
            </div>
          </div>
        </div>

        <div class="text-center">
          <span class="text-muted small">PiruluGCP &copy; <?= date("Y") ?></span>
        </div>
      </div>
    </div>
  </div>

  <script src="/assets/js/piruadmin.js"></script>
</body>
</html>
