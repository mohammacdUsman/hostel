
<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HostelHub Faisalabad</title>
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
    
    <!-- HERO SECTION START -->
    <div class="hero-section d-flex align-items-center text-center text-white">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Find Your Second Home in Faisalabad</h1>
            <p class="lead mb-4">Safe, affordable, and verified hostels for students & professionals.</p>
            
            <!-- Search Box -->
            <div class="search-box p-4 bg-white rounded shadow-lg mx-auto" style="max-width: 1000px;">
                <form method="GET" class="row g-3">
                    <!-- 1. TYPE SELECT (Responsive: Full width on mobile, half on tablet, quarter on desktop) -->
                    <div class="col-12 col-md-6 col-lg-3 text-start">
                        <label class="form-label text-dark small fw-bold">TYPE</label>
                        <select name="category" class="form-select border-0 bg-light">
                            <option value="">All Types</option>
                            <option value="Boys">Boys Hostel</option>
                            <option value="Girls">Girls Hostel</option>
                            <option value="Family">Family Flat</option>
                        </select>
                    </div>

                    <!-- 2. AREA SELECT -->
                    <div class="col-12 col-md-6 col-lg-3 text-start">
                        <label class="form-label text-dark small fw-bold">LOCATION</label>
                        <select name="area" class="form-select border-0 bg-light">
                            <option value="">All Areas</option>
                            <option value="D-Ground">D-Ground</option>
                            <option value="Kohinoor City">Kohinoor City</option>
                            <option value="Peoples Colony">Peoples Colony</option>
                            <option value="Satyana Road">Satyana Road</option>
                            <option value="Madina Town">Madina Town</option>
                        </select>
                    </div>

                    <!-- 3. BUDGET INPUT -->
                    <div class="col-12 col-md-6 col-lg-3 text-start">
                        <label class="form-label text-dark small fw-bold">BUDGET</label>
                        <input type="number" name="max_price" class="form-control border-0 bg-light" placeholder="Max Price (PKR)">
                    </div>

                    <!-- 4. SEARCH BUTTON -->
                    <div class="col-12 col-md-6 col-lg-3 d-grid">
                        <label class="form-label text-white small d-none d-lg-block">.</label> <!-- Hide label on mobile to save space -->
                        <button type="submit" class="btn btn-primary fw-bold mt-lg-0 mt-2">SEARCH</button>
                    </div>
                </form>
            </div>
            <!-- End Search Box -->
        </div>
    </div>
    <!-- HERO SECTION END -->

    <!-- Hostel Listings -->
    <div class="container mt-5 mb-5">
        <div class="row g-4"> <!-- Added g-4 for better gap spacing -->
            <?php
            $sql = "SELECT * FROM hostels WHERE 1=1";
            
            if (!empty($_GET['area'])) {
                $area = $conn->real_escape_string($_GET['area']);
                $sql .= " AND area = '$area'";
            }
            if (!empty($_GET['max_price'])) {
                $price = $conn->real_escape_string($_GET['max_price']);
                $sql .= " AND price <= $price";
            }
            if (!empty($_GET['category'])) {
                $cat = $conn->real_escape_string($_GET['category']);
                $sql .= " AND category = '$cat'";
            }

            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
            ?>
                <!-- RESPONSIVE GRID: Mobile(1), Tablet(2), Desktop(3) -->
                <div class="col-12 col-md-6 col-lg-4"> 
                    <div class="card h-100 shadow-sm border-0">
                        
                        <!-- IMAGE CONTAINER -->
                        <div class="position-relative overflow-hidden">
                            <img src="<?php echo !empty($row['image_url']) ? $row['image_url'] : 'https://via.placeholder.com/300'; ?>" class="card-img-top hostel-img" alt="Hostel">
                            
                            <!-- WISHLIST HEART BUTTON (Only for Students) -->
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'student'): 
                                $uid = $_SESSION['user_id'];
                                $hid = $row['id'];
                                $w_check = $conn->query("SELECT * FROM wishlist WHERE student_id=$uid AND hostel_id=$hid");
                                $is_active = ($w_check->num_rows > 0) ? 'text-danger' : 'text-secondary';
                            ?>
                            <button class="position-absolute top-0 end-0 m-3 btn btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center" 
                                    style="width: 40px; height: 40px;"
                                    onclick="toggleWishlist(this, <?php echo $row['id']; ?>)">
                                <i class="bi bi-heart-fill <?php echo $is_active; ?>"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- CARD BODY -->
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted"><i class="bi bi-geo-alt-fill text-danger"></i> <?php echo htmlspecialchars($row['area']); ?></small>
                                
                                <!-- Verified Check -->
                                <?php if(isset($row['is_verified']) && $row['is_verified'] == 1): ?>
                                    <small class="text-primary fw-bold"><i class="bi bi-patch-check-fill"></i> Verified</small>
                                <?php endif; ?>
                            </div>

                            <!-- NAME & PRICE ROW -->
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <h5 class="card-title fw-bold text-dark mb-0 text-truncate" style="max-width: 60%;">
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </h5>
                                <h5 class="text-primary fw-bold mb-0">
                                    Rs. <?php echo number_format($row['price']); ?>
                                </h5>
                            </div>
                            
                            <p class="card-text text-secondary small mt-2">
                                <?php echo substr(htmlspecialchars($row['description']), 0, 70); ?>...
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
                echo "<div class='col-12 text-center py-5 text-muted'><h4>No hostels found matching your criteria.</h4></div>";
            }
            ?>
        </div>
    </div>

    <!-- FOOTER START (Responsive Alignment) -->
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
    
    <!-- SCRIPTS -->
    <script>
        // 1. PRELOADER SCRIPT
        window.addEventListener("load", function () {
            var loader = document.getElementById("preloader");
            loader.style.opacity = "0"; 
            setTimeout(function(){ 
                loader.style.display = "none"; 
            }, 500);
        });

        // 2. WISHLIST AJAX SCRIPT
        function toggleWishlist(btn, hostelId) {
            var icon = btn.querySelector("i");
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "process_wishlist.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            
            xhr.onload = function () {
                var response = this.responseText.trim();
                
                if (response == "added") {
                    icon.classList.remove("text-secondary");
                    icon.classList.add("text-danger"); // Change to Red
                } else if (response == "removed") {
                    icon.classList.remove("text-danger");
                    icon.classList.add("text-secondary"); // Change to Grey
                } else if (response == "login_required") {
                    alert("Please Login as a Student to save hostels!");
                    window.location.href = "login.php";
                }
            };
            xhr.send("hostel_id=" + hostelId);
        }
    </script>
</body>
</html>