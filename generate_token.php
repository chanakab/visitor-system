<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $inst_id = isset($_POST['inst_id']) ? intval($_POST['inst_id']) : 1;
    $nic = isset($_POST['nic']) ? trim($_POST['nic']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

    if ($service_id > 0 && !empty($nic)) {
        
        // 1. Insert/Update Visitor
        $stmt = $conn->prepare("INSERT INTO visitors (nic_number, phone_number) VALUES (?, ?)");
        $stmt->bind_param("ss", $nic, $phone);
        $stmt->execute();
        $visitor_id = $conn->insert_id;
        $stmt->close();

        // 2. Get Service Info for this Institute
        $sql_service = "SELECT token_prefix, name, avg_service_time_min FROM services WHERE id = $service_id AND institute_id = $inst_id";
        $service_res = $conn->query($sql_service);
        $service_row = $service_res->fetch_assoc();
        
        $prefix = $service_row['token_prefix'];
        $service_name = $service_row['name'];
        $est_time = $service_row['avg_service_time_min'];

        // 3. Generate Token (Scoped to Institute & Service)
        $today = date("Y-m-d");
        $sql_count = "SELECT COUNT(*) as count FROM queue_tokens WHERE institute_id = $inst_id AND service_id = $service_id AND DATE(generated_at) = '$today'";
        $count_res = $conn->query($sql_count);
        $new_seq = $count_res->fetch_assoc()['count'] + 1;
        
        $token_number = $prefix . '-' . str_pad($new_seq, 3, '0', STR_PAD_LEFT);

        // 4. Insert Token
        $stmt_token = $conn->prepare("INSERT INTO queue_tokens (institute_id, visitor_id, service_id, token_number) VALUES (?, ?, ?, ?)");
        $stmt_token->bind_param("iiis", $inst_id, $visitor_id, $service_id, $token_number);
        
        if ($stmt_token->execute()) {
             // Calculate Wait (Institute Scoped)
             $sql_pending = "SELECT COUNT(*) as pending FROM queue_tokens WHERE institute_id = $inst_id AND service_id = $service_id AND status = 'pending'";
             $people_ahead = $conn->query($sql_pending)->fetch_assoc()['pending'] - 1;
             if ($people_ahead < 0) $people_ahead = 0;
             $wait_time = $people_ahead * $est_time;
             
             // Reuse the display logic (inline for simplicity)
             ?>
             <!DOCTYPE html>
             <html lang="en">
             <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Your Token</title>
                <link rel="stylesheet" href="style.css">
                <style>
                    .token-card { background: white; text-align: center; padding: 40px; border-radius: 20px; box-shadow: var(--shadow-lg); max-width: 400px; margin: 40px auto; border-top: 6px solid var(--primary); }
                    .token-number { font-size: 4rem; font-weight: 800; color: var(--primary); margin: 20px 0; }
                    .btn-action { display: inline-block; margin-top: 20px; padding: 12px 24px; background: var(--border-light); text-decoration: none; color: var(--text-main); border-radius: 8px; font-weight: 600; }
                    .btn-print { background: var(--primary); color: white; margin-left: 10px; border:none; cursor:pointer;}
                    @media print { .btn-action { display: none; } }
                </style>
             </head>
             <body style="background: var(--bg-body);">
                 <div class="token-card">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5f/Emblem_of_Sri_Lanka.svg/1200px-Emblem_of_Sri_Lanka.svg.png" style="height: 50px;">
                        <h3 class="mt-4">Divisional Secretariat</h3>
                        <p class="text-muted"><?php echo date("Y-m-d H:i"); ?></p>
                        
                        <div class="token-number"><?php echo $token_number; ?></div>
                        
                        <div class="mb-4">
                            <strong>Service:</strong> <?php echo $service_name; ?><br>
                            <?php if ($people_ahead > 0): ?>
                                <span style="color: var(--danger);">People Ahead: <?php echo $people_ahead; ?></span><br>
                                Est. Wait: ~<?php echo $wait_time; ?> mins
                            <?php else: ?>
                                <span style="color: var(--secondary);">You are next!</span>
                            <?php endif; ?>
                        </div>

                        <div>
                            <a href="index.php?inst_id=<?php echo $inst_id; ?>" class="btn-action">Back</a>
                            <button onclick="window.print()" class="btn-action btn-print">Print Token</button>
                        </div>
                 </div>
                 <script>window.print();</script>
             </body>
             </html>
             <?php
        }
        $stmt_token->close();
    }
}
?>
