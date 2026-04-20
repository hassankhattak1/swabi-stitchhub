<?php
require_once '../includes/header.php';
checkRole('tailor');

$tailorId = $_SESSION['user_id'];

// Handle Status Updates
if (isset($_GET['action']) && isset($_GET['order_id'])) {
    $orderId = (int)$_GET['order_id'];
    $action = $_GET['action'];
    
    $validActions = ['accepted', 'in_progress', 'completed', 'delivered', 'rejected'];
    if (in_array($action, $validActions)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? AND tailor_id = ?");
        $stmt->execute([$action, $orderId, $tailorId]);
        redirect("dashboard.php", "Order status updated to $action.");
    }
}

// Fetch Tailor Stats
$activeOrders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE tailor_id = ? AND status NOT IN ('delivered', 'rejected')");
$activeOrders->execute([$tailorId]);
$countActive = $activeOrders->fetchColumn();

$completedOrders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE tailor_id = ? AND status = 'delivered'");
$completedOrders->execute([$tailorId]);
$countCompleted = $completedOrders->fetchColumn();

// Fetch Orders
$orders = $pdo->prepare("SELECT o.*, u.name as customer_name, s.name as service_name 
                         FROM orders o 
                         JOIN users u ON o.customer_id = u.id 
                         JOIN services s ON o.service_id = s.id 
                         WHERE o.tailor_id = ? 
                         ORDER BY o.created_at DESC");
$orders->execute([$tailorId]);
$myOrders = $orders->fetchAll();
?>

<div class="dashboard-container">
    <aside class="sidebar">
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li><a href="profile.php"><i class="fa-solid fa-user-tie"></i> Profile</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <h1>Tailor Dashboard</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <p>Active Orders</p>
                <h3><?php echo $countActive; ?></h3>
            </div>
            <div class="stat-card">
                <p>Total Revenue</p>
                <h3><?php 
                    $rev = $pdo->prepare("SELECT SUM(total_price) FROM orders WHERE tailor_id = ? AND payment_status = 'paid'");
                    $rev->execute([$tailorId]);
                    echo formatCurrency($rev->fetchColumn() ?: 0); 
                ?></h3>
            </div>
            <div class="stat-card">
                <p>Completed Delivery</p>
                <h3><?php echo $countCompleted; ?></h3>
            </div>
        </div>

        <?php if (isset($_SESSION['msg'])): ?>
            <div class="card" style="background: #e8f5e9; color: #2e7d32; padding: 10px; margin-bottom: 20px;">
                <?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <h3>Order Requests & Management</h3>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($myOrders)): ?>
                        <tr><td colspan="6" class="text-center">No orders yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($myOrders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td>
                            <strong><?php echo $order['customer_name']; ?></strong>
                            <br><a href="chat.php?order_id=<?php echo $order['id']; ?>" style="font-size: 0.8rem; color: var(--primary);"><i class="fa-solid fa-comment"></i> Chat</a>
                        </td>
                        <td><?php echo $order['service_name']; ?></td>
                        <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                        <td><?php echo date('M d', strtotime($order['created_at'])); ?></td>
                        <td>
                            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                <?php if ($order['status'] === 'pending'): ?>
                                    <a href="dashboard.php?action=accepted&order_id=<?php echo $order['id']; ?>" class="btn btn-primary" style="padding: 5px 8px; font-size: 0.8rem;">Accept</a>
                                    <a href="dashboard.php?action=rejected&order_id=<?php echo $order['id']; ?>" class="btn btn-danger" style="padding: 5px 8px; font-size: 0.8rem;">Reject</a>
                                <?php elseif ($order['status'] === 'accepted'): ?>
                                    <a href="dashboard.php?action=in_progress&order_id=<?php echo $order['id']; ?>" class="btn btn-secondary" style="padding: 5px 8px; font-size: 0.8rem;">Start Work</a>
                                <?php elseif ($order['status'] === 'in_progress'): ?>
                                    <a href="dashboard.php?action=completed&order_id=<?php echo $order['id']; ?>" class="btn btn-primary" style="padding: 5px 8px; font-size: 0.8rem;">Finish Work</a>
                                <?php elseif ($order['status'] === 'completed'): ?>
                                    <a href="dashboard.php?action=delivered&order_id=<?php echo $order['id']; ?>" class="btn btn-success" style="padding: 5px 8px; font-size: 0.8rem;">Mark Delivered</a>
                                <?php else: ?>
                                    <span style="font-size: 0.8rem; opacity: 0.5;">No actions</span>
                                <?php endif; ?>
                                
                                <button class="btn btn-secondary" style="padding: 5px 8px; font-size: 0.8rem;" onclick="alert('Measurements:\nChest: <?php echo $order['chest'] ?? 0; ?>\nWaist: <?php echo $order['waist'] ?? 0; ?>\nInseam: <?php echo $order['inseam'] ?? 0; ?>\nSleeve: <?php echo $order['sleeve'] ?? 0; ?>\nShoulder: <?php echo $order['shoulder'] ?? 0; ?>\nLength: <?php echo $order['length'] ?? 0; ?>')">View Size</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
