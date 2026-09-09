<?php
require_once __DIR__ . "/../config.php";
require_once PROJECT_ROOT . "/GymsManager/backend/config.php";
require_once PROJECT_ROOT . "/GymsManager/backend/db_connect.php";
require_once PROJECT_ROOT . "/GymsManager/backend/gyms.php";
require_once PROJECT_ROOT . "/GymsManager/backend/handling_members.php";
require_once PROJECT_ROOT . "/GymsManager/backend/membershipBack.php";
require_once PROJECT_ROOT . "/GymsManager/backend/gym_data.php";

$current_page = 'membership';
$gym = get_gym_info();
$success_message = $_SESSION['membership_success'] ?? '';
$error_message = $_SESSION['membership_error'] ?? '';
unset($_SESSION['membership_success'], $_SESSION['membership_error']);
$durations = get_durations();

$isLoggedIn = !empty($_SESSION['user_id']);
$loggedMember = $isLoggedIn ? get_member_profile($_SESSION['user_id']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$result = add_subscription($_POST);
	if ($result === 'active') {
		$_SESSION['membership_success'] = "Subscription successful! Your plan is now active.";
	} elseif ($result === 'pending') {
		$_SESSION['membership_success'] = "Renewal successful! Your new plan is queued and will start when your current one finishes.";
	} elseif ($result === 'pending_approval') {
		$_SESSION['membership_success'] = "Registration submitted! Your subscription is pending admin approval.";
	} else {
		$_SESSION['membership_error'] = "Failed to register or you already have a pending request. Please check your details and try again.";
	}
	header("Location: " . $_SERVER['REQUEST_URI']);
	exit;
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
	<link rel="stylesheet" href="../css/membership.css?v=<?= filemtime(__DIR__ . '/../css/membership.css'); ?>" />
	<link rel="icon" type="image/png"
		href="<?= GYM_BASE_URL; ?>/assets/images/<?= htmlspecialchars($gym["logo"] ?? "logo.png"); ?>">
</head>

<body>
	<?php
	include __DIR__ . "/includes/header.php"
	?>

	<section class="register" aria-labelledby="register-heading">
		<div>
			<h2 id="register-heading">
				<?= $isLoggedIn ? "Renew Membership" : ("Join " . htmlspecialchars($gym["name"] ?? "Royal Gym")); ?>
			</h2>

			<?php if (!empty($success_message)): ?>
				<div id="success-msg" style="color: #d4af37; font-weight: 700; margin-top: 10px;">
					✓
					<?= htmlspecialchars($success_message); ?>
				</div>
			<?php endif; ?>

			<?php if (!empty($error_message)): ?>
				<div id="error-msg" style="color: #ef4444; font-weight: 700; margin-top: 10px;">
					⚠
					<?= htmlspecialchars($error_message); ?>
				</div>
			<?php endif; ?>

			<br>

			<?php if ($isLoggedIn && $loggedMember): ?>
				<div class="logged-in-notice">
					<span>Logged in as <strong><?= htmlspecialchars($loggedMember['name']); ?></strong>
						(<?= htmlspecialchars($loggedMember['email']); ?>)</span>
					<span class="locked-badge">🔒 Your Info</span>
				</div>
			<?php endif; ?>

			<form id="register-form" action="<?= GYM_BASE_URL; ?>/client/membership.php" method="POST" novalidate>
				<fieldset>
					<legend>Personal Information</legend>

					<div class="row">
						<div class="col">
							<label for="name">Full Name</label>
							<input id="name" name="name" type="text"
								value="<?= htmlspecialchars($loggedMember['name'] ?? ''); ?>" <?= $isLoggedIn ? 'readonly class="readonly-input"' : 'placeholder="yacine"'; ?> required />
							<span id="name-error" role="alert"></span>
						</div>

						<div class="col">
							<label for="email">Email</label>
							<input id="email" name="email" type="email"
								value="<?= htmlspecialchars($loggedMember['email'] ?? ''); ?>" <?= $isLoggedIn ? 'readonly class="readonly-input"' : 'placeholder="yacine@example.com"'; ?> required />
							<span id="email-error" role="alert"></span>
						</div>
					</div>

					<div class="row mt-12">
						<div class="col">
							<label for="phone">Phone</label>
							<input id="phone" name="phone" type="tel"
								value="<?= htmlspecialchars($loggedMember['phone'] ?? ''); ?>" <?= $isLoggedIn ? 'readonly class="readonly-input"' : 'placeholder="0123456789"'; ?> />
							<span id="phone-error" role="alert"></span>
						</div>

						<div class="col">
							<label for="gender">Gender</label>
							<?php if ($isLoggedIn): ?>
								<input type="text"
									value="<?= htmlspecialchars($loggedMember['gender'] ?? 'Not specified'); ?>" readonly
									class="readonly-input" />
								<input id="gender" type="hidden" name="gender"
									value="<?= htmlspecialchars($loggedMember['gender'] ?? 'Male'); ?>" />
							<?php else: ?>
								<select id="gender" name="gender" required>
									<option value="">Select Gender</option>
									<option value="Male">Male</option>
									<option value="Female">Female</option>
								</select>
								<span id="gender-error" role="alert"></span>
							<?php endif; ?>
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
					<legend>Select Duration</legend>

					<div class="custom-controls" id="duration-radios" role="radiogroup"
						aria-label="Membership duration">
						<?php
						write_membership_duration_radios();
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

				<!-- Live Order Summary -->
				<div class="order-summary" id="order-summary">
					<div class="summary-header">
						<span class="summary-title">Membership Summary</span>
						<span class="summary-badge" id="summary-duration-badge">1 Month</span>
					</div>
					<div class="summary-body">
						<div class="summary-row">
							<span>Selected Plan</span>
							<strong id="summary-plan-name">—</strong>
						</div>
						<div class="summary-row">
							<span>Duration</span>
							<span id="summary-duration">—</span>
						</div>
						<div class="summary-row" id="summary-savings-row" style="display: none;">
							<span>Discount &amp; Savings</span>
							<strong class="savings-text" id="summary-savings">—</strong>
						</div>
						<div class="summary-divider"></div>
						<div class="summary-row total-row">
							<span>Total to Pay</span>
							<strong class="total-price" id="summary-total-price">0 DA</strong>
						</div>
					</div>
				</div>

				<div class="submit-row">
					<button type="submit" class="btn-ghost">
						<?= $isLoggedIn ? "Renew Plan &amp; Continue" : "Register &amp; Continue"; ?>
					</button>
					<div class="muted">
						You will be directed to secure payment.
					</div>
				</div>

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

	<script src="<?= BASE_URL ?>GymsManager/frontend/js/membership.js?v=<?= filemtime(PROJECT_ROOT . '/GymsManager/frontend/js/membership.js'); ?>"></script>
</body>

</html>