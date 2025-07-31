<?php
require_once '../backend/conexion.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error: No se ha especificado una observación válida.");
}
$idObservacion = $_GET['id'];

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

$stmt = $conexion->prepare("SELECT o.*, e.Nombre AS NombreEstudiante, e.Apellido AS ApellidoEstudiante FROM observador o JOIN estudiante e ON o.IDEstudiante = e.IDEstudiante WHERE o.IDObservacion = ?");
$stmt->bind_param("i", $idObservacion);
$stmt->execute();
$resultado = $stmt->get_result();
$obs = $resultado->fetch_assoc();

if (!$obs) {
    die("Observación no encontrada.");
}

$nombreAutor = $autores[$obs['IDAdministrador']] ?? 'ID: ' . $obs['IDAdministrador'];
?>
<link rel="stylesheet" href="../css_modulos/observador_formulario.css">

<div class="observador-container">
    <div class="form-observacion">
        <h2>Detalles de la Observación</h2>

        <div class="observador-form-fila">
            <div class="observador-form-item">
                <label>Fecha de anotación</label>
                <p><?= date("d/m/Y", strtotime($obs['Fecha'])) ?></p>
            </div>
            <div class="observador-form-item">
                <label>Realizado por:</label>
                <p><?= htmlspecialchars($nombreAutor) ?></p>
            </div>
        </div>

        <div class="observador-form-fila">
             <div class="observador-form-item">
                <label>Estudiante</label>
                <p><?= htmlspecialchars($obs['NombreEstudiante'] . ' ' . $obs['ApellidoEstudiante']) ?></p>
            </div>
             <div class="observador-form-item">
                <label>Tipo de observación</label>
                <p><?= htmlspecialchars($obs['TipoFalta']) ?></p>
            </div>
        </div>

        <div class="observador-form-item">
            <label>Descripción de la situación</label>
            <p><?= nl2br(htmlspecialchars($obs['Descripcion'])) ?></p>
        </div>

        <?php if (!empty($obs['Compromiso'])): ?>
            <div class="observador-form-item">
                <label>Compromiso del Estudiante</label>
                <p><?= nl2br(htmlspecialchars($obs['Compromiso'])) ?></p>
            </div>
        <?php endif; ?>
        <div class="observador-form-boton">
             <button onclick="history.back()">Volver</button>
        </div>
    </div>
</div>