#!/usr/bin/env bash
# Modulo 04: Descarga e instalacion de phpMyAdmin

setup_phpmyadmin() {
    echo "[Paso 5/7] Instalando y preparando phpMyAdmin..."

    mkdir -p /usr/share/phpmyadmin
    if [ ! -f "/usr/share/phpmyadmin/index.php" ]; then
        echo "  - Descargando paquete oficial de phpMyAdmin..."
        curl -sL https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.tar.gz | tar -xz -C /usr/share/phpmyadmin --strip-components=1
    fi

    mkdir -p /usr/share/phpmyadmin/tmp
    chmod 777 /usr/share/phpmyadmin/tmp
}
