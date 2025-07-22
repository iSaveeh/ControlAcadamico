<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Plataforma Duver Freud FG</title>

    <!-- Hojas de estilo -->
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/menulateral.css">
    <link rel="stylesheet" href="../css/general.css">
    <link rel="stylesheet" href="../css/menu.css">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;1,400;1,700&display=swap" rel="stylesheet">
</head>

<body>

    <!-- NAVBAR -->
    <div class="contenedor-navbar">
        <div class="navbar">
            <div class="logo-colegio-text">
                <img src="../images/LogoColegio.png" class="logo-colegio" alt="Logo Colegio">
                <div class="titulo">
                    <div class="superior">PsicoPedagógico</div>
                    <div class="inferior">Duver Freud</div>
                </div>
            </div>

            <div class="navbar-botones">
                <div class="separador"></div>

                <button class="btn-navbar">
                    <img src="../images/icons/notis.png" class="icono-navbar" alt="Notificaciones">
                    <span class="texto-navbar">Notificaciones</span>
                </button>

                <div class="separador"></div>

                <button class="btn-navbar">
                    <img src="../images/icons/perfil.png" class="icono-navbar" alt="Perfil">
                    <span class="texto-navbar perfil-usuario">Sebastian Iza</span>
                </button>

                <div class="separador"></div>

                <button class="btn-navbar" id="btnCerrarSesion">
                    <img src="../images/icons/cs.png" class="icono-navbar" alt="Cerrar sesión">
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL -->
    <div class="modal" id="modalCerrarSesion">
        <div class="modal-contenido">
            <h2>¿Estás seguro de cerrar sesión?</h2>
            <div class="modal-botones">
                <button class="btn-si">Sí</button>
                <button class="btn-no" id="cancelarCerrarSesion">No</button>
            </div>
        </div>
    </div>

    <!-- MENÚ LATERAL -->
    <div class="menu-lateral" id="menulateral">
        <div class="perfil-lateral">
            <img src="../images/icons/perfil.png" alt="Foto de perfil" class="foto-perfil">
            <div class="info-usuario">
                <h3>Nombre</h3>
                <span class="rol">Admin</span>
                <div class="estado-en-linea">
                    <div class="punto-verde"></div> En línea
                </div>
            </div>
        </div>

        <div class="opciones-menu-lateral">
            <button class="item-menu"><i class="icono fas fa-home"></i> Inicio</button>
            <button class="item-menu" onclick="mostrarPanel('panel-menu')"><i class="icono fas fa-sitemap"></i> Menu</button>
            <button class="item-menu"><i class="icono fas fa-envelope"></i> Correo</button>
            <button class="item-menu"><i class="icono fas fa-calendar-alt"></i> Calendario</button>
            <button class="item-menu"><i class="icono fas fa-book"></i> Manual Convivencia / SIEE</button>
            <button class="item-menu"><i class="icono fas fa-comment-dots"></i> PQR</button>
            <button class="item-menu"><i class="icono fas fa-download"></i> Descargas</button>
            <button class="item-menu"><i class="icono fas fa-question-circle"></i> Ayuda</button>

            <!-- Logo Inferior -->
            <div class="logo-inferior">
                <img src="../images/FocusGrade.png" alt="FocusGrade">
            </div>
        </div>
    </div>

    <!-- PANEL QUE CAMBIA SEGÚN BOTONES -->
    <div class="contenido-principal">
        <div id="panel-menu" class="panel-contenido">
            <h2>Opciones del Menú</h2>
            <div class="submenu-botones">
                <button class="btn-submenu">Estudiantes</button>
                <button class="btn-submenu">Docentes</button>
                <button class="btn-submenu">Materias</button>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script>
        // Modal Cerrar Sesión
        const btnCerrar = document.getElementById('btnCerrarSesion');
        const modal = document.getElementById('modalCerrarSesion');
        const cancelar = document.getElementById('cancelarCerrarSesion');

        btnCerrar.addEventListener('click', () => {
            modal.style.display = 'flex';
        });

        cancelar.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Mostrar panel dinámico
        function mostrarPanel(id) {
            document.querySelectorAll('.panel-contenido').forEach(panel => {
                panel.classList.remove('activo');
            });

            const panel = document.getElementById(id);
            if (panel) {
                panel.classList.add('activo');
            }
        }
    </script>
</body>
</html>
