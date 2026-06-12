<?php
// api/orders.php
require_once '../auth.php';
checkAuth();
require_once '../config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['search_supplier'])) {
            $q = $_GET['search_supplier'];
            if (empty($q)) {
                echo json_encode([]);
                exit;
            }
            $q = "%{$q}%";
            $stmt = $conn->prepare("SELECT KodeSupplier, NamaSupplier FROM m_supplier WHERE NamaSupplier LIKE ? OR KodeSupplier LIKE ? LIMIT 10");
            $stmt->bind_param("ss", $q, $q);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode($data);
            exit;
        }

        if (isset($_GET['no_sp'])) {
            $no_sp = $_GET['no_sp'];
            $stmt = $conn->prepare("SELECT * FROM sp_pesanan WHERE no_sp = ?");
            $stmt->bind_param("s", $no_sp);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $items = [];
            $header = null;
            
            while ($row = $result->fetch_assoc()) {
                if ($header === null) {
                    $header = [
                        'no_sp' => $row['no_sp'],
                        'tgl_sp' => $row['tgl_sp'],
                        'namasup' => $row['namasup'],
                        'kodesup' => $row['kodesup'],
                        'no_tawar' => $row['no_tawar'],
                        'tgl_tawar' => $row['tgl_tawar'],
                        'unit' => $row['unit'],
                        'pembayaran' => $row['pembayaran'],
                        'noteout' => $row['noteout'],
                        'noteout1' => $row['noteout1'],
                        'noteout2' => $row['noteout2'],
                        'notein' => $row['notein'],
                        'user' => $row['user'],
                        'ppn' => $row['ppn'],
                        'grand_total' => $row['flag']
                    ];
                }
                $items[] = [
                    'barang' => $row['barang'],
                    'merk' => $row['merk'],
                    'model' => $row['model'],
                    'spec' => $row['spec'],
                    'qty' => $row['qty'],
                    'satuan' => $row['satuan'],
                    'harga' => $row['harga'],
                    'potongan' => $row['potongan'],
                    'total' => $row['total']
                ];
            }
            
            if ($header === null) {
                http_response_code(404);
                echo json_encode(['error' => 'Order not found']);
            } else {
                echo json_encode(['header' => $header, 'items' => $items]);
            }
            exit;
        }

        // Default GET: list orders with pagination and filtering
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';
        $start_date = isset($_GET['start_date']) ? $conn->real_escape_string(trim($_GET['start_date'])) : '';
        $end_date = isset($_GET['end_date']) ? $conn->real_escape_string(trim($_GET['end_date'])) : '';

        if ($page < 1) $page = 1;
        if ($limit < 1 || $limit > 200) $limit = 50;
        $offset = ($page - 1) * $limit;

        $where = "1=1";
        if (!empty($search)) {
            $where .= " AND (no_sp LIKE '%$search%' OR namasup LIKE '%$search%')";
        }
        if (!empty($start_date)) {
            $where .= " AND tgl_sp >= '$start_date'";
        }
        if (!empty($end_date)) {
            $where .= " AND tgl_sp <= '$end_date'";
        }

        $is_export = isset($_GET['export']) && $_GET['export'] == '1';

        $count_sql = "SELECT COUNT(DISTINCT no_sp) as total FROM sp_pesanan WHERE $where";
        $total_res = $conn->query($count_sql);
        $total_row = $total_res->fetch_assoc();
        $total_records = (int)$total_row['total'];

        if ($is_export) {
            // For export, return all items without DISTINCT and include item details
            $sql = "SELECT no_sp, tgl_sp, namasup, unit, flag, user, pembayaran, barang, qty, harga, total 
                    FROM sp_pesanan WHERE $where ORDER BY tgl_sp DESC";
        } else {
            // For normal list view, return grouped header data
            $sql = "SELECT DISTINCT no_sp, tgl_sp, namasup, unit, flag 
                    FROM sp_pesanan WHERE $where ORDER BY tgl_sp DESC LIMIT $offset, $limit";
        }
        
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
        exit;
    }

    if ($method === 'POST' || $method === 'PATCH' || $method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['header'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data payload']);
            exit;
        }
        
        $header = $data['header'];
        $items = $data['items'];
        
        $conn->begin_transaction();
        
        try {
            // Delete existing items for this no_sp to do a clean replace
            $stmt_del = $conn->prepare("DELETE FROM sp_pesanan WHERE no_sp = ?");
            $stmt_del->bind_param("s", $header['no_sp']);
            if(!$stmt_del->execute()) throw new Exception($stmt_del->error);
            
            // Convert empty dates to '1970-01-01' to prevent strict mode errors with '0000-00-00' and NOT NULL constraints
            $tgl_tawar = empty($header['tgl_tawar']) ? '1970-01-01' : $header['tgl_tawar'];
            $tglkirim = '1970-01-01'; // default safe date instead of '0000-00-00'
            
            $stmt_ins = $conn->prepare("INSERT INTO sp_pesanan 
                (no_sp, tgl_sp, namasup, kodesup, no_tawar, tgl_tawar, unit, pembayaran, pembayaran1, noteout, noteout1, noteout2, notein, user, ppn, flag, uang, xx,
                barang, merk, model, spec, qty, satuan, harga, potongan, total, tglkirim) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
            if(!$stmt_ins) throw new Exception("Prepare failed: " . $conn->error);
                
            foreach ($items as $item) {
                // strict mode fixes: ensure numerical values are floats
                $ppn = (float)(isset($header['ppn']) ? $header['ppn'] : 0);
                $grand_total = (float)(isset($header['grand_total']) ? $header['grand_total'] : 0);
                $qty = (float)(isset($item['qty']) ? $item['qty'] : 0);
                $harga = (float)(isset($item['harga']) ? $item['harga'] : 0);
                $potongan = (float)(isset($item['potongan']) ? $item['potongan'] : 0);
                $total = (float)(isset($item['total']) ? $item['total'] : 0);
                
                $pembayaran1 = '';
                $uang = 'RP';
                $xx = '@';

                $stmt_ins->bind_param("ssssssssssssssddssssssssddds", 
                    $header['no_sp'],
                    $header['tgl_sp'],
                    $header['namasup'],
                    $header['kodesup'],
                    $header['no_tawar'],
                    $tgl_tawar,
                    $header['unit'],
                    $header['pembayaran'],
                    $pembayaran1,
                    $header['noteout'],
                    $header['noteout1'],
                    $header['noteout2'],
                    $header['notein'],
                    $header['user'],
                    $ppn,
                    $grand_total,
                    $uang,
                    $xx,
                    
                    $item['barang'],
                    $item['merk'],
                    $item['model'],
                    $item['spec'],
                    $qty,
                    $item['satuan'],
                    $harga,
                    $potongan,
                    $total,
                    $tglkirim
                );
                if(!$stmt_ins->execute()) throw new Exception("Insert failed: " . $stmt_ins->error);
            }
            
            $conn->commit();
            http_response_code($method === 'POST' ? 201 : 200);
            echo json_encode(['success' => true, 'message' => 'Order saved successfully']);
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    if ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $no_sp = isset($input['no_sp']) ? $input['no_sp'] : '';
        if (empty($no_sp)) {
            http_response_code(400);
            echo json_encode(['error' => 'No SP provided']);
            exit;
        }
        $stmt = $conn->prepare("DELETE FROM sp_pesanan WHERE no_sp = ?");
        $stmt->bind_param("s", $no_sp);
        if($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Order deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => $conn->error]);
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
