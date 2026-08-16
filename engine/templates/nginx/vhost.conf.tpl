server {
    listen 80;
    listen [::]:80;
    server_name %DOMAIN% www.%DOMAIN% %ALIASES%;

    root %DOC_ROOT%;
    index index.php index.html index.htm;

    # Soporte para validacion de certificados Let's Encrypt (Certbot)
    location /.well-known/acme-challenge/ {
        allow all;
    }

    # Endpoint publico para Webhooks de GitHub (Auto-Deploy)
    location ^~ /api/git/webhook/ {
        proxy_pass http://127.0.0.1:8083;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header CF-Connecting-IP $http_cf_connecting_ip;
        proxy_set_header CF-Ray $http_cf_ray;
        proxy_set_header CF-IPCountry $http_cf_ipcountry;
    }

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
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header CF-Connecting-IP $http_cf_connecting_ip;
        proxy_set_header CF-Ray $http_cf_ray;
        proxy_set_header CF-IPCountry $http_cf_ipcountry;
    }

    location @fallback {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header CF-Connecting-IP $http_cf_connecting_ip;
        proxy_set_header CF-Ray $http_cf_ray;
        proxy_set_header CF-IPCountry $http_cf_ipcountry;
    }

    location ~ /\.ht {
        deny all;
    }

    access_log /var/log/nginx/%DOMAIN%_access.log;
    error_log /var/log/nginx/%DOMAIN%_error.log;
}
