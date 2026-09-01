<?php
require_once __DIR__ . "/db_connect.php";

// 2. Write your query
$sql = "SELECT * FROM facilities";

// 3. Prepare and bind (safer than putting variables directly in the query)
$stmt = $connGym->prepare($sql);

// 4. run it
$stmt->execute();

// 5. Get the result
$result = $stmt->get_result();

// 6. Loop through rows
while ($row = $result->fetch_assoc()) {
    echo "
    <article class='facility-card'>
    <h3>{$row['name']}</h3>
    <p>{$row['discription']}</p>
    <img src='" . BASE_URL . $row['image'] . "' alt='Royal Gym {$row['name']} Picture'>
    </article>
    ";
}

$stmt->close();
$connGym->close();
