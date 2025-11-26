<?php 
include 'db.php'; 
include 'header_sidebar.php'; 

// Check if user is logged in as owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    echo "<script>window.location='login.php';</script>";
    exit();
}

$uid = $_SESSION['user_id'];

// FIX: Check if the owner has added a hostel first
$h_query = $conn->query("SELECT id FROM hostels WHERE owner_id=$uid");

if ($h_query->num_rows == 0) {
    echo "<div class='content-wrapper container mt-5'>
            <div class='alert alert-warning text-center p-5 shadow-sm'>
                <h3 class='fw-bold'><i class='bi bi-exclamation-circle'></i> No Hostel Found</h3>
                <p>You need to add a hostel before creating a mess menu.</p>
                <a href='add_hostel.php' class='btn btn-gold'>Add Hostel Now</a>
            </div>
          </div>";
    exit(); // Stop loading the rest of the page
}

$hostel_id = $h_query->fetch_assoc()['id'];

// Save Menu Logic
if(isset($_POST['save_mess'])) {
    // Clear old menu
    $conn->query("DELETE FROM mess_menu WHERE hostel_id=$hostel_id");

    $days = $_POST['day'];
    $bkf = $_POST['bkf'];
    $lun = $_POST['lun'];
    $din = $_POST['din'];

    // Insert new menu
    $stmt = $conn->prepare("INSERT INTO mess_menu (hostel_id, day_name, breakfast, lunch, dinner) VALUES (?, ?, ?, ?, ?)");
    for($i=0; $i<7; $i++){
        $stmt->bind_param("issss", $hostel_id, $days[$i], $bkf[$i], $lun[$i], $din[$i]);
        $stmt->execute();
    }
    echo "<script>alert('Menu Updated Successfully!');</script>";
}
?>

<div class="content-wrapper">
    <div class="card shadow-sm border-0 p-4">
        <h3 class="mb-4 fw-bold" style="font-family: 'Cinzel'; color: var(--gold);">Weekly Mess Menu</h3>
        <form method="POST">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light"><tr><th>Day</th><th>Breakfast</th><th>Lunch</th><th>Dinner</th></tr></thead>
                    <tbody>
                        <?php 
                        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                        foreach($days as $day) {
                            echo "<tr>
                                <td class='fw-bold'>$day <input type='hidden' name='day[]' value='$day'></td>
                                <td><input type='text' name='bkf[]' class='form-control' placeholder='Breakfast item'></td>
                                <td><input type='text' name='lun[]' class='form-control' placeholder='Lunch item'></td>
                                <td><input type='text' name='din[]' class='form-control' placeholder='Dinner item'></td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <button type="submit" name="save_mess" class="btn btn-gold w-100 mt-3">Save Menu</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>