<?php
require 'auth.php';
Auth::requireRole(['admin', 'super_admin']);

$inst_id = $_SESSION['institute_id'];
if ($_SESSION['role'] == 'super_admin' && isset($_GET['inst_id'])) {
    $inst_id = intval($_GET['inst_id']);
}

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $prefix = $_POST['prefix'];
    $time = $_POST['avg_time'];
    $icon = $_POST['icon'];
    
    // Add Service
    if (isset($_POST['add_service'])) {
        $stmt = $conn->prepare("INSERT INTO services (institute_id, name, token_prefix, avg_service_time_min, icon) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $inst_id, $name, $prefix, $time, $icon);
        $stmt->execute();
    }
    // Delete
    if (isset($_POST['delete_id'])) {
        $did = $_POST['delete_id'];
        $conn->query("DELETE FROM services WHERE id=$did AND institute_id=$inst_id");
    }
}

$services = $conn->query("SELECT * FROM services WHERE institute_id = $inst_id");
$inst_name = $conn->query("SELECT name FROM institutes WHERE id=$inst_id")->fetch_assoc()['name'];

// Common Icon List
$icons = [
    'file-text' => 'File / General',
    'user' => 'Person',
    'users' => 'Group',
    'land-plot' => 'Land',
    'home' => 'Housing',
    'car' => 'Vehicle',
    'credit-card' => 'Payment',
    'briefcase' => 'Business',
    'baby' => 'Birth',
    'scroll' => 'Certificate'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Services | <?php echo $inst_name; ?></title>
    <?php include 'header_includes.php'; ?>
</head>
<body>

<div class="app-container">
    <?php include 'menu.php'; ?>

    <div class="counter-wrapper">
        <div class="glass-panel">
            <h3>Add Service (<?php echo $inst_name; ?>)</h3>
            <form method="POST" class="mt-4">
                <div class="mb-4">
                    <label>Service Name</label>
                    <input type="text" name="name" required placeholder="e.g. Land Registry">
                </div>
                <div class="mb-4">
                    <label>Token Prefix (1-2 chars)</label>
                    <input type="text" name="prefix" required placeholder="e.g. L" maxlength="5">
                </div>
                <div class="mb-4">
                    <label>Avg Time (Minutes)</label>
                    <input type="number" name="avg_time" required value="10">
                </div>
                
                <div class="mb-4">
                    <label>Icon</label>
                    <div class="icon-selector" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px;">
                        <?php foreach($icons as $key => $label): ?>
                        <label style="cursor: pointer; text-align: center; padding: 10px; border: 1px solid var(--border-light); border-radius: 8px;">
                            <input type="radio" name="icon" value="<?php echo $key; ?>" <?php echo $key=='file-text'?'checked':''; ?>>
                            <div style="margin-top: 5px;"><i data-lucide="<?php echo $key; ?>"></i></div>
                            <small style="font-size: 10px;"><?php echo $label; ?></small>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" name="add_service" class="btn btn-primary btn-full">Add Service</button>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Prefix</th>
                        <th>Avg Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $services->fetch_assoc()): ?>
                    <tr>
                        <td><i data-lucide="<?php echo $row['icon']; ?>"></i></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['token_prefix']; ?></td>
                        <td><?php echo $row['avg_service_time_min']; ?>m</td>
                        <td><span class="badge badge-completed"><?php echo $row['status']; ?></span></td>
                        <td>
                             <form method="POST" onsubmit="return confirm('Delete this service?');">
                                 <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                 <button class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;">Delete</button>
                             </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
