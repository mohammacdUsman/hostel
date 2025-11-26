<?php
include 'db.php';

if(!isset($_POST['action'])) exit();

$action = $_POST['action'];
$area = $conn->real_escape_string($_POST['area']);

// SEND MESSAGE
if($action == 'send') {
    $uid = intval($_POST['uid']);
    $msg = htmlspecialchars($_POST['message']);
    $stmt = $conn->prepare("INSERT INTO community_messages (area_name, sender_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $area, $uid, $msg);
    $stmt->execute();
}

// FETCH MESSAGES
if($action == 'fetch') {
    $uid = $_SESSION['user_id']; // Current logged in user
    $sql = "SELECT m.*, u.name, u.profile_pic FROM community_messages m 
            JOIN users u ON m.sender_id = u.id 
            WHERE m.area_name = '$area' 
            ORDER BY m.created_at ASC";
    
    $res = $conn->query($sql);
    
    if($res->num_rows > 0) {
        while($row = $res->fetch_assoc()) {
            $is_me = ($row['sender_id'] == $uid);
            $cls = $is_me ? 'my-msg' : 'other-msg';
            $pic = !empty($row['profile_pic']) ? 'uploads/'.$row['profile_pic'] : 'https://via.placeholder.com/30';
            
            echo "<div class='d-flex " . ($is_me ? "justify-content-end" : "") . " mb-2'>
                    " . (!$is_me ? "<img src='$pic' class='rounded-circle me-2' style='width:30px; height:30px; object-fit:cover;'>" : "") . "
                    <div class='msg $cls'>
                        " . (!$is_me ? "<span class='sender-name'>{$row['name']}</span>" : "") . "
                        {$row['message']}
                    </div>
                  </div>";
        }
    } else {
        echo "<div class='text-center text-muted mt-5'>No messages yet. Start the conversation!</div>";
    }
}
?>