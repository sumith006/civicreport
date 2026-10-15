<?php 
session_start();
include 'db_connect.php'; 

// 1. Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'citizen') {
    header("Location: login.php");
    exit();
}

// 2. Handle Form Submission
if(isset($_POST['submit_issue'])){
    // Sanitize inputs
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc_raw = mysqli_real_escape_string($conn, $_POST['description']);
    $cat = mysqli_real_escape_string($conn, $_POST['category']);
    $lat = $_POST['latitude']; 
    $long = $_POST['longitude'];
    $uid = $_SESSION['user_id'];

    // Combine Title & Details into one string for the 'description' column
    // This allows us to keep the existing database structure without adding a new column
    $final_description = "ISSUE: $title \nDETAILS: $desc_raw";
    
    // 3. Image Upload Handling
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = NULL;
    if(!empty($_FILES['photo']['name'])){
        $file_name = time() . "_" . basename($_FILES['photo']['name']);
        $target_path = $target_dir . $file_name;
        
        // Basic validation for image types
        $fileType = strtolower(pathinfo($target_path, PATHINFO_EXTENSION));
        if(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if(move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)){
                $target_file = $target_path;
            }
        }
    }

    // 4. Insert into DB
    // We insert 'Pending' as default status
    $stmt = $conn->prepare("INSERT INTO complaints (user_id, category, description, photo_path, latitude, longitude, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("isssss", $uid, $cat, $final_description, $target_file, $lat, $long);
    
    if($stmt->execute()){
        echo "<script>alert('Issue reported successfully! Thank you for helping the city.'); window.location.href='citizen_dashboard.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Civic Issue - BCIR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .form-card { border: none; border-radius: 15px; box-shadow: 0 10px 20px rgba(0,0,0,0.08); overflow: hidden; }
        .header-bg { background: linear-gradient(135deg, #FF512F 0%, #DD2476 100%); color: white; padding: 30px; }
        #map { height: 300px; border-radius: 10px; border: 2px solid #e9ecef; box-shadow: inset 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card form-card">
                <div class="header-bg text-center">
                    <h3 class="mb-1 fw-bold"><i class="fa-solid fa-camera"></i> Report a Civic Issue</h3>
                    <p class="mb-0 opacity-75 small">Help us fix Potholes, Street Lights, Garbage & more in Belagavi.</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    
                    <form method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">What is the issue?</label>
                            <input type="text" name="title" class="form-control form-control-lg bg-light" placeholder="e.g. Huge Pothole near Central Bus Stand" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">Category</label>
                            <select name="category" class="form-select" required>
                                <option value="" disabled selected>Select Category...</option>
                                <option value="Roads & Potholes">🚧 Roads & Potholes</option>
                                <option value="Street Lights">💡 Street Lights / Electricity</option>
                                <option value="Water Supply">💧 Water Supply / Leakage</option>
                                <option value="Garbage & Sanitation">🗑️ Garbage & Sanitation</option>
                                <option value="Drainage">🕳️ Blocked Drainage</option>
                                <option value="Parks & Trees">🌳 Public Park / Fallen Tree</option>
                                <option value="Traffic & Parking">🚗 Traffic / Illegal Parking</option>
                                <option value="Other">❓ Other</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">Additional Details</label>
                            <textarea name="description" class="form-control bg-light" rows="3" placeholder="Describe the severity and exact landmark..." required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">Photo Evidence (Optional)</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                            <div class="form-text"><i class="fa fa-info-circle"></i> Uploading a photo helps field workers identify the issue faster.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">Pin Exact Location <span class="text-danger">*</span></label>
                            <div id="map"></div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted" id="loc_status">Click on map to pin location.</small>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="getLocation()">
                                    <i class="fa-solid fa-location-crosshairs"></i> Detect My Location
                                </button>
                            </div>
                            
                            <input type="hidden" id="latitude" name="latitude" required>
                            <input type="hidden" id="longitude" name="longitude" required>
                        </div>

                        <hr class="my-4">

                        <div class="d-grid gap-2">
                            <button type="submit" name="submit_issue" class="btn btn-danger btn-lg rounded-pill fw-bold shadow">
                                <i class="fa-solid fa-paper-plane"></i> Submit Report
                            </button>
                            <a href="citizen_dashboard.php" class="btn btn-light text-muted rounded-pill">Cancel & Go Back</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // 1. Initialize Map centered on Belagavi
    var map = L.map('map').setView([15.8497, 74.4977], 13); 
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);
    
    var marker;

    // 2. Click to Pin Function
    map.on('click', function(e) {
        if(marker) map.removeLayer(marker);
        marker = L.marker(e.latlng).addTo(map);
        
        document.getElementById('latitude').value = e.latlng.lat;
        document.getElementById('longitude').value = e.latlng.lng;
        document.getElementById('loc_status').innerText = "Pinned: " + e.latlng.lat.toFixed(5) + ", " + e.latlng.lng.toFixed(5);
    });

    // 3. Get User Location Function
    function getLocation() {
        document.getElementById('loc_status').innerText = "Locating...";
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position){
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;
                
                // Update Inputs
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                document.getElementById('loc_status').innerText = "Detected: " + lat.toFixed(5) + ", " + lng.toFixed(5);
                
                // Update Map View
                map.setView([lat, lng], 16);
                
                // Place Marker
                if(marker) map.removeLayer(marker);
                marker = L.marker([lat, lng]).addTo(map)
                    .bindPopup("Your Location").openPopup();
            }, function() {
                alert("Could not access your location. Please ensure GPS is on and allow permission.");
                document.getElementById('loc_status').innerText = "Location access denied.";
            });
        } else {
            alert("Geolocation is not supported by this browser.");
        }
    }
</script>

</body>
</html>