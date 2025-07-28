<?php
$rol = $_SESSION['rol'] ?? 'acudiente';

// Simulación de grados y ubicaciones
$grados = [
    '05-1' => '2025 > principal > educaciónBásicaSecundaria > Formal > Tarde',
    '06-2' => '2025 > principal > educaciónBásicaSecundaria > Formal > Mañana'
];

// Simulación de historial por grado
$historial = [
    '05-1' => [
        [
            'tipo' => 'Academica',
            'funcionario' => 'sebastian hernandez rojas',
            'fecha' => '22/julio/2025',
            'texto' => 'Demostró buen desarrollo en su proceso de aprendizaje, trabajando de manera autónoma y eficaz, asumiendo con responsabilidad los compromisos académicos y manifestando respeto hacia los demás, vivenciando los valores que lo habilitan para convivir en sociedad.',
            'color' => 'academica'
        ],
        [
            'tipo' => 'Convivencia',
            'funcionario' => 'sebastian hernandez rojas',
            'fecha' => '22/julio/2025',
            'texto' => 'Demostró buen desarrollo en su proceso de aprendizaje, trabajando de manera autónoma y eficaz, asumiendo con responsabilidad los compromisos académicos y manifestando respeto hacia los demás, vivenciando los valores que lo habilitan para convivir en sociedad.',
            'color' => 'convivencia'
        ],
        [
            'tipo' => 'Disciplinaria',
            'funcionario' => 'sebastian hernandez rojas',
            'fecha' => '22/julio/2025',
            'texto' => 'Demostró buen desarrollo en su proceso de aprendizaje, trabajando de manera autónoma y eficaz, asumiendo con responsabilidad los compromisos académicos y manifestando respeto hacia los demás, vivenciando los valores que lo habilitan para convivir en sociedad.',
            'color' => 'disciplinaria'
        ]
    ],
    '06-2' => [
        [
            'tipo' => 'Disciplinaria',
            'funcionario' => 'sebastian hernandez rojas',
            'fecha' => '15/junio/2025',
            'texto' => 'Incumplió normas de convivencia en clase.',
            'color' => 'disciplinaria'
        ]
    ]
];

// Grado seleccionado (por defecto el primero)
$gradoSeleccionado = $_GET['grado'] ?? array_key_first($grados);
$ubicacion = $grados[$gradoSeleccionado] ?? '';
$observaciones = $historial[$gradoSeleccionado] ?? [];
?>

<link rel="stylesheet" href="../css_modulos/observador.css">

<div class="observador-container">

    <!-- FRAME 1: Filtros -->
    <div class="observador-filtros-box">
        <form method="get" class="observador-form-grado">
            <input type="hidden" name="mod" value="observador_del_estudiante">
            <label for="grado">Grado:</label>
            <select name="grado" id="grado" onchange="this.form.submit()">
                <?php foreach ($grados as $grado => $ubi): ?>
                    <option value="<?= htmlspecialchars($grado) ?>" <?= $grado == $gradoSeleccionado ? 'selected' : '' ?>>
                        <?= htmlspecialchars($grado) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($ubicacion): ?>
                <span class="observador-ubicacion"><?= htmlspecialchars($ubicacion) ?></span>
            <?php endif; ?>
        </form>
    </div>

    <!-- FRAME 2: Resultados -->
    <div class="observador-frame observador-frame-resultados">
        <div class="observador-historial" id="observadorHistorial">
            <div class="observador-header">
                <span>Observador</span>
                <?php if (in_array($rol, ['admin', 'profesor'])): ?>
                    <div class="observador-acciones">
                        <button onclick="window.location.href='../mod/observador_formulario.php'" class="btn-crear">Crear</button>
                        <button class="btn-editar">Editar</button>
                        <button class="btn-eliminar">Eliminar</button>
                    </div>
                <?php endif; ?>
                <div class="observador-buscar">
                    <input type="text" placeholder="Buscar...">
                </div>
            </div>
            <div class="observador-lista">
                <?php if ($observaciones): ?>
                    <?php foreach ($observaciones as $obs): ?>
                        <div class="observador-tarjeta <?= $obs['color'] ?>">
                            <div class="observador-funcionario">
                                <img src="../assets/images/avatar_funcionario.png" alt="Funcionario">
                                <div>
                                    <strong><?= htmlspecialchars($obs['funcionario']) ?></strong><br>
                                    <small><?= htmlspecialchars($obs['fecha']) ?></small>
                                </div>
                            </div>
                            <div class="observador-info">
                                <strong><?= htmlspecialchars($obs['tipo']) ?></strong>
                                <p><?= htmlspecialchars($obs['texto']) ?></p>
                            </div>
                            <button class="btn-detalles">Detalles</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 20px; color: #888;">No hay historial para este grado.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function mostrarHistorial() {
    document.getElementById('observadorHistorial').style.display = 'block';
}
</script>
