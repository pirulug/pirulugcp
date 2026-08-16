<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle ?? "Conectando a phpMyAdmin...") ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #1a1d20; color: #ffffff; display: flex; align-items: center; justify-content: center; height: 100vh; font-family: system-ui, -apple-system, sans-serif; }
        .spinner-border { width: 3rem; height: 3rem; }
    </style>
</head>
<body>
    <div class="text-center">
        <div class="spinner-border text-primary mb-3" role="status"></div>
        <h4 class="fw-bold">Iniciando sesion en phpMyAdmin...</h4>
        <p class="text-secondary small">Conectando con la base de datos <strong><?= htmlspecialchars($dbName) ?></strong> como <code><?= htmlspecialchars($dbUser) ?></code></p>
        
        <form id="pmaForm" method="POST" action="/phpmyadmin/index.php">
            <input type="hidden" name="pma_username" value="<?= htmlspecialchars($dbUser) ?>">
            <input type="hidden" name="pma_password" value="<?= htmlspecialchars($dbPass) ?>">
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
