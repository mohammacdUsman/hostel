<?php 
include 'config.php'; 

// Security Check
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') { 
    header("Location: login.php"); 
    exit();
}

// Handle "Remove" Action directly on this page
if(isset($_GET['remove_id'])) {
    $rem_id = $_GET['remove_id'];
    $uid = $_SESSION['user_id'];
    $conn->query("DELETE FROM wishlist WHERE hostel_id = $rem_id AND student_id = $uid");
    header("Location: my_wishlist.php"); // Refresh page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - HostelHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
    <!-- PRELOADER -->
    <div id="preloader"><div class="spinner"></div></div>

    <!-- NAVBAR -->
    <?php include 'navbar.php'; ?>

    <div class="container mt-5 mb-5" style="min-height: 60vh;">
        
        <div class="d-flex align-items-center mb-4">
            <h2 class="fw-bold mb-0 text-danger"><i class="bi bi-heart-fill"></i> My Saved Hostels</h2>
        </div>

        <div class="row g-4">
            <?php
            $uid = $_SESSION['user_id'];
            $sql = "SELECT h.* FROM wishlist w JOIN hostels h ON w.hostel_id = h.id WHERE w.student_id = $uid";
            $result = $conn->query($sql);
            
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    ?>
                    <!-- RESPONSIVE CARD: Mobile(12), Tablet(6), Desktop(4) -->
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-0">
                            
                            <!-- IMAGE CONTAINER -->
                            <div class="position-relative overflow-hidden">
                                <img src="<?php echo !empty($row['image_url']) ? $row['image_url'] : 'https://via.placeholder.com/300'; ?>" class="card-img-top hostel-img" alt="Hostel">
                                
                                <!-- Price Badge -->
                                <span class="position-absolute top-0 start-0 m-3 badge bg-dark text-white shadow-sm" style="backdrop-filter: blur(5px); opacity: 0.9;">
                                    PKR <?php echo number_format($row['price']); ?>
                                </span>

                                <!-- Remove Button -->
                                <a href="my_wishlist.php?remove_id=<?php echo $row['id']; ?>" 
                                   class="position-absolute top-0 end-0 m-3 btn btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center text-danger" 
                                   style="width: 40px; height: 40px;" 
                                   onclick="return confirm('Remove from wishlist?');"
                                   title="Remove">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            </div>
                            
                            <!-- CARD BODY -->
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <small class="text-muted"><i class="bi bi-geo-alt-fill text-danger"></i> <?php echo htmlspecialchars($row['area']); ?></small>
                                    
                                    <?php if(isset($row['is_verified']) && $row['is_verified'] == 1): ?>
                                        <small class="text-primary fw-bold"><i class="bi bi-patch-check-fill"></i> Verified</small>
                                    <?php endif; ?>
                                </div>

                                <h5 class="card-title fw-bold text-dark mb-1 text-truncate"><?php echo htmlspecialchars($row['name']); ?></h5>
                                
                                <p class="card-text text-secondary small mt-2">
                                    <?php echo substr(htmlspecialchars($row['description']), 0, 60); ?>...
                                </p>
                                
                                <hr class="my-3 opacity-25">
                                <div class="d-grid">
                                    <a href="details.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-primary fw-bold rounded-pill">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='col-12 text-center py-5'>
                        <div class='text-muted mb-3'><i class='bi bi-heartbreak display-1'></i></div>
                        <h4 class='text-secondary'>Your wishlist is empty.</h4>
                        <a href='index.php' class='btn btn-primary mt-3'>Browse Hostels</a>
                      </div>";
            }
            ?>
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
            loader.style.opacity = "0"; 
            setTimeout(function(){ 
                loader.style.display = "none"; 
            }, 500);
        });
    </script>
</body>
</html>