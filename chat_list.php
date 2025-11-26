<?php 
include 'db.php'; // FIX: Correct path
include 'header_sidebar.php'; // FIX: Add Sidebar

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$my_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .content-wrapper { margin-left: 250px; padding-top: 80px; padding-left: 20px; padding-right: 20px;}
        @media (max-width: 768px) { .content-wrapper { margin-left: 0; } }
        .chat-item:hover { background-color: #f8f9fa; cursor: pointer; }
    </style>
</head>
<body class="bg-light">

<div class="content-wrapper">
    <div class="container" style="max-width: 800px;">
        <h3 class="fw-bold mb-4"><i class="bi bi-chat-dots-fill text-primary"></i> Recent Conversations</h3>
        
        <div class="list-group shadow-sm border-0">
            <?php
            // Get unique users current user has chatted with
            $sql = "SELECT DISTINCT u.id, u.name, u.role FROM messages m 
                    JOIN users u ON (m.sender_id = u.id OR m.receiver_id = u.id)
                    WHERE (m.sender_id = $my_id OR m.receiver_id = $my_id) AND u.id != $my_id
                    ORDER BY m.created_at DESC";
            
            $res = $conn->query($sql);
            
            if($res && $res->num_rows > 0){
                while($row = $res->fetch_assoc()){
                    $role_badge = ($row['role'] == 'owner') ? 'bg-success' : 'bg-info';
                    
                    echo "<a href='chat.php?receiver_id={$row['id']}' class='list-group-item list-group-item-action p-3 chat-item border-bottom'>
                            <div class='d-flex w-100 justify-content-between align-items-center'>
                                <div>
                                    <h5 class='mb-1 fw-bold'><i class='bi bi-person-circle text-secondary'></i> {$row['name']}</h5>
                                    <span class='badge $role_badge rounded-pill'>".ucfirst($row['role'])."</span>
                                </div>
                                <span class='text-primary small'>Open Chat <i class='bi bi-chevron-right'></i></span>
                            </div>
                          </a>";
                }
            } else {
                echo "<div class='text-center py-5 bg-white rounded'>
                        <i class='bi bi-chat-square-text fs-1 text-muted'></i>
                        <p class='text-muted mt-2'>No conversations yet.</p>
                        <a href='index.php' class='btn btn-primary btn-sm'>Browse Hostels to Chat</a>
                      </div>";
            }
            ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>