<?php
require_once __DIR__ . "/backend/config.php";
require_once __DIR__ . "/backend/db_connect.php";
require_once __DIR__ . "/backend/gyms.php";

$error = "";
$gym_input = "";
$email_input = "";

if (isset($_POST["gym"])) {

    $gym_input = trim($_POST["gym"]);
    $email_input = trim($_POST["email"]);
    $password_input = $_POST["password"];

    $gym = get_gym($gym_input);

    if ($gym == null) {
        $error = "No gym found with that name.";
    } else {

        $conn = connect_gym($gym["db_name"]);

        if ($conn == null) {
            $error = "Could not connect to gym database.";
        } else {

            // 1. Check staff table first
            $stmt = $conn->prepare("SELECT id, name, password, role FROM staff WHERE email = ?");
            $stmt->bind_param("s", $email_input);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $is_staff = ($user !== null);

            // 2. If not in staff, check members table
            if ($user === null) {
                $stmt = $conn->prepare("SELECT id, name, password, role FROM members WHERE email = ?");
                $stmt->bind_param("s", $email_input);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            }

            $conn->close();

            // 3. Verify password and authenticate
            if ($user !== null && password_verify($password_input, $user["password"])) {

                $_SESSION["user_id"]  = $user["id"];
                $_SESSION["name"]     = $user["name"];
                $_SESSION["email"]    = $email_input;
                $_SESSION["role"]     = $user["role"];
                $_SESSION["gym_db"]   = $gym["db_name"];
                $_SESSION["gym_slug"] = $gym["slug"];

                // Build redirect to the gym's own frontend
                $gym_base = BASE_URL . "Gyms/" . $gym["slug"];

                $is_staff = $user["role"] !== "member";
                if ($is_staff) {
                    $destination = $gym_base . "/admin/dashboard.php";
                } else {
                    $destination = $gym_base . "/index.php";
                }

                header("Location: " . $destination);
                exit;
            } else {
                $error = "Incorrect email or password.";
            }
        }
    }
}

$gyms = [];
if (isset($connReg) && $connReg !== null) {
    $res = $connReg->query("SELECT name, slug, db_name FROM gyms ORDER BY name ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $gyms[] = $row;
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?= BASE_URL; ?>GymsManager/css/login.css" />
    <title>Login | Gym Management</title>
</head>

<body>
    <main>
        <section id="login-container">
            <header>
                <h1>Gym Management</h1>
                <h2>Member &amp; Staff Access</h2>
                <p>Enter your gym and credentials to continue.</p>
            </header>

            <form id="login-form" action="<?= BASE_URL; ?>GymsManager/login.php" method="POST">
                <fieldset>
                    <legend>Login Details</legend>

                    <div class="form-group">
                        <label for="gym">Gym:</label>
                        <select id="gym" name="gym" required>
                            <option value="">-- Select your gym --</option>
                            <?php foreach ($gyms as $g): ?>
                                <option value="<?= htmlspecialchars($g['name']); ?>" <?= ($gym_input === $g['name'] || $gym_input === $g['slug']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($g['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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

                    <?php if ($error !== ""): ?>
                        <div id="error-display"><?= htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <button type="submit" class="button">Login</button>
                </fieldset>
            </form>

            <footer>
                <p>
                    Not a member yet?
                    Enter your gym name above to find your gym's registration page.
                </p>
            </footer>
        </section>
    </main>
</body>

</html>