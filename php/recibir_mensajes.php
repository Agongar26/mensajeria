<?php
session_start();
include('db.php'); // Asegúrate de que esta conexión esté configurada correctamente

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "No estás autenticado."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $user_id = $_SESSION['user_id'];
    $conversation_user_id = $_GET['user_id'];

    if (empty($conversation_user_id)) {
        echo json_encode(["error" => "ID del usuario de la conversación es obligatorio."]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT m.sender_id, m.receiver_id, m.message, m.timestamp, 
               u.username AS sender_name
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?)
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.timestamp ASC
    ");
    $stmt->bind_param("iiii", $user_id, $conversation_user_id, $conversation_user_id, $user_id);

    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    echo json_encode($messages);

    $stmt->close();
} else {
    echo json_encode(["error" => "Método no permitido."]);
}

$conn->close();
?>