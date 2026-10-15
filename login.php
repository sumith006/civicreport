<?php
session_start();
include 'db_connect.php';

$msg = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // 1. Check if user exists
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // 2. Verify Password
        if (password_verify($password, $row['password'])) {
            // Set Session Variables
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = $row['role'];

            // 3. REDIRECT BASED ON ROLE
            if ($row['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } 
            elseif ($row['role'] === 'helper') {
                // 'helper' is the database name for Employees
                header("Location: emp_dashboard.php");
            } 
            else {
                // Default is Citizen
                header("Location: citizen_dashboard.php");
            }
            exit();
        } else {
            $msg = "<div class='alert alert-danger text-center'>Incorrect password.</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger text-center'>Email not found.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Civic Issue Reporter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(135deg, #e0eaec 0%, #2c3e50 100%);
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            font-family: 'Segoe UI', sans-serif;
        }
        .login-box { 
            background: white; 
            padding: 40px; 
            border-radius: 15px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.2); 
            width: 100%; 
            max-width: 400px; 
            transition: transform 0.3s;
        }
        .login-box:hover { transform: translateY(-5px); }
        .brand-icon { font-size: 3.5rem; color: #17a2b8; margin-bottom: 15px; }
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

<div class="login-box text-center">
    <i class="fa-solid fa-city brand-icon"></i>
    <h3 class="fw-bold mb-1 text-dark">Civic Issue Reporter</h3>
    <p class="text-muted mb-4 small">Secure Login Portal</p>
    
    <?php echo $msg; ?>
    
    <form method="POST" class="text-start mt-4">
        <div class="mb-3">
            <label class="form-label fw-bold small text-uppercase text-secondary">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fa fa-envelope text-secondary"></i></span>
                <input type="email" name="email" class="form-control border-start-0" placeholder="name@example.com" required>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="form-label fw-bold small text-uppercase text-secondary">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fa fa-lock text-secondary"></i></span>
                <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
            </div>
        </div>
        
        <button type="submit" name="login" class="btn btn-custom w-100 fw-bold shadow-sm">
            Sign In <i class="fa-solid fa-arrow-right-to-bracket ms-2"></i>
        </button>
        
        <div class="text-center mt-4">
            <p class="small text-muted mb-1">New to the platform?</p>
            <a href="register.php" class="text-decoration-none fw-bold text-info">
                Create a Citizen Account
            </a>
        </div>
    </form>
</div>

</body>
</html>