<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;1,400;1,700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="contenedor-login">
    <div class="login-box">
      <img src="../images/LogoColegio.png" alt="Escudo" class="logo">

      <form action="../backend/DatabaseHandler.php" method="post" class="form-credenciales" id="formCredenciales">
        <input type="hidden" name="rol" id="rolInput">
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="password" name="contrasena" placeholder="Contraseña" required>
        <button type="submit">Ingresar</button><br>
      </form>

      <?php if (isset($_GET['error'])): ?>
        <div style="color:red; margin-bottom:10px;">
          Usuario, contraseña o rol incorrectos.
        </div>
      <?php endif; ?>

      <div class="botones">
        <button class="btn admin" data-rol="admin">👤<br>ADMIN</button>
        <button class="btn profe" data-rol="profe">👩‍🏫<br>PROFE</button>
        <button class="btn estudiante" data-rol="estudiante">🎓<br>ESTUDIANTE</button>
        <button class="btn padres" data-rol="padres">👪<br>PADRES</button>
      </div>
    </div>
  </div>
  <script>
    // Mostrar el formulario y setear el rol al hacer click en un botón
    document.querySelectorAll('.btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const rol = this.dataset.rol;
        document.getElementById('rolInput').value = rol;
        const form = document.getElementById('formCredenciales');
        form.style.display = 'flex';
        // Quitar clases anteriores de color
        form.classList.remove('form-admin', 'form-profe', 'form-estudiante', 'form-padres');
        // Agregar clase según rol
        form.classList.add('form-' + rol);
      });
    });
  </script>
</body>
</html>
