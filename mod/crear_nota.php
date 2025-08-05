<?php
include '../backend/conexion.php';

$mensaje = '';
$error = '';

// Procesar formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_nota'])) {
    $estudiante_id = $_POST['estudiante_id'];
    $asignatura_id = $_POST['asignatura_id'];
    $periodo_id = $_POST['periodo_id'];
    $nota_final = $_POST['nota_final'];
    $observaciones = $_POST['observaciones'] ?? '';
    
    // Validaciones
    if (empty($estudiante_id) || empty($asignatura_id) || empty($periodo_id) || empty($nota_final)) {
        $error = "Todos los campos marcados con * son obligatorios";
    } elseif ($nota_final < 0 || $nota_final > 5) {
        $error = "La nota debe estar entre 0.0 y 5.0";
    } else {
        // Verificar si ya existe una nota para este estudiante, asignatura y periodo
        $verificar = $conexion->prepare("
            SELECT IDNota FROM notas 
            WHERE IDEstudiante = ? AND IDAsignatura = ? AND IDPeriodo = ?
        ");
        $verificar->bind_param("iii", $estudiante_id, $asignatura_id, $periodo_id);
        $verificar->execute();
        $resultado = $verificar->get_result();
        
        if ($resultado->num_rows > 0) {
            $error = "Ya existe una nota registrada para este estudiante en esta asignatura y periodo";
        } else {
            // Insertar la nueva nota
            $stmt = $conexion->prepare("
                INSERT INTO notas (IDEstudiante, IDAsignatura, IDPeriodo, NotaFinal, Observaciones, FechaRegistro) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("iiiDS", $estudiante_id, $asignatura_id, $periodo_id, $nota_final, $observaciones);
            
            if ($stmt->execute()) {
                $mensaje = "Nota creada exitosamente";
                // Limpiar formulario
                $_POST = array();
            } else {
                $error = "Error al crear la nota: " . $conexion->error;
            }
            $stmt->close();
        }
        $verificar->close();
    }
}

// Obtener datos para los selectores
$estudiantes = $conexion->query("
    SELECT e.IDEstudiante, CONCAT(e.Nombre, ' ', e.Apellido) as NombreCompleto, g.NombreGrado
    FROM estudiante e 
    JOIN grados g ON e.IDGrado = g.IDGrado 
    ORDER BY g.IDGrado, e.Apellido, e.Nombre
");

$asignaturas = $conexion->query("
    SELECT IDAsignatura, NombreAsignatura 
    FROM asignaturas 
    ORDER BY NombreAsignatura
");

$periodos = $conexion->query("
    SELECT IDPeriodo, NombrePeriodo 
    FROM periodos 
    ORDER BY IDPeriodo
");

$grados = $conexion->query("
    SELECT IDGrado, NombreGrado 
    FROM grados 
    ORDER BY IDGrado
");
?>

    <!-- css -->
    <link rel="stylesheet" href="../css_modulos/crear_notas.css">

<div class="crear-nota-container">
    <div class="header-seccion">
        <h2>📝 Crear Nueva Nota</h2>
        <p>Registra las calificaciones de los estudiantes</p>
    </div>
    
    <?php if ($mensaje): ?>
        <div class="mensaje-exito">
            <span>✅</span>
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="mensaje-error">
            <span>❌</span>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <div class="formulario-card">
        <form method="POST" class="form-crear-nota" id="formCrearNota">
            <div class="form-section">
                <h3>👨‍🎓 Información del Estudiante</h3>
                
                <div class="form-row">
                    <div class="form-grupo">
                        <label for="filtro_grado">🎓 Filtrar por Grado:</label>
                        <select id="filtro_grado" class="form-control" onchange="filtrarEstudiantes()">
                            <option value="">Todos los grados</option>
                            <?php
                            $grados->data_seek(0); // Reiniciar puntero
                            while ($grado = $grados->fetch_assoc()):
                            ?>
                                <option value="<?= $grado['IDGrado'] ?>">
                                    <?= htmlspecialchars($grado['NombreGrado']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-grupo">
                        <label for="estudiante_id">👨‍🎓 Estudiante: *</label>
                        <select name="estudiante_id" id="estudiante_id" class="form-control" required>
                            <option value="">Seleccionar estudiante...</option>
                            <?php while ($estudiante = $estudiantes->fetch_assoc()): ?>
                                <option value="<?= $estudiante['IDEstudiante'] ?>" 
                                        data-grado="<?= $estudiante['NombreGrado'] ?>"
                                        <?= (isset($_POST['estudiante_id']) && $_POST['estudiante_id'] == $estudiante['IDEstudiante']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($estudiante['NombreCompleto']) ?> - <?= $estudiante['NombreGrado'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>📚 Información Académica</h3>
                
                <div class="form-row">
                    <div class="form-grupo">
                        <label for="asignatura_id">📖 Asignatura: *</label>
                        <select name="asignatura_id" id="asignatura_id" class="form-control" required>
                            <option value="">Seleccionar asignatura...</option>
                            <?php while ($asignatura = $asignaturas->fetch_assoc()): ?>
                                <option value="<?= $asignatura['IDAsignatura'] ?>"
                                        <?= (isset($_POST['asignatura_id']) && $_POST['asignatura_id'] == $asignatura['IDAsignatura']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($asignatura['NombreAsignatura']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-grupo">
                        <label for="periodo_id">📅 Periodo: *</label>
                        <select name="periodo_id" id="periodo_id" class="form-control" required>
                            <option value="">Seleccionar periodo...</option>
                            <?php while ($periodo = $periodos->fetch_assoc()): ?>
                                <option value="<?= $periodo['IDPeriodo'] ?>"
                                        <?= (isset($_POST['periodo_id']) && $_POST['periodo_id'] == $periodo['IDPeriodo']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($periodo['NombrePeriodo']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>📊 Calificación</h3>
                
                <div class="form-row">
                    <div class="form-grupo nota-grupo">
                        <label for="nota_final">📝 Nota Final: *</label>
                        <div class="nota-input-container">
                            <input type="number" 
                                   name="nota_final" 
                                   id="nota_final" 
                                   class="form-control nota-input" 
                                   min="0" 
                                   max="5" 
                                   step="0.1" 
                                   placeholder="0.0"
                                   value="<?= isset($_POST['nota_final']) ? htmlspecialchars($_POST['nota_final']) : '' ?>"
                                   required
                                   onchange="actualizarEstadoNota()">
                            <span class="nota-rango">/ 5.0</span>
                        </div>
                        <div id="estado_nota" class="estado-nota"></div>
                        <small class="ayuda-texto">Ingresa una calificación entre 0.0 y 5.0</small>
                    </div>
                    
                    <div class="form-grupo">
                        <label for="observaciones">💭 Observaciones:</label>
                        <textarea name="observaciones" 
                                  id="observaciones" 
                                  class="form-control" 
                                  rows="4" 
                                  placeholder="Observaciones adicionales sobre la calificación (opcional)..."><?= isset($_POST['observaciones']) ? htmlspecialchars($_POST['observaciones']) : '' ?></textarea>
                        <small class="ayuda-texto">Opcional: Comentarios sobre el desempeño del estudiante</small>
                    </div>
                </div>
            </div>
            
            <div class="form-acciones">
                <button type="button" onclick="limpiarFormulario()" class="btn-limpiar">
                    🗑️ Limpiar Formulario
                </button>
                <button type="submit" name="crear_nota" class="btn-crear">
                    💾 Crear Nota
                </button>
            </div>
        </form>
    </div>
    
    <!-- Preview de la nota -->
    <div class="preview-nota" id="previewNota" style="display: none;">
        <h3>👁️ Vista Previa</h3>
        <div class="preview-content">
            <div class="preview-item">
                <strong>Estudiante:</strong> <span id="preview-estudiante">-</span>
            </div>
            <div class="preview-item">
                <strong>Asignatura:</strong> <span id="preview-asignatura">-</span>
            </div>
            <div class="preview-item">
                <strong>Periodo:</strong> <span id="preview-periodo">-</span>
            </div>
            <div class="preview-item">
                <strong>Nota:</strong> <span id="preview-nota">-</span>
            </div>
        </div>
    </div>
</div>

<script>
function filtrarEstudiantes() {
    const gradoSeleccionado = document.getElementById('filtro_grado').value;
    const selectEstudiante = document.getElementById('estudiante_id');
    const opciones = selectEstudiante.options;
    
    for (let i = 1; i < opciones.length; i++) { // Empezar en 1 para saltar "Seleccionar..."
        const opcion = opciones[i];
        const gradoEstudiante = opcion.getAttribute('data-grado');
        
        if (gradoSeleccionado === '' || gradoEstudiante.includes(gradoSeleccionado)) {
            opcion.style.display = '';
        } else {
            opcion.style.display = 'none';
        }
    }
    
    // Resetear selección si el estudiante actual no está visible
    if (selectEstudiante.selectedIndex > 0) {
        const opcionSeleccionada = selectEstudiante.options[selectEstudiante.selectedIndex];
        if (opcionSeleccionada.style.display === 'none') {
            selectEstudiante.selectedIndex = 0;
        }
    }
    
    actualizarPreview();
}

function actualizarEstadoNota() {
    const nota = parseFloat(document.getElementById('nota_final').value);
    const estadoDiv = document.getElementById('estado_nota');
    
    if (isNaN(nota)) {
        estadoDiv.innerHTML = '';
        return;
    }
    
    if (nota >= 3.0) {
        estadoDiv.innerHTML = '<span class="estado-aprobado">✅ APROBADO</span>';
    } else {
        estadoDiv.innerHTML = '<span class="estado-reprobado">❌ REPROBADO</span>';
    }
    
    actualizarPreview();
}

function actualizarPreview() {
    const estudiante = document.getElementById('estudiante_id');
    const asignatura = document.getElementById('asignatura_id');
    const periodo = document.getElementById('periodo_id');
    const nota = document.getElementById('nota_final');
    const preview = document.getElementById('previewNota');
    
    if (estudiante.value && asignatura.value && periodo.value && nota.value) {
        document.getElementById('preview-estudiante').textContent = 
            estudiante.options[estudiante.selectedIndex].text;
        document.getElementById('preview-asignatura').textContent = 
            asignatura.options[asignatura.selectedIndex].text;
        document.getElementById('preview-periodo').textContent = 
            periodo.options[periodo.selectedIndex].text;
        document.getElementById('preview-nota').textContent = nota.value;
        
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

function limpiarFormulario() {
    if (confirm('¿Estás seguro de que deseas limpiar el formulario?')) {
        document.getElementById('formCrearNota').reset();
        document.getElementById('estado_nota').innerHTML = '';
        document.getElementById('previewNota').style.display = 'none';
        document.getElementById('filtro_grado').selectedIndex = 0;
        filtrarEstudiantes();
    }
}

// Event listeners
document.getElementById('estudiante_id').addEventListener('change', actualizarPreview);
document.getElementById('asignatura_id').addEventListener('change', actualizarPreview);
document.getElementById('periodo_id').addEventListener('change', actualizarPreview);
document.getElementById('nota_final').addEventListener('input', actualizarEstadoNota);

// Validación en tiempo real
document.getElementById('nota_final').addEventListener('input', function() {
    const valor = parseFloat(this.value);
    if (valor > 5) {
        this.value = 5;
    } else if (valor < 0) {
        this.value = 0;
    }
});
</script>