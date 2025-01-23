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
$solicitudQuery = "SELECT u.nombre, a.alias_Usuario, a.alias_Amigo
                FROM esamigo a, usuario u
                WHERE a.alias_Amigo = ? AND a.estado = 'Espera'
                GROUP BY a.alias_Amigo";
$stmt = $conn->prepare($solicitudQuery);
$stmt->bind_param("s", $aliasUsuario);
$stmt->execute();
$result = $stmt->get_result();
$solicitudes = [];
while ($row = $result->fetch_assoc()) {
    $solicitudes[] = $row;
}

// Obtener lista de amigos del usuario
$amigosQuery = "SELECT a.alias_Usuario, a.alias_Amigo
                FROM esamigo a
                WHERE (a.alias_Amigo = ? OR a.alias_Usuario = ?) AND a.estado = 'Aceptada'
                GROUP BY a.alias_Amigo";
$stmt2 = $conn->prepare($amigosQuery);
$stmt2->bind_param("ss", $aliasUsuario, $aliasUsuario);
$stmt2->execute();
$result2 = $stmt2->get_result();
$amigos = [];
while ($row2 = $result2->fetch_assoc()) {
    $amigos[] = $row2;
}

// Gestión de solicitudes
$selectedFriendAlias = isset($_GET['alias_Usuario']) ? $_GET['alias_Usuario'] : null;

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
            <form action="enviar_solicitud.php" method="POST">
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
            <form action="procesar_solicitud.php" method="POST">
                <ul class="text-align-start">
                    <?php foreach ($solicitudes as $solicitud): ?>
                        <li class="list-group-item">
                            <p>
                                <?= htmlspecialchars($solicitud['nombre']) ?> (ID: <?= htmlspecialchars($solicitud['alias_Usuario']) ?>)
                                <!-- Alias del usuario y del amigo como campos ocultos -->
                                <input type="hidden" name="alias_Usuario" value="<?= htmlspecialchars($solicitud['alias_Usuario']) ?>">
                                <input type="hidden" name="alias_Amigo" value="<?= htmlspecialchars($solicitud['alias_Amigo']) ?>">

                                <!-- Botones para aprobar o rechazar -->
                                <button class="btn btn-success" type="submit" name="action" value="aprobar">Aprobar</button>
                                <button class="btn btn-danger" type="submit" name="action" value="rechazar">Rechazar</button>
                            </p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </form>
            <p>Jose <button class="btn btn-success" type="button">aprobar</button> <button class="btn btn-danger" type="button">rechazar</button></p> 
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
                <?php foreach ($amigos as $amigo): ?>
                    <?php if($amigo['alias_Usuario'] == $_SESSION['alias']): ?>
                    <li class="list-group-item">
                        <a href="?friend_ALias=<?= htmlspecialchars($amigo['alias_Amigo']) ?>" class="text-decoration-none text-dark">
                            <?= htmlspecialchars($amigo['alias_Amigo']) ?>
                        </a>
                        <!-- <?/*php echo '<pre>';
                            print_r($amigo);
                            echo '</pre>';*/?> -->
                    </li>
                    <?php else: ?>
                    <li class="list-group-item">
                        <a href="?friend_ALias=<?= htmlspecialchars($amigo['alias_Usuario']) ?>" class="text-decoration-none text-dark">
                            <?= htmlspecialchars($amigo['alias_Usuario']) ?>
                        </a>
                        <!--<?php/* echo '<pre>';
                            print_r($amigo);
                            echo '</pre>'*/;?>-->
                    </li>
                    <?php endif;?>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Ventana de chat -->
        <div class="col-9 d-flex flex-column">
            <?php if ($selectedFriendAlias): 'UsuarioNuevo'?>
                <!-- Encabezado del chat -->
                <!--<div class="bg-primary text-white text-center py-2">
                    Conversación con <?= htmlspecialchars($solicitudes[$selectedFriendAlias]['nombre']) ?>
                </div>-->

                <!-- Mensajes -->
                <div class="flex-grow-1 bg-light overflow-auto p-3">
                    <!-- Mensajes de ejemplo -->
                    <div class="mb-3">
                        <div class="text-start">
                            <span class="badge bg-secondary"> 
                                <?= htmlspecialchars($solicitudes[$selectedFriendAlias]['alias_Amigo']) ?> 
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