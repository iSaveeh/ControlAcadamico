<?php
session_start();
require_once 'conexion.php'; // Debe definir $conexion como MySQLi

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Normalizar entradas
    $usuario = strtolower(trim($_POST['usuario'] ?? ''));
    $contrasena = strtolower(trim($_POST['contrasena'] ?? ''));
    $rol = strtolower(trim($_POST['rol'] ?? ''));

    if (empty($usuario) || empty($contrasena) || empty($rol)) {
        $_SESSION['login_error'] = "Todos los campos son obligatorios.";
        header("Location: ../screens/login.php");
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

        // 3. Obtener TODOS los datos del usuario según su rol
        $tabla = '';
        switch ($rol) {
            case 'profesor':
                $tabla = 'profesor';
                break;
            case 'estudiante':
                $tabla = 'estudiante';
                break;
            case 'admin':
                $tabla = 'administrador';
                break;
            case 'acudiente':
                $tabla = 'acudiente';
                break;
            default:
                $_SESSION['login_error'] = "Rol no válido.";
                header("Location: ../screens/login.php");
                exit;
        }

        // ===== CÓDIGO CORREGIDO AQUÍ =====
        // Usamos SELECT * para traer todos los datos del usuario, incluyendo el ID con su nombre original
        $query = "SELECT * FROM $tabla WHERE Usuario = ?";
        // ===================================
        
        $stmt2 = $conexion->prepare($query);
        $stmt2->bind_param("s", $usuario);
        $stmt2->execute();
        $resultado2 = $stmt2->get_result();
        $datos = $resultado2->fetch_assoc();

        if (!$datos) {
            $_SESSION['login_error'] = "El usuario no existe en la tabla de su rol.";
            header("Location: ../screens/login.php");
            exit;
        }

        // 4. Guardar todos los datos del usuario en la sesión
        $_SESSION['datos'] = $datos;

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