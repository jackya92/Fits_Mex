<?php
session_start();

// Borrar todos los datos de la sesión
$_SESSION = [];

// Destruir la sesión completamente
session_destroy();

// Redirigir al inicio de sesión
header("Location: ../Inicio_Sesion.html");
exit();
?>