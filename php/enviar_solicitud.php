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
$dbname = "mensajeriaweb";
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar si hay algún error en la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Variables para mensajes
$message = null;
$messageType = null;

// Procesar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_request'])) {
    $friendAlias = trim($_POST['friend_alias']);
    $userAlias = $_SESSION['alias'];

    // Validar que no sea el mismo usuario
    if ($friendAlias === $userAlias) {
        $message = "No puedes enviarte una solicitud a ti mismo.";
        $messageType = "warning";
    } else {
        // Verificar si ya existe una solicitud o relación
        $queryCheck = "SELECT * FROM EsAmigo WHERE 
            (alias_Usuario = ? AND alias_Amigo = ?) OR (alias_Usuario = ? AND alias_Amigo = ?)";
        $stmtCheck = $conn->prepare($queryCheck);
        $stmtCheck->bind_param("ssss", $userAlias, $friendAlias, $friendAlias, $userAlias);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows > 0) {
            $message = "Ya existe una solicitud o relación con este usuario.";
            $messageType = "warning";
        } else {
            // Insertar la nueva solicitud
            $queryInsert = "INSERT INTO EsAmigo (alias_Usuario, alias_Amigo, estado) VALUES (?, ?, 'Espera')";
            $stmtInsert = $conn->prepare($queryInsert);
            $stmtInsert->bind_param("ss", $userAlias, $friendAlias);

            if ($stmtInsert->execute()) {
                $message = "Solicitud enviada correctamente.";
                $messageType = "success";
            } else {
                $message = "Error al enviar la solicitud. Inténtalo más tarde.";
                $messageType = "danger";
            }
            $stmtInsert->close();
        }
        $stmtCheck->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- bootstrap -->
    <title>Aplicación de Mensajería</title>
</head>
<body>

    <!-- Off canvas enviar solicitud amistad -->
    <div class="offcanvas offcanvas-start text-bg-dark <?= ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_request'])) ? 'show' : '' ?>" 
         tabindex="-1" 
         id="send_request" 
         aria-labelledby="offcanvasLabel" 
         style="<?= ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_request'])) ? 'visibility: visible;' : '' ?>">
        <div class="offcanvas-header">
            <h1 class="offcanvas-title" id="offcanvasLabel">Enviar solicitud amistad</h1>
            <a href="index.php" class="btn-close btn-close-white" aria-label="Close"></a>
        </div>
        <div class="offcanvas-body">
            <form action="" method="POST">
                <input 
                    type="text" 
                    name="friend_alias" 
                    id="BuscarAmigos" 
                    placeholder="Alias del amigo" 
                    class="form-control mb-3" 
                    required>
                <button class="btn btn-success" type="submit" name="send_request">Enviar solicitud</button>
            </form>
            <?php if (isset($message)): ?>
                <div class="mt-3 alert alert-<?= htmlspecialchars($messageType) ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> <!-- Bootstrap -->
</body>
</html>
