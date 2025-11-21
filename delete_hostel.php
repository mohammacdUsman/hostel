<?php
include 'config.php';

// Check if user is logged in and is an owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $hostel_id = $_GET['id'];
    $owner_id = $_SESSION['user_id'];

    // Security Check: Ensure the person deleting the hostel actually OWNS it
    $check = $conn->prepare("SELECT * FROM hostels WHERE id = ? AND owner_id = ?");
    $check->bind_param("ii", $hostel_id, $owner_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Delete the hostel
        $del = $conn->prepare("DELETE FROM hostels WHERE id = ?");
        $del->bind_param("i", $hostel_id);
        if ($del->execute()) {
            header("Location: add_hostel.php?msg=deleted");
        } else {
            echo "Error deleting record.";
        }
    } else {
        echo "You do not have permission to delete this hostel.";
    }
}
?>