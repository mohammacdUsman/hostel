<?php
include 'db.php';
include 'header_sidebar.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    echo "<script>window.location='login.php';</script>"; 
    exit();
}

$owner_id = $_SESSION['user_id'];

// 2. Handle Approve/Reject Logic
if (isset($_GET['action']) && isset($_GET['id'])) {
    $booking_id = intval($_GET['id']);
    $action = $_GET['action']; 
    
    $status = '';
    if(strtolower($action) == 'approved') $status = 'Approved';
    if(strtolower($action) == 'rejected') $status = 'Rejected';

    if($status != '') {
        // Verify ownership
        $verify_sql = "SELECT b.id FROM bookings b JOIN hostels h ON b.hostel_id = h.id WHERE b.id = ? AND h.owner_id = ?";
        $stmt = $conn->prepare($verify_sql);
        $stmt->bind_param("ii", $booking_id, $owner_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $update = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $update->bind_param("si", $status, $booking_id);
            $update->execute();
            echo "<script>window.location.href='owner_bookings.php?msg=updated';</script>";
            exit();
        }
    }
}

// 3. Fetch ALL Hostels owned by this user
$my_hostels = $conn->query("SELECT * FROM hostels WHERE owner_id = $owner_id");
?>

<div class="content-wrapper">
    <div class="container-fluid" style="min-height: 80vh;">
        
        <!-- HEADER & ADD BUTTON -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0" style="font-family: 'Cinzel';">Owner Dashboard</h2>
                <p class="text-muted small">Manage your properties and bookings</p>
            </div>
            <a href="add_hostel.php" class="btn btn-gold shadow-sm">
                <i class="bi bi-plus-lg"></i> Add New Hostel
            </a>
        </div>

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle"></i> Action successful! <button class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <!-- 📊 ANALYTICS SECTION (NEW) -->
        <div class="row mb-5">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark"><i class="bi bi-graph-up-arrow text-gold" style="color: var(--gold);"></i> Booking Performance</h5>
                        <select class="form-select form-select-sm w-auto" onchange="updateChart(this)">
                            <option value="2023">Last 6 Months</option>
                        </select>
                    </div>
                    
                    <!-- CHART CANVAS -->
                    <div style="height: 300px;">
                        <canvas id="bookingChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // --- PHP LOGIC TO FETCH DATA FOR CHART ---
        // Get count of bookings grouped by Month Name
        $chart_sql = "SELECT DATE_FORMAT(b.created_at, '%M') as month_name, COUNT(b.id) as total 
                      FROM bookings b 
                      JOIN hostels h ON b.hostel_id = h.id 
                      WHERE h.owner_id = $owner_id 
                      GROUP BY MONTH(b.created_at) 
                      ORDER BY b.created_at ASC LIMIT 6";
        
        $chart_res = $conn->query($chart_sql);
        
        $months = [];
        $counts = [];
        
        while($c = $chart_res->fetch_assoc()) {
            $months[] = $c['month_name'];
            $counts[] = $c['total'];
        }
        
        // Convert PHP arrays to JSON for Javascript
        $json_months = json_encode($months);
        $json_counts = json_encode($counts);
        ?>

        <!-- CHART.JS LIBRARY -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            const ctx = document.getElementById('bookingChart').getContext('2d');
            
            // Create Gradient for Premium Look
            let gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(212, 175, 55, 0.5)'); // Gold Top
            gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');   // Transparent Bottom

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo $json_months; ?>, // Data from PHP
                    datasets: [{
                        label: 'New Bookings',
                        data: <?php echo $json_counts; ?>, // Data from PHP
                        borderColor: '#D4AF37', // Gold Line
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#D4AF37',
                        pointRadius: 6,
                        fill: true,
                        tension: 0.4 // Smooth curves
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [5, 5] }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        </script>

        <!-- SECTION 1: MY HOSTELS LIST -->
        <h5 class="fw-bold text-dark mb-3 border-start border-4 border-warning ps-3">My Properties</h5>
        
        <?php if ($my_hostels->num_rows > 0): ?>
            <div class="row g-3 mb-5">
                <?php while($h = $my_hostels->fetch_assoc()): 
                    $img = !empty($h['image']) ? 'uploads/'.$h['image'] : 'https://via.placeholder.com/100';
                ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="d-flex p-2 align-items-center">
                            <img src="<?php echo $img; ?>" class="rounded" style="width: 70px; height: 70px; object-fit: cover;">
                            <div class="ms-3 overflow-hidden">
                                <h6 class="fw-bold mb-1 text-truncate"><?php echo htmlspecialchars($h['name']); ?></h6>
                                <small class="text-muted d-block"><i class="bi bi-geo-alt"></i> <?php echo $h['area']; ?></small>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0">
                            <div class="d-grid gap-2">
                                <a href="edit_hostel.php?id=<?php echo $h['id']; ?>" class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-pencil-square"></i> Edit Details
                                </a>
                                <a href="manage_hostel.php?id=<?php echo $h['id']; ?>" class="btn btn-sm btn-light text-muted">
                                    <i class="bi bi-megaphone"></i> Announcement
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-light border text-center py-4 mb-5">
                <h5 class="text-muted">You haven't listed any hostels yet.</h5>
                <a href="add_hostel.php" class="btn btn-sm btn-gold mt-2">List Your First Hostel</a>
            </div>
        <?php endif; ?>


       <!-- SECTION 2: BOOKING REQUESTS -->
        <h5 class="fw-bold text-dark mb-3 border-start border-4 border-primary ps-3">Booking & Payment Management</h5>
        
        <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th class="ps-4">Student</th>
                                <th>Hostel</th>
                                <th>Status</th>
                                <th>Payment Proof</th> <!-- NEW COLUMN -->
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // UPDATED SQL: Include payment_proof column
                            $sql = "SELECT b.id as booking_id, b.message, b.status, b.payment_proof, b.created_at,
                                           u.name as student_name, u.email, 
                                           h.name as hostel_name 
                                    FROM bookings b 
                                    JOIN hostels h ON b.hostel_id = h.id 
                                    JOIN users u ON b.student_id = u.id 
                                    WHERE h.owner_id = '$owner_id' 
                                    ORDER BY b.created_at DESC";
                            
                            $result = $conn->query($sql);

                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    // Status Colors
                                    $badge = 'bg-secondary';
                                    if($row['status'] == 'Approved') $badge = 'bg-success';
                                    if($row['status'] == 'Rejected') $badge = 'bg-danger';
                                    if($row['status'] == 'Pending') $badge = 'bg-warning text-dark';

                                    echo "<tr>
                                        <td class='ps-4'>
                                            <div class='fw-bold'>{$row['student_name']}</div>
                                            <small class='text-muted'>{$row['email']}</small>
                                        </td>
                                        <td class='text-primary fw-bold'>{$row['hostel_name']}</td>
                                        <td><span class='badge $badge'>{$row['status']}</span></td>
                                        
                                        <!-- NEW PAYMENT COLUMN -->
                                        <td>";
                                            if (!empty($row['payment_proof'])) {
                                                echo "<a href='uploads/{$row['payment_proof']}' target='_blank' class='btn btn-sm btn-outline-dark border-0'>
                                                        <i class='bi bi-file-earmark-image text-primary'></i> View Receipt
                                                      </a>";
                                            } else {
                                                echo "<small class='text-muted'>Not uploaded</small>";
                                            }
                                    echo "</td>

                                        <td>";
                                    
                                    // Buttons
                                    if ($row['status'] == 'Pending') {
                                        echo "<div class='btn-group'>
                                                <a href='owner_bookings.php?action=Approved&id={$row['booking_id']}' class='btn btn-sm btn-success' onclick=\"return confirm('Approve?');\" title='Approve'><i class='bi bi-check-lg'></i></a>
                                                <a href='owner_bookings.php?action=Rejected&id={$row['booking_id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Reject?');\" title='Reject'><i class='bi bi-x-lg'></i></a>
                                              </div>";
                                    } else {
                                        echo "<span class='text-muted small'><i class='bi bi-check-all'></i> Done</span>";
                                    }
                                    
                                    echo "</td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-5 text-muted'>No bookings received yet.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php'; ?>
</body>
</html>
