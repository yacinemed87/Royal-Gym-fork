<?php
require_once __DIR__ . "/../config.php";
require_once PROJECT_ROOT . "/GymsManager/backend/config.php";
require_once PROJECT_ROOT . "/GymsManager/backend/db_connect.php";
require_once PROJECT_ROOT . "/GymsManager/backend/gyms.php";
require_once PROJECT_ROOT . "/GymsManager/backend/membershipBack.php";

$current_page = 'membership';
$gym = get_gym_info();
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$result = add_subscription($_POST);
	if ($result) {
		$success_message = "Registration submitted! Your subscription is now active.";
	} else {
		$error_message = "Failed to register. Please check your details and try again.";
	}
}
?>


<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />

	<title><?= htmlspecialchars($gym["name"] ?? "Royal Gym"); ?> — Membership</title>

	<link
		href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;600&family=Roboto:wght@700&display=swap"
		rel="stylesheet" />
	<link rel="stylesheet" href="../css/membership.css" />
	<link rel="icon" type="image/png" href="<?= GYM_BASE_URL; ?>/assets/images/<?= htmlspecialchars($gym["logo"] ?? "logo.png"); ?>">
</head>

<body>
	<?php
	include __DIR__ . "/includes/header.php"
	?>
	<section aria-labelledby="plans-heading">
		<h2 id="plans-heading" class="sr-only">Membership Plans</h2>

		<div class="plans-grid" id="plans-grid">
			<?php
			write_membership_plan_cards();
			?>
		</div>
	</section>

	<section class="register" aria-labelledby="register-heading">
		<div>
			<h2 id="register-heading">Join <?= htmlspecialchars($gym["name"] ?? "Royal Gym"); ?></h2>

			<form id="register-form" action="<?= GYM_BASE_URL; ?>/client/membership.php" method="POST" novalidate>
				<fieldset>
					<legend>Personal Information</legend>

					<div class="row">
						<div class="col">
							<label for="name">Full Name</label>
							<input id="name" name="name" type="text" placeholder="yacine" required />
							<span id="name-error" role="alert"></span>
						</div>

						<div class="col">
							<label for="email">Email</label>
							<input id="email" name="email" type="email" placeholder="yacine@example.com" required />
							<span id="email-error" role="alert"></span>
						</div>
					</div>

					<div class="row mt-12">
						<div class="col">
							<label for="phone">Phone</label>
							<input id="phone" name="phone" type="tel" placeholder="0123456789" />
							<span id="phone-error" role="alert"></span>
						</div>

						<div class="col">
							<label for="gender">Gender</label>
							<select id="gender" name="gender" required>
								<option value="">Select Gender</option>
								<option value="Male">Male</option>
								<option value="Female">Female</option>
							</select>
							<span id="gender-error" role="alert"></span>
						</div>
					</div>
				</fieldset>

				<fieldset class="mt-12">
					<legend>Choose a Plan</legend>

					<div class="custom-controls" id="plan-radios" role="radiogroup" aria-label="Membership plans">
						<?php
						write_membership_plan_radios();
						?>
					</div>
				</fieldset>

				<fieldset class="mt-12">
					<legend>Terms</legend>

					<label class="custom terms">
						<input type="checkbox" name="terms" required />
						<span class="control" aria-hidden="true"></span>
						<span>I agree to the terms &amp; conditions</span>
					</label>
				</fieldset>

				<div class="submit-row">
					<button type="submit" class="btn-ghost">
						Register &amp; Continue
					</button>
					<div class="muted">
						You will be directed to secure payment.
					</div>
				</div>

				<?php if (!empty($success_message)): ?>
					<div id="success-msg" style="color: #d4af37; font-weight: 700; margin-top: 10px;">
						✓ <?= htmlspecialchars($success_message); ?>
					</div>
				<?php endif; ?>

				<?php if (!empty($error_message)): ?>
					<div id="error-msg" style="color: #ef4444; font-weight: 700; margin-top: 10px;">
						⚠ <?= htmlspecialchars($error_message); ?>
					</div>
				<?php endif; ?>

				<div class="payments">
					<div class="pay">CIB</div>
					<div class="pay">EDDAHABIA</div>
				</div>
			</form>
		</div>
	</section>

	<?php
	include __DIR__ . "/includes/footer.php"
	?>

	<script src="../js/membership.js"></script>
</body>

</html>