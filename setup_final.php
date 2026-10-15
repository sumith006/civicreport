<?php
// setup_final.php
$host = 'localhost';
$db = 'disaster_help';
$user = 'root';
$pass = '';

// 1. Connect to Database
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "<h2>System Full Reset</h2>";

// --- CRITICAL FIX: Disable Foreign Key Checks ---
// This allows us to drop tables even if they are linked
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// 2. Drop BOTH tables (Child first, then Parent)
$conn->query("DROP TABLE IF EXISTS complaints");
$conn->query("DROP TABLE IF EXISTS users");

// Re-enable Foreign Key Checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

echo "<p style='color:green'>✔ Old tables deleted successfully.</p>";

// 3. Recreate Users Table
$sql_users = "CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'employee', 'citizen') DEFAULT 'citizen',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql_users)) echo "<p>✔ Users table created.</p>";

// 4. Recreate Complaints Table (So the app doesn't crash later)
$sql_complaints = "CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(150),
    description TEXT,
    category VARCHAR(50),
    photo_path VARCHAR(255),
    latitude VARCHAR(50),
    longitude VARCHAR(50),
    status ENUM('Pending', 'In Progress', 'Resolved') DEFAULT 'Pending',
    assigned_to INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql_complaints)) echo "<p>✔ Complaints table created.</p>";

// 5. Create Fresh Users
$users = [
    ['Super Admin', 'admin@sys.com', 'admin123', 'admin'],
    ['John Volunteer', 'volunteer@sys.com', '12345', 'employee'],
    ['Jane Citizen', 'citizen@sys.com', '12345', 'citizen']
];

echo "<h3>Creating Accounts...</h3><ul>";
foreach ($users as $u) {
    $hash = password_hash($u[2], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $u[0], $u[1], $hash, $u[3]);
    if ($stmt->execute()) {
        echo "<li>Created: <strong>{$u[3]}</strong> ({$u[1]})</li>";
    }
}
echo "</ul>";

echo "<hr><h3><a href='index.php'>System Ready! Click here to Login</a></h3>";
?>