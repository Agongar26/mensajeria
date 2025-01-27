<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['alias'])) {
    header("Location: login.php");
    exit();
}

// Verificar si se ha enviado el formulario o los datos por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si se envió el botón de rechazar
    if (isset($_POST['rechazar'])) {
        $rechazarId = $_POST['rechazar'];

        // Redirigir a procesar_solicitud.php con el valor de rechazar como parámetro GET
        header("Location: procesar_solicitud.php" . urlencode($rechazarId));
        exit();
    }
    
    // Verificar si se envió el valor del id
    if (isset($_POST['id'])) {
        $amigoId = $_POST['id'];

        // Redirigir a procesar_amigos.php con el valor del id como parámetro GET
        header("Location: procesar_amigos.php?id=" . urlencode($amigoId));
        exit();
    }
}

// Si no se recibe nada por POST, redirigir a una página de error o al inicio
header("Location: error.php?mensaje=No se enviaron datos válidos");
exit();
?>