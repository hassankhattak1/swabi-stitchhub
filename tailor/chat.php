<?php
require_once '../includes/header.php';
checkRole('tailor');

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if (!$orderId) redirect("dashboard.php");

$order = getOrder($pdo, $orderId);
$userId = $_SESSION['user_id'];
?>

<div class="container mt-20">
    <div style="max-width: 800px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Chat with Customer (<?php echo $order['customer_name']; ?>)</h2>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        
        <div class="card">
            <p style="font-size: 0.9rem; opacity: 0.7; margin-bottom: 15px;">Conversation regarding <strong>Order #<?php echo $orderId; ?> (<?php echo $order['service_name']; ?>)</strong></p>
            
            <div class="chat-box" data-order-id="<?php echo $orderId; ?>" data-user-id="<?php echo $userId; ?>">
                <!-- Messages will be loaded here via main.js AJAX polling -->
                <div class="text-center" style="margin-top: 100px; opacity: 0.5;">Loading conversation...</div>
            </div>

            <form id="chat-form" style="display: flex; gap: 10px;">
                <input type="text" id="message-input" class="form-control" placeholder="Type your message..." required>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Send</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
