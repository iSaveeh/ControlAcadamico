<?php
session_start();

// Redirigir si el usuario no está logueado
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['rol'])) { // Ajusta 'usuario_id' si usas otro nombre para el ID en la sesión
    header('Location: login.php'); // Redirige a login.php (está en la misma carpeta screens/)
    exit();
}

$rol = $_SESSION['rol'] ?? '';
$datos = $_SESSION['datos'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Plataforma Duver Freud FG</title>

    <!-- Hojas de estilo -->
    <link rel="stylesheet" href="../css/general.css">
    <link rel="stylesheet" href="../css/menu.css">

    <?php
// Incluir el CSS específico del módulo si existe
// Asegúrate de que $modulo ya esté definido si se ha cargado un módulo
$current_modulo_name = $_GET['mod'] ?? ''; // Obtener el nombre del módulo de la URL
if (!empty($current_modulo_name)) {
    $css_modulo_path = '../css_modulos/' . basename($current_modulo_name) . '.css';
    if (file_exists($css_modulo_path)) {
        echo '<link rel="stylesheet" href="' . $css_modulo_path . '">';
    }
}
?>

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;1,400;1,700&display=swap" rel="stylesheet">
</head>

<body data-rol="<?= $_SESSION['rol'] ?>">

    <!-- NAVBAR -->
    <div class="contenedor-navbar">
        <div class="navbar">
            <div class="logo-colegio-text">
                <img src="../assets/images/LogoColegio.png" class="logo-colegio" alt="Logo Colegio">
                <div class="titulo">
                    <div class="superior">PsicoPedagógico</div>
                    <div class="inferior">Duver Freud</div>
                </div>
            </div>

            <div class="navbar-botones">
                <div class="separador"></div>

                <button class="btn-navbar">
                    <img src="../assets/icons/notis.png" class="icono-navbar" alt="Notificaciones">
                    <span class="texto-navbar">Notificaciones</span>
                </button>

                <div class="separador"></div>

                <button class="btn-navbar">
                    <img src="../assets/icons/perfil.png" class="icono-navbar" alt="Perfil">
                    <span class="texto-navbar perfil-usuario">
                        <?= ($datos['Nombre'] ?? '') . ' ' . ($datos['Apellido'] ?? '') ?>
                    </span>
                </button>

                <div class="separador"></div>

                <button class="btn-navbar" id="btnCerrarSesion">
                    <img src="../assets/icons/cs.png" class="icono-navbar" alt="Cerrar sesión">
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL -->
    <div class="modal" id="modalCerrarSesion">
        <div class="modal-contenido">
            <h2>¿Estás seguro de cerrar sesión?</h2>
            <div class="modal-botones">
                <button class="btn-si" id="confirmarCerrarSesion">Sí</button>
                <button class="btn-no" id="cancelarCerrarSesion">No</button>
            </div>
        </div>
    </div>

    <!-- AGRUPA TODO EN UN CONTENEDOR FLEXIBLE -->
    <div class="layout-container">
        <!-- MENÚ LATERAL -->
        <div class="menu-lateral" id="menulateral">
            <div class="perfil-lateral">
                <img src="../assets/icons/perfil.png" alt="Foto de perfil" class="foto-perfil">
                <div class="info-usuario">
                    <h3><?= ($datos['Nombre'] ?? '') . ' ' . ($datos['Apellido'] ?? '') ?></h3>
                    <span class="rol"><?= ucfirst($rol) ?></span>
                    <div class="estado-en-linea">
                        <div class="punto-verde"></div> En línea
                    </div>
                </div>
            </div>

            <div class="opciones-menu-lateral">
                <!-- Boton cargado por JS -->
                <button class="item-menu" id="btnMenu">
                    <i class="icono fas fa-sitemap"></i> Menú
                </button>

                <!-- Resto de enlaces normales -->

                <!-- Logo Inferior -->
                <div class="logo-inferior">
                    <img src="../assets/images/FocusGrade.png" alt="FocusGrade">
                </div>
            </div>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="contenido-principal" id="contenido-principal">
            <?php
                if (isset($_GET['mod'])) {
                    $modulo = basename($_GET['mod']); // evita rutas fuera del proyecto
                    $archivo = '../mod/' . $modulo . '.php'; // Ruta corregida
                    if (file_exists($archivo)) {
                        include $archivo;
                    } else {
                        echo "<p>Módulo no encontrado: $modulo</p>";
                    }
                } else {
                    echo "<p>Bienvenido a Focus Grade</p>";
                }
            ?>
        </div>
    </div>

    <!-- JS -->
    <script src="../js/menu.js"></script>
    <?php
        // Incluir el JS específico del módulo si existe
        $current_modulo_name = $_GET['mod'] ?? '';
        if (!empty($current_modulo_name)) {
            $js_modulo_path = '../js/' . basename($current_modulo_name) . '.js';
            if (file_exists($js_modulo_path)) {
                echo '<script src="' . $js_modulo_path . '"></script>';
            }
        }
    ?>
</body>
</html>
