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

$alias = $_POST['alias'];

$Query = "DELETE 
        FROM usuario
        WHERE alias = ?";
$stmt = $conn->prepare($Query);
$stmt->bind_param("s", $alias);
$stmt->execute();

// Redirigir a la página principal junto al id del usuairo como ruta
header("Location: logout.php");
exit();
?>