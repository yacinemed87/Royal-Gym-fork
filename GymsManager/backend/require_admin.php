<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/require_login.php";
require_once __DIR__ . "/db_connect.php";

if (empty($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "GymsManager/login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'staff' && $_SESSION['role'] !== 'trainer') {
    http_response_code(403);
    exit("Forbidden");
}
