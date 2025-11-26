<?php
include 'db.php';
if(isset($_FILES['proof']) && isset($_POST['booking_id'])){
    $bid = $_POST['booking_id'];
    $img = time() . "_" . $_FILES['proof']['name'];
    move_uploaded_file($_FILES['proof']['tmp_name'], "uploads/" . $img);
    
    // Update DB: Status remains Approved, but we add proof
    $conn->query("UPDATE bookings SET payment_proof='$img' WHERE id=$bid");
    
    echo "<script>alert('Payment Proof Uploaded! Owner will verify.'); window.location='my_bookings.php';</script>";
}
?>