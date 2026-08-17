# Guia de Instalacion y Despliegue en Produccion - Debian 13 (Trixie)

Esta guia describe el procedimiento completo y detallado para instalar y poner en marcha **PiruluGCP Control Panel** en un entorno de produccion sobre **Debian 13 (Trixie)**.

---

## 1. Arquitectura del Sistema en Produccion

PiruluGCP opera bajo una arquitectura de alto rendimiento y bajo consumo de recursos:

- **Frontend / Proxy Reverso**: Nginx escuchando en los puertos `80` (HTTP) y `443` (HTTPS) para sitios web de clientes, y en el puerto `8083` para la interfaz web de administracion.
- **Backend Web**: Apache 2 escuchando internamente en `127.0.0.1:8080`, gestionando archivos `.htaccess`, mod_rewrite y compatibilidad con CMS (WordPress, Laravel, etc.).
- **Procesador PHP Multi-Version**: Sockets dedicados de PHP-FPM para cada version soportada (7.4, 8.0, 8.1, 8.2, 8.3, 8.4 y 8.5).
- **Servicio PHP del Panel**: Instancia aislada de PHP-FPM (`pirulugcp-php.service`) para garantizar que la administracion continue operativa independientemente de las versiones de PHP usadas por los sitios de clientes.
- **Base de Datos**: MariaDB Server optimizado para conexiones locales seguras y base de datos SQLite interna para la gestion del panel.
- **phpMyAdmin**: Integrado con autenticacion Signon SSO automatica (un solo clic desde el panel sin ingresar contrasena).
- **Seguridad**: Reglas de filtrado de trafico (UFW / IPTables) y proteccion contra fuerza bruta mediante Fail2Ban.
- **Servidor de Correo (Opcional)**: Exim4 (MTA), Dovecot (IMAP/POP3) y Roundcube Webmail.

---

## 2. Requisitos Previos del Servidor

### Requisitos de Hardware
- **Minimo**: 1 vCPU, 1 GB de memoria RAM, 10 GB de almacenamiento SSD/NVMe.
- **Recomendado**: 2 vCPU o superior, 2 GB a 4 GB de memoria RAM, 25 GB+ de almacenamiento SSD.

### Requisitos de Red
- IP publica estatica dedicada.
- Puertos requeridos abiertos en el firewall perimetral del proveedor (AWS, Google Cloud, DigitalOcean, Hetzner, Linode, OVH, etc.):
  - `22/tcp` (SSH)
  - `80/tcp` (HTTP)
  - `443/tcp` (HTTPS)
  - `8083/tcp` (Panel PiruluGCP)
  - Puertos de Correo (si se utiliza mail stack): `25`, `465`, `587`, `143`, `993`, `110`, `995`.

---

## 3. Preparacion Inicial de Debian 13

Conectate a tu servidor via SSH como usuario `root`:

```bash
ssh root@IP_DE_TU_SERVIDOR
```

### Paso 3.1: Actualizar el Sistema Operativo

```bash
apt-get update && apt-get upgrade -y
```

### Paso 3.2: Configurar Nombre de Servidor (Hostname y FQDN)

Establece el Fully Qualified Domain Name (FQDN) correspondiente a tu servidor (ejemplo: `panel.tudominio.com`):

```bash
hostnamectl set-hostname panel.tudominio.com
```

Edita `/etc/hosts` para vincular tu IP publica con el FQDN:

```bash
echo "127.0.0.1 localhost" > /etc/hosts
echo "IP_DE_TU_SERVIDOR panel.tudominio.com panel" >> /etc/hosts
```

### Paso 3.3: Configurar Memoria Swap (Recomendado para servidores <= 2GB RAM)

```bash
fallocate -l 2G /swapfile
chmod 600 /swapfile
mkswap /swapfile
swapon /swapfile
echo "/swapfile none swap sw 0 0" >> /etc/fstab
```

### Paso 3.4: Instalar Paquetes Base Esenciales

```bash
apt-get install -y sudo curl git rsync lsb-release ca-certificates apt-transport-https gnupg2 ufw openssl unzip zip
```

---

## 4. Metodo 1: Instalacion Automatica (Recomendada)

El instalador automatizado configura todos los repositorios, instala las pilas de servicios, despliega PiruluGCP y aplica las politicas de seguridad.

### Paso 4.1: Descargar el Codigo Fuente de PiruluGCP

Clona el repositorio en una ubicacion temporal:

```bash
cd /tmp
git clone https://github.com/tu-usuario/pirulugcp.git pirulugcp
cd /tmp/pirulugcp
```

*(Si ya tienes los archivos en el servidor o descargaste un release comprimido, accede al directorio raiz del proyecto).*

### Paso 4.2: Ejecutar el Instalador Oficial

Concede permisos de ejecucion y ejecuta como `root`:

```bash
chmod +x install.sh
./install.sh
```

El script ejecutara en secuencia:
1. Validacion de Debian 13 (Trixie) y dependencias.
2. Inyeccion del repositorio oficial de PHP Multi-Version (Ondrej Sury).
3. Instalacion y configuracion de Nginx, Apache 2 y MariaDB.
4. Instalacion de versiones PHP-FPM (7.4, 8.0, 8.1, 8.2, 8.3, 8.4, 8.5) y Composer.
5. Descarga y configuracion de phpMyAdmin con SSO.
6. Despliegue del Core en `/usr/local/pirulugcp`, creacion de sudoers y configuracion de PHP-FPM dedicado para el panel.
7. Configuracion del VirtualHost de Nginx en el puerto `8083`.
8. Configuracion de seguridad con Fail2Ban e IPTables.
9. Despliegue de la pila de correo (Exim4, Dovecot, Roundcube).

### Paso 4.3: Finalizacion y Datos de Acceso

Al terminar el proceso, se mostrara en consola:

```text
============================================================
    Instalacion de PiruluGCP completada exitosamente!
============================================================
Acceso al panel: http://IP_DE_TU_SERVIDOR:8083
Usuario por defecto: admin
Contrasena: admin123
============================================================
```

---

## 5. Metodo 2: Instalacion Manual Paso a Paso

Si requieres auditar cada componente o desplegar en infraestructuras personalizadas, sigue este procedimiento paso a paso.

### Paso 5.1: Repositorio Oficial de PHP (Ondrej Sury para Debian 13)

```bash
curl -sSLo /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
echo "deb https://packages.sury.org/php/ trixie main" > /etc/apt/sources.list.d/php.list
apt-get update -y
```

### Paso 5.2: Instalar Servidores Web y Base de Datos

```bash
mkdir -p /etc/nginx/sites-enabled /etc/nginx/sites-available
rm -f /etc/nginx/sites-enabled/default

apt-get install -y nginx apache2 libapache2-mod-fcgid certbot python3-certbot-nginx mariadb-server mariadb-client
```

Configura Apache para escuchar en `127.0.0.1:8080`:

```bash
cat <<EOF > /etc/apache2/ports.conf
Listen 127.0.0.1:8080
EOF

a2enmod proxy proxy_fcgi remoteip rewrite headers alias actions
a2dissite 000-default.conf

cat <<EOF > /etc/apache2/conf-available/remoteip.conf
RemoteIPHeader X-Forwarded-For
RemoteIPInternalProxy 127.0.0.1
EOF
a2enconf remoteip

cat <<EOF > /etc/apache2/conf-available/pirulugcp.conf
<Directory /home>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EOF
a2enconf pirulugcp

systemctl enable nginx apache2 mariadb
systemctl restart nginx apache2 mariadb
```

### Paso 5.3: Instalar PHP Multi-Version y Extensiones

```bash
apt-get install -y imagemagick unzip zip git curl

# Instalar version base PHP 8.5 (las demas versiones se instalan bajo demanda desde el panel)
ver="8.5"
apt-get install -y "php${ver}-fpm" "php${ver}-cli" "php${ver}-mysql" "php${ver}-common" \
                   "php${ver}-mbstring" "php${ver}-xml" "php${ver}-curl" "php${ver}-zip" \
                   "php${ver}-gd" "php${ver}-sqlite3" "php${ver}-imagick" "php${ver}-intl" \
                   "php${ver}-bcmath" || true
systemctl enable "php${ver}-fpm" || true
systemctl restart "php${ver}-fpm" || true

# Instalar Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
chmod +x /usr/local/bin/composer
```

### Paso 5.4: Instalar phpMyAdmin

```bash
mkdir -p /usr/share/phpmyadmin
curl -sL https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.tar.gz | tar -xz -C /usr/share/phpmyadmin --strip-components=1
mkdir -p /usr/share/phpmyadmin/tmp
chmod 777 /usr/share/phpmyadmin/tmp
```

### Paso 5.5: Desplegar PiruluGCP Core y Permisos

```bash
mkdir -p /usr/local/pirulugcp /var/log/pirulugcp /var/lib/pirulugcp

# Copiar archivos del proyecto
rsync -av --exclude='.git' /tmp/pirulugcp/ /usr/local/pirulugcp/

# Configurar phpMyAdmin Signon SSO
cp /usr/local/pirulugcp/engine/templates/phpmyadmin/config.inc.php.tpl /usr/share/phpmyadmin/config.inc.php
ln -sf /usr/share/phpmyadmin /usr/local/pirulugcp/cp-web/public/phpmyadmin

# Permisos ejecutables para el Engine
chmod +x /usr/local/pirulugcp/engine/bin/*

# Configurar permisos sudoers para www-data
cat <<EOF > /etc/sudoers.d/pirulugcp
Defaults:www-data !use_pty
Defaults:www-data !requiretty
www-data ALL=(ALL) NOPASSWD: /usr/local/pirulugcp/engine/bin/*
EOF
chmod 0440 /etc/sudoers.d/pirulugcp

# Propietario de directorios del panel
chown -R www-data:www-data /usr/local/pirulugcp/cp-web /usr/local/pirulugcp/config /var/lib/pirulugcp /var/log/pirulugcp
chmod -R 775 /usr/local/pirulugcp/config /var/lib/pirulugcp /var/log/pirulugcp
```

### Paso 5.6: Servicio PHP-FPM Dedicado para el Panel

```bash
cp /usr/local/pirulugcp/engine/templates/php-fpm/panel-fpm.conf.tpl /usr/local/pirulugcp/config/php-fpm.conf

cat <<EOF > /etc/systemd/system/pirulugcp-php.service
[Unit]
Description=PiruluGCP Control Panel PHP-FPM Service
After=network.target

[Service]
Type=notify
ExecStart=/usr/sbin/php-fpm8.2 --nodaemonize --fpm-config /usr/local/pirulugcp/config/php-fpm.conf
ExecReload=/bin/kill -USR2 \$MAINPID
PrivateTmp=true

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable pirulugcp-php
systemctl restart pirulugcp-php
```

### Paso 5.7: Configurar Nginx para el Panel (Puerto 8083)

```bash
cat <<EOF > /etc/nginx/sites-available/pirulugcp.conf
server {
    listen 8083;
    listen [::]:8083;
    server_name _;

    root /usr/local/pirulugcp/cp-web/public;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$args;
    }

    location /phpmyadmin {
        root /usr/local/pirulugcp/cp-web/public;
        index index.php;

        location ~ ^/phpmyadmin/(.+\.php)$ {
            include fastcgi_params;
            fastcgi_pass unix:/run/php/pirulugcp-panel.sock;
            fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        }

        location ~* ^/phpmyadmin/(.+\.(jpg|jpeg|gif|css|png|js|ico|html|xml|txt))$ {
            root /usr/local/pirulugcp/cp-web/public;
        }
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/pirulugcp-panel.sock;
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
nginx -t && systemctl restart nginx
```

### Paso 5.8: Configurar Seguridad (Fail2Ban y UFW)

```bash
apt-get install -y fail2ban ufw

# Habilitar reglas UFW
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp comment "SSH"
ufw allow 80/tcp comment "HTTP"
ufw allow 443/tcp comment "HTTPS"
ufw allow 8083/tcp comment "PiruluGCP Panel"
ufw --force enable

systemctl enable fail2ban
systemctl restart fail2ban
```

---

## 6. Configuracion Post-Instalacion en Produccion

### 6.1. Asegurar la Base de Datos MariaDB

Ejecuta el asistente de seguridad de MariaDB:

```bash
mariadb-secure-installation
```

- Configura una contrasena segura para el usuario `root` de MariaDB.
- Elimina usuarios anonimos (`Y`).
- Deshabilita el login remoto de root (`Y`).
- Elimina la base de datos `test` (`Y`).
- Recarga los privilegios (`Y`).

### 6.2. Cambio Inmediato de Credenciales por Defecto

1. Accede desde tu navegador a `http://IP_DE_TU_SERVIDOR:8083`.
2. Inicia sesion con el usuario `admin` y la contrasena `admin123`.
3. Dirigete inmediatamente al modulo de **Perfil / Usuarios** y actualiza el correo electronico y la contrasena del administrador.

### 6.3. Asegurar el Panel con Certificado SSL Let's Encrypt (HTTPS en Puerto 8083)

Para acceder de manera segura a traves de `https://panel.tudominio.com:8083`:

1. Asegurate de que el registro DNS tipo `A` de `panel.tudominio.com` apunte a la IP de tu servidor.
2. Solicita el certificado SSL con Certbot:
   ```bash
   certbot certonly --webroot -w /usr/local/pirulugcp/cp-web/public -d panel.tudominio.com
   ```
3. Edita `/etc/nginx/sites-available/pirulugcp.conf` para activar SSL en el puerto 8083:
   ```nginx
   server {
       listen 8083 ssl http2;
       listen [::]:8083 ssl http2;
       server_name panel.tudominio.com;

       ssl_certificate /etc/letsencrypt/live/panel.tudominio.com/fullchain.pem;
       ssl_certificate_key /etc/letsencrypt/live/panel.tudominio.com/privkey.pem;
       ssl_protocols TLSv1.2 TLSv1.3;
       ssl_ciphers HIGH:!aNULL:!MD5;

       root /usr/local/pirulugcp/cp-web/public;
       index index.php index.html;

       location / {
           try_files $uri $uri/ /index.php?$args;
       }

       location /phpmyadmin {
           root /usr/local/pirulugcp/cp-web/public;
           index index.php;

           location ~ ^/phpmyadmin/(.+\.php)$ {
               include fastcgi_params;
               fastcgi_pass unix:/run/php/pirulugcp-panel.sock;
               fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           }

           location ~* ^/phpmyadmin/(.+\.(jpg|jpeg|gif|css|png|js|ico|html|xml|txt))$ {
               root /usr/local/pirulugcp/cp-web/public;
           }
       }

       location ~ \.php$ {
           include fastcgi_params;
           fastcgi_pass unix:/run/php/pirulugcp-panel.sock;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
       }

       location ~ /\.ht {
           deny all;
       }

       access_log /var/log/nginx/pirulugcp_access.log;
       error_log /var/log/nginx/pirulugcp_error.log;
   }
   ```
4. Aplica los cambios:
   ```bash
   nginx -t && systemctl reload nginx
   ```

---

## 7. Comandos CLI y Administracion del Servidor

PiruluGCP cuenta con comandos CLI ubicados en `/usr/local/pirulugcp/engine/bin/` que permiten administrar el servidor directamente desde la terminal:

| Comando | Descripcion |
|---|---|
| `pirulu-system status` | Muestra el estado operativo en tiempo real de todos los servicios. |
| `pirulu-system restart <servicio>` | Reinicia un servicio (`nginx`, `apache2`, `mariadb`, `php8.2-fpm`, etc.). |
| `pirulu-web add-domain <dominio> <usuario> [php_version]` | Crea un nuevo VirtualHost con Nginx, Apache y PHP-FPM. |
| `pirulu-web delete-domain <dominio>` | Elimina un dominio y sus configuraciones asociadas. |
| `pirulu-ssl issue <dominio> [email]` | Emite e instala un certificado SSL Let's Encrypt para un dominio de cliente. |
| `pirulu-server set-panel-domain <subdominio> [force_https]` | Configura el subdominio de acceso directo al panel en Nginx (puertos 80 y 443). |
| `pirulu-server issue-panel-ssl <subdominio> [email] [force_https]` | Emite e instala un certificado SSL Let's Encrypt para el subdominio de acceso al panel. |
| `pirulu-server delete-panel-ssl <subdominio>` | Elimina el certificado SSL del panel y revierte el acceso a HTTP. |
| `pirulu-db create <nombre_bd> <usuario> <password>` | Crea una base de datos MariaDB con usuario y privilegios. |
| `pirulu-mail domain-add <dominio> [usuario]` | Configura el stack de correo y VirtualHost de Webmail para un dominio. |
| `pirulu-mail webmail-ssl-issue <dominio> [email] [force_https]` | Emite e instala un certificado SSL Let's Encrypt para `webmail.<dominio>`. |
| `pirulu-mail webmail-ssl-delete <dominio>` | Elimina el certificado SSL de `webmail.<dominio>` y revierte a HTTP. |
| `pirulu-ftp account-add <user> <pass> <sys_user> <domain> [path]` | Crea una cuenta de acceso FTP virtual para un dominio. |
| `pirulu-ftp status` | Verifica el estado del servicio vsftpd. |

---

## 8. Monitoreo, Logs y Rutas Criticas

### Ubicacion de Archivos y Configuraciones
- **Directorio de Instalacion**: `/usr/local/pirulugcp`
- **Configuracion General**: `/usr/local/pirulugcp/config/panel.conf`
- **VirtualHosts de Clientes (Nginx)**: `/etc/nginx/sites-available/` y `/etc/nginx/sites-enabled/`
- **VirtualHosts de Clientes (Apache)**: `/etc/apache2/sites-available/` y `/etc/apache2/sites-enabled/`
- **DocumentRoot de Sitios de Clientes**: `/home/<usuario>/web/<dominio>/public_html`
- **phpMyAdmin**: `/usr/share/phpmyadmin`

### Archivos de Registro (Logs)
- **Log del Engine de PiruluGCP**: `/var/log/pirulugcp/engine.log`
- **Log de Errores del Panel Web**: `/var/log/nginx/pirulugcp_error.log`
- **Log de Acceso del Panel Web**: `/var/log/nginx/pirulugcp_access.log`
- **Log de PHP-FPM del Panel**: `/var/log/pirulugcp/php-fpm.log`
- **Log de Apache**: `/var/log/apache2/error.log`
- **Log de MariaDB**: `/var/log/mysql/error.log`

---

## 9. Diagnostico y Solucion de Problemas (Troubleshooting)

### El Panel no responde en el puerto 8083
1. Verifica que el puerto 8083 este permitido en el firewall del proveedor y en UFW:
   ```bash
   ufw status
   ```
2. Revisa si el socket de PHP del panel existe y tiene permisos correctos:
   ```bash
   ls -la /run/php/pirulugcp-panel.sock
   systemctl status pirulugcp-php
   ```
3. Comprueba el estado de Nginx:
   ```bash
   nginx -t
   systemctl status nginx
   ```

### Error "502 Bad Gateway" en el Panel
- Ocurre si el servicio `pirulugcp-php` no esta corriendo:
  ```bash
  systemctl restart pirulugcp-php
  ```

### Error de Permisos al guardar configuraciones
- Reasigna los permisos requeridos para `www-data`:
  ```bash
  chown -R www-data:www-data /usr/local/pirulugcp/cp-web /usr/local/pirulugcp/config /var/lib/pirulugcp /var/log/pirulugcp
  chmod -R 775 /usr/local/pirulugcp/config /var/lib/pirulugcp /var/log/pirulugcp
  ```

### Error de Sudoers desde la interfaz web
- Comprueba que la sintaxis de `/etc/sudoers.d/pirulugcp` sea valida:
  ```bash
  visudo -c
  ```
