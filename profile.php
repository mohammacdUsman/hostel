<?php
include 'db.php';
include 'header_sidebar.php';

// Security: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location='login.php';</script>"; exit();
}

$uid = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

// --- HANDLE FORM SUBMISSION ---
if (isset($_POST['update_profile'])) {
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $about = htmlspecialchars($_POST['about']);
    
    // Update Text Info
    $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, about=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $phone, $about, $uid);
    
    if ($stmt->execute()) {
        $_SESSION['name'] = $name; // Update session name immediately
        $msg = "Profile updated successfully!";
        $msg_type = "success";
        
        // 1. Handle Profile Picture Upload
        if (!empty($_FILES['profile_pic']['name'])) {
            $img_name = time() . "_" . basename($_FILES['profile_pic']['name']);
            $target = "uploads/" . $img_name;
            
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
                $conn->query("UPDATE users SET profile_pic='$img_name' WHERE id=$uid");
            }
        }

        // 2. Handle CNIC Upload (Identity Verification)
        if (!empty($_FILES['cnic_front']['name'])) {
            $cnic_img = time() . "_cnic_" . basename($_FILES['cnic_front']['name']);
            $target_cnic = "uploads/" . $cnic_img;

            if (move_uploaded_file($_FILES['cnic_front']['tmp_name'], $target_cnic)) {
                $conn->query("UPDATE users SET cnic_front='$cnic_img' WHERE id=$uid");
            }
        }

    } else {
        $msg = "Error updating profile.";
        $msg_type = "danger";
    }
}

// --- FETCH USER DATA ---
$user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
$pic = !empty($user['profile_pic']) ? "uploads/".$user['profile_pic'] : "https://via.placeholder.com/150";
?>

<div class="content-wrapper">
    <div class="container" style="max-width: 900px; min-height: 80vh;">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0" style="font-family: 'Cinzel';">My Profile</h2>
            <?php if($user['role'] == 'owner'): ?>
                <a href="owner_bookings.php" class="btn btn-outline-dark btn-sm">Back to Dashboard</a>
            <?php else: ?>
                <a href="index.php" class="btn btn-outline-dark btn-sm">Back to Home</a>
            <?php endif; ?>
        </div>

        <?php if($msg): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $msg; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                
                <!-- LEFT COL: Image & Role -->
                <div class="col-md-4 text-center mb-4">
                    <div class="card border-0 shadow-sm p-4 h-100 d-flex flex-column align-items-center justify-content-center">
                        
                        <div class="position-relative">
                            <img src="<?php echo $pic; ?>" class="rounded-circle shadow-sm" 
                                 style="width: 150px; height: 150px; object-fit: cover; border: 4px solid var(--gold);">
                            
                            <!-- Hidden File Input Trigger -->
                            <label for="picInput" class="position-absolute bottom-0 end-0 bg-dark text-white rounded-circle p-2 shadow cursor-pointer" 
                                   style="cursor: pointer;" title="Change Photo">
                                <i class="bi bi-camera-fill"></i>
                            </label>
                            <input type="file" name="profile_pic" id="picInput" class="d-none" accept="image/*" onchange="previewImage(this)">
                        </div>

                        <h4 class="mt-3 fw-bold"><?php echo $user['name']; ?></h4>
                        <span class="badge bg-gold text-white px-3 py-2 rounded-pill" style="background: var(--gold);">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                        
                        <p class="text-muted small mt-3">Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>

                <!-- RIGHT COL: Edit Details -->
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold mb-3 text-primary">Personal Information</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo $user['name']; ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Email (Cannot be changed)</label>
                                <input type="text" class="form-control bg-light" value="<?php echo $user['email']; ?>" readonly>
                            </div>

                            <div class="col-12">
                                <label class="form-label small text-muted">Phone Number</label>
                                <input type="text" name="phone" class="form-control" placeholder="+92 300 1234567" value="<?php echo $user['phone']; ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label small text-muted">About Me / Bio</label>
                                <textarea name="about" class="form-control" rows="4" placeholder="Tell us a little about yourself..."><?php echo $user['about']; ?></textarea>
                            </div>
                        </div>

                        <!-- Feature 5: ID Verification -->
                        <hr class="my-4">
                        <h5 class="fw-bold text-primary">Identity Verification (Get Blue Tick)</h5>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="small text-muted">Upload CNIC Front Photo</label>
                                <input type="file" name="cnic_front" class="form-control">
                                <?php if(isset($user['is_id_verified']) && $user['is_id_verified']): ?>
                                    <span class="text-success small mt-2 d-block"><i class="bi bi-check-circle-fill"></i> Verified ID</span>
                                <?php else: ?>
                                    <span class="text-warning small mt-2 d-block"><i class="bi bi-hourglass"></i> Not Verified / Pending</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <div class="text-end">
                            <button type="submit" name="update_profile" class="btn btn-gold px-5 py-2 fw-bold shadow-sm">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
// Simple Image Preview Script
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            document.querySelector('img.rounded-circle').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>