<?php 
session_start();
include 'db_connect.php'; 

// 1. Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'helper') {
    header("Location: login.php");
    exit();
}

$eid = $_SESSION['user_id'];
$msg = "";

// 2. Handle Status Update & Photo Upload
if(isset($_POST['mark_resolved'])) {
    $cid = $_POST['complaint_id'];
    
    // IMAGE UPLOAD LOGIC
    $proof_path = NULL;
    $uploadOk = 1;
    
    // Check if image file is a actual image
    if(isset($_FILES['proof_photo']) && $_FILES['proof_photo']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

        $file_name = time() . "_proof_" . basename($_FILES["proof_photo"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["proof_photo"]["tmp_name"], $target_file)) {
            $proof_path = $target_file;
        } else {
            $uploadOk = 0;
            $msg = "<div class='alert alert-danger'>Sorry, there was an error uploading the proof photo.</div>";
        }
    } else {
        $uploadOk = 0;
        $msg = "<div class='alert alert-warning'>Please upload a proof photo to complete the task.</div>";
    }

    // Only update DB if upload was successful
    if($uploadOk == 1) {
        // You MUST have the 'proof_path' column in your 'complaints' table for this to work
        $stmt = $conn->prepare("UPDATE complaints SET status='Resolved', proof_path=? WHERE id=?");
        $stmt->bind_param("si", $proof_path, $cid);
        
        if($stmt->execute()) {
            $msg = "<div class='alert alert-success fw-bold'><i class='fa-solid fa-check-circle'></i> Great Job! Task #$cid marked as Completed.</div>";
            
            // Trigger Email (Optional)
            if (file_exists('mailer.php')) {
                include_once 'mailer.php';
                if (function_exists('sendResolvedNotification')) {
                    $user_sql = "SELECT u.email, u.name FROM users u JOIN complaints c ON u.id = c.user_id WHERE c.id = '$cid'";
                    $res = mysqli_query($conn, $user_sql);
                    if ($u = mysqli_fetch_assoc($res)) {
                        sendResolvedNotification($u['email'], $u['name'], $cid);
                    }
                }
            }
        }
    }
}

// 3. CRASH-PROOF TIME HELPER FUNCTION (FIXED)
function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year', 'm' => 'month', 'w' => 'week',
        'd' => 'day', 'h' => 'hour', 'i' => 'minute',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    // If nothing elapsed (less than a minute)
    if (empty($string)) {
        return 'just now';
    }
    
    // Return only the biggest unit (e.g., 2 hours ago)
    return array_shift($string) . ' ago';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Field Worker App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --success: #10b981; --bg-color: #f3f4f6; }
        body { background-color: var(--bg-color); font-family: 'Inter', sans-serif; padding-bottom: 80px; }
        .navbar { background: #1f2937; color: white; padding: 15px 0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .task-card { border: none; border-radius: 16px; overflow: hidden; background: white; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); transition: transform 0.2s; margin-bottom: 25px; }
        .card-img-wrapper { position: relative; height: 200px; }
        .card-img-top { height: 100%; width: 100%; object-fit: cover; }
        .status-badge { position: absolute; top: 15px; right: 15px; background: rgba(255, 255, 255, 0.95); color: #d97706; padding: 5px 12px; border-radius: 20px; font-weight: 700; font-size: 0.85rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .category-badge { display: inline-block; background: #eff6ff; color: var(--primary); padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; margin-bottom: 10px; }
        .action-btn { border-radius: 12px; padding: 10px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; transition: 0.2s; }
        .btn-call { background: #f3f4f6; color: #374151; flex: 1; }
        .btn-nav { background: #e0e7ff; color: #4338ca; flex: 1; }
        .btn-resolve { background: var(--success); color: white; width: 100%; padding: 14px; font-size: 1.1rem; border: none; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3); }
        .btn-resolve:hover { background: #059669; }
        .time-badge { font-size: 0.8rem; color: #6b7280; display: flex; align-items: center; gap: 5px; }
    </style>
</head>
<body>

<nav class="navbar sticky-top">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:40px; height:40px; font-weight:bold;">
                <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
            </div>
            <div>
                <div style="font-size: 0.75rem; opacity: 0.8;">LOGGED IN AS</div>
                <div style="font-weight: 600; line-height: 1.1;"><?php echo $_SESSION['name']; ?></div>
            </div>
        </div>
        <a href="logout.php" class="text-white opacity-75"><i class="fa-solid fa-right-from-bracket fa-lg"></i></a>
    </div>
</nav>

<div class="container mt-4">
    
    <!-- Success/Error Message -->
    <?php echo $msg; ?>

    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-dark">My Tasks</h4>
            <p class="text-muted small mb-0">Upload proof to complete tasks</p>
        </div>
        <?php
        $count_res = $conn->query("SELECT count(*) as total FROM complaints WHERE assigned_to = '$eid' AND status != 'Resolved'")->fetch_assoc();
        ?>
        <span class="badge bg-dark rounded-pill px-3 py-2"><?php echo $count_res['total']; ?> Active</span>
    </div>

    <div class="row">
        <?php
        $sql = "SELECT c.*, u.name as uname, u.phone as uphone 
                FROM complaints c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.assigned_to = '$eid' AND c.status != 'Resolved' 
                ORDER BY c.created_at ASC"; 
                
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $photo = !empty($row['photo_path']) ? $row['photo_path'] : 'https://placehold.co/600x400?text=No+Photo';
        ?>
            <div class="col-md-6 col-lg-4">
                <div class="task-card">
                    <div class="card-img-wrapper">
                        <img src="<?php echo $photo; ?>" class="card-img-top">
                        <div class="status-badge"><i class="fa-solid fa-hourglass-half me-1"></i> In Progress</div>
                    </div>

                    <div class="p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="category-badge"><?php echo $row['category']; ?></span>
                            <span class="time-badge"><i class="fa-regular fa-clock"></i> <?php echo time_elapsed_string($row['created_at']); ?></span>
                        </div>

                        <h5 class="fw-bold mb-2">Issue #<?php echo $row['id']; ?></h5>
                        <p class="text-muted small mb-4"><?php echo $row['description']; ?></p>
                        <hr class="opacity-10">

                        <div class="d-flex gap-2 mb-3">
                            <a href="tel:<?php echo $row['uphone']; ?>" class="action-btn btn-call"><i class="fa-solid fa-phone"></i> Call</a>
                            <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo $row['latitude']; ?>,<?php echo $row['longitude']; ?>" target="_blank" class="action-btn btn-nav"><i class="fa-solid fa-diamond-turn-right"></i> Navigate</a>
                        </div>

                        <!-- UPLOAD FORM -->
                        <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('Confirm task completion?');">
                            <input type="hidden" name="complaint_id" value="<?php echo $row['id']; ?>">
                            
                            <!-- File Input -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Proof of Completion (Required)</label>
                                <input type="file" name="proof_photo" class="form-control" accept="image/*" required>
                            </div>

                            <button type="submit" name="mark_resolved" class="action-btn btn-resolve rounded-pill">
                                <i class="fa-solid fa-camera me-2"></i> Upload & Complete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php 
            }
        } else {
            echo "<div class='col-12 text-center mt-5'>
                <div class='bg-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm mb-4' style='width:100px; height:100px;'>
                    <i class='fa-solid fa-mug-hot fa-3x text-success'></i>
                </div>
                <h4 class='fw-bold text-dark'>All caught up!</h4>
                <p class='text-muted'>No active tasks. Great work!</p>
                <button onclick='location.reload()' class='btn btn-outline-dark rounded-pill px-4 mt-2'><i class='fa-solid fa-rotate-right me-2'></i> Refresh</button>
            </div>";
        }
        ?>
    </div>
</div>

</body>
</html>