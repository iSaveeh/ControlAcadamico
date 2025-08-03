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

// Verificar si el usuario es administrador
$es_admin = false;
$stmt_rol = $conexion->prepare("SELECT rol FROM usuarios WHERE usuario = ?");
$stmt_rol->bind_param("s", $_SESSION['usuario']);
$stmt_rol->execute();
$stmt_rol->bind_result($rol_usuario);
$stmt_rol->fetch();
$stmt_rol->close();
if ($rol_usuario === 'administrador') {
    $es_admin = true;
}

if ($es_admin) {
    // Consulta para obtener todos los estudiantes con grado y salón
    $sql = "SELECT e.IDEstudiante, e.Nombre, e.Apellido, g.NombreGrado, s.NombreSalon
            FROM estudiante e
            LEFT JOIN grados g ON e.IDGrado = g.IDGrado
            LEFT JOIN salon s ON e.IDSalon = s.IDSalon
            ORDER BY g.NombreGrado, s.NombreSalon, e.Apellido, e.Nombre";
    $result = $conexion->query($sql);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Listado de Estudiantes</title>
        <link rel="stylesheet" href="../css/general.css">
        <link rel="stylesheet" href="../css/menu.css">
        <style>
            .tabla-estudiantes {
                width: 100%;
                border-collapse: collapse;
                margin-top: 24px;
                background: #fff;
            }
            .tabla-estudiantes th, .tabla-estudiantes td {
                border: 1px solid #e2e6ea;
                padding: 10px 8px;
                text-align: center;
            }
            .tabla-estudiantes th {
                background: #f4f6fa;
            }
        </style>
    </head>
    <body>
        <div class="contenedor">
            <h1>Listado de Estudiantes por Grado y Salón</h1>
            <table class="tabla-estudiantes">
                <thead>
                    <tr>
                        <!-- <th>ID Estudiante</th> --> <!-- Eliminado -->
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Grado</th>
                        <th>Salón</th>
                        <th>Boletín</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <!-- <td><?= htmlspecialchars($row['IDEstudiante']) ?></td> --> <!-- Eliminado -->
                                <td><?= htmlspecialchars($row['Nombre']) ?></td>
                                <td><?= htmlspecialchars($row['Apellido']) ?></td>
                                <td><?= htmlspecialchars($row['NombreGrado']) ?></td>
                                <td><?= htmlspecialchars($row['NombreSalon']) ?></td>
                                <td>
                                    <form method="get" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="margin:0;">
                                        <input type="hidden" name="idestudiante" value="<?= htmlspecialchars($row['IDEstudiante']) ?>">
                                        <button type="submit" style="padding:6px 14px; background:#2575fc; color:#fff; border:none; border-radius:5px; cursor:pointer;">
                                            Ver Boletín
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No hay estudiantes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </body>
    </html>
    <?php
    exit();
}

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
        
        <?php if ($es_admin): ?>
            <a href="boletines_academicos.php" style="margin-bottom:15px;display:inline-block;">← Volver al listado de estudiantes</a>
        <?php endif; ?>
        
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

        <?php
        // Consulta para obtener las notas del estudiante
        $sql_notas = "SELECT n.IDNota, n.IDAsignatura, a.NombreAsignatura, n.NotaFinal, n.Observaciones, n.FechaRegistro
                      FROM notas n
                      LEFT JOIN asignaturas a ON n.IDAsignatura = a.IDAsignatura
                      WHERE n.IDEstudiante = ?";
        $stmt_notas = $conexion->prepare($sql_notas);
        $stmt_notas->bind_param("s", $id_estudiante);
        $stmt_notas->execute();
        $result_notas = $stmt_notas->get_result();
        ?>

        <h2>Notas del Estudiante</h2>
        <table class="tabla-boletin">
            <thead>
                <tr>
                    <th>Asignatura</th>
                    <th>Nota Final</th>
                    <th>Observaciones</th>
                    <th>Fecha de Registro</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_notas->num_rows > 0): ?>
                    <?php while ($nota = $result_notas->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($nota['NombreAsignatura'] ?? 'Sin asignatura') ?></td>
                            <td><?= htmlspecialchars($nota['NotaFinal']) ?></td>
                            <td><?= htmlspecialchars($nota['Observaciones']) ?></td>
                            <td><?= htmlspecialchars($nota['FechaRegistro']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No hay notas registradas para este estudiante.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>