#!/usr/bin/env bash
# Script orquestador de instalacion de PiruluGCP con Sistema de Logs Detallado
# Arquitectura Modular para Debian 13 (Trixie), Debian 12/11 y Ubuntu

set -eE

SOURCE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
INSTALL_MODULES_DIR="${SOURCE_DIR}/install/modules"
PIRULU_INSTALL_DIR="/usr/local/pirulugcp"
LOG_DIR="/var/log/pirulugcp"
LOG_FILE="${LOG_DIR}/install.log"

# Preparar directorio de logs y redireccion
mkdir -p "$LOG_DIR" 2>/dev/null || LOG_DIR="/tmp"
LOG_FILE="${LOG_DIR}/pirulugcp_install.log"
touch "$LOG_FILE" 2>/dev/null || true

# Redirigir toda la salida (stdout y stderr) hacia la terminal y el archivo de log simultaneamente
exec > >(tee -i -a "$LOG_FILE") 2>&1

export SOURCE_DIR PIRULU_INSTALL_DIR LOG_FILE

# Manejador de errores para capturar la linea y comando exacto que falle
error_handler() {
    local exit_code="$1"
    local line_number="$2"
    local bash_command="$3"
    echo ""
    echo "=========================================================="
    echo "  [ERROR CRITICO EN LA INSTALACION]"
    echo "  Fallo en la linea: ${line_number}"
    echo "  Comando fallido: ${bash_command}"
    echo "  Codigo de salida: ${exit_code}"
    echo "  Log detallado guardado en: ${LOG_FILE}"
    echo "=========================================================="
    exit "$exit_code"
}

trap 'error_handler $? $LINENO "$BASH_COMMAND"' ERR

# Normalizar saltos de linea de scripts (LF)
find "$SOURCE_DIR" -type f \( -name "*.sh" -o -name "pirulu-*" \) -exec sed -i 's/\r$//' {} + 2>/dev/null || true

# Cargar modulos de instalacion
for module in "${INSTALL_MODULES_DIR}"/*.sh; do
    if [ -f "$module" ]; then
        # shellcheck disable=SC1090
        source "$module"
    fi
done

print_banner() {
    echo "=========================================================="
    echo "          Instalador Modular de PiruluGCP Panel           "
    echo "        Compatible con Debian 13 (Trixie) y Ubuntu        "
    echo "  Nginx + Certbot SSL + MariaDB + PHP-FPM + phpMyAdmin   "
    echo "  Registro de Log: ${LOG_FILE}"
    echo "=========================================================="
}

print_summary() {
    echo "=========================================================="
    echo "  Instalacion de PiruluGCP completada con exito!          "
    echo "  Panel de Control:  http://localhost:8083               "
    echo "  phpMyAdmin:        http://localhost:8083/phpmyadmin    "
    echo "  Usuario por defecto: admin                             "
    echo "  Clave por defecto: admin123                            "
    echo "  Registro completo de log: ${LOG_FILE}"
    echo "=========================================================="
}

main() {
    print_banner

    check_system_requirements
    setup_php_repositories
    setup_webserver
    setup_php_multiversion
    setup_phpmyadmin
    deploy_pirulugcp_core
    setup_nginx_vhost

    print_summary
}

main "$@"
