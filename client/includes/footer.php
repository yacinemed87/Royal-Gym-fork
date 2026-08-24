<?php
require_once __DIR__ . "/../../backend/gyms.php";
$gym = get_gym_info();
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
</footer>