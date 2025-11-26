<?php 
include 'db.php'; 
include 'header_sidebar.php'; 
?>

<style>
    /* Heart Button Styling */
    .wishlist-btn {
        position: absolute; top: 15px; right: 15px;
        background: white; width: 40px; height: 40px;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.15); cursor: pointer;
        transition: transform 0.2s ease; z-index: 10;
    }
    .wishlist-btn:hover { transform: scale(1.1); }
    .wishlist-btn i { font-size: 1.2rem; color: #ccc; transition: color 0.3s; }
    .wishlist-btn.active i { color: #dc3545; /* Red */ }

    /* Compare Checkbox Style */
    .compare-checkbox {
        position: absolute; top: 15px; left: 15px; z-index: 10;
        width: 20px; height: 20px; cursor: pointer; accent-color: var(--gold);
    }

    /* Floating Compare Button */
    #compareBtn {
        position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%);
        z-index: 9999; display: none; box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    }
</style>

<!-- HERO SECTION -->
<div style="background: url('https://images.unsplash.com/photo-1555854877-bab0e564b8d5?q=80&w=1920') center/cover; height: 70vh; position: relative; margin-top: -80px; display: flex; align-items: center; justify-content: center;">
    <div style="position: absolute; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5);"></div>
    
    <div class="container text-center position-relative text-white">
        <h1 style="font-family: 'Cinzel'; font-size: 3.5rem; text-shadow: 0 4px 10px rgba(0,0,0,0.5);">
            Hostel<span style="color: var(--gold);">Hub</span>
        </h1>
        <p class="lead mb-4 opacity-75">Experience Luxury & Secure Living</p>

        <!-- Search Box -->
        <form action="index.php" method="GET" class="d-inline-flex bg-white p-2 rounded-pill shadow-lg" style="max-width: 600px; width: 100%;">
            <input type="text" name="area" class="form-control border-0 rounded-pill ps-4" placeholder="Search by Area (e.g. D-Ground)...">
            <button class="btn btn-gold rounded-pill px-4 ms-2">Search</button>
        </form>
    </div>
</div>

<!-- LISTINGS -->
<div class="content-wrapper my-5">
    <div class="container">
        <h3 class="fw-bold mb-4" style="font-family: 'Cinzel'; border-left: 5px solid var(--gold); padding-left: 15px;">Featured Residences</h3>
        
        <div class="row g-4">
            <?php
            $area = isset($_GET['area']) ? $_GET['area'] : '';
            $sql = "SELECT * FROM hostels WHERE area LIKE '%$area%'";
            $result = $conn->query($sql);

            // Get User ID for wishlist check
            $uid = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    
                    // CHECK: Is this hostel already in wishlist?
                    $is_saved = false;
                    if(isset($_SESSION['role']) && $_SESSION['role'] == 'student') {
                        $hid = $row['id'];
                        $check = $conn->query("SELECT id FROM wishlist WHERE student_id = $uid AND hostel_id = $hid");
                        if($check->num_rows > 0) { $is_saved = true; }
                    }
                    $heart_class = $is_saved ? 'active' : '';
            ?>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                        
                        <!-- IMAGE CONTAINER -->
                        <div class="position-relative">
                            <img src="<?php echo !empty($row['image']) ? 'uploads/'.$row['image'] : 'https://via.placeholder.com/400x300'; ?>" 
                                 class="card-img-top" style="height: 220px; object-fit: cover;">
                            
                            <!-- ✅ 1. COMPARE CHECKBOX -->
                            <input type="checkbox" class="compare-checkbox" onclick="addToCompare(<?php echo $row['id']; ?>)" title="Select to Compare">

                            <!-- ♥️ HEART BUTTON -->
                            <button class="wishlist-btn <?php echo $heart_class; ?>" onclick="toggleWishlist(this, <?php echo $row['id']; ?>)">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-light text-dark border"><?php echo $row['category']; ?></span>
                                <?php if($row['is_verified']) echo '<i class="bi bi-patch-check-fill text-primary" title="Verified"></i>'; ?>
                            </div>
                            <h5 class="fw-bold"><?php echo $row['name']; ?></h5>
                            <p class="text-muted small"><i class="bi bi-geo-alt"></i> <?php echo $row['area']; ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <h5 class="mb-0 text-success fw-bold">Rs. <?php echo number_format($row['price']); ?></h5>
                                <a href="details.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-dark rounded-pill btn-sm px-4">Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo "<div class='col-12 text-center text-muted py-5'>No hostels found.</div>";
            }
            ?>
        </div>
    </div>
</div>

<!-- ✅ 2. FLOATING COMPARE BUTTON (Hidden by default) -->
<div id="compareBtn">
    <button onclick="goToCompare()" class="btn btn-dark shadow-lg rounded-pill px-4 py-2 border-gold fw-bold">
        <i class="bi bi-arrow-left-right text-warning me-2"></i> Compare (<span id="count">0</span>/2)
    </button>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // --- WISHLIST LOGIC ---
    function toggleWishlist(btn, hostelId) {
        $.post('toggle_wishlist.php', { hostel_id: hostelId }, function(response) {
            response = response.trim();
            
            if (response === 'login_required') {
                alert("Please login as a Student to save hostels.");
                window.location.href = 'login.php';
            } 
            else if (response === 'added') {
                $(btn).addClass('active'); // Turn Red
                $(btn).find('i').addClass('text-danger');
                $(btn).find('i').removeClass('text-secondary');
            } 
            else if (response === 'removed') {
                $(btn).removeClass('active'); // Turn Grey
                $(btn).find('i').removeClass('text-danger');
                $(btn).find('i').addClass('text-secondary');
            }
        });
    }

    // --- COMPARISON LOGIC ---
    let selected = [];
    function addToCompare(id) {
        if(selected.includes(id)) {
            selected = selected.filter(item => item !== id); // Uncheck logic
        } else {
            if(selected.length < 2) selected.push(id); // Add logic (Max 2)
            else {
                alert("You can only compare 2 hostels at a time.");
                // Uncheck the box immediately if limit reached
                event.target.checked = false; 
                return;
            }
        }
        
        // Update Button UI
        document.getElementById('count').innerText = selected.length;
        document.getElementById('compareBtn').style.display = selected.length > 0 ? 'block' : 'none';
    }

    function goToCompare() {
        if(selected.length === 2) {
            window.location = `compare.php?h1=${selected[0]}&h2=${selected[1]}`;
        } else {
            alert("Please select exactly 2 hostels to compare.");
        }
    }
</script>
<?php include 'footer.php'; ?>
</body>
</html>