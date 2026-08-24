<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db_connect.php";

if (empty($_SESSION['gym_db'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}
