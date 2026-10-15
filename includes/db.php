<?php
$host = 'localhost';
$db = 'disaster_help';
$user = 'root'; // Default XAMPP user
$pass = '';     // Default XAMPP pass

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
session_start();
?>