<VirtualHost *:80>
    ServerName %DOMAIN%
    ServerAlias www.%DOMAIN% %ALIASES%
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName %DOMAIN%
    ServerAlias www.%DOMAIN% %ALIASES%
    ServerAdmin webmaster@%DOMAIN%

    DocumentRoot %DOC_ROOT%

    <Directory %DOC_ROOT%>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    SSLEngine on
    SSLCertificateFile %SSL_CERT%
    SSLCertificateKeyFile %SSL_KEY%
    %SSL_CA_DIRECTIVE%

    <FilesMatch \.php$>
        SetHandler "proxy:unix:%FPM_SOCKET%|fcgi://localhost"
    </FilesMatch>

    ErrorLog ${APACHE_LOG_DIR}/%DOMAIN%_ssl_error.log
    CustomLog ${APACHE_LOG_DIR}/%DOMAIN%_ssl_access.log combined
</VirtualHost>
