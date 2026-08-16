<?php
// Configuracion de Roundcube Webmail para PiruluGCP

$config = [];

// Base de datos de Roundcube
$config["db_dsnw"] = "sqlite:////var/lib/roundcube/roundcube.sqlite?mode=0640";

// Servidor IMAP local
$config["default_host"] = "localhost";
$config["default_port"] = 143;
$config["imap_auth_type"] = "LOGIN";
$config["imap_delimiter"] = "/";

// Servidor SMTP local
$config["smtp_server"] = "localhost";
$config["smtp_port"] = 587;
$config["smtp_user"] = "%u";
$config["smtp_pass"] = "%p";
$config["smtp_auth_type"] = "LOGIN";

// Interfaz y apariencia
$config["product_name"] = "PiruluGCP Webmail";
$config["skin"] = "elastic";
$config["language"] = "es_ES";
$config["support_url"] = "";

// Seguridad
$config["des_key"] = "rcmail-!2026!pirulugcp!secretkey!random";
$config["ip_check"] = true;
$config["session_lifetime"] = 30;

// Plugins habilitados
$config["plugins"] = [
    "archive",
    "zipdownload",
    "hide_blockquote"
];

// Carpetas especiales predeterminadas
$config["drafts_mbox"] = "Drafts";
$config["junk_mbox"] = "Junk";
$config["sent_mbox"] = "Sent";
$config["trash_mbox"] = "Trash";
$config["create_default_folders"] = true;
