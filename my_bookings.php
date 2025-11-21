<?php
include 'config.php';

// 1. SECURITY FIX: Allow 'student' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - HostelHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
    <!-- PRELOADER -->
    <div id="preloader"><div class="spinner"></div></div>

    <!-- INCLUDE NAVBAR -->
    <?php include 'navbar.php'; ?>

    <div class="container mt-5" style="min-height: 60vh;">
        <h2 class="fw-bold mb-4">My Booking Requests</h2>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                
                <!-- RESPONSIVE WRAPPER START -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <!-- Added min-width to prevent squashing on mobile -->
                                <th class="ps-4" style="min-width: 200px;">Hostel</th>
                                <th style="min-width: 120px;">Area</th>
                                <th style="min-width: 120px;">Date Sent</th>
                                <th style="min-width: 250px;">Message Sent</th>
                                <th style="min-width: 100px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $student_id = $_SESSION['user_id'];
                            
                            // QUERY: Select bookings for the specific STUDENT
                            $sql = "SELECT b.*, h.name, h.area, h.image_url 
                                    FROM bookings b 
                                    JOIN hostels h ON b.hostel_id = h.id 
                                    WHERE b.student_id = '$student_id'
                                    ORDER BY b.booking_date DESC";
                                    
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    // Status Color Logic
                                    $status_class = 'bg-secondary';
                                    if($row['status'] == 'approved') $status_class = 'bg-success';
                                    if($row['status'] == 'pending') $status_class = 'bg-warning text-dark';
                                    if($row['status'] == 'rejected') $status_class = 'bg-danger';

                                    echo "<tr>";
                                    // Hostel Image & Name
                                    echo "<td class='ps-4'>
                                            <div class='d-flex align-items-center'>
                                                <img src='".$row['image_url']."' class='rounded me-3' style='width: 50px; height: 50px; object-fit: cover;'>
                                                <span class='fw-bold'>".$row['name']."</span>
                                            </div>
                                          </td>";
                                    echo "<td>" . $row['area'] . "</td>";
                                    echo "<td>" . date('d M Y', strtotime($row['booking_date'])) . "</td>";
                                    echo "<td class='text-muted small'><em>" . (empty($row['message']) ? 'No message' : substr($row['message'], 0, 50).'...') . "</em></td>";
                                    echo "<td><span class='badge rounded-pill $status_class'>" . ucfirst($row['status']) . "</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-5 text-muted'>You haven't booked any hostels yet. <br><a href='index.php' class='btn btn-primary btn-sm mt-2'>Browse Hostels</a></td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <!-- RESPONSIVE WRAPPER END -->

            </div>
        </div>
    </div>

   <!-- FOOTER START -->
    <footer class="bg-dark text-white mt-5 pt-5 pb-3">
        <div class="container">
            <div class="row text-center text-md-start">
                <div class="col-md-4 mb-4">
                    <h5 class="text-warning fw-bold">HostelHub 🇵🇰</h5>
                    <p class="small text-secondary">
                        The easiest way for students in Faisalabad to find reliable, affordable, and safe hostel accommodation.
                    </p>
                </div>
                
                <div class="col-md-4 mb-4">
                    <h5 class="text-warning">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-decoration-none text-secondary">Home Search</a></li>
                        <li><a href="login.php" class="text-decoration-none text-secondary">Login / Register</a></li>
                        <li><a href="#" class="text-decoration-none text-secondary">Terms of Service</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4 mb-4">
                    <h5 class="text-warning">Contact Us</h5>
                    <p class="small text-secondary">
                        <i class="bi bi-geo-alt-fill"></i> D-Ground, Faisalabad<br>
                        <i class="bi bi-envelope-fill"></i> support@hostelhub.com<br>
                        <i class="bi bi-telephone-fill"></i> +92 300 1234567
                    </p>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="text-center small text-secondary">
                &copy; <?php echo date('Y'); ?> HostelHub Faisalabad. All Rights Reserved.
            </div>
        </div>
    </footer>
    <!-- FOOTER END -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Animation Script -->
    <script>
        window.addEventListener("load", function () {
            var loader = document.getElementById("preloader");
            loader.style.opacity = "0"; 
            setTimeout(function(){ 
                loader.style.display = "none"; 
            }, 500);
        });
    </script>
</body>
</html>