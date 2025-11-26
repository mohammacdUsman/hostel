<?php
include 'db.php';
include 'header_sidebar.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$area = isset($_GET['area']) ? htmlspecialchars($_GET['area']) : 'General';
$uid = $_SESSION['user_id'];
?>

<style>
    .chat-box { height: 65vh; overflow-y: auto; background: #f0f2f5; padding: 20px; border-radius: 15px; }
    .msg { padding: 10px 15px; margin-bottom: 10px; border-radius: 15px; width: fit-content; max-width: 75%; word-wrap: break-word; }
    .my-msg { background: #D4AF37; color: white; margin-left: auto; border-bottom-right-radius: 0; }
    .other-msg { background: white; color: black; border-bottom-left-radius: 0; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .sender-name { font-size: 10px; font-weight: bold; margin-bottom: 2px; display: block; color: #555; }
</style>

<div class="content-wrapper">
    <div class="container" style="max-width: 800px;">
        <div class="card border-0 shadow-lg">
            
            <div class="card-header bg-white py-3 d-flex align-items-center">
                <a href="community.php" class="btn btn-sm btn-light me-3"><i class="bi bi-arrow-left"></i></a>
                <div>
                    <h5 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-danger"></i> <?php echo $area; ?> Community</h5>
                    <small class="text-muted">Public Group Chat</small>
                </div>
            </div>

            <div class="card-body p-0">
                <div id="chatBox" class="chat-box">
                    <div class="text-center mt-5"><div class="spinner-border text-warning"></div></div>
                </div>
            </div>

            <div class="card-footer bg-white p-3">
                <div class="input-group">
                    <input type="text" id="msgInput" class="form-control rounded-pill" placeholder="Ask something about <?php echo $area; ?>...">
                    <button class="btn btn-gold rounded-pill ms-2 px-4" onclick="sendMsg()"><i class="bi bi-send-fill"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const areaName = "<?php echo $area; ?>";
    const myId = <?php echo $uid; ?>;

    function loadMessages() {
        $.post('api_community.php', { action: 'fetch', area: areaName }, function(data) {
            $('#chatBox').html(data);
        });
    }

    function sendMsg() {
        let msg = $('#msgInput').val();
        if(msg.trim() !== "") {
            $.post('api_community.php', { action: 'send', area: areaName, message: msg, uid: myId }, function() {
                $('#msgInput').val('');
                loadMessages();
                setTimeout(() => { 
                    var box = document.getElementById("chatBox");
                    box.scrollTop = box.scrollHeight; 
                }, 300);
            });
        }
    }

    setInterval(loadMessages, 2000); // Refresh every 2 seconds
    
    // Send on Enter key
    document.getElementById("msgInput").addEventListener("keyup", function(e) {
        if (e.keyCode === 13) sendMsg();
    });
</script>
</body>
</html>