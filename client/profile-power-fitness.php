<?php
$current_page = 'profile';
$active_gym   = 'power-fitness';
?>

<!doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>My Profile | PowerFitness</title>
	<link rel="stylesheet" href="../css/profile.css" />
	<link rel="icon" type="image/png" href="../assets/images/logo2.png" />
</head>

<body>
	<?php
	include __DIR__ . "/includes/header.php"
	?>

	<main>
		<section class="profile-intro">
			<h2>My Profile</h2>
			<p>Your membership details and how much time you have left.</p>
		</section>

		<!-- Placeholder member. Swap for real data when the backend is wired up. -->
		<div class="profile-grid">

			<section class="profile-card">
				<div class="avatar">AB</div>
				<h3>Ahmed Benali</h3>
				<p class="member-id">Member #1</p>

				<dl class="details">
					<dt>Email</dt>
					<dd>ahmed.benali@gmail.com</dd>

					<dt>Phone</dt>
					<dd>0551234567</dd>

					<dt>Gender</dt>
					<dd>Male</dd>

					<dt>Member since</dt>
					<dd>15 January 2024</dd>
				</dl>
			</section>

			<section class="sub-card" id="subscription" data-start="2026-08-01" data-end="2026-08-31">
				<div class="sub-head">
					<h3>Premium Plan</h3>
					<span class="sub-status" id="sub-status">—</span>
				</div>

				<p class="sub-price">24,900 DA<span class="price-unit">/1 month</span></p>

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
					<dd id="start-date">—</dd>

					<dt>Expires on</dt>
					<dd id="end-date">—</dd>
				</dl>

				<a href="<?php echo BASE_URL; ?>/client/membership.php" class="renew-btn">Renew Membership</a>
			</section>

		</div>
	</main>

	<?php
	include __DIR__ . "/includes/footer.php"
	?>

	<script src="../js/profile.js"></script>
	<script>
		document.querySelector('.menu-toggle').addEventListener('click', function() {
			document.querySelector('header nav').classList.toggle('open');
		});
	</script>
</body>

</html>
