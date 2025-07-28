<?php
$conexion = new mysqli("localhost", "root", "", "focusgrade");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>