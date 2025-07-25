<?php
session_start();
$rol = $_SESSION['rol'] ?? '';

// Módulos por rol
$modulos = [
    'admin' => [
        'Control de Notas',
        'Boletines Académicos',
        'Registro de Asistencias',
        'Planes de Mejoramiento Académico',
        'Observador del Estudiante',
        'Registro Integral',
        'Seguimiento de Notas por Periodo y Asignatura',
        'Autoevaluaciones por Periodo',
        'Ficha de Matrícula del Estudiante',
        'Perfil del Estudiante',
        'Listado de Compañeros de Clase',
        'Visualización de Profesores',
        'Horario de Clase',
        'Estado de Salud',
        'Citaciones Académicas',
        'Circulares Descargables',
        'Notificaciones de Matrícula',
        'Agenda Escolar',
        'Encuestas Institucionales',
        'Votaciones del Gobierno Escolar',
        'Agenda de Eventos del Colegio',
        'Pizarrón de Tareas',
        'Visualización de Calendario Académico'
    ],
    'profesor' => [
        'Control de Notas',
        'Registro de Asistencias',
        'Planes de Mejoramiento Académico',
        'Observador del Estudiante',
        'Seguimiento de Notas por Periodo y Asignatura',
        'Autoevaluaciones por Periodo',
        'Agenda Escolar',
        'Pizarrón de Tareas'
    ],
    'estudiante' => [
        'Boletines Académicos',
        'Perfil del Estudiante',
        'Listado de Compañeros de Clase',
        'Visualización de Profesores',
        'Horario de Clase',
        'Estado de Salud',
        'Citaciones Académicas',
        'Agenda Escolar',
        'Encuestas Institucionales',
        'Visualización de Calendario Académico'
    ],
    'acudiente' => [
        'Boletines Académicos',
        'Observador del Estudiante',
        'Registro Integral',
        'Citaciones Académicas',
        'Estado de Salud',
        'Encuestas Institucionales',
        'Visualización de Calendario Académico'
    ]
];

// Función para convertir nombre de módulo a nombre de archivo
function convertirNombreArchivo($nombre) {
    $nombre = strtolower($nombre);
    $nombre = str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ñ', ' '],
        ['a', 'e', 'i', 'o', 'u', 'n', '_'],
        $nombre
    );
    return $nombre;
}

$modulosVisibles = $modulos[$rol] ?? [];
?>

<div class="menu-modular">
    <h2 class="titulo-modular">Módulos disponibles</h2>
    <div class="grid-modulos">
        <?php foreach ($modulosVisibles as $modulo): 
            $archivo = convertirNombreArchivo($modulo);
        ?>
            <a href="?mod=<?= $archivo ?>" class="btn-opcion">
                <img src="../assets/images/modulos/<?= $archivo ?>.png" alt="<?= htmlspecialchars($modulo) ?>">
                <span><?= htmlspecialchars($modulo) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
