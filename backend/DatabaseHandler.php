<?php
session_start();
// Habilitar errores para depuración si no lo hiciste en php.ini
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php'; // Debe definir $conexion como MySQLi

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Normalizar entradas
    $usuario = strtolower(trim($_POST['usuario'] ?? ''));
    $contrasena = strtolower(trim($_POST['contrasena'] ?? ''));
    $rol = strtolower(trim($_POST['rol'] ?? ''));

    // --- Depuración ---
    error_log("Intento de login: Usuario=" . $usuario . ", Rol=" . $rol . ", Contraseña=" . $contrasena);
    // Para ver la contraseña REAL que se envía, puedes descomentar la línea de abajo, PERO QUÍTALA EN PRODUCCIÓN
    // error_log("Contraseña enviada: " . $_POST['contrasena']);


    if (empty($usuario) || empty($contrasena) || empty($rol)) {
        $_SESSION['login_error'] = "Todos los campos son obligatorios.";
        header("Location: ../screens/login.php");
        exit;
    }

    // 1. Verificar credenciales en tabla 'usuarios'
    // --- Depuración: Comprobar si la conexión está OK ---
    if ($conexion->connect_error) {
        error_log("Error de conexión a la base de datos: " . $conexion->connect_error);
        $_SESSION['login_error'] = "Error interno del servidor (conexión DB).";
        header("Location: ../screens/login.php");
        exit;
    }

    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ? AND contrasena = ? AND rol = ?");
    if ($stmt === false) {
        error_log("Error al preparar la consulta de usuarios: " . $conexion->error);
        $_SESSION['login_error'] = "Error interno del servidor (consulta usuarios).";
        header("Location: ../screens/login.php");
        exit;
    }

    $stmt->bind_param("sss", $usuario, $contrasena, $rol);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        error_log("Credenciales de usuario VÁLIDAS en tabla 'usuarios'. Rol: " . $rol);
        // 2. Guardar info básica
        $_SESSION['usuario'] = $usuario;
        $_SESSION['rol'] = $rol;

        // 3. Obtener el ID y nombre del usuario según su rol
        switch ($rol) {
            case 'profesor':
                $query = "SELECT IDProfesor AS id, Nombre, Apellido FROM profesor WHERE Usuario = ?";
                break;
            case 'estudiante':
                $query = "SELECT IDEstudiante AS id, Nombre, Apellido FROM estudiante WHERE Usuario = ?";
                break;
            case 'admin':
                $query = "SELECT IDAdministrador AS id, Nombre, Apellido FROM administrador WHERE Usuario = ?";
                break;
            case 'acudiente':
                $query = "SELECT IDAcudiente AS id, Nombre, Apellido FROM acudiente WHERE Usuario = ?";
                break;
            default:
                $_SESSION['login_error'] = "Rol no válido.";
                header("Location: ../screens/login.php");
                exit;
        }

        $stmt2 = $conexion->prepare($query);
        if ($stmt2 === false) {
            error_log("Error al preparar la consulta de rol específico: " . $conexion->error);
            $_SESSION['login_error'] = "Error interno del servidor (consulta rol).";
            header("Location: ../screens/login.php");
            exit;
        }

        $stmt2->bind_param("s", $usuario);
        $stmt2->execute();
        $resultado2 = $stmt2->get_result();
        $datos = $resultado2->fetch_assoc();

        if (!$datos) {
            error_log("Usuario '" . $usuario . "' no encontrado en tabla de rol '" . $rol . "'");
            $_SESSION['login_error'] = "Error: Usuario no encontrado para el rol seleccionado.";
            header("Location: ../screens/login.php");
            exit;
        }

        error_log("Datos específicos del rol obtenidos: ID=" . $datos['id'] . ", Nombre=" . $datos['Nombre'] . ", Apellido=" . $datos['Apellido']);

        // 4. Guardar ID y nombre completo en sesión
        $_SESSION['id'] = $datos['id']; // ID numérico principal
        $_SESSION['usuario_id'] = $datos['id']; // Alias para compatibilidad con screens/menu.php y mod/pizarron_de_tareas.php
        $_SESSION['nombre_completo'] = $datos['Nombre'] . ' ' . $datos['Apellido'];
        $_SESSION['datos'] = $datos; // Array con id, Nombre, Apellido

        // Guardar el ID específico según el rol para compatibilidad con módulos
        switch ($rol) {
            case 'profesor':
                $_SESSION['idprofesor'] = $datos['id'];
                break;
            case 'estudiante':
                $_SESSION['idestudiante'] = $datos['id'];
                break;
            case 'admin':
                $_SESSION['idadministrador'] = $datos['id'];
                break;
            case 'acudiente':
                $_SESSION['idacudiente'] = $datos['id'];
                break;
        }

        error_log("Sesión establecida correctamente. Redireccionando a screens/menu.php");
        // 5. Redirigir al menú principal
        header("Location: ../screens/menu.php");
        exit;

    } else {
        // Credenciales incorrectas en la tabla 'usuarios'
        error_log("Credenciales INCORRECTAS para usuario: " . $usuario . ", Rol: " . $rol);
        $_SESSION['login_error'] = "Usuario, contraseña o rol incorrecto.";
        header("Location: ../screens/login.php");
        exit;
    }
} else {
    // Si alguien intenta acceder directamente a DatabaseHandler.php sin POST
    $_SESSION['login_error'] = "Acceso no autorizado.";
    header("Location: ../screens/login.php");
    exit;
}
?>