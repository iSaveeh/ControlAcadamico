<?php
session_start();
include 'conexion.php'; 

header('Content-Type: application/json');

$accion = $_GET['accion'] ?? null;
$rol = $_SESSION['rol'] ?? '';
$idProfesor = $_SESSION['datos']['IDProfesor'] ?? 0;

if ($rol !== 'profesor') {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado.']);
    exit;
}

switch ($accion) {
    case 'get_materias_profesor':
        $sql = $conexion->prepare("SELECT IDAsignatura, NombreAsignatura FROM asignaturas WHERE IDProfesor = ?");
        $sql->bind_param("i", $idProfesor);
        $sql->execute();
        $resultado = $sql->get_result();
        $materias = $resultado->fetch_all(MYSQLI_ASSOC);
        echo json_encode($materias);
        break;

    case 'get_grados_profesor':
        $sql = $conexion->prepare("SELECT IDGrado, NombreGrado FROM grados");
        $sql->execute();
        $resultado = $sql->get_result();
        $grados = $resultado->fetch_all(MYSQLI_ASSOC);
        echo json_encode($grados);
        break;

    case 'get_alumnos_por_grado':
        $idGrado = $_GET['idGrado'] ?? 0;
        if (empty($idGrado)) {
            echo json_encode([]);
            exit;
        }
        $sql = $conexion->prepare("SELECT IDEstudiante, Nombre, Apellido FROM estudiante WHERE IDGrado = ? ORDER BY Apellido, Nombre");
        $sql->bind_param("s", $idGrado);
        $sql->execute();
        $resultado = $sql->get_result();
        $alumnos = $resultado->fetch_all(MYSQLI_ASSOC);
        echo json_encode($alumnos);
        break;

    case 'guardar_asistencia':
        $data = json_decode(file_get_contents('php://input'), true);
        $asistencias = $data['asistencias'] ?? [];
        $idMateria = $data['idMateria'] ?? 0;
        $idGrado = $data['idGrado'] ?? 0;
        $fecha = date('Y-m-d');

        if (empty($asistencias) || empty($idMateria) || empty($idGrado)) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos para guardar la asistencia.']);
            exit;
        }
        $sql = $conexion->prepare("
            INSERT INTO asistencia (IDEstudiante_as, IDAsignatura_as, IDGrado_as, FechaAsistencia, Estado) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE Estado = VALUES(Estado)
        ");
        $conexion->begin_transaction();
        $exito = true;
        foreach ($asistencias as $asistencia) {
            $sql->bind_param("sssss", $asistencia['idEstudiante'], $idMateria, $idGrado, $fecha, $asistencia['estado']);
            if (!$sql->execute()) {
                $exito = false;
                break;
            }
        }
        if ($exito) {
            $conexion->commit();
            echo json_encode(['status' => 'success', 'message' => 'Asistencia guardada correctamente.']);
        } else {
            $conexion->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Hubo un error al guardar la asistencia.']);
        }
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}

$conexion->close();
?>