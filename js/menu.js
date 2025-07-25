document.addEventListener('DOMContentLoaded', function () {
    const btnMenu = document.getElementById('btnMenu');
    const contenedor = document.getElementById('contenido-principal');

    if (btnMenu && contenedor) {
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

    // Cierre de sesión (sin cambios)
    const btnCerrar = document.getElementById('btnCerrarSesion');
    const modal = document.getElementById('modalCerrarSesion');
    const cancelar = document.getElementById('cancelarCerrarSesion');
    const confirmar = document.getElementById('confirmarCerrarSesion');

    btnCerrar?.addEventListener('click', () => {
        modal.style.display = 'flex';
    });

    cancelar?.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    confirmar?.addEventListener('click', () => {
        window.location.href = '../php/cerrar_sesion.php';
    });
});
