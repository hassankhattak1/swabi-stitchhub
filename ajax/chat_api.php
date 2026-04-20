<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];

// Send Message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)$_POST['order_id'];
    $message = sanitize($_POST['message']);
    
    // Check if user is part of the order
    $stmt = $pdo->prepare("SELECT customer_id, tailor_id FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if ($order && ($order['customer_id'] == $userId || $order['tailor_id'] == $userId)) {
        $receiverId = ($userId == $order['customer_id']) ? $order['tailor_id'] : $order['customer_id'];
        
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        if ($stmt->execute([$userId, $receiverId, $message])) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    }
} 
// Fetch Messages
else if (isset($_GET['order_id'])) {
    $orderId = (int)$_GET['order_id'];
    
    $stmt = $pdo->prepare("SELECT * FROM messages 
                           WHERE (sender_id = ?) OR (receiver_id = ?)
                           ORDER BY created_at ASC");
    // Simplified: Getting messages for the user. Ideally filter by the pair involved in order.
    // Better query:
    $stmt = $pdo->prepare("SELECT m.* FROM messages m
                           JOIN orders o ON (m.sender_id = o.customer_id AND m.receiver_id = o.tailor_id) 
                                         OR (m.sender_id = o.tailor_id AND m.receiver_id = o.customer_id)
                           WHERE o.id = ?
                           ORDER BY m.created_at ASC");
    $stmt->execute([$orderId]);
    $messages = $stmt->fetchAll();
    
    echo json_encode(['status' => 'success', 'messages' => $messages]);
}
?>
