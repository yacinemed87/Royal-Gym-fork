<?php
$current_page = 'profile';
require_once __DIR__ . "/../config.php";
require_once PROJECT_ROOT . "/GymsManager/backend/require_login.php";
require_once PROJECT_ROOT . "/GymsManager/backend/gyms.php";
require_once PROJECT_ROOT . "/GymsManager/backend/handling_members.php";

$gym = get_gym_info();
$user_id = $_SESSION['user_id'] ?? 0;
$member = get_member_profile($user_id);
$sub = get_member_subscription($user_id);

$name = $member['name'] ?? $_SESSION['name'] ?? 'Member';
$email = $member['email'] ?? $_SESSION['email'] ?? '';
$phone = !empty($member['phone']) ? $member['phone'] : 'Not provided';
$gender = !empty($member['gender']) ? $member['gender'] : 'Not specified';
$joinDate = !empty($member['joinDate']) ? date('d F Y', strtotime($member['joinDate'])) : 'Recent';

// Initials avatar
$words = explode(' ', trim($name));
$initials = '';
foreach ($words as $w) {
	if (!empty($w)) $initials .= strtoupper($w[0]);
}
$initials = substr($initials, 0, 2);
?>

<!doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>My Profile | <?= htmlspecialchars($gym['name'] ?? 'Gym'); ?></title>
	<link rel="stylesheet" href="../css/profile.css" />
	<link rel="icon" type="image/png" href="../assets/images/<?= htmlspecialchars($gym['logo'] ?? 'logo.png'); ?>" />
</head>

<body>
	<?php
	include __DIR__ . "/includes/header.php";
	?>

	<main>
		<section class="profile-intro">
			<h2>My Profile</h2>
			<p>Your membership details and how much time you have left.</p>
		</section>

		<div class="profile-grid">

			<section class="profile-card">
				<div class="avatar"><?= htmlspecialchars($initials ?: 'U'); ?></div>
				<h3><?= htmlspecialchars($name); ?></h3>
				<p class="member-id">Member #<?= htmlspecialchars($member['id'] ?? $user_id); ?></p>

				<dl class="details">
					<dt>Email</dt>
					<dd><?= htmlspecialchars($email); ?></dd>

					<dt>Phone</dt>
					<dd><?= htmlspecialchars($phone); ?></dd>

					<dt>Gender</dt>
					<dd><?= htmlspecialchars($gender); ?></dd>

					<dt>Member since</dt>
					<dd><?= htmlspecialchars($joinDate); ?></dd>
				</dl>
			</section>

			<?php if ($sub): ?>
				<?php
				$planName = htmlspecialchars($sub['plan_name'] ?? 'Custom Plan');
				$pricePaid = number_format($sub['price_paid']) . ' DA';
				$durationText = htmlspecialchars(($sub['durationDays'] ?: 30) . ' days');
				$startDate = $sub['start_date'];
				$endDate = $sub['end_date'];
				?>
				<section class="sub-card" id="subscription" data-start="<?= htmlspecialchars($startDate); ?>" data-end="<?= htmlspecialchars($endDate); ?>">
					<div class="sub-head">
						<h3><?= $planName; ?></h3>
						<span class="sub-status" id="sub-status">—</span>
					</div>

					<p class="sub-price"><?= $pricePaid; ?><span class="price-unit">/<?= $durationText; ?></span></p>

					<div class="countdown">
						<span class="days-left" id="days-left">—</span>
						<span class="days-label" id="days-label">days remaining</span>
					</div>

					<div class="progress-track">
						<div class="progress-bar" id="progress-bar"></div>
					</div>
					<p class="progress-note" id="progress-note">—</p>

					<dl class="details">
						<dt>Started on</dt>
						<dd id="start-date"><?= date('d F Y', strtotime($startDate)); ?></dd>

						<dt>Expires on</dt>
						<dd id="end-date"><?= date('d F Y', strtotime($endDate)); ?></dd>
					</dl>

					<a href="<?= GYM_BASE_URL; ?>/client/membership.php" class="renew-btn">Renew / Change Plan</a>
				</section>
			<?php else: ?>
				<section class="sub-card" style="text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2.5rem 1.5rem;">
					<div class="sub-head" style="justify-content: center;">
						<h3>No Active Subscription</h3>
					</div>
					<p style="color: var(--text-muted, #94a3b8); margin: 1.5rem 0; font-size: 0.95rem;">You currently do not have an active membership plan.</p>
					<a href="<?= GYM_BASE_URL; ?>/client/membership.php" class="renew-btn">Choose a Membership Plan</a>
				</section>
			<?php endif; ?>

		</div>
	</main>

	<?php
	include __DIR__ . "/includes/footer.php";
	?>

	<script src="../js/profile.js?v=<?= filemtime(__DIR__ . '/../js/profile.js'); ?>"></script>
</body>

</html>