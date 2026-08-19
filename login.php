<?php
session_start();
require_once __DIR__ . "/backend/gyms.php";

$error = "";
$gym_input = "";
$email_input = "";

if (($_SERVER["REQUEST_METHOD"] ?? "") === "POST") {
	$gym_input   = trim($_POST["gym"] ?? "");
	$email_input = trim($_POST["email"] ?? "");
	$password    = $_POST["password"] ?? "";

	if ($gym_input === "" || $email_input === "" || $password === "") {
		$error = "Please fill in the gym, your email and your password.";
	} else {
		// Look the gym up in the registry, then open its own database.
		$gym  = gym_by_name($gym_input);
		$conn = $gym === null ? null : gym_db_connect($gym);

		if ($conn === null) {
			$error = "No gym found with that name.";
		} else {
			$gym_db = $gym["db_name"];
			try {
				$stmt = $conn->prepare(
					"SELECT id, name, email, password, role FROM members WHERE email = ? LIMIT 1"
				);
				$stmt->bind_param("s", $email_input);
				$stmt->execute();
				$user = $stmt->get_result()->fetch_assoc();
				$stmt->close();
			} catch (mysqli_sql_exception $e) {
				$user = null;
				$error = "Could not read the members table for that gym.";
			}
			$conn->close();

			if ($error === "") {
				if ($user && password_verify($password, $user["password"])) {
					session_regenerate_id(true);
					$_SESSION["user_id"] = $user["id"];
					$_SESSION["name"]    = $user["name"];
					$_SESSION["role"]    = $user["role"];
					$_SESSION["gym"]     = $gym_db;

					// Staff go to the dashboard, everyone else to their own profile.
					if (in_array($user["role"], ["admin", "super_admin"], true)) {
						$destination = "/admin/dashboard.php";
					} else {
						// Each gym can have its own profile page, named after
						// its database: profile-<gym>.php. Falls back to the
						// shared one when the gym has no page of its own.
						$gym_profile = "/client/profile-" . $gym_db . ".php";
						$destination = file_exists(__DIR__ . $gym_profile)
							? $gym_profile
							: "/client/profile.php";
					}

					header("Location: " . BASE_URL . $destination);
					exit;
				}

				// Same message either way, so this can't be used to find emails.
				$error = "Incorrect email or password.";
			}
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
