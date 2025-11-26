<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); } 

// --- GLOBAL DATA FETCHING ---
$unread_dot = "";
$user_pic = "https://via.placeholder.com/150"; // Default Avatar

if(isset($_SESSION['user_id']) && isset($conn)) { 
    $uid = $_SESSION['user_id'];
    
    // 1. Check for Unread Messages (Red Dot)
    $sql_msg = "SELECT COUNT(*) as unread FROM messages WHERE receiver_id = $uid AND is_read = 0";
    $res_msg = $conn->query($sql_msg);
    if($res_msg) {
        $row_msg = $res_msg->fetch_assoc();
        if($row_msg['unread'] > 0) {
            $unread_dot = '<span class="badge bg-danger rounded-circle p-1 ms-2" style="font-size: 8px; vertical-align: middle; box-shadow: 0 0 5px red;"> </span>';
        }
    }

    // 2. Get User Profile Picture
    $sql_pic = "SELECT profile_pic FROM users WHERE id = $uid";
    $res_pic = $conn->query($sql_pic);
    if($res_pic && $row_pic = $res_pic->fetch_assoc()){
        if(!empty($row_pic['profile_pic'])) {
            $user_pic = "uploads/" . $row_pic['profile_pic'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HostelHub - Premium</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Custom Premium CSS -->
    <link rel="stylesheet" href="style.css">

    <style>
        /* --- UNIQUE GLOBAL LOADER CSS --- */
        #global-loader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: #ffffff; z-index: 99999; /* Always on top */
            display: flex; justify-content: center; align-items: center; flex-direction: column;
            transition: opacity 0.5s ease, visibility 0.5s;
        }
        /* The Spinning Ring */
        .loader-ring {
            width: 80px; height: 80px; border: 5px solid rgba(212, 175, 55, 0.2);
            border-top: 5px solid #D4AF37; border-radius: 50%;
            animation: spin 1s linear infinite; margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.3);
        }
        /* The Brand Text */
        .loader-brand {
            font-family: 'Cinzel', serif; font-size: 24px; font-weight: 700; color: #1a1a1a;
            letter-spacing: 2px; animation: pulse 1.5s infinite ease-in-out;
        }
        /* The 'Loading...' Text */
        .loader-text {
            font-family: 'Poppins', sans-serif; font-size: 14px; color: #888; margin-top: 5px; letter-spacing: 1px;
        }
        /* Animations */
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @keyframes pulse { 0% { opacity: 0.6; transform: scale(0.98); } 50% { opacity: 1; transform: scale(1.02); } 100% { opacity: 0.6; transform: scale(0.98); } }

        /* --- SCROLLABLE SIDEBAR CSS --- */
        .sidebar {
            height: 100vh;
            width: 280px;
            position: fixed;
            top: 0;
            left: -320px; /* Hidden completely */
            background: var(--dark-bg);
            padding-top: 100px;
            /* KEY FIX: Make it scrollable independently */
            overflow-y: auto;
            overflow-x: hidden;
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); 
            z-index: 1040;
            box-shadow: 10px 0 30px rgba(0,0,0,0.1);
            
            /* Custom Gold Scrollbar */
            scrollbar-width: thin;
            scrollbar-color: var(--gold) #222;
        }

        .sidebar.active { left: 0; }

        /* Webkit Scrollbar Styling (Chrome/Safari) */
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-track { background: #222; }
        .sidebar::-webkit-scrollbar-thumb { background-color: var(--gold); border-radius: 20px; border: 2px solid #222; }

        /* Prevent Body Scroll when Sidebar Open (Optional JS Logic below) */
        body.sidebar-open { overflow: hidden; }
    </style>
</head>
<body>

<!-- --- UNIQUE LOADER HTML --- -->
<div id="global-loader">
    <div class="loader-ring"></div>
    <div class="loader-brand">Hostel<span style="color: #D4AF37;">Hub</span></div>
    <div class="loader-text">Please wait...</div>
</div>

<!-- 1. Minimal Glass Header -->
<div class="premium-header">
    <div class="d-flex align-items-center">
        <!-- Menu Icon (Click to open Sidebar) -->
        <i class="bi bi-list menu-trigger me-3" onclick="toggleSidebar()"></i>
        <a href="index.php" class="brand-gold">HostelHub</a>
    </div>
    
    <div class="d-flex align-items-center">
        <?php if(isset($_SESSION['user_id'])): ?>
            <!-- Profile Pic in Header -->
            <a href="profile.php" class="me-3 d-none d-sm-block">
                <img src="<?php echo $user_pic; ?>" class="rounded-circle border" style="width: 35px; height: 35px; object-fit: cover; border-color: var(--gold) !important;">
            </a>
            
            <a href="logout.php" class="btn btn-outline-dark rounded-pill btn-sm" style="border-color: var(--gold); color: #333;">Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-dark rounded-pill btn-sm" style="background: var(--gold); border:none;">Login</a>
        <?php endif; ?>
    </div>
</div>

<!-- 2. The Animated Sidebar -->
<div class="sidebar" id="sidebar">
    
    <!-- Dynamic Profile Section in Sidebar -->
    <div class="text-center mb-4 mt-2">
        <?php if(isset($_SESSION['user_id'])): ?>
            <img src="<?php echo $user_pic; ?>" class="rounded-circle mb-2 shadow" style="width: 70px; height: 70px; object-fit: cover; border: 3px solid var(--gold); padding: 2px;">
            <h6 class="text-white mb-0 fw-bold" style="font-family: 'Cinzel';"><?php echo $_SESSION['name']; ?></h6>
            <small class="text-white-50" style="font-size: 11px; letter-spacing: 1px;"><?php echo strtoupper($_SESSION['role']); ?></small>
        <?php else: ?>
            <h4 class="text-white" style="font-family: 'Cinzel'; letter-spacing: 2px;">MENU</h4>
        <?php endif; ?>
    </div>

    <!-- General Links (ADDED ABOUT & CONTACT HERE) -->
    <a href="index.php"><i class="bi bi-house me-2"></i> Home</a>
    <a href="about.php"><i class="bi bi-info-circle me-2"></i> About Us</a>
    <a href="contact.php"><i class="bi bi-envelope me-2"></i> Contact Us</a>

    <?php if(isset($_SESSION['user_id'])): ?>
        <a href="profile.php"><i class="bi bi-person-circle me-2"></i> My Profile</a>
        <a href="community.php"><i class="bi bi-people-fill me-2"></i> Community Chat</a>
    <?php endif; ?>

    <hr class="border-secondary mx-3">

    <!-- Student Links -->
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'student'): ?>
        <small class="text-muted ms-4 mb-2 d-block">STUDENT PANEL</small>
        <a href="my_bookings.php"><i class="bi bi-calendar-check me-2"></i> My Bookings</a>
        <a href="my_wishlist.php"><i class="bi bi-heart me-2"></i> Saved Hostels</a>
        <a href="chat_list.php"><i class="bi bi-chat-dots me-2"></i> Messages <?php echo $unread_dot; ?></a>
    <?php endif; ?>

    <!-- Owner Links -->
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'owner'): ?>
        <small class="text-muted ms-4 mb-2 d-block">OWNER PANEL</small>
        <a href="owner_bookings.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        <a href="manage_hostel.php"><i class="bi bi-megaphone me-2"></i> Announcements</a>
        <a href="manage_mess.php"><i class="bi bi-egg-fried me-2"></i> Mess Menu</a>
        <a href="chat_list.php"><i class="bi bi-chat-dots me-2"></i> Messages <?php echo $unread_dot; ?></a>
    <?php endif; ?>
    
    <!-- Bottom Padding for Scrolling -->
    <div style="height: 100px;"></div>
</div>

<!-- 3. Dark Overlay (Click to close) -->
<div class="sidebar-overlay" id="overlay" onclick="toggleSidebar()"></div>

<!-- Script for Sidebar & Loader -->
<script>
    // 1. Sidebar Logic
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const body = document.body;

        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        
        // Prevent Body Scroll when Sidebar is Open
        if (sidebar.classList.contains('active')) {
            body.classList.add('sidebar-open');
        } else {
            body.classList.remove('sidebar-open');
        }
    }

    // 2. Unique Loader Logic
    window.addEventListener("load", function () {
        const loader = document.getElementById("global-loader");
        loader.style.opacity = "0"; 
        setTimeout(() => {
            loader.style.display = "none"; 
        }, 500); 
    });
</script>

<!-- TOASTIFY CSS & JS (Alert System) -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
    // 1. FUNCTION TO SHOW TOAST
    function showToast(message, type = "success") {
        let color = type === "error" ? "#dc3545" : "#198754"; 
        if(type === "gold") color = "linear-gradient(to right, #D4AF37, #F9D776)"; 

        Toastify({
            text: message,
            duration: 3000,
            gravity: "top", 
            position: "right", 
            stopOnFocus: true, 
            style: {
                background: color,
                borderRadius: "10px",
                boxShadow: "0 4px 15px rgba(0,0,0,0.1)",
                color: type === "gold" ? "#000" : "#fff",
                fontWeight: "bold"
            },
        }).showToast();
    }

    // 2. OVERRIDE DEFAULT ALERT
    window.alert = function(message) {
        showToast(message, "gold");
    };

    // 3. CHECK URL FOR PHP MESSAGES
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('msg')) {
        let msg = urlParams.get('msg');
        if(msg === 'updated') showToast("Updated Successfully!", "success");
        if(msg === 'deleted') showToast("Item Deleted!", "error");
        if(msg === 'sent') showToast("Request Sent!", "success");
        
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>
</body>
</html>