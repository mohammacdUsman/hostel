<?php
include 'config.php';

if (isset($_POST['book_now']) && isset($_SESSION['user_id'])) {
    $student_id = $_SESSION['user_id'];
    $hostel_id = $_POST['hostel_id'];
    $message = $_POST['message'];

    // Prevent double booking
    $check = $conn->prepare("SELECT id FROM bookings WHERE student_id = ? AND hostel_id = ? AND status = 'pending'");
    $check->bind_param("ii", $student_id, $hostel_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('You already have a pending request for this hostel.'); window.location='index.php';</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO bookings (hostel_id, student_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $hostel_id, $student_id, $message);
        
        if ($stmt->execute()) {
            echo "<script>alert('Booking Request Sent!'); window.location='my_bookings.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
} else {
    header("Location: index.php");
}
?>