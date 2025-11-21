<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - HostelHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0 auth-wrapper" style="min-height: 100vh;">
        
        <!-- LEFT SIDE: IMAGE -->
        <!-- d-none d-lg-block: Hides this on mobile/tablet, shows only on Large screens -->
        <div class="col-lg-6 d-none d-lg-block auth-image-side" style="background-image: url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80'); background-size: cover; background-position: center; position: relative; min-height: 100vh;">
            <div class="auth-overlay" style="background: linear-gradient(to bottom, rgba(37,99,235,0.4), rgba(15,23,42,0.8)); position: absolute; top:0; left:0; width:100%; height:100%; display: flex; flex-direction: column; justify-content: center; padding: 60px; color: white;">
                <h1 class="display-4 fw-bold">Join HostelHub</h1>
                <p class="lead">Create an account to book hostels or list your property.</p>
            </div>
        </div>

        <!-- RIGHT SIDE: FORM -->
        <!-- col-12: Full width on Mobile. col-lg-6: Half width on Desktop -->
        <div class="col-12 col-lg-6 auth-form-side bg-white d-flex align-items-center justify-content-center">
            
            <!-- Responsive Padding and Max Width container -->
            <div class="w-100 p-4 p-md-5" style="max-width: 550px;">
                
                <div class="mb-4">
                    <a href="index.php" class="text-decoration-none fw-bold text-primary small">
                        <i class="bi bi-arrow-left"></i> Back to Home
                    </a>
                    <h2 class="fw-bold mt-3">Create an Account</h2>
                    <p class="text-muted">Fill in your details to get started.</p>
                </div>

                <?php
                if (isset($_POST['register'])) {
                    $name = $_POST['name'];
                    $email = $_POST['email'];
                    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $role = $_POST['role'];

                    $check = $conn->prepare("SELECT email FROM users WHERE email = ?");
                    $check->bind_param("s", $email);
                    $check->execute();
                    if ($check->get_result()->num_rows > 0) {
                        echo "<div class='alert alert-danger shadow-sm border-0'><i class='bi bi-exclamation-circle-fill'></i> Email already registered!</div>";
                    } else {
                        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("ssss", $name, $email, $pass, $role);
                        if ($stmt->execute()) {
                            echo "<div class='alert alert-success shadow-sm border-0'><i class='bi bi-check-circle-fill'></i> Account created! <a href='login.php' class='fw-bold'>Login now</a></div>";
                        }
                    }
                }
                ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                            <input type="text" name="name" class="form-control p-3 border-start-0 bg-light" placeholder="e.g. Ali Khan" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control p-3 border-start-0 bg-light" placeholder="name@example.com" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control p-3 border-start-0 bg-light" placeholder="Create a password" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-secondary">I am a:</label>
                        <select name="role" class="form-select p-3 bg-light">
                            <option value="student">Student (Looking for Hostel)</option>
                            <option value="owner">Hostel Owner (Listing Hostel)</option>
                        </select>
                    </div>
                    
                    <div class="d-grid mb-4">
                        <button type="submit" name="register" class="btn btn-primary btn-lg fw-bold shadow-sm">Sign Up</button>
                    </div>
                </form>

                <div class="divider-text mb-4 small text-muted">OR SIGN UP WITH</div>

                <!-- Responsive Grid for Social Buttons -->
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <button class="social-btn w-100 btn btn-outline-light text-dark border" onclick="alert('Google API Key required.')">
                            <img src="https://img.icons8.com/color/48/000000/google-logo.png" width="20" class="me-2"> Google
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="social-btn w-100 btn btn-outline-light text-dark border" onclick="alert('GitHub API Key required.')">
                            <img src="https://img.icons8.com/ios-glyphs/30/000000/github.png" width="20" class="me-2"> GitHub
                        </button>
                    </div>
                </div>

                <p class="text-center text-muted">
                    Already have an account? <a href="login.php" class="fw-bold text-primary text-decoration-none">Log in</a>
                </p>
            </div>
        </div>
    </div>
</div>

</body>
</html>