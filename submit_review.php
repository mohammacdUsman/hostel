<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $hostel_id = intval($_POST['hostel_id']);
    $student_id = $_SESSION['user_id'];
    $rating = intval($_POST['rating']);
    $comment = htmlspecialchars($_POST['comment']);

    // Security: Check if student already reviewed this hostel
    $check = $conn->query("SELECT id FROM reviews WHERE student_id=$student_id AND hostel_id=$hostel_id");
    
    if ($check->num_rows > 0) {
        echo "<script>alert('You have already reviewed this hostel.'); window.location='details.php?id=$hostel_id';</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO reviews (hostel_id, student_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $hostel_id, $student_id, $rating, $comment);
        $stmt->execute();
        echo "<script>window.location='details.php?id=$hostel_id';</script>";
    }
} else {
    header("Location: login.php");
}
?>