document.addEventListener('DOMContentLoaded', function () {
    const contenedor = document.getElementById('contenido-principal');

    // --- Lógica para el botón del Menú Principal ---
    const btnMenu = document.getElementById('btnMenu');
    if (btnMenu) {
        btnMenu.addEventListener('click', () => {
            fetch('../php/menu_modular.php')
                .then(response => {
                    if (!response.ok) throw new Error('Error al cargar el menú');
                    return response.text();
                })
                .then(html => {
                    contenedor.innerHTML = html;
                })
                .catch(error => {
                    contenedor.innerHTML = '<p>Error al cargar el menú.</p>';
                    console.error(error);
                });
        });
    }

    // --- Lógica para los botones de "Asistencia" ---
    document.body.addEventListener('click', function(event) {
        if (event.target.closest('.btn-cargar-asistencia')) {
            event.preventDefault();
            
            fetch('../mod/asistencia.php')
                .then(response => response.ok ? response.text() : Promise.reject('Error al cargar.'))
                .then(html => {
                    contenedor.innerHTML = html;
                    const scriptTag = contenedor.querySelector('script');
                    if (scriptTag) {
                        const newScript = document.createElement('script');
                        newScript.src = scriptTag.src;
                        document.body.appendChild(newScript).remove();
                    }
                })
                .catch(error => {
                    contenedor.innerHTML = '<p>Error al cargar el módulo de asistencia.</p>';
                    console.error(error);
                });
        }
    });

    // --- Lógica completa para el modal de Cerrar Sesión ---
    const btnCerrar = document.getElementById('btnCerrarSesion');
    const modal = document.getElementById('modalCerrarSesion');
    const cancelar = document.getElementById('cancelarCerrarSesion');
    const confirmar = document.getElementById('confirmarCerrarSesion');

    if (btnCerrar) {
        btnCerrar.addEventListener('click', () => {
            if(modal) modal.style.display = 'flex';
        });
    }

    if (cancelar) {
        cancelar.addEventListener('click', () => {
            if(modal) modal.style.display = 'none';
        });
    }

    if (confirmar) {
        confirmar.addEventListener('click', () => {
            window.location.href = '../php/cerrar_sesion.php';
        });
    }

    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            if(modal) modal.style.display = 'none';
        }
    });
});