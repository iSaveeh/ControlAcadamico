document.addEventListener('DOMContentLoaded', function() {
    console.count('DOMContentLoaded init'); // Contador para ver si DOMContentLoaded se ejecuta dos veces

    // Referencias a elementos del DOM
    const activityNameToDelete = document.getElementById('activityNameToDelete');
    const btnNuevaTarea = document.getElementById('btnNuevaTarea');
    const modalNuevaTarea = document.getElementById('modalNuevaTarea');
    const closeButton = modalNuevaTarea.querySelector('.close-button');
    const formNuevaActividad = document.getElementById('formNuevaActividad');
    const modalTitle = document.getElementById('modalTitle');
    const idActividadInput = document.getElementById('idActividad');
    const nombreActividadInput = document.getElementById('nombreActividad');
    const descripcionInput = document.getElementById('descripcion');
    const idAsignaturaSelect = document.getElementById('idAsignatura');
    const fechaInput = document.getElementById('fecha');
    const porcentajeInput = document.getElementById('porcentaje');
    const mensajeFormulario = document.getElementById('mensajeFormulario');
    const btnGuardarTarea = document.getElementById('btnGuardarTarea');

    // Modal de confirmación
    const confirmDeleteModal = document.getElementById('confirmDeleteModal');
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');
    const btnCancelDelete = document.getElementById('btnCancelDelete');
    const confirmCloseButton = confirmDeleteModal.querySelector('.close-button');
    let currentActivityIdToDelete = null;

    // Función para abrir el modal de tarea
    function openModal(isEdit = false) {
        modalNuevaTarea.classList.add('mostrar');
        if (!isEdit) {
            formNuevaActividad.reset();
            modalTitle.textContent = 'Nueva Actividad';
            idActividadInput.value = '';
        }
    }

    // Cerrar modal de tarea
    closeButton.addEventListener('click', function() {
        modalNuevaTarea.classList.remove('mostrar');
    });

    // Cerrar modales al hacer clic fuera
    window.addEventListener('click', function(event) {
        if (event.target == modalNuevaTarea) {
            modalNuevaTarea.classList.remove('mostrar');
        }
        if (event.target == confirmDeleteModal) {
            confirmDeleteModal.classList.remove('mostrar');
        }
    });

    // Botón "Nueva Tarea"
    btnNuevaTarea.addEventListener('click', function() {
        openModal(false);
    });

    // Cerrar modal de confirmación
    confirmCloseButton.addEventListener('click', function(event) {
        event.stopPropagation();
        confirmDeleteModal.classList.remove('mostrar');
    });
    btnCancelDelete.addEventListener('click', function(event) {
        event.stopPropagation();
        confirmDeleteModal.classList.remove('mostrar');
    });

    // Confirmar eliminación
    btnConfirmDelete.addEventListener('click', function() {
        if (currentActivityIdToDelete) {
            confirmDeleteModal.classList.remove('mostrar');
            fetch('../backend/eliminar_actividad.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'idActividad=' + currentActivityIdToDelete
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error al eliminar la actividad: ' + (data.message || 'Error desconocido.'));
                }
            })
            .catch(error => {
                console.error('Error en la solicitud de eliminación:', error);
                alert('Ocurrió un error al intentar eliminar la actividad.');
            });
        }
    });

    // Evitar doble adjunción del submit
    if (formNuevaActividad._submitListenerAttached) {
        console.warn('ADVERTENCIA: El event listener de submit ya está adjunto. Posible doble adjunción.');
        return;
    }
    formNuevaActividad._submitListenerAttached = true;

    // Envío del formulario (Crear/Editar)
    formNuevaActividad.addEventListener('submit', function(event) {
        console.count('Formulario enviado');
        event.preventDefault();

        btnGuardarTarea.disabled = true;
        mensajeFormulario.textContent = 'Guardando tarea...';
        mensajeFormulario.style.color = 'blue';

        const formData = new FormData(formNuevaActividad);
        const actividadId = formData.get('idActividad');
        const targetScript = actividadId ? '../backend/editar_actividad.php' : '../backend/crear_actividad.php';

        fetch(targetScript, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => { throw new Error('Error del servidor: ' + text); });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                mensajeFormulario.style.color = 'green';
                mensajeFormulario.textContent = data.message;
                setTimeout(() => {
                    modalNuevaTarea.classList.remove('mostrar');
                    location.reload();
                }, 800);
            } else {
                mensajeFormulario.style.color = 'red';
                mensajeFormulario.textContent = data.message;
                btnGuardarTarea.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mensajeFormulario.style.color = 'red';
            mensajeFormulario.textContent = 'Ocurrió un error al intentar guardar la tarea.';
            btnGuardarTarea.disabled = false;
        });
    });

    // Delegación de eventos para los botones de la tabla
    const tablaActividades = document.querySelector('.actividades-table');
    if (tablaActividades) {
        tablaActividades.addEventListener('click', function(event) {
            const target = event.target;

            // Botón "Borrar"
            if (target.classList.contains('btn-borrar')) {
                event.preventDefault();
                event.stopPropagation();

                const actividadId = target.dataset.id;
                const row = target.closest('tr');
                const nombreActividad = row.querySelector('td:nth-child(3)').textContent;

                currentActivityIdToDelete = actividadId;
                confirmDeleteModal.classList.add('mostrar');
                activityNameToDelete.textContent = nombreActividad;
            }

            // Botón "Editar"
            if (target.classList.contains('btn-editar')) {
                event.preventDefault();
                event.stopPropagation();

                const actividadId = target.dataset.id;
                openModal(true);
                modalTitle.textContent = 'Editar Actividad';
                idActividadInput.value = actividadId;

                fetch('../backend/obtener_actividad.php?id=' + actividadId)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error al obtener los datos de la actividad: ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success && data.actividad) {
                            const actividad = data.actividad;
                            nombreActividadInput.value = actividad.NombreActividad;
                            descripcionInput.value = actividad.Descripcion;
                            idAsignaturaSelect.value = actividad.IDAsignatura;
                            fechaInput.value = actividad.Fecha;
                            porcentajeInput.value = parseFloat(actividad.Porcentaje).toFixed(2).replace('.', ',');
                        } else {
                            mensajeFormulario.style.color = 'red';
                            mensajeFormulario.textContent = data.message || 'No se pudieron cargar los datos de la actividad.';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        mensajeFormulario.style.color = 'red';
                        mensajeFormulario.textContent = 'Ocurrió un error al cargar los datos de la actividad.';
                    });
            }
        });
    }

    // ... (resto de tus event listeners: btn-editar, btn-borrar, etc.) ...
});

// Mostrar modal
modalNuevaTarea.classList.add('mostrar');
// Ocultar modal
modalNuevaTarea.classList.remove('mostrar');

// Mostrar modal de confirmación
confirmDeleteModal.classList.add('mostrar');

// Cerrar modal de confirmación
confirmDeleteModal.classList.remove('mostrar');