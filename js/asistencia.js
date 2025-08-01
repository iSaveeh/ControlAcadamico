(function() {
    // Esta función encapsula todo para evitar el error de redeclaración.

    const materiaSelect = document.getElementById('materia-select');
    const gradoSelect = document.getElementById('grado-select');
    const alumnosBody = document.getElementById('lista-alumnos-body');
    const guardarBtn = document.getElementById('guardar-asistencia-btn');

    // Si los elementos no existen, detenemos el script para evitar errores.
    if (!materiaSelect || !gradoSelect || !alumnosBody || !guardarBtn) {
        return;
    }

    function cargarOpciones() {
        fetch('../backend/api_asistencia.php?accion=get_materias_profesor')
            .then(response => response.json())
            .then(materias => {
                // Limpiamos opciones anteriores, excepto la primera
                materiaSelect.innerHTML = '<option value="">Seleccionar Materia</option>';
                materias.forEach(materia => {
                    const option = document.createElement('option');
                    option.value = materia.IDAsignatura;
                    option.textContent = materia.NombreAsignatura;
                    materiaSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error al cargar materias:', error));

        fetch('../backend/api_asistencia.php?accion=get_grados_profesor')
            .then(response => response.json())
            .then(grados => {
                // Limpiamos opciones anteriores, excepto la primera
                gradoSelect.innerHTML = '<option value="">Seleccionar Nivel</option>';
                grados.forEach(grado => {
                    const option = document.createElement('option');
                    option.value = grado.IDGrado;
                    option.textContent = grado.NombreGrado;
                    gradoSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error al cargar grados:', error));
    }

    const cargarAlumnos = () => {
        const idGrado = gradoSelect.value;
        const idMateria = materiaSelect.value;

        if (!idGrado || !idMateria) {
            alumnosBody.innerHTML = '<tr><td colspan="4">Seleccione una materia y un nivel para ver la lista.</td></tr>';
            return;
        }

        alumnosBody.innerHTML = '<tr><td colspan="4">Cargando...</td></tr>';

        fetch(`../backend/api_asistencia.php?accion=get_alumnos_por_grado&idGrado=${idGrado}`)
            .then(response => response.json())
            .then(alumnos => {
                alumnosBody.innerHTML = ''; 
                if (alumnos.length === 0) {
                     alumnosBody.innerHTML = '<tr><td colspan="4">No hay alumnos registrados en este nivel.</td></tr>';
                     return;
                }
                
                alumnos.forEach(alumno => {
                    const row = document.createElement('tr');
                    row.dataset.idEstudiante = alumno.IDEstudiante;
                    row.innerHTML = `
                        <td>${alumno.Apellido} ${alumno.Nombre}</td>
                        <td><input type="radio" name="asistencia-${alumno.IDEstudiante}" value="Presente" checked></td>
                        <td><input type="radio" name="asistencia-${alumno.IDEstudiante}" value="Ausente"></td>
                        <td><input type="radio" name="asistencia-${alumno.IDEstudiante}" value="Justificado"></td>
                    `;
                    alumnosBody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error al cargar alumnos:', error);
                alumnosBody.innerHTML = '<tr><td colspan="4">Error al cargar la lista de alumnos.</td></tr>';
            });
    };
    
    guardarBtn.addEventListener('click', function() {
        const filas = alumnosBody.querySelectorAll('tr');
        const asistencias = [];
        
        filas.forEach(fila => {
            const idEstudiante = fila.dataset.idEstudiante;
            if (idEstudiante) {
                const estadoSeleccionado = fila.querySelector('input[type="radio"]:checked');
                asistencias.push({
                    idEstudiante: idEstudiante,
                    estado: estadoSeleccionado.value
                });
            }
        });

        if (asistencias.length > 0) {
            const dataParaGuardar = {
                idMateria: materiaSelect.value,
                idGrado: gradoSelect.value,
                asistencias: asistencias
            };

            fetch('../backend/api_asistencia.php?accion=guardar_asistencia', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataParaGuardar)
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    alert(result.message);
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error al guardar la asistencia:', error);
                alert('Ocurrió un error de conexión al intentar guardar.');
            });
        }
    });

    gradoSelect.addEventListener('change', cargarAlumnos);
    materiaSelect.addEventListener('change', cargarAlumnos);

    cargarOpciones();

})(); // Los paréntesis al final ejecutan la función inmediatamente.