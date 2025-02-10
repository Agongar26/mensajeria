<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['alias'])) {
    header("Location: login.php"); // Redirigir al inicio de sesión si no está autenticado
    exit();
}

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

// Consulta para agregar el mensaje en la base de datos
$emisor = $_POST['emisor'];
$receptor = $_POST['receptor'];
$mensaje = $_POST['message'];
$fechaActual = date('Y-m-d H:i:s');
$leido = false;
$mensajeQuery = "INSERT INTO mensaje (emisor, receptor, mensaje, fechaHora, leido) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($mensajeQuery);
$stmt->bind_param("sssss", $emisor, $receptor, $mensaje, $fechaActual, $leido);
$stmt->execute();

// Id del amigo seleccionado
$idAmigo = $_POST['idAmigo'];

// Redirigir a la página principal junto al id del usuairo como ruta
header("Location: index.php" . ($idAmigo ? "?alias_Amigo=" . urlencode($idAmigo) : ""));
exit();
?>