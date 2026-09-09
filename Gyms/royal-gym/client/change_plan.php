<?php
$current_page = 'membership';
require_once __DIR__ . "/../config.php";
require_once PROJECT_ROOT . "/GymsManager/backend/require_login.php";
require_once PROJECT_ROOT . "/GymsManager/backend/gyms.php";
require_once PROJECT_ROOT . "/GymsManager/backend/handling_members.php";
require_once PROJECT_ROOT . "/GymsManager/backend/gym_data.php";

$gym = get_gym_info();
$user_id = $_SESSION['user_id'] ?? 0;
$sub = get_member_subscription($user_id);
$error = '';
$success = '';

if (!$sub) {
    header("Location: " . GYM_BASE_URL . "/client/membership.php");
    exit;
}

if (!can_change_plan($sub)) {
    header("Location: " . GYM_BASE_URL . "/client/profile.php?msg=window_closed");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_plan_id = intval($_POST['new_plan_id'] ?? 0);
    if ($new_plan_id && $new_plan_id !== intval($sub['plan_id'])) {
        $new_plan = get_plan_by_id($new_plan_id);
        if ($new_plan) {
            $cost = calculate_change_cost($sub, $new_plan);
            $ok = change_plan($sub['id'], $new_plan_id, $cost['amount'], $cost['new_end_date']);
            if ($ok) {
                if ($cost['is_refund']) {
                    insert_refund_request($user_id, $cost['amount'], 'Plan downgrade from ' . ($sub['plan_name'] ?? '') . ' to ' . $new_plan['name']);
                }
                header("Location: " . GYM_BASE_URL . "/client/profile.php?msg=plan_changed");
                exit;
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    } else {
        $error = "Please select a different plan.";
    }
}

$all_plans_result = $connGym->query("SELECT * FROM plans ORDER BY price ASC");
$plans = [];
while ($row = $all_plans_result->fetch_assoc()) {
    if ($row['id'] != $sub['plan_id']) {
        $cost = calculate_change_cost($sub, $row);
        $row['_cost'] = $cost;
        $plans[] = $row;
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Change Plan | <?= htmlspecialchars($gym['name'] ?? 'Gym'); ?></title>
    <link rel="stylesheet" href="../css/profile.css" />
    <link rel="stylesheet" href="../css/change_plan.css" />
    <link rel="icon" type="image/png" href="../assets/images/<?= htmlspecialchars($gym['logo'] ?? 'logo.png'); ?>" />
</head>

<body>
    <?php include __DIR__ . "/includes/header.php"; ?>

    <main>
        <section class="profile-intro">
            <h2>Change Your Plan</h2>
            <p>You're currently on <strong><?= htmlspecialchars($sub['plan_name'] ?? 'Unknown'); ?></strong>.
                Select a new plan below. Your remaining months will be credited.</p>
        </section>

        <?php if ($error): ?>
            <div class="cp-error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="cp-grid">
            <?php foreach ($plans as $plan): ?>
                <?php
                $cost = $plan['_cost'];
                $amount_display = number_format(abs($cost['amount'])) . ' DA';
                $is_refund = $cost['is_refund'];
                $months_remaining = $cost['months_remaining'];
                ?>
                <div class="cp-card">
                    <h3 class="cp-plan-name"><?= htmlspecialchars($plan['name']); ?></h3>
                    <p class="cp-base-price"><?= number_format($plan['price']); ?> DA<span>/month</span></p>

                    <div class="cp-breakdown">
                        <p>New plan total: <?= number_format(round($plan['price'] * intval($sub['durationMonths']) * (1 - floatval($sub['discount_pct']) / 100))); ?> DA</p>
                        <p>Your refund: −<?= number_format(round((intval($sub['price_paid']) / intval($sub['durationMonths'])) * $months_remaining)); ?> DA</p>
                    </div>

                    <div class="cp-total <?= $is_refund ? 'is-refund' : 'is-charge'; ?>">
                        <?php if ($is_refund): ?>
                            Refund: <?= $amount_display; ?>
                        <?php else: ?>
                            To pay: <?= $amount_display; ?>
                        <?php endif; ?>
                    </div>

                    <?php if ($is_refund): ?>
                        <p class="cp-refund-note">The gym will contact you to process this refund manually.</p>
                    <?php endif; ?>

                    <button type="submit" name="new_plan_id" value="<?= intval($plan['id']); ?>" class="cp-btn">
                        Switch to <?= htmlspecialchars($plan['name']); ?>
                    </button>
                </div>
            <?php endforeach; ?>
        </form>

        <div style="text-align:center; margin-top: 2rem;">
            <a href="<?= GYM_BASE_URL; ?>/client/profile.php" style="color: var(--accent); font-size: 0.9rem;">← Back to Profile</a>
        </div>
    </main>

    <?php include __DIR__ . "/includes/footer.php"; ?>
    <script>
        document.querySelector('.menu-toggle')?.addEventListener('click', function() {
            document.querySelector('header nav').classList.toggle('open');
        });
    </script>
</body>

</html>