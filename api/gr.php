<!-- FILE PENERIMAAN BARANG/good receipt-->

<?php
// api/gr.php
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
                    'qty_terima' => $row['qty'], // default to full receipt
                    'batch_no' => '',
                    'exp_date' => ''
                ];
            }
            echo json_encode(['header' => $header, 'items' => $items]);
            exit;
        }

        // List GRs
        $sql = "SELECT DISTINCT no_gr, tgl_gr, no_sp, user FROM sp_penerimaan ORDER BY tgl_gr DESC LIMIT 50";
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
        if (!$data || !isset($data['no_gr']) || !isset($data['items']) || empty($data['items'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data payload']);
            exit;
        }

        $no_gr = $data['no_gr'];
        $tgl_gr = $data['tgl_gr'];
        $no_sp = $data['no_sp'];
        $user = $_SESSION['nik'];

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO sp_penerimaan (no_gr, tgl_gr, no_sp, user, barang, qty_pesan, qty_terima, satuan, batch_no, exp_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($data['items'] as $item) {
                $barang = $item['barang'];
                $qty_pesan = (float)$item['qty_pesan'];
                $qty_terima = (float)$item['qty_terima'];
                $satuan = $item['satuan'];
                $batch_no = isset($item['batch_no']) ? $item['batch_no'] : '';
                $exp_date = empty($item['exp_date']) ? null : $item['exp_date'];
                
                $stmt->bind_param("sssssddsss", $no_gr, $tgl_gr, $no_sp, $user, $barang, $qty_pesan, $qty_terima, $satuan, $batch_no, $exp_date);
                if(!$stmt->execute()) throw new Exception("Insert failed: " . $stmt->error);
            }
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Penerimaan barang berhasil disimpan']);
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
