
<?php
include 'conexion.php';

$boletinId = $_GET['id'] ?? 0;

if (!$boletinId) {
    echo "<p>Boletín no encontrado</p>";
    exit;
}

// Obtener información del boletín
$sqlBoletin = "
    SELECT 
        b.IDBoletin,
        CONCAT(e.Nombre, ' ', e.Apellido) AS NombreCompleto,
        e.IDEstudiante,
        g.NombreGrado,
        p.NombrePeriodo,
        b.PromedioGeneral,
        b.Estado,
        b.FechaGeneracion,
        b.Observaciones
    FROM boletin b
    JOIN estudiante e ON b.IDEstudiante = e.IDEstudiante
    JOIN grados g ON e.IDGrado = g.IDGrado
    JOIN periodos p ON b.IDPeriodo = p.IDPeriodo
    WHERE b.IDBoletin = ?
";

$stmt = $conexion->prepare($sqlBoletin);
$stmt->bind_param("i", $boletinId);
$stmt->execute();
$boletin = $stmt->get_result()->fetch_assoc();

if (!$boletin) {
    echo "<p>Boletín no encontrado</p>";
    exit;
}

// Obtener notas por materia
$sqlNotas = "
    SELECT 
        a.NombreAsignatura,
        AVG(n.NotaFinal) as PromedioMateria,
        COUNT(n.IDNota) as TotalNotas
    FROM notas n
    JOIN asignaturas a ON n.IDAsignatura = a.IDAsignatura
    WHERE n.IDEstudiante = ? AND n.IDPeriodo = ?
    GROUP BY a.IDAsignatura, a.NombreAsignatura
    ORDER BY a.NombreAsignatura
";

$stmt = $conexion->prepare($sqlNotas);
$stmt->bind_param("ii", $boletin['IDEstudiante'], 
    // Necesitamos obtener el IDPeriodo del boletín
    $conexion->query("SELECT IDPeriodo FROM boletin WHERE IDBoletin = {$boletinId}")->fetch_assoc()['IDPeriodo']
);
$stmt->execute();
$notas = $stmt->get_result();
?>

  <!-- Estilos personalizados -->
  <link rel="stylesheet" href="../css_modulos/obtener_boletin.css">

<div class="boletin-completo">
    <div class="boletin-header">
        <h2>🏫 INSTITUCIÓN EDUCATIVA</h2>
        <h3>📋 BOLETÍN DE CALIFICACIONES</h3>
    </div>
    
    <div class="boletin-info">
        <div class="info-estudiante">
            <div class="campo">
                <strong>👨‍🎓 Estudiante:</strong> 
                <?= htmlspecialchars($boletin['NombreCompleto']) ?>
            </div>
            <div class="campo">
                <strong>🆔 ID:</strong> 
                <?= $boletin['IDEstudiante'] ?>
            </div>
            <div class="campo">
                <strong>🎓 Grado:</strong> 
                <?= htmlspecialchars($boletin['NombreGrado']) ?>
            </div>
            <div class="campo">
                <strong>📅 Periodo:</strong> 
                <?= htmlspecialchars($boletin['NombrePeriodo']) ?>
            </div>
            <div class="campo">
                <strong>📊 Promedio General:</strong> 
                <span class="promedio-general <?= ($boletin['PromedioGeneral'] >= 3.0) ? 'aprobado' : 'reprobado' ?>">
                    <?= number_format($boletin['PromedioGeneral'], 2) ?>
                </span>
            </div>
            <div class="campo">
                <strong>✅ Estado:</strong> 
                <span class="estado-final <?= strtolower($boletin['Estado']) ?>">
                    <?= $boletin['Estado'] ?>
                </span>
            </div>
        </div>
    </div>
    
    <div class="tabla-materias-container">
        <h4>📚 Calificaciones por Materia</h4>
        <table class="tabla-materias">
            <thead>
                <tr>
                    <th>Asignatura</th>
                    <th>Promedio</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalMaterias = 0;
                $materiasAprobadas = 0;
                
                if ($notas->num_rows > 0):
                    while ($nota = $notas->fetch_assoc()):
                        $totalMaterias++;
                        $estado = $nota['PromedioMateria'] >= 3.0 ? 'Aprobado' : 'Reprobado';
                        if ($estado == 'Aprobado') $materiasAprobadas++;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($nota['NombreAsignatura']) ?></td>
                        <td>
                            <span class="nota-materia <?= ($nota['PromedioMateria'] >= 3.0) ? 'aprobado' : 'reprobado' ?>">
                                <?= number_format($nota['PromedioMateria'], 2) ?>
                            </span>
                        </td>
                        <td>
                            <span class="estado-materia <?= strtolower($estado) ?>">
                                <?= $estado ?>
                            </span>
                        </td>
                    </tr>
                <?php 
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="3" class="no-notas">No hay calificaciones registradas</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="resumen-academico">
        <h4>📈 Resumen Académico</h4>
        <div class="resumen-grid">
            <div class="resumen-item">
                <span class="numero"><?= $totalMaterias ?></span>
                <span class="label">Total Materias</span>
            </div>
            <div class="resumen-item">
                <span class="numero"><?= $materiasAprobadas ?></span>
                <span class="label">Materias Aprobadas</span>
            </div>
            <div class="resumen-item">
                <span class="numero"><?= ($totalMaterias - $materiasAprobadas) ?></span>
                <span class="label">Materias Reprobadas</span>
            </div>
            <div class="resumen-item">
                <span class="numero"><?= $totalMaterias > 0 ? round(($materiasAprobadas / $totalMaterias) * 100) : 0 ?>%</span>
                <span class="label">Porcentaje Aprobación</span>
            </div>
        </div>
    </div>
    
    <?php if ($boletin['Observaciones']): ?>
    <div class="observaciones">
        <h4>💭 Observaciones</h4>
        <p><?= nl2br(htmlspecialchars($boletin['Observaciones'])) ?></p>
    </div>
    <?php endif; ?>
    
    <div class="boletin-footer">
        <p><strong>📅 Fecha de generación:</strong> <?= date('d/m/Y H:i', strtotime($boletin['FechaGeneracion'])) ?></p>
    </div>
</div>