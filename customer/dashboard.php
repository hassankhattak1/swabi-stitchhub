<?php
require_once '../includes/header.php';
checkRole('customer');

$customerId = $_SESSION['user_id'];

// Fetch Available Services
$services = $pdo->query("SELECT * FROM services")->fetchAll();

// Fetch Available Tailors
$tailors = $pdo->query("SELECT id, name, email, bio FROM users WHERE role = 'tailor'")->fetchAll();

// Fetch Customer's Orders
$ordersStmt = $pdo->prepare("SELECT o.*, t.name as tailor_name, s.name as service_name 
                             FROM orders o 
                             JOIN users t ON o.tailor_id = t.id 
                             JOIN services s ON o.service_id = s.id 
                             WHERE o.customer_id = ? 
                             ORDER BY o.created_at DESC");
$ordersStmt->execute([$customerId]);
$myOrders = $ordersStmt->fetchAll();
?>

<div class="container mt-20" style="padding-bottom: 50px;">
    <h1>Customer Dashboard</h1>
    
    <?php if (isset($_SESSION['msg'])): ?>
        <div class="card" style="background: #e8f5e9; color: #2e7d32; padding: 10px; margin-bottom: 20px;">
            <?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?>
        </div>
    <?php endif; ?>

    <!-- Orders Tracking -->
    <section class="card">
        <h3>My Orders</h3>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Tailor</th>
                    <th>Service</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($myOrders)): ?>
                    <tr><td colspan="6" class="text-center">You haven't placed any orders yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($myOrders as $o): ?>
                <tr>
                    <td>#<?php echo $o['id']; ?></td>
                    <td>
                        <?php echo $o['tailor_name']; ?>
                        <br><a href="chat.php?order_id=<?php echo $o['id']; ?>" style="font-size: 0.8rem; color: var(--primary);"><i class="fa-solid fa-message"></i> Chat</a>
                    </td>
                    <td><?php echo $o['service_name']; ?></td>
                    <td><?php echo formatCurrency($o['total_price']); ?></td>
                    <td><span class="badge badge-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                    <td>
                        <?php if ($o['payment_status'] === 'pending' && $o['status'] !== 'rejected'): ?>
                            <a href="payment.php?order_id=<?php echo $o['id']; ?>" class="btn btn-secondary" style="padding: 5px 8px; font-size: 0.8rem;">Pay Now</a>
                        <?php elseif ($o['payment_status'] === 'paid'): ?>
                            <span style="color: var(--success); font-weight: 600; font-size: 0.8rem;"><i class="fa-solid fa-check-circle"></i> Paid</span>
                        <?php endif; ?>
                        
                        <?php if ($o['status'] === 'delivered'): ?>
                            <br><a href="feedback.php?order_id=<?php echo $o['id']; ?>" style="font-size: 0.8rem; color: var(--secondary);">Leave Feedback</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <!-- Services & Tailors -->
    <div id="tailors" style="margin-top: 50px;">
        <h2 class="text-center">Book a Tailoring Service</h2>
        <p class="text-center" style="margin-bottom: 30px; opacity: 0.7;">Select a service and find the perfect tailor for you.</p>
        
        <div class="stats-grid">
            <?php foreach ($services as $s): ?>
            <div class="card" style="text-align: center;">
                <div style="background: var(--bg); height: 150px; border-radius: var(--radius); margin-bottom: 15px; display: flex; align-items: center; justify-content: center;">
                     <i class="fa-solid fa-shirt" style="font-size: 3rem; color: var(--primary-light);"></i>
                </div>
                <h3><?php echo $s['name']; ?></h3>
                <p><?php echo formatCurrency($s['price']); ?></p>
                <div style="margin-top: 15px;">
                    <a href="book_order.php?service_id=<?php echo $s['id']; ?>" class="btn btn-primary" style="width: 100%;">Select Service</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
