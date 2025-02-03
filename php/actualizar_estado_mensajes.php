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
$emisor = $_POST['alias_Amigo'];
$receptor = $_POST['alias_Usuario'];
$leido = true;
$mensajeQuery = "UPDATE mensaje 
                SET leido = ?
                WHERE (emisor = ? AND receptor = ?)
                OR (emisor = ? AND receptor = ?)";
$stmt = $conn->prepare($mensajeQuery);
$stmt->bind_param("sssss", $leido, $emisor, $receptor, $receptor, $emisor);
$stmt->execute();

$idAmigo = $_POST['idAmigo'];

// Redirigir a la página principal junto al id del usuairo como ruta
header("Location: index.php" . ($idAmigo ? "?alias_Amigo=" . urlencode($idAmigo) : ""));
exit();
?>