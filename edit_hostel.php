<?php
include 'db.php';
include 'header_sidebar.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    echo "<script>window.location='login.php';</script>"; exit();
}

$owner_id = $_SESSION['user_id'];
$hostel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 2. Fetch Hostel Data & Verify Ownership
$sql = "SELECT * FROM hostels WHERE id = $hostel_id AND owner_id = $owner_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<div class='content-wrapper container mt-5'><div class='alert alert-danger'>
            <h3>Error!</h3>
            <p>Hostel not found or you do not have permission to edit this.</p>
            <a href='owner_bookings.php' class='btn btn-dark'>Back to Dashboard</a>
          </div></div>";
    exit();
}

$hostel = $result->fetch_assoc();

// 3. Handle Image Deletion
if (isset($_GET['delete_img'])) {
    $img_path = $_GET['delete_img'];
    // Remove from DB
    $conn->query("DELETE FROM hostel_images WHERE hostel_id = $hostel_id AND image_path = '$img_path'");
    // Remove file from folder
    if(file_exists("uploads/$img_path")) { unlink("uploads/$img_path"); }
    
    echo "<script>window.location.href='edit_hostel.php?id=$hostel_id';</script>";
}

// 4. Handle Form Submission (Update Details)
if (isset($_POST['update_hostel'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $area = $_POST['area'];
    $price = $_POST['price'];
    $desc = $_POST['desc'];
    $facilities = isset($_POST['facilities']) ? implode(',', $_POST['facilities']) : "";

    $stmt = $conn->prepare("UPDATE hostels SET name=?, category=?, area=?, price=?, description=?, facilities=? WHERE id=?");
    $stmt->bind_param("sssdssi", $name, $category, $area, $price, $desc, $facilities, $hostel_id);
    
    if ($stmt->execute()) {
        
        // Handle NEW Images
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $key => $val) {
                $img_name = time() . "_" . basename($_FILES['images']['name'][$key]);
                $tmp_name = $_FILES['images']['tmp_name'][$key];
                
                if(move_uploaded_file($tmp_name, "uploads/" . $img_name)) {
                    $conn->query("INSERT INTO hostel_images (hostel_id, image_path) VALUES ($hostel_id, '$img_name')");
                    // Update main thumbnail if empty
                    $conn->query("UPDATE hostels SET image='$img_name' WHERE id=$hostel_id AND (image IS NULL OR image = '')");
                }
            }
        }
        
        echo "<script>alert('Hostel Updated Successfully!'); window.location='edit_hostel.php?id=$hostel_id';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// 5. Fetch Current Images
$img_res = $conn->query("SELECT image_path FROM hostel_images WHERE hostel_id = $hostel_id");
?>

<div class="content-wrapper">
    <div class="container" style="max-width: 900px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0" style="font-family: 'Cinzel';">Edit Hostel Details</h2>
            <a href="owner_bookings.php" class="btn btn-outline-dark">Back to Dashboard</a>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="card p-5 shadow-sm border-0">
            
            <!-- Basic Info -->
            <div class="mb-3">
                <label class="fw-bold">Hostel Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($hostel['name']); ?>" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Category</label>
                    <select name="category" class="form-select">
                        <option <?php echo ($hostel['category'] == 'Boys') ? 'selected' : ''; ?>>Boys</option>
                        <option <?php echo ($hostel['category'] == 'Girls') ? 'selected' : ''; ?>>Girls</option>
                        <option <?php echo ($hostel['category'] == 'Family') ? 'selected' : ''; ?>>Family</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Area / City</label>
                    <input type="text" name="area" class="form-control" value="<?php echo htmlspecialchars($hostel['area']); ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-bold">Price (PKR)</label>
                <input type="number" name="price" class="form-control" value="<?php echo $hostel['price']; ?>" required>
            </div>

            <!-- Facilities Checkboxes (Auto-Checked) -->
            <div class="mb-3 p-3 bg-light rounded">
                <label class="fw-bold mb-2">Facilities:</label><br>
                <div class="btn-group flex-wrap gap-2" role="group">
                    <?php 
                    $my_facs = explode(',', $hostel['facilities']); // Convert DB string to array
                    $all_facs = ['WiFi', 'Mess', 'Laundry', 'Generator', 'CCTV', 'Parking'];
                    
                    foreach($all_facs as $f) {
                        $checked = in_array($f, $my_facs) ? "checked" : "";
                        echo "<input type='checkbox' class='btn-check' name='facilities[]' value='$f' id='edit_$f' $checked>
                              <label class='btn btn-outline-dark rounded-pill px-3' for='edit_$f'>$f</label>";
                    }
                    ?>
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-bold">Description</label>
                <textarea name="desc" class="form-control" rows="4"><?php echo htmlspecialchars($hostel['description']); ?></textarea>
            </div>

            <!-- MANAGE IMAGES SECTION -->
            <div class="mb-4 border p-3 rounded">
                <label class="fw-bold text-primary mb-3"><i class="bi bi-images"></i> Manage Photos</label>
                
                <!-- Existing Images Gallery -->
                <div class="d-flex flex-wrap gap-3 mb-3">
                    <?php 
                    if($img_res->num_rows > 0) {
                        while($img = $img_res->fetch_assoc()) {
                            echo "<div class='position-relative'>
                                    <img src='uploads/{$img['image_path']}' class='rounded shadow-sm' style='width: 100px; height: 100px; object-fit: cover;'>
                                    <a href='edit_hostel.php?id=$hostel_id&delete_img={$img['image_path']}' class='btn btn-danger btn-sm position-absolute top-0 end-0' style='border-radius: 50%; padding: 0px 6px;' onclick=\"return confirm('Delete this photo?');\">×</a>
                                  </div>";
                        }
                    } else {
                        echo "<small class='text-muted'>No photos uploaded yet.</small>";
                    }
                    ?>
                </div>

                <!-- Add New Images -->
                <label class="small fw-bold">Add New Photos:</label>
                <input type="file" name="images[]" class="form-control mt-1" multiple accept="image/*">
            </div>

            <button type="submit" name="update_hostel" class="btn btn-gold w-100 py-3 fw-bold fs-5 shadow-sm">
                Update Hostel Details
            </button>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>