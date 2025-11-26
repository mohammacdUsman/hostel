<?php
include 'db.php';
include 'header_sidebar.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    echo "<script>window.location='login.php';</script>"; exit();
}

if (isset($_POST['add_hostel'])) {
    $owner_id = $_SESSION['user_id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $area = $_POST['area']; // Using 'area' as confirmed by your DB
    $price = $_POST['price'];
    $desc = $_POST['desc'];
    
    // 1. Handle Facilities
    $facilities = isset($_POST['facilities']) ? implode(',', $_POST['facilities']) : "";

    // 2. Prepare SQL Statement
    $sql = "INSERT INTO hostels (owner_id, name, category, area, price, description, facilities) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // DIAGNOSTIC CHECK: Prints exact error if DB mismatch occurs
    if ($stmt === false) {
        die("<div class='container mt-5'><div class='alert alert-danger'>
             <h4>Database Error!</h4>
             <p>The system could not prepare the query.</p>
             <strong>Details:</strong> " . $conn->error . "
             </div></div>");
    }

    $stmt->bind_param("isssdss", $owner_id, $name, $category, $area, $price, $desc, $facilities);
    
    if ($stmt->execute()) {
        $hostel_id = $stmt->insert_id; // Get the ID of the new hostel

        // 3. Handle Multiple Images Upload
        if (!empty($_FILES['images']['name'][0])) {
            // Create uploads folder if not exists
            if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }

            foreach ($_FILES['images']['name'] as $key => $val) {
                $img_name = time() . "_" . basename($_FILES['images']['name'][$key]);
                $tmp_name = $_FILES['images']['tmp_name'][$key];
                
                if(move_uploaded_file($tmp_name, "uploads/" . $img_name)) {
                    // Save to hostel_images table
                    $conn->query("INSERT INTO hostel_images (hostel_id, image_path) VALUES ($hostel_id, '$img_name')");
                    
                    // Set the first image as the main thumbnail
                    if($key == 0) {
                        $conn->query("UPDATE hostels SET image='$img_name' WHERE id=$hostel_id");
                    }
                }
            }
        }

        echo "<script>alert('Hostel Added Successfully!'); window.location='owner_bookings.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}
?>

<div class="content-wrapper">
    <div class="container" style="max-width: 800px;">
        <h2 class="fw-bold mb-4" style="font-family: 'Cinzel';">Add New Hostel</h2>
        
        <form method="POST" enctype="multipart/form-data" class="card p-5 shadow-sm border-0">
            
            <!-- Basic Info -->
            <div class="mb-3">
                <label class="fw-bold">Hostel Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Sunshine Boys Hostel" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Category</label>
                    <select name="category" class="form-select">
                        <option>Boys</option><option>Girls</option><option>Family</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Area / City</label>
                    <input type="text" name="area" class="form-control" placeholder="e.g. D-Ground" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-bold">Price (PKR)</label>
                <input type="number" name="price" class="form-control" placeholder="e.g. 15000" required>
            </div>

            <!-- Facilities Checkboxes -->
            <div class="mb-3 p-3 bg-light rounded">
                <label class="fw-bold mb-2">Facilities Included:</label><br>
                <div class="btn-group flex-wrap gap-2" role="group">
                    <input type="checkbox" class="btn-check" name="facilities[]" value="WiFi" id="f1">
                    <label class="btn btn-outline-dark rounded-pill px-3" for="f1"><i class="bi bi-wifi"></i> WiFi</label>

                    <input type="checkbox" class="btn-check" name="facilities[]" value="Mess" id="f2">
                    <label class="btn btn-outline-dark rounded-pill px-3" for="f2"><i class="bi bi-egg-fried"></i> Mess</label>

                    <input type="checkbox" class="btn-check" name="facilities[]" value="Laundry" id="f3">
                    <label class="btn btn-outline-dark rounded-pill px-3" for="f3"><i class="bi bi-bucket"></i> Laundry</label>

                    <input type="checkbox" class="btn-check" name="facilities[]" value="Generator" id="f4">
                    <label class="btn btn-outline-dark rounded-pill px-3" for="f4"><i class="bi bi-lightning-charge"></i> UPS/Gen</label>
                    
                    <input type="checkbox" class="btn-check" name="facilities[]" value="CCTV" id="f5">
                    <label class="btn btn-outline-dark rounded-pill px-3" for="f5"><i class="bi bi-camera-video"></i> CCTV</label>
                    
                    <input type="checkbox" class="btn-check" name="facilities[]" value="Parking" id="f6">
                    <label class="btn btn-outline-dark rounded-pill px-3" for="f6"><i class="bi bi-car-front"></i> Parking</label>
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-bold">Description</label>
                <textarea name="desc" class="form-control" rows="4" placeholder="Tell students about your hostel..."></textarea>
            </div>
            <div class="mb-3">
    <label class="fw-bold">Google Maps Embed Code (Optional)</label>
    <textarea name="map_embed" class="form-control" rows="3" placeholder='Paste the <iframe> code from Google Maps here...'></textarea>
</div>
            <!-- Multiple Image Upload -->
            <div class="mb-4 border p-3 rounded">
                <label class="fw-bold text-primary"><i class="bi bi-images"></i> Upload Photos</label>
                <input type="file" name="images[]" class="form-control mt-2" multiple required accept="image/*">
                <small class="text-muted">Hold <strong>Ctrl</strong> (or Cmd) to select multiple images at once.</small>
            </div>

            <button type="submit" name="add_hostel" class="btn btn-gold w-100 py-3 fw-bold fs-5 shadow-sm">
                Save Hostel & Publish
            </button>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>