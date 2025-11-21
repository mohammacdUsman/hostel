<?php
include 'config.php';

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    header("Location: login.php"); 
    exit();
}

$msg = "";

if (isset($_POST['add_hostel'])) {
    $name = $_POST['name'];
    $area = $_POST['area'];
    $price = $_POST['price'];
    $desc = $_POST['desc'];
    $facilities = $_POST['facilities'];
    $contact = $_POST['contact'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $category = $_POST['category']; 
    $room_type = $_POST['room_type'];
    $owner_id = $_SESSION['user_id'];
    
    // 1. INSERT HOSTEL DATA
    $stmt = $conn->prepare("INSERT INTO hostels (owner_id, name, area, price, description, facilities, contact_number, latitude, longitude, category, room_type, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("issssssssss", $owner_id, $name, $area, $price, $desc, $facilities, $contact, $lat, $lng, $category, $room_type);
    
    if ($stmt->execute()) {
        $hostel_id = $stmt->insert_id; 
        $main_image = ""; 

        // 2. HANDLE MULTIPLE IMAGES
        if (!is_dir('uploads')) { mkdir('uploads'); }

        $countfiles = count($_FILES['images']['name']);

        for($i = 0; $i < $countfiles; $i++) {
            $filename = $_FILES['images']['name'][$i];
            $target_file = "uploads/" . time() . "_" . $i . "_" . basename($filename);
            
            if(move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                $img_stmt = $conn->prepare("INSERT INTO hostel_images (hostel_id, image_path) VALUES (?, ?)");
                $img_stmt->bind_param("is", $hostel_id, $target_file);
                $img_stmt->execute();

                if($i == 0) { $main_image = $target_file; }
            }
        }

        // 3. UPDATE MAIN IMAGE
        if($main_image != "") {
            $update = $conn->prepare("UPDATE hostels SET image_url = ? WHERE id = ?");
            $update->bind_param("si", $main_image, $hostel_id);
            $update->execute();
        }

        $msg = "<div class='alert alert-success'>Hostel & Images Uploaded Successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Database Error: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
    <!-- PRELOADER -->
    <div id="preloader"><div class="spinner"></div></div>

    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark">Owner Dashboard</h2>
            <!-- Mobile responsive button sizing -->
            <a href="owner_bookings.php" class="btn btn-warning fw-bold shadow-sm">
                <i class="bi bi-bell-fill"></i> <span class="d-none d-md-inline">Bookings</span>
            </a>
        </div>

        <!-- STATS SECTION (Responsive Grid) -->
        <div class="row mb-4">
            <?php
            $oid = $_SESSION['user_id'];
            $total_hostels = $conn->query("SELECT COUNT(*) as c FROM hostels WHERE owner_id=$oid")->fetch_assoc()['c'];
            $total_views = $conn->query("SELECT SUM(views) as v FROM hostels WHERE owner_id=$oid")->fetch_assoc()['v'];
            $pending_req = $conn->query("SELECT COUNT(*) as c FROM bookings b JOIN hostels h ON b.hostel_id=h.id WHERE h.owner_id=$oid AND b.status='pending'")->fetch_assoc()['c'];
            ?>
            <!-- col-12 (Mobile), col-md-4 (Desktop) -->
            <div class="col-12 col-md-4 mb-3">
                <div class="stat-card h-100">
                    <h3><?php echo $total_hostels; ?></h3><p>Listings</p>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="stat-card h-100" style="border-left-color: #10b981;">
                    <h3><?php echo $total_views ? $total_views : 0; ?></h3><p>Total Views</p>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="stat-card h-100" style="border-left-color: #f59e0b;">
                    <h3><?php echo $pending_req; ?></h3><p>Pending Requests</p>
                </div>
            </div>
        </div>

        <?php echo $msg; ?>

        <!-- ADD HOSTEL FORM -->
        <div class="card p-4 shadow-sm border-0 mb-5">
            <h4 class="mb-4 text-primary fw-bold"><i class="bi bi-plus-circle-fill"></i> Add New Hostel</h4>
            <form method="POST" enctype="multipart/form-data">
                
                <!-- Added 'g-3' for better spacing on mobile stack -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="fw-bold form-label">Hostel Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold form-label">Area</label>
                        <select name="area" class="form-select">
                            <option>D-Ground</option>
                            <option>Kohinoor City</option>
                            <option>Peoples Colony</option>
                            <option>Satyana Road</option>
                            <option>Madina Town</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="fw-bold form-label">Type</label>
                        <select name="category" class="form-select">
                            <option>Boys</option>
                            <option>Girls</option>
                            <option>Family</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold form-label">Room</label>
                        <select name="room_type" class="form-select">
                            <option>Shared</option>
                            <option>Single</option>
                            <option>Dorm</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="fw-bold form-label">Price (PKR)</label>
                        <input type="number" name="price" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label class="fw-bold form-label">Description</label>
                        <textarea name="desc" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="fw-bold form-label">Facilities</label>
                        <input type="text" name="facilities" class="form-control" placeholder="e.g. WiFi, AC, Mess">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="fw-bold form-label">Contact</label>
                        <input type="text" name="contact" class="form-control" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="fw-bold form-label">Upload Photos (Select Multiple)</label>
                        <input type="file" name="images[]" class="form-control" multiple required>
                        <small class="text-muted d-block mt-1">Hold 'Ctrl' to select multiple images.</small>
                    </div>

                    <div class="col-md-6">
                        <input type="text" name="lat" placeholder="Latitude" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="lng" placeholder="Longitude" class="form-control">
                    </div>
                </div>
                
                <button type="submit" name="add_hostel" class="btn btn-primary mt-4 w-100 fw-bold py-2">Publish Listing</button>
            </form>
        </div>
        
        <!-- MANAGED HOSTELS TABLE (RESPONSIVE WRAPPER ADDED) -->
        <h3 class="fw-bold mb-3">My Managed Hostels</h3>
        
        <!-- This div makes the table scroll horizontally on mobile -->
        <div class="table-responsive rounded shadow-sm">
            <table class="table table-bordered table-hover bg-white mb-0 align-middle">
                <thead class="table-dark text-nowrap">
                    <tr>
                        <th style="width: 80px;">Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th style="width: 180px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = $conn->query("SELECT * FROM hostels WHERE owner_id = '$oid'");
                    if ($res->num_rows > 0) {
                        while($row = $res->fetch_assoc()) {
                            echo "<tr>
                                <td class='text-center'><img src='".$row['image_url']."' class='rounded' width='50' height='50' style='object-fit:cover;'></td>
                                <td class='fw-bold'>".$row['name']."</td>
                                <td>Rs. ".number_format($row['price'])."</td>
                                <td>
                                    <div class='d-flex gap-2'>
                                        <a href='edit_hostel.php?id=".$row['id']."' class='btn btn-warning btn-sm text-white'><i class='bi bi-pencil-square'></i> Edit</a>
                                        <a href='delete_hostel.php?id=".$row['id']."' class='btn btn-danger btn-sm' onclick='return confirm(\"Delete this hostel?\")'><i class='bi bi-trash'></i></a>
                                    </div>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center py-4 text-muted'>No listings found. Add one above!</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Animation Loader -->
    <script>
        window.addEventListener("load", function () {
            var loader = document.getElementById("preloader");
            if(loader) {
                loader.style.opacity = "0"; 
                setTimeout(function(){ loader.style.display = "none"; }, 500);
            }
        });
    </script>
</body>
</html>