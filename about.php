<?php 
include 'db.php'; 
include 'header_sidebar.php'; 
?>

<!-- HERO BANNER -->
<div style="background: url('https://images.unsplash.com/photo-1522771753014-df2d992b0ed1?q=80&w=1920') center/cover; height: 50vh; position: relative; margin-top: -80px; display: flex; align-items: center; justify-content: center;">
    <div style="position: absolute; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6);"></div>
    <div class="container text-center position-relative text-white">
        <h1 style="font-family: 'Cinzel'; font-size: 3.5rem; text-shadow: 0 4px 10px rgba(0,0,0,0.5);">About <span style="color: var(--gold);">Us</span></h1>
        <p class="lead opacity-75">Redefining Student Living Standards in Pakistan</p>
    </div>
</div>

<div class="content-wrapper container my-5">
    
    <!-- STORY SECTION -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <img src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?q=80&w=800" class="img-fluid rounded shadow-lg border border-light" style="border-width: 5px !important;">
        </div>
        <div class="col-md-6 mt-4 mt-md-0 ps-md-5">
            <h5 class="text-gold fw-bold text-uppercase ls-2" style="color: var(--gold);">Our Mission</h5>
            <h2 class="fw-bold mb-4" style="font-family: 'Cinzel';">Your Home Away From Home</h2>
            <p class="text-muted" style="line-height: 1.8;">
                Finding a safe, affordable, and comfortable hostel shouldn't be a struggle. <strong>HostelHub</strong> was built to bridge the gap between students and hostel owners.
            </p>
            <p class="text-muted" style="line-height: 1.8;">
                We provide a transparent platform where owners can list their facilities with Verified IDs, and students can compare, review, and book rooms seamlessly. We believe in quality, security, and community.
            </p>
            <div class="mt-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                    <span>Verified Owners & Secure Locations</span>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                    <span>Transparent Pricing & No Hidden Fees</span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                    <span>24/7 Digital Complaint Support</span>
                </div>
            </div>
        </div>
    </div>

    <!-- STATS COUNTER -->
    <div class="row g-4 mb-5 text-center">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h1 class="fw-bold text-gold" style="color: var(--gold);">500+</h1>
                <p class="small text-muted fw-bold">HAPPY STUDENTS</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h1 class="fw-bold text-gold" style="color: var(--gold);">120+</h1>
                <p class="small text-muted fw-bold">HOSTELS LISTED</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h1 class="fw-bold text-gold" style="color: var(--gold);">50+</h1>
                <p class="small text-muted fw-bold">AREAS COVERED</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h1 class="fw-bold text-gold" style="color: var(--gold);">24/7</h1>
                <p class="small text-muted fw-bold">SUPPORT ACTIVE</p>
            </div>
        </div>
    </div>

    <!-- TEAM / CTA -->
    <div class="card bg-dark text-white p-5 text-center shadow-lg" style="border-radius: 20px; background: linear-gradient(45deg, #1a1a1a, #333);">
        <h2 style="font-family: 'Cinzel';">Are you a Hostel Owner?</h2>
        <p class="mb-4 opacity-75">Join the fastest growing network in Faisalabad and fill your rooms today.</p>
        <div>
            <a href="register.php" class="btn btn-gold px-5 py-3 rounded-pill fw-bold">Register Your Hostel</a>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php'; ?>
</body>
</html>