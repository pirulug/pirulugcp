<?php
/**
 * Configuracion de phpMyAdmin con Signon SSO para PiruluGCP
 */

declare(strict_types=1);

$cfg["blowfish_secret"] = "pirulugcp_pma_secret_key_32_bytes_long_string_123456";

$i = 0;
$i++;

/* Configuracion del Servidor MariaDB Local con Signon SSO */
$cfg["Servers"][$i]["auth_type"] = "signon";
$cfg["Servers"][$i]["host"] = "localhost";
$cfg["Servers"][$i]["compress"] = false;
$cfg["Servers"][$i]["AllowNoPassword"] = false;
$cfg["Servers"][$i]["SignonSession"] = "PHPSESSID";
$cfg["Servers"][$i]["SignonURL"] = "/phpmyadmin/login.php";
$cfg["Servers"][$i]["LogoutURL"] = "/database";

/* Configuraciones de apariencia y seguridad */
$cfg["UploadDir"] = "";
$cfg["SaveDir"] = "";
$cfg["SendErrorReports"] = "never";
$cfg["ShowPhpInfo"] = false;
$cfg["ShowServerInfo"] = false;
$cfg["ExecTimeLimit"] = 300;
