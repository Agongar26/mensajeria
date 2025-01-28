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

// Obtener lista de amigos del usuario
$emisor = $POST['emisor'];
$receptor = $POST['receptor'];
$mensaje = $POST['message'];
$fechaActual = date("Ymd_His")
$mensajeQuery = "INSERT INTO mensaje (emisor, receptor, mensaje, fechaHora, leido) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($solicitudQuery);
$stmt->bind_param("ssss", $emisor, $receptor, $mensaje, $fechaActual, "false");
$stmt->execute();
$result = $stmt->get_result();
$solicitudes = [];
while ($row = $result->fetch_assoc()) {
    $solicitudes[] = $row;
}
?>