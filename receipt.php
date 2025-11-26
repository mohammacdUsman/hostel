<?php
include 'db.php';
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$bid = isset($_GET['id']) ? intval($_GET['id']) : 0;
$uid = $_SESSION['user_id'];

// Fetch Booking Details
$sql = "SELECT b.*, h.name as hostel_name, h.price, u.name as student_name, u.email 
        FROM bookings b 
        JOIN hostels h ON b.hostel_id = h.id 
        JOIN users u ON b.student_id = u.id 
        WHERE b.id = $bid AND b.student_id = $uid";
$res = $conn->query($sql);

if($res->num_rows == 0) { die("Receipt not found."); }
$row = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt #<?php echo $bid; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; padding: 50px; font-family: 'Courier New', Courier, monospace; }
        .receipt-box { max-width: 600px; margin: auto; background: white; padding: 30px; border: 2px dashed #333; }
    </style>
</head>
<body>
    <div class="receipt-box">
        <h2 class="text-center fw-bold">HOSTELHUB RECEIPT</h2>
        <p class="text-center text-muted">Booking Reference #<?php echo $bid; ?></p>
        <hr>
        <div class="row">
            <div class="col-6"><strong>Student:</strong><br><?php echo $row['student_name']; ?><br><?php echo $row['email']; ?></div>
            <div class="col-6 text-end"><strong>Date:</strong><br><?php echo date('d M Y'); ?></div>
        </div>
        <hr>
        <table class="table table-borderless">
            <tr><td>Hostel Name</td><td class="text-end"><?php echo $row['hostel_name']; ?></td></tr>
            <tr><td>Monthly Rent</td><td class="text-end">PKR <?php echo number_format($row['price']); ?></td></tr>
            <tr class="border-top"><th>TOTAL PAID</th><th class="text-end">PKR <?php echo number_format($row['price']); ?></th></tr>
        </table>
        <hr>
        <div class="text-center">
            <span class="badge bg-success fs-6 px-3 py-2">PAID & VERIFIED</span>
            <p class="mt-3 small">Thank you for using HostelHub!</p>
            <button onclick="window.print()" class="btn btn-dark btn-sm no-print">Print Receipt</button>
        </div>
    </div>
</body>
</html>