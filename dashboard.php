<?php
require 'auth.php';
Auth::requireRole(['admin', 'super_admin']);

// GOD MODE: Allow Super Admin to override Institute ID
$inst_id = $_SESSION['institute_id'];
if ($_SESSION['role'] == 'super_admin' && isset($_GET['inst_id'])) {
    $inst_id = intval($_GET['inst_id']);
}

$inst_data = $conn->query("SELECT name FROM institutes WHERE id = $inst_id")->fetch_assoc();
$inst_name = $inst_data ? $inst_data['name'] : "Unknown Institute";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo $inst_name; ?></title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<div class="app-container">
    
    <!-- Navigation -->
    <?php include 'menu.php'; ?>

    <div class="dashboard-grid">
        <div class="stat-card" style="border-left-color: var(--primary);">
            <div class="stat-title">Total Visits Today</div>
            <div class="stat-val" id="kpi-total">0</div>
        </div>
        <div class="stat-card" style="border-left-color: var(--accent);">
            <div class="stat-title">Avg Wait Time</div>
            <div class="stat-val"><span id="kpi-wait">0</span> min</div>
        </div>
        <div class="stat-card" style="border-left-color: var(--secondary);">
            <div class="stat-title">Completed</div>
            <div class="stat-val" id="kpi-completed">0</div>
        </div>
        <div class="stat-card" style="border-left-color: var(--danger);">
            <div class="stat-title">Pending</div>
            <div class="stat-val" id="kpi-pending">0</div>
        </div>
    </div>

    <div class="counter-wrapper">
        <div class="table-wrapper">
            <div style="padding: 20px;"><h3>Live Activity (<?php echo $inst_name; ?>)</h3></div>
            <table>
                <thead>
                    <tr>
                        <th>Token</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Officer</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody id="live-table-body"></tbody>
            </table>
        </div>
        <div class="glass-panel">
             <h3>Status Distribution</h3>
             <canvas id="mainChart"></canvas>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
    const ctx = document.getElementById('mainChart').getContext('2d');
    const mainChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Completed'],
            datasets: [{ data: [0, 0], backgroundColor: ['#ef4444', '#10b981'], borderWidth: 0 }]
        },
        options: { responsive: true, cutout: '70%' }
    });

    async function updateDashboard() {
        try {
            // Pass inst_id explicitly for API if needed, or API needs update?
            // Let's update API to accept override too or use this page's context.
            // Actually API relies on session. We should fix API too. 
            // Workaround: Pass GET param to API.
            const res = await fetch('api.php?action=dashboard_stats&inst_id=<?php echo $inst_id; ?>');
            const data = await res.json();

            document.getElementById('kpi-total').innerText = data.total;
            document.getElementById('kpi-wait').innerText = data.avg_wait;
            document.getElementById('kpi-pending').innerText = data.pending;
            document.getElementById('kpi-completed').innerText = data.completed;

            mainChart.data.datasets[0].data = [data.pending, data.completed];
            mainChart.update();

            const tbody = document.getElementById('live-table-body');
            tbody.innerHTML = '';
            data.recent.forEach(row => {
                let badge = 'badge-pending';
                if(row.status=='called') badge='badge-called';
                if(row.status=='completed') badge='badge-completed';
                
                tbody.innerHTML += `<tr>
                    <td><strong>${row.token_number}</strong></td>
                    <td>${row.service}</td>
                    <td><span class="badge ${badge}">${row.status}</span></td>
                    <td>${row.officer || '-'}</td>
                    <td>${row.generated_at.split(' ')[1]}</td>
                </tr>`;
            });
        } catch (e) { console.error(e); }
    }
    updateDashboard();
    setInterval(updateDashboard, 5000);
</script>
</body>
</html>
