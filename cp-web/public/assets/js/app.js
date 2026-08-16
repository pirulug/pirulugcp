// Scripts de interfaz para PiruluGCP

document.addEventListener("DOMContentLoaded", function () {
    // Auto-ocultar alertas flash despues de 5 segundos
    const alerts = document.querySelectorAll(".alert-dismissible");
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });
});
