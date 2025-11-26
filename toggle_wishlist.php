<?php
include 'db.php'; // This now starts the session automatically

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "login_required";
    exit();
}

// 2. Check if user is a student (Case-Insensitive Check)
// This fixes issues if DB says "Student" but code checks "student"
if (strtolower($_SESSION['role']) != 'student') {
    echo "login_required";
    exit();
}

// 3. Handle Toggle
if (isset($_POST['hostel_id'])) {
    $uid = $_SESSION['user_id'];
    $hid = intval($_POST['hostel_id']);

    // Check if already in wishlist
    $check = $conn->query("SELECT id FROM wishlist WHERE student_id = $uid AND hostel_id = $hid");

    if ($check->num_rows > 0) {
        // Already there? Remove it
        $conn->query("DELETE FROM wishlist WHERE student_id = $uid AND hostel_id = $hid");
        echo "removed";
    } else {
        // Not there? Add it
        $conn->query("INSERT INTO wishlist (student_id, hostel_id) VALUES ($uid, $hid)");
        echo "added";
    }
}
?>