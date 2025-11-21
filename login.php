<?php 
include 'config.php';
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['name'] = $row['name'];
            header("Location: " . ($row['role'] == 'owner' ? 'add_hostel.php' : 'index.php'));
            exit();
        } else { $error = "Invalid Password"; }
    } else { $error = "User not found"; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HostelHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0 auth-wrapper" style="min-height: 100vh;">
        
        <!-- LEFT SIDE: IMAGE -->
        <!-- d-none d-lg-block: Hides this on mobile/tablet, shows only on Large screens -->
        <div class="col-lg-6 d-none d-lg-block auth-image-side" style="position: relative; min-height: 100vh;">
            <div class="auth-overlay">
                <h1 class="display-4 fw-bold">Welcome Back!</h1>
                <p class="lead">Find the best hostels in Faisalabad with just one click.</p>
            </div>
        </div>

        <!-- RIGHT SIDE: FORM -->
        <!-- col-12: Full width on Mobile. col-lg-6: Half width on Desktop -->
        <div class="col-12 col-lg-6 auth-form-side bg-white d-flex align-items-center justify-content-center">
            
            <!-- Responsive Padding and Max Width -->
            <div class="w-100 p-4 p-md-5" style="max-width: 550px;">
                
                <div class="mb-4">
                    <a href="index.php" class="text-decoration-none fw-bold text-primary small">
                        <i class="bi bi-arrow-left"></i> Back to Home
                    </a>
                    <h2 class="fw-bold mt-3">Log in to HostelHub</h2>
                    <p class="text-muted">Enter your details below to continue.</p>
                </div>

                <?php if(isset($error)): ?>
                    <div class="alert alert-danger py-2 shadow-sm border-0">
                        <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control p-3 border-start-0 bg-light" placeholder="name@example.com" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control p-3 border-start-0 bg-light" placeholder="••••••••" required>
                        </div>
                    </div>
                    
                    <div class="d-grid mb-4">
                        <button type="submit" name="login" class="btn btn-primary btn-lg fw-bold shadow-sm">Log In</button>
                    </div>
                </form>

                <div class="divider-text mb-4 small text-muted">OR CONTINUE WITH</div>

                <!-- Social Buttons Grid -->
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
                    Don't have an account? <a href="register.php" class="fw-bold text-primary text-decoration-none">Sign up</a>
                </p>
            </div>
        </div>
    </div>
</div>

</body>
</html>