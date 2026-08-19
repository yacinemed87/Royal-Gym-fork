<?php
$current_page = 'membership';
?>


<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />

	<title>Royal Gym — Membership</title>

	<link
		href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;600&family=Roboto:wght@700&display=swap"
		rel="stylesheet" />
	<link rel="stylesheet" href="../css/membership.css" />
	<link rel="icon" type="image/png" href="../assets/images/logo.png">
</head>

<body>
	<?php
	include __DIR__ . "/includes//header.php"
	?>
	<section aria-labelledby="plans-heading">
		<h2 id="plans-heading" class="sr-only">Membership Plans</h2>

		<div class="plans-grid" id="plans-grid">
			<!-- Rendered by membership.js from data.js -->
		</div>
	</section>

	<section class="register" aria-labelledby="register-heading">
		<div>
			<h2 id="register-heading">Join Royal Gym</h2>

			<form id="register-form" action="#" method="post" novalidate>
				<fieldset>
					<legend>Personal Information</legend>

					<div class="row">
						<div class="col">
							<label for="fullname">Full Name</label>
							<input id="fullname" name="fullname" type="text" placeholder="yacine" required />
							<span id="fullname-error" role="alert"></span>
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
							<input id="phone" name="phone" type="tel" placeholder="+213 555 555 5555" />
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

					<div class="row mt-12">
						<div class="col">
							<label for="dob">Date of Birth</label>
							<input id="dob" name="dob" type="date" />
							<span id="dob-error" role="alert"></span>
						</div>
					</div>
				</fieldset>

				<fieldset class="mt-12">
					<legend>Choose a Plan</legend>

					<div class="custom-controls" id="plan-radios" role="radiogroup" aria-label="Membership plans">
						<!-- Rendered by membership.js from data.js -->
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

				<div id="success-msg" style="
							display: none;
							color: #d4af37;
							font-weight: 700;
							margin-top: 10px;
						">
					✓ Registration submitted! We'll be in touch shortly.
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

	<script type="module" src="../js/membership.js"></script>
	<script>
		//need explanation
		document.querySelector('.menu-toggle').addEventListener('click', function() {
			document.querySelector('header nav').classList.toggle('open');
		});
	</script>
</body>

</html>
