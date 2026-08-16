#!/usr/bin/env bash
# Libreria comun de funciones para PiruluGCP

PIRULU_ROOT="/usr/local/pirulugcp"
CONFIG_FILE="${PIRULU_ROOT}/config/panel.conf"
SERVICES_FILE="${PIRULU_ROOT}/config/services.conf"

if [ -f "$CONFIG_FILE" ]; then
    # shellcheck disable=SC1090
    source "$CONFIG_FILE"
fi

if [ -f "$SERVICES_FILE" ]; then
    # shellcheck disable=SC1090
    source "$SERVICES_FILE"
fi

log_message() {
    local level="$1"
    local message="$2"
    local timestamp
    timestamp=$(date "+%Y-%m-%d %H:%M:%S")
    echo "[$timestamp] [$level] $message"
    if [ -d "$LOG_DIR" ]; then
        echo "[$timestamp] [$level] $message" >> "${LOG_DIR}/engine.log"
    fi
}

log_info() {
    log_message "INFO" "$1"
}

log_error() {
    log_message "ERROR" "$1" >&2
}

exit_error() {
    log_error "$1"
    exit 1
}

check_root() {
    if [ "$EUID" -ne 0 ]; then
        exit_error "Este comando debe ejecutarse con privilegios de root (sudo)."
    fi
}

validate_domain() {
    local domain="$1"
    if [[ ! "$domain" =~ ^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$ ]]; then
        exit_error "Nombre de dominio no valido: $domain"
    fi
}

validate_username() {
    local user="$1"
    if [[ ! "$user" =~ ^[a-z_][a-z0-9_-]{2,31}$ ]]; then
        exit_error "Nombre de usuario no valido: $user"
    fi
}
