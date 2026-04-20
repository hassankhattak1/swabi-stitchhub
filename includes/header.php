<?php
// includes/header.php
require_once 'config.php';
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . " - " . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container nav-content">
            <a href="<?php echo BASE_URL; ?>" class="logo">Stitch<span>Hub</span></a>
            
            <ul class="nav-links">
                <?php if (!isLoggedIn()): ?>
                    <li><a href="<?php echo BASE_URL; ?>index.php">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>auth/login.php">Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-secondary">Register</a></li>
                <?php else: ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/users.php">Users</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/services.php">Services</a></li>
                    <?php elseif ($_SESSION['role'] === 'tailor'): ?>
                        <li><a href="<?php echo BASE_URL; ?>tailor/dashboard.php">Dashboard</a></li>
                        <li><a href="<?php echo BASE_URL; ?>tailor/profile.php">My Profile</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>customer/dashboard.php">Dashboard</a></li>
                        <li><a href="<?php echo BASE_URL; ?>customer/dashboard.php#tailors">Find Tailors</a></li>
                    <?php endif; ?>
                    
                    <li><a href="<?php echo BASE_URL; ?>auth/logout.php" style="color: #ff5252;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main>
