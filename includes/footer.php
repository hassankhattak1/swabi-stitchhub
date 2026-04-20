<?php // includes/footer.php ?>
    </main>
    <footer style="background: var(--primary-dark); color: white; padding: 40px 0; margin-top: 50px;">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div>
                <h3>StitchHub</h3>
                <p style="opacity: 0.7;">Premium Tailoring Management System</p>
            </div>
            <div style="display: flex; gap: 40px;">
                <div>
                    <h4 style="color: var(--secondary);">Quick Links</h4>
                    <ul style="opacity: 0.7; font-size: 0.9rem;">
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h4 style="color: var(--secondary);">Follow Us</h4>
                    <div style="display: flex; gap: 15px; font-size: 1.2rem; margin-top: 10px;">
                        <a href="#"><i class="fa-brands fa-facebook"></i></a>
                        <a href="#"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#"><i class="fa-brands fa-twitter"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="container text-center" style="margin-top: 30px; opacity: 0.5; font-size: 0.8rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
            &copy; <?php echo date('Y'); ?> StitchHub. All rights reserved.
        </div>
    </footer>
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
</body>
</html>
