<?php
require_once __DIR__ . "/../config.php";
require_once PROJECT_ROOT . "/GymsManager/backend/config.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION = []; // empty the data
session_destroy(); // kill the session on the server

header("Location: " . GYM_BASE_URL . "/index.php");
exit;
