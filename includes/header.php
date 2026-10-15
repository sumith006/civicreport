<?php
// Ensure session is started only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Civic Issue Reporter</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

    <link href="assets/css/style.css" rel="stylesheet">

    <style>
        /* Sticky Footer Logic */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
        }
        
        /* Navbar Tweaks */
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fa-solid fa-city text-warning"></i> Civic Issue Reporter
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    
                    <?php if($_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                        </li>
                    
                    <?php elseif($_SESSION['role'] == 'employee'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="emp_dashboard.php"><i class="fa-solid fa-list-check"></i> Assigned Tasks</a>
                        </li>
                    
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="citizen_dashboard.php"><i class="fa-solid fa-clock-rotate-left"></i> My History</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-warning btn-sm text-dark px-3 ms-2 rounded-pill shadow-sm fw-bold" href="raise_complaint.php">
                                <i class="fa-solid fa-plus-circle"></i> Report Issue
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item ms-3">
                        <span class="text-white-50 small me-2">Hi, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white hover-scale" href="logout.php" title="Logout">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </a>
                    </li>
                
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="index.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container main-content mt-4">