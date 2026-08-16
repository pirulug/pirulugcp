# Guia de Instalacion de PiruluGCP en Debian 13 (Trixie)

Esta guia detalla los pasos oficiales para instalar y poner en marcha el panel de control **PiruluGCP** en un servidor limpio con **Debian 13 (Trixie)** o Debian 12.

---

## 1. Requisitos Previos del Servidor

- **Sistema Operativo**: Debian 13 (Trixie) o Debian 12 (Bookworm) instalacion limpia (Minimal / Cloud Base).
- **Acceso**: Privilegios de superusuario `root` por SSH.
- **Hardware Recomendado**:
  - CPU: 2 nucleos o superior.
  - RAM: 2 GB como minimo (4 GB recomendado para MariaDB, Multi-PHP y Apache/Nginx simultaneos).
  - Disco: 20 GB SSD/NVMe.
- **Puertos de Red Requeridos en el Firewall / Proveedor de Nube**:
  - `22/TCP`: SSH
  - `80/TCP`: HTTP (Nginx Proxy)
  - `443/TCP`: HTTPS / SSL (Nginx Proxy)
  - `8083/TCP`: Panel de Control PiruluGCP
  - `21/TCP`: Servidor FTP (vsftpd)
  - `40000:50000/TCP`: Rango de Puertos Pasivos FTP
  - `25/TCP`, `465/TCP`, `587/TCP`: Servidor de Correo SMTP (Exim4)
  - `143/TCP`, `993/TCP`, `110/TCP`, `995/TCP`: Buzones IMAP / POP3 (Dovecot)

---

## 2. Metodo 1: Instalacion Rapida en un Solo Comando (Recomendado)

Conectate a tu servidor por SSH como usuario `root` y ejecuta:

```bash
apt-get update && apt-get install -y git curl sudo ca-certificates && \
rm -rf /tmp/pirulugcp && \
git clone https://github.com/pirulug/pirulugcp.git /tmp/pirulugcp && \
cd /tmp/pirulugcp && \
chmod +x install.sh install/modules/*.sh && \
./install.sh
```

---

## 3. Metodo 2: Instalacion Paso a Paso

Si prefieres realizar el procedimiento paso a paso:

### Paso 1: Actualizar el Sistema Base
```bash
apt-get update && apt-get upgrade -y
```

### Paso 2: Instalar Paquetes de Soporte Inicial
```bash
apt-get install -y git curl sudo ca-certificates lsb-release gnupg2 software-properties-common rsync
```

### Paso 3: Clonar el Repositorio de PiruluGCP
```bash
git clone https://github.com/pirulug/pirulugcp.git /tmp/pirulugcp
```

### Paso 4: Asignar Permisos y Ejecutar el Instalador
```bash
cd /tmp/pirulugcp
chmod +x install.sh install/modules/*.sh
./install.sh
```

El instalador ejecutara automaticamente los siguientes modulos en orden:
1. Comprobacion de arquitectura y compatibilidad Debian/Ubuntu.
2. Configuracion de repositorios APT para PHP multi-version (Ondrej Sury / Debian).
3. Instalacion y configuracion del stack web hibrido (Nginx Proxy en puertos 80/443 y Apache Backend en puerto 8080).
4. Instalacion de la version base PHP-FPM 8.5 y Composer (las demas versiones 7.4 a 8.4 se pueden instalar bajo demanda desde el panel de control).
5. Descarga y despliegue de phpMyAdmin con modo Signon SSO.
6. Despliegue del nucleo de PiruluGCP en `/usr/local/pirulugcp`, base de datos interna SQLite y permisos sudoers.
7. Configuracion de VirtualHost Nginx en puerto 8083 para el acceso al panel.
8. Configuracion de seguridad con Fail2Ban e IPTables/UFW.
9. Despliegue del stack de correo (Exim4, Dovecot y Roundcube Webmail).
10. Despliegue y configuracion del servidor FTP (vsftpd) con usuarios virtuales.

---

## 4. Acceso Inicial al Panel de Control

Al finalizar la instalacion, el panel web quedara disponible de forma inmediata:

- **URL de Acceso**: `http://IP_DE_TU_SERVIDOR:8083`
- **Usuario por Defecto**: `admin`
- **Contrasena Inicial**: `admin123`

---

## 5. Pasos Obligatorios Post-Instalacion en Produccion

### 5.1. Asegurar el Servidor MariaDB
Ejecuta el asistente de endurecimiento de base de datos en la terminal:

```bash
mariadb-secure-installation
```
- Asigna una contrasena segura al usuario `root` de MariaDB.
- Responde `Y` a remover usuarios anonimos.
- Responde `Y` a deshabilitar login remoto de root.
- Responde `Y` a eliminar base de datos de prueba `test`.
- Responde `Y` a recargar tablas de privilegios.

### 5.2. Cambiar la Contrasena del Administrador del Panel
1. Ingresa a `http://IP_DE_TU_SERVIDOR:8083`.
2. Inicia sesion con `admin` / `admin123`.
3. Ve a tu perfil en la esquina superior derecha y actualiza tu contrasena inmediatamente.

### 5.3. Configurar Subdominio y Certificado SSL para el Panel
1. En tu proveedor DNS, crea un registro tipo `A` (ejemplo: `cp.tudominio.com`) apuntando a la direccion IP publica del servidor.
2. Dentro del panel de PiruluGCP, ve a **Configuracion Servidor > Identidad del Servidor** e ingresa `cp.tudominio.com`.
3. Al guardar, el servidor web Nginx habilitara el acceso directo a traves de `http://cp.tudominio.com` y `http://cp.tudominio.com:8083`.
4. Para activar SSL con Let's Encrypt:
   ```bash
   certbot certonly --webroot -w /usr/local/pirulugcp/cp-web/public -d cp.tudominio.com
   ```
5. En `/etc/nginx/sites-available/pirulugcp.conf`, asigna los certificados emitidos en el bloque `listen 443 ssl` y recarga Nginx:
   ```bash
   systemctl reload nginx
   ```

---

## 6. Comandos Utiles de Mantenimiento

| Tarea | Comando por Terminal |
|---|---|
| Estado de Servicios | `/usr/local/pirulugcp/engine/bin/pirulu-system status` |
| Reiniciar Panel Web | `systemctl restart pirulugcp-php && systemctl reload nginx` |
| Ver Logs del Panel | `tail -f /var/log/nginx/pirulugcp_error.log` |
| Ver Logs del Engine | `tail -f /var/log/pirulugcp/engine.log` |
| Crear Cuenta FTP | `/usr/local/pirulugcp/engine/bin/pirulu-ftp account-add <usuario> <clave> admin <dominio> [ruta]` |
