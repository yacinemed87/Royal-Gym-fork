<?php
require_once __DIR__ . "/../../config.php";
require_once PROJECT_ROOT . "/GymsManager/backend/config.php";
require_once PROJECT_ROOT . "/GymsManager/backend/db_connect.php";
require_once PROJECT_ROOT . "/GymsManager/backend/gyms.php";

$login = isset($_SESSION['user_id']);
$gym = get_gym_info();

function write_button($name, $path, $originPath)
{
    global $current_page;
    $active = ($current_page === $name) ? "active" : "";
    $url = htmlspecialchars($originPath . $path);
    $text = htmlspecialchars(ucfirst($name));

    echo "
        <li>
            <a href=\"{$url}\" class=\"{$active}\">
                {$text}
            </a>
        </li>
    ";
}

?>


<?php include PROJECT_ROOT . "/GymsManager/frontend/includes/notification.php"; ?>
<header>
    <a href="<?= GYM_BASE_URL . "/index.php"; ?>">
        <span class="name"><?= $gym["name"]; ?></span>
        <img src="<?= GYM_BASE_URL; ?>/assets/images/<?= $gym["logo"]; ?>" alt="<?= $gym["name"]; ?> Logo" class="logo">
    </a>
    <button class="menu-toggle" aria-label="Toggle menu">&#9776;</button>
    <nav>
        <ul>
            <?php
            write_button("home", "/index.php", GYM_BASE_URL);
            write_button("membership", "/client/membership.php", GYM_BASE_URL);

            if ($login) {
                $is_admin = ($_SESSION['role'] ?? '') !== 'member';

                if ($is_admin) {
                    write_button("dashboard", "/admin/dashboard.php", GYM_BASE_URL);
                } else {
                    write_button("profile", "/client/profile.php", GYM_BASE_URL);
                }
                write_button("logout", "/client/logout.php", GYM_BASE_URL);
            } else {
                write_button("login", "/GymsManager/login.php", BASE_URL);
            }
            ?>
        </ul>
        <!-- <li>
                    <a href="<?php echo BASE_URL; ?>/client/classes.php"
                        class="<?php if ($current_page == "classes") {
                            echo "active";
                        } ?>">
                        Classes
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/client/trainers.php"
                        class="<?php if ($current_page == "trainers") {
                            echo "active";
                        } ?>">
                        Trainers
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/client/contact.php"
                        class="<?php if ($current_page == "contact") {
                            echo "active";
                        } ?>">
                        Contact
                    </a>
                </li> -->
    </nav>
</header>
<script>
    document.querySelector('.menu-toggle')?.addEventListener('click', function () {
        document.querySelector('header nav')?.classList.toggle('open');
    });
</script>
