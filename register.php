<?php
// 1. Include Database & Logic
include 'db.php';

// 2. HANDLE REGISTRATION LOGIC
$error = "";
$success = "";

if (isset($_POST['register'])) {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Basic Validation
    if ($password !== $confirm_pass) {
        $error = "Passwords do not match!";
    } 
    else {
        // Domain Validation
        $domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($domain, 'MX') && $domain != 'localhost') {
            $error = "Invalid email domain. Please use a real email provider.";
        } else {
            // Check Duplicate
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "This email is already registered. Please login.";
            } else {
                // Create User
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

                if ($stmt->execute()) {
                    echo "<script>alert('Account created successfully! Redirecting to login...'); window.location.href='login.php';</script>";
                    exit();
                } else {
                    $error = "Database error: " . $conn->error;
                }
            }
        }
    }
}

// 3. Include Header (For DB/Session) but hide the visual nav
include 'header_sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Add FontAwesome for Social Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- PAGE SPECIFIC STYLES (Overrides global styles) --- */
        
        /* Hide default Sidebar/Header for a clean page */
        .premium-header, .sidebar, .menu-trigger { display: none !important; }
        body { padding-top: 0 !important; overflow-x: hidden; }

        /* Animated Background */
        .register-wrapper {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1555854877-bab0e564b8d5?q=80&w=1920&auto=format&fit=crop') center/cover no-repeat;
            display: flex; align-items: center; justify-content: center;
            z-index: 9999;
            overflow-y: auto; /* Allow scrolling on small screens */
            padding: 20px;
        }
        .register-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(28, 28, 28, 0.9) 0%, rgba(28, 28, 28, 0.7) 100%);
            backdrop-filter: blur(5px);
            z-index: -1;
        }

        /* Glass Card Animation */
        .register-card {
            position: relative;
            width: 100%; max-width: 500px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            animation: fadeInUp 1s cubic-bezier(0.2, 0.8, 0.2, 1);
            color: white;
            margin-top: 50px; /* Space for back arrow on mobile */
        }

        /* Back Arrow */
        .back-arrow {
            position: fixed; top: 30px; left: 30px;
            color: rgba(255,255,255,0.7);
            text-decoration: none; font-size: 16px; font-weight: 500;
            display: flex; align-items: center; gap: 10px;
            transition: 0.3s; z-index: 10000;
        }
        .back-arrow:hover { color: var(--gold); transform: translateX(-5px); }

        /* Inputs */
        .form-floating .form-control, .form-floating .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white; border-radius: 10px;
        }
        .form-floating .form-control:focus, .form-floating .form-select:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: var(--gold);
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.3);
            color: white;
        }
        .form-floating label { color: rgba(255,255,255,0.6); }
        
        /* Fix for Select Option Visibility */
        .form-select option { color: #333; background: white; }

        /* Buttons */
        .btn-register {
            background: linear-gradient(45deg, #D4AF37, #F9D776);
            border: none; color: #1a1a1a; font-weight: 700;
            padding: 12px; border-radius: 50px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn-register:hover {
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

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <!-- Background Wrapper -->
    <div class="register-wrapper">
        <div class="register-overlay"></div>

        <!-- Back Navigation -->
        <a href="index.php" class="back-arrow">
            <i class="bi bi-arrow-left-circle-fill fs-4"></i> Back to Home
        </a>

        <!-- Animated Register Card -->
        <div class="register-card">
            <div class="text-center mb-4">
                <h2 class="fw-bold" style="font-family: 'Cinzel', serif; letter-spacing: 2px;">
                    Hostel<span style="color: var(--gold);">Hub</span>
                </h2>
                <p class="small text-white-50">Join our premium community today.</p>
            </div>

            <!-- Error Message -->
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger bg-danger bg-opacity-25 text-white border-0 text-center py-2 mb-3">
                    <i class="bi bi-exclamation-circle me-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <!-- Name -->
                <div class="form-floating mb-3">
                    <input type="text" name="name" class="form-control" id="nameInput" placeholder="John Doe" required>
                    <label for="nameInput">Full Name</label>
                </div>
                
                <!-- Email -->
                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" id="emailInput" placeholder="name@example.com" required>
                    <label for="emailInput">Email Address</label>
                </div>

                <!-- Role -->
                <div class="form-floating mb-3">
                    <select name="role" class="form-select" id="roleInput" required>
                        <option value="student">I am a Student</option>
                        <option value="owner">I am a Hostel Owner</option>
                    </select>
                    <label for="roleInput">I want to...</label>
                </div>

                <!-- Passwords -->
                <div class="row g-2 mb-4">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="password" name="password" class="form-control" id="passInput" placeholder="Pass" required>
                            <label for="passInput">Password</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="password" name="confirm_password" class="form-control" id="confPassInput" placeholder="Confirm" required>
                            <label for="confPassInput">Confirm</label>
                        </div>
                    </div>
                </div>

                <button type="submit" name="register" class="btn btn-register w-100 mb-3">CREATE ACCOUNT</button>
            </form>

            <!-- Divider -->
            <div class="divider">OR REGISTER WITH</div>

            <!-- Social Buttons -->
            <div class="row g-2">
                <div class="col-6">
                    <a href="#" class="social-btn"><i class="fab fa-google text-danger"></i> Google</a>
                </div>
                <div class="col-6">
                    <a href="#" class="social-btn"><i class="fab fa-github"></i> GitHub</a>
                </div>
            </div>

            <!-- Login Link -->
            <div class="text-center mt-4">
                <span class="text-white-50 small">Already have an account?</span>
                <a href="login.php" class="fw-bold text-decoration-none" style="color: var(--gold);">Login</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>