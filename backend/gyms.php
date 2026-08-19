<?php
require_once __DIR__ . "/config.php";

/*
 * Branding for a page. A page overrides it by setting $active_gym before
 * including the header, e.g. power-fitness.php. Anything left out falls back
 * to the values below.
 */
const DEFAULT_GYM = [
    "name" => "Royal Gym",
    "logo" => "logo.png",
    "home" => "/index.php",
];

function current_gym()
{
    global $active_gym;
    return isset($active_gym) && is_array($active_gym)
        ? $active_gym + DEFAULT_GYM
        : DEFAULT_GYM;
}

/**
 * Find and open the gym database the user named.
 *
 * The typed name is used as the database name. MySQL database names cannot
 * contain spaces, so if the name as typed does not exist we also try the
 * obvious spellings of it (lowercased, spaces as hyphens, spaces removed).
 * $resolved receives the name that actually worked.
 *
 * Returns null when no database of that name exists.
 */
function gym_db_connect($db_name, &$resolved = null)
{
    $typed = trim($db_name);
    $lower = strtolower($typed);

    $candidates = [
        $typed,
        $lower,
        str_replace(" ", "-", $lower),
        str_replace(" ", "", $lower),
    ];

    foreach (array_unique($candidates) as $name) {
        // Skip anything that could not be a MySQL database name.
        if ($name === "" || strlen($name) > 64 || !preg_match('/^[A-Za-z0-9_$\-]+$/', $name)) {
            continue;
        }

        try {
            // PHP 8.1+ makes mysqli throw on failure rather than set a flag.
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, $name);
        } catch (mysqli_sql_exception $e) {
            continue;
        }

        $conn->set_charset("utf8mb4");
        $resolved = $name;
        return $conn;
    }

    $resolved = null;
    return null;
}
