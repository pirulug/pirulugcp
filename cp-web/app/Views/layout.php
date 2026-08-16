<?php
use Pirulu\Core\View;
$flash = View::getFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="author" content="Pirulug">
  <meta name="robots" content="noindex, nofollow">
  <title><?= $pageTitle ?? "PiruluGCP - Panel de Control" ?></title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicon/favicon-32x32.png">

  <!-- Icons & Fonts -->
  <link rel="stylesheet" href="/assets/plugins/bootstrapicons.css">
  <link rel="stylesheet" href="/assets/css/piruadmin-fonts.css">
  <link rel="stylesheet" href="/assets/css/piruadmin.css">

  <!-- Theme Mode Init -->
  <script>
    (function () {
      const storedTheme = localStorage.getItem("theme");
      const prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)").matches;
      const theme = storedTheme || (prefersDarkScheme ? "dark" : "light");
      document.documentElement.setAttribute("data-bs-theme", theme);
    })();
  </script>
</head>
<body>
  <div class="wrapper">
    <!-- Sidebar Navigation -->
    <?php require __DIR__ . "/sidebar.php"; ?>

    <div class="sidebar-backdrop"></div>

    <div class="main">
      <!-- Top Navigation Bar -->
      <?php require __DIR__ . "/header.php"; ?>

      <!-- Main Content Area -->
      <main class="content">
        <div class="container-fluid p-0">
          <?php if ($flash): ?>
            <div class="alert alert-<?= $flash["type"] ?> alert-dismissible fade show mb-3" role="alert">
              <?= $flash["message"] ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
          <?php endif; ?>

          <?= $content ?? "" ?>
        </div>
      </main>

      <!-- Footer -->
      <?php require __DIR__ . "/footer.php"; ?>
    </div>

    <!-- Back to Top Button -->
    <a class="back-to-top" href="#">
      <svg class="back-to-top-progress" width="50" height="50" viewBox="0 0 50 50">
        <circle class="back-to-top-circle" cx="25" cy="25" r="22"></circle>
        <circle class="back-to-top-progress-bar" cx="25" cy="25" r="22"></circle>
      </svg>
      <i class="bi bi-arrow-up-short"></i>
    </a>
  </div>

  <!-- Scripts -->
  <script src="/assets/js/piruadmin.js"></script>

  <!-- Global Helper: Clipboard Copy con soporte para HTTP y HTTPS -->
  <script>
    function copyToClipboard(target, message) {
      let text = "";
      const el = typeof target === "string" ? document.getElementById(target) : target;
      if (el && (el.value !== undefined || el.textContent !== undefined)) {
        text = el.value !== undefined ? el.value : el.textContent;
      } else if (typeof target === "string") {
        text = target;
      }

      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function () {
          if (message) alert(message);
        }).catch(function () {
          fallbackCopy(text, el, message);
        });
      } else {
        fallbackCopy(text, el, message);
      }
    }

    function fallbackCopy(text, el, message) {
      if (el && typeof el.select === "function") {
        el.focus();
        el.select();
        try {
          document.execCommand("copy");
          if (message) alert(message);
          return;
        } catch (e) {}
      }
      const textarea = document.createElement("textarea");
      textarea.value = text;
      textarea.style.position = "fixed";
      textarea.style.left = "-9999px";
      textarea.style.top = "0";
      document.body.appendChild(textarea);
      textarea.focus();
      textarea.select();
      try {
        document.execCommand("copy");
        if (message) alert(message);
      } catch (err) {
        prompt("Copia el siguiente texto:", text);
      }
      document.body.removeChild(textarea);
    }
  </script>
</body>
</html>
