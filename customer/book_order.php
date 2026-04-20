<?php
require_once '../includes/header.php';
checkRole('customer');

$customerId = $_SESSION['user_id'];
$serviceId = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;

if (!$serviceId) {
    redirect("dashboard.php", "Please select a service first.", "error");
}

$service = getService($pdo, $serviceId);
$tailors = $pdo->query("SELECT id, name FROM users WHERE role = 'tailor'")->fetchAll();

if (isset($_POST['place_order'])) {
    $tailorId = (int)$_POST['tailor_id'];
    $chest = (float)$_POST['chest'];
    $waist = (float)$_POST['waist'];
    $inseam = (float)$_POST['inseam'];
    $sleeve = (float)$_POST['sleeve'];
    $shoulder = (float)$_POST['shoulder'];
    $length = (float)$_POST['length'];
    $total = $service['price']; // Simplified: base price
    
    $stmt = $pdo->prepare("INSERT INTO orders (customer_id, tailor_id, service_id, chest, waist, inseam, sleeve, shoulder, length, total_price, status, payment_status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')");
    
    if ($stmt->execute([$customerId, $tailorId, $serviceId, $chest, $waist, $inseam, $sleeve, $shoulder, $length, $total])) {
        $orderId = $pdo->lastInsertId();
        redirect("payment.php?order_id=$orderId", "Order placed! Please complete the payment.");
    }
}
?>

<div class="container mt-20" style="padding-bottom: 50px;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 class="text-center">Customize Your Order</h2>
        <p class="text-center" style="opacity: 0.7;">Service: <strong><?php echo $service['name']; ?></strong> - Base Price: <?php echo formatCurrency($service['price']); ?></p>

        <form method="POST" class="card mt-20">
            <div class="form-group">
                <label>Choose a Tailor</label>
                <select name="tailor_id" class="form-control" required>
                    <option value="">-- Select a Specialist --</option>
                    <?php foreach ($tailors as $t): ?>
                        <option value="<?php echo $t['id']; ?>"><?php echo $t['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <hr style="margin: 25px 0; border: 0; border-top: 1px solid var(--border);">
            
            <h3 style="font-size: 1.1rem; margin-bottom: 20px;"><i class="fa-solid fa-ruler-combined"></i> Body Measurements (Inches)</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Chest</label>
                    <input type="number" step="0.1" name="chest" class="form-control" placeholder="e.g. 40.5" required>
                </div>
                <div class="form-group">
                    <label>Waist</label>
                    <input type="number" step="0.1" name="waist" class="form-control" placeholder="e.g. 34.0" required>
                </div>
                <div class="form-group">
                    <label>Inseam / Leg Length</label>
                    <input type="number" step="0.1" name="inseam" class="form-control" placeholder="e.g. 32.0" required>
                </div>
                <div class="form-group">
                    <label>Sleeve Length</label>
                    <input type="number" step="0.1" name="sleeve" class="form-control" placeholder="e.g. 25.0" required>
                </div>
                <div class="form-group">
                    <label>Shoulder Width</label>
                    <input type="number" step="0.1" name="shoulder" class="form-control" placeholder="e.g. 18.5" required>
                </div>
                <div class="form-group">
                    <label>Total Garment Length</label>
                    <input type="number" step="0.1" name="length" class="form-control" placeholder="e.g. 30.0" required>
                </div>
            </div>

            <div style="background: var(--bg); padding: 20px; border-radius: var(--radius); margin-top: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600;">Subtotal:</span>
                    <span style="font-size: 1.2rem; font-weight: 700; color: var(--primary);"><?php echo formatCurrency($service['price']); ?></span>
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 10px;">
                <button type="submit" name="place_order" class="btn btn-primary" style="flex: 2;">Confirm & Proceed to Payment</button>
                <a href="dashboard.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
