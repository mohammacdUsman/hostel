<?php 
include 'db.php'; 
include 'header_sidebar.php'; 

// Security Check
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') { 
    echo "<script>window.location='login.php';</script>"; 
    exit();
}

// Handle "Remove" Action
if(isset($_GET['remove_id'])) {
    $rem_id = intval($_GET['remove_id']);
    $uid = $_SESSION['user_id'];
    $conn->query("DELETE FROM wishlist WHERE hostel_id = $rem_id AND student_id = $uid");
    echo "<script>window.location='my_wishlist.php';</script>";
    exit();
}
?>

<!-- CONTENT WRAPPER (Fixes the layout issue) -->
<div class="content-wrapper">
    <div class="container" style="min-height: 80vh;">
        
        <div class="d-flex align-items-center mb-5 mt-3">
            <i class="bi bi-heart-fill fs-1 me-3 text-danger"></i>
            <h2 class="fw-bold mb-0" style="font-family: 'Cinzel';">My Saved Hostels</h2>
        </div>

        <div class="row g-4">
            <?php
            $uid = $_SESSION['user_id'];
            
            // JOIN query to get details of saved hostels
            $sql = "SELECT h.* FROM wishlist w JOIN hostels h ON w.hostel_id = h.id WHERE w.student_id = $uid";
            $result = $conn->query($sql);
            
            // Error Handling: If query fails, show why
            if (!$result) {
                die("<div class='alert alert-danger'>Database Error: " . $conn->error . "</div>");
            }
            
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    ?>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden; transition: 0.3s;">
                            
                            <!-- IMAGE -->
                            <div class="position-relative">
                                <img src="<?php echo !empty($row['image']) ? 'uploads/'.$row['image'] : 'https://via.placeholder.com/400x300'; ?>" 
                                     class="card-img-top" style="height: 220px; object-fit: cover;">
                                
                                <!-- Price Badge -->
                                <span class="position-absolute top-0 start-0 m-3 badge bg-dark shadow-sm" style="backdrop-filter: blur(5px); opacity: 0.9;">
                                    PKR <?php echo number_format($row['price']); ?>
                                </span>

                                <!-- Remove Button -->
                                <a href="my_wishlist.php?remove_id=<?php echo $row['id']; ?>" 
                                   class="btn btn-light position-absolute top-0 end-0 m-3 text-danger shadow-sm rounded-circle d-flex align-items-center justify-content-center" 
                                   style="width: 40px; height: 40px;"
                                   onclick="return confirm('Remove from wishlist?');">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            </div>
                            
                            <!-- DETAILS -->
                            <div class="card-body">
                                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($row['name']); ?></h5>
                                <p class="text-muted small mb-2"><i class="bi bi-geo-alt-fill text-warning"></i> <?php echo htmlspecialchars($row['area']); ?></p>
                                
                                <div class="d-grid mt-3">
                                    <a href="details.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-dark rounded-pill">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                // Empty State Design
                echo "<div class='col-12 text-center py-5'>
                        <div class='mb-3'><i class='bi bi-heartbreak text-muted' style='font-size: 80px;'></i></div>
                        <h4 class='text-secondary' style='font-family: Cinzel;'>Your wishlist is empty.</h4>
                        <p class='text-muted'>Save hostels here to view them later.</p>
                        <a href='index.php' class='btn btn-gold px-4 mt-2'>Browse Hostels</a>
                      </div>";
            }
            ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php'; ?>
</body>
</html>