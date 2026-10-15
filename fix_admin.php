<?php
// Save this as fix_admin.php
include 'includes/db.php';

// Enable error reporting to see if DB connection fails
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $email = 'admin@sys.com';
    $raw_pass = 'admin123';
    
    // 1. Generate the hash using YOUR server's algorithm
    $secure_hash = password_hash($raw_pass, PASSWORD_DEFAULT);

    // 2. Delete the old admin to avoid duplicates
    $conn->query("DELETE FROM users WHERE email = '$email'");

    // 3. Insert the new Admin
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $name = "Super Admin";
    $role = "admin";
    
    $stmt->bind_param("ssss", $name, $email, $secure_hash, $role);
    $stmt->execute();

    echo "<h1>Admin Fixed Successfully</h1>";
    echo "<p><strong>Email:</strong> admin@sys.com</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<p><strong>Generated Hash:</strong> " . $secure_hash . "</p>";
    echo "<br><a href='index.php'>Click here to Login</a>";

} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "Database error: " . $e->getMessage();
}
?>