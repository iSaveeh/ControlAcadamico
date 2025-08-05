<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
header('Content-Type: application/json');


require_once 'conexion.php'; // Asegúrate que este archivo también no tenga salida extra

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idActividad = filter_var($_POST['idActividad'] ?? '', FILTER_VALIDATE_INT);
    $id_profesor_logueado = $_SESSION['id'];

    if ($idActividad === false) {
        $response['message'] = 'ID de actividad no válido para eliminar.';
        echo json_encode($response);
        exit();
    }

    try {
        $query = "
            DELETE act FROM actividades AS act
            JOIN asignaturas AS asig ON act.IDAsignatura = asig.IDAsignatura
            WHERE act.IDActividad = ? AND asig.IDProfesor = ?
        ";
        $stmt = $conexion->prepare($query);

        if ($stmt === false) {
            throw new Exception("Error al preparar la consulta de eliminación: " . $conexion->error);
        }

        $stmt->bind_param("ii", $idActividad, $id_profesor_logueado);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Actividad eliminada exitosamente.';
            } else {
                // Si affected_rows es 0, significa que la actividad no existía o no pertenecía al profesor logueado.
                // En este caso, no hubo una eliminación exitosa del registro deseado.
                $response['success'] = false;
                $response['message'] = 'No se encontró la actividad o no tienes permisos para eliminarla.';
            }
        } else {
            $response['message'] = 'Error al eliminar la actividad de la base de datos: ' . $stmt->error;
        }

        $stmt->close();

    } catch (Exception $e) {
        $response['message'] = 'Error en el servidor: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Método de solicitud no permitido.';
}

echo json_encode($response);
exit(); // Importante para asegurar que no haya más salida después del JSON
// Si no hay más código PHP, no cierres la etiqueta ?>