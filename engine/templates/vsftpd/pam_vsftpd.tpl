# ==============================================================================
# PiruluGCP - Configuracion PAM para vsftpd (libpam-pwdfile)
# ==============================================================================
auth required pam_pwdfile.so pwdfile /etc/vsftpd/ftpd.passwd
account required pam_permit.so
