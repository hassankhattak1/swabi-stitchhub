<?php
require_once '../includes/header.php';
checkRole('admin');

// Delete User logic
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    if ($stmt->execute([$id])) {
        redirect("users.php", "User deleted successfully.");
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY role, created_at DESC")->fetchAll();
?>

<div class="dashboard-container">
    <aside class="sidebar">
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li><a href="users.php" class="active"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="services.php"><i class="fa-solid fa-list"></i> Services</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <h1>Manage Users</h1>
        
        <?php if (isset($_SESSION['msg'])): ?>
            <div class="card" style="background: #e8f5e9; color: #2e7d32; padding: 10px; margin-bottom: 20px;">
                <?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo $u['name']; ?></td>
                        <td><?php echo $u['email']; ?></td>
                        <td><span class="badge" style="background: #eee;"><?php echo ucfirst($u['role']); ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                        <td>
                            <?php if ($u['role'] !== 'admin'): ?>
                                <a href="users.php?delete=<?php echo $u['id']; ?>" class="btn btn-danger" style="padding: 5px 10px;" onclick="return confirm('Delete this user?')">Delete</a>
                            <?php else: ?>
                                <span style="font-size: 0.8rem; opacity: 0.5;">System</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
