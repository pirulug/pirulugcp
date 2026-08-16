#!/usr/bin/env bash
# ==============================================================================
# PiruluGCP - Instalador Integral para Debian 12 / 13 (Trixie) y Ubuntu
# ==============================================================================

set -e

SOURCE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PIRULU_INSTALL_DIR="${PIRULU_INSTALL_DIR:-/usr/local/pirulugcp}"
export SOURCE_DIR PIRULU_INSTALL_DIR

echo "============================================================"
echo "    Iniciando Instalacion de PiruluGCP Control Panel"
echo "============================================================"

MODULES_DIR="${SOURCE_DIR}/install/modules"

# shellcheck disable=SC1090
for mod in "${MODULES_DIR}"/*.sh; do
    if [ -f "$mod" ]; then
        source "$mod"
    fi
done

# Ejecutar pasos en orden
check_system_requirements
setup_php_repositories
setup_webserver
setup_php_multiversion
setup_phpmyadmin
deploy_pirulugcp_core
setup_nginx_vhost
install_firewall
install_mail_stack
install_ftp_server

echo "============================================================"
echo "    Instalacion de PiruluGCP completada exitosamente!"
echo "============================================================"
echo "Acceso al panel: http://$(hostname -I | awk '{print $1}'):8083"
echo "Usuario por defecto: admin"
echo "Contrasena: admin123"
echo "============================================================"
