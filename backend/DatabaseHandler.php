<?php
// ==== DATOS DE LA BASE DE DATOS ====
// Aquí van los datos para conectarse a la base de datos
$DbName = 'focusgrade';
$DbUser = 'root';
$DbPassword = '';
$DbHost = 'localhost';
// Este array es para saber el nombre de cada tabla según su posición
$tables = array('usuarios','salon','profesor','observador', 'matricula','horario', 'estudiante', 'calificaciones', 'boletin','asignaturas','administrador','acudiente', 'actividades');

// ==== FUNCIONES DE CONEXIÓN ====
// Esta función conecta a la base de datos y devuelve la conexión
function getConnection() {
    global $DbHost, $DbUser, $DbPassword, $DbName;
    $conn = new mysqli($DbHost, $DbUser, $DbPassword, $DbName);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
// Esta función cierra la conexión (para no dejar conexiones abiertas)
function closeConnection($conn) {
    $conn->close();
}

// ==== FUNCIONES CRUD GENÉRICAS ====
// Buscar registros en una tabla usando el índice del array $tables
function buscarEnTablaPorIndice($indiceTabla, $campo, $valor) {
    global $tables;
    // Si el índice no existe, no hacemos nada
    if (!isset($tables[$indiceTabla])) {
        return false;
    }
    $tabla = $tables[$indiceTabla];
    $conn = getConnection();
    // Preparamos la consulta para evitar SQL injection
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
    closeConnection($conn);
    return $datos;
}

// Insertar un registro en una tabla usando el índice
function insertarEnTablaPorIndice($indiceTabla, $datos) {
    global $tables;
    if (!isset($tables[$indiceTabla])) {
        return false;
    }
    $tabla = $tables[$indiceTabla];
    $conn = getConnection();
    // Armamos la consulta con los campos y valores
    $campos = implode('`, `', array_keys($datos));
    $valores = implode("', '", array_values($datos));
    $sql = "INSERT INTO `$tabla` (`$campos`) VALUES ('$valores')";
    // Ejecutamos la consulta
    if ($conn->query($sql) === TRUE) {
        closeConnection($conn);
        return true;
    } else {
        closeConnection($conn);
        return false;
    }
}

// Eliminar un registro de una tabla usando el índice
function eliminarEnTablaPorIndice($indiceTabla, $campo, $valor) {
    global $tables;
    if (!isset($tables[$indiceTabla])) {
        return false;
    }
    $tabla = $tables[$indiceTabla];
    $conn = getConnection();
    $stmt = $conn->prepare("DELETE FROM `$tabla` WHERE `$campo` = ?");
    if (!$stmt) {
        closeConnection($conn);
        return false;
    }
    $stmt->bind_param('s', $valor);
    $resultado = $stmt->execute();
    $stmt->close();
    closeConnection($conn);
    return $resultado;
}

// Actualizar un registro en una tabla usando el índice
function actualizarEnTablaPorIndice($indiceTabla, $datos, $campoCondicion, $valor) {
    global $tables;
    if (!isset($tables[$indiceTabla])) {
        return false;
    }
    $tabla = $tables[$indiceTabla];
    $conn = getConnection();
    // Armamos la parte del SET con los campos a actualizar
    $setPart = '';
    foreach ($datos as $campo => $valorCampo) {
        $setPart .= "`$campo` = '$valorCampo', ";
    }
    $setPart = rtrim($setPart, ', ');
    $sql = "UPDATE `$tabla` SET $setPart WHERE `$campoCondicion` = '$valor'";
    if ($conn->query($sql) === TRUE) {
        closeConnection($conn);
        return true;
    } else {
        closeConnection($conn);
        return false;
    }
}

// ==== INICIO DE SESIÓN ====
// Arrancamos la sesión para poder usar $_SESSION
session_start();
// (OJO: este require_once puede causar recursión si no lo controlas, pero lo dejo como lo tenías)
// require_once '../backend/DatabaseHandler.php';

// Si recibimos datos por POST, es porque alguien intentó iniciar sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario'], $_POST['contrasena'], $_POST['rol'])) {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];
    $rol = $_POST['rol'];

    // Buscamos el usuario en la tabla usuarios (índice 0)
    $resultados = buscarEnTablaPorIndice(0, 'usuario', $usuario);
    if ($resultados && count($resultados) > 0) {
        $user = $resultados[0];
        // Verificamos la contraseña y el rol
        if (password_verify($contrasena, $user['contrasena']) && $user['rol'] === $rol) {
            // Guardamos los datos en la sesión para saber quién está logueado
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['id_usuario'] = $user['id'];
            // Aquí podrías redirigir según el rol, pero lo dejo para que lo pongas donde quieras
            exit;
        }
    }
    // Si algo falla, volvemos al login con error
    header('Location: ../screens/login.php?error=credenciales');
    exit;
}

// ==== FUNCIONES PARA EL MÓDULO DE CALIFICACIONES ====
// Buscar todos los estudiantes de un profesor (usando la función genérica)
function buscarEstudiantesPorProfesor($id_profesor) {
    // 6 es el índice de 'estudiante' en $tables
    return buscarEnTablaPorIndice(6, 'id_profesor', $id_profesor);
}

// Guardar o actualizar la calificación de un estudiante
function guardarCalificacionPorIndice($id_estudiante, $id_profesor, $calificacion) {
    // 7 es el índice de 'calificaciones' en $tables
    // Buscamos si ya existe una calificación para ese estudiante y profesor
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id FROM calificaciones WHERE id_estudiante = ? AND id_profesor = ?");
    $stmt->bind_param('ii', $id_estudiante, $id_profesor);
    $stmt->execute();
    $stmt->store_result();
    $datos = [
        'id_estudiante' => $id_estudiante,
        'id_profesor' => $id_profesor,
        'calificacion' => $calificacion
    ];
    if ($stmt->num_rows > 0) {
        // Si ya existe, actualizamos solo la calificación
        $stmt->close();
        // OJO: aquí solo actualiza por id_estudiante, si tienes varios profes por estudiante, deberías actualizar por ambos campos
        actualizarEnTablaPorIndice(7, ['calificacion' => $calificacion], 'id_estudiante', $id_estudiante);
    } else {
        // Si no existe, insertamos la calificación
        $stmt->close();
        insertarEnTablaPorIndice(7, $datos);
    }
    closeConnection($conn);
}
?>