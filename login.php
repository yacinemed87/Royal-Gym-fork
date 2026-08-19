<?php
include __DIR__ . "/backend/config.php"
?>

<!doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="stylesheet" href="<?= BASE_URL; ?>/css/login.css" />
	<title>Admin Login | Royal Gym</title>
	<link rel="icon" type="image/png" href="<?= BASE_URL; ?>/assets/images/logo.png" />
</head>

<body>
	<main>
		<section id="login-container">
			<header>
				<h1>Royal Gym</h1>
				<h2>Log in</h2>
				<p>Please enter your credentials</p>
			</header>

			<form id="login-form" action="<?= BASE_URL; ?>/login.php" method="POST">
				<fieldset>
					<legend>Login Details</legend>
					<div class="form-group">
						<label for="gym-name">Gym name:</label>
						<input
							type="text"
							id="gym-name"
							name="gymname"
							placeholder="Enter your Gym name"
							required />
					</div>

					<div class="form-group">
						<label for="email">Username:</label>
						<input
							type="text"
							id="email"
							name="email"
							placeholder="Enter your username"
							required />
					</div>

					<div class="form-group">
						<label for="password">Password:</label>
						<input
							type="password"
							id="password"
							name="password"
							placeholder="Enter your password"
							required />
					</div>
					<!-- 
					<div
						id="error-display"
						style="color: #ff4d4d; margin-bottom: 10px"></div> -->
					<input type="submit" name="Log In" class="button" value="Log In">
					</input>
				</fieldset>
			</form>

			<footer>
				<p>
					Not an admin?
					<a href="<?= BASE_URL; ?>/index.php">Return to Home Page</a>
				</p>
			</footer>
		</section>
	</main>

	<script>
		document
			.getElementById("login-form")
			.addEventListener("submit", function(e) {
				e.preventDefault();
				const gym = document
					.getElementById("gymname")
					.value.trim();
				const user = document
					.getElementById("username")
					.value.trim();
				const pass = document
					.getElementById("password")
					.value.trim();

				const err = document.getElementById("error-display");

				if (!user || !pass || !gym) {
					err.textContent =
						"Please enter both username and password.";
					return;
				}
				err.textContent = "";
				window.location.href = "<?php echo BASE_URL; ?>/admin/dashboard.php";
			});
	</script>
</body>

</html>