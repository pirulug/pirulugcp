[global]
pid = /run/pirulugcp-php.pid
error_log = /var/log/pirulugcp/php-fpm.log
daemonize = no

[pirulugcp]
user = www-data
group = www-data
listen = /run/php/pirulugcp-panel.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = ondemand
pm.max_children = 10
pm.process_idle_timeout = 60s
pm.max_requests = 500

php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 128M
php_admin_value[post_max_size] = 128M
