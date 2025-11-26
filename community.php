<?php
include 'db.php';
include 'header_sidebar.php';

if (!isset($_SESSION['user_id'])) { echo "<script>window.location='login.php';</script>"; exit(); }

// Define Areas (You can also fetch distinct areas from DB)
$areas = ["D-Ground", "Kohinoor", "Peoples Colony", "Madina Town", "Satyana Road", "Canal Road"];
?>

<div class="content-wrapper">
    <div class="container mt-4">
        <h2 class="fw-bold text-center mb-2" style="font-family: 'Cinzel';">Community Hub</h2>
        <p class="text-center text-muted mb-5">Join a discussion room to ask about specific areas.</p>

        <div class="row g-4">
            <?php foreach($areas as $area): ?>
            <div class="col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100 text-center p-4 hover-scale">
                    <div class="mb-3">
                        <i class="bi bi-buildings-fill fs-1 text-gold" style="color: var(--gold);"></i>
                    </div>
                    <h5 class="fw-bold"><?php echo $area; ?></h5>
                    <p class="text-muted small">Chat with students & owners in <?php echo $area; ?></p>
                    <a href="area_chat.php?area=<?php echo urlencode($area); ?>" class="btn btn-outline-dark rounded-pill btn-sm stretched-link">Join Room</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php'; ?>
</body>
</html>