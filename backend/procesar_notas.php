<?php
header('Content-Type: application/json');
include 'conexion.php';

// Verificar que la conexión existe
if (!$conexion) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

// Verificar que se recibió una acción
if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Acción no especificada']);
    exit;
}

$action = $_POST['action'];

try {
    switch ($action) {
        case 'editar_nota':
            editarNota();
            break;
            
        case 'eliminar_nota':
            eliminarNota();
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

function editarNota() {
    global $conexion;
    
    // Validar datos requeridos
    if (!isset($_POST['IDNota']) || !isset($_POST['NotaFinal'])) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        return;
    }
    
    $idNota = intval($_POST['IDNota']);
    $notaFinal = floatval($_POST['NotaFinal']);
    $observaciones = $_POST['Observaciones'] ?? '';
    
    // Validar rango de la nota
    if ($notaFinal < 0 || $notaFinal > 5) {
        echo json_encode(['success' => false, 'message' => 'La nota debe estar entre 0 y 5']);
        return;
    }
    
    // Verificar que la nota existe
    $verificar = $conexion->prepare("SELECT IDNota FROM notas WHERE IDNota = ?");
    $verificar->bind_param("i", $idNota);
    $verificar->execute();
    $resultado = $verificar->get_result();
    
    if ($resultado->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'La nota no existe']);
        return;
    }
    
    // Actualizar la nota
    $stmt = $conexion->prepare("
        UPDATE notas 
        SET NotaFinal = ?, 
            Observaciones = ?, 
            FechaModificacion = NOW() 
        WHERE IDNota = ?
    ");
    
    $stmt->bind_param("dsi", $notaFinal, $observaciones, $idNota);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Nota actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se realizaron cambios']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la nota: ' . $conexion->error]);
    }
    
    $stmt->close();
}

function eliminarNota() {
    global $conexion;
    
    // Validar datos requeridos
    if (!isset($_POST['IDNota'])) {
        echo json_encode(['success' => false, 'message' => 'ID de nota no especificado']);
        return;
    }
    
    $idNota = intval($_POST['IDNota']);
    
    // Verificar que la nota existe
    $verificar = $conexion->prepare("SELECT IDNota FROM notas WHERE IDNota = ?");
    $verificar->bind_param("i", $idNota);
    $verificar->execute();
    $resultado = $verificar->get_result();
    
    if ($resultado->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'La nota no existe']);
        return;
    }
    
    // Eliminar la nota
    $stmt = $conexion->prepare("DELETE FROM notas WHERE IDNota = ?");
    $stmt->bind_param("i", $idNota);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Nota eliminada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la nota']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la nota: ' . $conexion->error]);
    }
    
    $stmt->close();
}

// Cerrar conexión
$conexion->close();
?>