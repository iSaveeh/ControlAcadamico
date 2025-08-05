<?php
session_start();
header('Content-Type: application/json');

require_once 'conexion.php';

$response = ['success' => false, 'message' => ''];

// Verifica que el usuario esté autenticado como profesor
$usuario = $_SESSION['usuario'] ?? '';
$rol = $_SESSION['rol'] ?? '';

if ($rol !== 'profesor' || empty($usuario)) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit();
}

// Obtener IDProfesor desde la tabla profesor usando el campo Usuario
$id_profesor_logueado = null;
$stmt = $conexion->prepare("SELECT Usuario FROM profesor WHERE Usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$stmt->bind_result($id_profesor);
if ($stmt->fetch()) {
    $id_profesor_logueado = $id_profesor;
}
$stmt->close();

if ($id_profesor_logueado === null) {
    echo json_encode(['success' => false, 'message' => 'No se pudo verificar el profesor.']);
    exit();
}

// Validación del método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idActividad = filter_var($_POST['idActividad'] ?? '', FILTER_VALIDATE_INT);
    $nombreActividad = trim($_POST['nombreActividad'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $idAsignatura = filter_var($_POST['idAsignatura'] ?? '', FILTER_VALIDATE_INT);
    $fecha = trim($_POST['fecha'] ?? '');

    $porcentaje_raw = trim($_POST['porcentaje'] ?? '');
    $porcentaje_cleaned = str_replace(',', '.', $porcentaje_raw);
    $porcentaje = filter_var($porcentaje_cleaned, FILTER_VALIDATE_FLOAT);

    // Validación de datos
    if (
        $idActividad === false || empty($nombreActividad) || empty($descripcion) ||
        $idAsignatura === false || empty($fecha) ||
        $porcentaje === false || $porcentaje < 0 || $porcentaje > 100
    ) {
        $response['message'] = 'Por favor, complete todos los campos correctamente y asegúrese que el porcentaje esté entre 0 y 100.';
        echo json_encode($response);
        exit();
    }

    // Actualizar la actividad (solo si el profesor es dueño de esa asignatura)
    try {
        $query = "
            UPDATE actividades AS act
            JOIN asignaturas AS asig ON act.IDAsignatura = asig.IDAsignatura
            SET
                act.IDAsignatura = ?,
                act.NombreActividad = ?,
                act.Descripcion = ?,
                act.Fecha = ?,
                act.Porcentaje = ?
            WHERE
                act.IDActividad = ? AND asig.IDProfesor = ?
        ";
        $stmt = $conexion->prepare($query);

        if ($stmt === false) {
            throw new Exception("Error al preparar la consulta de actualización: " . $conexion->error);
        }

        $stmt->bind_param("isssdii", $idAsignatura, $nombreActividad, $descripcion, $fecha, $porcentaje, $idActividad, $id_profesor_logueado);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Actividad actualizada exitosamente.';
        } else {
            $response['message'] = 'Error al actualizar la actividad en la base de datos: ' . $stmt->error;
        }

        $stmt->close();

    } catch (Exception $e) {
        $response['message'] = 'Error en el servidor: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Método de solicitud no permitido.';
}

echo json_encode($response);
exit();
