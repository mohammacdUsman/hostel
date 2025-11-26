<?php
// FIX: Start session and use db.php
session_start();
include 'db.php'; 

if (isset($_POST['book_now']) && isset($_SESSION['user_id'])) {
    $student_id = $_SESSION['user_id'];
    $hostel_id = $_POST['hostel_id'];
    
    // In details.php, we didn't have a text box, so we set a default message
    $message = isset($_POST['message']) ? $_POST['message'] : "I am interested in booking this hostel.";

    // 1. Prevent double booking for the same hostel
    $check = $conn->prepare("SELECT id FROM bookings WHERE student_id = ? AND hostel_id = ? AND status = 'Pending'");
    $check->bind_param("ii", $student_id, $hostel_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('You already have a pending request for this hostel.'); window.location='index.php';</script>";
    } else {
        // 2. Insert Booking
        $stmt = $conn->prepare("INSERT INTO bookings (hostel_id, student_id, message, status) VALUES (?, ?, ?, 'Pending')");
        $stmt->bind_param("iis", $hostel_id, $student_id, $message);
        
        if ($stmt->execute()) {
            // Redirect to My Bookings page (we will create this next if you don't have it)
            echo "<script>alert('Booking Request Sent Successfully!'); window.location='my_bookings.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
} else {
    // If accessed directly or not logged in
    header("Location: login.php");
}
?>