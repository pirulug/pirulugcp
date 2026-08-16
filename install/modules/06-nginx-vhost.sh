#!/usr/bin/env bash
# Modulo 06: Configuracion del VirtualHost de Nginx para PiruluGCP

setup_nginx_vhost() {
    echo "[Paso 7/7] Configurando VirtualHost de Nginx para PiruluGCP en puerto 8083..."

    local panel_php_sock="/run/php/pirulugcp-panel.sock"
    if [ ! -S "$panel_php_sock" ]; then
        panel_php_sock="/run/php/php8.2-fpm.sock"
        if [ ! -S "$panel_php_sock" ]; then
            panel_php_sock=$(find /run/php/ -name "php*-fpm.sock" 2>/dev/null | head -n 1)
        fi
    fi

    cat <<EOF > /etc/nginx/sites-available/pirulugcp.conf
server {
    listen 8083;
    listen [::]:8083;
    server_name _;

    root ${PIRULU_INSTALL_DIR}/cp-web/public;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$args;
    }

    location /phpmyadmin {
        root ${PIRULU_INSTALL_DIR}/cp-web/public;
        index index.php;

        location ~ ^/phpmyadmin/(.+\.php)$ {
            include fastcgi_params;
            fastcgi_pass unix:${panel_php_sock};
            fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        }

        location ~* ^/phpmyadmin/(.+\.(jpg|jpeg|gif|css|png|js|ico|html|xml|txt))$ {
            root ${PIRULU_INSTALL_DIR}/cp-web/public;
        }
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:${panel_php_sock};
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }

    access_log /var/log/nginx/pirulugcp_access.log;
    error_log /var/log/nginx/pirulugcp_error.log;
}
EOF

    ln -sf /etc/nginx/sites-available/pirulugcp.conf /etc/nginx/sites-enabled/pirulugcp.conf

    nginx -t && (systemctl reload nginx || systemctl restart nginx)
}
