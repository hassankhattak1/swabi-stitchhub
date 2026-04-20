<?php
require_once '../includes/header.php';
checkRole('customer');

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if (!$orderId) redirect("dashboard.php");

$order = getOrder($pdo, $orderId);

if (isset($_POST['pay'])) {
    $amount = $order['total_price'];
    $txnId = strtoupper(substr(md5(time()), 0, 10));
    
    // Update Order
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
    $stmt->execute([$orderId]);
    
    // Insert Payment Record
    $stmt = $pdo->prepare("INSERT INTO payments (order_id, amount, transaction_id, method) VALUES (?, ?, ?, 'Simulated Credit Card')");
    $stmt->execute([$orderId, $amount, $txnId]);
    
    redirect("dashboard.php", "Payment successful! Your order #$orderId is now being processed.");
}
?>

<div class="container mt-20">
    <div style="max-width: 500px; margin: 0 auto;">
        <div class="card">
            <h2 class="text-center">Secure Payment</h2>
            <p class="text-center" style="opacity: 0.7; margin-bottom: 30px;">Payment for Order #<?php echo $orderId; ?></p>
            
            <div style="background: var(--bg); padding: 15px; border-radius: var(--radius); margin-bottom: 25px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span>Service:</span>
                    <strong><?php echo $order['service_name']; ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span>Tailor:</span>
                    <span><?php echo $order['tailor_name']; ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--border); padding-top: 10px; margin-top: 10px;">
                    <strong>Amount to Pay:</strong>
                    <strong style="color: var(--primary); font-size: 1.2rem;"><?php echo formatCurrency($order['total_price']); ?></strong>
                </div>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Cardholder Name</label>
                    <input type="text" class="form-control" value="<?php echo $_SESSION['name']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Card Number</label>
                    <div style="position: relative;">
                        <input type="text" class="form-control" placeholder="4242 4242 4242 4242" maxlength="19" required>
                        <i class="fa-brands fa-cc-visa" style="position: absolute; right: 10px; top: 12px; color: #1a1f71; font-size: 1.5rem;"></i>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="text" class="form-control" placeholder="MM/YY" required>
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" class="form-control" placeholder="123" maxlength="3" required>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <p style="font-size: 0.8rem; opacity: 0.6; text-align: center; margin-bottom: 15px;"><i class="fa-solid fa-lock"></i> This is a simulated payment gateway for demonstration purposes.</p>
                    <button type="submit" name="pay" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 15px;">Pay Now</button>
                    <a href="dashboard.php" class="btn btn-secondary" style="width: 100%; text-align: center; margin-top: 10px;">Pay Later</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
