Guía paso a paso para desplegar y probar PiruluGCP en una máquina virtual limpia con Debian 13 (Trixie) en VirtualBox:

Paso 1: Configurar la Máquina Virtual en VirtualBox
Crear Máquina Virtual:
Nombre: Debian-PiruluGCP
Tipo: Linux
Versión: Debian (64-bit)
Hardware:
Memoria RAM: 2048 MB (2 GB) mínimo (4096 MB recomendado).
Procesadores: 2 CPUs.
Disco duro: 20 GB o superior (formato VDI dinámico).
Configuración de Red (Paso Clave):
Ve a la Configuración de la VM > Red > Adaptador 1.
En Conectado a:, cambia NAT por Adaptador puente (Bridged Adapter) y selecciona tu tarjeta de red física (WiFi o Ethernet).
Ventaja: La máquina virtual obtendrá una dirección IP real de tu red local (ejemplo: 192.168.1.150), permitiendo acceder al panel y a los sitios web directamente desde el navegador de tu computadora Windows.
Paso 2: Instalar Debian 13
Inicia la VM con la ISO de Debian (Netinst o DVD).
Durante la selección de software (Software selection):
Desmarca los entornos de escritorio gráficos (GNOME, XFCE, KDE) para que el servidor sea ligero y rápido.
Marca: SSH server y Standard system utilities.
Asigna una contraseña al usuario root.
Paso 3: Obtener la IP y Preparar el Sistema
Una vez termine la instalación y reinicie la máquina virtual, inicia sesión como root:

Identifica la dirección IP asignada por tu router:

bash
ip a
(Busca la dirección en la interfaz enp0s3 o eth0, por ejemplo: 192.168.1.150).

Actualiza los repositorios e instala las herramientas base:

bash
apt-get update && apt-get install -y git curl sudo ca-certificates rsync
Paso 4: Instalar PiruluGCP
Ejecuta el instalador automatizado directamente en la terminal de la máquina virtual:

bash
rm -rf /tmp/pirulugcp && \
git clone https://github.com/pirulug/pirulugcp.git /tmp/pirulugcp && \
cd /tmp/pirulugcp && \
chmod +x install.sh install/modules/*.sh && \
./install.sh
(Si deseas probar directamente el código que tienes actualmente en tu máquina local Windows en lugar del repositorio remoto de GitHub, puedes sincronizarlo por rsync o scp hacia la VM: rsync -av --exclude='.git' /ruta/local/ root@192.168.1.150:/tmp/pirulugcp/).

El instalador configurará de forma automática:

Nginx (Proxy en puertos 80 y 443).
Apache 2 (Backend en puerto 8080).
MariaDB Server.
PHP-FPM base y Composer.
phpMyAdmin con Signon SSO.
Servicio y VirtualHost de PiruluGCP en el puerto 8083.
Paso 5: Probar el Panel desde Windows
Abre tu navegador web en Windows e ingresa a:

http://IP_DE_TU_VM:8083
(Ejemplo: http://192.168.1.150:8083).

Credenciales por defecto:

Usuario: admin
Contraseña: admin123
Paso 6: Probar la Creación de Dominios Reales
Dentro de PiruluGCP, ve a Sitios Web > Nuevo Dominio y crea un dominio de prueba (ejemplo: misitio.test).
En tu computadora Windows, abre el Bloc de notas como Administrador y edita el archivo: C:\Windows\System32\drivers\etc\hosts
Agrega la siguiente línea al final:
192.168.1.150 misitio.test
Abre http://misitio.test en tu navegador para verificar el funcionamiento del stack completo (Nginx + Apache + PHP-FPM) y comprueba el seguimiento de peticiones y depuración SQL en vivo dentro del panel.