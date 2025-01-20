<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['alias'])) {
    //header("Location: login.php"); // Redirigir al inicio de sesión si no está autenticado
    exit();
}

// Simulación de datos de ejemplo
$friends = [
    ["id" => 1, "name" => "Juan"],
    ["id" => 2, "name" => "María"],
    ["id" => 3, "name" => "Carlos"],
];
$selectedFriendId = isset($_GET['friend_id']) ? $_GET['friend_id'] : null;
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
          <a class="navbar-brand" href="#">
            <img src="../img/Logo.jpeg" alt="Logo" style="width:40px;" class="rounded-pill">
          </a>
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
    
    <div class="container-fluid vh-100 d-flex flex-column">
    <div class="row flex-grow-1">
        <!-- Lista de amigos -->
        <div class="col-3 bg-light border-end overflow-auto">
            <h5 class="text-center py-3 border-bottom">Lista de amigos</h5>
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