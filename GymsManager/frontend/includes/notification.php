<?php
if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'member') {
    require_once PROJECT_ROOT . "/GymsManager/backend/gym_data.php";

    $sub = get_member_subscription($_SESSION['user_id']);
    $active_sub = ($sub && $sub['status'] === 'active') ? $sub : null;

    if ($active_sub) {
        $days_left = calculate_subscription_days_left($active_sub);

        if ($days_left >= 0 && $days_left <= 7) {
            $day_text = $days_left === 1 ? '1 day' : $days_left . ' days';
            echo "<div style='background-color: #ff4444; color: white; text-align: center; padding: 10px; font-weight: bold; width: 100%; z-index: 1000;'>
                    ⚠️ Heads up! Your subscription expires in {$day_text}. <a href='" . GYM_BASE_URL . "/client/membership.php' style='color: white; text-decoration: underline;'>Renew now</a>
                  </div>";
        }
    }
}
?>