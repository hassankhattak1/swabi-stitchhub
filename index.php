<?php
require_once 'includes/header.php';

// Fetch sample services
$services = $pdo->query("SELECT * FROM services LIMIT 3")->fetchAll();
?>

<section class="hero">
    <div class="container">
        <h1>Bespoke Tailoring. <span style="color: var(--secondary);">Redefined.</span></h1>
        <p style="font-size: 1.2rem; margin-bottom: 30px; max-width: 700px; margin-left: auto; margin-right: auto;">
            Connect with expert tailors, get precise measurements, and receive custom-fitted clothing at your doorstep.
        </p>
        <div style="display: flex; gap: 15px; justify-content: center;">
            <a href="auth/register.php" class="btn btn-secondary">Get Started Now</a>
            <a href="#services" class="btn btn-primary" style="background: rgba(255,255,255,0.1); border: 1px solid white;">Explore Services</a>
        </div>
    </div>
</section>

<div class="container" id="services" style="margin-top: 80px;">
    <h2 class="text-center">Our Premium Services</h2>
    <p class="text-center" style="margin-bottom: 50px; opacity: 0.7;">Crafted with precision and care for every occasion.</p>
    
    <div class="stats-grid">
        <?php foreach ($services as $s): ?>
        <div class="card" style="text-align: center; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="background: var(--bg); height: 200px; border-radius: var(--radius); margin-bottom: 20px; display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-scissors" style="font-size: 4rem; color: var(--primary);"></i>
            </div>
            <h3><?php echo $s['name']; ?></h3>
            <p style="font-size: 0.9rem; margin-bottom: 15px;"><?php echo $s['description']; ?></p>
            <p style="font-size: 1.2rem; font-weight: 700; color: var(--primary);">Starting at <?php echo formatCurrency($s['price']); ?></p>
            <div style="margin-top: 20px;">
                <a href="customer/dashboard.php" class="btn btn-primary" style="width: 100%;">Book Service</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<section style="background: var(--surface); padding: 80px 0; margin-top: 80px;">
    <div class="container" style="display: flex; align-items: center; gap: 50px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 300px;">
            <h2 style="font-size: 2.5rem;">For the <span style="color: var(--primary);">Master Tailors</span></h2>
            <p style="margin-bottom: 25px; opacity: 0.8; font-size: 1.1rem;">
                Grow your business with StitchHub. Manage orders, communicate with clients, and showcase your skills to a wider audience.
            </p>
            <ul style="margin-bottom: 30px;">
                <li style="margin-bottom: 10px;"><i class="fa-solid fa-check-circle" style="color: var(--success); margin-right: 10px;"></i> Order Management Dashboard</li>
                <li style="margin-bottom: 10px;"><i class="fa-solid fa-check-circle" style="color: var(--success); margin-right: 10px;"></i> Real-time Client Chat</li>
                <li style="margin-bottom: 10px;"><i class="fa-solid fa-check-circle" style="color: var(--success); margin-right: 10px;"></i> Detailed Design Requirements</li>
            </ul>
            <a href="auth/register.php?role=tailor" class="btn btn-primary">Join as a Tailor</a>
        </div>
        <div style="flex: 1; min-width: 300px; background: var(--bg); height: 400px; border-radius: 20px; display: flex; align-items: center; justify-content: center; position: relative;">
             <i class="fa-solid fa-user-tie" style="font-size: 10rem; color: var(--primary-light); opacity: 0.2;"></i>
             <div style="position: absolute; bottom: 20px; left: 20px; right: 20px; background: white; padding: 20px; border-radius: 12px; box-shadow: var(--shadow);">
                <p style="font-style: italic; font-size: 0.9rem;">"StitchHub has transformed my workshop from a local shop to a digital powerhouse."</p>
                <p style="margin-top: 10px; font-weight: 700;">- Master Ali, Lead Tailor</p>
             </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
