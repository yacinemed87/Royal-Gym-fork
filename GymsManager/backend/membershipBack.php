<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db_connect.php";
require_once __DIR__ . "/gym_data.php";
require_once __DIR__ . "/handling_members.php";

function write_membership_plan_radios()
{
    $plans = get_plans();

    $i = 0;
    $defaultIndex = 1;
    $preselectedPlan = trim($_GET['plan'] ?? '');

    foreach ($plans as $row) {
        $requiredAttr = ($i === 0) ? 'required' : '';
        $planName = htmlspecialchars($row['name']);
        if (!empty($preselectedPlan)) {
            $checkedAttr = (strcasecmp($preselectedPlan, $row['name']) === 0) ? 'checked' : '';
        } else {
            $checkedAttr = ($i === $defaultIndex) ? 'checked' : '';
        }

        echo "
		<label class='custom' title='{$planName}'>
			<input type='radio' name='plan' value='{$planName}' data-price='{$row['price']}' data-name='{$planName}' {$requiredAttr} {$checkedAttr} />
			<span class='control radio' aria-hidden='true'></span>
			<span>{$planName}</span>
		</label>
		";
        $i++;
    }

    echo "<input type='hidden' name='price_paid' id='price_paid_input' value='' />";
}

function write_membership_duration_radios()
{
    $durations = get_durations();
    if (empty($durations))
        return;

    $preselectedDurId = intval($_GET['duration_id'] ?? 0);

    foreach ($durations as $i => $dur) {
        if ($preselectedDurId > 0) {
            $checkedAttr = (intval($dur['id']) === $preselectedDurId) ? 'checked' : '';
        } else {
            $checkedAttr = (intval($dur['months']) === 1) ? 'checked' : '';
        }
        $durLabel = htmlspecialchars($dur['label']);
        $badge = ($dur['discount_pct'] > 0) ? "<span class='duration-tag'>-" . intval($dur['discount_pct']) . "%</span>" : "";

        echo "
		<label class='custom' title='{$durLabel}'>
			<input type='radio' name='duration_id' value='{$dur['id']}' data-months='{$dur['months']}' data-discount='{$dur['discount_pct']}' {$checkedAttr} />
			<span class='control radio' aria-hidden='true'></span>
			<span>{$durLabel} {$badge}</span>
		</label>
		";
    }
}


