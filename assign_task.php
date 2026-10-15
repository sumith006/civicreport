<?php include 'includes/db.php';
if(isset($_POST['emp_id'])){
    $eid = $_POST['emp_id'];
    $cid = $_POST['cid'];
    $conn->query("UPDATE complaints SET assigned_to = $eid, status = 'In Progress' WHERE id = $cid");
    header("Location: admin_dashboard.php");
}
?>