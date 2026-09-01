<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db_connect.php";


if (empty($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "GymsManager/login.php");
    exit;
}
