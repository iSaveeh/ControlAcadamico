<?php
session_start();
$rol = $_SESSION['rol'] ?? '';

// Módulos por rol
$modulos = [
    'admin' => [
        'Control de Notas',
        'Boletines Académicos',
        'Observador del Estudiante',
        'Ficha de Matrícula del Estudiante',
        'Perfil del Estudiante',
        'Visualización de Profesores',
        'Registro Usuario',
        'Asignacion de Horarios'
    ],
    'profesor' => [
        'Control de Notas',
        'Registro de Asistencias',
        'Observador del Estudiante',
        'Pizarrón de Tareas',
    ],
    'estudiante' => [
        'Boletines Académicos',
        'Horario de Clase',
        'Pizarrón de Tareas',
    ],
    'acudiente' => [
        'Boletines Académicos',
        'Observador del Estudiante',
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
            
            <?php if ($modulo == 'Registro de Asistencias'): ?>
                
                <a href="#" class="btn-opcion btn-cargar-asistencia">
                    <img src="../assets/images/modulos/<?= $archivo ?>.png" alt="<?= htmlspecialchars($modulo) ?>">
                    <span><?= htmlspecialchars($modulo) ?></span>
                </a>

            <?php else: ?>
                
                <a href="?mod=<?= $archivo ?>" class="btn-opcion">
                    <img src="../assets/images/modulos/<?= $archivo ?>.png" alt="<?= htmlspecialchars($modulo) ?>">
                    <span><?= htmlspecialchars($modulo) ?></span>
                </a>

            <?php endif; ?>
            <?php endforeach; ?>
    </div>
</div>
