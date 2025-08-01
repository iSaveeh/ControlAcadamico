<?php
// No debe haber session_start() aquí

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'profesor') {
    header('Location: ../screens/login.php');
    exit();
}

require_once '../backend/conexion.php';

$id_profesor_logueado = $_SESSION['id'];

$actividades = [];
$asignaturas_profesor = []; // Nuevo array para las asignaturas del profesor

// Lógica para obtener las actividades del profesor desde la base de datos
try {
    $query_actividades = "
        SELECT
            A.IDActividad,
            Asig.NombreAsignatura,
            A.NombreActividad,
            A.Descripcion,
            A.Fecha,
            A.Porcentaje
        FROM
            actividades AS A
        JOIN
            asignaturas AS Asig ON A.IDAsignatura = Asig.IDAsignatura
        WHERE
            Asig.IDProfesor = ?
        ORDER BY
            A.Fecha DESC
    ";

    $stmt_actividades = $conexion->prepare($query_actividades);

    if ($stmt_actividades === false) {
        throw new Exception("Error al preparar la consulta de actividades: " . $conexion->error);
    }

    $stmt_actividades->bind_param("i", $id_profesor_logueado);
    $stmt_actividades->execute();
    $resultado_actividades = $stmt_actividades->get_result();

    if ($resultado_actividades) {
        while ($row = $resultado_actividades->fetch_assoc()) {
            $actividades[] = $row;
        }
        $resultado_actividades->free();
    }
    $stmt_actividades->close();

    // --- NUEVA LÓGICA PARA OBTENER LAS ASIGNATURAS DEL PROFESOR ---
    $query_asignaturas = "
        SELECT
            IDAsignatura,
            NombreAsignatura
        FROM
            asignaturas
        WHERE
            IDProfesor = ?
        ORDER BY
            NombreAsignatura ASC
    ";

    $stmt_asignaturas = $conexion->prepare($query_asignaturas);

    if ($stmt_asignaturas === false) {
        throw new Exception("Error al preparar la consulta de asignaturas: " . $conexion->error);
    }

    $stmt_asignaturas->bind_param("i", $id_profesor_logueado);
    $stmt_asignaturas->execute();
    $resultado_asignaturas = $stmt_asignaturas->get_result();

    if ($resultado_asignaturas) {
        while ($row = $resultado_asignaturas->fetch_assoc()) {
            $asignaturas_profesor[] = $row;
        }
        $resultado_asignaturas->free();
    }
    $stmt_asignaturas->close();

} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>Error al cargar datos: " . htmlspecialchars($e->getMessage()) . "</p>";
    $actividades = [];
    $asignaturas_profesor = [];
}
// Fin de la lógica PHP
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizarrón de Tareas - Focus Grade</title>
    <link rel="stylesheet" href="../css/general.css">
    <link rel="stylesheet" href="../css/menu.css">
    <link rel="stylesheet" href="../css_modulos/pizarron.css">
</head>
<body>
    <div class="container">
        <div class="pizarron-container">
            <h1>Pizarrón de Tareas</h1>

            <button type="button" id="btnNuevaTarea" class="btn-nueva-tarea">Nueva Tarea</button>

            <?php if (empty($actividades)): ?>
                <p>No hay actividades registradas para tus asignaturas.</p>
            <?php else: ?>
                <table class="actividades-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Asignatura</th>
                            <th>Actividad</th>
                            <th>Descripción</th>
                            <th>Fecha de Entrega</th>
                            <th>Porcentaje</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($actividades as $actividad): ?>
                            <tr>
                                <td data-label="ID"><?php echo htmlspecialchars($actividad['IDActividad']); ?></td>
                                <td data-label="Asignatura"><?php echo htmlspecialchars($actividad['NombreAsignatura']); ?></td>
                                <td data-label="Actividad"><?php echo htmlspecialchars($actividad['NombreActividad']); ?></td>
                                <td data-label="Descripción"><?php echo htmlspecialchars($actividad['Descripcion']); ?></td>
                                <td data-label="Fecha de Entrega"><?php echo htmlspecialchars($actividad['Fecha']); ?></td>
                                <td data-label="Porcentaje"><?php echo htmlspecialchars(number_format($actividad['Porcentaje'], 2)) . '%'; ?></td>
                                <td data-label="Acciones">
                                    <button type="button" class="btn-editar" data-id="<?php echo htmlspecialchars($actividad['IDActividad']); ?>">Editar</button>
                                    <button type="button" class="btn-borrar" data-id="<?php echo htmlspecialchars($actividad['IDActividad']); ?>">
      Borrar
    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div id="modalNuevaTarea" class="modal">
            <div class="modal-content">
                <span class="close-button">&times;</span>
                <h2 id="modalTitle">Nueva Actividad</h2>
                <form id="formNuevaActividad">
                    <input type="hidden" id="idActividad" name="idActividad">
                    <label for="nombreActividad">Nombre de la Actividad:</label>
                    <input type="text" id="nombreActividad" name="nombreActividad" required><br><br>

                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="4"></textarea><br><br>

                    <label for="idAsignatura">Asignatura:</label>
                    <select id="idAsignatura" name="idAsignatura" required>
                        <option value="">Seleccione una asignatura</option>
                        <?php foreach ($asignaturas_profesor as $asignatura): ?>
                            <option value="<?php echo htmlspecialchars($asignatura['IDAsignatura']); ?>">
                                <?php echo htmlspecialchars($asignatura['NombreAsignatura']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select><br><br>

                    <label for="fecha">Fecha de Entrega (YYYY-MM-DD):</label>
                    <input type="date" id="fecha" name="fecha" required><br><br>

                    <label for="porcentaje">Porcentaje (%):</label>
                    <input type="number" id="porcentaje" name="porcentaje" min="0" max="100" step="0.01" required><br><br>

                    <button type="submit" id="btnGuardarTarea">Guardar Tarea</button>
                </form>
                <p id="mensajeFormulario" style="color: green; font-weight: bold;"></p>
            </div>
        </div>

        <!-- Modal de confirmación para eliminar -->
        <div id="confirmDeleteModal" class="modal">
            <div class="modal-content modal-confirm">
                <span class="close-button confirm-close-button">&times;</span>
                <h2 id="confirmDeleteTitle">Confirmar Eliminación</h2>
                <p>¿Estás seguro de que deseas eliminar la actividad "<span id="activityNameToDelete"></span>"?</p>
                <div class="confirm-buttons">
                    <button type="button" id="btnConfirmDelete" class="btn-borrar">Sí, Eliminar</button>
                    <button type="button" id="btnCancelDelete" class="btn-cancelar">Cancelar</button>
                </div>
            </div>
        </div>

        <script src="../js/pizarron_de_tareas.js"></script>
    </body>
</html>