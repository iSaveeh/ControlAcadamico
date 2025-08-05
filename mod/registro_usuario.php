<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../backend/conexion.php';

// Función para generar el usuario/ID
function generarID($rol, $nombre, $apellido) {
    $rolPart = strtolower(substr($rol, 0, 2));
    $nomPart = strtolower(substr($nombre, 0, 2));
    $apePart = strtolower(substr($apellido, 0, 2));
    $num = rand(100, 999);
    return $rolPart . $nomPart . $apePart . $num;
}

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $rol = $_POST['rol'];
    $edad = intval($_POST['edad']);
    $usuario = generarID($rol, $nombre, $apellido);
    $contrasena = $usuario; // Puedes cambiar esto si quieres

    // Insertar en la tabla usuarios
    $stmt = $conexion->prepare("INSERT INTO usuarios (usuario, contrasena, rol) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $usuario, $contrasena, $rol);
    if ($stmt->execute()) {
        // Según el rol, insertar en la tabla correspondiente
        if ($rol === 'estudiante') {
            $idgrado = $_POST['grado'] ?? '';
            $stmt2 = $conexion->prepare("INSERT INTO estudiante (Usuario, IDEstudiante, Nombre, Apellido, IDGrado) VALUES (?, ?, ?, ?, ?)");
            $stmt2->bind_param("sssss", $usuario, $usuario, $nombre, $apellido, $idgrado);
            $stmt2->execute();
        } elseif ($rol === 'profesor') {
            $especialidad = $_POST['especialidad'] ?? '';
            $stmt2 = $conexion->prepare("INSERT INTO profesor (Usuario, IDProfesor, Nombre, Apellido, Grado) VALUES (?, ?, ?, ?, ?)");
            $stmt2->bind_param("sssss", $usuario, $usuario, $nombre, $apellido, $especialidad);
            $stmt2->execute();
        } elseif ($rol === 'administrador') {
            $area = $_POST['area'] ?? '';
            $stmt2 = $conexion->prepare("INSERT INTO administrador (IDAdministrador, Usuario, Nombre, Apellido, Cargo) VALUES (?, ?, ?, ?, ?)");
            $stmt2->bind_param("sssss", $usuario, $usuario, $nombre, $apellido, $area);
            $stmt2->execute();
        } elseif ($rol === 'acudiente') {
            $idestudiante = $_POST['idestudiante'] ?? '';
            $stmt2 = $conexion->prepare("INSERT INTO acudiente (Usuario, IDAcudiente, Nombre, Apellido, IDEstudiante) VALUES (?, ?, ?, ?, ?)");
            $stmt2->bind_param("sssss", $usuario, $usuario, $nombre, $apellido, $idestudiante);
            $stmt2->execute();
        }
        $mensaje = "Usuario registrado correctamente. Usuario: $usuario, Contraseña: $contrasena";
    } else {
        $mensaje = "Error al registrar usuario: " . $conexion->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="../css/general.css">
    <link rel="stylesheet" href="../css_modulos/registro_usuario.css">
    <style>
        form { max-width: 400px; margin: 30px auto; background: #fff; padding: 20px; border-radius: 8px; }
        label { display: block; margin-top: 10px; }
        input, select { width: 100%; padding: 6px; margin-top: 4px; }
        .extra { display: none; }
        .mensaje { color: green; margin-bottom: 10px; }
    </style>
    <script>
        function mostrarCamposExtra() {
            var rol = document.getElementById('rol').value;
            document.getElementById('campos-estudiante').style.display = rol === 'estudiante' ? 'block' : 'none';
            document.getElementById('campos-profesor').style.display = rol === 'profesor' ? 'block' : 'none';
            document.getElementById('campos-administrador').style.display = rol === 'administrador' ? 'block' : 'none';
            document.getElementById('campos-acudiente').style.display = rol === 'acudiente' ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <form method="post">
        <h2>Registro de Usuario</h2>
        <?php if ($mensaje): ?>
            <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <label>Nombre:
            <input type="text" name="nombre" required>
        </label>
        <label>Apellido:
            <input type="text" name="apellido" required>
        </label>
        <label>Rol:
            <select name="rol" id="rol" onchange="mostrarCamposExtra()" required>
                <option value="">Seleccione...</option>
                <option value="estudiante">Estudiante</option>
                <option value="profesor">Profesor</option>
                <option value="administrador">Administrador</option>
                <option value="acudiente">Acudiente</option>
            </select>
        </label>
        <label>Edad:
            <input type="number" name="edad" min="1" required>
        </label>
        <div id="campos-estudiante" class="extra">
            <label>Grado:
                <input type="text" name="grado">
            </label>
        </div>
        <div id="campos-profesor" class="extra">
            <label>Especialidad:
                <input type="text" name="especialidad">
            </label>
        </div>
        <div id="campos-administrador" class="extra">
            <label>Área:
                <input type="text" name="area">
            </label>
        </div>
        <div id="campos-acudiente" class="extra">
            <label>ID Estudiante a cargo:
                <input type="text" name="idestudiante">
            </label>
        </div><br>
        <button type="submit">Registrar</button>
    </form>
    <script>mostrarCamposExtra();</script>
</body>
</html>