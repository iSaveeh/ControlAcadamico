<!-- css -->
<link rel="stylesheet" href="../css_modulos/control_de_notas.css">

<?php
include '../backend/conexion.php';

// Captura de filtros GET
$periodo = $_GET['periodo'] ?? '';
$asignatura = $_GET['asignatura'] ?? '';
$estudiante = $_GET['estudiante'] ?? '';
$grado = $_GET['grado'] ?? '';
$salon = $_GET['salon'] ?? '';

// Construcción dinámica del WHERE
$where = "WHERE 1=1";
if (!empty($periodo)) $where .= " AND n.IDPeriodo = " . intval($periodo);
if (!empty($asignatura)) $where .= " AND a.IDAsignatura = " . intval($asignatura);
if (!empty($estudiante)) {
    $estudiante = $conexion->real_escape_string($estudiante);
    $where .= " AND (e.Nombre LIKE '%$estudiante%' OR e.Apellido LIKE '%$estudiante%' OR e.IDEstudiante LIKE '%$estudiante%')";
}
if (!empty($grado)) $where .= " AND g.IDGrado = " . intval($grado);
if (!empty($salon)) $where .= " AND s.IDSalon = " . intval($salon);

// Consulta principal
$sql = "
SELECT 
    n.IDNota,
    CONCAT(e.Nombre, ' ', e.Apellido) AS Estudiante,
    a.NombreAsignatura AS Asignatura,
    g.NombreGrado AS Grado,
    s.NombreSalon AS Salon,
    n.IDPeriodo AS Periodo,
    n.NotaFinal AS Nota,
    n.Observaciones
FROM notas n
JOIN estudiante e ON n.IDEstudiante = e.IDEstudiante
JOIN asignaturas a ON n.IDAsignatura = a.IDAsignatura
JOIN grados g ON e.IDGrado = g.IDGrado
JOIN salon s ON e.IDSalon = s.IDSalon
$where
ORDER BY n.FechaRegistro DESC
";

$resultado = $conexion->query($sql);
?>

<!-- Filtros -->
<form method="GET" action="menu.php" class="filtros-notas">
    <input type="hidden" name="mod" value="control_de_notas">

    <input type="text" name="estudiante" class="filtro-select" placeholder="Buscar estudiante..." value="<?= htmlspecialchars($estudiante) ?>">

    <select name="grado" class="filtro-select">
        <option value="">Seleccionar Grado</option>
        <?php
        $grados = $conexion->query("SELECT IDGrado, NombreGrado FROM grados ORDER BY IDGrado ASC");
        while ($g = $grados->fetch_assoc()):
        ?>
            <option value="<?= $g['IDGrado'] ?>" <?= ($grado == $g['IDGrado']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($g['NombreGrado']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <select name="salon" class="filtro-select">
        <option value="">Seleccionar Salón</option>
        <?php
        $salones = $conexion->query("SELECT IDSalon, NombreSalon FROM salon ORDER BY IDSalon ASC");
        while ($s = $salones->fetch_assoc()):
        ?>
            <option value="<?= $s['IDSalon'] ?>" <?= ($salon == $s['IDSalon']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['NombreSalon']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <select name="periodo" class="filtro-select">
        <option value="">Seleccionar Periodo</option>
        <?php
        $periodosQuery = $conexion->query("SELECT IDPeriodo, NombrePeriodo FROM periodos ORDER BY IDPeriodo ASC");
        while ($p = $periodosQuery->fetch_assoc()):
        ?>
            <option value="<?= $p['IDPeriodo'] ?>" <?= ($periodo == $p['IDPeriodo']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['NombrePeriodo']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <select name="asignatura" class="filtro-select">
        <option value="">Seleccionar Asignatura</option>
        <?php
        $asignaturasQuery = $conexion->query("SELECT IDAsignatura, NombreAsignatura FROM asignaturas ORDER BY NombreAsignatura ASC");
        while ($a = $asignaturasQuery->fetch_assoc()):
        ?>
            <option value="<?= $a['IDAsignatura'] ?>" <?= ($asignatura == $a['IDAsignatura']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($a['NombreAsignatura']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit" class="btn-editar">🔍 Filtrar</button>
</form>

<!-- Tabla -->
<div class="tabla-container">
    <table class="tabla-notas">
        <thead>
            <tr>
                <th>Estudiante</th>
                <th>Asignatura</th>
                <th>Grado</th>
                <th>Salón</th>
                <th>Periodo</th>
                <th>Nota</th>
                <th>Observación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($fila = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['Estudiante']) ?></td>
                        <td><?= $fila['Asignatura'] ?></td>
                        <td><?= $fila['Grado'] ?></td>
                        <td><?= $fila['Salon'] ?></td>
                        <td><?= $fila['Periodo'] ?></td>
                        <td><?= $fila['Nota'] ?></td>
                        <td><?= htmlspecialchars($fila['Observaciones']) ?></td>
                        <td>
                            <button class="btn-editar">✏️ Editar</button>
                            <button class="btn-eliminar">🗑️ Eliminar</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8">No se encontraron notas.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
