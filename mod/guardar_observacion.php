<?php
session_start();
require_once '../backend/conexion.php'; 

if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['profesor', 'admin'])) {
    die("Acceso denegado. Tu rol no tiene permisos.");
}

if (empty($_POST['IDEstudiante']) || empty($_POST['tipo']) || empty($_POST['descripcion'])) {
    die("Error: Faltan datos obligatorios.");
}

$IDEstudiante = $_POST['IDEstudiante'];
$TipoFalta = $_POST['tipo'];
$Descripcion = $_POST['descripcion'];
$Compromiso = $_POST['compromiso'] ?? ''; 
$Fecha = $_POST['fecha'];
$IDAutor = $_SESSION['id'];

$stmt_grado = $conexion->prepare("SELECT s.IDGrado FROM estudiante e JOIN salon s ON e.IDSalon = s.IDSalon WHERE e.IDEstudiante = ?");
$stmt_grado->bind_param("s", $IDEstudiante);
$stmt_grado->execute();
$resultado_grado = $stmt_grado->get_result();
$info_grado = $resultado_grado->fetch_assoc();
$IDGrado = $info_grado['IDGrado'] ?? null;
$stmt_grado->close();

if (!$IDGrado) {
    die("Error fatal: No se pudo encontrar el grado del estudiante seleccionado.");
}

$sql = "INSERT INTO observador (IDEstudiante, IDGrado, Descripcion, Compromiso, TipoFalta, Fecha, IDAdministrador) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);

$stmt->bind_param("sssssss", $IDEstudiante, $IDGrado, $Descripcion, $Compromiso, $TipoFalta, $Fecha, $IDAutor);

if ($stmt->execute()) {
    header("Location: ../screens/menu.php?mod=observador_del_estudiante&status=exito");
    exit();
} else {
    echo "Error al guardar la observación: " . $stmt->error;
}

$stmt->close();
$conexion->close();
?>