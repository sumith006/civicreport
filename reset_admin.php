<?php
include 'includes/db.php';

// The new password you want
$new_password = 'admin123';
$email = 'admin@sys.com';

// Generate a secure hash
$hash = password_hash($new_password, PASSWORD_DEFAULT);

// Update the database
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hash, $email);

if ($stmt->execute()) {
    echo "<h1>Success!</h1>";
    echo "<p>Password for <b>$email</b> has been reset to: <b>$new_password</b></p>";
    echo "<a href='index.php'>Go to Login</a>";
} else {
    echo "Error updating record: " . $conn->error;
}
?>