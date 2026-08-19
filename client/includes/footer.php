<?php
require_once __DIR__ . "/../../backend/gyms.php";
$gym = current_gym();
?>

<footer>
    <?php if (isset($current_page) && $current_page === "home") : ?>
        <section class="contact-details">
            <h2>Our Contact Details</h2>
            <address>
                <p><?php echo htmlspecialchars($gym["name"]); ?></p>
                <?php if ($gym["address"]) : ?><p><?php echo htmlspecialchars($gym["address"]); ?></p><?php endif; ?>
                <?php if ($gym["phone"]) : ?><p><?php echo htmlspecialchars($gym["phone"]); ?></p><?php endif; ?>
                <?php if ($gym["email"]) : ?><p><?php echo htmlspecialchars($gym["email"]); ?></p><?php endif; ?>
                <a href="#">Instagram</a>
                <a href="#">Facebook</a>
            </address>
        </section>
    <?php endif; ?>

    <p>&copy; 2026 <?php echo htmlspecialchars($gym["name"]); ?>. All rights reserved.</p>

    <?php if (isset($current_page) && $current_page === "home") : ?>
        <a href="<?php echo BASE_URL; ?>/login.php">Admin Login</a>
    <?php endif; ?>
</footer>
