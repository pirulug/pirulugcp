#!/usr/bin/env bash
# Modulo 05: Despliegue de PiruluGCP y configuracion de permisos

deploy_pirulugcp_core() {
    echo "[Paso 6/7] Desplegando archivos de PiruluGCP en ${PIRULU_INSTALL_DIR}..."

    mkdir -p "${PIRULU_INSTALL_DIR}"
    mkdir -p /var/log/pirulugcp
    mkdir -p /var/lib/pirulugcp

    rsync -av --exclude='.git' "$SOURCE_DIR/" "${PIRULU_INSTALL_DIR}/" || [ $? -eq 24 ] || true

    # Copiar configuracion de phpMyAdmin con modo Signon SSO
    cp "${PIRULU_INSTALL_DIR}/engine/templates/phpmyadmin/config.inc.php.tpl" /usr/share/phpmyadmin/config.inc.php

    # Crear enlace simbolico para phpMyAdmin en el panel web
    ln -sf /usr/share/phpmyadmin "${PIRULU_INSTALL_DIR}/cp-web/public/phpmyadmin"

    # Permisos ejecutables para el Engine
    chmod +x "${PIRULU_INSTALL_DIR}/engine/bin/"*

    # Permisos sudoers para www-data
    local sudoers_file="/etc/sudoers.d/pirulugcp"
    cat <<EOF > "$sudoers_file"
www-data ALL=(ALL) NOPASSWD: ${PIRULU_INSTALL_DIR}/engine/bin/*
EOF
    chmod 0440 "$sudoers_file"

    # Permisos de almacenamiento y base de datos interna SQLite
    chown -R www-data:www-data "${PIRULU_INSTALL_DIR}/cp-web" "${PIRULU_INSTALL_DIR}/config" /var/lib/pirulugcp /var/log/pirulugcp
    chmod -R 775 "${PIRULU_INSTALL_DIR}/config" /var/lib/pirulugcp /var/log/pirulugcp
}
