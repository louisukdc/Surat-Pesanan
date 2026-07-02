<?php
// auth.php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Get user by NIK
        $stmt = $conn->prepare("
            SELECT u.id, u.NIK, u.password, d.Nama 
            FROM m_user u 
            LEFT JOIN datadasar d ON u.NIK = d.NIP 
            WHERE u.NIK = ? LIMIT 1
        ");
        
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['nik'] = $row['NIK'];
                $_SESSION['nama'] = $row['Nama'] ? $row['Nama'] : 'User ' . $row['NIK'];
                
                // Load allowed menus
                $menus = [];
                $menu_stmt = $conn->prepare("SELECT NoMenu FROM m_user WHERE NIK = ?");
                $menu_stmt->bind_param("s", $row['NIK']);
                $menu_stmt->execute();
                $menu_res = $menu_stmt->get_result();
                while ($m = $menu_res->fetch_assoc()) {
                    $menus[] = $m['NoMenu'];
                }
                $_SESSION['allowed_menus'] = $menus;

                header("Location: dashboard.php");
                exit;
            } else {
                $_SESSION['error'] = "Password salah!";
                header("Location: index.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "NIK tidak ditemukan!";
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
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Unauthorized. Please login first."]);
            exit;
        }
        header("Location: /sp_umum/index.php"); // Or whatever the root path is, safer relative:
        // Because we might be in api/, we can redirect to absolute path if we know it.
        // Let's just use a relative redirect that works from root
        $base = (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) ? '../index.php' : 'index.php';
        header("Location: " . $base);
        exit;
    }
}

function checkMenuAccess($noId) {
    if (!isset($_SESSION['allowed_menus'])) return false;
    return in_array($noId, $_SESSION['allowed_menus']);
}
?>
