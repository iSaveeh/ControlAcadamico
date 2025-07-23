<?php
session_start();
session_unset(); // Elimina todas las variables de sesión
session_destroy(); // Destruye la sesión
header('Location: ../screens/login.php'); // Redirige al login o página de inicio
exit();
