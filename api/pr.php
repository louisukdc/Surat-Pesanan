<!-- FILE PURCHASE REQUEST/fitur pesanan -->

<?php
// api/pr.php
require_once '../auth.php';
checkAuth();
require_once '../config.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['no_pr'])) {
            $no_pr = $conn->real_escape_string($_GET['no_pr']);
            $stmt = $conn->query("SELECT * FROM sp_permintaan WHERE no_pr='$no_pr'");
            
            $header = null;
            $items = [];
            while($row = $stmt->fetch_assoc()) {
                if(!$header) {
                    $header = [
                        'no_pr' => $row['no_pr'],
                        'tgl_pr' => $row['tgl_pr'],
                        'unit' => $row['unit'],
                        'keterangan' => $row['keterangan']
                    ];
                }
                $items[] = [
                    'barang' => $row['barang'],
                    'qty' => $row['qty'],
                    'satuan' => $row['satuan'],
                    'harga' => 0,
                    'total' => 0
                ];
            }
            echo json_encode(['header' => $header, 'items' => $items]);
            exit;
        }

        $status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
        
        $where = "1=1";
        if (!empty($status)) {
            $where .= " AND status = '$status'";
        }

        // Return distinct PR headers
        $sql = "SELECT DISTINCT no_pr, tgl_pr, unit, user, status, alasan_tolak FROM sp_permintaan WHERE $where ORDER BY tgl_pr DESC";
        $result = $conn->query($sql);
        
        $data = [];
        if ($result) {
            while($row = $result->fetch_assoc()) {
                // Fetch items count for summary
                $no_pr = $row['no_pr'];
                $item_res = $conn->query("SELECT COUNT(*) as c FROM sp_permintaan WHERE no_pr='$no_pr'");
                $row['item_count'] = $item_res->fetch_assoc()['c'];
                $data[] = $row;
            }
        }
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['no_pr']) || !isset($data['items']) || empty($data['items'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data payload']);
            exit;
        }

        $no_pr = $data['no_pr'];
        $tgl_pr = $data['tgl_pr'];
        $unit = $data['unit'];
        $user = $_SESSION['nik'];
        $keterangan = isset($data['keterangan']) ? $data['keterangan'] : '';

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO sp_permintaan (no_pr, tgl_pr, unit, user, barang, qty, satuan, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($data['items'] as $item) {
                $barang = $item['barang'];
                $qty = (float)$item['qty'];
                $satuan = $item['satuan'];
                
                $stmt->bind_param("sssssdss", $no_pr, $tgl_pr, $unit, $user, $barang, $qty, $satuan, $keterangan);
                if(!$stmt->execute()) throw new Exception("Insert failed: " . $stmt->error);
            }
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Permintaan berhasil disimpan']);
        } catch(Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    if ($method === 'PATCH') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['no_pr']) || !isset($data['action'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data payload']);
            exit;
        }

        $no_pr = $data['no_pr'];
        $action = $data['action']; // 'approve' or 'reject'
        
        if ($action === 'approve') {
            $stmt = $conn->prepare("UPDATE sp_permintaan SET status = 'Approved' WHERE no_pr = ?");
            $stmt->bind_param("s", $no_pr);
        } else if ($action === 'reject') {
            $alasan = isset($data['alasan']) ? $data['alasan'] : 'Tidak ada alasan';
            $stmt = $conn->prepare("UPDATE sp_permintaan SET status = 'Rejected', alasan_tolak = ? WHERE no_pr = ?");
            $stmt->bind_param("ss", $alasan, $no_pr);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            exit;
        }

        if($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => "Permintaan $no_pr berhasil di-$action"]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => $stmt->error]);
        }
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
