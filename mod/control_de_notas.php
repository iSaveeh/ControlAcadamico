<!-- css -->
<link rel="stylesheet" href="../css_modulos/control_de_notas.css">

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../backend/conexion.php';

$usuario = $_SESSION['usuario'] ?? '';
$rol = $_SESSION['rol'] ?? '';

$idProfesorActual = null;
if ($rol === 'profesor') {
    $stmt = $conexion->prepare("SELECT IDProfesor FROM profesor WHERE Usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->bind_result($idProfesor);
    if ($stmt->fetch()) {
        $idProfesorActual = $idProfesor;
    }
    $stmt->close();
}

$periodo = $_GET['periodo'] ?? '';
$asignatura = $_GET['asignatura'] ?? '';
$estudiante = $_GET['estudiante'] ?? '';
$grado = $_GET['grado'] ?? '';
$salon = $_GET['salon'] ?? '';

$where = "WHERE 1=1";
if (!empty($periodo)) $where .= " AND n.IDPeriodo = " . intval($periodo);
if (!empty($asignatura)) $where .= " AND a.IDAsignatura = " . intval($asignatura);
if (!empty($estudiante)) {
    $estudiante = $conexion->real_escape_string($estudiante);
    $where .= " AND (e.Nombre LIKE '%$estudiante%' OR e.Apellido LIKE '%$estudiante%' OR e.IDEstudiante LIKE '%$estudiante%')";
}
if (!empty($grado)) $where .= " AND g.IDGrado = " . intval($grado);
if (!empty($salon)) $where .= " AND ts.IDTipoSalon = " . intval($salon);

if ($rol === 'profesor' && $idProfesorActual !== null) {
    $where .= " AND a.IDProfesor = " . intval($idProfesorActual);
}

$sql = "
SELECT 
    n.IDNota,
    CONCAT(e.Nombre, ' ', e.Apellido) AS Estudiante,
    a.NombreAsignatura AS Asignatura,
    g.NombreGrado AS Grado,
    sg.NombreCompleto AS Salon,
    ts.NombreTipoSalon AS TipoSalon,
    n.IDPeriodo AS Periodo,
    n.NotaFinal AS Nota,
    n.Observaciones,
    e.IDEstudiante,
    a.IDAsignatura
FROM notas n
JOIN estudiante e ON n.IDEstudiante = e.IDEstudiante
JOIN asignaturas a ON n.IDAsignatura = a.IDAsignatura
JOIN grados g ON e.IDGrado = g.IDGrado
JOIN salon_grado sg ON e.IDSalonGrado = sg.IDSalonGrado
JOIN tipos_salon ts ON sg.IDTipoSalon = ts.IDTipoSalon
$where
ORDER BY n.FechaRegistro DESC
";

$resultado = $conexion->query($sql);
?>

<div class="control-notas-container">
    <h2>📚 Control de Notas</h2>

    <!-- Filtros -->
    <div class="filtros-card">
        <form method="GET" action="menu.php" class="filtros-notas">
            <input type="hidden" name="mod" value="control_de_notas">

            <!-- Filtros individuales -->
            <div class="filtro-grupo">
                <label>🔍 Buscar Estudiante:</label>
                <input type="text" name="estudiante" class="filtro-input" placeholder="Nombre, apellido o ID..." value="<?= htmlspecialchars($estudiante) ?>">
            </div>

            <div class="filtro-grupo">
                <label>🎓 Grado:</label>
                <select name="grado" class="filtro-select">
                    <option value="">Todos los grados</option>
                    <?php
                    $grados = $conexion->query("SELECT IDGrado, NombreGrado FROM grados ORDER BY IDGrado ASC");
                    while ($g = $grados->fetch_assoc()):
                    ?>
                        <option value="<?= $g['IDGrado'] ?>" <?= ($grado == $g['IDGrado']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['NombreGrado']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filtro-grupo">
                <label>🏫 Salón:</label>
                <select name="salon" class="filtro-select">
                    <option value="">Todos los salones</option>
                    <?php
                    $salones = $conexion->query("SELECT IDTipoSalon, NombreTipoSalon FROM tipos_salon ORDER BY NombreTipoSalon ASC");
                    while ($s = $salones->fetch_assoc()):
                    ?>
                        <option value="<?= $s['IDTipoSalon'] ?>" <?= ($salon == $s['IDTipoSalon']) ? 'selected' : '' ?>>
                            Salón <?= htmlspecialchars($s['NombreTipoSalon']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filtro-grupo">
                <label>📅 Periodo:</label>
                <select name="periodo" class="filtro-select">
                    <option value="">Todos los periodos</option>
                    <?php
                    $periodos = $conexion->query("SELECT IDPeriodo, NombrePeriodo FROM periodos ORDER BY IDPeriodo ASC");
                    while ($p = $periodos->fetch_assoc()):
                    ?>
                        <option value="<?= $p['IDPeriodo'] ?>" <?= ($periodo == $p['IDPeriodo']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['NombrePeriodo']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filtro-grupo">
                <label>📖 Asignatura:</label>
                <select name="asignatura" class="filtro-select">
                    <option value="">Todas las asignaturas</option>
                    <?php
                    if ($rol === 'profesor' && $idProfesorActual !== null) {
                        $asignaturas = $conexion->query("SELECT IDAsignatura, NombreAsignatura FROM asignaturas WHERE IDProfesor = " . intval($idProfesorActual) . " ORDER BY NombreAsignatura ASC");
                    } else {
                        $asignaturas = $conexion->query("SELECT IDAsignatura, NombreAsignatura FROM asignaturas ORDER BY NombreAsignatura ASC");
                    }
                    while ($a = $asignaturas->fetch_assoc()):
                    ?>
                        <option value="<?= $a['IDAsignatura'] ?>" <?= ($asignatura == $a['IDAsignatura']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['NombreAsignatura']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filtro-acciones">
                <button type="submit" class="btn-filtrar">🔍 Filtrar</button>
                <a href="menu.php?mod=control_de_notas" class="btn-limpiar">🗑️ Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Botón crear nueva nota -->
    <div style="margin-bottom: 20px; text-align: right;">
        <a href="menu.php?mod=crear_nota" class="btn-crear-nota">➕ Nueva Nota</a>
    </div>

    <!-- Estadísticas -->
    <div class="estadisticas-card">
        <?php
        $total_notas = $resultado ? $resultado->num_rows : 0;
        $promedio_general = 0;

        if ($total_notas > 0) {
            $sqlPromedio = "
            SELECT AVG(n.NotaFinal) AS promedio
            FROM notas n
            JOIN estudiante e ON n.IDEstudiante = e.IDEstudiante
            JOIN asignaturas a ON n.IDAsignatura = a.IDAsignatura
            JOIN grados g ON e.IDGrado = g.IDGrado
            JOIN salon_grado sg ON e.IDSalonGrado = sg.IDSalonGrado
            JOIN tipos_salon ts ON sg.IDTipoSalon = ts.IDTipoSalon
            $where
            ";
            $resProm = $conexion->query($sqlPromedio);
            if ($resProm && $filaProm = $resProm->fetch_assoc()) {
                $promedio_general = round(floatval($filaProm['promedio']), 2);
            }
        }
        ?>
        <div class="stat-item">
            <span class="stat-number"><?= $total_notas ?></span>
            <span class="stat-label">Notas encontradas</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= $promedio_general ?></span>
            <span class="stat-label">Promedio general</span>
        </div>
    </div>

    <!-- Tabla de resultados -->
    <div class="tabla-container">
        <table class="tabla-notas" id="tablaNotas">
            <thead>
                <tr>
                    <th>👨‍🎓 Estudiante</th>
                    <th>📖 Asignatura</th>
                    <th>🎓 Grado</th>
                    <th>🏫 Salón</th>
                    <th>📅 Periodo</th>
                    <th>📝 Nota</th>
                    <th>💭 Observación</th>
                    <th>⚙️ Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while ($fila = $resultado->fetch_assoc()): ?>
                        <tr data-id-nota="<?= $fila['IDNota'] ?>">
                            <td>
                                <div class="estudiante-info">
                                    <strong><?= htmlspecialchars($fila['Estudiante']) ?></strong>
                                    <small>ID: <?= $fila['IDEstudiante'] ?></small>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($fila['Asignatura']) ?></td>
                            <td><span class="badge-grado"><?= htmlspecialchars($fila['Grado']) ?></span></td>
                            <td><?= htmlspecialchars($fila['Salon']) ?></td>
                            <td><span class="badge-periodo">P<?= $fila['Periodo'] ?></span></td>
                            <td>
                                <span class="nota-valor <?= ($fila['Nota'] >= 3.0) ? 'aprobado' : 'reprobado' ?>">
                                    <?= number_format($fila['Nota'], 1) ?>
                                </span>
                            </td>
                            <td>
                                <div class="observacion-cell">
                                    <?= htmlspecialchars(substr($fila['Observaciones'], 0, 50)) ?>
                                    <?= strlen($fila['Observaciones']) > 50 ? '...' : '' ?>
                                </div>
                            </td>
                            <td>
                                <div class="acciones-grupo">
                                    <button class="btn-editar" onclick="editarNota(<?= $fila['IDNota'] ?>, '<?= addslashes($fila['Estudiante']) ?>', <?= $fila['Nota'] ?>, '<?= addslashes($fila['Observaciones']) ?>')">✏️ Editar</button>
                                    <button class="btn-eliminar" onclick="confirmarEliminacion(<?= $fila['IDNota'] ?>, '<?= addslashes($fila['Estudiante']) ?>')">🗑️ Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="no-datos">
                            <div class="mensaje-vacio">
                                <span>📋</span>
                                <p>No se encontraron notas con los filtros aplicados</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../js/control_de_notas.js"></script>

