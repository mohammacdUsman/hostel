<?php
include 'config.php';
// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    header("Location: login.php"); exit();
}

// Handle Status Update
if (isset($_GET['action']) && isset($_GET['id'])) {
    $booking_id = $_GET['id'];
    $status = $_GET['action'];
    
    $owner_id = $_SESSION['user_id'];
    $verify = $conn->prepare("SELECT b.id FROM bookings b JOIN hostels h ON b.hostel_id = h.id WHERE b.id = ? AND h.owner_id = ?");
    $verify->bind_param("ii", $booking_id, $owner_id);
    $verify->execute();
    $res = $verify->get_result();

    if ($res->num_rows > 0) {
        $update = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $update->bind_param("si", $status, $booking_id);
        $update->execute();
        header("Location: owner_bookings.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - HostelHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
    <!-- PRELOADER -->
    <div id="preloader"><div class="spinner"></div></div>
    
    <!-- INCLUDE NAVBAR -->
    <?php include 'navbar.php'; ?>

    <div class="container mt-4" style="min-height: 70vh;">
        
        <!-- Header Section with responsive flex -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h2 class="fw-bold mb-0">Incoming Requests</h2>
            <a href="add_hostel.php" class="btn btn-outline-primary shadow-sm">
                <i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Dashboard</span>
            </a>
        </div>

        <div class="card shadow border-0 rounded-3 overflow-hidden">
            <div class="card-body p-0">
                
                <!-- RESPONSIVE TABLE WRAPPER START -->
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th class="ps-4" style="min-width: 200px;">Student Name</th>
                                <th style="min-width: 150px;">Hostel Requested</th>
                                <th style="min-width: 250px;">Message</th>
                                <th style="min-width: 100px;">Status</th>
                                <th style="min-width: 180px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $owner_id = $_SESSION['user_id'];
                            $sql = "SELECT b.id as booking_id, b.message, b.status, u.name as student_name, u.email, h.name as hostel_name 
                                    FROM bookings b 
                                    JOIN hostels h ON b.hostel_id = h.id 
                                    JOIN users u ON b.student_id = u.id 
                                    WHERE h.owner_id = '$owner_id' 
                                    ORDER BY b.booking_date DESC";
                            
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    
                                    // Student Info
                                    echo "<td class='ps-4'>
                                            <div class='fw-bold'>" . $row['student_name'] . "</div>
                                            <small class='text-muted'>" . $row['email'] . "</small>
                                          </td>";
                                    
                                    // Hostel Name
                                    echo "<td>" . $row['hostel_name'] . "</td>";
                                    
                                    // Message (Truncated if too long)
                                    echo "<td><small class='text-secondary'>" . (strlen($row['message']) > 50 ? substr($row['message'], 0, 50) . '...' : $row['message']) . "</small></td>";
                                    
                                    // Status Badge
                                    $badge_color = 'bg-secondary';
                                    if($row['status'] == 'approved') $badge_color = 'bg-success';
                                    if($row['status'] == 'rejected') $badge_color = 'bg-danger';
                                    if($row['status'] == 'pending') $badge_color = 'bg-warning text-dark';
                                    
                                    echo "<td><span class='badge rounded-pill $badge_color'>" . ucfirst($row['status']) . "</span></td>";
                                    
                                    // Action Buttons
                                    echo "<td>";
                                    if ($row['status'] == 'pending') {
                                        echo "<div class='d-flex gap-2'>
                                                <a href='owner_bookings.php?action=approved&id=".$row['booking_id']."' class='btn btn-success btn-sm shadow-sm'>Approve</a>
                                                <a href='owner_bookings.php?action=rejected&id=".$row['booking_id']."' class='btn btn-outline-danger btn-sm shadow-sm'>Reject</a>
                                              </div>";
                                    } else {
                                        echo "<span class='text-muted small'><i class='bi bi-check-all'></i> Done</span>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-5 text-muted'>
                                        <i class='bi bi-inbox fs-1 d-block mb-2'></i>
                                        No new booking requests found.
                                      </td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <!-- RESPONSIVE TABLE WRAPPER END -->

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