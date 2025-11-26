<?php
// 1. Include Database (Starts Session)
include 'db.php';

// 2. HANDLE LOGIN LOGIC
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            if ($user['role'] == 'owner') {
                header("Location: owner_bookings.php");
            } elseif ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "Email not found!";
    }
}

// 3. Include Header (For DB/Session) but we will hide the nav visually via CSS
include 'header_sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Add FontAwesome for Social Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- PAGE SPECIFIC STYLES (Overrides global styles for Login only) --- */
        
        /* Hide default Sidebar/Header for a clean login page */
        .premium-header, .sidebar, .menu-trigger { display: none !important; }
        body { padding-top: 0 !important; overflow: hidden; }

        /* Animated Background */
        .login-wrapper {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1555854877-bab0e564b8d5?q=80&w=1920&auto=format&fit=crop') center/cover no-repeat;
            display: flex; align-items: center; justify-content: center;
            z-index: 9999;
        }
        .login-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(28, 28, 28, 0.9) 0%, rgba(28, 28, 28, 0.7) 100%);
            backdrop-filter: blur(5px);
        }

        /* Glass Card Animation */
        .login-card {
            position: relative;
            width: 100%; max-width: 450px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            animation: fadeInUp 1s cubic-bezier(0.2, 0.8, 0.2, 1);
            color: white;
        }

        /* Back Arrow */
        .back-arrow {
            position: absolute; top: 30px; left: 30px;
            color: rgba(255,255,255,0.7);
            text-decoration: none; font-size: 16px; font-weight: 500;
            display: flex; align-items: center; gap: 10px;
            transition: 0.3s; z-index: 10000;
        }
        .back-arrow:hover { color: var(--gold); transform: translateX(-5px); }

        /* Inputs */
        .form-floating .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white; border-radius: 10px;
        }
        .form-floating .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: var(--gold);
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.3);
            color: white;
        }
        .form-floating label { color: rgba(255,255,255,0.6); }
        
        /* Buttons */
        .btn-login {
            background: linear-gradient(45deg, #D4AF37, #F9D776);
            border: none; color: #1a1a1a; font-weight: 700;
            padding: 12px; border-radius: 50px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(212, 175, 55, 0.4);
            color: black;
        }

        /* Social Buttons */
        .social-btn {
            width: 100%; padding: 10px; border-radius: 50px;
            border: 1px solid rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.05);
            color: white; font-size: 14px; text-decoration: none;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            transition: 0.3s; margin-bottom: 10px;
        }
        .social-btn:hover { background: white; color: #333; transform: translateY(-2px); }
        .divider { display: flex; align-items: center; margin: 20px 0; color: rgba(255,255,255,0.4); font-size: 12px; }
        .divider::before, .divider::after { content: ""; flex: 1; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .divider:not(:empty)::before { margin-right: .5em; }
        .divider:not(:empty)::after { margin-left: .5em; }

        /* Keyframe Animation */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <!-- Background Wrapper -->
    <div class="login-wrapper">
        <div class="login-overlay"></div>

        <!-- Back Navigation -->
        <a href="index.php" class="back-arrow">
            <i class="bi bi-arrow-left-circle-fill fs-4"></i> Back to Home
        </a>

        <!-- Animated Login Card -->
        <div class="login-card">
            <div class="text-center mb-4">
                <h2 class="fw-bold" style="font-family: 'Cinzel', serif; letter-spacing: 2px;">
                    Hostel<span style="color: var(--gold);">Hub</span>
                </h2>
                <p class="small text-white-50">Welcome Back! Please login to continue.</p>
            </div>

            <!-- Error Message -->
            <?php if(isset($error)): ?>
                <div class="alert alert-danger bg-danger bg-opacity-25 text-white border-0 text-center py-2 mb-3">
                    <i class="bi bi-exclamation-circle me-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST">
                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" id="emailInput" placeholder="name@example.com" required>
                    <label for="emailInput">Email Address</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" name="password" class="form-control" id="passInput" placeholder="Password" required>
                    <label for="passInput">Password</label>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input bg-dark border-secondary" type="checkbox" id="remember">
                        <label class="form-check-label text-white-50 small" for="remember">Remember me</label>
                    </div>
                    <a href="#" class="text-white-50 small text-decoration-none">Forgot Password?</a>
                </div>

                <button type="submit" name="login" class="btn btn-login w-100 mb-3">LOGIN</button>
            </form>

            <!-- Divider -->
            <div class="divider">OR CONTINUE WITH</div>

            <!-- Social Buttons -->
            <div class="row g-2">
                <div class="col-6">
                    <a href="#" class="social-btn"><i class="fab fa-google text-danger"></i> Google</a>
                </div>
                <div class="col-6">
                    <a href="#" class="social-btn"><i class="fab fa-github"></i> GitHub</a>
                </div>
            </div>

            <!-- Register Link -->
            <div class="text-center mt-4">
                <span class="text-white-50 small">Don't have an account?</span>
                <a href="register.php" class="fw-bold text-decoration-none" style="color: var(--gold);">Sign Up</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>