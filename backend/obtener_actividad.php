<?php
session_start();
header('Content-Type: application/json');

// Verificar si el usuario está logueado y es un profesor
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'profesor') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit();
}

require_once 'conexion.php';

$response = ['success' => false, 'message' => ''];

if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $idActividad = $_GET['id'];
    $id_profesor_logueado = $_SESSION['id'];

    try {
        // Asegurarse de que la actividad pertenezca al profesor logueado
        $query = "
            SELECT
                act.IDActividad,
                act.IDAsignatura,
                act.NombreActividad,
                act.Descripcion,
                act.Fecha,
                act.Porcentaje
            FROM
                actividades AS act
            JOIN
                asignaturas AS asig ON act.IDAsignatura = asig.IDAsignatura
            WHERE
                act.IDActividad = ? AND asig.IDProfesor = ?
        ";
        $stmt = $conexion->prepare($query);

        if ($stmt === false) {
            throw new Exception("Error al preparar la consulta: " . $conexion->error);
        }

        $stmt->bind_param("ii", $idActividad, $id_profesor_logueado);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $actividad = $resultado->fetch_assoc();

        if ($actividad) {
            $response['success'] = true;
            $response['actividad'] = $actividad;
        } else {
            $response['message'] = 'Actividad no encontrada o no pertenece a este profesor.';
        }

        $stmt->close();

    } catch (Exception $e) {
        $response['message'] = 'Error en el servidor: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'ID de actividad no válido.';
}

echo json_encode($response);
exit();
?>