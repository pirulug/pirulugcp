# Configuracion principal de Dovecot para PiruluGCP (Debian 12 / 13 Trixie)
dovecot_config_version = 2.4.0
dovecot_storage_version = 2.4.0

protocols = imap pop3 lmtp
listen = *, ::

# Autenticacion de usuarios virtuales
auth_mechanisms = plain login

passdb passwd-file {
    default_password_scheme = SHA512-CRYPT
    auth_username_format = %{user}
    passwd_file_path = /etc/dovecot/users
}

userdb passwd-file {
    auth_username_format = %{user}
    passwd_file_path = /etc/dovecot/users
}

# Ubicacion de buzones en formato Maildir
mail_driver = maildir
mail_path = %{home}/Maildir

# SSL / TLS
ssl = yes
ssl_server_cert_file = /etc/ssl/certs/ssl-cert-snakeoil.pem
ssl_server_key_file = /etc/ssl/private/ssl-cert-snakeoil.key
ssl_min_protocol = TLSv1.2

# Socket de autenticacion Dovecot SASL para Exim4
service auth {
    unix_listener auth-client {
        mode = 0660
        user = Debian-exim
        group = Debian-exim
    }
    unix_listener auth-userdb {
        mode = 0600
        user = root
    }
}

service lmtp {
    unix_listener lmtp {
        mode = 0660
        user = Debian-exim
        group = Debian-exim
    }
}
