<?php
session_start();
include('db.php'); // Asegúrate de que esta conexión esté configurada correctamente

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "No estás autenticado."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];

    if (empty($receiver_id) || empty($message)) {
        echo json_encode(["error" => "Los campos receptor y mensaje son obligatorios."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message);

    if ($stmt->execute()) {
        echo json_encode(["success" => "Mensaje enviado correctamente."]);
    } else {
        echo json_encode(["error" => "Error al enviar el mensaje."]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Método no permitido."]);
}

$conn->close();
?>