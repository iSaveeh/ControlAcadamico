<?php
// Configuración de la base de datos
$DbName = 'ControlAcadamico';
$DbUser = 'root';
$DbPassword = '';
$DbHost = 'localhost';
$tables = array('usuarios','salon','profesor','observador', 'matricula','horario', 'estudiante', 'calificaciones', 'boletin','asignaturas','administrador','acudiente', 'actividades');

// Función para obtener la conexión
function getConnection() {
    global $DbHost, $DbUser, $DbPassword, $DbName;
    $conn = new mysqli($DbHost, $DbUser, $DbPassword, $DbName);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
// Función para cerrar la conexión
function closeConnection($conn) {
    $conn->close();
}

// Función para buscar registros en una tabla usando el índice del array $tables
function buscarEnTablaPorIndice($indiceTabla, $campo, $valor) {
    global $tables;
    if (!isset($tables[$indiceTabla])) {
        return false; // Índice de tabla no permitido
    }
    $tabla = $tables[$indiceTabla];
    $conn = getConnection();
    // Usar consultas preparadas para evitar inyección SQL
    $stmt = $conn->prepare("SELECT * FROM `$tabla` WHERE `$campo` = ?"); 
    if (!$stmt) {
        closeConnection($conn);
        return false;
    }
    $stmt->bind_param('s', $valor);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $datos = $resultado->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    closeConnection($conn); // Cerrar la conexion
    return $datos;
}

// funcion para insertar un registro en una tabla usando el índice del array $tables
function insertarEnTablaPorIndice($indiceTabla, $datos) {
    global $tables;
    if (!isset($tables[$indiceTabla])) {
        return false; // Índice de tabla no permitido
    }
    $tabla = $tables[$indiceTabla];
    $conn = getConnection();
    
    // Construir la consulta SQL dinámicamente
    $campos = implode('`, `', array_keys($datos));
    $valores = implode("', '", array_values($datos));
    
    $sql = "INSERT INTO `$tabla` (`$campos`) VALUES ('$valores')";
    
    if ($conn->query($sql) === TRUE) {
        closeConnection($conn);
        return true;
    } else {
        closeConnection($conn);
        return false;
    }
}

// Funcion para eliminar un registro en una tabla usando el índice del array $tables
function eliminarEnTablaPorIndice($indiceTabla, $campo, $valor) {
    global $tables;
    if (!isset($tables[$indiceTabla])) {
        return false; // Índice de tabla no permitido
    }
    $tabla = $tables[$indiceTabla];
    $conn = getConnection();
    
    // Usar consultas preparadas para evitar inyección SQL
    $stmt = $conn->prepare("DELETE FROM `$tabla` WHERE `$campo` = ?");
    if (!$stmt) {
        closeConnection($conn);
        return false;
    }
    $stmt->bind_param('s', $valor);
    $resultado = $stmt->execute();
    $stmt->close();
    closeConnection($conn); // Cerrar la conexion
    return $resultado;
}

// Función para actualizar un registro en una tabla usando el índice del array $tables
function actualizarEnTablaPorIndice($indiceTabla, $datos, $campoCondicion, $valor) {
    global $tables;
    if (!isset($tables[$indiceTabla])) {
        return false; // Índice de tabla no permitido
    }
    $tabla = $tables[$indiceTabla];
    $conn = getConnection();
    
    // Construir la consulta SQL dinámicamente
    $setPart = '';
    foreach ($datos as $campo => $valor) {
        $setPart .= "`$campo` = '$valor', ";
    }
    $setPart = rtrim($setPart, ', '); // Eliminar la última coma
    
    $sql = "UPDATE `$tabla` SET $setPart WHERE `$campoCondicion` = '$valor'";
    
    if ($conn->query($sql) === TRUE) {
        closeConnection($conn);
        return true;
    } else {
        closeConnection($conn);
        return false;
    }
}


?>