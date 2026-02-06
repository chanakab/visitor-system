<?php
require 'auth.php';
Auth::requireRole(['super_admin']);

// Handle Institute Creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_institute'])) {
    $name = $_POST['name'];
    $code = $_POST['code'];
    $addr = $_POST['address'];
    $conn->query("INSERT INTO institutes (name, code, address) VALUES ('$name', '$code', '$addr')");
}

$institutes = $conn->query("SELECT * FROM institutes");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin - Manage Institutes</title>
    <?php include 'header_includes.php'; ?>
</head>
<body>

<div class="app-container">
    <?php include 'menu.php'; ?>

    <div class="counter-wrapper">
        <!-- Form -->
        <div class="glass-panel">
            <h3><i data-lucide="building"></i> Create New Institute</h3>
            <p class="text-muted mb-4">Add a new Divisional Secretariat to the system.</p>
            <form method="POST" class="mt-4">
                <div class="mb-4">
                    <label>Institute Name</label>
                    <input type="text" name="name" required placeholder="e.g. Kandy DS Office">
                </div>
                <div class="mb-4">
                    <label>Institute Code</label>
                    <input type="text" name="code" required placeholder="e.g. KDY-001">
                </div>
                <div class="mb-4">
                    <label>Address</label>
                    <input type="text" name="address" required placeholder="Full Address">
                </div>
                <button type="submit" name="add_institute" class="btn btn-primary btn-full">Create Institute</button>
            </form>
        </div>

        <!-- List -->
        <div class="table-wrapper" style="grid-column: span 1;">
            <div style="padding: 20px;"><h3>Active Institutes</h3></div>
            <table class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $institutes->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><span class="badge badge-called"><?php echo $row['code']; ?></span></td>
                        <td>
                            <!-- The Magic Link: Context Switching -->
                            <a href="dashboard.php?inst_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                <i data-lucide="settings" style="width:14px;"></i> Manage
                            </a>
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
