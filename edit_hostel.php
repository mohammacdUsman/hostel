code
PHP
<?php
include 'config.php';

// 1. Security Checks
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    header("Location: login.php"); exit();
}

if (!isset($_GET['id'])) {
    header("Location: add_hostel.php"); exit();
}

$hostel_id = $_GET['id'];
$owner_id = $_SESSION['user_id'];

// 2. Verify Ownership
$stmt = $conn->prepare("SELECT * FROM hostels WHERE id = ? AND owner_id = ?");
$stmt->bind_param("ii", $hostel_id, $owner_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) die("Hostel not found or Permission Denied.");
$row = $result->fetch_assoc();

// 3. HANDLE IMAGE DELETION
if (isset($_GET['del_img'])) {
    $img_id = $_GET['del_img'];
    $q = $conn->query("SELECT image_path FROM hostel_images WHERE id = $img_id AND hostel_id = $hostel_id");
    if($q->num_rows > 0) {
        $img_data = $q->fetch_assoc();
        if(file_exists($img_data['image_path'])) { unlink($img_data['image_path']); }
        $conn->query("DELETE FROM hostel_images WHERE id = $img_id");
    }
    header("Location: edit_hostel.php?id=$hostel_id&msg=deleted");
    exit();
}

// 4. HANDLE FORM UPDATE
$msg = "";
if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $msg = "<div class='alert alert-warning'>Image deleted successfully.</div>";
}

if (isset($_POST['update_hostel'])) {
    $name = $_POST['name'];
    $area = $_POST['area'];
    $price = $_POST['price'];
    $desc = $_POST['desc'];
    $facilities = $_POST['facilities'];
    $contact = $_POST['contact'];
    $cat = $_POST['category'];
    $room = $_POST['room_type'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];

    // A. Update Text Data
    $sql = "UPDATE hostels SET name=?, area=?, price=?, description=?, facilities=?, contact_number=?, category=?, room_type=?, latitude=?, longitude=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsssssssi", $name, $area, $price, $desc, $facilities, $contact, $cat, $room, $lat, $lng, $hostel_id);
    
    if ($stmt->execute()) {
        // B. Handle NEW Images
        if (!empty($_FILES['images']['name'][0])) {
            if (!is_dir('uploads')) { mkdir('uploads'); }
            $countfiles = count($_FILES['images']['name']);
            $first_new_image = "";

            for($i = 0; $i < $countfiles; $i++) {
                $filename = $_FILES['images']['name'][$i];
                $target_file = "uploads/" . time() . "_" . $i . "_" . basename($filename);
                if(move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                    $img_stmt = $conn->prepare("INSERT INTO hostel_images (hostel_id, image_path) VALUES (?, ?)");
                    $img_stmt->bind_param("is", $hostel_id, $target_file);
                    $img_stmt->execute();
                    if($i == 0) $first_new_image = $target_file;
                }
            }
            if($row['image_url'] == 'pending' || empty($row['image_url'])) {
                $conn->query("UPDATE hostels SET image_url = '$first_new_image' WHERE id = $hostel_id");
            }
        }
        $msg = "<div class='alert alert-success'>Hostel Updated Successfully! <a href='add_hostel.php'>Back to Dashboard</a></div>";
        
        // Refresh Data
        $stmt = $conn->prepare("SELECT * FROM hostels WHERE id = ?");
        $stmt->bind_param("i", $hostel_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
    } else {
        $msg = "<div class='alert alert-danger'>Error updating record.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hostel - HostelHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
    <!-- PRELOADER -->
    <div id="preloader"><div class="spinner"></div></div>

    <?php include 'navbar.php'; ?>

    <div class="container mt-5 mb-5" style="max-width: 900px;">
        <div class="card shadow border-0">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="fw-bold text-primary">Edit Hostel Details</h3>
            </div>
            <div class="card-body p-4">
                <?php echo $msg; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    
                    <!-- Name & Area -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Hostel Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Area</label>
                            <select name="area" class="form-select">
                                <option value="<?php echo $row['area']; ?>" selected><?php echo $row['area']; ?> (Current)</option>
                                <option value="D-Ground">D-Ground</option>
                                <option value="Kohinoor City">Kohinoor City</option>
                                <option value="Peoples Colony">Peoples Colony</option>
                                <option value="Satyana Road">Satyana Road</option>
                                <option value="Madina Town">Madina Town</option>
                            </select>
                        </div>
                    </div>

                    <!-- Category & Room Type -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category" class="form-select">
                                <option value="Boys" <?php if($row['category']=='Boys') echo 'selected'; ?>>Boys</option>
                                <option value="Girls" <?php if($row['category']=='Girls') echo 'selected'; ?>>Girls</option>
                                <option value="Family" <?php if($row['category']=='Family') echo 'selected'; ?>>Family</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Room Type</label>
                            <select name="room_type" class="form-select">
                                <option value="Shared" <?php if($row['room_type']=='Shared') echo 'selected'; ?>>Shared</option>
                                <option value="Single" <?php if($row['room_type']=='Single') echo 'selected'; ?>>Single</option>
                                <option value="Dorm" <?php if($row['room_type']=='Dorm') echo 'selected'; ?>>Dorm</option>
                            </select>
                        </div>
                    </div>

                    <!-- Price & Desc -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Price (PKR)</label>
                        <input type="number" name="price" class="form-control" value="<?php echo $row['price']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="desc" class="form-control" rows="4"><?php echo htmlspecialchars($row['description']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Facilities</label>
                        <input type="text" name="facilities" class="form-control" value="<?php echo htmlspecialchars($row['facilities']); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Contact Number</label>
                        <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($row['contact_number']); ?>">
                    </div>

                    <!-- =========================================== -->
                    <!-- IMAGE MANAGEMENT SECTION -->
                    <!-- =========================================== -->
                    <div class="card bg-light p-3 mb-4 border-0">
                        <label class="form-label fw-bold">Manage Images</label>
                        
                        <!-- Responsive Grid for Images -->
                        <div class="row g-2 mb-3">
                            <?php
                            $gallery = $conn->query("SELECT * FROM hostel_images WHERE hostel_id = $hostel_id");
                            if($gallery->num_rows > 0) {
                                while($img = $gallery->fetch_assoc()) {
                                    echo '<div class="col-4 col-sm-3 col-md-2 position-relative">
                                            <div class="ratio ratio-1x1">
                                                <img src="'.$img['image_path'].'" class="rounded shadow-sm w-100 h-100" style="object-fit: cover;">
                                            </div>
                                            <a href="edit_hostel.php?id='.$hostel_id.'&del_img='.$img['id'].'" class="position-absolute top-0 end-0 badge bg-danger text-decoration-none m-1 shadow-sm" onclick="return confirm(\'Delete this image?\')">
                                                <i class="bi bi-x-lg"></i>
                                            </a>
                                          </div>';
                                }
                            } else {
                                echo "<div class='col-12'><p class='text-muted small'>No additional images uploaded.</p></div>";
                            }
                            ?>
                        </div>

                        <!-- 2. Upload New Images -->
                        <label class="form-label small fw-bold text-primary">Add More Photos (Multiple)</label>
                        <input type="file" name="images[]" class="form-control" multiple>
                        <small class="text-muted">Hold 'Ctrl' to select multiple.</small>
                    </div>
                    <!-- =========================================== -->

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <input type="text" name="lat" class="form-control" value="<?php echo $row['latitude']; ?>" placeholder="Latitude">
                        </div>
                        <div class="col-12 col-md-6">
                            <input type="text" name="lng" class="form-control" value="<?php echo $row['longitude']; ?>" placeholder="Longitude">
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="update_hostel" class="btn btn-primary fw-bold">Update Details</button>
                        <a href="add_hostel.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
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
                        D-Ground, Faisalabad <br> +92 300 1234567
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
    <!-- Animation Script -->
    <script>
        window.addEventListener("load", function () {
            var loader = document.getElementById("preloader");
            loader.style.opacity = "0"; 
            setTimeout(function(){ loader.style.display = "none"; }, 500);
        });
    </script>
</body>
</html>