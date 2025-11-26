<?php 
include 'db.php'; 
include 'header_sidebar.php'; 

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$my_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;

// 1. MARK MESSAGES AS READ (The "Disappear" Logic)
// We update messages where the SENDER is the person we are chatting with, 
// and the RECEIVER is me.
$conn->query("UPDATE messages SET is_read = 1 WHERE sender_id = $receiver_id AND receiver_id = $my_id");

// Check user
$user_query = $conn->query("SELECT name FROM users WHERE id = $receiver_id");
if($user_query->num_rows > 0) {
    $user = $user_query->fetch_assoc();
} else {
    echo "<div class='content-wrapper container mt-5'><h3>User not found</h3></div>"; exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Chat with <?php echo $user['name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css"> 
    <style>
        .content-wrapper { margin-left: 250px; padding-top: 80px; padding-right: 20px; padding-left: 20px;}
        @media (max-width: 768px) { .content-wrapper { margin-left: 0; } }
        
        /* Chat Specific Styles */
        .chat-container { height: 65vh; overflow-y: scroll; background: #e5ddd5; padding: 20px; border-radius: 10px; }
        .msg { padding: 8px 15px; margin-bottom: 10px; border-radius: 20px; max-width: 75%; width: fit-content; clear: both; position: relative; word-wrap: break-word;}
        .sent { background: #dcf8c6; float: right; border-bottom-right-radius: 0; }
        .received { background: #fff; float: left; border-top-left-radius: 0; }
    </style>
</head>
<body class="bg-light">

<div class="content-wrapper">
    <div class="card shadow-sm mx-auto" style="max-width: 900px;">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="fas fa-user-circle"></i> <?php echo $user['name']; ?></span>
            <a href="chat_list.php" class="btn btn-sm btn-outline-light">Back</a>
        </div>
        
        <div class="card-body chat-container" id="chatBox">
            <div class="text-center text-muted mt-5"><i class="fas fa-spinner fa-spin"></i> Loading chat...</div>
        </div>
        
        <div class="card-footer bg-white">
            <div class="input-group">
                <input type="text" id="msgInput" class="form-control" placeholder="Type a message..." autocomplete="off">
                <button class="btn btn-success" onclick="sendMessage()"><i class="fas fa-paper-plane"></i> Send</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const myId = <?php echo $my_id; ?>;
    const recId = <?php echo $receiver_id; ?>;

    function scrollToBottom() {
        var chatBox = document.getElementById("chatBox");
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function loadMessages() {
        $.post('api_chat.php', { action: 'fetch', s_id: myId, r_id: recId }, function(data) {
            $('#chatBox').html(data);
        });
    }

    function sendMessage() {
        let msg = $('#msgInput').val();
        if(msg.trim() !== "") {
            $.post('api_chat.php', { action: 'send', s_id: myId, r_id: recId, message: msg }, function() {
                $('#msgInput').val('');
                loadMessages(); 
                setTimeout(scrollToBottom, 500); 
            });
        }
    }

    setInterval(loadMessages, 2000);
    
    document.getElementById("msgInput").addEventListener("keyup", function(event) {
        if (event.keyCode === 13) { sendMessage(); }
    });
</script>
</body>
</html>