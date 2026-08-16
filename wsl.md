# Guia de Sincronizacion y Actualizacion en WSL (Debian)

Este documento detalla los comandos para reflejar los cambios realizados en el entorno de desarrollo Windows hacia el entorno de pruebas en WSL Debian (`/usr/local/pirulugcp`), asi como el procedimiento para reinstalar Debian y PiruluGCP completamente desde cero.

---

## 1. Comando de Sincronizacion Completa (Recomendado)

Ejecuta el siguiente comando en PowerShell desde la raiz del proyecto (`D:\Development\pirulugcp`) para sincronizar todo el codigo, ajustar permisos y recargar servicios:

```powershell
wsl -d Debian -u root -- bash -c "
  # 1. Sincronizar archivos del proyecto (excluyendo git)
  rsync -av --exclude='.git' /mnt/d/Development/pirulugcp/ /usr/local/pirulugcp/

  # 2. Asignar permisos de ejecucion a los binarios del engine
  chmod +x /usr/local/pirulugcp/engine/bin/*

  # 3. Asignar propietario web para la interfaz y configuracion
  chown -R www-data:www-data /usr/local/pirulugcp/cp-web /usr/local/pirulugcp/config /var/lib/pirulugcp /var/log/pirulugcp

  # 4. Recargar servicio PHP del panel y servidores web
  systemctl restart pirulugcp-php 2>/dev/null || true
  systemctl reload nginx 2>/dev/null || true
  systemctl reload apache2 2>/dev/null || true

  echo 'Sincronizacion completada con exito.'
"
```

---

## 2. Sincronizacion por Modulos Especificos

### Actualizar solo el Engine (`engine/bin` y `engine/lib`)
```powershell
wsl -d Debian -u root -- bash -c "
  cp -r /mnt/d/Development/pirulugcp/engine/* /usr/local/pirulugcp/engine/
  chmod +x /usr/local/pirulugcp/engine/bin/*
  echo 'Engine actualizado.'
"
```

### Actualizar solo la Interfaz Web (`cp-web`)
```powershell
wsl -d Debian -u root -- bash -c "
  cp -r /mnt/d/Development/pirulugcp/cp-web/* /usr/local/pirulugcp/cp-web/
  chown -R www-data:www-data /usr/local/pirulugcp/cp-web
  systemctl reload nginx
  echo 'Panel web actualizado.'
"
```

### Actualizar Configuracion y Templates
```powershell
wsl -d Debian -u root -- bash -c "
  cp -r /mnt/d/Development/pirulugcp/config/* /usr/local/pirulugcp/config/ 2>/dev/null || true
  cp -r /mnt/d/Development/pirulugcp/engine/templates/* /usr/local/pirulugcp/engine/templates/ 2>/dev/null || true
  chown -R www-data:www-data /usr/local/pirulugcp/config
  systemctl restart pirulugcp-php
  echo 'Templates y configuracion actualizados.'
"
```

---

## 3. Verificacion de Estado de Servicios en WSL

Para comprobar que todos los servicios estan activos tras una actualizacion:

```powershell
wsl -d Debian -u root -- bash -c "
  source /usr/local/pirulugcp/config/panel.conf
  /usr/local/pirulugcp/engine/bin/pirulu-system status
"
```

O verificar el servicio PHP del panel:

```powershell
wsl -d Debian -u root -- systemctl status pirulugcp-php --no-pager
```

---

## 4. Verificacion de Logs de Error

Para inspeccionar si ocurrio algun error en tiempo real:

```powershell
# Log del Engine
wsl -d Debian -u root -- tail -n 50 /var/log/pirulugcp/engine.log

# Log del Panel Web (Nginx)
wsl -d Debian -u root -- tail -n 50 /var/log/nginx/pirulugcp_error.log

# Log del PHP del Panel
wsl -d Debian -u root -- tail -n 50 /var/log/pirulugcp/php-fpm.log
```

---

## 5. Reinstalacion Limpia de Debian y PiruluGCP desde Cero

Para restablecer completamente el entorno de pruebas, eliminar la distribución actual e instalar Debian y PiruluGCP de forma limpia:

### Paso 1: Eliminar la distribucion Debian existente
Abre PowerShell como Administrador y ejecuta:

```powershell
wsl --unregister Debian
```

### Paso 2: Instalar una nueva instancia limpia de Debian
```powershell
wsl --install -d Debian
```

### Paso 3: Habilitar Systemd y reiniciar WSL
Ejecuta los siguientes comandos para habilitar systemd (indispensable para Nginx, Apache, MariaDB y PHP-FPM):

```powershell
wsl -d Debian -u root -- bash -c "printf '[boot]\nsystemd=true\n' > /etc/wsl.conf"
wsl --shutdown
```

### Paso 4: Instalar dependencias base y ejecutar el instalador de PiruluGCP
Abre PowerShell y ejecuta la instalacion completa:

```powershell
wsl -d Debian -u root -- bash -c "
  # Actualizar repositorios e instalar paquetes base
  apt-get update
  apt-get install -y sudo curl rsync lsb-release ca-certificates

  # Ejecutar instalador de PiruluGCP
  cd /mnt/d/Development/pirulugcp/install
  chmod +x install.sh
  ./install.sh
"
```

### Script Completo de Reinstalacion en Un Solo Comando (PowerShell)

Para hacer todo el proceso anterior automaticamente de una sola vez:

```powershell
# 1. Eliminar distro vieja
wsl --unregister Debian

# 2. Instalar nueva distro
wsl --install -d Debian --no-launch

# 3. Configurar systemd y reiniciar
wsl -d Debian -u root -- bash -c "printf '[boot]\nsystemd=true\n' > /etc/wsl.conf"
wsl --shutdown

# 4. Instalar dependencias y PiruluGCP
wsl -d Debian -u root -- bash -c "
  apt-get update && apt-get install -y sudo curl rsync lsb-release ca-certificates
  cd /mnt/d/Development/pirulugcp/install
  chmod +x install.sh
  ./install.sh
"
```
