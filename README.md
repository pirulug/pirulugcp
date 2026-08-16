# PiruluGCP - Panel de Control Web Ligero

PiruluGCP es un panel de control de hosting web simple, modular y eficiente desarrollado con **Bootstrap 5**, **PHP Vanilla**, **Nginx / Apache**, **MariaDB**, **Let's Encrypt (Certbot)**, **phpMyAdmin con Auto-Login (SSO)** y soporte multi-version para **PHP-FPM** (7.4, 8.0, 8.1, 8.2, 8.3).

---

## Modulos del Sistema

1. **Dashboard**: Metricas de carga, memoria RAM, almacenamiento en disco y estado de servicios en tiempo real.
2. **Dominios Web (Nginx / Apache)**:
   - Creacion y eliminacion de VirtualHosts.
   - Selector directo de version PHP-FPM por dominio.
   - **Certificados SSL Gratuitos (Let's Encrypt)** con un solo clic y renovacion automatica.
3. **PHP-FPM Multi-Version**: Gestion de versiones aisladas por sockets UNIX dedicados y reinicio individual de servicios FastCGI.
4. **Bases de Datos (MariaDB) y phpMyAdmin**:
   - Creacion automatica de bases de datos, asignacion de usuarios, claves y privilegios.
   - **Boton "Abrir phpMyAdmin" con Auto-Login (Signon SSO)** directo a la base de datos sin necesidad de ingresar contrasena.
5. **Servicios y Sistema**: Control de estados (Start, Stop, Restart, Reload) para Nginx, MariaDB y servicios PHP-FPM.

---

## Estructura del Proyecto

```text
pirulugcp/
├── config/
│   ├── panel.conf                 # Parametros generales del panel
│   └── services.conf              # Definicion de rutas y comandos
├── engine/
│   ├── bin/                       # Comandos CLI (pirulu-web, pirulu-ssl, pirulu-php, pirulu-db, etc.)
│   ├── lib/                       # Librerias comunes y helpers SQL
│   └── templates/                 # Plantillas de Nginx, Apache, PHP-FPM y phpMyAdmin
├── cp-web/                        # Panel Web
│   ├── app/
│   │   ├── Core/                  # Router, Base de datos, Auth, Engine Bridge, View
│   │   ├── Modules/               # Modulos (Dashboard, Web, Php, Database, System, Auth)
│   │   └── Views/                 # Layout maestro, cabecera, barra lateral y pie
│   └── public/                    # DocumentRoot del panel (index.php, CSS, JS, phpMyAdmin)
└── install/
    └── install.sh                 # Script instalador automatizado para Ubuntu/Debian
```

---

## Requisitos e Instalacion

Para instalar en un servidor **Ubuntu 20.04/22.04/24.04** o **Debian 11/12**:

```bash
sudo chmod +x install/install.sh
sudo ./install/install.sh
```

El panel quedara disponible en:
- **URL**: `http://TU-IP-SERVIDOR:8083`
- **phpMyAdmin**: `http://TU-IP-SERVIDOR:8083/phpmyadmin`
- **Usuario por defecto**: `admin`
- **Contrasena por defecto**: `admin123`
