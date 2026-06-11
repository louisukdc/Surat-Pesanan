<?php
// auth.php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        $username = $_POST['username'];
        $password = md5($_POST['password']); // Simple MD5 as per legacy request

        $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            header("Location: dashboard.php");
            exit;
        } else {
            $_SESSION['error'] = "Invalid username or password!";
            header("Location: index.php");
            exit;
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Function to check if user is logged in
function checkAuth($allowed_roles = array()) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }
    
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        die("Access Denied.");
    }
}
?>
