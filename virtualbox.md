# Guía de Despliegue de PiruluGCP en Debian (VirtualBox)

Guía paso a paso para desplegar y probar PiruluGCP en una máquina virtual limpia con Debian 13 (Trixie) o Debian 12 en VirtualBox.

---

## Paso 1: Configurar la Máquina Virtual en VirtualBox

### 1. Crear Máquina Virtual
- **Nombre:** `Debian-PiruluGCP`
- **Tipo:** `Linux`
- **Versión:** `Debian (64-bit)`

### 2. Hardware
- **Memoria RAM:** `2048 MB` (2 GB) mínimo (`4096 MB` recomendado).
- **Procesadores:** `2 CPUs`.
- **Disco duro:** `20 GB` o superior (formato VDI de tamaño dinámico).

### 3. Configuración de Red (Paso Clave)
1. Ve a **Configuración de la VM** &rarr; **Red** &rarr; **Adaptador 1**.
2. En **Conectado a:**, cambia `NAT` por **Adaptador puente (Bridged Adapter)**.
3. Selecciona tu tarjeta de red física activa (Wi-Fi o Ethernet).

> **Ventaja:** La máquina virtual obtendrá una dirección IP real dentro de tu red local (por ejemplo: `192.168.1.150`), permitiendo acceder al panel y a los sitios web directamente desde el navegador de tu equipo Windows.

---

## Paso 2: Instalar Debian

1. Inicia la máquina virtual seleccionando la imagen ISO de Debian (*Netinst* o *DVD*).
2. Durante la selección de paquetes (**Software selection**):
   - **Desmarca** los entornos de escritorio gráficos (GNOME, XFCE, KDE) para mantener el servidor optimizado y ligero.
   - **Marca:** `SSH server` y `Standard system utilities`.
3. Asigna una contraseña segura al usuario `root`.

---

## Paso 3: Obtener la IP y Preparar el Sistema

Una vez finalizada la instalación y reiniciado el sistema, inicia sesión en la terminal como `root`:

### 1. Identificar la dirección IP del servidor
```bash
ip a
```
*(Localiza la dirección IP en la interfaz `enp0s3` o `eth0`, por ejemplo: `192.168.1.150`).*

### 2. Actualizar repositorios e instalar paquetes base
```bash
apt-get update && apt-get install -y git curl sudo ca-certificates rsync
```

---

## Paso 4: Instalar PiruluGCP

Ejecuta el instalador automatizado directamente en la terminal de la máquina virtual:

```bash
rm -rf /tmp/pirulugcp && \
git clone https://github.com/pirulug/pirulugcp.git /tmp/pirulugcp && \
cd /tmp/pirulugcp && \
chmod +x install.sh install/modules/*.sh && \
./install.sh
```

*(Si deseas probar el código local de Windows en lugar del repositorio remoto, sincronízalo directamente por `rsync` hacia la VM: `rsync -av --exclude='.git' /ruta/local/ root@192.168.1.150:/tmp/pirulugcp/`).*

### Servicios configurados automáticamente por el instalador:
- **Nginx:** Proxy reverso en puertos `80` (HTTP) y `443` (HTTPS).
- **Apache 2:** Servidor backend en puerto `8080`.
- **MariaDB Server:** Base de datos relacional.
- **PHP-FPM:** Múltiples versiones (7.4, 8.0, 8.1, 8.2, 8.3, 8.4) y Composer.
- **phpMyAdmin:** Con inicio de sesión directo vía SSO.
- **Panel PiruluGCP:** Servicio web en el puerto `8083`.

---

## Paso 5: Acceder al Panel de Control

Abre el navegador web en Windows e ingresa a:

```text
http://192.168.1.150:8083
```
*(Reemplaza `192.168.1.150` por la IP real de tu máquina virtual).*

### Credenciales por defecto:
- **Usuario:** `admin`
- **Contraseña:** `admin123`

---

## Paso 6: Probar la Creación de Dominios

1. Dentro de PiruluGCP, dirígete a **Gestión Web** &rarr; **Nuevo Dominio** y crea un dominio de prueba (ejemplo: `misitio.test`).
2. En tu computadora Windows, abre el **Bloc de notas** como **Administrador** y edita el archivo:
   ```text
   C:\Windows\System32\drivers\etc\hosts
   ```
3. Agrega la siguiente línea al final:
   ```text
   192.168.1.150 misitio.test
   ```
4. Abre `http://misitio.test` en tu navegador para verificar el funcionamiento del stack completo (Nginx + Apache + PHP-FPM), el seguimiento de peticiones y la depuración SQL en vivo desde el panel.