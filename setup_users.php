<?php
// setup_users.php
include 'includes/db.php';

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Shared password for testing
    $pass = '12345';
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    // --- 1. Create Employee (Volunteer) ---
    $emp_email = 'volunteer@sys.com';
    // Delete if exists to avoid duplicates
    $conn->query("DELETE FROM users WHERE email = '$emp_email'");
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, 'employee', '9876543210')");
    $name = "John Volunteer";
    $stmt->bind_param("sss", $name, $emp_email, $hash);
    $stmt->execute();

    // --- 2. Create Citizen (Client) ---
    $cit_email = 'citizen@sys.com';
    // Delete if exists
    $conn->query("DELETE FROM users WHERE email = '$cit_email'");

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, 'citizen', '1234567890')");
    $name = "Jane Citizen";
    $stmt->bind_param("sss", $name, $cit_email, $hash);
    $stmt->execute();

    echo "<h1>Users Created Successfully!</h1>";
    echo "<hr>";
    echo "<h3>1. Volunteer / Employee Login</h3>";
    echo "Email: <b>volunteer@sys.com</b><br>";
    echo "Password: <b>12345</b>";
    echo "<hr>";
    echo "<h3>2. Citizen / Client Login</h3>";
    echo "Email: <b>citizen@sys.com</b><br>";
    echo "Password: <b>12345</b>";
    echo "<hr>";
    echo "<a href='index.php'>Go to Login Page</a>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
