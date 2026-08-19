<?php
require_once __DIR__ . "/config.php";

/*
 * Every gym this install serves lives in the gym_registry database, one row
 * per gym. Nothing about a gym is hardcoded here: the name, logo, home page
 * and contact details all come from that table, and the row's db_name says
 * which database holds that gym's members, plans, classes and trainers.
 *
 * Adding a gym is an INSERT plus its own database — no code change.
 */

const REGISTRY_DB = "gym_registry";

/** Connection to the registry, opened once per request. */
function registry_connect()
{
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, REGISTRY_DB);
            $conn->set_charset("utf8mb4");
        } catch (mysqli_sql_exception $e) {
            $conn = false;
        }
    }
    return $conn === false ? null : $conn;
}

/** One gym row by its db_name, or null. */
function gym_by_slug($slug)
{
    static $cache = [];
    if (array_key_exists($slug, $cache)) {
        return $cache[$slug];
    }

    $conn = registry_connect();
    if ($conn === null) {
        return $cache[$slug] = null;
    }

    $stmt = $conn->prepare("SELECT * FROM gyms WHERE db_name = ? LIMIT 1");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $cache[$slug] = $row ?: null;
}

/**
 * Find a gym from what someone typed on the login form. Matches the display
 * name or the database name, ignoring case, spaces and punctuation, so
 * "Royal Gym", "royal gym" and "royal-gym" all reach the same row.
 */
function gym_by_name($typed)
{
    $conn = registry_connect();
    if ($conn === null) {
        return null;
    }

    $needle = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $typed));
    if ($needle === "") {
        return null;
    }

    $result = $conn->query(
        "SELECT * FROM gyms
         WHERE LOWER(REPLACE(REPLACE(name, ' ', ''), '-', '')) = '" . $conn->real_escape_string($needle) . "'
            OR LOWER(REPLACE(db_name, '-', ''))                = '" . $conn->real_escape_string($needle) . "'
         LIMIT 1"
    );
    return $result ? ($result->fetch_assoc() ?: null) : null;
}

/** Open the database belonging to a gym row. */
function gym_db_connect(array $gym)
{
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, $gym["db_name"]);
    } catch (mysqli_sql_exception $e) {
        return null;
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

/**
 * The gym the current page belongs to. A page names itself by setting
 * $active_gym to a db_name before including the header.
 *
 * If the registry is unreachable, or the page names a gym that isn't in it,
 * this returns a placeholder rather than silently branding as another gym.
 */
function current_gym()
{
    global $active_gym;

    $gym = isset($active_gym) ? gym_by_slug($active_gym) : null;
    if ($gym !== null) {
        return $gym;
    }

    return [
        "name"      => isset($active_gym) ? $active_gym : "Gym",
        "db_name"   => isset($active_gym) ? $active_gym : "",
        "logo"      => "logo.png",
        "home_page" => "/index.php",
        "tagline"   => "",
        "address"   => "",
        "phone"     => "",
        "email"     => "",
    ];
}
