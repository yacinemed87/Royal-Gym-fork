<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db_connect.php";

if (empty($_SESSION['gym_db'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin') {
    http_response_code(403);
    exit("Forbidden");
}
