<?php
// Default XAMPP credentials
$servername = "localhost";
$username = "ugym"; 
$password = "yacine123";   
$dbname = "royal-gym";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
