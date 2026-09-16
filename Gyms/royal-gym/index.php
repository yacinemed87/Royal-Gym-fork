<?php

require_once __DIR__ . "/config.php";
require_once PROJECT_ROOT . "/GymsManager/backend/config.php";
require_once PROJECT_ROOT . "/GymsManager/backend/db_connect.php";
require_once PROJECT_ROOT . "/GymsManager/backend/gyms.php";
require_once PROJECT_ROOT . "/GymsManager/backend/indexBack.php";
require_once PROJECT_ROOT . "/GymsManager/backend/gym_data.php";

$login = isset($_SESSION['user_id']);
$current_page = "home";
$gym = get_gym_info();
$durations = get_durations();
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="./css/index.css?v=<?= filemtime(__DIR__ . '/css/index.css'); ?>">
	<title><?= htmlspecialchars($gym["name"] ?? "Gym"); ?></title>
	<link rel="icon" type="image/png" href="assets/images/<?= htmlspecialchars($gym["logo"] ?? "logo.png"); ?>">
</head>

<body>
	<?php
	include __DIR__ . "/client/includes/header.php"
		?>
	<main>
		<section class="hero">
			<h1><?= htmlspecialchars($gym["name"] ?? "Gym"); ?></h1>
			<p class="tagline"><?= htmlspecialchars($gym["tagline"] ?? ""); ?></p>
			<a href="./client/membership.php">Sign Up Today</a>
		</section>
		<section class="facilities">
			<h2>Facilities</h2>
			<div class="facilities-container">
				<?php
				write_facilities();
				?>
			</div>
		</section>
		<section class="membership" id="membership">
			<h2>Membership Plans</h2>

			<?php if (!empty($durations)): ?>
				<div class="duration-switcher" id="duration-switcher">
					<?php foreach ($durations as $dur): ?>
						<button type="button" class="duration-pill<?= intval($dur['months']) === 1 ? ' active' : ''; ?>"
							data-duration-id="<?= intval($dur['id']); ?>" data-months="<?= intval($dur['months']); ?>"
							data-discount="<?= floatval($dur['discount_pct']); ?>">
							<?= htmlspecialchars($dur['label']); ?>
							<?php if ($dur['discount_pct'] > 0): ?>
								<span class="pill-badge">-<?= intval($dur['discount_pct']); ?>%</span>
							<?php endif; ?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="plans-grid" id="plans-grid">
				<?php
				write_membership_plan_cards();
				?>
			</div>
		</section>
		<section class="opening-hours">
			<h2>Opening Hours</h2>
			<?php
			write_opening_hours_table();
			?>
		</section>
	</main>
	<?php
	include __DIR__ . "/client/includes/footer.php"
		?>
	<script
		src="<?= BASE_URL ?>GymsManager/frontend/js/index-plans.js?v=<?= filemtime(PROJECT_ROOT . '/GymsManager/frontend/js/index-plans.js'); ?>"></script>
</body>

</html>