<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db_connect.php";
require_once __DIR__ . "/gyms.php";
require_once __DIR__ . "/gym_data.php";
function write_facilities()
{
    global $connGym;
    $gym = get_gym_info();
    $gymName = htmlspecialchars($gym['name'] ?? 'Gym');

    $sql = "SELECT * FROM facilities";
    $stmt = $connGym->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $facilityName = htmlspecialchars($row['name']);
        $facilityDesc = htmlspecialchars($row['discription']);
        echo "
    <article class='facility-card'>
    <h3>{$facilityName}</h3>
    <p>{$facilityDesc}</p>
    <img src='" . GYM_BASE_URL . htmlspecialchars($row['image']) . "' alt='{$gymName} {$facilityName} Picture'>
    </article>
    ";
    }
    $stmt->close();
}

function write_opening_hours_table()
{
    global $connGym;
    if (!isset($connGym) || $connGym == null)
        return;

    $sql = "SELECT * FROM opening_hours";
    $result = $connGym->query($sql);
    if (!$result)
        return;

    echo "<table>";
    echo "<tr><th>Day</th><th>Opening Time</th><th>Closing Time</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $day = htmlspecialchars($row['day']);
        $openTime = htmlspecialchars($row['opening_time']);
        $closeTime = htmlspecialchars($row['closing_time']);
        echo "
        <tr>
            <td>{$day}</td>
            <td>{$openTime}</td>
            <td>{$closeTime}</td>
        </tr>
        ";
    }
    echo "</table>";
}


function write_membership_plan_cards()
{
    $plans = get_plans();

    $durations = get_durations();
    $max_discount = 0;
    foreach ($durations as $d) {
        if (floatval($d['discount_pct']) > $max_discount)
            $max_discount = floatval($d['discount_pct']);
    }

    $i = 0;
    $highlightIndex = 1;

    foreach ($plans as $row) {
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
