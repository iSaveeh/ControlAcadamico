<?php
include 'conexion.php';

$boletinId = $_GET['id'] ?? 0;

if (!$boletinId) {
    echo "<p>Boletín no encontrado</p>";
    exit;
}

// Misma lógica que obtener_boletin.php pero con estilos de impresión
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

// Obtener IDPeriodo para las notas
$periodoQuery = $conexion->query("SELECT IDPeriodo FROM boletin WHERE IDBoletin = {$boletinId}");
$periodoId = $periodoQuery->fetch_assoc()['IDPeriodo'];

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
$stmt->bind_param("ii", $boletin['IDEstudiante'], $periodoId);
$stmt->execute();
$notas = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boletín - <?= htmlspecialchars($boletin['NombreCompleto']) ?></title>
    <style>
        @media print {
            body { 
                margin: 0; 
                font-family: Arial, sans-serif;
                font-size: 12pt;
            }
            
            .no-print { display: none !important; }
            
            .boletin-imprimible {
                width: 100%;
                max-width: none;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: white;
        }
        
        .boletin-imprimible {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
        }
        
        .header-institucional {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header-institucional h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        
        .header-institucional h2 {
            margin: 10px 0 0 0;
            font-size: 18px;
            color: #666;
        }
        
        .info-general {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        
        .info-fila {
            display: table-row;
        }
        
        .info-celda {
            display: table-cell;
            padding: 8px;
            border: 1px solid #ddd;
            vertical-align: middle;
        }
        
        .info-label {
            background: #f5f5f5;
            font-weight: bold;
            width: 25%;
        }
        
        .tabla-notas {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .tabla-notas th,
        .tabla-notas td {
            border: 1px solid #333;
            padding: 10px;
            text-align: center;
        }
        
        .tabla-notas th {
            background: #f5f5f5;
            font-weight: bold;
        }
        
        .tabla-notas .materia {
            text-align: left;
        }
        
        .aprobado {
            background: #e8f5e8 !important;
            font-weight: bold;
        }
        
        .reprobado {
            background: #f5e8e8 !important;
            font-weight: bold;
        }
        
        .resumen-final {
            margin-top: 30px;
            border: 2px solid #333;
            padding: 20px;
        }
        
        .resumen-final h3 {
            margin-top: 0;
            text-align: center;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
        }
        
        .stats-row {
            display: table-row;
        }
        
        .stats-cell {
            display: table-cell;
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        
        .stats-label {
            background: #f5f5f5;
            font-weight: bold;
        }
        
        .observaciones-box {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            background: #fafafa;
        }
        
        .firma-section {
            margin-top: 50px;
            display: table;
            width: 100%;
        }
        
        .firma-cell {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 20px;
        }
        
        .firma-linea {
            border-bottom: 1px solid #333;
            width: 200px;
            margin: 0 auto 10px auto;
            height: 30px;
        }
        
        .btn-imprimir {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
        .btn-imprimir:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <button class="btn-imprimir no-print" onclick="window.print()">🖨️ Imprimir</button>
    
    <div class="boletin-imprimible">
        <div class="header-institucional">
            <h1>🏫 PSICOPEDAGOGICO DUVER FREUD</h1>
            <h2>BOLETÍN DE CALIFICACIONES</h2>
            <p>Periodo Académico <?= htmlspecialchars($boletin['NombrePeriodo']) ?></p>
        </div>
        
        <div class="info-general">
            <div class="info-fila">
                <div class="info-celda info-label">Estudiante:</div>
                <div class="info-celda"><?= htmlspecialchars($boletin['NombreCompleto']) ?></div>
                <div class="info-celda info-label">ID:</div>
                <div class="info-celda"><?= $boletin['IDEstudiante'] ?></div>
            </div>
            <div class="info-fila">
                <div class="info-celda info-label">Grado:</div>
                <div class="info-celda"><?= htmlspecialchars($boletin['NombreGrado']) ?></div>
                <div class="info-celda info-label">Fecha:</div>
                <div class="info-celda"><?= date('d/m/Y', strtotime($boletin['FechaGeneracion'])) ?></div>
            </div>
        </div>
        
        <table class="tabla-notas">
            <thead>
                <tr>
                    <th style="width: 50%">ASIGNATURA</th>
                    <th style="width: 20%">CALIFICACIÓN</th>
                    <th style="width: 30%">ESTADO</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalMaterias = 0;
                $materiasAprobadas = 0;
                
                if ($notas->num_rows > 0):
                    while ($nota = $notas->fetch_assoc()):
                        $totalMaterias++;
                        $estado = $nota['PromedioMateria'] >= 3.0 ? 'APROBADO' : 'REPROBADO';
                        $clase = $nota['PromedioMateria'] >= 3.0 ? 'aprobado' : 'reprobado';
                        if ($estado == 'APROBADO') $materiasAprobadas++;
                ?>
                    <tr>
                        <td class="materia"><?= htmlspecialchars($nota['NombreAsignatura']) ?></td>
                        <td class="<?= $clase ?>"><?= number_format($nota['PromedioMateria'], 2) ?></td>
                        <td class="<?= $clase ?>"><?= $estado ?></td>
                    </tr>
                <?php 
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="3">NO HAY CALIFICACIONES REGISTRADAS</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="resumen-final">
            <h3>RESUMEN ACADÉMICO</h3>
            <div class="stats-grid">
                <div class="stats-row">
                    <div class="stats-cell stats-label">PROMEDIO GENERAL</div>
                    <div class="stats-cell <?= ($boletin['PromedioGeneral'] >= 3.0) ? 'aprobado' : 'reprobado' ?>">
                        <?= number_format($boletin['PromedioGeneral'], 2) ?>
                    </div>
                    <div class="stats-cell stats-label">ESTADO FINAL</div>
                    <div class="stats-cell <?= strtolower($boletin['Estado']) ?>">
                        <?= strtoupper($boletin['Estado']) ?>
                    </div>
                </div>
                <div class="stats-row">
                    <div class="stats-cell stats-label">MATERIAS CURSADAS</div>
                    <div class="stats-cell"><?= $totalMaterias ?></div>
                    <div class="stats-cell stats-label">MATERIAS APROBADAS</div>
                    <div class="stats-cell"><?= $materiasAprobadas ?></div>
                </div>
                <div class="stats-row">
                    <div class="stats-cell stats-label">MATERIAS REPROBADAS</div>
                    <div class="stats-cell"><?= ($totalMaterias - $materiasAprobadas) ?></div>
                    <div class="stats-cell stats-label">% APROBACIÓN</div>
                    <div class="stats-cell"><?= $totalMaterias > 0 ? round(($materiasAprobadas / $totalMaterias) * 100) : 0 ?>%</div>
                </div>
            </div>
        </div>
        
        <?php if ($boletin['Observaciones']): ?>
        <div class="observaciones-box">
            <h4>OBSERVACIONES:</h4>
            <p><?= nl2br(htmlspecialchars($boletin['Observaciones'])) ?></p>
        </div>
        <?php endif; ?>
        
        <div class="firma-section">
            <div class="firma-cell">
                <div class="firma-linea"></div>
                <p><strong>DIRECTOR(A)</strong></p>
            </div>
            <div class="firma-cell">
                <div class="firma-linea"></div>
                <p><strong>COORDINADOR(A) ACADÉMICO(A)</strong></p>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-imprimir cuando se abre en nueva ventana
        window.onload = function() {
            if (window.location.search.includes('auto_print=1')) {
                setTimeout(() => window.print(), 500);
            }
        }
    </script>
</body>
</html>