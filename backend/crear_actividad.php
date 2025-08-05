<?php
ini_set('display_errors', 1); // Muestra errores en el navegador
ini_set('display_startup_errors', 1); // Muestra errores de inicio
error_reporting(E_ALL); // Reporta todos los tipos de errores

session_start();
header('Content-Type: application/json');



require_once 'conexion.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CAMBIO: Usar los nombres de los campos HTML en minúscula para $_POST
    $nombreActividad_post = trim($_POST['nombreActividad'] ?? '');
    $descripcion_post = trim($_POST['descripcion'] ?? '');
    $idAsignatura_post = filter_var($_POST['idAsignatura'] ?? '', FILTER_VALIDATE_INT);
    $fecha_post = trim($_POST['fecha'] ?? '');

    // Manejar el separador decimal para el porcentaje
    $porcentaje_raw = trim($_POST['porcentaje'] ?? '');
    $porcentaje_cleaned = str_replace(',', '.', $porcentaje_raw);
    $porcentaje_post = filter_var($porcentaje_cleaned, FILTER_VALIDATE_FLOAT);

    // CAMBIO: Usar las variables locales correctas en la validación
    if (empty($nombreActividad_post) || empty($descripcion_post) || $idAsignatura_post === false || empty($fecha_post) || $porcentaje_post === false || $porcentaje_post < 0 || $porcentaje_post > 100) {
        $response['message'] = 'Por favor, complete todos los campos correctamente y asegúrese que el porcentaje esté entre 0 y 100.';
        echo json_encode($response);
        exit();
    }

    try {
        // CAMBIO: Usar 'actividades' en la consulta INSERT
        $query = "INSERT INTO actividades (IDAsignatura, NombreActividad, Descripcion, Fecha, Porcentaje) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($query);

        if ($stmt === false) {
            throw new Exception("Error al preparar la consulta: " . $conexion->error);
        }

        $stmt->bind_param("isssd", $idAsignatura_post, $nombreActividad_post, $descripcion_post, $fecha_post, $porcentaje_post);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Actividad guardada exitosamente.';
        } else {
            $response['message'] = 'Error al guardar la actividad en la base de datos: ' . $stmt->error;
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