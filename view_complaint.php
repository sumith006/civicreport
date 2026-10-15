<?php 
include 'includes/db.php'; 
include 'includes/header.php'; 

// Check if ID is passed
if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger'>Invalid Request ID.</div>";
    include 'includes/footer.php';
    exit();
}

$cid = $_GET['id'];
$stmt = $conn->prepare("SELECT c.*, u.name as citizen_name, u.phone, e.name as employee_name 
                        FROM complaints c 
                        JOIN users u ON c.user_id = u.id 
                        LEFT JOIN users e ON c.assigned_to = e.id 
                        WHERE c.id = ?");
$stmt->bind_param("i", $cid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<div class='alert alert-danger'>Complaint not found.</div>";
    include 'includes/footer.php';
    exit();
}

$row = $result->fetch_assoc();

// Security: Prevent citizens from viewing others' complaints
if ($_SESSION['role'] == 'citizen' && $row['user_id'] != $_SESSION['user_id']) {
    echo "<div class='alert alert-danger'>Access Denied.</div>";
    include 'includes/footer.php';
    exit();
}
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Request #<?php echo $row['id']; ?> Details</h4>
                <span class="badge bg-light text-dark"><?php echo $row['status']; ?></span>
            </div>
            <div class="card-body">
                
                <h3 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                <p class="text-muted">Category: <span class="badge bg-info"><?php echo $row['category']; ?></span></p>
                <hr>
                
                <p><strong>Description:</strong><br> <?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5>Reporter Details</h5>
                        <p><strong>Name:</strong> <?php echo $row['citizen_name']; ?><br>
                        <strong>Date:</strong> <?php echo $row['created_at']; ?><br>
                        <strong>Phone:</strong> <?php echo $row['phone'] ?? 'N/A'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Resolution Status</h5>
                        <p><strong>Assigned To:</strong> <?php echo $row['employee_name'] ?? '<span class="text-danger">Not yet assigned</span>'; ?><br>
                        <strong>Current Status:</strong> <?php echo $row['status']; ?></p>
                    </div>
                </div>

                <?php if (!empty($row['photo_path'])): ?>
                    <hr>
                    <h5>Photo Evidence</h5>
                    <img src="<?php echo $row['photo_path']; ?>" class="img-fluid rounded border" alt="Evidence Photo" style="max-height: 400px;">
                <?php endif; ?>

                <?php if (!empty($row['latitude']) && !empty($row['longitude'])): ?>
                    <hr>
                    <h5>Location</h5>
                    <div id="map" style="height: 300px; width: 100%;" class="rounded border"></div>
                    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
                    <script>
                        var lat = <?php echo $row['latitude']; ?>;
                        var lng = <?php echo $row['longitude']; ?>;
                        var map = L.map('map').setView([lat, lng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                        L.marker([lat, lng]).addTo(map)
                            .bindPopup("<b>Incident Location</b><br><?php echo htmlspecialchars($row['title']); ?>").openPopup();
                    </script>
                    <div class="mt-2">
                        <a href="https://www.google.com/maps/search/?api=1&query=<?php echo $row['latitude'] . ',' . $row['longitude']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">Open in Google Maps</a>
                    </div>
                <?php endif; ?>

            </div>
            <div class="card-footer text-center">
                <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>