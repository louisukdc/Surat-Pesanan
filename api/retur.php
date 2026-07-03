<!-- FILE RETUR/BARANG TIDAK SESUAI-->

<?php
// api/retur.php
require_once '../auth.php';
checkAuth();
require_once '../config.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['no_sp'])) {
            $no_sp = $conn->real_escape_string($_GET['no_sp']);
            $stmt = $conn->query("SELECT * FROM sp_pesanan WHERE no_sp='$no_sp'");
            
            $header = null;
            $items = [];
            while($row = $stmt->fetch_assoc()) {
                if(!$header) {
                    $header = [
                        'no_sp' => $row['no_sp'],
                        'tgl_sp' => $row['tgl_sp'],
                        'namasup' => $row['namasup']
                    ];
                }
                $items[] = [
                    'barang' => $row['barang'],
                    'qty_pesan' => $row['qty'],
                    'satuan' => $row['satuan'],
                    'qty_retur' => 0,
                    'alasan' => ''
                ];
            }
            echo json_encode(['header' => $header, 'items' => $items]);
            exit;
        }

        // List Retur
        $sql = "SELECT DISTINCT no_retur, tgl_retur, no_sp, user FROM sp_retur ORDER BY tgl_retur DESC LIMIT 50";
        $result = $conn->query($sql);
        $data = [];
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['no_retur']) || !isset($data['items']) || empty($data['items'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data payload']);
            exit;
        }

        $no_retur = $data['no_retur'];
        $tgl_retur = $data['tgl_retur'];
        $no_sp = $data['no_sp'];
        $user = $_SESSION['nik'];

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO sp_retur (no_retur, tgl_retur, no_sp, user, barang, qty_retur, satuan, alasan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($data['items'] as $item) {
                if ((float)$item['qty_retur'] > 0) {
                    $barang = $item['barang'];
                    $qty_retur = (float)$item['qty_retur'];
                    $satuan = $item['satuan'];
                    $alasan = isset($item['alasan']) ? $item['alasan'] : '';
                    
                    $stmt->bind_param("sssssdss", $no_retur, $tgl_retur, $no_sp, $user, $barang, $qty_retur, $satuan, $alasan);
                    if(!$stmt->execute()) throw new Exception("Insert failed: " . $stmt->error);
                }
            }
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Retur barang berhasil disimpan']);
        } catch(Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
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
