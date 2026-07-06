<?php
// auth.php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Get user by NIK from hrd.datadasar
        $stmt_hrd = $hrd_conn->prepare("SELECT NIP, Nama, password, encrypt_pass FROM datadasar WHERE NIP = ? LIMIT 1");
        
        if ($stmt_hrd === false) {
            die("Prepare failed: " . $hrd_conn->error);
        }
        $stmt_hrd->bind_param("s", $username);
        $stmt_hrd->execute();
        $result = $stmt_hrd->get_result();

        if ($row = $result->fetch_assoc()) {
            $password_md5 = md5($password);
            if ($password === $row['password'] || $password_md5 === $row['encrypt_pass']) {
                // If authenticated, check if they have sp_user access
                $stmt_sp = $conn->prepare("SELECT id FROM sp_user WHERE NIK = ? LIMIT 1");
                $stmt_sp->bind_param("s", $row['NIP']);
                $stmt_sp->execute();
                $res_sp = $stmt_sp->get_result();
                
                if ($sp_row = $res_sp->fetch_assoc()) {
                    $_SESSION['user_id'] = $sp_row['id'];
                } else {
                    $_SESSION['user_id'] = time(); // Temporary ID if they don't have menus yet
                }

                $_SESSION['nik'] = $row['NIP'];
                $_SESSION['nama'] = $row['Nama'] ? $row['Nama'] : 'User ' . $row['NIP'];
                
                // Load allowed menus
                $menus = [];
                $menu_stmt = $conn->prepare("SELECT NoMenu FROM sp_user WHERE NIK = ?");
                $menu_stmt->bind_param("s", $row['NIP']);
                $menu_stmt->execute();
                $menu_res = $menu_stmt->get_result();
                while ($m = $menu_res->fetch_assoc()) {
                    $menus[] = $m['NoMenu'];
                }
                
                // Jika user memiliki akses '0' (legacy admin), berikan akses penuh ke semua menu baru
                if (in_array(0, $menus)) {
                    $menus = array_unique(array_merge($menus, [99, 3443, 3444, 3445, 3446]));
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
