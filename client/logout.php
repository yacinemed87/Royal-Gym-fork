<?php
session_start();
require_once __DIR__ . "/../backend/config.php";

$_SESSION = [];        // empty the data
session_destroy();     // kill the session on the server

header("Location: " . BASE_URL . "/index.php");
exit;
