<?php
require_once __DIR__ . "/../../backend/gyms.php";
$gym = current_gym();
?>


<header>
    <a href="<?php echo BASE_URL . $gym["home_page"]; ?>">
        <span class="name"><?php echo $gym["name"]; ?></span>
        <img src="<?php echo BASE_URL; ?>/assets/images/<?php echo $gym["logo"]; ?>" alt="<?php echo $gym["name"]; ?> Logo" class="logo">
    </a>
    <button class="menu-toggle" aria-label="Toggle menu">&#9776;</button>
    <nav>
        <ul>
            <li><a href="<?php echo BASE_URL . $gym["home_page"]; ?>" class="<?php if ($current_page == "home") {
                                                                            echo "active";
                                                                        } ?>">Home</a></li>
            <li><a href="<?php echo BASE_URL; ?>/client/classes.php" class="<?php if ($current_page == "classes") {
                                                                                echo "active";
                                                                            } ?>">Classes</a></li>
            <li><a href="<?php echo BASE_URL; ?>/client/membership.php" class="<?php if ($current_page == "membership") {
                                                                                    echo "active";
                                                                                } ?>">Membership</a></li>
            <li><a href="<?php echo BASE_URL; ?>/client/trainers.php" class="<?php if ($current_page == "trainers") {
                                                                                    echo "active";
                                                                                } ?>">Trainers</a></li>
            <li><a href="<?php echo BASE_URL; ?>/client/contact.php" class="<?php if ($current_page == "contact") {
                                                                                echo "active";
                                                                            } ?>">Contact</a></li>
            <li><a href="<?php echo BASE_URL; ?>/client/profile.php" class="<?php if ($current_page == "profile") {
                                                                                echo "active";
                                                                            } ?>">Profile</a></li>
            <li><a href="<?php echo BASE_URL; ?>/login.php">Login</a></li>
        </ul>
    </nav>
</header>

<?php
