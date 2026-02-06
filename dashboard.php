<?php
require 'db.php';

// 1. Calculations for KPIs
// Total Visitors Today
$today = date("Y-m-d");
$sql_total = "SELECT COUNT(*) as cnt FROM queue_tokens WHERE DATE(generated_at) = '$today'";
$res_total = $conn->query($sql_total);
$total_today = $res_total->fetch_assoc()['cnt'];

// Avg Wait Time (Called - Generated)
$sql_wait = "SELECT AVG(TIMESTAMPDIFF(MINUTE, generated_at, called_at)) as avg_wait FROM queue_tokens WHERE DATE(generated_at) = '$today' AND status IN ('called', 'completed')";
$res_wait = $conn->query($sql_wait);
$avg_wait = round($res_wait->fetch_assoc()['avg_wait'], 1);

// Active Counters
$sql_active = "SELECT COUNT(*) as cnt FROM counters WHERE status='active'";
$res_active = $conn->query($sql_active);
$active_counters = $res_active->fetch_assoc()['cnt'];

// Recent Tokens List
$sql_tokens = "SELECT t.*, s.name as service_name, c.counter_number FROM queue_tokens t 
               LEFT JOIN services s ON t.service_id = s.id 
               LEFT JOIN counters c ON t.assigned_counter_id = c.id
               ORDER BY t.id DESC LIMIT 10";
$recent_tokens = $conn->query($sql_tokens);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - DS Office</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 30px; border-radius: 16px; box-shadow: var(--shadow-soft); text-align: center; }
        .stat-val { font-size: 3rem; font-weight: 800; color: var(--primary-color); margin: 10px 0; }
        .stat-label { color: var(--text-light); text-transform: uppercase; font-size: 0.9rem; letter-spacing: 1px; }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-soft); }
        th, td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #f3f4f6; }
        th { background: #f9fafb; color: var(--text-light); font-weight: 600; font-size: 0.9rem; }
        tr:last-child td { border: none; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-called { background: #dbeafe; color: #2563eb; }
        .status-completed { background: #d1fae5; color: #059669; }
        
        .nav-links { margin-bottom: 30px; }
        .nav-links a { margin-right: 20px; text-decoration: none; color: var(--text-light); font-weight: 600; }
        .nav-links a:hover { color: var(--primary-color); }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="nav-links">
        <a href="index.php">← Kiosk Home</a>
        <a href="counter.php">Officer Portal</a>
    </div>

    <header class="kiosk-header" style="border-radius: 16px; margin-bottom: 40px;">
        <div class="titles">
            <h1>Divisional Secretariat</h1>
            <h2>Executive Dashboard</h2>
            <p>Real-time analytics for <?php echo date("F j, Y"); ?></p>
        </div>
    </header>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-val"><?php echo $total_today; ?></div>
            <div class="stat-label">Total Visitors Today</div>
        </div>
        <div class="stat-card">
            <div class="stat-val"><?php echo $avg_wait; ?>m</div>
            <div class="stat-label">Avg. Wait Time</div>
        </div>
        <div class="stat-card">
            <div class="stat-val"><?php echo $active_counters; ?></div>
            <div class="stat-label">Active Counters</div>
        </div>
    </div>

    <h3 style="margin-bottom: 20px; color: var(--text-main);">Recent Activity</h3>
    <table>
        <thead>
            <tr>
                <th>Token</th>
                <th>Service</th>
                <th>Status</th>
                <th>Counter</th>
                <th>Wait Time</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $recent_tokens->fetch_assoc()): ?>
            <tr>
                <td><strong><?php echo $row['token_number']; ?></strong></td>
                <td><?php echo $row['service_name']; ?></td>
                <td>
                    <span class="status-badge status-<?php echo $row['status']; ?>">
                        <?php echo ucfirst($row['status']); ?>
                    </span>
                </td>
                <td><?php echo $row['counter_number'] ? '#'.$row['counter_number'] : '-'; ?></td>
                <td>
                    <?php 
                    if ($row['called_at']) {
                        $start = strtotime($row['generated_at']);
                        $end = strtotime($row['called_at']);
                        echo round(($end - $start) / 60) . " min";
                    } else {
                        echo "-";
                    }
                    ?>
                </td>
                <td><?php echo date("H:i", strtotime($row['generated_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
