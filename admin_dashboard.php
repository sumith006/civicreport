<?php 
session_start();
include 'db_connect.php'; 

// 0. Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Helper Function for Alerts
function setAlert($icon, $title, $text) {
    $_SESSION['alert'] = ['icon' => $icon, 'title' => $title, 'text' => $text];
}

// 1. Logic: Delete Employee
if (isset($_GET['delete_emp'])) {
    $id = $_GET['delete_emp'];
    $conn->query("DELETE FROM users WHERE id='$id' AND role='helper'");
    setAlert('success', 'Deleted!', 'Field worker has been removed.');
    header("Location: admin_dashboard.php");
    exit();
}

// 2. Logic: Handle Status Updates
if (isset($_POST['update_status'])) {
    $id = $_POST['complaint_id'];
    $status = $_POST['update_status']; 
    $conn->query("UPDATE complaints SET status='$status' WHERE id='$id'");
    setAlert('success', 'Status Updated', 'Complaint marked as ' . $status);
    header("Location: admin_dashboard.php"); // Prevent resubmission
    exit();
}

// 3. Logic: Assign Task
if (isset($_POST['assign_task'])) {
    $cid = $_POST['complaint_id'];
    $hid = $_POST['helper_id'];
    $sql = empty($hid) ? "UPDATE complaints SET assigned_to = NULL, status = 'Pending' WHERE id = '$cid'" : "UPDATE complaints SET assigned_to = '$hid', status = 'In Progress' WHERE id = '$cid'";
    $conn->query($sql);
    setAlert('success', 'Task Assigned', 'The complaint has been assigned successfully.');
    header("Location: admin_dashboard.php");
    exit();
}

// 4. Logic: Add/Edit Employee
if (isset($_POST['save_employee'])) {
    $id = $_POST['emp_id'];
    $name = mysqli_real_escape_string($conn, $_POST['emp_name']);
    $email = mysqli_real_escape_string($conn, $_POST['emp_email']);
    $phone = $_POST['emp_phone'];
    
    if(empty($id)) { // Add
        $pass = password_hash($_POST['emp_pass'], PASSWORD_DEFAULT);
        $check = $conn->query("SELECT * FROM users WHERE email='$email'");
        if($check->num_rows > 0) {
            setAlert('error', 'Error', 'Email already exists!');
        } else { 
            $conn->query("INSERT INTO users (name, email, phone, password, role) VALUES ('$name', '$email', '$phone', '$pass', 'helper')"); 
            setAlert('success', 'Added', 'New team member added.');
        }
    } else { // Edit
        $pass_sql = !empty($_POST['emp_pass']) ? ", password='".password_hash($_POST['emp_pass'], PASSWORD_DEFAULT)."'" : "";
        $conn->query("UPDATE users SET name='$name', email='$email', phone='$phone' $pass_sql WHERE id='$id'");
        setAlert('success', 'Updated', 'Worker details updated.');
    }
    header("Location: admin_dashboard.php");
    exit();
}

// 5. Fetch Data
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'All';

$whereSQL = "WHERE 1";
if($filter != 'All') $whereSQL .= " AND c.status = '$filter'";
if($search) $whereSQL .= " AND (c.description LIKE '%$search%' OR c.id LIKE '%$search%')";

$stats = $conn->query("SELECT 
    (SELECT COUNT(*) FROM complaints) as total,
    (SELECT COUNT(*) FROM complaints WHERE status='Pending') as pending,
    (SELECT COUNT(*) FROM complaints WHERE status='Resolved') as resolved,
    (SELECT COUNT(*) FROM users WHERE role='citizen') as citizens
")->fetch_assoc();

$helpers = $conn->query("SELECT * FROM users WHERE role='helper'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CivicAdmin Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css" rel="stylesheet">
    
    <style>
        :root { --glass-bg: rgba(255, 255, 255, 0.95); --glass-border: 1px solid rgba(255, 255, 255, 0.3); }
        body { background: #eef2f6; font-family: 'Inter', sans-serif; color: #344767; }
        
        /* Modern Navbar */
        .navbar-glass {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
            border-bottom: var(--glass-border);
            padding: 15px 0;
        }
        
        /* Gradient Cards */
        .stat-card {
            border: none; border-radius: 20px; padding: 25px; color: white; position: relative; overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1); transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .bg-grad-1 { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .bg-grad-2 { background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 99%, #fecfef 100%); color: #555; }
        .bg-grad-3 { background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: #333; }
        .bg-grad-4 { background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%); color: #555; }
        .stat-icon { position: absolute; right: 15px; top: 15px; font-size: 4rem; opacity: 0.2; }
        
        /* Glass Panel */
        .glass-panel {
            background: white; border-radius: 20px; padding: 25px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05); border: var(--glass-border);
        }
        
        /* Table Styles */
        .table thead th { 
            background: #f8f9fa; border: none; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: #8898aa; padding: 15px; 
        }
        .table tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f1f1; font-weight: 500; }
        .status-badge { padding: 6px 12px; border-radius: 30px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; }
        .table-img { width: 40px; height: 40px; border-radius: 10px; object-fit: cover; }
        .proof-img, .issue-img { width: 100%; height: 200px; object-fit: cover; border-radius: 12px; }

        /* Print Styles */
        @media print {
            .navbar, .btn-primary, .btn-dark, form, .text-end { display: none !important; }
            .glass-panel, .stat-card { box-shadow: none !important; border: 1px solid #ddd !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-glass sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="#">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow" style="width:40px; height:40px;">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <span class="text-dark">Civic<span class="text-primary">Admin</span></span>
        </a>
        
        <div class="ms-auto d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-dark rounded-pill px-3 fw-bold d-none d-md-block">
                <i class="fa-solid fa-print"></i> Report
            </button>
            <a class="btn btn-danger text-white rounded-pill px-4 shadow-sm fw-bold" href="logout.php">
                <i class="fa-solid fa-power-off me-2"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container py-5">
    
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-grad-1">
                <i class="fa-solid fa-layer-group stat-icon"></i>
                <h2 class="fw-bold mb-0 display-5"><?php echo $stats['total']; ?></h2>
                <span class="fw-bold opacity-75">TOTAL REPORTS</span>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-grad-2">
                <i class="fa-solid fa-hourglass-half stat-icon"></i>
                <h2 class="fw-bold mb-0 display-5 text-dark"><?php echo $stats['pending']; ?></h2>
                <span class="fw-bold opacity-75">PENDING FIXES</span>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-grad-3">
                <i class="fa-solid fa-circle-check stat-icon"></i>
                <h2 class="fw-bold mb-0 display-5 text-dark"><?php echo $stats['resolved']; ?></h2>
                <span class="fw-bold opacity-75">RESOLVED</span>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-grad-4">
                <i class="fa-solid fa-users stat-icon"></i>
                <h2 class="fw-bold mb-0 display-5 text-dark"><?php echo $stats['citizens']; ?></h2>
                <span class="fw-bold opacity-75">CITIZENS</span>
            </div>
        </div>
    </div>

    <div class="glass-panel mb-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h4 class="fw-bold mb-0">Live Incident Feed</h4>
                <p class="text-muted small mb-0">Real-time reports from citizens</p>
            </div>
            
            <form class="d-flex gap-2 shadow-sm rounded-pill bg-light p-1">
                <input type="text" name="search" class="form-control border-0 bg-transparent ps-3" placeholder="Search ID..." value="<?php echo $search; ?>">
                <select name="filter" class="form-select border-0 bg-transparent fw-bold text-secondary" style="width:120px;" onchange="this.form.submit()">
                    <option value="All">All</option>
                    <option value="Pending" <?php if($filter=='Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Resolved" <?php if($filter=='Resolved') echo 'selected'; ?>>Resolved</option>
                </select>
                <button class="btn btn-primary rounded-circle" style="width:38px; height:38px;"><i class="fa fa-search"></i></button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>ID</th><th>Issue</th><th>Reporter</th><th>Status</th><th>Assign To</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                    <?php
                    $sql = "SELECT c.*, u.name as uname, u.email as uemail FROM complaints c JOIN users u ON c.user_id=u.id $whereSQL ORDER BY c.id DESC";
                    $res = $conn->query($sql);
                    
                    if($res->num_rows > 0):
                        while($row = $res->fetch_assoc()):
                            $status_style = match($row['status']) {
                                'Resolved' => 'background:#d1e7dd; color:#0f5132',
                                'Pending' => 'background:#fff3cd; color:#664d03',
                                default => 'background:#cff4fc; color:#055160'
                            };
                            
                            $options = "<option value=''>Assign...</option>";
                            if($helpers->num_rows > 0){
                                $helpers->data_seek(0);
                                while($h = $helpers->fetch_assoc()){
                                    $sel = ($row['assigned_to'] == $h['id']) ? 'selected' : '';
                                    $options .= "<option value='{$h['id']}' $sel>{$h['name']}</option>";
                                }
                            }
                    ?>
                    <tr>
                        <td class="fw-bold text-muted">#<?php echo $row['id']; ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if($row['photo_path']): ?>
                                    <img src="<?php echo $row['photo_path']; ?>" class="table-img me-3">
                                <?php endif; ?>
                                <div>
                                    <div class="fw-bold text-dark"><?php echo $row['category']; ?></div>
                                    <small class="text-muted d-block text-truncate" style="max-width: 200px;"><?php echo $row['description']; ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold"><?php echo $row['uname']; ?></div>
                            <small class="text-muted"><?php echo $row['uemail']; ?></small>
                        </td>
                        <td><span class="status-badge" style="<?php echo $status_style; ?>"><?php echo $row['status']; ?></span></td>
                        <td>
                            <form method="POST" class="d-flex gap-1">
                                <input type="hidden" name="complaint_id" value="<?php echo $row['id']; ?>">
                                <select name="helper_id" class="form-select form-select-sm border-0 bg-light fw-bold" style="width: 120px;"><?php echo $options; ?></select>
                                <button type="submit" name="assign_task" class="btn btn-sm btn-light border"><i class="fa-solid fa-check text-success"></i></button>
                            </form>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-light rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $row['id']; ?>">View</button>
                        </td>
                    </tr>

                    <div class="modal fade" id="viewModal<?php echo $row['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="fw-bold">Details: Complaint #<?php echo $row['id']; ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <h6 class="text-danger fw-bold text-uppercase small">Reported Issue</h6>
                                            <img src="<?php echo $row['photo_path'] ?: 'https://via.placeholder.com/400'; ?>" class="issue-img mb-2 shadow-sm">
                                            <p class="text-muted bg-light p-3 rounded mb-0"><?php echo $row['description']; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-success fw-bold text-uppercase small">Resolution Proof</h6>
                                            <?php if($row['proof_path']): ?>
                                                <img src="<?php echo $row['proof_path']; ?>" class="proof-img shadow-sm">
                                                <div class="alert alert-success mt-3 py-2 small"><i class="fa fa-check-circle"></i> Job Verified Complete</div>
                                            <?php else: ?>
                                                <div class="bg-light rounded h-50 d-flex align-items-center justify-content-center text-muted border border-dashed">No Proof Uploaded</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                                        <a href="http://maps.google.com/?q=<?php echo $row['latitude']; ?>,<?php echo $row['longitude']; ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill"><i class="fa fa-map-location-dot"></i> Map</a>
                                        <form method="POST">
                                            <input type="hidden" name="complaint_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="update_status" value="Resolved" class="btn btn-success btn-sm rounded-pill px-3 shadow">Mark Resolved</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; else: echo "<tr><td colspan='6' class='text-center py-5 text-muted'>No complaints found.</td></tr>"; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="staff-section" class="glass-panel">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Team Management</h4>
                <p class="text-muted small mb-0">Manage field workers and access</p>
            </div>
            <button class="btn btn-dark rounded-pill px-4 shadow-lg" onclick="openEmpModal()">
                <i class="fa-solid fa-user-plus me-2"></i> Add New Worker
            </button>
        </div>

        <div class="row g-3">
            <?php 
            $helpers->data_seek(0);
            while($h = $helpers->fetch_assoc()): 
                $h_json = htmlspecialchars(json_encode($h), ENT_QUOTES, 'UTF-8');
            ?>
            <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-white shadow-sm d-flex align-items-center justify-content-between position-relative">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-secondary fw-bold" style="width:45px; height:45px;">
                            <?php echo substr($h['name'], 0, 1); ?>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold"><?php echo $h['name']; ?></h6>
                            <small class="text-muted" style="font-size: 0.8rem;"><?php echo $h['email']; ?></small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-light border rounded-circle" onclick="openEmpModal(<?php echo $h_json; ?>)"><i class="fa fa-pen"></i></button>
                        <a href="?delete_emp=<?php echo $h['id']; ?>" class="btn btn-sm btn-light border rounded-circle text-danger" onclick="return confirm('Remove this worker?');"><i class="fa fa-trash"></i></a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

</div>

<div class="modal fade" id="empModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalTitle">Add Team Member</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="emp_id" id="emp_id">
                    <div class="mb-3">
                        <label class="fw-bold small text-muted text-uppercase">Full Name</label>
                        <input type="text" name="emp_name" id="emp_name" class="form-control bg-light border-0 py-2" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold small text-muted text-uppercase">Email</label>
                            <input type="email" name="emp_email" id="emp_email" class="form-control bg-light border-0 py-2" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold small text-muted text-uppercase">Phone</label>
                            <input type="text" name="emp_phone" id="emp_phone" class="form-control bg-light border-0 py-2" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="fw-bold small text-muted text-uppercase">Password</label>
                        <input type="text" name="emp_pass" id="emp_pass" class="form-control bg-light border-0 py-2" placeholder="Leave empty if not changing">
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-link text-decoration-none text-muted fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_employee" class="btn btn-dark rounded-pill px-4 fw-bold shadow">Save Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Logic to handle Modal Opening
function openEmpModal(data = null) {
    const modal = new bootstrap.Modal(document.getElementById('empModal'));
    if (data) {
        document.getElementById('modalTitle').innerText = "Edit Profile: " + data.name;
        document.getElementById('emp_id').value = data.id;
        document.getElementById('emp_name').value = data.name;
        document.getElementById('emp_email').value = data.email;
        document.getElementById('emp_phone').value = data.phone;
        document.getElementById('emp_pass').placeholder = "Enter new password to reset";
    } else {
        document.getElementById('modalTitle').innerText = "Add New Worker";
        document.getElementById('emp_id').value = "";
        document.getElementById('emp_name').value = "";
        document.getElementById('emp_email').value = "";
        document.getElementById('emp_phone').value = "";
        document.getElementById('emp_pass').placeholder = "Create a login password";
    }
    modal.show();
}

// Check for PHP Alerts and Fire SweetAlert
<?php if(isset($_SESSION['alert'])): ?>
    Swal.fire({
        icon: '<?php echo $_SESSION['alert']['icon']; ?>',
        title: '<?php echo $_SESSION['alert']['title']; ?>',
        text: '<?php echo $_SESSION['alert']['text']; ?>',
        timer: 3000,
        showConfirmButton: false
    });
    <?php unset($_SESSION['alert']); ?>
<?php endif; ?>
</script>

</body>
</html>