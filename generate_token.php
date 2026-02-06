<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $nic = isset($_POST['nic']) ? trim($_POST['nic']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

    if ($service_id > 0 && !empty($nic)) {
        
        // 1. Insert Visitor (if not exists or just log new visit)
        // For simplicity, we insert a new record for every visit or find id if recent
        $stmt = $conn->prepare("INSERT INTO visitors (nic_number, phone_number) VALUES (?, ?)");
        $stmt->bind_param("ss", $nic, $phone);
        $stmt->execute();
        $visitor_id = $conn->insert_id;
        $stmt->close();

        // 2. Get Token Prefix
        $sql_service = "SELECT token_prefix, name, avg_service_time_min FROM services WHERE id = $service_id";
        $service_res = $conn->query($sql_service);
        $service_row = $service_res->fetch_assoc();
        
        $prefix = $service_row['token_prefix'];
        $service_name = $service_row['name'];
        $est_time = $service_row['avg_service_time_min'];

        // 3. Generate Token Number (Count for today for specific service + 1)
        $today = date("Y-m-d");
        $sql_count = "SELECT COUNT(*) as count FROM queue_tokens WHERE service_id = $service_id AND DATE(generated_at) = '$today'";
        $count_res = $conn->query($sql_count);
        $count_row = $count_res->fetch_assoc();
        $new_seq = $count_row['count'] + 1;
        
        // Format: P-1001 for example. Or just P-001. Let's do P-001.
        $token_number = $prefix . '-' . str_pad($new_seq, 3, '0', STR_PAD_LEFT);

        // 4. Insert Token
        $stmt_token = $conn->prepare("INSERT INTO queue_tokens (visitor_id, service_id, token_number) VALUES (?, ?, ?)");
        $stmt_token->bind_param("iis", $visitor_id, $service_id, $token_number);
        
        if ($stmt_token->execute()) {
             // Calculate approx wait time (Basic logic: pending tokens * avg time / active counters)
             // Simplified: just pending tokens * avg time for this service
             $sql_pending = "SELECT COUNT(*) as pending FROM queue_tokens WHERE service_id = $service_id AND status = 'pending'";
             $pending_res = $conn->query($sql_pending);
             $pending_row = $pending_res->fetch_assoc();
             $people_ahead = $pending_row['pending'] - 1; // Exclude self
             if ($people_ahead < 0) $people_ahead = 0;
             
             $wait_time = $people_ahead * $est_time;
             
             // Display Token
             ?>
             <!DOCTYPE html>
             <html lang="en">
             <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Your Token</title>
                <link rel="stylesheet" href="style.css">
                <style>
                    /* Specific styles for print token page */
                    .token-card {
                        background: white;
                        text-align: center;
                        padding: 40px;
                        border-radius: 20px;
                        box-shadow: var(--shadow-strong);
                        max-width: 400px;
                        margin: 0 auto;
                    }
                    .token-number {
                        font-size: 4rem;
                        font-weight: 800;
                        color: var(--primary-color);
                        margin: 20px 0;
                        letter-spacing: 2px;
                    }
                    .token-info {
                        color: var(--text-light);
                        margin-bottom: 30px;
                        line-height: 1.6;
                    }
                    .info-block {
                        margin-top: 15px;
                        padding-top: 15px;
                        border-top: 1px dashed #e5e7eb;
                    }
                    @media print {
                        body * { visibility: hidden; }
                        .token-card, .token-card * { visibility: visible; }
                        .token-card { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; }
                        .btn-home, .btn-print { display: none; }
                    }
                    .btn-group { display: flex; gap: 10px; justify-content: center; }
                    .btn-home { background: #e5e7eb; color: var(--text-main); }
                    .btn-print { background: var(--primary-color); color: white; }
                    .btn-action { padding: 12px 24px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block;}
                </style>
             </head>
             <body>
                 <div class="kiosk-container" style="max-width: 600px; padding: 40px;">
                    <div class="token-card">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5f/Emblem_of_Sri_Lanka.svg/1200px-Emblem_of_Sri_Lanka.svg.png" alt="Emblem" style="height: 50px; opacity: 0.8;">
                        <h3>Divisional Secretariat</h3>
                        <p><?php echo date("Y-m-d H:i"); ?></p>
                        
                        <div class="token-number"><?php echo $token_number; ?></div>
                        
                        <div class="token-info">
                            <strong>Service:</strong> <?php echo $service_name; ?><br>
                            <?php if ($people_ahead > 0): ?>
                                <strong>People Ahead:</strong> <?php echo $people_ahead; ?><br>
                                <strong>Est. Wait:</strong> ~<?php echo $wait_time; ?> mins
                            <?php else: ?>
                                <strong>Please proceed to the counter.</strong>
                            <?php endif; ?>
                            <div class="info-block">
                                <small>Please wait in the lobby area until your number is called.</small>
                            </div>
                        </div>

                        <div class="btn-group">
                            <a href="index.php" class="btn-action btn-home">Back to Home</a>
                            <button onclick="window.print()" class="btn-action btn-print">Print Token</button>
                        </div>
                    </div>
                 </div>
                 <script>
                     // Auto print dialog
                     // window.print();
                 </script>
             </body>
             </html>
             <?php
        } else {
            echo "Error generating token.";
        }
        $stmt_token->close();
    } else {
         header("Location: index.php");
         exit();
    }

} else {
    header("Location: index.php");
    exit();
}
?>
