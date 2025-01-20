<?php
//session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['alias'])) {
    header("Location: login.php"); // Redirigir al inicio de sesión si no está autenticado
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
    <link rel="stylesheet" href="../css/style.css">
    <title>Aplicación de Mensajería</title>
</head>
<body>
    <div class="container">
        <div class="friends-list">
            <ul>
                <?php foreach ($friends as $friend): ?>
                    <li>
                        <a href="?friend_id=<?= $friend['id'] ?>">
                            <?= htmlspecialchars($friend['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="chat-window">
            <?php if ($selectedFriendId): ?>
                <div class="chat-header">
                    Conversación con <?= htmlspecialchars($friends[$selectedFriendId - 1]['name']) ?>
                </div>
                <div class="chat-messages">
                    <!-- Mensajes de ejemplo -->
                    <p><strong><?= htmlspecialchars($friends[$selectedFriendId - 1]['name']) ?>:</strong> Hola, ¿cómo estás?</p>
                    <p><strong>Tú:</strong> ¡Hola! Estoy bien, ¿y tú?</p>
                </div>
                <div class="chat-input">
                    <form action="send_message.php" method="post">
                        <input type="text" name="message" placeholder="Escribe un mensaje...">
                        <button type="submit">Enviar</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="chat-header">
                    Selecciona un amigo para iniciar la conversación
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>