<?php
include 'config.php';

// 1. Security Check: Only 'admin' can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Handle Actions (Delete & Verify)
if (isset($_GET['delete_user'])) {
    $uid = $_GET['delete_user'];
    if($uid != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id=$uid");
    }
    header("Location: admin_dashboard.php?msg=deleted");
}

if (isset($_GET['delete_hostel'])) {
    $hid = $_GET['delete_hostel'];
    $conn->query("DELETE FROM hostels WHERE id=$hid");
    header("Location: admin_dashboard.php?msg=deleted");
}

// Verify Logic
if (isset($_GET['verify_hostel'])) {
    $hid = $_GET['verify_hostel'];
    $conn->query("UPDATE hostels SET is_verified = 1 WHERE id=$hid");
    header("Location: admin_dashboard.php?msg=verified");
}

// Un-Verify Logic
if (isset($_GET['unverify_hostel'])) {
    $hid = $_GET['unverify_hostel'];
    $conn->query("UPDATE hostels SET is_verified = 0 WHERE id=$hid");
    header("Location: admin_dashboard.php?msg=unverified");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HostelHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
    <!-- PRELOADER -->
    <div id="preloader"><div class="spinner"></div></div>

    <!-- NAVBAR -->
    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        
        <!-- Header (Responsive Flex) -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h2 class="fw-bold text-primary mb-0"><i class="bi bi-shield-lock-fill"></i> Admin Panel</h2>
                <p class="text-muted mb-0">Manage users, listings, and verification status.</p>
            </div>
            <div>
                <span class="badge bg-dark p-2 shadow-sm">Super Admin Access</span>
            </div>
        </div>

        <!-- Statistics Cards (Responsive Grid) -->
        <div class="row mb-4">
            <!-- Card 1 -->
            <div class="col-12 col-md-4 mb-3">
                <div class="card shadow-sm border-0 border-start border-primary border-5 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase fw-bold small">Total Users</h6>
                            <h2 class="mb-0 display-6 fw-bold"><?php echo $conn->query("SELECT * FROM users")->num_rows; ?></h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                            <i class="bi bi-people-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Card 2 -->
            <div class="col-12 col-md-4 mb-3">
                <div class="card shadow-sm border-0 border-start border-success border-5 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase fw-bold small">Total Hostels</h6>
                            <h2 class="mb-0 display-6 fw-bold"><?php echo $conn->query("SELECT * FROM hostels")->num_rows; ?></h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                            <i class="bi bi-building-check fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Card 3 -->
            <div class="col-12 col-md-4 mb-3">
                <div class="card shadow-sm border-0 border-start border-warning border-5 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase fw-bold small">Bookings</h6>
                            <h2 class="mb-0 display-6 fw-bold"><?php echo $conn->query("SELECT * FROM bookings")->num_rows; ?></h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                            <i class="bi bi-bookmarks-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 1: MANAGE HOSTELS -->
        <div class="card shadow-sm border-0 mb-5">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-houses"></i> Manage Hostels</h5>
            </div>
            <div class="card-body p-0">
                
                <!-- Responsive Table Wrapper -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th>Hostel Details</th>
                                <th>Area</th>
                                <th>Owner</th>
                                <th>Verified?</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch hostels with verified status
                            $h_sql = "SELECT h.*, u.name as owner_name, u.email as owner_email 
                                      FROM hostels h 
                                      JOIN users u ON h.owner_id = u.id 
                                      ORDER BY h.id DESC";
                            $h_res = $conn->query($h_sql);

                            if($h_res->num_rows > 0) {
                                while($row = $h_res->fetch_assoc()){
                                    // Safely check verification status
                                    $is_verified = isset($row['is_verified']) ? $row['is_verified'] : 0;

                                    $verified_badge = ($is_verified == 1) 
                                        ? '<span class="badge bg-primary"><i class="bi bi-patch-check-fill"></i> Verified</span>' 
                                        : '<span class="badge bg-secondary">Unverified</span>';
                                    
                                    echo "<tr>";
                                    // Image & Name
                                    echo "<td style='min-width: 200px;'>
                                            <div class='d-flex align-items-center'>
                                                <img src='".$row['image_url']."' class='rounded me-2' width='40' height='40' style='object-fit:cover'>
                                                <div>
                                                    <span class='fw-bold d-block text-truncate' style='max-width: 150px;'>".$row['name']."</span>
                                                    <small class='text-muted'>ID: ".$row['id']."</small>
                                                </div>
                                            </div>
                                          </td>";
                                    echo "<td class='text-nowrap'>".$row['area']."</td>";
                                    echo "<td style='min-width: 150px;'>".$row['owner_name']."<br><small class='text-muted'>".$row['owner_email']."</small></td>";
                                    echo "<td>$verified_badge</td>";
                                    
                                    echo "<td class='text-nowrap'>
                                            <div class='btn-group shadow-sm'>
                                                <a href='details.php?id=".$row['id']."' target='_blank' class='btn btn-sm btn-outline-primary' title='View Details'><i class='bi bi-eye'></i></a>";
                                                
                                                if($is_verified == 1) {
                                                    echo "<a href='admin_dashboard.php?unverify_hostel=".$row['id']."' class='btn btn-sm btn-warning text-white' title='Revoke Verification'><i class='bi bi-x-circle'></i></a>";
                                                } else {
                                                    echo "<a href='admin_dashboard.php?verify_hostel=".$row['id']."' class='btn btn-sm btn-success' title='Approve & Verify'><i class='bi bi-check-circle'></i></a>";
                                                }

                                    echo       "<a href='admin_dashboard.php?delete_hostel=".$row['id']."' class='btn btn-sm btn-danger' onclick='return confirm(\"Delete this hostel completely?\")' title='Delete'><i class='bi bi-trash'></i></a>
                                            </div>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No hostels found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECTION 2: MANAGE USERS -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-people"></i> Manage Users</h5>
            </div>
            <div class="card-body p-0">
                
                <!-- Responsive Table Wrapper -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th>User Info</th>
                                <th>Role</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $u_res = $conn->query("SELECT * FROM users ORDER BY id DESC");
                            while($row = $u_res->fetch_assoc()){
                                $role_color = 'bg-secondary';
                                if($row['role'] == 'admin') $role_color = 'bg-danger';
                                if($row['role'] == 'owner') $role_color = 'bg-info text-dark';
                                if($row['role'] == 'student') $role_color = 'bg-success';

                                echo "<tr>";
                                echo "<td style='min-width: 200px;'>
                                        <div class='fw-bold'>".$row['name']."</div>
                                        <small class='text-muted'>".$row['email']."</small>
                                      </td>";
                                echo "<td><span class='badge $role_color text-uppercase shadow-sm'>".$row['role']."</span></td>";
                                echo "<td class='text-nowrap'>".date('d M Y', strtotime($row['created_at']))."</td>";
                                echo "<td>";
                                
                                // Don't allow deleting admins
                                if($row['role'] != 'admin') {
                                    echo "<a href='admin_dashboard.php?delete_user=".$row['id']."' class='btn btn-outline-danger btn-sm' onclick='return confirm(\"Delete this user account permanently?\")'><i class='bi bi-trash'></i> Delete</a>";
                                } else {
                                    echo "<span class='text-muted small'><i class='bi bi-lock-fill'></i> Protected</span>";
                                }
                                
                                echo "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener("load", function () {
            var loader = document.getElementById("preloader");
            if(loader){
                loader.style.opacity = "0"; 
                setTimeout(function(){ loader.style.display = "none"; }, 500);
            }
        });
    </script>
</body>
</html>