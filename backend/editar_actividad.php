<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'profesor') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit();
}

require_once 'conexion.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idActividad = filter_var($_POST['idActividad'] ?? '', FILTER_VALIDATE_INT);
    $nombreActividad = trim($_POST['nombreActividad'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $idAsignatura = filter_var($_POST['idAsignatura'] ?? '', FILTER_VALIDATE_INT);
    $fecha = trim($_POST['fecha'] ?? '');

    $porcentaje_raw = trim($_POST['porcentaje'] ?? '');
    $porcentaje_cleaned = str_replace(',', '.', $porcentaje_raw);
    $porcentaje = filter_var($porcentaje_cleaned, FILTER_VALIDATE_FLOAT);

    $id_profesor_logueado = $_SESSION['id'];

    if ($idActividad === false || empty($nombreActividad) || empty($descripcion) || $idAsignatura === false || empty($fecha) || $porcentaje === false || $porcentaje < 0 || $porcentaje > 100) {
        $response['message'] = 'Por favor, complete todos los campos correctamente y asegúrese que el porcentaje esté entre 0 y 100.';
        echo json_encode($response);
        exit();
    }

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

        // Si la ejecución de la consulta fue exitosa (true)
        if ($stmt->execute()) {
            // No importa si affected_rows es 0 o > 0, si la ejecución fue exitosa, la acción fue "exitosa"
            // Solo si hubo un error de ejecución, entonces sí sería false.
            $response['success'] = true;
            $response['message'] = 'Actividad actualizada exitosamente.';
            // Opcional: Si quieres un mensaje diferente para "sin cambios":
            // if ($stmt->affected_rows === 0) {
            //     $response['message'] = 'La actividad se guardó, pero no se detectaron cambios.';
            // } else {
            //     $response['message'] = 'Actividad actualizada exitosamente.';
            // }

        } else {
            // Solo aquí se debe considerar un error de la base de datos
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
?>