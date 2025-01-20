<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="chat-container">
        <div id="chat-box" class="chat-box">
            <!-- Los mensajes se mostrarán aquí -->
            <?php
                // Incluir el archivo PHP para mostrar los mensajes desde la base de datos
                include 'obtener_mensajes.php';
            ?>
        </div>

        <!-- Formulario para enviar el mensaje -->
        <form method="POST" action="enviar_mensajes.php">
            <input type="text" name="contenido" id="message-input" placeholder="Escribe un mensaje..." required>
            <button type="submit">Enviar</button>
        </form>
    </div>
</body>
</html>
