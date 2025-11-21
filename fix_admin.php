<?php
include 'config.php';

$email = 'admin@hostelhub.com';
$pass = 'admin123';
$hashed_pass = password_hash($pass, PASSWORD_DEFAULT); // Generates a valid hash for YOUR system

// Update the database
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed_pass, $email);

if($stmt->execute()) {
    echo "<h1>✅ Admin Password Fixed!</h1>";
    echo "<p>You can now login with: <b>admin123</b></p>";
    echo "<a href='login.php'>Go to Login Page</a>";
} else {
    echo "Error updating record: " . $conn->error;
}
?>