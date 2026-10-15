<?php
session_start();
include 'db_connect.php'; // <--- Ensures we use the 'disaster_help_system' database

$msg = "";

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];

    // 1. Check if email already exists
    $check_email = "SELECT * FROM users WHERE email='$email'";
    $res = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($res) > 0) {
        $msg = "<div class='alert alert-warning text-center'>Email already registered. <a href='login.php'>Login here</a></div>";
    } else {
        // 2. Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert into Database (Default role is 'citizen')
        $sql = "INSERT INTO users (name, email, phone, password, role) VALUES ('$name', '$email', '$phone', '$hashed_password', 'citizen')";

        if (mysqli_query($conn, $sql)) {
            // Success: Redirect to login
            echo "<script>alert('Account created successfully! Please login.'); window.location.href='login.php';</script>";
        } else {
            $msg = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Civic Issue Reporter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(135deg, #e0eaec 0%, #2c3e50 100%);
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
        }
        .register-box { 
            background: white; 
            padding: 40px; 
            border-radius: 15px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.2); 
            width: 100%; 
            max-width: 450px; 
        }
        .brand-icon { font-size: 3rem; color: #17a2b8; margin-bottom: 15px; }
        .form-control { border-radius: 8px; padding: 10px 15px; }
        .btn-custom { 
            background-color: #2c3e50; 
            color: white; 
            border: none; 
            padding: 10px;
            border-radius: 8px;
            font-size: 1rem;
            transition: background 0.3s;
        }
        .btn-custom:hover { background-color: #17a2b8; color: white; }
    </style>
</head>
<body>

<div class="register-box">
    <div class="text-center">
        <i class="fa-solid fa-city brand-icon"></i>
        <h3 class="fw-bold mb-1 text-dark">Join the Community</h3>
        <p class="text-muted mb-4 small">Create an account to report civic issues</p>
    </div>

    <?php echo $msg; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-bold small text-secondary">Full Name</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fa fa-user text-secondary"></i></span>
                <input type="text" name="name" class="form-control border-start-0" placeholder="John Doe" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold small text-secondary">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fa fa-envelope text-secondary"></i></span>
                <input type="email" name="email" class="form-control border-start-0" placeholder="name@example.com" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold small text-secondary">Phone Number</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fa fa-phone text-secondary"></i></span>
                <input type="text" name="phone" class="form-control border-start-0" placeholder="9876543210" required>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold small text-secondary">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fa fa-lock text-secondary"></i></span>
                <input type="password" name="password" class="form-control border-start-0" placeholder="Create a password" required>
            </div>
        </div>

        <button type="submit" name="register" class="btn btn-custom w-100 fw-bold shadow-sm">
            Create Account
        </button>

        <div class="text-center mt-4">
            <p class="small text-muted mb-1">Already have an account?</p>
            <a href="login.php" class="text-decoration-none fw-bold text-info">
                Sign In Here
            </a>
        </div>
    </form>
</div>

</body>
</html>