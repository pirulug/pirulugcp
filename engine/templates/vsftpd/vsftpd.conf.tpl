# ==============================================================================
# PiruluGCP - Configuracion de vsftpd con Usuarios Virtuales
# ==============================================================================

listen=YES
listen_ipv6=NO
anonymous_enable=NO
local_enable=YES
write_enable=YES
local_umask=022
dirmessage_enable=YES
use_localtime=YES
xferlog_enable=YES
connect_from_port_20=YES

# Configuracion de Chroot y Seguridad
chroot_local_user=YES
allow_writeable_chroot=YES
hide_ids=YES
utf8_filesystem=YES
seccomp_sandbox=NO

# Soporte de Usuarios Virtuales con PAM
guest_enable=YES
guest_username=admin
user_config_dir=/etc/vsftpd/users
pam_service_name=vsftpd
virtual_use_local_privs=YES

# Rango de Puertos Pasivos para Cortafuegos
pasv_enable=YES
pasv_min_port=40000
pasv_max_port=50000

# Configuracion SSL/TLS (Opcional)
ssl_enable=NO
rsa_cert_file=/etc/ssl/certs/ssl-cert-snakeoil.pem
rsa_private_key_file=/etc/ssl/private/ssl-cert-snakeoil.key
