<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
// Use Query Param if Super Admin and set, else Session
$inst_id = isset($_SESSION['institute_id']) ? $_SESSION['institute_id'] : 0;
if ($role == 'super_admin' && isset($_GET['inst_id'])) {
    $inst_id = $_GET['inst_id'];
}

function renderLink($url, $label, $icon = null, $extra_params = '') {
    $current = basename($_SERVER['PHP_SELF']);
    // Append params if needed (to keep context)
    if (!empty($extra_params)) {
        $url .= (strpos($url, '?') !== false ? '&' : '?') . $extra_params;
    }
    
    $active_class = ($current == basename($url)) ? 'active-nav' : '';
    $icon_html = $icon ? "<i data-lucide='$icon'></i>" : "";
    echo "<a href='$url' class='nav-link $active_class'>$icon_html $label</a>";
}

$ctx_param = ($role == 'super_admin' && isset($_GET['inst_id'])) ? "inst_id=" . $_GET['inst_id'] : "";
?>

<nav class="main-nav glass-panel">
    <div class="nav-brand">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5f/Emblem_of_Sri_Lanka.svg/1200px-Emblem_of_Sri_Lanka.svg.png" alt="Logo">
        <span>Smart Visitor System</span>
    </div>

    <div class="nav-items">
        <?php if ($role == 'super_admin'): ?>
            <?php renderLink('super_admin.php', 'All Institutes', 'building'); ?>
        <?php endif; ?>

        <?php if ($role == 'admin' || ($role == 'super_admin' && !empty($ctx_param))): ?>
            <?php renderLink('dashboard.php', 'Dashboard', 'layout-dashboard', $ctx_param); ?>
            <?php renderLink('manage_services.php', 'Services', 'settings', $ctx_param); ?>
            <?php renderLink('manage_users.php', 'Users & Officers', 'users', $ctx_param); ?>
        <?php endif; ?>
        
        <?php if ($inst_id > 0): ?>
             <a href="index.php?inst_id=<?php echo $inst_id; ?>" target="_blank" class="nav-link special-link"><i data-lucide="monitor"></i> Kiosk</a>
        <?php endif; ?>
    </div>

    <div class="nav-user">
        <?php if ($role): ?>
            <span class="user-badge"><?php echo ucfirst($role); ?></span>
            <a href="logout.php" class="btn-logout"><i data-lucide="log-out"></i></a>
        <?php else: ?>
            <a href="login.php" class="nav-link">Login</a>
        <?php endif; ?>
    </div>
</nav>

<style>
.main-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 30px;
    margin-bottom: 30px;
    border-radius: 16px;
}
.nav-brand { display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 1.1rem; color: var(--text-main); }
.nav-brand img { height: 40px; }
.nav-items { display: flex; gap: 10px; }
.nav-link { 
    display: flex; gap: 8px; align-items: center; 
    padding: 10px 18px; 
    border-radius: 10px; 
    text-decoration: none; 
    color: var(--text-muted); 
    font-weight: 600; 
    transition: all 0.2s;
}
.nav-link:hover { background: #f1f5f9; color: var(--primary); }
.active-nav { background: #eef2ff; color: var(--primary); }
.special-link { border: 1px dashed var(--border-light); }

.nav-user { display: flex; align-items: center; gap: 15px; }
.user-badge { background: #f3f4f6; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; }
.btn-logout { 
    color: var(--danger); 
    padding: 8px; 
    border-radius: 8px; 
    display: flex; 
    align-items: center; 
    justify-content: center;
    transition: background 0.2s;
}
.btn-logout:hover { background: #fee2e2; }

@media (max-width: 900px) {
    .main-nav { flex-direction: column; gap: 20px; }
    .nav-items { flex-wrap: wrap; justify-content: center; }
}
</style>
