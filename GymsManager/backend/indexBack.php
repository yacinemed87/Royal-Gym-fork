<?php

function write_facilities()
{
    global $connGym;

    $sql = "SELECT * FROM facilities";
    $stmt = $connGym->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo "
    <article class='facility-card'>
    <h3>{$row['name']}</h3>
    <p>{$row['discription']}</p>
    <img src='" . GYM_BASE_URL . $row['image'] . "' alt='Royal Gym {$row['name']} Picture'>
    </article>
    ";
    }
    $stmt->close();
}

function write_plans()
{
    global $connGym;
    if (!isset($connGym) || $connGym == null)
        return;

    $sql = "SELECT * FROM plans";
    $result = $connGym->query($sql);
    if (!$result)
        return;

    $i = 0;
    $highlightIndex = 1; // 2nd plan recommended

    while ($row = $result->fetch_assoc()) {
        $cardClass = ($i === $highlightIndex) ? 'membership-card elite' : 'membership-card';
        $recommended = ($i === $highlightIndex) ? '<p class="price">Recommended</p>' : '';

        $featuresList = array_map('trim', explode(',', $row['features'] ?? ''));
        $featuresHtml = '';
        foreach ($featuresList as $f) {
            if (!empty($f)) {
                $featuresHtml .= "<dd>" . htmlspecialchars($f) . "</dd>";
            }
        }

        $durationText = !empty($row['duration']) ? (is_numeric($row['duration']) ? ($row['duration'] . ' days') : $row['duration']) : 'month';
        $formattedPrice = number_format($row['price']) . ' DA / ' . htmlspecialchars($durationText);

        echo "
		<article class='{$cardClass}'>
			<h3>" . htmlspecialchars($row['name']) . "</h3>
			<p class='price'>{$formattedPrice}</p>
			<dl>
				<dt>Features</dt>
				{$featuresHtml}
			</dl>
			{$recommended}
		</article>
		";
        $i++;
    }
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
        echo "
        <tr>
            <td>{$row['day']}</td>
            <td>{$row['opening_time']}</td>
            <td>{$row['closing_time']}</td>
        </tr>
        ";
    }
    echo "</table>";
}