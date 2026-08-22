<?php
// Default XAMPP credentials
$servername = "localhost";
$username = "ugym";
$password = "yacine123";
$dbname = "royal-gym";
$dbname2 = "gym-registry";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

$conn2 = new mysqli($servername, $username, $password, $dbname2);
