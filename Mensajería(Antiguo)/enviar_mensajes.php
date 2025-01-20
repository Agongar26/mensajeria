<?php
// Incluir la conexión a la base de datos
include 'db_config.php';

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener el contenido del mensaje
    $contenido = trim($_POST['contenido']);

    // Obtener la hora actual
    $hora_actual = date('Y-m-d H:i:s'); // Formato: Año-Mes-Día Hora:Minuto:Segundo

    // Verificar si el mensaje no está vacío
    if (!empty($contenido)) {
        try {
            // Insertar el mensaje y la hora en la base de datos
            $stmt = $pdo->prepare("INSERT INTO mensajes (contenido, timestamp) VALUES (:contenido, :timestamp)");
            $stmt->execute(['contenido' => $contenido, 'timestamp' => $hora_actual]);

            // Redirigir al usuario de vuelta a la página de chat
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>