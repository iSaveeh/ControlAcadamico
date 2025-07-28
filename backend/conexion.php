<?php
$conexion = new mysqli("localhost", "root", "Katiritas0221", "focusgrade");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>