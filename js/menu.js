document.addEventListener("DOMContentLoaded", function () {
    const btnMenu = document.getElementById("btnMenu");
    const panelMenu = document.getElementById("panel-menu");

    // Detectar el rol actual desde un atributo data-rol
    const rol = document.body.dataset.rol; // Asegúrate de tener <body data-rol="admin"> en tu HTML

    btnMenu.addEventListener("click", function () {
        if (panelMenu.classList.contains("activo")) {
            panelMenu.classList.remove("activo");
            panelMenu.innerHTML = "";
        } else {
            fetch("../php/menu_modular.php")
                .then(response => response.text())
                .then(data => {
                    panelMenu.innerHTML = data;
                    panelMenu.classList.add("activo");

                    // Detectar clic en los botones del menú
                    const botones = panelMenu.querySelectorAll(".btn-opcion");
                    botones.forEach(boton => {
                        boton.addEventListener("click", function () {
                            const modulo = boton.textContent.trim();

                            const archivo = modulo
                                .toLowerCase()
                                .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // sin tildes
                                .replace(/\s+/g, "_") + ".php"; // espacios por "_"

                            // Cargar desde carpeta de su rol
                            const ruta = `../mod/${archivo}`;

                            fetch(ruta)
                                .then(res => {
                                    if (!res.ok) throw new Error("No se encontró el módulo.");
                                    return res.text();
                                })
                                .then(html => {
                                    panelMenu.innerHTML = html;
                                })
                                .catch(error => {
                                    panelMenu.innerHTML = `<p style="padding: 1rem;">No se pudo cargar el módulo: <strong>${modulo}</strong></p>`;
                                    console.error(error);
                                });
                        });
                    });
                })
                .catch(error => {
                    panelMenu.innerHTML = "<p>Error al cargar el menú.</p>";
                });
        }
    });

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
