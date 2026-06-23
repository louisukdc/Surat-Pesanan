<?php
// api/supplier.php
require_once '../auth.php';
checkAuth();
require_once '../config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['kode'])) {
            $kode = $_GET['kode'];
            $stmt = $conn->prepare("SELECT * FROM m_supplier WHERE KodeSupplier = ?");
            $stmt->bind_param("s", $kode);
            $stmt->execute();
            $result = $stmt->get_result();
            if($row = $result->fetch_assoc()) {
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Not found']);
            }
        } else {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';

            if ($page < 1) $page = 1;
            if ($limit < 1 || $limit > 200) $limit = 50;
            $offset = ($page - 1) * $limit;

            $where = "1=1";
            if (!empty($search)) {
                $where .= " AND (KodeSupplier LIKE '%$search%' OR NamaSupplier LIKE '%$search%')";
            }

            $count_sql = "SELECT COUNT(*) as total FROM m_supplier WHERE $where";
            $total_res = $conn->query($count_sql);
            $total_row = $total_res->fetch_assoc();
            $total_records = (int)$total_row['total'];

            $sql = "SELECT KodeSupplier, NamaSupplier, ContactPerson, Telp1, Kota1 FROM m_supplier WHERE $where ORDER BY IdSupplier DESC LIMIT $offset, $limit";
            $result = $conn->query($sql);
            
            $data = [];
            if ($result) {
                while($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
            }

            echo json_encode([
                'data' => $data,
                'total' => $total_records,
                'page' => $page,
                'total_pages' => ceil($total_records / $limit)
            ]);
        }
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST; // Fallback for form-data

        $kode = trim(isset($input['KodeSupplier']) ? $input['KodeSupplier'] : '');
        $nama = isset($input['NamaSupplier']) ? $input['NamaSupplier'] : '';
        $alamat = isset($input['Alamat1']) ? $input['Alamat1'] : '';
        $kota = isset($input['Kota1']) ? $input['Kota1'] : '';
        $npwp = isset($input['NPWP']) ? $input['NPWP'] : '';
        $telp = isset($input['Telp1']) ? $input['Telp1'] : '';
        $cp = isset($input['ContactPerson']) ? $input['ContactPerson'] : '';

        if(empty($kode) || empty($nama)) {
            http_response_code(400);
            echo json_encode(['error' => 'Kode and Nama are required']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO m_supplier (KodeSupplier, NamaSupplier, Alamat1, Kota1, NPWP, Telp1, ContactPerson, tanda, NamaInvoice) VALUES (?, ?, ?, ?, ?, ?, ?, '', '')");
        $stmt->bind_param("sssssss", $kode, $nama, $alamat, $kota, $npwp, $telp, $cp);
        
        if($stmt->execute()) {
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Supplier created']);
        } else {
            throw new Exception($conn->error);
        }
        exit;
    }

    if ($method === 'PATCH' || $method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        $kode = trim(isset($input['KodeSupplier']) ? $input['KodeSupplier'] : '');
        $nama = isset($input['NamaSupplier']) ? $input['NamaSupplier'] : '';
        $alamat = isset($input['Alamat1']) ? $input['Alamat1'] : '';
        $kota = isset($input['Kota1']) ? $input['Kota1'] : '';
        $npwp = isset($input['NPWP']) ? $input['NPWP'] : '';
        $telp = isset($input['Telp1']) ? $input['Telp1'] : '';
        $cp = isset($input['ContactPerson']) ? $input['ContactPerson'] : '';

        if(empty($kode)) {
            http_response_code(400);
            echo json_encode(['error' => 'KodeSupplier is required for update']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE m_supplier SET NamaSupplier=?, Alamat1=?, Kota1=?, NPWP=?, Telp1=?, ContactPerson=? WHERE KodeSupplier=?");
        $stmt->bind_param("sssssss", $nama, $alamat, $kota, $npwp, $telp, $cp, $kode);
        
        if($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Supplier updated']);
        } else {
            throw new Exception($conn->error);
        }
        exit;
    }

    if ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $kode = isset($input['KodeSupplier']) ? $input['KodeSupplier'] : '';
        if(empty($kode)) {
            http_response_code(400);
            echo json_encode(['error' => 'KodeSupplier is required for deletion']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM m_supplier WHERE KodeSupplier = ?");
        $stmt->bind_param("s", $kode);
        if($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Supplier deleted']);
        } else {
            throw new Exception($conn->error);
        }
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
