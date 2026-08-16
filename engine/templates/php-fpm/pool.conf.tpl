[%POOL_NAME%]
user = %USER%
group = %USER%

listen = %FPM_SOCKET%
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = ondemand
pm.max_children = 10
pm.process_idle_timeout = 30s
pm.max_requests = 500

chdir = /

; Aislamiento: permite acceso al directorio web completo del dominio
; incluyendo directorios padre (ej. vendor de Composer fuera del docroot)
php_admin_value[open_basedir] = %WEB_ROOT%:/tmp:/var/tmp
php_admin_value[upload_tmp_dir] = /tmp
php_admin_value[session.save_path] = /tmp
php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 64M
php_admin_value[post_max_size] = 64M
php_admin_value[max_execution_time] = 120
