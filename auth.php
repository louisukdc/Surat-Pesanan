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
        // If it's an API request (URL contains /api/), return JSON instead of redirecting
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Unauthorized. Please login first."]);
            exit;
        }
        // Otherwise redirect to the main login page (using absolute path to avoid 404 in subfolders)
        header("Location: /index.php");
        exit;
    }
    
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Access Denied."]);
            exit;
        }
        die("Access Denied.");
    }
}
?>
