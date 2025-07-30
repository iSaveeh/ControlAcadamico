<?php
session_start();
require_once 'conexion.php'; // Debe definir $conexion como MySQLi

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Normalizar entradas
    $usuario = strtolower(trim($_POST['usuario'] ?? ''));
    $contrasena = strtolower(trim($_POST['contrasena'] ?? ''));
    $rol = strtolower(trim($_POST['rol'] ?? ''));

    if (empty($usuario) || empty($contrasena) || empty($rol)) {
        echo "error: campos vacíos";
        exit;
    }

    // 1. Verificar credenciales en tabla 'usuarios'
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ? AND contrasena = ? AND rol = ?");
    $stmt->bind_param("sss", $usuario, $contrasena, $rol);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
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
                echo "error: rol no válido";
                exit;
        }

        $stmt2 = $conexion->prepare($query);
        $stmt2->bind_param("s", $usuario);
        $stmt2->execute();
        $resultado2 = $stmt2->get_result();
        $datos = $resultado2->fetch_assoc();

        if (!$datos) {
            echo "error: usuario no encontrado en tabla correspondiente al rol.";
            exit;
        }

        // 4. Guardar ID y nombre completo en sesión
        $_SESSION['id'] = $datos['id'];
        $_SESSION['nombre_completo'] = $datos['Nombre'] . ' ' . $datos['Apellido'];
        $_SESSION['datos'] = $datos;

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

        // 5. Redirigir al menú principal
        header("Location: ../screens/menu.php");
        exit;

    } else {
        // Credenciales incorrectas
        $_SESSION['login_error'] = "Usuario, contraseña o rol incorrecto";
        header("Location: ../screens/login.php");
        exit;
    }
}
?>
