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
$aliasUsuario = $_SESSION['alias'];
$friendsQuery = "SELECT u.alias, u.nombre FROM Usuario u 
                 INNER JOIN EsAmigo ea ON (ea.alias_Usuario = u.alias OR ea.alias_Amigo = u.alias)
                 WHERE (ea.alias_Usuario = ? OR ea.alias_Amigo = ?) AND u.alias != ?";
$stmt = $conn->prepare($friendsQuery);
$stmt->bind_param("sss", $aliasUsuario, $aliasUsuario, $aliasUsuario);
$stmt->execute();
$result = $stmt->get_result();
$friends = [];
while ($row = $result->fetch_assoc()) {
    $friends[] = $row;
}

// Gestión de solicitudes
$selectedFriendId = isset($_GET['friend_id']) ? $_GET['friend_id'] : null;

// Cerrar la conexión
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> <!-- bootstrap -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script> <!-- ioicon -->
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script> <!-- ioicon -->
    <script src="../js/funciones.js"></script> <!-- JavaScript para el manejo de las solicitudes-->
    <title>Aplicación de Mensajería</title>
</head>
<body>

    <nav class="navbar navbar-expand-sm bg-dark navbar-dark">
        <div class="container-fluid">
            <img src="../img/Logo.jpeg" alt="Logo" style="width:40px;" class="rounded-pill">
        </div>
        <div class="d-flex align-items-center justify-content-end w-100">
            <?php if (isset($_SESSION['alias'])): ?>
                <p class="mb-0 text-white" id="User">Bienvenido, <?php echo htmlspecialchars($_SESSION['alias']);?>!</p>
                <a href="logout.php" class="btn btn-danger" style="margin-left: 10px; margin-right: 5px;">Cerrar sesión</a>
            <?php else: ?>
                <p class="mb-0 text-white" id="User"></p>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Off canvas enviar solicitud amistad -->
    <div class="offcanvas offcanvas-start text-bg-dark" id="send_request">
        <div class="offcanvas-header">
            <h1 class="offcanvas-title">Enviar solicitud amistad</h1>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <form action="">
                <input type="text" name="friend_alias" id="BuscarAmigos" placeholder="Alias del amigo" required>
                <button class="btn btn-success" type="submit" id="send_request">Enviar solicitud</button>
                <div id="responseMessage" class="mt-3"></div>
            </form>
            <?php if (isset($message)): ?>
                <div class="mt-3 alert alert-<?= htmlspecialchars($messageType) ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Off canvas ver solicitud amistad -->
    <div class="offcanvas offcanvas-start text-bg-dark" id="aprove_request">
        <div class="offcanvas-header">
            <h1 class="offcanvas-title">Solicitudes recibidas</h1>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <hr style="border-top: 10px solid white;">
        <div class="offcanvas-body">
            <p>Jose <button class="btn btn-success" type="button">aprovar</button> <button class="btn btn-danger" type="button">rechazar</button></p> 
        </div>
    </div>
    
    <div class="container-fluid vh-100 d-flex flex-column">
        <div class="row flex-grow-1">
        <!-- Lista de amigos -->
        <div class="col-3 bg-light border-end overflow-auto">
            <div class="d-flex align-items-center">
                <h5 class="text-center py-3 border-bottom flex-grow-1">Lista de amigos</h5>
                <a href="personal_information.php"><img src="../img/config.webp" style="width: 50px;"></a>
            </div>

            <!-- Botones solicitudes -->
            <div class="text-center">
                <button class="btn btn-primary btn-sm m-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#send_request">
                    Enviar solicitud
                </button>
                <button class="btn btn-secondary btn-sm m-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#aprove_request">
                    Ver solicitudes
                </button>
            </div>

            <ul class="list-group list-group-flush">
                <?php foreach ($friends as $friend): ?>
                    <li class="list-group-item">
                        <a href="?friend_id=<?= $friend['id'] ?>" class="text-decoration-none text-dark">
                            <?= htmlspecialchars($friend['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Ventana de chat -->
        <div class="col-9 d-flex flex-column">
            <?php if ($selectedFriendId): ?>
                <!-- Encabezado del chat -->
                <div class="bg-primary text-white text-center py-2">
                    Conversación con <?= htmlspecialchars($friends[$selectedFriendId - 1]['name']) ?>
                </div>

                <!-- Mensajes -->
                <div class="flex-grow-1 bg-light overflow-auto p-3">
                    <!-- Mensajes de ejemplo -->
                    <div class="mb-3">
                        <div class="text-start">
                            <span class="badge bg-secondary"> 
                                <?= htmlspecialchars($friends[$selectedFriendId - 1]['name']) ?> 
                            </span>
                            <p class="bg-white border rounded p-2 d-inline-block mt-1">
                                Hola, ¿cómo estás?
                            </p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="text-end">
                            <span class="badge bg-primary">Tú</span>
                            <p class="bg-primary text-white rounded p-2 d-inline-block mt-1">
                                ¡Hola! Estoy bien, ¿y tú?
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Formulario para enviar mensajes -->
                <div class="bg-light border-top p-3">
                    <form action="send_message.php" method="post" class="d-flex">
                        <input 
                            type="text" 
                            name="message" 
                            class="form-control me-2" 
                            placeholder="Escribe un mensaje..."
                            required>
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Mensaje de selección -->
                <div class="flex-grow-1 d-flex align-items-center justify-content-center bg-light">
                    <p class="text-muted">Selecciona un amigo para iniciar la conversación</p>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>


    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            <p>&copy; 2024 Alejandro González García. Todos los derechos reservados.</p>
            <p>
                <a href="#" class="text-white">Política de Privacidad</a>
                <a href="#" class="text-white">Términos de Servicio</a>
            </p>
            <p>
                <a href="https://www.instagram.com/"><ion-icon name="logo-instagram"></ion-icon></a>
                <a href="https://www.facebook.com/"><ion-icon name="logo-facebook"></ion-icon></a>
                <a href="https://x.com/"><ion-icon name="logo-twitter"></ion-icon></a>
            </p>
        </div>
    </footer>
</body>
</html>