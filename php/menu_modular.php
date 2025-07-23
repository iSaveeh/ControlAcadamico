<?php
session_start();

$rol = $_SESSION['rol'] ?? '';

// Definimos los módulos por rol
$modulos_por_rol = [
    'admin' => [
        'Control de notas',
        'Boletines académicos',
        'Registro de asistencias',
        'Planes de mejoramiento académico',
        'Observador del estudiante',
        'Registro integral (académico y convivencia)',
        'Seguimiento de notas por periodo y asignatura',
        'Autoevaluaciones por periodo',
        'Ficha de matrícula del estudiante',
        'Perfil del estudiante',
        'Listado de compañeros de clase',
        'Visualización de profesores',
        'Horario de clase',
        'Estado de salud (encuesta sanitaria)',
        'Bandeja de mensajería institucional',
        'Citaciones académicas',
        'Circulares descargables',
        'Notificaciones de matrícula',
        'PQRSF',
        'Agenda escolar',
        'Encuestas institucionales',
        'Manual de convivencia',
        'Votaciones del gobierno escolar',
        'Agenda de eventos del colegio',
        'Pizarrón de tareas',
        'Visualización de calendario académico'
    ],
    'profesor' => [
        'Control de notas',
        'Boletines académicos',
        'Registro de asistencias',
        'Planes de mejoramiento académico',
        'Observador del estudiante',
        'Seguimiento de notas por periodo y asignatura',
        'Autoevaluaciones por periodo',
        'Citaciones académicas',
        'Bandeja de mensajería institucional'
    ],
    'estudiante' => [
        'Ficha de matrícula del estudiante',
        'Perfil del estudiante',
        'Listado de compañeros de clase',
        'Visualización de profesores',
        'Horario de clase',
        'Estado de salud (encuesta sanitaria)',
        'Bandeja de mensajería institucional',
        'Citaciones académicas',
        'Circulares descargables',
        'Notificaciones de matrícula',
        'PQRSF',
        'Agenda escolar',
        'Encuestas institucionales',
        'Manual de convivencia',
        'Votaciones del gobierno escolar',
        'Agenda de eventos del colegio',
        'Pizarrón de tareas',
        'Visualización de calendario académico'
    ],
    'acudiente' => [
        'Boletines académicos',
        'Observador del estudiante',
        'Registro integral (académico y convivencia)',
        'Seguimiento de notas por periodo y asignatura',
        'Citaciones académicas',
        'Bandeja de mensajería institucional',
        'Estado de salud (encuesta sanitaria)',
        'Notificaciones de matrícula',
        'PQRSF',
        'Circulares descargables',
        'Agenda escolar',
        'Manual de convivencia',
        'Encuestas institucionales',
        'Visualización de calendario académico'
    ]
];

// Seleccionamos los módulos según el rol actual
$modulos = $modulos_por_rol[$rol] ?? [];
?>

<div class="menu-modular">
    <h2>Opciones del menú</h2>

    <?php if (!empty($modulos)): ?>
        <ul>
            <?php foreach ($modulos as $modulo): ?>
                <li><button class="btn-opcion"><?= htmlspecialchars($modulo) ?></button></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p style="padding: 1rem;">No tienes permisos para ver los módulos.</p>
    <?php endif; ?>
</div>
