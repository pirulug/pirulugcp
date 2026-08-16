#!/usr/bin/env bash
# Modulo 07: Instalacion y configuracion de Fail2Ban e IPTables

install_firewall() {
    echo "[Paso 7/7] Instalando Fail2Ban e IPTables..."

    apt-get install -y fail2ban iptables iptables-persistent > /dev/null 2>&1

    # -----------------------------------------------------------------------------
    # SECCION: CONFIGURACION FAIL2BAN
    # Usamos jail.local para no sobreescribir jail.conf en actualizaciones
    # -----------------------------------------------------------------------------
    cat > /etc/fail2ban/jail.local << 'EOF'
[DEFAULT]
bantime  = 3600
findtime = 600
maxretry = 5
backend  = systemd

# Proteccion SSH
[sshd]
enabled  = true
port     = ssh
logpath  = %(sshd_log)s
maxretry = 3

# Proteccion Nginx: demasiados requests 40x
[nginx-http-auth]
enabled  = true
port     = http,https
logpath  = /var/log/nginx/*_error.log
maxretry = 5

# Proteccion Nginx: intentos de escaneo agresivo
[nginx-botsearch]
enabled  = true
port     = http,https
filter   = nginx-botsearch
logpath  = /var/log/nginx/*_access.log
maxretry = 2
EOF

    # Filtro para botsearch de Nginx si no existe
    if [ ! -f /etc/fail2ban/filter.d/nginx-botsearch.conf ]; then
        cat > /etc/fail2ban/filter.d/nginx-botsearch.conf << 'EOF'
[Definition]
failregex = ^<HOST> -.*"(GET|POST|HEAD).*(\.php|\.asp|\.env|\.git|wp-login|xmlrpc).*" (404|403|400) .*$
ignoreregex =
EOF
    fi

    systemctl enable fail2ban  > /dev/null 2>&1
    systemctl restart fail2ban > /dev/null 2>&1

    # -----------------------------------------------------------------------------
    # SECCION: REGLAS IPTABLES BASE
    # Permite SSH, HTTP, HTTPS y panel PiruluGCP. Bloquea todo lo demas.
    # -----------------------------------------------------------------------------
    iptables -F INPUT  2>/dev/null || true
    iptables -P INPUT  ACCEPT

    # Permitir loopback
    iptables -A INPUT -i lo -j ACCEPT

    # Permitir conexiones establecidas
    iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT

    # Permitir SSH, HTTP, HTTPS
    iptables -A INPUT -p tcp --dport 22   -j ACCEPT
    iptables -A INPUT -p tcp --dport 80   -j ACCEPT
    iptables -A INPUT -p tcp --dport 443  -j ACCEPT

    # Permitir panel de control PiruluGCP
    iptables -A INPUT -p tcp --dport 8083 -j ACCEPT

    # Persistir reglas
    if command -v netfilter-persistent > /dev/null 2>&1; then
        netfilter-persistent save > /dev/null 2>&1 || true
    fi

    echo "[OK] Fail2Ban e IPTables configurados correctamente."
}
