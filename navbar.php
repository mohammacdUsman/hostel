<?php
// Get current page name to highlight the active link
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-glass">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <i class="bi bi-building-fill-check text-warning fs-4"></i> 
            <span>HostelHub <span class="text-warning small">PK</span></span>
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                
                <!-- GLOBAL LINK: Home -->
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-house-door"></i> Home
                    </a>
                </li>

                <!-- LOGGED IN USER LINKS -->
                <?php if(isset($_SESSION['user_id'])): ?>

                    <!-- 🎓 STUDENT LINKS -->
                    <?php if($_SESSION['role'] == 'student'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'my_wishlist.php') ? 'active' : ''; ?>" href="my_wishlist.php">
                                <i class="bi bi-heart"></i> Wishlist
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'my_bookings.php') ? 'active' : ''; ?>" href="my_bookings.php">
                                <i class="bi bi-calendar-check"></i> My Bookings
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- 💼 OWNER LINKS -->
                    <?php if($_SESSION['role'] == 'owner'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'add_hostel.php') ? 'active' : ''; ?>" href="add_hostel.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'owner_bookings.php') ? 'active' : ''; ?>" href="owner_bookings.php">
                                <i class="bi bi-bell"></i> Requests
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- 🛡️ ADMIN LINKS -->
                    <?php if($_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link text-danger fw-bold <?php echo ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>" href="admin_dashboard.php">
                                <i class="bi bi-shield-lock-fill"></i> Admin Panel
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- USER DROPDOWN (AVATAR) -->
                    <li class="nav-item dropdown ms-2">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 profile-btn" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="profile-icon">
                                <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                            </div>
                            <span class="d-none d-lg-block"><?php echo explode(' ', $_SESSION['name'])[0]; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 animate-slide">
                            <li class="px-3 py-2 text-muted small border-bottom mb-2">
                                Signed in as <br><strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>
                            </li>
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-gear me-2"></i> Edit Profile</a></li>
                            <?php if($_SESSION['role'] == 'owner'): ?>
                                <li><a class="dropdown-item" href="add_hostel.php"><i class="bi bi-building me-2"></i> My Hostels</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                        </ul>
                    </li>

                <?php else: ?>
                    <!-- 👤 GUEST LINKS -->
                    <li class="nav-item ms-2">
                        <a class="nav-link fw-bold" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm" href="register.php">
                            Join Now
                        </a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>
<!-- NO INTERNET OVERLAY (Hidden by default) -->
<div id="offline-screen">
    <i class="bi bi-wifi-off wifi-icon"></i>
    <h2 class="fw-bold">No Internet Connection</h2>
    <p class="text-secondary">Please check your network settings.<br>We'll reconnect automatically when you're back online.</p>
    <button class="btn btn-outline-light mt-3 rounded-pill" onclick="window.location.reload()">Try Again</button>
</div>

<!-- OFFLINE DETECTION SCRIPT -->
<script>
    function updateOnlineStatus() {
        var screen = document.getElementById("offline-screen");
        if (navigator.onLine) {
            screen.style.display = "none"; // Hide if online
        } else {
            screen.style.display = "flex"; // Show if offline
        }
    }

    window.addEventListener('online',  updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    
    // Check on load
    window.addEventListener('load', updateOnlineStatus);
</script>