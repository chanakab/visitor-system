<?php
session_start();
require_once 'db.php';

class Auth {
    // Login Function
    public static function login($username, $password) {
        global $conn;
        $username = $conn->real_escape_string($username);
        
        $sql = "SELECT * FROM users WHERE username = '$username' AND status='active'";
        $result = $conn->query($sql);
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            // Verify Password
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['institute_id'] = $user['institute_id'];
                $_SESSION['username'] = $user['username'];
                return true;
            }
        }
        return false;
    }

    // Check Login Status
    public static function check() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }
    }

    // Check Permissions
    public static function requireRole($roles = []) {
        self::check();
        if (is_array($roles) && !in_array($_SESSION['role'], $roles)) {
             die("Access Denied: You do not have permission to view this page.");
        }
    }

    // Redirect based on role
    public static function redirectHome() {
        if (!isset($_SESSION['role'])) return;
        
        switch ($_SESSION['role']) {
            case 'super_admin':
                header("Location: super_admin.php");
                break;
            case 'admin':
                header("Location: dashboard.php");
                break;
            case 'officer':
                header("Location: counter.php");
                break;
            default:
                header("Location: login.php");
        }
        exit();
    }
}
?>
