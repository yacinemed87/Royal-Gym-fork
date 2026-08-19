<?php
session_start();
require_once __DIR__ . "/backend/gyms.php";

$error = "";
$gym_input = "";
$email_input = "";

// Did the user press the Login button?
if (isset($_POST["gym"])) {

	$gym_input = trim($_POST["gym"]);
	$email_input = trim($_POST["email"]);
	$password = $_POST["password"];

	// 1. Find the gym in the registry.
	$gym = find_gym($gym_input);

	if ($gym == null) {
		$error = "No gym found with that name.";
	} else {

		// 2. Open that gym's own database.
		$conn = connect_gym($gym["db_name"]);

		if ($conn == null) {
			$error = "Could not open the database for that gym.";
		} else {

			// 3. Look for a member with this email.
			$stmt = $conn->prepare("SELECT id, name, password, role FROM members WHERE email = ?");
			$stmt->bind_param("s", $email_input);
			$stmt->execute();
			$user = $stmt->get_result()->fetch_assoc();
			$stmt->close();
			$conn->close();

			// 4. Check the password. It is stored scrambled, so we cannot
			//    compare it with ==. password_verify does the check for us.
			if ($user != null && password_verify($password, $user["password"])) {

				// 5. Remember who logged in.
				$_SESSION["user_id"] = $user["id"];
				$_SESSION["name"] = $user["name"];
				$_SESSION["role"] = $user["role"];
				$_SESSION["gym"] = $gym["db_name"];

				// 6. Send staff to the dashboard, members to their profile.
				if ($user["role"] == "admin" || $user["role"] == "super_admin") {
					$destination = "/admin/dashboard.php";
				} else {
					// A gym can have its own profile page called
					// profile-<database name>.php. If it does not exist,
					// use the normal profile page.
					$gym_profile = "/client/profile-" . $gym["db_name"] . ".php";

					if (file_exists(__DIR__ . $gym_profile)) {
						$destination = $gym_profile;
					} else {
						$destination = "/client/profile.php";
					}
				}

				header("Location: " . BASE_URL . $destination);
				exit;
			}

			// We do not say which one was wrong, so nobody can use this
			// page to find out which emails exist.
			$error = "Incorrect email or password.";
		}
	}
}
?>
?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="stylesheet" href="<?= BASE_URL; ?>/css/login.css" />
	<title>Login | Royal Gym</title>
	<link rel="icon" type="image/png" href="<?= BASE_URL; ?>/assets/images/logo.png" />
</head>

<body>
	<main>
		<section id="login-container">
			<header>
				<h1>Royal Gym</h1>
				<h2>Member &amp; Staff Access</h2>
				<p>Enter your gym and credentials to continue.</p>
			</header>

			<form id="login-form" action="<?= BASE_URL; ?>/login.php" method="POST">
				<fieldset>
					<legend>Login Details</legend>

					<div class="form-group">
						<label for="gym">Gym:</label>
						<input
							type="text"
							id="gym"
							name="gym"
							value="<?= htmlspecialchars($gym_input); ?>"
							placeholder="Enter your gym name"
							required />
					</div>

					<div class="form-group">
						<label for="email">Email:</label>
						<input
							type="email"
							id="email"
							name="email"
							value="<?= htmlspecialchars($email_input); ?>"
							placeholder="Enter your email"
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

					<div id="error-display"><?= htmlspecialchars($error); ?></div>

					<button type="submit" class="button">Login</button>
				</fieldset>
			</form>

			<footer>
				<p>
					Not a member yet?
					<a href="<?= BASE_URL; ?>/client/membership.php">Join the gym</a>
				</p>
			</footer>
		</section>
	</main>
</body>

</html>
