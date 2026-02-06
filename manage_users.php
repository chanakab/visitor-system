<?php
require 'auth.php';
Auth::requireRole(['admin', 'super_admin']);

$inst_id = $_SESSION['institute_id'];
if ($_SESSION['role'] == 'super_admin' && isset($_GET['inst_id'])) {
    $inst_id = intval($_GET['inst_id']);
}

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        $username = $_POST['username'];
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $fullname = $_POST['fullname'];
        $role = $_POST['role'];
        $counter = isset($_POST['counter']) ? $_POST['counter'] : null;
        
        $stmt = $conn->prepare("INSERT INTO users (institute_id, username, password_hash, full_name, role, counter_number) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $inst_id, $username, $pass, $fullname, $role, $counter);
        $stmt->execute();
    }
}

$users = $conn->query("SELECT * FROM users WHERE institute_id = $inst_id ORDER BY role");
$inst_name = $conn->query("SELECT name FROM institutes WHERE id=$inst_id")->fetch_assoc()['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | <?php echo $inst_name; ?></title>
    <?php include 'header_includes.php'; ?>
</head>
<body>

<div class="app-container">
    <?php include 'menu.php'; ?>

    <div class="counter-wrapper">
        <div class="glass-panel">
            <h3>Create User (<?php echo $inst_name; ?>)</h3>
            <form method="POST" class="mt-4">
                <div class="mb-4">
                    <label>Full Name</label>
                    <input type="text" name="fullname" required>
                </div>
                <div class="mb-4">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="mb-4">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="mb-4">
                    <label>Role</label>
                    <select name="role">
                        <option value="officer">Officer</option>
                        <option value="admin">Institute Admin</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label>Counter No (For Officers)</label>
                    <input type="text" name="counter" placeholder="e.g. 05">
                </div>
                <button type="submit" name="add_user" class="btn btn-primary btn-full">Create User</button>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Counter</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $row['username']; ?></strong></td>
                        <td><?php echo $row['full_name']; ?></td>
                        <td><span class="badge badge-called"><?php echo $row['role']; ?></span></td>
                        <td><?php echo $row['counter_number'] ? '#'.$row['counter_number'] : '-'; ?></td>
                        <td><?php echo $row['status']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
