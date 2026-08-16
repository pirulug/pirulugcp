<VirtualHost 127.0.0.1:8080>
    ServerName %DOMAIN%
    ServerAlias www.%DOMAIN% %ALIASES%
    ServerAdmin webmaster@%DOMAIN%

    DocumentRoot %DOC_ROOT%

    <Directory %DOC_ROOT%>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:%FPM_SOCKET%|fcgi://localhost"
    </FilesMatch>

    ErrorLog ${APACHE_LOG_DIR}/%DOMAIN%_error.log
    CustomLog ${APACHE_LOG_DIR}/%DOMAIN%_access.log combined
</VirtualHost>
