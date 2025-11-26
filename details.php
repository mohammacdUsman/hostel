<?php 
include 'db.php'; 
include 'header_sidebar.php'; 

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Fetch Hostel Details
$sql = "SELECT * FROM hostels WHERE id = $id";
$result = $conn->query($sql);

if($result->num_rows == 0) { 
    echo "<div class='content-wrapper container mt-5'><h3>Hostel not found</h3></div>"; 
    exit(); 
}
$hostel = $result->fetch_assoc();

// 2. Check Owner Logic
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$is_owner = ($current_user_id == $hostel['owner_id']);

// 3. Fetch Images
$img_sql = "SELECT image_path FROM hostel_images WHERE hostel_id = $id";
$img_res = $conn->query($img_sql);
$images = [];
while($img = $img_res->fetch_assoc()) { $images[] = $img['image_path']; }
if(empty($images) && !empty($hostel['image'])) { $images[] = $hostel['image']; }
if(empty($images)) { $images[] = 'default.jpg'; }

// 4. Fetch Mess
$mess_check = $conn->query("SHOW TABLES LIKE 'mess_menu'");
$mess_res = ($mess_check->num_rows > 0) ? $conn->query("SELECT * FROM mess_menu WHERE hostel_id = $id") : false;
?>

<style>
    /* Floating Announcement */
    .announcement-pill {
        position: fixed; bottom: 30px; right: 30px;
        background: linear-gradient(135deg, #D4AF37 0%, #F9D776 100%);
        color: #1a1a1a; padding: 12px 25px; border-radius: 50px;
        box-shadow: 0 10px 25px rgba(212, 175, 55, 0.4);
        font-family: 'Cinzel', serif; font-weight: 700; cursor: pointer; z-index: 9999;
        transition: all 0.3s ease; display: flex; align-items: center; gap: 10px;
        border: 2px solid #fff; animation: floatPulse 3s infinite;
    }
    .announcement-pill:hover { transform: translateY(-5px) scale(1.05); box-shadow: 0 15px 30px rgba(212, 175, 55, 0.6); }
    @keyframes floatPulse { 0% { transform: translateY(0); } 50% { transform: translateY(-6px); } 100% { transform: translateY(0); } }

    /* Back Button */
    .btn-back-premium {
        display: inline-flex; align-items: center; padding: 10px 25px;
        background: #ffffff; color: #1a1a1a; border-radius: 50px;
        text-decoration: none; font-family: 'Poppins', sans-serif; font-weight: 600;
        border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    .btn-back-premium:hover {
        background: #fff; color: #D4AF37; border-color: #D4AF37;
        box-shadow: 0 8px 25px rgba(212, 175, 55, 0.2); transform: translateX(-5px);
    }
    .btn-back-premium i { margin-right: 10px; transition: 0.3s; }
    .btn-back-premium:hover i { transform: translateX(-3px); }
</style>

<div class="content-wrapper">
    
    <!-- BACK BUTTON -->
    <div class="container mt-4 mb-3">
        <a href="index.php" class="btn-back-premium"><i class="bi bi-arrow-left"></i> Home</a>
    </div>

    <div class="row">
        <!-- LEFT COLUMN -->
        <div class="col-lg-8">
            
            <!-- IMAGE SLIDER -->
            <div id="hostelCarousel" class="carousel slide mb-4 shadow-sm rounded" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <?php foreach($images as $index => $img): ?>
                        <button type="button" data-bs-target="#hostelCarousel" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index == 0 ? 'active' : ''; ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-inner rounded">
                    <?php foreach($images as $index => $img): ?>
                        <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?>" data-bs-interval="3000">
                            <img src="uploads/<?php echo $img; ?>" class="d-block w-100" style="height: 450px; object-fit: cover;">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if(count($images) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#hostelCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                    <button class="carousel-control-next" type="button" data-bs-target="#hostelCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                <?php endif; ?>
            </div>

            <!-- MAIN INFO CARD -->
            <div class="card border-0 shadow-sm p-4 mb-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2 class="fw-bold mb-0" style="font-family: 'Cinzel';"><?php echo htmlspecialchars($hostel['name']); ?></h2>
                        
                        <p class="text-muted mt-1 mb-3">
                            <i class="bi bi-geo-alt-fill text-danger"></i> <?php echo htmlspecialchars($hostel['area']); ?>  
                            <span class="badge bg-dark"><?php echo htmlspecialchars($hostel['category']); ?></span>
                        </p>

                        <!-- ✅ NEW NAVIGATION BUTTON (Always Visible) -->
                        <?php 
                            // Generate Google Maps Link automatically
                            $map_query = urlencode($hostel['name'] . " " . $hostel['area'] . " Faisalabad");
                            $google_maps_link = "https://www.google.com/maps/search/?api=1&query=" . $map_query;
                        ?>
                        <a href="<?php echo $google_maps_link; ?>" target="_blank" class="btn btn-outline-primary rounded-pill px-4 fw-bold shadow-sm">
                            <i class="bi bi-map-fill"></i> Get Directions
                        </a>
                    </div>

                    <?php if($hostel['is_verified']): ?>
                        <span class="badge bg-primary p-2"><i class="bi bi-patch-check-fill"></i> Verified</span>
                    <?php endif; ?>
                </div>

                <!-- FACILITIES -->
                <div class="mb-3 mt-4">
                    <h6 class="fw-bold small text-muted">FACILITIES</h6>
                    <?php 
                    if (!empty($hostel['facilities'])) {
                        $facs = explode(',', $hostel['facilities']);
                        foreach($facs as $f) {
                            echo "<span class='badge bg-success bg-opacity-75 me-2 mb-1 p-2'><i class='bi bi-check-circle-fill'></i> ".trim($f)."</span>";
                        }
                    } else {
                        echo "<span class='text-muted small'>No facilities listed.</span>";
                    }
                    ?>
                </div>

                <hr>
                <h5 class="fw-bold">Description</h5>
                <p class="text-secondary" style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($hostel['description'])); ?></p>
            </div>

            <!-- VISUAL MAP SECTION (Shows only if Embed Code exists) -->
            <?php if(!empty($hostel['map_embed'])): ?>
            <div class="card border-0 shadow-sm p-3 mb-4">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-geo-alt-fill text-danger"></i> Exact Location</h5>
                <div class="ratio ratio-21x9 rounded overflow-hidden border shadow-sm">
                    <?php echo $hostel['map_embed']; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- REVIEWS SECTION -->
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h4 class="text-dark mb-3 fw-bold" style="font-family: 'Cinzel';">
                    <i class="bi bi-star-fill text-warning"></i> Student Reviews
                </h4>

                <?php
                $avg_sql = "SELECT AVG(rating) as avg_rate, COUNT(*) as total FROM reviews WHERE hostel_id = $id";
                $avg_res = $conn->query($avg_sql)->fetch_assoc();
                $average = round($avg_res['avg_rate'], 1);
                $total_rev = $avg_res['total'];
                ?>
                
                <div class="d-flex align-items-center mb-4 bg-light p-3 rounded">
                    <h1 class="fw-bold mb-0 me-3 display-4"><?php echo $average; ?></h1>
                    <div>
                        <div class="text-warning fs-5">
                            <?php for($i=1; $i<=5; $i++) echo ($i <= $average) ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>'; ?>
                        </div>
                        <small class="text-muted fw-bold"><?php echo $total_rev; ?> Verified Reviews</small>
                    </div>
                </div>

                <div class="review-list mb-4" style="max-height: 300px; overflow-y: auto;">
                    <?php
                    $rev_sql = "SELECT r.*, u.name, u.profile_pic FROM reviews r JOIN users u ON r.student_id = u.id WHERE r.hostel_id = $id ORDER BY r.created_at DESC";
                    $reviews = $conn->query($rev_sql);

                    if($reviews->num_rows > 0) {
                        while($row = $reviews->fetch_assoc()) {
                            $u_pic = !empty($row['profile_pic']) ? 'uploads/'.$row['profile_pic'] : 'https://via.placeholder.com/40';
                            echo "<div class='d-flex mb-3 border-bottom pb-3'>
                                    <img src='$u_pic' class='rounded-circle me-3 shadow-sm' style='width:45px; height:45px; object-fit:cover;'>
                                    <div>
                                        <h6 class='mb-0 fw-bold'>{$row['name']}</h6>
                                        <div class='text-warning small mb-1'>";
                                            for($i=1; $i<=5; $i++) echo ($i <= $row['rating']) ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                            echo       "</div>
                                        <p class='text-secondary small mb-0'>{$row['comment']}</p>
                                        <small class='text-muted' style='font-size:10px;'>".date('d M Y', strtotime($row['created_at']))."</small>
                                    </div>
                                  </div>";
                        }
                    } else {
                        echo "<p class='text-muted small text-center my-3'>No reviews yet. Be the first!</p>";
                    }
                    ?>
                </div>

                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'student'): ?>
                    <div class="bg-light p-3 rounded">
                        <h5 class="fw-bold small mb-2">Leave a Review</h5>
                        <form action="submit_review.php" method="POST">
                            <input type="hidden" name="hostel_id" value="<?php echo $id; ?>">
                            <div class="mb-2">
                                <select name="rating" class="form-select form-select-sm w-auto d-inline-block">
                                    <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                                    <option value="4">⭐⭐⭐⭐ Good</option>
                                    <option value="3">⭐⭐⭐ Average</option>
                                    <option value="2">⭐⭐ Poor</option>
                                    <option value="1">⭐ Terrible</option>
                                </select>
                            </div>
                            <textarea name="comment" class="form-control mb-2" placeholder="Share your experience..." rows="2" required></textarea>
                            <button type="submit" class="btn btn-sm btn-dark w-100">Post Review</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <!-- MESS MENU -->
            <?php if($mess_res && $mess_res->num_rows > 0): ?>
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h4 class="text-warning mb-3 fw-bold"><i class="bi bi-egg-fried"></i> Weekly Mess Menu</h4>
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light"><tr><th>Day</th><th>Breakfast</th><th>Lunch</th><th>Dinner</th></tr></thead>
                        <tbody>
                            <?php while($m = $mess_res->fetch_assoc()){
                                echo "<tr><td class='fw-bold'>{$m['day_name']}</td><td>{$m['breakfast']}</td><td>{$m['lunch']}</td><td>{$m['dinner']}</td></tr>";
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT COLUMN: Actions -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4 mb-4 sticky-top" style="top: 100px; z-index: 1;">
                <h3 class="text-success fw-bold text-center mb-3">Rs. <?php echo number_format($hostel['price']); ?> <small class="text-muted fs-6">/month</small></h3>
                
                <?php if ($is_owner): ?>
                    <div class="alert alert-info text-center small border-0 bg-info bg-opacity-10 text-info">
                        <i class="bi bi-info-circle-fill"></i> You own this hostel.
                    </div>
                    <a href="owner_bookings.php" class="btn btn-dark w-100 mb-2 py-2">Dashboard</a>
                    <a href="edit_hostel.php?id=<?php echo $id; ?>" class="btn btn-outline-dark w-100 py-2">Edit Details</a>
                <?php else: ?>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="chat.php?receiver_id=<?php echo $hostel['owner_id']; ?>" class="btn btn-outline-primary w-100 mb-2 py-2">
                            <i class="bi bi-chat-dots-fill"></i> Chat with Owner
                        </a>
                        <form action="process_booking.php" method="POST">
                            <input type="hidden" name="hostel_id" value="<?php echo $id; ?>">
                            <button type="submit" name="book_now" class="btn btn-gold w-100 fw-bold py-2">Book Now</button>
                        </form>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-gold w-100 fw-bold py-2">Login to Book</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if(!empty($hostel['announcement_text'])): ?>
    <div class="announcement-pill" data-bs-toggle="modal" data-bs-target="#announceModal">
        <i class="bi bi-megaphone-fill fs-5"></i> <span>Announcement from Owner</span>
    </div>
    <div class="modal fade" id="announceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="background: #fff8e1; border-left: 5px solid var(--gold) !important;">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-dark" style="font-family: 'Cinzel';">
                        <i class="bi bi-bell-fill text-warning"></i> Important Notice
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-dark fs-5" style="font-family: 'Poppins';">
                    <?php echo nl2br(htmlspecialchars($hostel['announcement_text'])); ?>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-sm btn-dark rounded-pill" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<?php include 'footer.php'; ?>
</body>
</html>