<?php
include 'db.php';
if(isset($_POST['issue'])){
    $uid = $_SESSION['user_id'];
    $hid = $_POST['hostel_id'];
    $iss = $_POST['issue'];
    $desc = $_POST['desc'];
    $conn->query("INSERT INTO complaints (student_id, hostel_id, issue_type, description) VALUES ($uid, $hid, '$iss', '$desc')");
    echo "<script>alert('Complaint Submitted!'); window.location='my_bookings.php';</script>";
}
?>