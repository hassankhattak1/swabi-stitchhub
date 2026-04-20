<?php
require_once '../includes/header.php';
checkRole('tailor');

$userId = $_SESSION['user_id'];

if (isset($_POST['update_profile'])) {
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $bio = sanitize($_POST['bio']);
    
    $stmt = $pdo->prepare("UPDATE users SET phone = ?, address = ?, bio = ? WHERE id = ?");
    if ($stmt->execute([$phone, $address, $bio, $userId])) {
        $_SESSION['msg'] = "Profile updated successfully.";
    }
}

$user = getUser($pdo, $userId);
?>

<div class="dashboard-container">
    <aside class="sidebar">
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li><a href="profile.php" class="active"><i class="fa-solid fa-user-tie"></i> Profile</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <h1>My Tailor Profile</h1>
        
        <?php if (isset($_SESSION['msg'])): ?>
            <div class="card" style="background: #e8f5e9; color: #2e7d32; padding: 10px; margin-bottom: 20px;">
                <?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="card" style="max-width: 600px;">
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" class="form-control" value="<?php echo $user['name']; ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" class="form-control" value="<?php echo $user['email']; ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo $user['phone']; ?>" placeholder="+123456789">
                </div>
                <div class="form-group">
                    <label>Address / Workshop Location</label>
                    <textarea name="address" class="form-control" rows="2"><?php echo $user['address']; ?></textarea>
                </div>
                <div class="form-group">
                    <label>Bio (Skills & Specialties)</label>
                    <textarea name="bio" class="form-control" rows="4" placeholder="e.g. Expert in Italian Suits and Traditional Wear. 10 years experience."><?php echo $user['bio']; ?></textarea>
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary" style="width: 100%;">Update Profile</button>
            </form>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
