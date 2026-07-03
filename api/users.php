<?php
// api/users.php
require_once '../auth.php';
checkAuth();

// Only admin can access user API
if (!checkMenuAccess(99)) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden: Admins only']);
    exit;
}

require_once '../config.php';
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

try {
    // Cari karyawan dari datadasar (untuk Select2)
    if ($method === 'GET' && isset($_GET['search_nik'])) {
        $q = $conn->real_escape_string($_GET['search_nik']);
        $sql = "SELECT NIP as nik, Nama as nama FROM datadasar 
                WHERE NIP LIKE '%$q%' OR Nama LIKE '%$q%' LIMIT 20";
        $res = $conn->query($sql);
        $data = [];
        while($r = $res->fetch_assoc()){
            $data[] = $r;
        }
        echo json_encode($data);
        exit;
    }

    if ($method === 'GET') {
        // List all users from m_user, grouped by NIK
        $sql = "SELECT u.NIK, d.Nama, GROUP_CONCAT(u.NoMenu) as menus 
                FROM m_user u 
                LEFT JOIN datadasar d ON u.NIK = d.NIP 
                GROUP BY u.NIK";
        $result = $conn->query($sql);
        $data = [];
        while($row = $result->fetch_assoc()) {
            $row['menus'] = $row['menus'] ? explode(',', $row['menus']) : [];
            $data[] = $row;
        }
        echo json_encode($data);
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $nik = isset($input['nik']) ? trim($input['nik']) : '';
        $password = isset($input['password']) ? $input['password'] : '';
        $menus = isset($input['menus']) && is_array($input['menus']) ? $input['menus'] : [];

        if(empty($nik)) {
            http_response_code(400);
            echo json_encode(['error' => 'NIK is required']);
            exit;
        }

        // Dapatkan nama user dari datadasar
        $stmt_nm = $conn->prepare("SELECT Nama FROM datadasar WHERE NIP = ?");
        $stmt_nm->bind_param("s", $nik);
        $stmt_nm->execute();
        $res_nm = $stmt_nm->get_result();
        $namaUser = 'Unknown';
        if($r = $res_nm->fetch_assoc()) {
            $namaUser = $r['Nama'];
        }

        // Dapatkan password lama jika tidak diisi password baru
        $hashed_password = '';
        if(empty($password)) {
            $stmt_pw = $conn->prepare("SELECT password FROM m_user WHERE NIK = ? LIMIT 1");
            $stmt_pw->bind_param("s", $nik);
            $stmt_pw->execute();
            $res_pw = $stmt_pw->get_result();
            if($r = $res_pw->fetch_assoc()) {
                $hashed_password = $r['password'];
            }
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        }

        if(empty($hashed_password) && empty($menus)) {
            // Nothing to do
            http_response_code(400);
            echo json_encode(['error' => 'Password required for new user']);
            exit;
        }

        $conn->begin_transaction();
        try {
            // Hapus akses lama
            $stmt_del = $conn->prepare("DELETE FROM m_user WHERE NIK = ?");
            $stmt_del->bind_param("s", $nik);
            $stmt_del->execute();

            // Insert akses baru
            if(count($menus) > 0) {
                $stmt_ins = $conn->prepare("INSERT INTO m_user (NIK, NoMenu, NamaUser, password, fidfile, fidcode, Tanda, Status, userdeleted) VALUES (?, ?, ?, ?, '', '', '', '', '')");
                if (!$stmt_ins) {
                    throw new Exception("Prepare insert failed: " . $conn->error);
                }
                foreach($menus as $m) {
                    $m_int = (int)$m;
                    $stmt_ins->bind_param("siss", $nik, $m_int, $namaUser, $hashed_password);
                    if (!$stmt_ins->execute()) {
                        throw new Exception("Insert failed: " . $stmt_ins->error);
                    }
                }
            }

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'User saved successfully']);
        } catch(Exception $e) {
            $conn->rollback();
            throw $e;
        }
        exit;
    }

    if ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $nik = isset($input['nik']) ? $input['nik'] : '';
        if(empty($nik)) {
            http_response_code(400);
            echo json_encode(['error' => 'NIK is required for deletion']);
            exit;
        }
        
        if ($nik == $_SESSION['nik']) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete your own account']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM m_user WHERE NIK = ?");
        $stmt->bind_param("s", $nik);
        
        if($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            throw new Exception($conn->error);
        }
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
