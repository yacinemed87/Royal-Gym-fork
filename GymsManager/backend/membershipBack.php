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

        $durationText = $row['duration'] . ' days';
        $formattedPrice = number_format($row['price']) . ' DA';
        $badge = $isFeatured ? '<span class="muted">Most popular</span>' : '<span class="muted">&nbsp;</span>';

        echo "
		<article class='plan-card{$featuredClass}' aria-labelledby='plan-title-{$row['id']}'>
			<div class='plan-title'>
				<h3 id='plan-title-{$row['id']}'>" . htmlspecialchars($row['name']) . "</h3>
				<div class='plan-price'>
					{$formattedPrice}<span class='price-unit'>/{$durationText}</span>
				</div>
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

    while ($row = $result->fetch_assoc()) {
        $requiredAttr = ($i === 0) ? 'required' : '';
        $checkedAttr = ($i === $defaultIndex) ? 'checked' : '';
        $planName = htmlspecialchars($row['name']);

        echo "
		<label class='custom' title='{$planName}'>
			<input type='radio' name='plan' value='{$planName}' {$requiredAttr} {$checkedAttr} />
			<span class='control radio' aria-hidden='true'></span>
			<span>{$planName}</span>
		</label>
		";
        $i++;
    }
}