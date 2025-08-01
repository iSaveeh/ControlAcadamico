4<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Plataforma FocusGrade - Psicopedagógico DuverFreud</title>

  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;1,400;1,700&display=swap" rel="stylesheet">

  <!-- Estilos personalizados -->
  <link rel="stylesheet" href="../css/login.css">

  <!-- JS personalizado -->
  <script src="../js/login.js" defer></script>
</head>

<body>
  <div class="contenedor-login">
    <div class="login-box">
      <img src="../assets/images/LogoColegioSinFondo.png" alt="Escudo" class="logo">

      <!-- Formulario -->
      <form action="../backend/DatabaseHandler.php" method="post" class="form-credenciales" id="formCredenciales">
        <h3 id="rolTitulo" style="display: none;">Rol</h3>
        <input type="hidden" name="rol" id="rolInput">
        <input type="text" name="usuario" placeholder="Usuario" required autocomplete="off">
        <input type="password" name="contrasena" placeholder="Contraseña" required autocomplete="off">
        <button type="submit">Ingresar</button><br>
      </form>

      <!-- Mensaje de error si hay problema de login -->
      <?php
        session_start();
        if (isset($_SESSION['login_error'])) {
            $error = $_SESSION['login_error'];
            unset($_SESSION['login_error']); // Se elimina para que no se repita
        }
        ?>
        <?php if (isset($error)): ?>
          <div id="mensaje-error" style="color: red; margin: 10px 0; text-align: center; font-weight: 500;">
            ❌ <?= htmlspecialchars($error) ?>
          </div>
          <script>
            document.addEventListener("DOMContentLoaded", function () {
              const mensaje = document.getElementById('mensaje-error');
              if (mensaje) {
                mensaje.style.opacity = 1;
                mensaje.style.transition = "opacity 0.8s ease-out";
                setTimeout(() => {
                  mensaje.style.opacity = 0;
                  setTimeout(() => mensaje.remove(), 800);
                }, 1000);
              }
            });
          </script>
        <?php endif; ?>

      <!-- Botones de rol -->
      <div class="botones">
        <button class="btn" data-rol="admin">
          <img src="../assets/icons/login/admin.png" class="imagen" alt="Admin">
        </button>
        <button class="btn" data-rol="profesor">
          <img src="../assets/icons/login/profe.png" class="imagen" alt="Profe">
        </button>
        <button class="btn" data-rol="estudiante">
          <img src="../assets/icons/login/estudiantes.png" class="imagen" alt="Estudiantes">
        </button>
        <button class="btn" data-rol="acudiente">
          <img src="../assets/icons/login/padres.png" class="imagen" alt="Acudiente">
        </button>
      </div>
    </div>
  </div>

  <!-- Boton de la ley Habeas Data -->
  <a href="http://www.secretariasenado.gov.co/senado/basedoc/ley_1581_2012.html" target="_blank" class="ley-datos-btn">
    Ley 1581 de 2012 - Habeas Data
  </a>
</body>
</html>