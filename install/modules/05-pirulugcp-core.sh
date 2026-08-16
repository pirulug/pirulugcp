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

    # Permisos sudoers para www-data (sin PTY ni password)
    local sudoers_file="/etc/sudoers.d/pirulugcp"
    cat <<EOF > "$sudoers_file"
Defaults:www-data !use_pty
Defaults:www-data !requiretty
www-data ALL=(ALL) NOPASSWD: ${PIRULU_INSTALL_DIR}/engine/bin/*
EOF
    chmod 0440 "$sudoers_file"

    # Permisos de almacenamiento y base de datos interna SQLite
    chown -R www-data:www-data "${PIRULU_INSTALL_DIR}/cp-web" "${PIRULU_INSTALL_DIR}/config" /var/lib/pirulugcp /var/log/pirulugcp
    chmod -R 775 "${PIRULU_INSTALL_DIR}/config" /var/lib/pirulugcp /var/log/pirulugcp

    # Configurar e iniciar servicio PHP-FPM dedicado para el panel PiruluGCP
    if [ ! -f "${PIRULU_INSTALL_DIR}/config/php-fpm.conf" ] && [ -f "${PIRULU_INSTALL_DIR}/engine/templates/php-fpm/panel-fpm.conf.tpl" ]; then
        cp "${PIRULU_INSTALL_DIR}/engine/templates/php-fpm/panel-fpm.conf.tpl" "${PIRULU_INSTALL_DIR}/config/php-fpm.conf"
    fi

    local fpm_bin="/usr/sbin/php-fpm8.2"
    if [ ! -x "$fpm_bin" ]; then
        fpm_bin=$(command -v php-fpm || which php-fpm || ls -1 /usr/sbin/php-fpm* 2>/dev/null | head -n 1)
    fi

    cat <<EOF > /etc/systemd/system/pirulugcp-php.service
[Unit]
Description=PiruluGCP Control Panel PHP-FPM Service
After=network.target

[Service]
Type=notify
ExecStart=${fpm_bin} --nodaemonize --fpm-config ${PIRULU_INSTALL_DIR}/config/php-fpm.conf
ExecReload=/bin/kill -USR2 \$MAINPID
Restart=always
RestartSec=3
PrivateTmp=true

[Install]
WantedBy=multi-user.target
EOF

    systemctl daemon-reload
    systemctl enable pirulugcp-php >/dev/null 2>&1 || true
    systemctl restart pirulugcp-php >/dev/null 2>&1 || true
}
