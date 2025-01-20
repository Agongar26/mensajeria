<?php
// Incluir la configuración de la base de datos
include 'db_config.php';

// Obtener los mensajes de la base de datos
try {
    $stmt = $pdo->query("SELECT * FROM mensajes ORDER BY timestamp DESC");
    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mostrar los mensajes
    foreach ($mensajes as $mensaje) {
        echo "<div class='message'>";
        echo "<p>" . htmlspecialchars($mensaje['contenido']) . " - " . date('d/m/Y - H:i', strtotime($mensaje['timestamp'])) . "</p>";
        echo "</div>";
    }
} catch (PDOException $e) {
    echo "Error al obtener mensajes: " . $e->getMessage();
}
?>
