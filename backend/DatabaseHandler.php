<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = strtolower(trim($_POST['usuario'] ?? ''));
    $contrasena = strtolower(trim($_POST['contrasena'] ?? ''));
    $rol = strtolower(trim($_POST['rol'] ?? ''));

    if (empty($usuario) || empty($contrasena) || empty($rol)) {
        echo "error: campos vacíos";
        exit;
    }

    // Verificar en tabla 'usuarios'
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = :usuario AND contrasena = :contrasena AND rol = :rol");
    $stmt->execute([
        ':usuario' => $usuario,
        ':contrasena' => $contrasena,
        ':rol' => $rol
    ]);
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($resultado) === 1) {
        $_SESSION['usuario'] = $usuario;
        $_SESSION['rol'] = $rol;

        // Obtener información adicional según el rol
        switch ($rol) {
            case 'profesor':
                $query = "SELECT IDProfesor, Nombre, Apellido FROM profesor WHERE Usuario = :usuario";
                break;
            case 'estudiante':
                $query = "SELECT IDEstudiante, Nombre, Apellido FROM estudiante WHERE Usuario = :usuario";
                break;
            case 'admin':
                $query = "SELECT IDAdministrador, Nombre, Apellido FROM administrador WHERE Usuario = :usuario";
                break;
            case 'acudiente':
                $query = "SELECT IDAcudiente, Nombre, Apellido FROM acudiente WHERE Usuario = :usuario";
                break;
            default:
                echo "error: rol no válido";
                exit;
        }

        $stmt = $conn->prepare($query);
        $stmt->execute([':usuario' => $usuario]);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        $_SESSION['datos'] = $datos;

        // Redirigir a su respectiva pantalla
        header("Location: ../screens/menu.php");
        exit;
        } else {
        $_SESSION['login_error'] = "Usuario, contraseña o rol incorrecto";
        header("Location: ../screens/login.php"); // Cambia según tu ruta al formulario de login
        exit;
    }
}
?>
