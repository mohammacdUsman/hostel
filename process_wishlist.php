<?php
include 'config.php';
if(!isset($_SESSION['user_id'])) { echo "login_required"; exit; }

if(isset($_POST['hostel_id'])) {
    $uid = $_SESSION['user_id'];
    $hid = $_POST['hostel_id'];

    // Check if already in wishlist
    $check = $conn->query("SELECT * FROM wishlist WHERE student_id=$uid AND hostel_id=$hid");
    
    if($check->num_rows > 0) {
        // Remove it
        $conn->query("DELETE FROM wishlist WHERE student_id=$uid AND hostel_id=$hid");
        echo "removed";
    } else {
        // Add it
        $conn->query("INSERT INTO wishlist (student_id, hostel_id) VALUES ($uid, $hid)");
        echo "added";
    }
}
?>