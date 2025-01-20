<?php
session_start(); // Inicia la sesión

// Si el usuario está logueado, muestra el mensaje de bienvenida
if (isset($_SESSION['alias'])) {
    echo "<h1>Bienvenido, " . $_SESSION['alias'] . "!</h1>";
    echo '<a href="logout.php">Cerrar sesión</a>';
} else {
    // Si no está logueado, muestra el formulario de login
    include("login.html");
}
?>