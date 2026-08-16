#!/usr/bin/env bash
# Modulo 01: Configuracion de repositorios PHP (Ondrej Sury)

setup_php_repositories() {
    echo "[Paso 2/7] Configurando repositorio oficial de PHP Multi-Version (Ondrej Sury)..."

    if [ "$OS_ID" = "ubuntu" ]; then
        apt-get install -y software-properties-common
        add-apt-repository ppa:ondrej/php -y
    else
        # Configuracion nativa para Debian (Trixie / Bookworm / Bullseye)
        curl -sSLo /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
        echo "deb https://packages.sury.org/php/ ${OS_CODENAME} main" > /etc/apt/sources.list.d/php.list
    fi

    apt-get update -y
}
