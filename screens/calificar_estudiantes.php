
<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'profe') {
    header('Location: login.php');
    exit;
}
require_once '../backend/DatabaseHandler.php';

// Obtener el id del profesor desde la sesión
$id_profesor = $_SESSION['id_usuario'];

// Obtener estudiantes asignados al profesor (ajusta la consulta según tu modelo)
$estudiantes = buscarEstudiantesPorProfesor($id_profesor); // Debes crear esta función en tu backend

// Procesar calificación enviada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_estudiante'], $_POST['calificacion'])) {
    $id_estudiante = $_POST['id_estudiante'];
    $calificacion = $_POST['calificacion'];
    // Guardar o actualizar calificación (debes crear esta función)
    guardarCalificacionPorIndice($id_estudiante, $id_profesor, $calificacion);
    header("Location: calificar_estudiantes.php?ok=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calificar Estudiantes</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #4A90E2; color: #fff; }
        input[type="number"] { width: 60px; }
        .btn-calificar { background: #4A90E2; color: #fff; border: none; border-radius: 6px; padding: 5px 12px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="contenedor-login">
        <div class="login-box">
            <h2>Calificar Estudiantes</h2>
            <?php if (isset($_GET['ok'])): ?>
                <div style="color:green;">¡Calificación guardada!</div>
            <?php endif; ?>
            <table>
                <tr>
                    <th>Estudiante</th>
                    <th>Calificación</th>
                    <th>Acción</th>
                </tr>
                <?php foreach ($estudiantes as $est): ?>
                <tr>
                    <form method="post">
                        <td><?= htmlspecialchars($est['nombre']) ?></td>
                        <td>
                            <input type="number" name="calificacion" min="0" max="100" value="<?= htmlspecialchars($est['calificacion'] ?? '') ?>" required>
                            <input type="hidden" name="id_estudiante" value="<?= $est['id'] ?>">
                        </td>
                        <td>
                            <button type="submit" class="btn-calificar">Guardar</button>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>