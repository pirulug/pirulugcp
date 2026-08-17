#!/usr/bin/env bash
# Modulo 00: Deteccion y validacion del sistema operativo

check_system_requirements() {
    echo "[Paso 1/7] Detectando sistema operativo y dependencias basicas..."

    if [ "$EUID" -ne 0 ]; then
        echo "Error: Debes ejecutar este instalador como root (sudo)." >&2
        exit 1
    fi

    apt-get update -y
    apt-get install -y lsb-release ca-certificates apt-transport-https curl git ufw openssl gnupg2 rsync zip unzip

    OS_ID="debian"
    OS_CODENAME="trixie"

    if [ -f /etc/os-release ]; then
        # shellcheck disable=SC1091
        source /etc/os-release
        OS_ID="$ID"
        OS_CODENAME="$VERSION_CODENAME"
        [ -z "$OS_CODENAME" ] && OS_CODENAME="$(lsb_release -sc 2>/dev/null || echo 'trixie')"
    fi

    echo "Sistema detectado: $OS_ID ($OS_CODENAME)"
    export OS_ID OS_CODENAME
}
