<?php
session_start();
include 'db_connect.php';

// 1. Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'citizen') {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];
$uname = $_SESSION['name'];

// 2. Fetch User Stats (Strictly filtered by user_id='$uid')
$total = $conn->query("SELECT count(*) as c FROM complaints WHERE user_id='$uid'")->fetch_assoc()['c'];
$pending = $conn->query("SELECT count(*) as c FROM complaints WHERE user_id='$uid' AND status='Pending'")->fetch_assoc()['c'];
$resolved = $conn->query("SELECT count(*) as c FROM complaints WHERE user_id='$uid' AND status='Resolved'")->fetch_assoc()['c'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Citizen Dashboard | BCIR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        
        /* Navbar */
        .navbar { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .btn-logout { border: 1px solid #dc3545; color: #dc3545; border-radius: 20px; padding: 5px 20px; }
        .btn-logout:hover { background: #dc3545; color: white; }

        /* Hero Banner */
        .hero-card {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border-radius: 15px;
            color: white;
            padding: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(78, 115, 223, 0.3);
        }
        .hero-icon { position: absolute; right: 20px; bottom: -20px; font-size: 150px; opacity: 0.2; }

        /* Stat Cards */
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-box { font-size: 2rem; margin-bottom: 10px; }

        /* Ticket Card */
        .ticket-card { border: none; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 20px; border-left: 5px solid #4e73df; }
        .status-badge { padding: 5px 15px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
        .timeline-container { position: relative; margin-top: 30px; height: 4px; background: #e9ecef; border-radius: 5px; }
        .timeline-progress { height: 100%; background: #28a745; border-radius: 5px; transition: width 0.5s; }
        
        .timeline-step { position: absolute; top: -12px; width: 30px; height: 30px; background: white; border: 3px solid #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; color: #aaa; }
        .step-1 { left: 0%; }
        .step-2 { left: 50%; transform: translateX(-50%); }
        .step-3 { right: 0%; }
        
        .active-step { border-color: #28a745; color: #28a745; background: #eaffea; }
        .timeline-label { position: absolute; top: 25px; font-size: 0.75rem; color: #6c757d; width: 80px; text-align: center; }
        .label-1 { left: -10px; text-align: left; }
        .label-2 { left: 50%; transform: translateX(-50%); }
        .label-3 { right: -10px; text-align: right; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg px-4 py-3 sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-primary" href="#">
            <i class="fa-solid fa-city"></i> Belagavi Civic Issues
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="text-secondary fw-bold d-none d-md-block">Welcome, <?php echo $uname; ?></span>
            <a href="logout.php" class="btn btn-logout btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    
    <div class="hero-card mb-5">
        <i class="fa-solid fa-hands-holding-circle hero-icon"></i>
        <h2 class="fw-bold">Hello, <?php echo $uname; ?>!</h2>
        <p class="mb-4 opacity-75" style="max-width: 600px;">We are here to help. Report civic incidents and track resolution progress in real-time.</p>
        <a href="raise_complaint.php" class="btn btn-light text-primary fw-bold rounded-pill px-4 py-2 shadow-sm">
            <i class="fa-solid fa-bullhorn me-2"></i> Report New Incident
        </a>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card stat-card p-3 text-center">
                <div class="text-primary icon-box"><i class="fa-solid fa-file-pen"></i></div>
                <h3 class="fw-bold mb-0"><?php echo $total; ?></h3>
                <small class="text-muted fw-bold">Total Reports</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-3 text-center">
                <div class="text-warning icon-box"><i class="fa-solid fa-clock"></i></div>
                <h3 class="fw-bold mb-0"><?php echo $pending; ?></h3>
                <small class="text-muted fw-bold">Pending Action</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-3 text-center">
                <div class="text-success icon-box"><i class="fa-solid fa-check-double"></i></div>
                <h3 class="fw-bold mb-0"><?php echo $resolved; ?></h3>
                <small class="text-muted fw-bold">Successfully Resolved</small>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-secondary"><i class="fa-solid fa-clock-rotate-left"></i> Your Incident History</h5>
        <span class="badge bg-secondary"><?php echo $total; ?> Records Found</span>
    </div>

    <?php
    // FILTER: WHERE user_id='$uid' ensures you NEVER see others' tickets
    $sql = "SELECT * FROM complaints WHERE user_id='$uid' ORDER BY created_at DESC";
    $result = $conn->query($sql);

    if($result->num_rows > 0):
        while($row = $result->fetch_assoc()):
            // Status Logic
            $status = $row['status'];
            $badge_class = ($status == 'Resolved') ? 'bg-success' : (($status == 'In Progress') ? 'bg-info' : 'bg-warning text-dark');
            
            // Progress Bar Width
            $progress_width = '0%';
            $step2_active = ''; $step3_active = '';
            
            if($status == 'In Progress') { 
                $progress_width = '50%'; 
                $step2_active = 'active-step'; 
            } elseif($status == 'Resolved') { 
                $progress_width = '100%'; 
                $step2_active = 'active-step'; 
                $step3_active = 'active-step'; 
            }
    ?>
    
    <div class="card ticket-card p-4 bg-white">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <span class="badge bg-light text-primary border border-primary mb-2"><?php echo $row['category']; ?></span>
                <h4 class="fw-bold mb-1">Ticket #<?php echo $row['id']; ?></h4> <small class="text-muted"><i class="fa-regular fa-calendar"></i> <?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></small>
            </div>
            <span class="badge <?php echo $badge_class; ?> status-badge"><?php echo $status; ?></span>
        </div>
        
        <p class="text-secondary bg-light p-3 rounded mt-2">
            <?php echo $row['description']; ?>
        </p>
        
        <div class="mt-4 px-2">
            <small class="fw-bold text-muted text-uppercase" style="font-size: 0.7rem;">Status Timeline</small>
            <div class="timeline-container mt-2">
                <div class="timeline-progress" style="width: <?php echo $progress_width; ?>;"></div>
                
                <div class="timeline-step step-1 active-step"><i class="fa-solid fa-paper-plane"></i></div>
                <div class="timeline-label label-1">Submitted</div>
                
                <div class="timeline-step step-2 <?php echo $step2_active; ?>"><i class="fa-solid fa-user-gear"></i></div>
                <div class="timeline-label label-2">Processing</div>
                
                <div class="timeline-step step-3 <?php echo $step3_active; ?>"><i class="fa-solid fa-check"></i></div>
                <div class="timeline-label label-3">Resolved</div>
            </div>
        </div>
    </div>

    <?php endwhile; ?>
    <?php else: ?>
        <div class="text-center py-5">
            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="100" class="mb-3 opacity-50">
            <h5 class="text-muted">No reports found.</h5>
            <p class="small text-muted">Use the button above to report your first issue.</p>
        </div>
    <?php endif; ?>

</div>

</body>
</html>