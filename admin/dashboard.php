<?php
require_once '../includes/header.php';
checkRole('admin');

// Fetch stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalServices = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(amount) FROM payments")->fetchColumn() ?: 0;

$recentOrders = $pdo->query("SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON o.customer_id = u.id ORDER BY o.created_at DESC LIMIT 5")->fetchAll();
?>

<div class="dashboard-container">
    <aside class="sidebar">
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="services.php"><i class="fa-solid fa-list"></i> Services</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <h1>Admin Dashboard</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <p>Total Users</p>
                <h3><?php echo $totalUsers; ?></h3>
            </div>
            <div class="stat-card">
                <p>Total Orders</p>
                <h3><?php echo $totalOrders; ?></h3>
            </div>
            <div class="stat-card">
                <p>Total Revenue</p>
                <h3><?php echo formatCurrency($totalRevenue); ?></h3>
            </div>
            <div class="stat-card">
                <p>Services</p>
                <h3><?php echo $totalServices; ?></h3>
            </div>
        </div>

        <section class="card">
            <h3>Recent Orders</h3>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo $order['customer_name']; ?></td>
                        <td><?php echo formatCurrency($order['total_price']); ?></td>
                        <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
