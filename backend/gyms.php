<?php
require_once __DIR__ . "/config.php";

/*
 * All the gyms are stored in the "gym_registry" database, in a table called
 * "gyms". Each row has the gym's name, logo, home page and contact details,
 * plus db_name: the database that holds that gym's members, plans, classes
 * and trainers.
 *
 * To add a new gym you add a row to that table and create its database.
 * You do not need to change any PHP.
 */

// Let us check for errors with if() instead of try/catch.
mysqli_report(MYSQLI_REPORT_OFF);


// Connect to the registry database (the list of gyms).
function connect_registry()
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, "gym_registry");

    if ($conn->connect_error) {
        return null;
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}


// Connect to one gym's own database, for example "royal-gym".
function connect_gym($db_name)
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, $db_name);

    if ($conn->connect_error) {
        return null;
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}


// Find one gym by its database name, for example "power-fitness".
// Returns an array with all the columns, or null if there is no such gym.
function get_gym($db_name)
{
    $registry = connect_registry();

    if ($registry == null) {
        return null;
    }

    $stmt = $registry->prepare("SELECT * FROM gyms WHERE db_name = ?");
    $stmt->bind_param("s", $db_name);
    $stmt->execute();

    $gym = $stmt->get_result()->fetch_assoc();

    $stmt->close();
    $registry->close();

    return $gym;
}


// Find one gym by the name someone typed on the login form.
// It accepts either the name ("Royal Gym") or the database name ("royal-gym").
// Capital letters do not matter, because MySQL ignores them when comparing.
function find_gym($typed_name)
{
    $registry = connect_registry();

    if ($registry == null) {
        return null;
    }

    $stmt = $registry->prepare("SELECT * FROM gyms WHERE name = ? OR db_name = ?");
    $stmt->bind_param("ss", $typed_name, $typed_name);
    $stmt->execute();

    $gym = $stmt->get_result()->fetch_assoc();

    $stmt->close();
    $registry->close();

    return $gym;
}


// The first gym in the table. Pages shared by every gym (classes, trainers,
// contact...) use this, because they do not belong to one gym in particular.
function get_first_gym()
{
    $registry = connect_registry();

    if ($registry == null) {
        return null;
    }

    $result = $registry->query("SELECT * FROM gyms ORDER BY id LIMIT 1");
    $gym = $result->fetch_assoc();

    $registry->close();

    return $gym;
}


// The gym that the page being shown belongs to.
// A page says which gym it is by writing, before including the header:
//     $active_gym = "power-fitness";
function current_gym()
{
    global $active_gym;

    if (isset($active_gym)) {
        $gym = get_gym($active_gym);

        if ($gym != null) {
            return $gym;
        }
    }

    return get_first_gym();
}
