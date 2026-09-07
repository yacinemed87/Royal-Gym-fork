<?php
$current_page = 'profile';
require_once __DIR__ . "/../config.php";
require_once PROJECT_ROOT . "/GymsManager/backend/require_login.php";
require_once PROJECT_ROOT . "/GymsManager/backend/gyms.php";
require_once PROJECT_ROOT . "/GymsManager/backend/handling_members.php";

$gym = get_gym_info();
$user_id = $_SESSION['user_id'] ?? 0;
$profile_success = $_SESSION['profile_success'] ?? '';
$profile_error = $_SESSION['profile_error'] ?? '';
unset($_SESSION['profile_success'], $_SESSION['profile_error']);

$member = get_member_profile($user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
	$newName = trim($_POST['name'] ?? '');
	$newEmail = trim($_POST['email'] ?? '');
	$newPhone = trim($_POST['phone'] ?? '');
	$newGender = $_POST['gender'] ?? 'Male';
	$newPassword = $_POST['password'] ?? '';

	$currentEmail = $member['email'] ?? '';
	$emailChangeRequested = false;
	$err = '';

	if (strlen($newName) < 3) {
		$err = "Full Name must be at least 3 characters.";
	} elseif (!empty($newEmail) && strtolower($newEmail) !== strtolower($currentEmail)) {
		if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
			$err = "Please enter a valid email address.";
		} else {
			// Check if email is already taken by another account
			$checkStmt = $connGym->prepare("SELECT id FROM members WHERE email = ? AND id != ?");
			$checkStmt->bind_param("si", $newEmail, $user_id);
			$checkStmt->execute();
			$exists = $checkStmt->get_result()->fetch_assoc();
			$checkStmt->close();

			if ($exists) {
				$err = "This email address is already in use by another account.";
			} else {
				// Record request in requests table (request_type='email_change', amount=NULL, new_value=newEmail)
				insert_member_request($user_id, 'email_change', null, $newEmail, 'Member requested email change');
				$emailChangeRequested = true;
			}
		}
	}

	if (empty($err)) {
		$updated = update_member_profile($user_id, $newName, $newPhone, $newGender, $newPassword);
		if ($updated) {
			$_SESSION['name'] = $newName;
			if ($emailChangeRequested) {
				$_SESSION['profile_success'] = "Profile updated! Your request to change email to '" . htmlspecialchars($newEmail) . "' has been submitted for admin approval.";
			} else {
				$_SESSION['profile_success'] = "Profile updated successfully!";
			}
		} else {
			$_SESSION['profile_error'] = "Failed to update profile. Please try again.";
		}
	} else {
		$_SESSION['profile_error'] = $err;
	}

	// Post/Redirect/Get: Prevents resubmission when reloading the page!
	header("Location: " . $_SERVER['REQUEST_URI']);
	exit;
}

$pendingEmailReq = get_pending_email_request($user_id);
$sub = get_member_subscription($user_id);
$can_change = can_change_plan($sub);

$name = $member['name'] ?? $_SESSION['name'] ?? 'Member';
$email = $member['email'] ?? $_SESSION['email'] ?? '';
$phone = !empty($member['phone']) ? $member['phone'] : 'Not provided';
$gender = !empty($member['gender']) ? $member['gender'] : 'Not specified';
$joinDate = !empty($member['joinDate']) ? date('d F Y', strtotime($member['joinDate'])) : 'Recent';

// Initials avatar
$words = explode(' ', trim($name));
$initials = '';
foreach ($words as $w) {
	if (!empty($w))
		$initials .= strtoupper($w[0]);
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

		<?php if (!empty($profile_success)): ?>
			<div class="profile-msg profile-msg--success">✓ <?= htmlspecialchars($profile_success); ?></div>
		<?php endif; ?>

		<?php if (!empty($profile_error)): ?>
			<div class="profile-msg profile-msg--error">⚠ <?= htmlspecialchars($profile_error); ?></div>
		<?php endif; ?>

		<div class="profile-grid">

			<section class="profile-card">
				<div class="avatar"><?= htmlspecialchars($initials ?: 'U'); ?></div>
				<h3><?= htmlspecialchars($name); ?></h3>
				<p class="member-id">Member #<?= htmlspecialchars($member['id'] ?? $user_id); ?></p>

				<dl class="details">
					<dt>Email</dt>
					<dd>
						<?= htmlspecialchars($email); ?>
						<?php if ($pendingEmailReq): ?>
							<span class="email-pending-badge" title="Awaiting admin approval">⏳ Change pending: <?= htmlspecialchars($pendingEmailReq['new_value']); ?></span>
						<?php endif; ?>
					</dd>

					<dt>Phone</dt>
					<dd><?= htmlspecialchars($phone); ?></dd>

					<dt>Gender</dt>
					<dd><?= htmlspecialchars($gender); ?></dd>

					<dt>Member since</dt>
					<dd><?= htmlspecialchars($joinDate); ?></dd>
				</dl>

				<button type="button" class="edit-profile-btn" id="openEditModal">✎ Edit Profile</button>
			</section>

			<?php if ($sub): ?>
				<?php
				$planName = htmlspecialchars($sub['plan_name'] ?? 'Custom Plan');
				$pricePaid = number_format($sub['price_paid']) . ' DA';
				$durationText = htmlspecialchars(($sub['durationMonths'] ?: 1) . ' Month');
				$startDate = $sub['start_date'];
				$endDate = $sub['end_date'];
				$status = strtolower($sub['status'] ?? 'active');

				$hasPending = !empty($sub['pending_sub']);
				$pending = $sub['pending_sub'] ?? null;

				// Dates
				$todayTs = strtotime(date('Y-m-d'));
				$startTs = strtotime($startDate);
				$activeEndTs = strtotime($endDate);

				// Active plan days left
				$activeDaysLeft = (int) max(0, ceil(($activeEndTs - $todayTs) / 86400));

				// Furthest end date: counts BOTH active and pending subscriptions!
				$furthestEndDate = $sub['furthest_end_date'] ?? ($hasPending ? $pending['end_date'] : $endDate);
				$furthestEndTs = strtotime($furthestEndDate);
				$totalDaysRemaining = (int) max(0, ceil(($furthestEndTs - $todayTs) / 86400));

				// Months + extra days breakdown
				$monthsRemaining = (int) floor($totalDaysRemaining / 30);
				$daysRemainingExtra = $totalDaysRemaining % 30;

				// Progress bar calculation
				$totalSpanDays = (int) max(1, ceil(($furthestEndTs - $startTs) / 86400));
				$daysUsed = (int) max(0, floor(($todayTs - $startTs) / 86400));
				$progressPct = min(100, max(0, round(($daysUsed / $totalSpanDays) * 100)));

				// Status badge
				if ($status === 'expired' || ($totalDaysRemaining === 0 && !$hasPending)) {
					$statusBadgeClass = 'is-expired';
					$statusLabel = 'Expired';
				} elseif ($activeDaysLeft <= 7 && !$hasPending) {
					$statusBadgeClass = 'is-expiring';
					$statusLabel = 'Expiring Soon';
				} else {
					$statusBadgeClass = 'is-active';
					$statusLabel = 'Active';
				}
				?>
				<section class="sub-card <?= $statusBadgeClass; ?>" id="subscription" data-start="<?= htmlspecialchars($startDate); ?>"
					data-end="<?= htmlspecialchars($furthestEndDate); ?>">
					<div class="sub-head">
						<h3><?= $planName; ?></h3>
						<span class="sub-status" id="sub-status"><?= $statusLabel; ?></span>
					</div>

					<p class="sub-price"><?= $pricePaid; ?><span class="price-unit">/<?= $durationText; ?></span></p>

					<div class="countdown">
						<?php if ($monthsRemaining > 0): ?>
							<div class="countdown-dual">
								<div class="count-block">
									<span class="count-number"><?= $monthsRemaining; ?></span>
									<span class="count-sublabel"><?= $monthsRemaining === 1 ? 'Month' : 'Months'; ?></span>
								</div>
								<?php if ($daysRemainingExtra > 0): ?>
									<span class="count-separator">+</span>
									<div class="count-block">
										<span class="count-number"><?= $daysRemainingExtra; ?></span>
										<span class="count-sublabel"><?= $daysRemainingExtra === 1 ? 'Day' : 'Days'; ?></span>
									</div>
								<?php endif; ?>
							</div>
							<span class="days-label">(<?= $totalDaysRemaining; ?> <?= $totalDaysRemaining === 1 ? 'day' : 'days'; ?> total remaining)</span>
						<?php else: ?>
							<span class="days-left" id="days-left"><?= $totalDaysRemaining; ?></span>
							<span class="days-label" id="days-label"><?= $totalDaysRemaining === 1 ? 'day remaining' : 'days remaining'; ?></span>
						<?php endif; ?>
					</div>

					<div class="progress-track">
						<div class="progress-bar" id="progress-bar" style="width: <?= $progressPct; ?>%;"></div>
					</div>

					<?php if ($hasPending): ?>
						<p class="progress-note" id="progress-note">
							<?= $activeDaysLeft; ?> days on current plan + <?= intval($pending['durationMonths'] * 30); ?> days on upcoming plan
						</p>

						<div class="queued-renewal-banner">
							<span class="queued-renewal-tag">✦ Upcoming Renewal Queued</span>
							<div class="queued-renewal-details">
								<strong><?= htmlspecialchars($pending['plan_name']); ?></strong>
								<span>Starts <?= date('d F Y', strtotime($pending['start_date'])); ?> (<?= $pending['durationMonths']; ?> <?= $pending['durationMonths'] == 1 ? 'Month' : 'Months'; ?>)</span>
							</div>
						</div>
					<?php else: ?>
						<p class="progress-note" id="progress-note">
							Day <?= $daysUsed; ?> of <?= $totalSpanDays; ?>
						</p>
					<?php endif; ?>

					<dl class="details">
						<dt>Started on</dt>
						<dd id="start-date"><?= date('d F Y', strtotime($startDate)); ?></dd>

						<dt>Current plan ends</dt>
						<dd id="end-date"><?= date('d F Y', strtotime($endDate)); ?></dd>

						<?php if ($hasPending): ?>
							<dt>Total access until</dt>
							<dd style="color: #4ade80; font-weight: 700;"><?= date('d F Y', strtotime($furthestEndDate)); ?></dd>
						<?php endif; ?>
					</dl>

					<a href="<?= GYM_BASE_URL; ?>/client/membership.php" class="renew-btn">Renew Plan</a>
					<?php if ($can_change): ?>
						<a href="<?= GYM_BASE_URL; ?>/client/change_plan.php" class="change-plan-btn">⇄ Change Plan</a>
					<?php endif; ?>
				</section>
			<?php else: ?>
				<section class="sub-card"
					style="text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2.5rem 1.5rem;">
					<div class="sub-head" style="justify-content: center;">
						<h3>No Active Subscription</h3>
					</div>
					<p style="color: var(--text-muted, #94a3b8); margin: 1.5rem 0; font-size: 0.95rem;">You currently do not
						have an active membership plan.</p>
					<a href="<?= GYM_BASE_URL; ?>/client/membership.php" class="renew-btn">Choose a Membership Plan</a>
				</section>
			<?php endif; ?>

		</div>
	</main>

	<!-- Edit Profile Modal -->
	<div id="editModal" class="modal-overlay hidden">
		<div class="modal-card">
			<div class="modal-header">
				<h3>Edit Profile</h3>
				<button type="button" class="modal-close" id="closeEditModal">&times;</button>
			</div>

			<form action="" method="POST" class="modal-form">
				<input type="hidden" name="action" value="update_profile" />

				<div class="form-group">
					<label for="edit_name">Full Name</label>
					<input type="text" id="edit_name" name="name" value="<?= htmlspecialchars($member['name'] ?? ''); ?>" required />
				</div>

				<div class="form-group">
					<label for="edit_email">Email Address <span class="hint">(Requires admin approval)</span></label>
					<input type="email" id="edit_email" name="email" value="<?= htmlspecialchars($member['email'] ?? ''); ?>" required />
					<?php if ($pendingEmailReq): ?>
						<span class="pending-email-hint">⏳ Request pending: <strong><?= htmlspecialchars($pendingEmailReq['new_value']); ?></strong> (awaiting admin approval)</span>
					<?php endif; ?>
				</div>

				<div class="form-group">
					<label for="edit_phone">Phone Number</label>
					<input type="tel" id="edit_phone" name="phone" value="<?= htmlspecialchars($member['phone'] ?? ''); ?>" placeholder="0551234567" />
				</div>

				<div class="form-group">
					<label for="edit_gender">Gender</label>
					<select id="edit_gender" name="gender">
						<option value="Male" <?= ($member['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
						<option value="Female" <?= ($member['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
					</select>
				</div>

				<div class="form-group">
					<label for="edit_password">New Password <span class="hint">(Leave blank to keep current)</span></label>
					<input type="password" id="edit_password" name="password" placeholder="••••••••" autocomplete="new-password" />
				</div>

				<div class="modal-actions">
					<button type="button" class="btn-cancel" id="cancelEditModal">Cancel</button>
					<button type="submit" class="btn-save">Save Changes</button>
				</div>
			</form>
		</div>
	</div>

	<?php
	include __DIR__ . "/includes/footer.php";
	?>

	<script>
	document.addEventListener('DOMContentLoaded', () => {
		const modal = document.getElementById('editModal');
		const openBtn = document.getElementById('openEditModal');
		const closeBtn = document.getElementById('closeEditModal');
		const cancelBtn = document.getElementById('cancelEditModal');

		function openModal() {
			if (modal) modal.classList.remove('hidden');
		}
		function closeModal() {
			if (modal) modal.classList.add('hidden');
		}

		if (openBtn) openBtn.addEventListener('click', openModal);
		if (closeBtn) closeBtn.addEventListener('click', closeModal);
		if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

		if (modal) {
			modal.addEventListener('click', (e) => {
				if (e.target === modal) closeModal();
			});
		}
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
				closeModal();
			}
		});
	});
	</script>
</body>

</html>