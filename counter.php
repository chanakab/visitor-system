<?php
require 'db.php';
session_start();

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['set_counter'])) {
        $_SESSION['counter_id'] = $_POST['counter_id'];
    }
    
    if (isset($_POST['action']) && isset($_SESSION['counter_id'])) {
        $counter_id = $_SESSION['counter_id'];
        
        // 1. Call Next Token
        if ($_POST['action'] == 'call_next') {
            // Find next pending token for services mapped to this counter? 
            // For simplicity, let's assume all counters handle all services OR just pick ANY pending for now strictly FCFS
            // To make it smarter, we should check mapping. Let's do simple global FCFS for this MVP unless mapped.
            
            // Get ANY pending token ordered by ID (FCFS)
            $sql = "SELECT id FROM queue_tokens WHERE status='pending' ORDER BY id ASC LIMIT 1";
            $res = $conn->query($sql);
            if ($res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $token_id = $row['id'];
                
                $update = "UPDATE queue_tokens SET status='called', assigned_counter_id=$counter_id, called_at=NOW() WHERE id=$token_id";
                $conn->query($update);
            }
        }
        
        // 2. Complete Current Token
        if ($_POST['action'] == 'complete') {
            $sql = "SELECT id FROM queue_tokens WHERE assigned_counter_id=$counter_id AND status='called' LIMIT 1";
            $res = $conn->query($sql);
            if ($res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $token_id = $row['id'];
                
                $update = "UPDATE queue_tokens SET status='completed', completed_at=NOW() WHERE id=$token_id";
                $conn->query($update);
            }
        }
    }
}

// Current State Data
$counter_id = isset($_SESSION['counter_id']) ? $_SESSION['counter_id'] : null;
$current_token = null;

if ($counter_id) {
    // Check if this counter has a 'called' token
    $sql = "SELECT t.*, s.name as service_name, v.nic_number FROM queue_tokens t 
            JOIN services s ON t.service_id = s.id 
            JOIN visitors v ON t.visitor_id = v.id
            WHERE t.assigned_counter_id = $counter_id AND t.status = 'called'";
    $res = $conn->query($sql);
    if ($res->num_rows > 0) {
        $current_token = $res->fetch_assoc();
    }
    
    // Get Pending Count
    $sql_pending = "SELECT COUNT(*) as count FROM queue_tokens WHERE status='pending'";
    $res_pending = $conn->query($sql_pending);
    $pending_count = $res_pending->fetch_assoc()['count'];
}

// Get Counters for selection
$counters_res = $conn->query("SELECT * FROM counters");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Officer Portal - DS Office</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .officer-panel { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 20px; box-shadow: var(--shadow-strong); }
        .token-display { font-size: 5rem; font-weight: 800; color: var(--primary-color); margin: 20px 0; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 20px; color: var(--text-light); }
        .controls { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 40px; }
        .btn-call { background: var(--primary-color); color: white; border: none; padding: 20px; font-size: 1.5rem; border-radius: 12px; cursor: pointer; }
        .btn-complete { background: #10b981; color: white; border: none; padding: 20px; font-size: 1.5rem; border-radius: 12px; cursor: pointer; }
        .btn-disabled { opacity: 0.5; cursor: not-allowed; }
        .login-box { max-width: 400px; margin: 50px auto; text-align: center; }
        select { width: 100%; padding: 15px; margin-bottom: 20px; border-radius: 8px; border: 1px solid #ccc; }
    </style>
</head>
<body>

<?php if (!$counter_id): ?>
    <div class="kiosk-container login-box">
        <h2>Officer Login</h2>
        <p>Select your counter to start</p>
        <form method="POST">
            <select name="counter_id" required>
                <option value="">-- Select Counter --</option>
                <?php while($c = $counters_res->fetch_assoc()): ?>
                    <option value="<?php echo $c['id']; ?>">Counter <?php echo $c['counter_number']; ?> - <?php echo $c['officer_name']; ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="set_counter" class="btn-generate">Start Session</button>
        </form>
        <div style="margin-top: 20px;">
           <a href="index.php">Back to Kiosk</a> | <a href="dashboard.php">Admin Dashboard</a>
        </div>
    </div>
<?php else: ?>
    <div class="officer-panel">
        <div class="info-row">
            <span>Counter: <strong>#<?php echo $counter_id; ?></strong></span>
            <span>Pending Pool: <strong><?php echo $pending_count; ?></strong></span>
            <a href="logout.php" style="color: red; text-decoration: none;">Logout</a>
        </div>
        
        <hr>
        
        <div style="text-align: center;">
            <?php if ($current_token): ?>
                <h3>CURRENT TOKEN</h3>
                <div class="token-display"><?php echo $current_token['token_number']; ?></div>
                <p>Service: <?php echo $current_token['service_name']; ?></p>
                <p>Visitor: <?php echo $current_token['nic_number']; ?></p>
            <?php else: ?>
                <h3>WAITING FOR NEXT</h3>
                <div class="token-display" style="color: #ccc;">---</div>
                <p>No active token.</p>
            <?php endif; ?>
        </div>
        
        <form method="POST" class="controls">
             <?php if ($current_token): ?>
                 <button type="button" class="btn-call btn-disabled" disabled>Call Next</button>
                 <button type="submit" name="action" value="complete" class="btn-complete">Complete ✅</button>
             <?php else: ?>
                 <button type="submit" name="action" value="call_next" class="btn-call">Call Next 📢</button>
                 <button type="button" class="btn-complete btn-disabled" disabled>Complete ✅</button>
             <?php endif; ?>
        </form>
    </div>
<?php endif; ?>

</body>
</html>
