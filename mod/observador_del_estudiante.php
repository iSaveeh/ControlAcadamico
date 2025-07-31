<?php
require_once '../backend/conexion.php';

$rol = $_SESSION['rol'] ?? 'estudiante';
$usuario_logueado = $_SESSION['usuario'] ?? '';
$id_usuario_logueado = $_SESSION['id'] ?? '';

$grados = [];
$estudiantes_del_grado = [];
$mis_estudiantes = []; // Para el acudiente

// --- CARGAMOS LOS NOMBRES DE LOS AUTORES EN UNA LISTA ---
$autores = [];
$result_profes = $conexion->query("SELECT IDProfesor, Nombre, Apellido FROM profesor");
if ($result_profes) {
    while ($p = $result_profes->fetch_assoc()) {
        $autores[$p['IDProfesor']] = $p['Nombre'] . ' ' . $p['Apellido'];
    }
}
$result_admins = $conexion->query("SELECT IDAdministrador, Nombre, Apellido FROM administrator");
if ($result_admins) {
    while ($a = $result_admins->fetch_assoc()) {
        $autores[$a['IDAdministrador']] = $a['Nombre'] . ' ' . $a['Apellido'];
    }
}
// --- FIN DEL BLOQUE DE AUTORES ---

$where_clauses = [];
$params = [];
$types = '';

// --- INICIO DE LA LÓGICA POR ROLES ---

if (in_array($rol, ['admin', 'profesor'])) {
    // LÓGICA PARA ADMIN Y PROFESOR (CON FILTROS)
    $resultado_grados = $conexion->query("SELECT IDGrado, NombreGrado FROM grados ORDER BY NombreGrado");
    if ($resultado_grados) while ($fila = $resultado_grados->fetch_assoc()) $grados[] = $fila;

    $grado_seleccionado = $_GET['grado'] ?? '';
    if (!empty($grado_seleccionado)) {
        $where_clauses[] = "o.IDGrado = ?";
        $types .= 's';
        $params[] = $grado_seleccionado;

        $stmt_estudiantes = $conexion->prepare("SELECT e.IDEstudiante, e.Nombre, e.Apellido FROM estudiante e JOIN salon s ON e.IDSalon = s.IDSalon WHERE s.IDGrado = ? ORDER BY e.Apellido, e.Nombre");
        $stmt_estudiantes->bind_param("s", $grado_seleccionado);
        $stmt_estudiantes->execute();
        $resultado_estudiantes = $stmt_estudiantes->get_result();
        if ($resultado_estudiantes) while ($fila = $resultado_estudiantes->fetch_assoc()) $estudiantes_del_grado[] = $fila;
        $stmt_estudiantes->close();
    }
    
    $estudiante_seleccionado = $_GET['estudiante'] ?? '';
    if (!empty($estudiante_seleccionado)) {
        $where_clauses[] = "o.IDEstudiante = ?";
        $types .= 's';
        $params[] = $estudiante_seleccionado;
    }
} elseif ($rol == 'acudiente') {
    // --- NUEVA LÓGICA PARA ACUDIENTE ---
    $stmt_mis_estudiantes = $conexion->prepare("SELECT IDEstudiante, Nombre, Apellido FROM estudiante WHERE IDAcudiente = ?");
    $stmt_mis_estudiantes->bind_param("s", $id_usuario_logueado);
    $stmt_mis_estudiantes->execute();
    $resultado_mis_estudiantes = $stmt_mis_estudiantes->get_result();
    while ($fila = $resultado_mis_estudiantes->fetch_assoc()) $mis_estudiantes[] = $fila;
    $stmt_mis_estudiantes->close();

    $estudiante_a_ver_id = $_GET['estudiante_id'] ?? ($mis_estudiantes[0]['IDEstudiante'] ?? null);
    
    if ($estudiante_a_ver_id) {
        $where_clauses[] = "o.IDEstudiante = ?";
        $types .= 's';
        $params[] = $estudiante_a_ver_id;
        // Medida de seguridad extra para que un acudiente no pueda ver observaciones de otros niños
        $where_clauses[] = "e.IDAcudiente = ?";
        $types .= 's';
        $params[] = $id_usuario_logueado;
    } else {
        // Si el acudiente no tiene estudiantes asignados, forzamos a que no se muestre nada.
        $where_clauses[] = "1 = 0"; 
    }

} else { // Si es estudiante, solo ve lo suyo
    $where_clauses[] = "e.Usuario = ?";
    $types .= 's';
    $params[] = $usuario_logueado;
}
// --- FIN DE LA LÓGICA POR ROLES ---


// Consulta principal
$sql = "SELECT o.IDObservacion, o.Descripcion, o.TipoFalta, o.Fecha, o.IDAdministrador AS IDAutor, e.Nombre AS NombreEstudiante, e.Apellido AS ApellidoEstudiante FROM observador o JOIN estudiante e ON o.IDEstudiante = e.IDEstudiante";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}
$sql .= " ORDER BY o.Fecha DESC";

$stmt = $conexion->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$observaciones = $stmt->get_result();
?>
<link rel="stylesheet" href="../css_modulos/observador.css">

<div class="observador-container">

    <?php if (in_array($rol, ['admin', 'profesor'])): ?>
        <div class="observador-filtros-box">
            <form method="get" class="observador-form-grado">
                <input type="hidden" name="mod" value="observador_del_estudiante">
                <label for="grado">Grado:</label>
                <select name="grado" id="grado" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <?php foreach ($grados as $grado): ?>
                        <option value="<?= htmlspecialchars($grado['IDGrado']) ?>" <?= ($grado['IDGrado'] ?? '') == ($_GET['grado'] ?? '') ? 'selected' : '' ?>><?= htmlspecialchars($grado['NombreGrado']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($_GET['grado'])): ?>
                    <label for="estudiante">Estudiante:</label>
                    <select name="estudiante" id="estudiante" onchange="this.form.submit()">
                        <option value="">Todos en este grado</option>
                        <?php foreach ($estudiantes_del_grado as $est): ?>
                            <option value="<?= htmlspecialchars($est['IDEstudiante']) ?>" <?= ($est['IDEstudiante'] ?? '') == ($_GET['estudiante'] ?? '') ? 'selected' : '' ?>><?= htmlspecialchars($est['Apellido'] . ', ' . $est['Nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </form>
        </div>
    <?php elseif ($rol == 'acudiente' && count($mis_estudiantes) > 1): ?>
        <div class="observador-filtros-box">
             <form method="get" class="observador-form-grado">
                <input type="hidden" name="mod" value="observador_del_estudiante">
                <label for="estudiante_id">Viendo a:</label>
                <select name="estudiante_id" id="estudiante_id" onchange="this.form.submit()">
                    <?php foreach ($mis_estudiantes as $est): ?>
                         <option value="<?= htmlspecialchars($est['IDEstudiante']) ?>" <?= ($est['IDEstudiante'] ?? '') == ($estudiante_a_ver_id ?? '') ? 'selected' : '' ?>><?= htmlspecialchars($est['Apellido'] . ', ' . $est['Nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    <?php endif; ?>

    <div class="observador-frame observador-frame-resultados">
        <div class="observador-historial" id="observadorHistorial">
            <div class="observador-header">
                <span>Observador del Estudiante</span>
                <?php if (in_array($rol, ['admin', 'profesor'])): ?>
                    <div class="observador-acciones">
                        <button onclick="window.location.href='../screens/menu.php?mod=observador_formulario'" class="btn-crear">Crear</button>
                    </div>
                <?php endif; ?>
            </div>
            <div class="observador-lista">
                <?php if ($observaciones && $observaciones->num_rows > 0): ?>
                    <?php while ($obs = $observaciones->fetch_assoc()):
                        $colorMapping = ['academica' => 'academica', 'convivencia' => 'convivencia', 'disciplinaria' => 'disciplinaria'];
                        $color = $colorMapping[strtolower($obs['TipoFalta'])] ?? '';
                        $nombreAutor = $autores[$obs['IDAutor']] ?? 'ID: ' . $obs['IDAutor'];
                        ?>
                        <div class="observador-tarjeta <?= $color ?>">
                            <div class="observador-funcionario">
                                <img src="../assets/images/avatar_funcionario.png" alt="Autor">
                                <div>
                                    <strong><?= htmlspecialchars($nombreAutor) ?></strong><br>
                                    <small><?= date("d/M/Y", strtotime($obs['Fecha'])) ?></small>
                                </div>
                            </div>
                            <div class="observador-info">
                                <p style="font-size: 1.1em; font-weight: bold; margin-bottom: 5px;"><?= htmlspecialchars($obs['ApellidoEstudiante'] . ', ' . $obs['NombreEstudiante']) ?></p>
                                <strong><?= htmlspecialchars($obs['TipoFalta']) ?></strong>
                                <p><?= htmlspecialchars($obs['Descripcion']) ?></p>
                            </div>
                            <a href="../screens/menu.php?mod=observador_detalle&id=<?= $obs['IDObservacion'] ?>" class="btn-detalles">Detalles</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="padding: 20px; color: #888;">No se encontraron observaciones con los filtros seleccionados.</div>
                <?php endif; ?>
                <?php if(isset($stmt)) $stmt->close(); if(isset($conexion)) $conexion->close(); ?>
            </div>
        </div>
    </div>
</div>