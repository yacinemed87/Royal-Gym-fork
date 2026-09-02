<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/handling_members.php";

function write_membership_plan_cards()
{
    global $connGym;
    if (!isset($connGym) || $connGym == null)
        return;

    $sql = "SELECT * FROM plans";
    $result = $connGym->query($sql);
    if (!$result)
        return;

    $durations = get_plan_durations();
    $max_discount = 0;
    foreach ($durations as $d) {
        if (floatval($d['discount_pct']) > $max_discount)
            $max_discount = floatval($d['discount_pct']);
    }

    $i = 0;
    $highlightIndex = 1;

    while ($row = $result->fetch_assoc()) {
        $isFeatured = ($i === $highlightIndex);
        $featuredClass = $isFeatured ? ' featured' : '';
        $featuresList = array_map('trim', explode(',', $row['features'] ?? ''));
        $featuresHtml = '';
        foreach ($featuresList as $f) {
            if (!empty($f)) {
                $featuresHtml .= "<li>" . htmlspecialchars($f) . "</li>";
            }
        }

        $base_price = intval($row['price']);
        $formattedPrice = number_format($base_price) . ' DA';
        $badge = $isFeatured ? '<span class="muted">Most popular</span>' : '<span class="muted">&nbsp;</span>';

        echo "
		<article class='plan-card{$featuredClass}' aria-labelledby='plan-title-{$row['id']}'
			data-base-price='{$base_price}' data-plan-id='{$row['id']}'>
			<div class='plan-title'>
				<h3 id='plan-title-{$row['id']}'>" . htmlspecialchars($row['name']) . "</h3>
				<div class='plan-price'>
					<span class='price-display' id='price-display-{$row['id']}'>{$formattedPrice}</span><span class='price-unit' id='price-unit-{$row['id']}'>/month</span>
				</div>
				<p class='price-per-month' id='price-per-month-{$row['id']}'></p>
				<p class='price-savings' id='price-savings-{$row['id']}'></p>
			</div>

			<ul class='plan-features'>
				{$featuresHtml}
			</ul>

			<div class='plan-cta'>
				{$badge}
				<button type='button' class='btn-ghost' data-plan='" . htmlspecialchars($row['name']) . "' aria-label='Choose " . htmlspecialchars($row['name']) . "'>
					Choose
				</button>
			</div>
		</article>
		";
        $i++;
    }
}

function write_membership_plan_radios()
{
    global $connGym;
    if (!isset($connGym) || $connGym == null)
        return;

    $sql = "SELECT * FROM plans";
    $result = $connGym->query($sql);
    if (!$result)
        return;
    $i = 0;
    $defaultIndex = 1;
    $preselectedPlan = trim($_GET['plan'] ?? '');

    while ($row = $result->fetch_assoc()) {
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
    $durations = get_plan_durations();
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