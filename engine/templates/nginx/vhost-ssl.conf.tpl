server {
    listen 80;
    listen [::]:80;
    server_name %DOMAIN% www.%DOMAIN% %ALIASES%;

    # Soporte para renovacion ACME de Certbot
    location /.well-known/acme-challenge/ {
        root %DOC_ROOT%;
        allow all;
    }

    location / {
        return 301 https://$host$request_uri;
    }
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    http2 on;
    server_name %DOMAIN% www.%DOMAIN% %ALIASES%;

    root %DOC_ROOT%;
    index index.php index.html index.htm;

    # Certificados SSL de Let's Encrypt
    ssl_certificate %SSL_CERT%;
    ssl_certificate_key %SSL_KEY%;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Entrega directa de archivos estaticos por Nginx
    location ~* \.(jpg|jpeg|gif|png|ico|svg|css|zip|tgz|gz|rar|bz2|doc|xls|exe|pdf|ppt|txt|tar|mid|midi|wav|bmp|rtf|js|woff|woff2|ttf|otf|eot|webp|avif)$ {
        access_log off;
        expires 30d;
        try_files $uri @fallback;
    }

    # Proxy inverso hacia el backend Apache (puerto 8080)
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
    }

    location @fallback {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
    }

    location ~ /\.ht {
        deny all;
    }

    access_log /var/log/nginx/%DOMAIN%_ssl_access.log;
    error_log /var/log/nginx/%DOMAIN%_ssl_error.log;
}
