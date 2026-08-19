<?php
require_once __DIR__ . "/../../backend/gyms.php";
?>

<footer>
    <?php if (isset($current_page) && $current_page === "home") : ?>
        <section class="contact-details">
            <h2>Our Contact Details</h2>
            <address>
                <p>Gym Management System</p>
                <p>123 Side Street, Constantine, Algeria</p>
                <p>+213561658876</p>
                <p>contact@royalgym.dz</p>
                <a href="#">Instagram</a>
                <a href="#">Facebook</a>
            </address>
        </section>
    <?php endif; ?>

    <p>&copy; 2026 <?php echo current_gym()["name"]; ?>. All rights reserved.</p>

    <?php if (isset($current_page) && $current_page === "home") : ?>
        <a href="<?php echo BASE_URL; ?>/login.php">Admin Login</a>
    <?php endif; ?>
</footer>