#!/usr/bin/env bash
# Modulo 02: Instalacion y configuracion de Nginx (Proxy Reverso) y Apache 2 (Backend)

setup_webserver() {
    echo "[Paso 3/7] Instalando y configurando Nginx (Proxy Reverso), Apache 2 y MariaDB..."

    mkdir -p /etc/nginx/sites-enabled /etc/nginx/sites-available
    rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true

    apt-get install -y git nginx apache2 libapache2-mod-fcgid certbot python3-certbot-nginx mariadb-server mariadb-client || (rm -f /etc/nginx/sites-enabled/default && dpkg --configure -a)
    rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true

    # 1. Configurar Apache para escuchar en el puerto interno 8080 (backend)
    cat <<EOF > /etc/apache2/ports.conf
# Configurado por PiruluGCP: Apache escucha en puerto interno backend
Listen 127.0.0.1:8080
EOF

    # 2. Habilitar modulos necesarios en Apache
    a2enmod proxy proxy_fcgi remoteip rewrite headers alias actions 2>/dev/null || true

    # 3. Configurar RemoteIP para que Apache registre la IP real del cliente enviada por Nginx
    cat <<EOF > /etc/apache2/conf-available/remoteip.conf
RemoteIPHeader X-Forwarded-For
RemoteIPInternalProxy 127.0.0.1
EOF
    a2enconf remoteip 2>/dev/null || true

    # 4. Configurar permisos de Apache para servir sitios en /home
    cat <<EOF > /etc/apache2/conf-available/pirulugcp.conf
<Directory /home>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EOF
    a2enconf pirulugcp 2>/dev/null || true

    # 5. Deshabilitar sitio por defecto de Apache en puerto 80
    a2dissite 000-default.conf 2>/dev/null || true

    # 6. Iniciar y habilitar servicios
    systemctl enable nginx || true
    systemctl restart nginx || true
    systemctl enable apache2 || true
    systemctl restart apache2 || true
    systemctl enable mariadb || true
    systemctl restart mariadb || true
}
