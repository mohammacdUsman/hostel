<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}

$msg = "";
$uid = $_SESSION['user_id'];

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    // Security: Use Real Escape String
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    
    // Update Name/Phone
    $sql = "UPDATE users SET name='$name', phone='$phone' WHERE id='$uid'";
    $conn->query($sql);
    
    // Update Password if provided
    if (!empty($_POST['password'])) {
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$pass' WHERE id='$uid'");
    }

    // Update Session Name immediately
    $_SESSION['name'] = $name;

    $msg = "<div class='alert alert-success shadow-sm border-0'><i class='bi bi-check-circle-fill'></i> Profile Updated Successfully!</div>";
}

// Fetch User Data
$user = $conn->query("SELECT * FROM users WHERE id='$uid'")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- CRITICAL FOR MOBILE RESPONSIVENESS -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>My Profile - HostelHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
    <!-- PRELOADER START -->
    <div id="preloader">
        <div class="spinner"></div>
    </div>
    <!-- PRELOADER END -->

    <?php include 'navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white p-4">
                        <h4 class="mb-0 fw-bold"><i class="bi bi-person-gear"></i> Edit Profile</h4>
                        <p class="mb-0 small opacity-75">Update your personal details</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php echo $msg; ?>
                        
                        <form method="POST">
                            <!-- Full Name -->
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                                    <input type="text" name="name" class="form-control bg-light border-start-0" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                            </div>
                            
                            <!-- Email (Read Only) -->
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                                    <input type="text" class="form-control bg-light border-start-0 text-muted" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="cursor: not-allowed;">
                                </div>
                                <div class="form-text small"><i class="bi bi-lock"></i> Email cannot be changed.</div>
                            </div>
                            
                            <!-- Phone -->
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone"></i></span>
                                    <input type="text" name="phone" class="form-control bg-light border-start-0" value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="0300-1234567">
                                </div>
                            </div>
                            
                            <hr class="my-4 opacity-25">
                            
                            <!-- Password -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-danger small text-uppercase">Change Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-key"></i></span>
                                    <input type="password" name="password" class="form-control bg-light border-start-0" placeholder="Leave blank to keep current password">
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" name="update_profile" class="btn btn-success btn-lg fw-bold shadow-sm">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

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
    
    <!-- ANIMATION SCRIPT -->
    <script>
        window.addEventListener("load", function () {
            var loader = document.getElementById("preloader");
            if(loader) {
                loader.style.opacity = "0"; 
                setTimeout(function(){ 
                    loader.style.display = "none"; 
                }, 500);
            }
        });
    </script>
</body>
</html>