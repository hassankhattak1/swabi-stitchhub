<?php
require_once '../includes/header.php';
checkRole('customer');

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if (!$orderId) redirect("dashboard.php");

$order = getOrder($pdo, $orderId);

if (isset($_POST['submit_feedback'])) {
    $rating = (int)$_POST['rating'];
    $comment = sanitize($_POST['comment']);
    
    $stmt = $pdo->prepare("INSERT INTO feedback (order_id, rating, comment) VALUES (?, ?, ?)");
    try {
        if ($stmt->execute([$orderId, $rating, $comment])) {
            redirect("dashboard.php", "Thank you for your feedback!");
        }
    } catch (Exception $e) {
        $error = "You have already provided feedback for this order.";
    }
}
?>

<div class="container mt-20">
    <div style="max-width: 500px; margin: 0 auto;">
        <div class="card">
            <h2 class="text-center">Rate Your Experience</h2>
            <p class="text-center" style="opacity: 0.7; margin-bottom: 30px;">Order #<?php echo $orderId; ?> - <?php echo $order['service_name']; ?></p>
            
            <?php if (isset($error)): ?>
                <div class="badge badge-rejected" style="width: 100%; display: block; margin-bottom: 15px; padding: 10px;"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Rating (1 to 5 Stars)</label>
                    <select name="rating" class="form-control" required>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Very Good</option>
                        <option value="3">3 - Good</option>
                        <option value="2">2 - Fair</option>
                        <option value="1">1 - Poor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Your Comments</label>
                    <textarea name="comment" class="form-control" rows="4" placeholder="How was the fit? Quality of work?" required></textarea>
                </div>
                <button type="submit" name="submit_feedback" class="btn btn-primary" style="width: 100%;">Submit Feedback</button>
                <a href="dashboard.php" class="btn btn-secondary" style="width: 100%; text-align: center; margin-top: 10px;">Skip</a>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
