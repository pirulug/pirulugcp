<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= $pageTitle ?? "Conectando a phpMyAdmin..." ?></title>
  <link rel="stylesheet" href="/assets/css/piruadmin.css">
  <style>
    body { display: flex; align-items: center; justify-content: center; height: 100vh; font-family: system-ui, -apple-system, sans-serif; }
    .spinner-border { width: 3rem; height: 3rem; }
  </style>
</head>
<body class="bg-body-tertiary">
  <div class="text-center">
    <div class="spinner-border text-primary mb-3" role="status"></div>
    <h4 class="fw-bold">Iniciando sesion en phpMyAdmin...</h4>
    <p class="text-secondary small">Conectando con la base de datos <strong><?= $dbName ?></strong> como <code><?= $dbUser ?></code></p>
    
    <form id="pmaForm" method="POST" action="/phpmyadmin/index.php">
      <input type="hidden" name="pma_username" value="<?= $dbUser ?>">
      <input type="hidden" name="pma_password" value="<?= $dbPass ?>">
      <input type="hidden" name="server" value="1">
      <input type="hidden" name="target" value="index.php?route=/database/structure&db=<?= urlencode($dbName) ?>">
    </form>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      document.getElementById("pmaForm").submit();
    });
  </script>
</body>
</html>
