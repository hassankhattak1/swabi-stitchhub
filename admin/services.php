<?php
require_once '../includes/header.php';
checkRole('admin');

// Add Service logic
if (isset($_POST['add_service'])) {
    $name = sanitize($_POST['name']);
    $desc = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    
    $stmt = $pdo->prepare("INSERT INTO services (name, description, price) VALUES (?, ?, ?)");
    if ($stmt->execute([$name, $desc, $price])) {
        redirect("services.php", "Service added successfully.");
    }
}

// Delete Service logic
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    if ($stmt->execute([$id])) {
        redirect("services.php", "Service deleted successfully.");
    }
}

$services = $pdo->query("SELECT * FROM services ORDER BY created_at DESC")->fetchAll();
?>

<div class="dashboard-container">
    <aside class="sidebar">
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="services.php" class="active"><i class="fa-solid fa-list"></i> Services</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>Manage Services</h1>
            <button class="btn btn-primary" onclick="document.getElementById('add-modal').style.display='block'">Add New Service</button>
        </div>
        
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
                        <th>Price</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $s): ?>
                    <tr>
                        <td><?php echo $s['id']; ?></td>
                        <td><strong><?php echo $s['name']; ?></strong></td>
                        <td><?php echo formatCurrency($s['price']); ?></td>
                        <td><?php echo $s['description']; ?></td>
                        <td>
                            <a href="services.php?delete=<?php echo $s['id']; ?>" class="btn btn-danger" style="padding: 5px 10px;" onclick="return confirm('Delete this service?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<!-- Simple Modal (Hidden by default) -->
<div id="add-modal" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000;">
    <div style="background:white; max-width:500px; margin:100px auto; padding:30px; border-radius:var(--radius);">
        <h3>Add Service</h3>
        <form method="POST">
            <div class="form-group">
                <label>Service Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Traditional Kurta" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Base Price ($)</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" name="add_service" class="btn btn-primary">Save Service</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('add-modal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
