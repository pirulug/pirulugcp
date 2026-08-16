# Configuracion de Exim4 para PiruluGCP con soporte Multi-Dominio, DKIM y Dovecot SASL

# -----------------------------------------------------------------------------
# SECCION: CONFIGURACION GLOBAL
# -----------------------------------------------------------------------------
domainlist local_domains = @ : localhost : dsearch;/etc/exim4/domains
domainlist relay_to_domains =
hostlist   relay_from_hosts = 127.0.0.1 : ::::1

acl_smtp_rcpt = acl_check_rcpt
acl_smtp_data = acl_check_data

qualify_domain = localhost
never_users = root
host_lookup = *
rfc1413_hosts = *
rfc1413_query_timeout = 5s
ignore_bounce_errors_after = 2d
timeout_frozen_after = 7d

# Puertos de escucha: SMTP (25), Submission (587), SMTPS (465)
daemon_smtp_ports = 25 : 465 : 587
tls_on_connect_ports = 465

# Certificados SSL/TLS
tls_certificate = /etc/ssl/certs/ssl-cert-snakeoil.pem
tls_privatekey = /etc/ssl/private/ssl-cert-snakeoil.key
tls_require_ciphers = NORMAL

# Firma DKIM dinamica por dominio
DKIM_DOMAIN = ${sender_address_domain}
DKIM_KEY_FILE = /etc/exim4/dkim/${DKIM_DOMAIN}.private
DKIM_PRIVATE_KEY = ${if exists{DKIM_KEY_FILE}{DKIM_KEY_FILE}{0}}
DKIM_SELECTOR = default
DKIM_CANON = relaxed

# -----------------------------------------------------------------------------
# SECCION: CONTROL DE ACCESO (ACLs)
# -----------------------------------------------------------------------------
begin acl

acl_check_rcpt:
  accept  hosts = :
  deny    message = "Caracteres invalidos en direccion"
          domains = +local_domains
          local_parts = ^[.] : ^.*[@%!/|]

  # Aceptar correo local o autenticado
  accept  hosts = +relay_from_hosts
          control = submission

  accept  authenticated = *
          control = submission/domain=

  # Comprobar si el destinatario es un dominio local valido
  require message = "Relevo no permitido"
          domains = +local_domains

  # Verificar existencia del destinatario local
  require verify = recipient

  accept

acl_check_data:
  accept

# -----------------------------------------------------------------------------
# SECCION: ENRUTADORES (ROUTERS)
# -----------------------------------------------------------------------------
begin routers

# Reenvios y alias virtuales por dominio
virtual_aliases:
  driver = redirect
  allow_defer
  allow_fail
  data = ${lookup{$local_part}lsearch{/etc/exim4/aliases/$domain}}
  retry_use_local_part

# Entrega a buzones de usuarios virtuales via Dovecot LMTP
virtual_user:
  driver = accept
  domains = dsearch;/etc/exim4/domains
  condition = ${if exists{/etc/dovecot/users}{${lookup{$local_part@$domain}lsearch{/etc/dovecot/users}{yes}{no}}}{no}}
  transport = dovecot_lmtp

# Enrutamiento de salida hacia internet
dnslookup:
  driver = dnslookup
  domains = ! +local_domains
  transport = remote_smtp
  ignore_target_hosts = 0.0.0.0 : 127.0.0.0/8
  no_more

# -----------------------------------------------------------------------------
# SECCION: TRANSPORTES (TRANSPORTS)
# -----------------------------------------------------------------------------
begin transports

# Envio remoto con DKIM
remote_smtp:
  driver = smtp
  dkim_domain = DKIM_DOMAIN
  dkim_selector = DKIM_SELECTOR
  dkim_private_key = DKIM_PRIVATE_KEY
  dkim_canon = DKIM_CANON
  message_linelength_limit = 2048

# Entrega local via Dovecot LMTP socket
dovecot_lmtp:
  driver = lmtp
  socket = /var/run/dovecot/lmtp
  batch_max = 200

# -----------------------------------------------------------------------------
# SECCION: AUTENTICACION (AUTHENTICATORS)
# -----------------------------------------------------------------------------
begin authenticators

# Autenticacion SASL usando Dovecot
dovecot_plain:
  driver = dovecot
  public_name = PLAIN
  server_socket = /var/run/dovecot/auth-client
  server_set_id = $auth1

dovecot_login:
  driver = dovecot
  public_name = LOGIN
  server_socket = /var/run/dovecot/auth-client
  server_set_id = $auth1
