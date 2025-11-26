<?php
include 'db.php'; // FIX: Removed 'includes/'

// Basic security: Ensure action is set
if(!isset($_POST['action'])) exit();

$action = $_POST['action'];
$s_id = intval($_POST['s_id']); // Security: Force integer
$r_id = intval($_POST['r_id']); // Security: Force integer

if($action == 'send'){
    $msg = htmlspecialchars($_POST['message']); // Security: Prevent XSS
    // Prepared statement is safer
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $s_id, $r_id, $msg);
    $stmt->execute();
}

if($action == 'fetch'){
    // Query to get chat history between these two users
    $sql = "SELECT * FROM messages 
            WHERE (sender_id=$s_id AND receiver_id=$r_id) 
               OR (sender_id=$r_id AND receiver_id=$s_id) 
            ORDER BY created_at ASC";
            
    $res = $conn->query($sql);
    
    if($res->num_rows > 0){
        while($row = $res->fetch_assoc()){
            // Determine class based on who sent the message
            $class = ($row['sender_id'] == $s_id) ? 'sent' : 'received';
            echo "<div class='msg $class'>{$row['message']}</div>";
        }
    } else {
        // Optional: Show specific message if chat is empty
        // echo "<div class='text-center text-muted small mt-5'>No messages yet. Say Hi! 👋</div>";
    }
}
?>