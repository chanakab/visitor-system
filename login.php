<?php
require 'db.php';
require 'auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (Auth::login($username, $password)) {
        Auth::redirectHome();
    } else {
        $error = "Invalid credentials or inactive account.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - System Access</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background: linear-gradient(135deg, #4f46e5 0%, #0ea5e9 100%);">

<div class="app-container" style="display: flex; justify-content: center; align-items: center; min-height: 90vh;">
    
    <div class="glass-panel" style="width: 100%; max-width: 400px; background: rgba(255,255,255,0.95); padding: 40px;">
        <div class="text-center mb-4">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5f/Emblem_of_Sri_Lanka.svg/1200px-Emblem_of_Sri_Lanka.svg.png" alt="Logo" style="height: 60px;">
            <h2 class="mt-4">System Login</h2>
            <p style="color: var(--text-muted);">Secure Gateway</p>
        </div>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Username</label>
                <input type="text" name="username" required placeholder="User ID">
            </div>
            
            <div class="mb-4">
                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Password</label>
                <input type="password" name="password" required placeholder="••••••••" style="width: 100%; padding: 14px; border: 2px solid var(--border-light); border-radius: 8px;">
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg">Login</button>
        </form>

        <div class="text-center mt-4">
            <a href="index.php" style="color: var(--primary); text-decoration: none;">Go to Kiosk Mode</a>
        </div>
    </div>

</div>

</body>
</html>
