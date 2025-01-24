<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['alias'])) {
    header("Location: login.php"); // Redirigir al inicio de sesión si no está autenticado
    exit();
}

// Verificar si se han enviado los datos necesarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alias_Usuario'], $_POST['alias_Amigo'], $_POST['action'])) {
    // Conectar a la base de datos
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "mensajeriaweb"; // Nombre de la base de datos
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Comprobar si hay algún error en la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Obtener los datos del formulario
    $aliasAmigo = $_POST['alias_Amigo'];
    $aliasUsuario = $_POST['alias_Usuario'];
    $action = $_POST['action'];

    // Determinar el nuevo estado según la acción
    $nuevoEstado = ($action === 'aprobar') ? 'Aceptada' : 'Rechazada';

    // Actualizar el estado en la base de datos
    $updateQuery = "UPDATE esamigo 
                    SET estado = ? 
                    WHERE alias_Usuario = ? AND alias_Amigo = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sss", $nuevoEstado, $aliasUsuario, $aliasAmigo);

    if ($stmt->execute()) {
        // Redirigir con un mensaje de éxito
        $_SESSION['mensaje'] = "Solicitud actualizada correctamente.";
    } else {
        // Redirigir con un mensaje de error
        $_SESSION['mensaje'] = "Error al procesar la solicitud.";
    }

    // Cerrar la conexión
    $stmt->close();
    $conn->close();

    // Redirigir a la página principal (o donde quieras)
    header("Location: index.php");
    exit();
} else {
    // Redirigir con un mensaje de error si faltan datos
    $_SESSION['mensaje'] = "Datos incompletos para procesar la solicitud.";
    header("Location: index.php");
    exit();
}
?>