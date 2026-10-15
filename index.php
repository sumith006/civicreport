<?php
session_start();
include 'db_connect.php';

// 1. Fetch System Stats
$total_alerts = $conn->query("SELECT count(*) as count FROM complaints")->fetch_assoc()['count'];
$resolved_alerts = $conn->query("SELECT count(*) as count FROM complaints WHERE status='Resolved'")->fetch_assoc()['count'];
$pending_alerts = $conn->query("SELECT count(*) as count FROM complaints WHERE status='Pending'")->fetch_assoc()['count'];

// 2. Fetch Map Data (Crash Proof)
$map_data = [];
$check_cols = $conn->query("SHOW COLUMNS FROM complaints LIKE 'latitude'");
if($check_cols->num_rows > 0) {
    // Only fetch if location data exists
    $loc_query = $conn->query("SELECT category, description, latitude, longitude, status FROM complaints WHERE latitude IS NOT NULL AND latitude != ''");
    while($row = $loc_query->fetch_assoc()) {
        $map_data[] = $row;
    }
}

// 3. SMART DASHBOARD BUTTON LOGIC
// If logged in, button says "Dashboard". If not, it says "Login".
$dashboard_link = "login.php"; 
$btn_text = "Login";
$is_logged_in = false;

if(isset($_SESSION['user_id'])) {
    $is_logged_in = true;
    $btn_text = "Go to Dashboard";
    if($_SESSION['role'] == 'admin') {
        $dashboard_link = "admin_dashboard.php";
    } elseif($_SESSION['role'] == 'helper') {
        $dashboard_link = "emp_dashboard.php";
    } else {
        $dashboard_link = "citizen_dashboard.php";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belagavi Civic Issue Reporter (BCIR)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1517090504586-fde19ea6066f?q=80&w=1600&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .stat-card { transition: transform 0.3s; cursor: pointer; border-radius: 15px; }
        .stat-card:hover { transform: translateY(-5px); }
        #map { height: 500px; width: 100%; border-radius: 15px; border: 4px solid #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top py-3">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fa-solid fa-city text-info"></i> Belagavi Civic Issue Reporter (<span class="text-info">BCIR</span>)
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link btn btn-info text-white px-4 rounded-pill fw-bold" href="<?php echo $dashboard_link; ?>">
                            <i class="fa-solid fa-user-circle"></i> <?php echo $btn_text; ?>
                        </a>
                    </li>
                    
                    <?php if($is_logged_in): ?>
                        <li class="nav-item ms-2">
                            <a class="nav-link btn btn-outline-danger px-3 rounded-pill" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-2">
                            <a class="nav-link btn btn-outline-light px-3 rounded-pill" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container">
            <h1 class="display-3 fw-bold mb-3">Better City, Better Life</h1>
            <p class="lead mb-4 opacity-75">Report potholes, broken lights, and garbage issues instantly.</p>
            
            <a href="<?php echo $dashboard_link; ?>" class="btn btn-lg btn-danger rounded-pill px-5 py-3 fw-bold shadow">
                <i class="fa-solid fa-camera"></i> Report an Issue Now
            </a>
        </div>
    </div>

    <div class="container mt-n5 position-relative" style="z-index: 2; margin-top: -50px;">
        <div class="row text-center g-4">
            <div class="col-md-4">
                <div class="card stat-card border-0 shadow p-4">
                    <h2 class="text-primary fw-bold display-4"><?php echo $total_alerts; ?></h2>
                    <p class="text-muted mb-0 fw-bold text-uppercase small">Total Reports</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card border-0 shadow p-4">
                    <h2 class="text-warning fw-bold display-4"><?php echo $pending_alerts; ?></h2>
                    <p class="text-muted mb-0 fw-bold text-uppercase small">Pending Fixes</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card border-0 shadow p-4">
                    <h2 class="text-success fw-bold display-4"><?php echo $resolved_alerts; ?></h2>
                    <p class="text-muted mb-0 fw-bold text-uppercase small">Resolved Issues</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5 py-5">
        <div class="row">
            <div class="col-lg-8 mb-4">
                <h4 class="fw-bold mb-3 text-secondary"><i class="fa-solid fa-map-location-dot"></i> Live Issue Map</h4>
                <div id="map"></div>
            </div>

            <div class="col-lg-4 mb-4">
                <h4 class="fw-bold mb-3 text-secondary"><i class="fa-solid fa-bell"></i> Recent Updates</h4>
                <div class="card shadow-sm border-0 h-100">
                    <div class="list-group list-group-flush rounded-3">
                        <?php
                        // Fetch last 5 active or resolved complaints
                        $recent = $conn->query("SELECT category, description, created_at, status FROM complaints ORDER BY created_at DESC LIMIT 5");
                        if($recent->num_rows > 0):
                            while($r = $recent->fetch_assoc()):
                                $status_color = ($r['status'] == 'Resolved') ? 'success' : (($r['status'] == 'Pending') ? 'warning' : 'info');
                        ?>
                            <div class="list-group-item p-3 border-bottom">
                                <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold text-dark"><?php echo $r['category']; ?></h6>
                                    <span class="badge bg-<?php echo $status_color; ?> rounded-pill" style="font-size: 0.7rem;">
                                        <?php echo $r['status']; ?>
                                    </span>
                                </div>
                                <p class="mb-1 small text-secondary text-truncate"><?php echo $r['description']; ?></p>
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    <i class="fa-regular fa-clock"></i> <?php echo date('M d, h:i A', strtotime($r['created_at'])); ?>
                                </small>
                            </div>
                        <?php endwhile; 
                        else: ?>
                            <div class="p-4 text-center text-muted">No recent reports found.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-0 opacity-50">© 2025 Belagavi Civic Issue Reporter (BCIR) | Built for Smart Cities</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // 1. Initialize Map centered on Belagavi
        var map = L.map('map').setView([15.8497, 74.4977], 13); 

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: ''
        }).addTo(map);

        // 2. Load Data Markers
        var incidents = <?php echo json_encode($map_data); ?>;

        incidents.forEach(function(item) {
            if(item.latitude && item.longitude) {
                // Color Code Logic (Resolved=Green, Pending=Red)
                // Note: Leaflet default markers are blue, customized icons would need extra code.
                // We use standard popup to show details.
                
                var popupContent = `
                    <div style="min-width: 150px;">
                        <strong class="text-primary">${item.category}</strong><br>
                        <span class="badge bg-dark mb-2">${item.status}</span><br>
                        ${item.description.substring(0, 50)}...
                    </div>
                `;

                L.marker([item.latitude, item.longitude])
                 .addTo(map)
                 .bindPopup(popupContent);
            }
        });
    </script>
</body>
</html>