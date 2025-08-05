// Crear y mostrar el modal para editar una nota
function editarNota(idNota, estudiante, nota, observaciones) {
    // Elimina cualquier modal abierto previamente
    cerrarModal('modalEditar');

    // Crear el modal
    const modalHTML = `
    <div class="modal show" id="modalEditar">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Editar Nota</h3>
                <span class="close" onclick="cerrarModal('modalEditar')">&times;</span>
            </div>
            <form id="formEditarNota">
                <input type="hidden" name="IDNota" value="${idNota}">
                <input type="hidden" name="action" value="editar_nota">
                <div class="form-grupo">
                    <label>👨‍🎓 Estudiante:</label>
                    <input type="text" class="input-readonly" value="${estudiante}" readonly>
                </div>
                <div class="form-grupo">
                    <label>📝 Nueva Nota:</label>
                    <input type="number" name="NotaFinal" step="0.1" min="0" max="5" value="${nota}" required class="input-nota">
                </div>
                <div class="form-grupo">
                    <label>💬 Observaciones:</label>
                    <textarea name="Observaciones" rows="4">${observaciones || ''}</textarea>
                </div>
                <div class="modal-acciones">
                    <button type="submit" class="btn-guardar">💾 Guardar</button>
                    <button type="button" class="btn-cancelar" onclick="cerrarModal('modalEditar')">❌ Cancelar</button>
                </div>
            </form>
        </div>
    </div>`;

    // Insertar en el DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Manejar envío
    document.getElementById('formEditarNota').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('../backend/procesar_notas.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarMensaje(data.message, 'success');
                cerrarModal('modalEditar');
                setTimeout(() => location.reload(), 1000);
            } else {
                mostrarMensaje(data.message, 'error');
            }
        })
        .catch(() => {
            mostrarMensaje('Error al conectar con el servidor', 'error');
        });
    });
}

// Mostrar el modal de confirmación para eliminar nota
function confirmarEliminacion(idNota, estudiante) {
    cerrarModal('modalEliminar');

    const modalHTML = `
    <div class="modal show" id="modalEliminar">
        <div class="modal-content modal-eliminar">
            <div class="modal-header">
                <h3>🗑️ Confirmar Eliminación</h3>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de eliminar la nota de <strong>${estudiante}</strong>?</p>
                <p class="advertencia">⚠️ Esta acción no se puede deshacer</p>
            </div>
            <div class="modal-acciones">
                <button class="btn-cancelar" onclick="cerrarModal('modalEliminar')">❌ Cancelar</button>
                <button class="btn-eliminar" onclick="eliminarNota(${idNota})">✅ Sí, eliminar</button>
            </div>
        </div>
    </div>`;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

// Cierra y elimina un modal por ID
function cerrarModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.remove();
}

// Eliminar nota (con fetch simulado, puedes reemplazarlo)
function eliminarNota(idNota) {
    const formData = new FormData();
    formData.append('IDNota', idNota);
    formData.append('action', 'eliminar_nota');

    fetch('../backend/procesar_notas.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            mostrarMensaje(data.message, 'success');
            cerrarModal('modalEliminar');
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrarMensaje(data.message, 'error');
        }
    })
    .catch(() => {
        mostrarMensaje('Error de conexión al eliminar', 'error');
    });
}

// Muestra un mensaje flotante tipo toast
function mostrarMensaje(texto, tipo = 'success') {
    const div = document.createElement('div');
    div.className = `mensaje-flotante ${tipo}`;
    div.innerHTML = `<span>${tipo === 'success' ? '✅' : '❌'}</span> ${texto}`;

    document.body.appendChild(div);
    setTimeout(() => div.classList.add('show'), 100);
    setTimeout(() => {
        div.classList.remove('show');
        setTimeout(() => div.remove(), 300);
    }, 3000);
}
