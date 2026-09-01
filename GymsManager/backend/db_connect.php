<?php

session_start();
$servername = "localhost";
$username = "ugym";
$password = "yacine123";
$registry_db = "gym-registry";

$connReg = new mysqli($servername, $username, $password, $registry_db);
$connReg->set_charset("utf8mb4");

global $gym_db;

if (!empty($gym_db)) {
    $connGym = new mysqli($servername, $username, $password, $gym_db);
    $connGym->set_charset("utf8mb4");
}
