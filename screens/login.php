<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">

  <title>Login</title>

  <link rel="stylesheet" href="../css/login.css"> <!-- Conexion de css -->

   <!-- Conexión previa a Google Fonts (mejora rendimiento) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Fuente Inter desde Google Fonts, con soporte para cursiva e intermedios -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;1,400;1,700&display=swap" rel="stylesheet">

</head>
<body>

  <div class="contenedor-login">
    <div class="login-box">
      <img src="../images/LogoColegio.png" alt="Escudo" class="logo">
      <h2>PSICO PEDAGÓGICO<br><span>DUVER FREUD</span></h2>

      <div class="botones">
        <form action="admin.php" method="post">
          <button class="btn admin" type="submit">👤<br>ADMIN</button>
        </form>
        <form action="profe.php" method="post">
          <button class="btn profe" type="submit">👩‍🏫<br>PROFE</button>
        </form>
        <form action="estudiante.php" method="post">
          <button class="btn estudiante" type="submit">🎓<br>ESTUDIANTE</button>
        </form>
        <form action="padres.php" method="post">
          <button class="btn padres" type="submit">👪<br>PADRES</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
