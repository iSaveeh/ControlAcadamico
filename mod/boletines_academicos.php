<?php
// filepath: modular-menu-php/mod/boletines_academicos.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: ../screens/login.php');
    exit();
}

// Conexión a la base de datos
require_once '../backend/conexion.php';

// Obtener el ID del estudiante (puede venir por GET o por sesión)
$id_estudiante = $_GET['idestudiante'] ?? $_SESSION['idestudiante'] ?? null;

if (!$id_estudiante) {
    echo "<p>No se ha especificado el estudiante.</p>";
    exit();
}

// Consulta para obtener los boletines del estudiante
$sql = "SELECT IDPeriodo, IDAsignatura, IDEstudiante, IDAdministrador, PromedioCalificaciones, PromedioAcumulado 
        FROM boletin 
        WHERE IDEstudiante = ?";
$stmt = $conexion->prepare($sql); // Cambiado $conn por $conexion
$stmt->bind_param("s", $id_estudiante);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletines Académicos</title>
    <link rel="stylesheet" href="../css/general.css">
    <link rel="stylesheet" href="../css/menu.css">
    <style>
        .tabla-boletin {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
            background: #fff;
        }
        .tabla-boletin th, .tabla-boletin td {
            border: 1px solid #e2e6ea;
            padding: 10px 8px;
            text-align: center;
        }
        .tabla-boletin th {
            background: #f4f6fa;
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <h1>Boletines Académicos</h1>
        <table class="tabla-boletin">
            <thead>
                <tr>
                    <th>IDPeriodo</th>
                    <th>IDAsignatura</th>
                    <th>IDEstudiante</th>
                    <th>IDAdministrador</th>
                    <th>PromedioCalificaciones</th>
                    <th>PromedioAcumulado</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['IDPeriodo']) ?></td>
                            <td><?= htmlspecialchars($row['IDAsignatura']) ?></td>
                            <td><?= htmlspecialchars($row['IDEstudiante']) ?></td>
                            <td><?= htmlspecialchars($row['IDAdministrador']) ?></td>
                            <td><?= htmlspecialchars($row['PromedioCalificaciones']) ?></td>
                            <td><?= htmlspecialchars($row['PromedioAcumulado']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No hay boletines registrados para este estudiante.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>