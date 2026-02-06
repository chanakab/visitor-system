<?php
require 'auth.php';
Auth::requireRole(['officer']);

$user_id = $_SESSION['user_id'];
$inst_id = $_SESSION['institute_id'];
$username = $_SESSION['username'];

// Fetch Officer Details (Counter Number is in user table now)
$user_q = $conn->query("SELECT counter_number FROM users WHERE id=$user_id");
$user_row = $user_q->fetch_assoc();
$counter_no = $user_row['counter_number'];

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'call_next') {
        // Find next pending in this institute
        // Note: Can improve to check service mapping, but simplified FCFS for now
        $sql = "SELECT id FROM queue_tokens WHERE institute_id=$inst_id AND status='pending' ORDER BY id ASC LIMIT 1";
        $res = $conn->query($sql);
        if ($res->num_rows > 0) {
            $token_id = $res->fetch_assoc()['id'];
            $conn->query("UPDATE queue_tokens SET status='called', assigned_user_id=$user_id, called_at=NOW() WHERE id=$token_id");
        }
    }
    
    if ($_POST['action'] == 'complete') {
         $conn->query("UPDATE queue_tokens SET status='completed', completed_at=NOW() WHERE assigned_user_id=$user_id AND status='called'");
    }
}

// Get Current State
$current_token = null;
$sql_curr = "SELECT t.*, s.name as service_name, v.nic_number FROM queue_tokens t 
            JOIN services s ON t.service_id = s.id 
            JOIN visitors v ON t.visitor_id = v.id
            WHERE t.assigned_user_id = $user_id AND t.status = 'called'";
$res_curr = $conn->query($sql_curr);
if ($res_curr->num_rows > 0) $current_token = $res_curr->fetch_assoc();

$pending_count = $conn->query("SELECT COUNT(*) as cnt FROM queue_tokens WHERE institute_id=$inst_id AND status='pending'")->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Officer Portal</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<div class="app-container">
    <header class="header-bar glass-panel">
        <div class="brand">
            <h1>Officer Portal</h1>
            <p>Counter #<?php echo $counter_no; ?> (<?php echo $username; ?>)</p>
        </div>
        <a href="logout.php" class="btn btn-danger"><i data-lucide="log-out"></i> Logout</a>
    </header>

    <div class="counter-wrapper">
        <div class="counter-main glass-panel">
            <?php if ($current_token): ?>
                <div style="animation: fadeIn 0.5s;">
                    <span class="badge badge-called" style="font-size: 1rem; padding: 8px 16px;">Now Serving</span>
                    <div class="big-token animate"><?php echo $current_token['token_number']; ?></div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; text-align: left; background: #f8fafc; padding: 20px; border-radius: 12px; margin: 20px 0;">
                        <div><small>SERVICE</small><h3><?php echo $current_token['service_name']; ?></h3></div>
                        <div><small>VISITOR NIC</small><h3><?php echo $current_token['nic_number']; ?></h3></div>
                    </div>

                    <form method="POST">
                         <button type="submit" name="action" value="complete" class="btn btn-secondary btn-lg" style="color: var(--secondary); border: 2px solid var(--secondary);">
                            <i data-lucide="check-square"></i> Complete
                         </button>
                    </form>
                </div>
            <?php else: ?>
                <div style="opacity: 0.6;">
                    <i data-lucide="coffee" style="width: 80px; height: 80px; color: var(--text-muted);"></i>
                    <h2 style="margin-top: 20px;">Ready</h2>
                    <p>Counter is available.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="counter-sidebar">
            <div class="stat-card" style="border-left-color: var(--danger);">
                <div class="stat-title">Pending</div>
                <div class="stat-val" id="pending-count"><?php echo $pending_count; ?></div>
            </div>
            <div class="glass-panel">
                <form method="POST">
                    <?php if ($current_token): ?>
                        <button disabled class="btn btn-primary btn-full btn-lg" style="opacity:0.5">Call Next</button>
                    <?php else: ?>
                        <button type="submit" name="action" value="call_next" class="btn btn-primary btn-full btn-lg">Call Next</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    lucide.createIcons();
    setInterval(() => {
        fetch('api.php?action=officer_state')
            .then(res => res.json())
            .then(data => document.getElementById('pending-count').innerText = data.pending);
    }, 3000);
</script>
</body>
</html>
