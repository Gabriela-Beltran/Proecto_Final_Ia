<?php
include_once "prolog.php";

session_start();
session_unset();
session_destroy();
setcookie('PHPSESSID', '', time() - 3600, '/'); // Elimina la cookie de sesión
procesarSolicitud("cerrar_sesion");
header("Location: login.php");
exit();
