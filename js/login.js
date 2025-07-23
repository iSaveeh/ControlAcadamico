// Constante para los roles
const nombresRol = {
  admin: 'Administrador',
  profesor: 'Profesor',
  estudiante: 'Estudiante',
  acudiente: 'Padres de familia'
};

// Mostrar el formulario y setear el rol al hacer click en un botón
document.querySelectorAll('.btn').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const rol = this.dataset.rol;
    document.getElementById('rolInput').value = rol;

    const form = document.getElementById('formCredenciales');
    form.style.display = 'flex';

    // Quitar clases anteriores de color
    form.classList.remove('form-admin', 'form-profesor', 'form-estudiante', 'form-acudiente');
    // Agregar clase según rol
    form.classList.add('form-' + rol);

    // Mostrar y actualizar el h3 dinamico
    const rolTitulo = document.getElementById('rolTitulo');
    rolTitulo.style.display = 'block';
    rolTitulo.textContent = nombresRol[rol];
  });
});