#!/usr/bin/env bash
# ==============================================================================
# Modulo 09: Instalacion y configuracion del Servidor FTP (vsftpd + PAM)
# ==============================================================================

install_ftp_server() {
    echo "[Paso 9/9] Instalando y configurando Servidor FTP (vsftpd + libpam-pwdfile)..."

    # 1. Instalar paquetes de vsftpd y libreria de autenticacion PAM por archivo
    DEBIAN_FRONTEND=noninteractive apt-get install -y \
        vsftpd \
        libpam-pwdfile \
        openssl ssl-cert

    # 2. Crear directorios de configuracion para usuarios virtuales
    mkdir -p /etc/vsftpd/users
    touch /etc/vsftpd/ftpd.passwd
    chmod 600 /etc/vsftpd/ftpd.passwd
    chmod 755 /etc/vsftpd/users

    # 3. Desplegar configuracion de vsftpd
    if [ -f "${PIRULU_INSTALL_DIR}/engine/templates/vsftpd/vsftpd.conf.tpl" ]; then
        cp "${PIRULU_INSTALL_DIR}/engine/templates/vsftpd/vsftpd.conf.tpl" /etc/vsftpd.conf
        if [ -d /etc/vsftpd ]; then
            cp "${PIRULU_INSTALL_DIR}/engine/templates/vsftpd/vsftpd.conf.tpl" /etc/vsftpd/vsftpd.conf
        fi
    fi

    # 4. Desplegar configuracion PAM para vsftpd
    if [ -f "${PIRULU_INSTALL_DIR}/engine/templates/vsftpd/pam_vsftpd.tpl" ]; then
        cp "${PIRULU_INSTALL_DIR}/engine/templates/vsftpd/pam_vsftpd.tpl" /etc/pam.d/vsftpd
    fi

    # 5. Reglas de Firewall para puertos FTP (Puerto 21 y Rango Pasivo 40000-50000)
    if command -v ufw >/dev/null 2>&1; then
        ufw allow 21/tcp comment "FTP Control" >/dev/null 2>&1 || true
        ufw allow 40000:50000/tcp comment "FTP Passive Ports" >/dev/null 2>&1 || true
    fi

    if command -v iptables >/dev/null 2>&1; then
        iptables -A INPUT -p tcp --dport 21 -j ACCEPT 2>/dev/null || true
        iptables -A INPUT -p tcp --dport 40000:50000 -j ACCEPT 2>/dev/null || true
    fi

    # 6. Iniciar y habilitar servicio vsftpd
    systemctl restart vsftpd 2>/dev/null || true
    systemctl enable vsftpd 2>/dev/null || true

    echo "Servidor FTP vsftpd configurado correctamente con soporte de usuarios virtuales."
}
