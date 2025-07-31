<?php
require_once '../backend/conexion.php'; 

if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['profesor', 'admin'])) {
    die("Acceso denegado.");
}
$grados = $conexion->query("SELECT IDGrado, NombreGrado FROM grados ORDER BY NombreGrado ASC");
$estudiantes_query = $conexion->query(
    "SELECT e.IDEstudiante, e.Nombre, e.Apellido, s.IDGrado 
     FROM estudiante e 
     JOIN salon s ON e.IDSalon = s.IDSalon 
     ORDER BY e.Apellido, e.Nombre ASC"
);

$todos_los_estudiantes = [];
while ($est = $estudiantes_query->fetch_assoc()) {
    $todos_los_estudiantes[] = $est;
}

$nombre_autor = $_SESSION['nombre_completo'] ?? 'Usuario no identificado';
?>
<link rel="stylesheet" href="../css_modulos/observador_formulario.css">

<div class="observador-container">
    <form class="form-observacion" method="post" action="../mod/guardar_observacion.php">
        <h2>Formulario de Observación</h2>

        <div class="observador-form-fila">
            <div class="observador-form-item">
                <label for="fecha">Fecha de anotación</label>
                <input type="date" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" required>
            </div>
             <div class="observador-form-item">
                <label>Realizado por:</label>
                <span style="padding-top: 8px; display: block; font-weight: normal;"><?= htmlspecialchars($nombre_autor) ?></span>
            </div>
        </div>

        <div class="observador-form-fila">
            <div class="observador-form-item">
                <label for="filtro-grado">Paso 1: Seleccione un Grado</label>
                <select id="filtro-grado">
                    <option value="">Seleccione un grado para ver los estudiantes...</option>
                    <?php foreach ($grados as $grado): ?>
                        <option value="<?= htmlspecialchars($grado['IDGrado']) ?>"><?= htmlspecialchars($grado['NombreGrado']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="observador-form-item">
                <label for="estudiante">Paso 2: Seleccione el Estudiante</label>
                <select id="estudiante" name="IDEstudiante" required disabled>
                    <option value="">--</option>
                </select>
            </div>
        </div>
        <div class="observador-form-item">
            <label for="tipo">Tipo de observación</label>
            <select id="tipo" name="tipo" required>
                <option value="">Seleccione...</option>
                <option value="Academica">Académica</option>
                <option value="Convivencia">Convivencia</option>
                <option value="Disciplinaria">Disciplinaria</option>
            </select>
        </div>

        <div class="observador-form-item">
            <label for="descripcion">Descripción de la situación</label>
            <textarea id="descripcion" name="descripcion" rows="4" required></textarea>
        </div>

        <div class="observador-form-item">
            <label for="compromiso">Compromiso del estudiante (Opcional)</label>
            <textarea id="compromiso" name="compromiso" rows="3" placeholder="Ej: El estudiante se compromete a..."></textarea>
        </div>

        <div class="observador-form-boton">
            <button type="submit">Crear observación</button>
        </div>
    </form>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const estudiantes = <?= json_encode($todos_los_estudiantes) ?>;

    const selectorGrado = document.getElementById('filtro-grado');
    const selectorEstudiante = document.getElementById('estudiante');

    selectorGrado.addEventListener('change', function() {
        const idGradoSeleccionado = this.value;

        selectorEstudiante.innerHTML = '<option value="">Seleccione un estudiante...</option>';
        selectorEstudiante.disabled = true;

        if (idGradoSeleccionado) {
            const estudiantesDelGrado = estudiantes.filter(est => est.IDGrado == idGradoSeleccionado);
            
            if (estudiantesDelGrado.length > 0) {
                estudiantesDelGrado.forEach(function(estudiante) {
                    const opcion = document.createElement('option');
                    opcion.value = estudiante.IDEstudiante;
                    opcion.textContent = `${estudiante.Apellido}, ${estudiante.Nombre}`;
                    selectorEstudiante.appendChild(opcion);
                });
                selectorEstudiante.disabled = false; 
            } else {
                 selectorEstudiante.innerHTML = '<option value="">No hay estudiantes en este grado</option>';
            }
        }
    });
});
</script>