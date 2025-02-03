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
$solicitudQuery = "SELECT a.alias_Usuario, a.alias_Amigo
                FROM esamigo a
                WHERE a.alias_Amigo = ? AND a.estado = 'Espera'
                GROUP BY a.alias_Usuario";
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
                ";
$stmt2 = $conn->prepare($amigosQuery);
$stmt2->bind_param("ss", $aliasUsuario, $aliasUsuario);
$stmt2->execute();
$result2 = $stmt2->get_result();
$amigos = [];
while ($row2 = $result2->fetch_assoc()) {
    $amigos[] = $row2;
}

//Para recoger el id de los amigos
$num = 1;

// Simulación de datos de ejemplo
$friends = [];
$selectedFriendId = isset($_GET['alias_Amigo']) ? $_GET['alias_Amigo'] : 0;

// Obtener mensajes entre el usuario y el amigo seleccionado
$chatQuery = "SELECT emisor, receptor, mensaje, fechaHora
FROM mensaje
WHERE (emisor = ? AND receptor = ?)
OR (emisor = ? AND receptor = ?)
ORDER BY fechaHora ASC";
$stmt3 = $conn->prepare($chatQuery);
$stmt3->bind_param("ssss", $amigos[$selectedFriendId-1]['alias_Usuario'], $amigos[$selectedFriendId-1]['alias_Amigo'], $amigos[$selectedFriendId-1]['alias_Amigo'], $amigos[$selectedFriendId-1]['alias_Usuario']);
$stmt3->execute();
$chatResult = $stmt3->get_result();
$mensajes = $chatResult->fetch_all(MYSQLI_ASSOC);

// Cantidad de mensajes no leidos
$MensajesNoLeidos = [];

$leido = 123;

// Obtener mensajes entre el usuario y el amigo seleccionado
for($i=0; $i<(count($amigos)-1); $i++){
    $mensajesQuery = "SELECT mensaje, leido
    FROM mensaje
    WHERE (emisor = ? AND receptor = ?)
    OR (emisor = ? AND receptor = ?)
    AND leido = ?";
    $stmt4 = $conn->prepare($mensajesQuery);
    $stmt4->bind_param("sssss", $amigos[$i]['alias_Usuario'], $_SESSION['alias'], $amigos[$i]['alias_Amigo'], $_SESSION['alias'], $leido);
    $stmt4->execute();
    $mensajesResult = $stmt4->get_result();
    //$MensajesNoLeidos = $mensajesResult->fetch_all(MYSQLI_ASSOC);

    while ($row3 = $mensajesResult->fetch_assoc()) {
        $MensajesNoLeidos[] = $row3;
    }
}

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
                <input type="text" name="alias_Amigo" id="BuscarAmigos" placeholder="Alias del amigo" required>
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
            <ul class="text-align-start">
                <?php foreach ($solicitudes as $solicitud): ?>
                    <li class="list-group-item">
                        <p>
                        <form action="procesar_solicitud.php" method="POST">
                            <!-- Mostrar alias solicitante de la solicitud -->
                            <?= htmlspecialchars($solicitud['alias_Usuario']) ?>
                            <!-- Alias del usuario y del amigo como campos ocultos -->
                            <input type="hidden" name="alias_Usuario" value="<?= htmlspecialchars($solicitud['alias_Usuario']) ?>">
                            <input type="hidden" name="alias_Amigo" value="<?= htmlspecialchars($solicitud['alias_Amigo']) ?>">

                            <!-- Botones para aprobar o rechazar -->
                            <button class="btn btn-success" type="submit" name="action" value="aprobar">Aprobar</button>
                            <button class="btn btn-danger" type="submit" name="action" value="rechazar">Rechazar</button>
                        </form>
                        </p>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    
    <div class="container-fluid d-flex flex-column" style="height: calc(100vh - 52px);">
        <div class="row flex-grow-1">
        <!-- Lista de amigos -->
        <div class="col-3 bg-light border-end overflow-auto" style="max-height: calc(100vh - 70px);">
            <div class="d-flex align-items-center">
                <h5 class="text-center py-3 border-bottom flex-grow-1">Lista de amigos</h5>
                <a href="personal_information.php"><img src="../img/config.webp" style="width: 50px;"></a>
            </div>

            <!-- Botones solicitudes -->
            <div class="text-center">
                <button class="btn btn-primary btn-sm m-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#send_request">Enviar solicitud</button>
                <button class="btn btn-secondary btn-sm m-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#aprove_request">Ver solicitudes</button>

                <!-- Mostrar cantidad de solicitudes de amistad recibidas -->
                <?php if(count($solicitudes) == 0):?>                
                    <p id="mostrar_solicitudes_pendientes"></p>
                <?php elseif(count($solicitudes) == 1):?>
                    <p id="mostrar_solicitudes_pendientes">Tienes 1 solicitud nueva</p>
                <?php elseif(count($solicitudes) > 1):?>
                    <p id="mostrar_solicitudes_pendientes">Tienes <?php print_r(count($solicitudes))?> solicitudes nuevas</p>
                <?php endif;?>
            </div>

            <ul class="list-group list-group-flush">
                <form action="procesar_solicitud.php" method="POST">
                    <?php 
                    
                    foreach ($amigos as $amigo): ?>
                        <?php if(strtoupper($amigo['alias_Usuario']) == strtoupper($_SESSION['alias'])): ?> <!-- Mostrar el alias_Amigo como amigo -->
                        <li class="list-group-item">
                            <a href="?alias_Amigo=<?php echo $num ?>" id="<?php echo $num ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($amigo['alias_Amigo']) ?></a>
                            <button id="<?php htmlspecialchars($amigo['alias_Amigo'])?>" class="btn btn-translucent text-black"><?php echo htmlspecialchars($amigo['alias_Amigo']) ?></button>
                            <!-- Alias del usuario y del amigo como campos ocultos -->
                            <input type="hidden" name="alias_Usuario" value="<?= htmlspecialchars($amigo['alias_Usuario']) ?>">
                            <input type="hidden" name="alias_Amigo" value="<?= htmlspecialchars($amigo['alias_Amigo']) ?>">

                            <button class="btn btn-translucent" type="submit" name="action" value="eliminar"><ion-icon name="trash-outline" style="color: red;"></ion-icon></button> <!-- Botón transparente borrar -->
                            
                            <!-- Igualar el id y alias del amigo -->
                            <?php array_push($friends, ['id' => $num, 'alias' => $amigo['alias_Amigo']]);?>

                            <!-- <?/*php echo '<pre>';
                                print_r($amigo);
                                echo '</pre>';*/?> -->

                            <!-- Igualar el alias del amigo al seleccionado -->
                            <?/*php echo $amigo['alias_Amigo'];
                            $selectedFriendAlias = $amigo['alias_Amigo'];*/?>
                        </li>
                        <?php elseif(strtoupper($amigo['alias_Amigo']) == strtoupper($_SESSION['alias'])) : ?> <!-- Mostrar el alias_Usuario como amigo -->
                        <li class="list-group-item">
                            <a href="?alias_Amigo=<?php echo $num ?>" id="<?= htmlspecialchars($amigo['alias_Usuario']) ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($amigo['alias_Usuario']) ?></a>
                            <button id="<?php htmlspecialchars($amigo['alias_Usuario'])?>" class="btn btn-translucent text-black"><?php echo htmlspecialchars($amigo['alias_Usuario']) ?></button>
                            <!-- Alias del usuario y del amigo como campos ocultos -->
                            <input type="hidden" name="alias_Usuario" value="<?= htmlspecialchars($amigo['alias_Usuario']) ?>">
                            <input type="hidden" name="alias_Amigo" value="<?= htmlspecialchars($amigo['alias_Amigo']) ?>">

                            <button class="btn btn-translucent" type="submit" name="action" value="eliminar"><ion-icon name="trash-outline" style="color: red;"></ion-icon></button> <!-- Botón transparente borrar -->

                            <?php if(count($MensajesNoLeidos) > 0): ?>
                            <p><?php echo count($MensajesNoLeidos) . " mensajes no leídos"?></p>
                            <?php endif;?>
                            <!-- Igualar el id y alias del usuario -->
                            <?php array_push($friends, ['id' => $num, 'alias' => $amigo['alias_Usuario']]);?>
                            
                            <!-- <?php/* echo '<pre>';
                                print_r($amigo);
                                echo '</pre>';*/?> -->

                            <!-- Igualar el alias del amigo al seleccionado -->
                            <?php/* echo $amigo['alias_Usuario'];
                            $selectedFriendAlias = $amigo['alias_Usuario'];*/?>
                        </li>
                        <?php endif;?>
                    <?php 
                    $num++;
                    endforeach; ?>
                </form>
            </ul>
            <?php print_r($friends)?>
            <?php print_r(" ------------------------------------------------------ ")?>
            <?php print_r($solicitudes)?>
            <?php print_r(" ------------------------------------------------------ ")?>
            <?php print_r($amigos)?>
            <?php print_r(" ------------------------------------------------------ ")?>
            <?php print_r($selectedFriendId)?>
            <?php print_r(" ------------------------------------------------------ ")?>
            <?php print_r($mensajes)?>
            <?php print_r(" ------------------------------------------------------ ")?>
            <?php print_r($MensajesNoLeidos)?>

            <?php if(strtoupper($_SESSION['alias']) == $amigos[$selectedFriendId-1]['alias_Usuario']): ?>
            <input type="hidden" name="emisor" value="<?= htmlspecialchars($amigos[$selectedFriendId-1]['alias_Usuario']) ?>">
            <input type="hidden" name="receptor" value="<?= htmlspecialchars($amigos[$selectedFriendId-1]['alias_Amigo']) ?>">
            <?php print_r("Alias usuario:" . $amigos[$selectedFriendId-1]['alias_Usuario']) ?>
            <?php print_r("Alias amigo: " . $amigos[$selectedFriendId-1]['alias_Amigo']) ?>
            <?php else: ?>
            <input type="hidden" name="emisor" value="<?= htmlspecialchars($amigos[$selectedFriendId-1]['alias_Amigo']) ?>">
            <input type="hidden" name="receptor" value="<?= htmlspecialchars($amigos[$selectedFriendId-1]['alias_Usuario']) ?>">
            <?php print_r("2.- Alias usuario: " . $amigos[$selectedFriendId-1]['alias_Amigo']) ?>
            <?php print_r("2.- Alias amigo:" . $amigos[$selectedFriendId-1]['alias_Usuario']) ?>
            <?php endif;?>

        </div>

        <!-- Ventana de chat -->
        <div class="col-9 d-flex flex-column" style="max-height: calc(100vh - 70px);">
            <?php if ($selectedFriendId > 0):?> <!-- Comprobar que se haya seleccionado un amigo para hablar con él -->
                <!-- Encabezado del chat -->
                <div class="bg-primary text-white py-2 d-flex align-items-center">
                <!-- Botón a la izquierda -->
                <div class="me-auto ms-3">
                    <a href="index.php" class="btn btn-danger">Cerrar conversación</a>
                </div>
                <!-- Texto centrado -->
                <div class="me-auto">
                    <p class="mb-0"><!-- Conversación con --><?php echo $friends[$selectedFriendId-1]['alias']?></p>
                </div>
                <div class="ma-auto me-3">
                    <!-- Mostrar cantidad de mensajes no leídos -->
                    <form action="actualizar_estado_mensajes.php" method="POST">
                        <input type="hidden" name="alias_Usuario" value="<?= htmlspecialchars($amigo['alias_Usuario']) ?>">
                        <input type="hidden" name="alias_Amigo" value="<?= htmlspecialchars($amigo['alias_Amigo']) ?>">
                        <input type="hidden" name="idAmigo" value="<?= htmlspecialchars($selectedFriendId) ?>">
                        <button class="btn btn-success fs-6 ms-3">Marcar leído</button>
                    </form>
                </div>
            </div>

                <!-- Mensajes -->
                <div class="mensajes d-flex flex-column overflow-auto scrollable-div" style="max-height: calc(100vh - 100px);">
                    <!-- Bucle que muestra todos los mensajes del chat -->
                    <?php for($i=0; $i<count($mensajes); $i++): ?>
                    <div class="flex-grow-1 bg-light p-3">
                        <!-- Mensajes de ejemplo -->
                        <?php if($mensajes[$i]['receptor'] === strtoupper($_SESSION['alias'])): ?>
                        <div class="mb-3">
                            <div class="text-start">
                                <span class="badge bg-secondary"> <?= htmlspecialchars($friends[$selectedFriendId-1]['alias']) ?> </span>
                                <p class="bg-white border rounded p-2 d-inline-block mt-1"><?php echo $mensajes[$i]['mensaje']?>   </p>
                                <?php echo date("H:i", strtotime($mensajes[$i]['fechaHora'])); ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="mb-3">
                            <div class="text-end">
                                <span class="badge bg-primary">Tú</span>
                                <p class="bg-primary text-white rounded p-2 d-inline-block mt-1"><?php echo $mensajes[$i]['mensaje'] ?>   </p>
                                <?php echo date("H:i", strtotime($mensajes[$i]['fechaHora'])); ?>
                            </div>
                        </div>
                        <?php endif;?>
                    </div>
                    <?php endfor;?>
                </div>
                <!-- Formulario para enviar mensajes -->
                <div class="bg-light border-top p-3">
                        <form action="enviar_mensaje.php" method="POST" class="d-flex">
                            <!-- Alias del usuario y del amigo como campos ocultos para la consulta de insercion de mensaje -->
                            <?php if(strtoupper($_SESSION['alias']) == $amigos[$selectedFriendId-1]['alias_Usuario']): ?>
                            <input type="hidden" name="emisor" value="<?= htmlspecialchars($amigos[$selectedFriendId-1]['alias_Usuario']) ?>">
                            <input type="hidden" name="receptor" value="<?= htmlspecialchars($amigos[$selectedFriendId-1]['alias_Amigo']) ?>">
                            <?php print_r("Emisor:" . $amigos[$selectedFriendId-1]['alias_Usuario']) ?>
                            <?php print_r("Receptor: " . $amigos[$selectedFriendId-1]['alias_Amigo']) ?>
                            <?php else: ?>
                            <input type="hidden" name="emisor" value="<?= htmlspecialchars($amigos[$selectedFriendId-1]['alias_Amigo']) ?>">
                            <input type="hidden" name="receptor" value="<?= htmlspecialchars($amigos[$selectedFriendId-1]['alias_Usuario']) ?>">
                            <?php print_r("2.- Emisor: " . $amigos[$selectedFriendId-1]['alias_Amigo']) ?>
                            <?php print_r("2.- Receptor:" . $amigos[$selectedFriendId-1]['alias_Usuario']) ?>
                            <?php print_r("2.- IdAmigo:" . $selectedFriendId) ?>
                            <?php endif;?>

                            <input type="hidden" name="idAmigo" value="<?= htmlspecialchars($selectedFriendId) ?>">

                            <!-- Campo de texto y botón para enviar el mensaje -->
                            <input type="text" name="message" class="form-control me-2" placeholder="Escribe un mensaje..."required>
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
            <p>&copy; 2025 Alejandro González García. Todos los derechos reservados.</p>
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