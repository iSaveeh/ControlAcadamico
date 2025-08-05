<?php
include '../backend/conexion.php';

$rol = $_SESSION['rol'] ?? '';
$idAcudiente = $_SESSION['id'] ?? null;

// Filtros
$periodo = $_GET['periodo'] ?? '';
$grado = $_GET['grado'] ?? '';
$estudiante = $_GET['estudiante'] ?? '';

// Acción para generar boletín
if (isset($_POST['generar_boletin'])) {
    $estudianteId = $_POST['estudiante_id'];
    $periodoId = $_POST['periodo_id'];

    $promedioQuery = "
        SELECT AVG(NotaFinal) as promedio 
        FROM notas 
        WHERE IDEstudiante = ? AND IDPeriodo = ?
    ";
    $stmt = $conexion->prepare($promedioQuery);
    $stmt->bind_param("ii", $estudianteId, $periodoId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $promedio = $resultado->fetch_assoc()['promedio'] ?? 0;

    $estado = $promedio >= 3.0 ? 'Aprobado' : 'Reprobado';

    $insertBoletin = "
        INSERT INTO boletin (IDEstudiante, IDPeriodo, PromedioGeneral, Estado) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        PromedioGeneral = VALUES(PromedioGeneral),
        Estado = VALUES(Estado),
        FechaGeneracion = NOW()
    ";
    $stmt = $conexion->prepare($insertBoletin);
    $stmt->bind_param("iids", $estudianteId, $periodoId, $promedio, $estado);
    $stmt->execute();

    $mensaje = "Boletín generado correctamente";
}

// Filtro según rol
$where = "WHERE 1=1";

if ($rol === 'acudiente' && $idAcudiente) {
    $where .= " AND e.IDAcudiente = " . intval($idAcudiente);
}

if (!empty($periodo)) $where .= " AND p.IDPeriodo = " . intval($periodo);
if (!empty($grado)) $where .= " AND g.IDGrado = " . intval($grado);
if (!empty($estudiante)) {
    $estudiante = $conexion->real_escape_string($estudiante);
    $where .= " AND (e.Nombre LIKE '%$estudiante%' OR e.Apellido LIKE '%$estudiante%')";
}

$sql = "
SELECT DISTINCT
    e.IDEstudiante,
    CONCAT(e.Nombre, ' ', e.Apellido) AS NombreCompleto,
    g.NombreGrado,
    p.IDPeriodo,
    p.NombrePeriodo,
    b.IDBoletin,
    b.PromedioGeneral,
    b.Estado,
    b.FechaGeneracion
FROM estudiante e
JOIN grados g ON e.IDGrado = g.IDGrado
CROSS JOIN periodos p
LEFT JOIN boletin b ON e.IDEstudiante = b.IDEstudiante AND p.IDPeriodo = b.IDPeriodo
$where
ORDER BY g.IDGrado, e.Apellido, e.Nombre, p.IDPeriodo
";

$resultado = $conexion->query($sql);
?>

<link rel="stylesheet" href="../css_modulos/boletin.css">

<div class="boletin-container">
    <h2>📋 Gestión de Boletines</h2>

    <?php if (isset($mensaje)): ?>
        <div class="mensaje-exito">✅ <?= $mensaje ?></div>
    <?php endif; ?>

    <div class="filtros-card">
        <form method="GET" action="menu.php" class="filtros-form">
            <input type="hidden" name="mod" value="boletin">

            <div class="filtro-grupo">
                <label>🔍 Buscar Estudiante:</label>
                <input type="text" name="estudiante" placeholder="Nombre o apellido..." 
                       value="<?= htmlspecialchars($estudiante) ?>">
            </div>

            <div class="filtro-grupo">
                <label>🎓 Grado:</label>
                <select name="grado">
                    <option value="">Todos los grados</option>
                    <?php
                    $grados = $conexion->query("SELECT IDGrado, NombreGrado FROM grados ORDER BY IDGrado");
                    while ($g = $grados->fetch_assoc()):
                    ?>
                        <option value="<?= $g['IDGrado'] ?>" <?= ($grado == $g['IDGrado']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['NombreGrado']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filtro-grupo">
                <label>📅 Periodo:</label>
                <select name="periodo">
                    <option value="">Todos los periodos</option>
                    <?php
                    $periodos = $conexion->query("SELECT IDPeriodo, NombrePeriodo FROM periodos ORDER BY IDPeriodo");
                    while ($p = $periodos->fetch_assoc()):
                    ?>
                        <option value="<?= $p['IDPeriodo'] ?>" <?= ($periodo == $p['IDPeriodo']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['NombrePeriodo']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filtro-acciones">
                <button type="submit" class="btn-filtrar">🔍 Filtrar</button>
                <a href="menu.php?mod=boletin" class="btn-limpiar">🗑️ Limpiar</a>
            </div>
        </form>
    </div>

    <div class="tabla-container">
        <table class="tabla-boletin">
            <thead>
                <tr>
                    <th>👨‍🎓 Estudiante</th>
                    <th>🎓 Grado</th>
                    <th>📅 Periodo</th>
                    <th>📊 Promedio</th>
                    <th>✅ Estado</th>
                    <th>📋 Boletín</th>
                    <th>⚙️ Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while ($fila = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="estudiante-info">
                                    <strong><?= htmlspecialchars($fila['NombreCompleto']) ?></strong>
                                    <small>ID: <?= $fila['IDEstudiante'] ?></small>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($fila['NombreGrado']) ?></td>
                            <td><?= htmlspecialchars($fila['NombrePeriodo']) ?></td>
                            <td>
                                <?php if ($fila['PromedioGeneral']): ?>
                                    <span class="promedio <?= ($fila['PromedioGeneral'] >= 3.0) ? 'aprobado' : 'reprobado' ?>">
                                        <?= number_format($fila['PromedioGeneral'], 2) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="sin-notas">Sin notas</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($fila['Estado']): ?>
                                    <span class="estado <?= strtolower($fila['Estado']) ?>">
                                        <?= $fila['Estado'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="pendiente">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($fila['IDBoletin']): ?>
                                    <span class="generado">✅ Generado</span>
                                    <small><?= date('d/m/Y', strtotime($fila['FechaGeneracion'])) ?></small>
                                <?php else: ?>
                                    <span class="no-generado">❌ No generado</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="acciones-grupo">
                                    <?php if (!$fila['IDBoletin']): ?>
                                        <button class="btn-generar" 
                                            onclick="generarBoletin(<?= $fila['IDEstudiante'] ?>, <?= $fila['IDPeriodo'] ?>, '<?= addslashes($fila['NombreCompleto']) ?>')">
                                            📋 Generar
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-ver" onclick="verBoletin(<?= $fila['IDBoletin'] ?>)">👁️ Ver</button>
                                        <button class="btn-imprimir" onclick="imprimirBoletin(<?= $fila['IDBoletin'] ?>)">🖨️ Imprimir</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="no-datos">
                            <div class="mensaje-vacio">
                                <span>📋</span>
                                <p>No se encontraron registros</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modales y scripts (puedes mantener los mismos) -->
<script>
function generarBoletin(estudianteId, periodoId, nombreEstudiante) {
    document.getElementById('estudianteId').value = estudianteId;
    document.getElementById('periodoId').value = periodoId;
    document.getElementById('nombreEstudiante').textContent = nombreEstudiante;
    document.getElementById('modalGenerar').style.display = 'block';
}
function verBoletin(boletinId) {
    fetch(`../backend/obtener_boletin.php?id=${boletinId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('contenidoBoletin').innerHTML = html;
            document.getElementById('modalVerBoletin').style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar el boletín');
        });
}
function imprimirBoletin(boletinId) {
    window.open(`../backend/imprimir_boletin.php?id=${boletinId}`, '_blank');
}
function imprimirBoletinModal() {
    const contenido = document.getElementById('contenidoBoletin').innerHTML;
    const ventana = window.open('', '_blank');
    ventana.document.write(`
        <html>
            <head><title>Boletín de Calificaciones</title></head>
            <body>${contenido}</body>
        </html>
    `);
    ventana.document.close();
    ventana.print();
}
function cerrarModal() {
    document.getElementById('modalGenerar').style.display = 'none';
}
function cerrarModalVer() {
    document.getElementById('modalVerBoletin').style.display = 'none';
}
window.onclick = function(event) {
    if (event.target === document.getElementById('modalGenerar')) cerrarModal();
    if (event.target === document.getElementById('modalVerBoletin')) cerrarModalVer();
}
</script>
