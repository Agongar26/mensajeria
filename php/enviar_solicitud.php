<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['alias'])) {
    $_SESSION['alert'] = 'Usuario no autenticado.';
    header("Location: index.php");
    exit();
}

// Conectar a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mensajeriaweb";
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar si hay algún error en la conexión
if ($conn->connect_error) {
    $_SESSION['alert'] = 'Error al conectar con la base de datos.';
    header("Location: index.php");
    exit();
}

// Verificar si se envió el alias del amigo
if (!isset($_POST['alias_Amigo']) || empty(trim($_POST['alias_Amigo']))) {
    $_SESSION['alert'] = 'Alias del amigo no proporcionado.';
    header("Location: index.php");
    exit();
}

// Obtener datos del usuario
$aliasUsuario = strtoupper($_SESSION['alias']);
$aliasAmigo = strtoupper(trim($_POST['alias_Amigo']));

// Validar que no se envíe una solicitud a sí mismo
if (strtoupper($aliasUsuario) === strtoupper($aliasAmigo)) {
    $_SESSION['alert'] = 'No puedes enviarte una solicitud a ti mismo.';
    header("Location: index.php");
    exit();
}

// Comprobar si ya existe una solicitud en cualquier dirección
$checkQuery = "
    SELECT COUNT(*) AS total 
    FROM esamigo 
    WHERE 
        (alias_Usuario = ? AND alias_Amigo = ?) 
        OR 
        (alias_Usuario = ? AND alias_Amigo = ?)
";
$stmt = $conn->prepare($checkQuery);
if (!$stmt) {
    $_SESSION['alert'] = 'Error al preparar la consulta.';
    header("Location: index.php");
    exit();
}

$stmt->bind_param("ssss", $aliasUsuario, $aliasAmigo, $aliasAmigo, $aliasUsuario);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    // Ya existe una solicitud de amistad entre los dos usuarios
    $_SESSION['alert'] = 'Ya existe una solicitud de amistad entre estos usuarios.';

    $checkEstado = "
    SELECT estado 
    FROM esamigo 
    WHERE 
        (alias_Usuario = ? AND alias_Amigo = ?) 
        OR 
        (alias_Usuario = ? AND alias_Amigo = ?)
    ";
    $stmt2 = $conn->prepare($checkEstado);
    $stmt2->bind_param("ssss", $aliasUsuario, $aliasAmigo, $aliasAmigo, $aliasUsuario);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $row2 = $result2->fetch_assoc();

    if($row2['estado'] == 'Eliminado' || $row2['estado'] == 'Rechazada') {
        //Cambiar el alias del usuario y del amigo para que se envíe bien la solicitud de amistad de nuuevo
        $updateEstado = "
        UPDATE esamigo
        SET estado = 'Espera', alias_Usuario = ?, alias_Amigo = ?
        WHERE 
        (alias_Usuario = ? AND alias_Amigo = ?) 
        OR 
        (alias_Usuario = ? AND alias_Amigo = ?);
        ";
        $stmt2 = $conn->prepare($updateEstado);
        $stmt2->bind_param("ssssss", $aliasUsuario, $aliasAmigo, $aliasUsuario, $aliasAmigo, $aliasAmigo, $aliasUsuario);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
    }

    $stmt->close();
    $conn->close();
    header("Location: index.php");
    exit();
}

// Insertar la solicitud en la base de datos
$insertQuery = "INSERT INTO esamigo (alias_Usuario, alias_Amigo, estado) VALUES (?, ?, 'Espera')";
$stmt = $conn->prepare($insertQuery);
if (!$stmt) {
    $_SESSION['alert'] = 'Error al preparar la consulta.';
    header("Location: index.php");
    exit();
}

$stmt->bind_param("ss", $aliasUsuario, $aliasAmigo);

if ($stmt->execute()) {
    $_SESSION['alert'] = 'Solicitud enviada con éxito.';
} else {
    $_SESSION['alert'] = 'Error al enviar la solicitud.';
}

// Cerrar la conexión
$stmt->close();
$conn->close();

// Redirigir a index.php 
header("Location: index.php");
exit();
?>