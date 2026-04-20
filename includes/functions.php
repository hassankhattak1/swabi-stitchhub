<?php
// includes/functions.php

/**
 * Sanitize Data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Check if User is Logged In
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check User Role
 */
function checkRole($role) {
    if (!isLoggedIn() || $_SESSION['role'] !== $role) {
        header("Location: ../auth/login.php");
        exit();
    }
}

/**
 * Redirect with Message
 */
function redirect($path, $message = "", $type = "success") {
    if (!empty($message)) {
        $_SESSION['msg'] = $message;
        $_SESSION['msg_type'] = $type;
    }
    header("Location: $path");
    exit();
}

/**
 * Format Currency
 */
function formatCurrency($amount) {
    return "$" . number_format($amount, 2);
}

/**
 * Get User Details
 */
function getUser($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get Service Details
 */
function getService($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get Order Details
 */
function getOrder($pdo, $id) {
    $stmt = $pdo->prepare("SELECT o.*, s.name as service_name, c.name as customer_name, t.name as tailor_name 
                           FROM orders o 
                           JOIN services s ON o.service_id = s.id 
                           JOIN users c ON o.customer_id = c.id 
                           JOIN users t ON o.tailor_id = t.id 
                           WHERE o.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}
?>
