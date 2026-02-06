<?php
require 'auth.php';
header('Content-Type: application/json');

Auth::check();
$inst_id = $_SESSION['institute_id'];

// GOD MODE: Allow Override
if (isset($_GET['inst_id']) && $_SESSION['role'] == 'super_admin') {
    $inst_id = intval($_GET['inst_id']);
}

$today = date("Y-m-d");
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'dashboard_stats') {
    $total = $conn->query("SELECT COUNT(*) as cnt FROM queue_tokens WHERE institute_id=$inst_id AND DATE(generated_at)='$today'")->fetch_assoc()['cnt'];
    
    $pending = $conn->query("SELECT COUNT(*) as cnt FROM queue_tokens WHERE institute_id=$inst_id AND status='pending'")->fetch_assoc()['cnt'];
    
    $completed = $conn->query("SELECT COUNT(*) as cnt FROM queue_tokens WHERE institute_id=$inst_id AND status='completed' AND DATE(generated_at)='$today'")->fetch_assoc()['cnt'];
    
    $sql_wait = "SELECT AVG(TIMESTAMPDIFF(MINUTE, generated_at, called_at)) as avg_wait FROM queue_tokens WHERE institute_id=$inst_id AND DATE(generated_at)='$today' AND status IN ('called', 'completed')";
    $res_wait = $conn->query($sql_wait);
    $avg_wait = ($res_wait && $res_wait->num_rows > 0) ? round($res_wait->fetch_assoc()['avg_wait'] ?? 0, 1) : 0;
    
    // Recent
    $sql_recent = "SELECT t.token_number, s.name as service, t.status, t.generated_at, u.username as officer
                   FROM queue_tokens t 
                   JOIN services s ON t.service_id = s.id 
                   LEFT JOIN users u ON t.assigned_user_id = u.id
                   WHERE t.institute_id = $inst_id
                   ORDER BY t.id DESC LIMIT 5";
    $recent = [];
    $res = $conn->query($sql_recent);
    if ($res) {
        while($row = $res->fetch_assoc()) $recent[] = $row;
    }

    echo json_encode(['total'=>$total, 'avg_wait'=>$avg_wait, 'pending'=>$pending, 'completed'=>$completed, 'recent'=>$recent]);
    exit;
}

if ($action == 'officer_state') {
    $pending = $conn->query("SELECT COUNT(*) as cnt FROM queue_tokens WHERE institute_id=$inst_id AND status='pending'")->fetch_assoc()['cnt'];
    echo json_encode(['pending' => $pending]);
    exit;
}
?>
