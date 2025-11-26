<?php 
include 'db.php'; 
include 'header_sidebar.php'; 

$h1_id = isset($_GET['h1']) ? intval($_GET['h1']) : 0;
$h2_id = isset($_GET['h2']) ? intval($_GET['h2']) : 0;

// Fetch Data for Both Hostels
$sql = "SELECT h.*, 
        (SELECT AVG(rating) FROM reviews r WHERE r.hostel_id = h.id) as avg_rating,
        (SELECT COUNT(*) FROM reviews r WHERE r.hostel_id = h.id) as review_count
        FROM hostels h 
        WHERE id IN ($h1_id, $h2_id)";
$res = $conn->query($sql);

$hostels = [];
while($row = $res->fetch_assoc()) {
    $hostels[$row['id']] = $row;
}

// Ensure we have 2 hostels
if(count($hostels) < 2) {
    echo "<div class='content-wrapper container mt-5 text-center'><h3>Select 2 hostels to compare.</h3><a href='index.php' class='btn btn-dark'>Back</a></div>";
    exit();
}

$h1 = $hostels[$h1_id];
$h2 = $hostels[$h2_id];

// --- CALCULATE WINNERS ---
// 1. Price Winner (Cheaper is better)
$price_winner = ($h1['price'] < $h2['price']) ? $h1['id'] : $h2['id'];

// 2. Rating Winner (Higher is better)
$rate1 = round($h1['avg_rating'], 1);
$rate2 = round($h2['avg_rating'], 1);
$rating_winner = ($rate1 > $rate2) ? $h1['id'] : (($rate2 > $rate1) ? $h2['id'] : 'tie');

// 3. Facilities Winner (More is better)
$fac1 = count(array_filter(explode(',', $h1['facilities'])));
$fac2 = count(array_filter(explode(',', $h2['facilities'])));
$fac_winner = ($fac1 > $fac2) ? $h1['id'] : (($fac2 > $fac1) ? $h2['id'] : 'tie');
?>

<div class="content-wrapper container mt-5 mb-5">
    
    <div class="text-center mb-5">
        <h2 class="fw-bold" style="font-family: 'Cinzel';">Comparison Showdown</h2>
        <p class="text-muted">Detailed side-by-side analysis</p>
    </div>

    <!-- COMPARISON TABLE -->
    <div class="table-responsive shadow-lg rounded mb-5">
        <table class="table table-bordered text-center align-middle mb-0 bg-white">
            <thead class="bg-dark text-white">
                <tr>
                    <th style="width: 20%;">Feature</th>
                    <th style="width: 40%;"><?php echo $h1['name']; ?></th>
                    <th style="width: 40%;"><?php echo $h2['name']; ?></th>
                </tr>
            </thead>
            <tbody>
                <!-- Images -->
                <tr>
                    <td class="fw-bold bg-light">Preview</td>
                    <td><img src="uploads/<?php echo $h1['image']; ?>" class="rounded shadow-sm" style="width: 120px; height: 80px; object-fit: cover;"></td>
                    <td><img src="uploads/<?php echo $h2['image']; ?>" class="rounded shadow-sm" style="width: 120px; height: 80px; object-fit: cover;"></td>
                </tr>

                <!-- Price -->
                <tr>
                    <td class="fw-bold bg-light">Monthly Rent</td>
                    <td class="<?php echo ($h1['id'] == $price_winner) ? 'bg-success bg-opacity-10 fw-bold text-success' : ''; ?>">
                        Rs. <?php echo number_format($h1['price']); ?>
                        <?php if($h1['id'] == $price_winner) echo '<i class="bi bi-check-circle-fill ms-1"></i>'; ?>
                    </td>
                    <td class="<?php echo ($h2['id'] == $price_winner) ? 'bg-success bg-opacity-10 fw-bold text-success' : ''; ?>">
                        Rs. <?php echo number_format($h2['price']); ?>
                        <?php if($h2['id'] == $price_winner) echo '<i class="bi bi-check-circle-fill ms-1"></i>'; ?>
                    </td>
                </tr>

                <!-- Rating -->
                <tr>
                    <td class="fw-bold bg-light">Rating</td>
                    <td><span class="text-warning fw-bold">★ <?php echo $rate1; ?></span> <small class="text-muted">(<?php echo $h1['review_count']; ?> reviews)</small></td>
                    <td><span class="text-warning fw-bold">★ <?php echo $rate2; ?></span> <small class="text-muted">(<?php echo $h2['review_count']; ?> reviews)</small></td>
                </tr>

                <!-- Facilities -->
                <tr>
                    <td class="fw-bold bg-light">Facilities</td>
                    <td class="text-start px-4">
                        <?php foreach(explode(',', $h1['facilities']) as $f) echo "<span class='badge bg-secondary m-1'>$f</span>"; ?>
                    </td>
                    <td class="text-start px-4">
                        <?php foreach(explode(',', $h2['facilities']) as $f) echo "<span class='badge bg-secondary m-1'>$f</span>"; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- 🏆 FINAL VERDICT CARD -->
    <div class="card border-0 shadow-lg overflow-hidden" style="background: linear-gradient(135deg, #1a1a1a 0%, #333 100%); color: white;">
        <div class="card-body p-5 text-center">
            <h3 class="fw-bold mb-4" style="font-family: 'Cinzel'; color: var(--gold);"><i class="bi bi-trophy-fill"></i> Final Verdict</h3>
            
            <div class="row g-4">
                <!-- Budget Choice -->
                <div class="col-md-4">
                    <div class="p-3 rounded border border-secondary bg-white bg-opacity-10">
                        <h6 class="text-white-50 text-uppercase small ls-1">Best for Budget</h6>
                        <h5 class="fw-bold mt-2 mb-0 text-success">
                            <?php echo ($h1['id'] == $price_winner) ? $h1['name'] : $h2['name']; ?>
                        </h5>
                    </div>
                </div>

                <!-- Quality Choice -->
                <div class="col-md-4">
                    <div class="p-3 rounded border border-secondary bg-white bg-opacity-10">
                        <h6 class="text-white-50 text-uppercase small ls-1">Highest Rated</h6>
                        <h5 class="fw-bold mt-2 mb-0 text-warning">
                            <?php 
                                if($rating_winner === 'tie') echo "It's a Tie!";
                                else echo ($h1['id'] == $rating_winner) ? $h1['name'] : $h2['name']; 
                            ?>
                        </h5>
                    </div>
                </div>

                <!-- Feature Choice -->
                <div class="col-md-4">
                    <div class="p-3 rounded border border-secondary bg-white bg-opacity-10">
                        <h6 class="text-white-50 text-uppercase small ls-1">Most Facilities</h6>
                        <h5 class="fw-bold mt-2 mb-0 text-info">
                            <?php 
                                if($fac_winner === 'tie') echo "Both are Equal";
                                else echo ($h1['id'] == $fac_winner) ? $h1['name'] : $h2['name']; 
                            ?>
                        </h5>
                    </div>
                </div>
            </div>

            <div class="mt-5 d-flex justify-content-center gap-3">
                <a href="details.php?id=<?php echo $h1['id']; ?>" class="btn btn-outline-light rounded-pill px-4">View <?php echo $h1['name']; ?></a>
                <a href="details.php?id=<?php echo $h2['id']; ?>" class="btn btn-outline-light rounded-pill px-4">View <?php echo $h2['name']; ?></a>
            </div>
        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>