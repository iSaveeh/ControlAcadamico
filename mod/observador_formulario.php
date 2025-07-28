<?php
// Aquí puedes validar sesión, permisos, o cargar datos dinámicos si necesitas
?>

<link rel="stylesheet" href="../css_modulos/observador_formulario.css">

<div class="observador-container">
    <form class="form-observacion" method="post" action="guardar_observacion.php">
        <h2>Formulario de Observación</h2>

        <div class="observador-form-fila">
            <div class="observador-form-item">
                <label for="fecha">Fecha de anotación</label>
                <input type="date" id="fecha" name="fecha" required>
            </div>
            <div class="observador-form-item">
                <label for="grado">Grado y Aula</label>
                <select id="grado" name="grado" required>
                    <option value="">Seleccione...</option>
                    <option value="6A">6A</option>
                    <option value="7B">7B</option>
                    <option value="8C">8C</option>
                    <!-- Agrega más grados y aulas -->
                </select>
            </div>
        </div>

        <div class="observador-form-item">
            <label for="estudiante">Nombre del estudiante</label>
            <input type="text" id="estudiante" name="estudiante" placeholder="Buscar por nombre o apellido" required>
        </div>

        <div class="observador-form-fila">
            <div class="observador-form-item">
                <label for="asignatura">Asignatura</label>
                <select id="asignatura" name="asignatura" onchange="mostrarProfesor(this.value)" required>
                    <option value="">Seleccione...</option>
                    <option value="Matemáticas">Matemáticas</option>
                    <option value="Lengua">Lengua</option>
                    <option value="Ciencias">Ciencias</option>
                    <!-- Agrega más asignaturas -->
                </select>
            </div>
            <div class="observador-form-item">
                <label>Profesor:</label>
                <span id="nombre-profesor">---</span>
            </div>
        </div>

        <div class="observador-form-item">
            <label for="tipo">Tipo de observación</label>
            <select id="tipo" name="tipo" required>
                <option value="">Seleccione...</option>
                <option value="Académica">Académica</option>
                <option value="Convivencia">Convivencia</option>
                <option value="Disciplinaria">Disciplinaria</option>
            </select>
        </div>

        <div class="observador-form-item">
            <label for="descripcion">Descripción de la situación</label>
            <textarea id="descripcion" name="descripcion" rows="4" required></textarea>
        </div>

        <div class="observador-form-item">
            <label for="compromiso1">Compromiso del estudiante</label>
            <input type="text" id="compromiso1" name="compromiso1" required>
        </div>

        <div class="observador-form-item">
            <label for="compromiso2">Si reincide, ¿qué pasa?</label>
            <input type="text" id="compromiso2" name="compromiso2">
        </div>

        <div class="observador-form-boton">
            <button type="submit">Crear observación</button>
        </div>

        <div class="observador-firmas">
            <span>Firma del estudiante</span>
            <span>Firma del padre/acudiente</span>
            <span>Firma del profesor</span>
        </div>
    </form>
</div>

<script>
// Script simple para mostrar el profesor (puedes mejorarlo con datos reales)
function mostrarProfesor(asignatura) {
    const profesores = {
        'Matemáticas': 'Prof. García',
        'Lengua': 'Prof. Martínez',
        'Ciencias': 'Prof. Rivera'
    };
    document.getElementById('nombre-profesor').textContent = profesores[asignatura] || '---';
}
</script>
