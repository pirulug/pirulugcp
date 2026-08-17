# VirtualHost de Nginx para Webmail de %DOMAIN% en HTTP (Puerto 80 y 8083)
server {
    listen 80;
    listen [::]:80;
    listen 8083;
    listen [::]:8083;
    server_name webmail.%DOMAIN%;

    root /usr/share/roundcube;
    index index.php index.html;

    client_max_body_size 64M;

    location /.well-known/acme-challenge/ {
        root /usr/share/roundcube;
        try_files $uri =404;
    }

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ ^/(README|INSTALL|LICENSE|CHANGELOG|UPGRADING)$ {
        deny all;
    }

    location ~ ^/(bin|SQL|config|temp|logs)/ {
        deny all;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/pirulugcp-panel.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_index index.php;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    location ~ /\.ht {
        deny all;
    }

    access_log /var/log/nginx/webmail_%DOMAIN%_access.log;
    error_log /var/log/nginx/webmail_%DOMAIN%_error.log;
}

# VirtualHost de Nginx para Webmail de %DOMAIN% en HTTPS (Puerto 443)
server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name webmail.%DOMAIN%;

    root /usr/share/roundcube;
    index index.php index.html;

    ssl_certificate /etc/ssl/certs/ssl-cert-snakeoil.pem;
    ssl_certificate_key /etc/ssl/private/ssl-cert-snakeoil.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    client_max_body_size 64M;

    location /.well-known/acme-challenge/ {
        root /usr/share/roundcube;
        try_files $uri =404;
    }

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ ^/(README|INSTALL|LICENSE|CHANGELOG|UPGRADING)$ {
        deny all;
    }

    location ~ ^/(bin|SQL|config|temp|logs)/ {
        deny all;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/pirulugcp-panel.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_index index.php;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    location ~ /\.ht {
        deny all;
    }

    access_log /var/log/nginx/webmail_%DOMAIN%_access.log;
    error_log /var/log/nginx/webmail_%DOMAIN%_error.log;
}
