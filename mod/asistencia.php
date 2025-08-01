<?php
// En el futuro, aquí puedes cargar datos del profesor como sus materias y grados.
?>

<link rel="stylesheet" href="../css_modulos/asistencia.css">

<div class="asistencia-container">
    <div class="asistencia-header">
        <div class="header-item">
            <label for="materia-select">Materia:</label>
            <select id="materia-select" name="materia">
                <option value="">Seleccionar Materia</option>
                </select>
        </div>
        <div class="header-item">
            <label for="grado-select">Nivel:</label>
            <select id="grado-select" name="grado">
                <option value="">Seleccionar Nivel</option>
                </select>
        </div>
        <div class="header-item">
            <span><?php echo date('d \d\e F, Y'); ?></span>
        </div>
    </div>

    <table class="asistencia-table">
        <thead>
            <tr>
                <th>Alumnos</th>
                <th>Presente</th>
                <th>Ausente</th>
                <th>Excusa</th>
            </tr>
        </thead>
        <tbody id="lista-alumnos-body">
            <tr>
                <td colspan="4">Seleccione una materia y un nivel para ver la lista de alumnos.</td>
            </tr>
        </tbody>
    </table>

    <div class="asistencia-footer">
        <button id="guardar-asistencia-btn" class="btn-guardar">
            <i class="fas fa-save"></i> Guardar
        </button>
    </div>
</div>

<script src="../js/asistencia.js"></script>