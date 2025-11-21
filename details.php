<?php 
include 'config.php';

// Helper function to turn text into icons
function getFacilityIcon($facility) {
    $f = strtolower(trim($facility));
    
    if (strpos($f, 'wifi') !== false || strpos($f, 'net') !== false) return '<i class="bi bi-wifi"></i>';
    if (strpos($f, 'ac') !== false || strpos($f, 'air') !== false) return '<i class="bi bi-snow"></i>';
    if (strpos($f, 'mess') !== false || strpos($f, 'food') !== false) return '<i class="bi bi-cup-hot-fill"></i>';
    if (strpos($f, 'generat') !== false || strpos($f, 'ups') !== false) return '<i class="bi bi-lightning-charge-fill"></i>';
    if (strpos($f, 'park') !== false) return '<i class="bi bi-car-front-fill"></i>';
    if (strpos($f, 'laund') !== false || strpos($f, 'wash') !== false) return '<i class="bi bi-bucket-fill"></i>';
    if (strpos($f, 'cctv') !== false) return '<i class="bi bi-camera-video-fill"></i>';
    if (strpos($f, 'geyser') !== false) return '<i class="bi bi-thermometer-half"></i>';

    return '<i class="bi bi-check-circle-fill"></i>';
}

if (!isset($_GET['id'])) header("Location: index.php");

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM hostels WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) die("Hostel not found.");

$hostel = $result->fetch_assoc();

// Handle View Count
$conn->query("UPDATE hostels SET views = views + 1 WHERE id = $id");

$facilities_list = explode(',', $hostel['facilities']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hostel['name']); ?> - Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Responsive Map Height */
        #map { width: 100%; border-radius: 10px; }
        
        /* Desktop Map Height */
        @media (min-width: 768px) { #map { height: 400px; } }
        /* Mobile Map Height */
        @media (max-width: 767px) { #map { height: 300px; } }

        /* Responsive Image Carousel Height */
        .carousel-custom-height { object-fit: cover; width: 100%; }
        @media (min-width: 768px) { .carousel-custom-height { height: 450px; } }
        @media (max-width: 767px) { .carousel-custom-height { height: 250px; } }
    </style>
</head>
<body>

    <!-- PRELOADER (Moved inside body for validity) -->
    <div id="preloader"><div class="spinner"></div></div>

    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <!-- Back Button -->
        <a href="index.php" class="btn btn-outline-secondary mb-3 shadow-sm rounded-pill px-4">
            <i class="bi bi-arrow-left"></i> Back to Search
        </a>
        
        <!-- Responsive Row with Gap -->
        <div class="row g-4">
            
            <!-- Left Column: Info -->
            <div class="col-lg-8 col-md-7 col-12">
                
                <!-- IMAGE CAROUSEL -->
                <div id="hostelCarousel" class="carousel slide mb-4 rounded-4 overflow-hidden shadow" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php
                        $img_sql = "SELECT * FROM hostel_images WHERE hostel_id = '$id'";
                        $img_res = $conn->query($img_sql);
                        $is_first = true;
                        
                        if($img_res->num_rows > 0) {
                            while($img = $img_res->fetch_assoc()) {
                                $active_class = $is_first ? 'active' : '';
                                echo '<div class="carousel-item '.$active_class.'">';
                                echo '<img src="'.$img['image_path'].'" class="carousel-custom-height d-block" alt="Hostel Image">';
                                echo '</div>';
                                $is_first = false;
                            }
                        } else {
                            echo '<div class="carousel-item active">';
                            echo '<img src="'.$hostel['image_url'].'" class="carousel-custom-height d-block" alt="Main Image">';
                            echo '</div>';
                        }
                        ?>
                    </div>
                    
                    <?php if($img_res->num_rows > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#hostelCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#hostelCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                        </button>
                    <?php endif; ?>
                </div>
                <!-- END CAROUSEL -->
                
                <!-- TITLE & PRICE BLOCK -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                    <div>
                        <h2 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($hostel['name']); ?></h2>
                        <p class="text-muted mb-0">
                            <i class="bi bi-geo-alt-fill text-danger"></i> <?php echo htmlspecialchars($hostel['area']); ?>, Faisalabad
                        </p>
                    </div>
                    <div class="mt-2 mt-md-0 text-md-end">
                        <h3 class="text-primary fw-bold mb-0">PKR <?php echo number_format($hostel['price']); ?></h3>
                        <small class="text-muted">per month</small>
                    </div>
                </div>
                
                <!-- FACILITIES -->
                <div class="card border-0 shadow-sm p-4 mb-4 bg-light rounded-4">
                    <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-stars text-warning"></i> Facilities</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach($facilities_list as $fac): ?>
                            <span class="badge bg-white text-dark border py-2 px-3 shadow-sm d-flex align-items-center gap-2 rounded-pill">
                                <span class="text-primary fs-6"><?php echo getFacilityIcon($fac); ?></span>
                                <span class="fw-semibold"><?php echo trim(htmlspecialchars($fac)); ?></span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- DESCRIPTION -->
                <div class="mb-4">
                    <h5 class="fw-bold">Description</h5>
                    <p class="text-secondary" style="line-height: 1.7;">
                        <?php echo nl2br(htmlspecialchars($hostel['description'])); ?>
                    </p>
                </div>

                <!-- REVIEWS SECTION -->
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white pt-4 px-4 border-0">
                        <h4 class="fw-bold"><i class="bi bi-star-fill text-warning"></i> Reviews</h4>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <!-- Existing Reviews -->
                        <?php
                        $sql_reviews = "SELECT r.*, u.name FROM reviews r JOIN users u ON r.student_id = u.id WHERE r.hostel_id = '$id' ORDER BY r.created_at DESC";
                        $res_reviews = $conn->query($sql_reviews);
                        
                        if ($res_reviews->num_rows > 0) {
                            while($rev = $res_reviews->fetch_assoc()) {
                                $stars = str_repeat("⭐", $rev['rating']);
                                echo "<div class='mb-3 border-bottom pb-3'>";
                                echo "<div class='d-flex justify-content-between'>";
                                echo "<strong>".$rev['name']."</strong>";
                                echo "<small class='text-warning'>".$stars."</small>";
                                echo "</div>";
                                echo "<p class='mb-1 text-muted small mt-1'>".htmlspecialchars($rev['comment'])."</p>";
                                echo "<small class='text-secondary' style='font-size: 0.8rem;'>".date('d M Y', strtotime($rev['created_at']))."</small>";
                                echo "</div>";
                            }
                        } else {
                            echo "<div class='alert alert-light text-center'>No reviews yet. Be the first!</div>";
                        }
                        ?>

                        <!-- Add Review Form -->
                        <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'student'): ?>
                            <hr>
                            <h6 class="fw-bold">Write a Review</h6>
                            <form method="POST">
                                <div class="row g-2 mb-2">
                                    <div class="col-md-4">
                                        <select name="rating" class="form-select">
                                            <option value="5">5 - Excellent</option>
                                            <option value="4">4 - Good</option>
                                            <option value="3">3 - Average</option>
                                            <option value="2">2 - Poor</option>
                                            <option value="1">1 - Bad</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="comment" class="form-control" placeholder="Describe your experience..." required>
                                    </div>
                                </div>
                                <button type="submit" name="submit_review" class="btn btn-dark btn-sm w-100">Post Review</button>
                            </form>
                            <?php
                            if (isset($_POST['submit_review'])) {
                                $u_id = $_SESSION['user_id'];
                                $rat = $_POST['rating'];
                                $com = $conn->real_escape_string($_POST['comment']);
                                $conn->query("INSERT INTO reviews (hostel_id, student_id, rating, comment) VALUES ($id, $u_id, $rat, '$com')");
                                echo "<script>window.location.href='details.php?id=$id';</script>";
                            }
                            ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sidebar (Sticky on Desktop, Normal on Mobile) -->
            <div class="col-lg-4 col-md-5 col-12">
                <div class="sticky-md-top" style="top: 100px; z-index: 1;">
                    
                    <!-- CONTACT & BOOKING CARD -->
                    <div class="card shadow border-0 rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Interested?</h5>
                            
                            <!-- WhatsApp Button -->
                            <?php 
                            $wa_num = preg_replace('/[^0-9]/', '', $hostel['contact_number']);
                            if(substr($wa_num, 0, 1) == '0') $wa_num = '92' . substr($wa_num, 1);
                            ?>
                            <a href="https://wa.me/<?php echo $wa_num; ?>" target="_blank" class="btn btn-success w-100 fw-bold mb-2 py-2">
                                <i class="bi bi-whatsapp"></i> Chat on WhatsApp
                            </a>
                            
                            <!-- Direct Call -->
                            <a href="tel:<?php echo $hostel['contact_number']; ?>" class="btn btn-outline-dark w-100 fw-bold mb-3">
                                <i class="bi bi-telephone"></i> Call Owner
                            </a>

                            <hr>

                            <!-- Booking Form -->
                            <h6 class="fw-bold text-primary">Book via HostelHub</h6>
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'student'): ?>
                                <form method="POST" action="process_booking.php">
                                    <input type="hidden" name="hostel_id" value="<?php echo $hostel['id']; ?>">
                                    <textarea name="message" class="form-control mb-2" rows="2" placeholder="Hi, is a room available?"></textarea>
                                    <button type="submit" name="book_now" class="btn btn-primary w-100 fw-bold">Send Request</button>
                                </form>
                            <?php elseif(isset($_SESSION['user_id']) && $_SESSION['role'] == 'owner'): ?>
                                <div class="alert alert-warning small mb-0">Owners cannot book hostels.</div>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary w-100">Login to Book</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- MAP CARD -->
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-0 pt-3 px-3">
                            <h6 class="fw-bold mb-0"><i class="bi bi-map"></i> Location</h6>
                        </div>
                        <div class="card-body p-2">
                            <div id="map"></div>
                            <p class="text-center text-muted small mt-2 mb-0">Exact location provided by owner.</p>
                        </div>
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
                        <li><a href="index.php" class="text-decoration-none text-secondary">Home</a></li>
                        <li><a href="login.php" class="text-decoration-none text-secondary">Login</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="text-warning">Contact Us</h5>
                    <p class="small text-secondary">
                        D-Ground, Faisalabad <br> +92 300 1234567
                    </p>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="text-center small text-secondary">
                &copy; <?php echo date('Y'); ?> HostelHub Faisalabad.
            </div>
        </div>
    </footer>
    <!-- FOOTER END -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- MAP SCRIPT -->
    <script>
        function initMap() {
            var location = { 
                lat: <?php echo $hostel['latitude'] ? $hostel['latitude'] : '31.4187'; ?>, 
                lng: <?php echo $hostel['longitude'] ? $hostel['longitude'] : '73.0791'; ?> 
            };

            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: location
            });

            new google.maps.Marker({
                position: location,
                map: map,
                title: '<?php echo htmlspecialchars($hostel['name']); ?>'
            });
        }
    </script>
    <!-- REPLACE 'YOUR_API_KEY_HERE' WITH YOUR ACTUAL KEY -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY_HERE&callback=initMap"></script>

    <!-- ANIMATION SCRIPT -->
    <script>
        window.addEventListener("load", function () {
            var loader = document.getElementById("preloader");
            loader.style.opacity = "0"; 
            setTimeout(function(){ loader.style.display = "none"; }, 500);
        });
    </script>
</body>
</html>