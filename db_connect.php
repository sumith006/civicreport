<?php
// 1. Force PHP to use Indian Standard Time (IST)
date_default_timezone_set('Asia/Kolkata');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "disaster_help_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Force MySQL to use Indian Time (+05:30)
$conn->query("SET time_zone = '+05:30'");
?>