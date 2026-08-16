#!/usr/bin/env bash
# Modulo 03: Instalacion de PHP-FPM y Extensiones (Version Base 8.5)

setup_php_multiversion() {
    echo "[Paso 4/7] Instalando version base de PHP-FPM (PHP 8.5) y extensiones..."

    apt-get install -y imagemagick unzip zip git curl

    # Instalar unicamente PHP 8.5 durante la instalacion inicial
    local ver="8.5"
    echo "  - Instalando PHP ${ver}..."
    DEBIAN_FRONTEND=noninteractive apt-get install -y \
        "php${ver}-fpm" "php${ver}-cli" "php${ver}-mysql" "php${ver}-common" \
        "php${ver}-mbstring" "php${ver}-xml" "php${ver}-curl" "php${ver}-zip" \
        "php${ver}-gd" "php${ver}-sqlite3" "php${ver}-imagick" "php${ver}-intl" \
        "php${ver}-bcmath" 2>/dev/null || true

    if systemctl list-unit-files | grep -q "php${ver}-fpm"; then
        systemctl enable "php${ver}-fpm" || true
        systemctl restart "php${ver}-fpm" || true
    fi

    # Instalar Composer globalmente si no existe
    if ! command -v composer >/dev/null 2>&1 && [ ! -x "/usr/local/bin/composer" ]; then
        echo "  - Instalando Composer globalmente..."
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer >/dev/null 2>&1 || true
        chmod +x /usr/local/bin/composer || true
    fi
}
