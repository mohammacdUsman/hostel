<?php 
include 'db.php'; 
include 'header_sidebar.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    echo "<script>window.location='login.php';</script>"; exit();
}

$uid = $_SESSION['user_id'];

// Handle Post Logic
if(isset($_POST['post_announce'])) {
    $text = htmlspecialchars($_POST['text']);
    $conn->query("UPDATE hostels SET announcement_text='$text' WHERE owner_id=$uid");
    $msg = "Announcement Published Successfully!";
}

// Fetch Current Announcement to show to Owner
$res = $conn->query("SELECT announcement_text FROM hostels WHERE owner_id=$uid");
$current_text = ($res->num_rows > 0) ? $res->fetch_assoc()['announcement_text'] : "";
?>

<div class="content-wrapper">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card p-5 mt-4 border-0 shadow-sm">
                
                <div class="d-flex align-items-center mb-4">
                    <i class="bi bi-megaphone-fill fs-2 me-3" style="color: var(--gold);"></i>
                    <h2 class="fw-bold mb-0" style="font-family: 'Cinzel';">Hostel Announcement</h2>
                </div>
                
                <!-- Success Message -->
                <?php if(isset($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>
                
                <!-- SHOW CURRENT ACTIVE ANNOUNCEMENT -->
                <?php if(!empty($current_text)): ?>
                    <div class="alert alert-warning border-warning">
                        <strong><i class="bi bi-eye"></i> Currently Live on Website:</strong><br>
                        "<?php echo $current_text; ?>"
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary">
                        <i class="bi bi-info-circle"></i> No active announcement right now.
                    </div>
                <?php endif; ?>
                
                <hr class="my-4">

                <form method="POST">
                    <label class="fw-bold mb-2">Write New Message:</label>
                    <div class="form-group mb-4">
                        <textarea name="text" class="form-control p-3" rows="4" 
                                  style="background: #f8f9fa; border: 2px solid #eee; border-radius: 10px;"
                                  placeholder="e.g. 20% Discount for new students..."></textarea>
                    </div>
                    <button type="submit" name="post_announce" class="btn btn-gold w-100 py-3 fw-bold">
                        Publish to Website
                    </button>
                    <p class="text-muted small text-center mt-2">
                        Note: This will replace any existing announcement.
                    </p>
                </form>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>