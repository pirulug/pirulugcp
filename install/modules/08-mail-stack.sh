#!/usr/bin/env bash
# Modulo 08: Instalacion y configuracion de Exim4, Dovecot y Roundcube Webmail

install_mail_stack() {
    echo "[Paso 8/8] Instalando Servidor de Correo (Exim4 + Dovecot + Roundcube Webmail)..."

    # 1. Instalar paquetes de correo
    DEBIAN_FRONTEND=noninteractive apt-get install -y \
        exim4-daemon-heavy \
        dovecot-core dovecot-imapd dovecot-pop3d dovecot-lmtpd \
        roundcube roundcube-core roundcube-sqlite3 \
        openssl ssl-cert

    # 2. Generar certificado SSL por defecto si no existe
    if [ ! -f /etc/ssl/certs/ssl-cert-snakeoil.pem ]; then
        make-ssl-cert generate-default-snakeoil --force-overwrite 2>/dev/null || true
    fi

    # 3. Crear directorios de configuracion para Exim4 y Dovecot
    mkdir -p /etc/exim4/domains /etc/exim4/aliases /etc/exim4/dkim /etc/dovecot
    touch /etc/dovecot/users
    chmod 640 /etc/dovecot/users
    chown root:dovecot /etc/dovecot/users 2>/dev/null || chown root:root /etc/dovecot/users
    chmod 750 /etc/exim4/dkim
    chown -R root:Debian-exim /etc/exim4/dkim 2>/dev/null || true

    # 4. Desplegar configuracion de Exim4
    if [ -f "${PIRULU_INSTALL_DIR}/engine/templates/exim4/exim4.conf.tpl" ]; then
        cp "${PIRULU_INSTALL_DIR}/engine/templates/exim4/exim4.conf.tpl" /etc/exim4/exim4.conf
    fi

    # 5. Desplegar configuracion de Dovecot
    if [ -f "${PIRULU_INSTALL_DIR}/engine/templates/dovecot/dovecot.conf.tpl" ]; then
        cp "${PIRULU_INSTALL_DIR}/engine/templates/dovecot/dovecot.conf.tpl" /etc/dovecot/dovecot.conf
    fi

    # 6. Configurar Roundcube Webmail
    mkdir -p /var/lib/roundcube
    if [ -d /usr/share/roundcube ]; then
        ln -sfn /usr/share/roundcube /var/lib/roundcube/public_html
    fi

    if [ -f "${PIRULU_INSTALL_DIR}/engine/templates/roundcube/config.inc.php.tpl" ]; then
        cp "${PIRULU_INSTALL_DIR}/engine/templates/roundcube/config.inc.php.tpl" /etc/roundcube/config.inc.php
        mkdir -p /var/lib/roundcube/config
        cp "${PIRULU_INSTALL_DIR}/engine/templates/roundcube/config.inc.php.tpl" /var/lib/roundcube/config/config.inc.php
    fi

    chown -R www-data:www-data /var/lib/roundcube /var/log/roundcube /etc/roundcube 2>/dev/null || true

    # 7. Reglas de Firewall para puertos de Correo
    if command -v ufw >/dev/null 2>&1; then
        ufw allow 25/tcp comment "SMTP" >/dev/null 2>&1 || true
        ufw allow 465/tcp comment "SMTPS" >/dev/null 2>&1 || true
        ufw allow 587/tcp comment "Submission" >/dev/null 2>&1 || true
        ufw allow 143/tcp comment "IMAP" >/dev/null 2>&1 || true
        ufw allow 993/tcp comment "IMAPS" >/dev/null 2>&1 || true
        ufw allow 110/tcp comment "POP3" >/dev/null 2>&1 || true
        ufw allow 995/tcp comment "POP3S" >/dev/null 2>&1 || true
    fi

    if command -v iptables >/dev/null 2>&1; then
        iptables -A INPUT -p tcp --dport 25 -j ACCEPT 2>/dev/null || true
        iptables -A INPUT -p tcp --dport 465 -j ACCEPT 2>/dev/null || true
        iptables -A INPUT -p tcp --dport 587 -j ACCEPT 2>/dev/null || true
        iptables -A INPUT -p tcp --dport 143 -j ACCEPT 2>/dev/null || true
        iptables -A INPUT -p tcp --dport 993 -j ACCEPT 2>/dev/null || true
        iptables -A INPUT -p tcp --dport 110 -j ACCEPT 2>/dev/null || true
        iptables -A INPUT -p tcp --dport 995 -j ACCEPT 2>/dev/null || true
    fi

    # 8. Iniciar y habilitar servicios
    systemctl restart exim4 2>/dev/null || true
    systemctl restart dovecot 2>/dev/null || true
    systemctl enable exim4 2>/dev/null || true
    systemctl enable dovecot 2>/dev/null || true

    echo "Servicios Exim4, Dovecot y Webmail configurados correctamente."
}
