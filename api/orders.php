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
        
        if (isset($_GET['search_gudang'])) {
            $q = $_GET['search_gudang'];
            if (empty($q)) {
                echo json_encode([]);
                exit;
            }
            $q = "%{$q}%";
            $stmt = $conn->prepare("SELECT KodeGudang as KodeGudang, NamaGudang as NamaGudang FROM m_gudang WHERE NamaGudang LIKE ? OR KodeGudang LIKE ? LIMIT 10");
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

        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $stmt = $conn->prepare("SELECT h.*, h.tgl_pesan, h.tglkirim as tgl_kirim, h.kodesup as id_supplier, h.no_tawar as no_penawaran, h.tgl_tawar as tgl_penawaran, h.unit as id_gudang, h.pembayaran as jenis_bayar, h.notein as keterangan, s.NamaSupplier, g.NamaGudang as NamaGudang FROM spu_h h LEFT JOIN m_supplier s ON h.id_supplier = s.KodeSupplier LEFT JOIN m_gudang g ON h.id_gudang = g.KodeGudang WHERE h.id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $header = [
                    'id' => $row['id'],
                    'no_permintaan' => $row['no_permintaan'],
                    'tgl_pesan' => $row['tgl_pesan'],
                    'tgl_kirim' => $row['tgl_kirim'],
                    'id_supplier' => $row['id_supplier'],
                    'namasup' => $row['NamaSupplier'],
                    'no_penawaran' => $row['no_penawaran'],
                    'tgl_penawaran' => $row['tgl_penawaran'],
                    'id_gudang' => $row['id_gudang'],
                    'namagudang' => $row['NamaGudang'],
                    'jenis_bayar' => $row['jenis_bayar'],
                    'keterangan' => $row['keterangan'],
                    'nama_lampiran' => $row['nama_lampiran']
                ];
                
                // Fetch items
                $items = [];
                $item_sql = "SELECT * FROM spu_d WHERE id_sp = $id";
                $item_res = $conn->query($item_sql);
                if($item_res) {
                    while($row = $item_res->fetch_assoc()) {
                        $items[] = $row;
                    }
                }
                echo json_encode(['header' => $header, 'items' => $items]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Order not found']);
            }
            exit;
        }

        if (isset($_GET['status_acc'])) {
            $status = $conn->real_escape_string($_GET['status_acc']);
            $where = "1=1";
            if (!empty($status)) {
                $where = "h.status_acc = '$status'";
            }
            
            $sql = "SELECT h.id, h.no_sp, h.no_permintaan, h.tgl_pesan, s.NamaSupplier as namasup, g.NamaGudang as unit, h.user, h.status_acc, h.alasan_tolak, 
                    (SELECT COUNT(id) FROM spu_d WHERE id_sp = h.id) as item_count 
                    FROM spu_h h LEFT JOIN m_supplier s ON h.id_supplier = s.KodeSupplier LEFT JOIN m_gudang g ON h.id_gudang = g.KodeGudang 
                    WHERE $where ORDER BY h.id DESC";
            
            $result = $conn->query($sql);
            $data = [];
            if($result) {
                while($row = $result->fetch_assoc()) {
                    if (empty($row['no_sp'])) $row['no_sp'] = $row['no_permintaan'];
                    $row['status'] = $row['status_acc'];
                    $data[] = $row;
                }
            }
            echo json_encode(['data' => $data]);
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

        $status_filter = isset($_GET['status_filter']) ? $conn->real_escape_string(trim($_GET['status_filter'])) : 'Approved'; // Default ke Approved
        
        $where = "1=1";
        if (!empty($search)) {
            $where .= " AND (h.id LIKE '%$search%' OR s.NamaSupplier LIKE '%$search%' OR h.no_sp LIKE '%$search%')";
        }
        if (!empty($start_date)) {
            $where .= " AND h.tgl_pesan >= '$start_date'";
        }
        if (!empty($end_date)) {
            $where .= " AND h.tgl_pesan <= '$end_date'";
        }
        if ($status_filter !== 'All') {
            $where .= " AND h.status_acc = '$status_filter'";
        }

        $count_sql = "SELECT COUNT(h.id) as total FROM spu_h h LEFT JOIN m_supplier s ON h.id_supplier = s.KodeSupplier WHERE $where";
        $total_res = $conn->query($count_sql);
        $total_row = $total_res->fetch_assoc();
        $total_records = (int)$total_row['total'];

        $sql = "SELECT h.id, h.no_sp, h.no_permintaan, h.tgl_pesan, h.date_acc, s.NamaSupplier as namasup, g.NamaGudang as unit, h.pembayaran, h.flag, h.status_acc, h.nama_lampiran, h.user 
                FROM spu_h h LEFT JOIN m_supplier s ON h.id_supplier = s.KodeSupplier LEFT JOIN m_gudang g ON h.id_gudang = g.KodeGudang
                WHERE $where ORDER BY h.id DESC LIMIT $offset, $limit";
        
        $result = $conn->query($sql);
        
        $data = [];
        if ($result) {
            while($row = $result->fetch_assoc()) {
                if (empty($row['no_sp'])) $row['no_sp'] = $row['no_permintaan'] ? $row['no_permintaan'] : 'PO-' . str_pad($row['id'], 5, '0', STR_PAD_LEFT);
                $row['tgl_sp'] = $row['tgl_pesan'];
                
                // Get real grand total
                $total_sql = "SELECT SUM(total) as gt FROM spu_d WHERE id_sp = " . (int)$row['id'];
                $t_res = $conn->query($total_sql);
                $row['grand_total'] = ($t_res && $t_row = $t_res->fetch_assoc()) ? (float)$t_row['gt'] : 0;
                
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

    if ($method === 'PATCH') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['id']) || !isset($data['action'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data payload']);
            exit;
        }

        $id = (int)$data['id'];
        $action = $data['action'];

        if ($action === 'approve') {
            $stmt = $conn->prepare("UPDATE spu_h SET status_acc = 'Approved', date_acc = NOW() WHERE id = ?");
            $stmt->bind_param("i", $id);
            if($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Pesanan berhasil disetujui']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => $conn->error]);
            }
        } else if ($action === 'reject') {
            $alasan = isset($data['alasan']) ? $data['alasan'] : '';
            $stmt = $conn->prepare("UPDATE spu_h SET status_acc = 'Rejected', alasan_tolak = ? WHERE id = ?");
            $stmt->bind_param("si", $alasan, $id);
            if($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Pesanan berhasil ditolak']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => $conn->error]);
            }
        } else if ($action === 'reset') {
            $stmt = $conn->prepare("UPDATE spu_h SET status_acc = 'Pending', date_acc = '1900-01-01', alasan_tolak = '' WHERE id = ?");
            $stmt->bind_param("i", $id);
            if($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Status pesanan berhasil di-reset kembali ke Pending']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => $conn->error]);
            }
        } else if ($action === 'set_approval_date') {
            $new_date = isset($data['date_acc']) ? $data['date_acc'] : date('Y-m-d');
            $stmt = $conn->prepare("UPDATE spu_h SET date_acc = ? WHERE id = ?");
            $stmt->bind_param("si", $new_date, $id);
            if($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Tanggal persetujuan berhasil diubah']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => $conn->error]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Unknown action']);
        }
        exit;
    }

    if ($method === 'POST' || $method === 'PUT') {
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
            $id = isset($header['id']) ? (int)$header['id'] : 0;
            
            $tgl_pesan = !empty($header['tgl_pesan']) ? $header['tgl_pesan'] : date('Y-m-d');
            $tgl_kirim = !empty($header['tgl_kirim']) ? $header['tgl_kirim'] : '1900-01-01';
            $tgl_penawaran = !empty($header['tgl_penawaran']) ? $header['tgl_penawaran'] : '1900-01-01';
            
            $no_permintaan = !empty($header['no_permintaan']) ? $header['no_permintaan'] : '0';
            $nama_lampiran = !empty($header['nama_lampiran']) ? $header['nama_lampiran'] : '';
            
            if ($id > 0) {
                // Update existing
                $stmt_upd = $conn->prepare("UPDATE spu_h SET no_permintaan=?, tgl_pesan=?, kodesup=?, id_supplier=?, no_tawar=?, no_penawaran=?, tgl_tawar=?, tgl_penawaran=?, tglkirim=?, tgl_kirim=?, unit=?, id_gudang=?, pembayaran=?, jenis_bayar=?, notein=?, keterangan=?, nama_lampiran=? WHERE id=?");
                $stmt_upd->bind_param("sssssssssssssssssi", 
                    $no_permintaan, $tgl_pesan, $header['id_supplier'], $header['id_supplier'], $header['no_penawaran'], $header['no_penawaran'], $tgl_penawaran, $tgl_penawaran, $tgl_kirim, $tgl_kirim, $header['id_gudang'], $header['id_gudang'], $header['jenis_bayar'], $header['jenis_bayar'], $header['keterangan'], $header['keterangan'], $nama_lampiran, $id
                );
                if(!$stmt_upd->execute()) throw new Exception("Update header failed: " . $stmt_upd->error);
                
                // Delete old details to replace them cleanly
                $conn->query("DELETE FROM spu_d WHERE id_sp = $id");
                $sp_id = $id;
            } else {
                // Insert new
                $tmp_no_sp = 'PO/TMP/' . time();
                $stmt_ins = $conn->prepare("INSERT INTO spu_h (no_sp, no_permintaan, tgl_pesan, kodesup, id_supplier, no_tawar, no_penawaran, tgl_tawar, tgl_penawaran, tglkirim, tgl_kirim, unit, id_gudang, pembayaran, jenis_bayar, notein, keterangan, nama_lampiran, user, user_created, created_at, dtime_created, user_acc, date_acc, status_acc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), '', '1900-01-01', 'Pending')");
                $user = $_SESSION['nik'];
                $stmt_ins->bind_param("ssssssssssssssssssss", 
                    $tmp_no_sp, $no_permintaan, $tgl_pesan, $header['id_supplier'], $header['id_supplier'], $header['no_penawaran'], $header['no_penawaran'], $tgl_penawaran, $tgl_penawaran, $tgl_kirim, $tgl_kirim, $header['id_gudang'], $header['id_gudang'], $header['jenis_bayar'], $header['jenis_bayar'], $header['keterangan'], $header['keterangan'], $nama_lampiran, $user, $user
                );
                if(!$stmt_ins->execute()) throw new Exception("Insert header failed: " . $stmt_ins->error);
                $sp_id = $conn->insert_id;
                
                // Optionally update no_sp to PO/XXXX/MM/YY if needed, but keeping TMP is fine for now
                $real_no_sp = 'PO/' . str_pad($sp_id, 5, '0', STR_PAD_LEFT) . '/' . date('m/y');
                $conn->query("UPDATE spu_h SET no_sp = '$real_no_sp' WHERE id = $sp_id");
            }
            
            // Insert Items
            $real_no_sp = isset($real_no_sp) ? $real_no_sp : (isset($header['no_sp']) ? $header['no_sp'] : 'PO-'.$sp_id);
            $stmt_det = $conn->prepare("INSERT INTO spu_d (id_sp, no_sp, barang, model, merk, spec, qty, satuan, harga, disc, total, jumlah, ppn, created_at, date_created) VALUES (?, ?, ?, ?, ?, ?, ?, 'PCS', ?, ?, ?, ?, ?, NOW(), NOW())");
            foreach ($items as $item) {
                $qty = (float)(isset($item['qty']) ? $item['qty'] : 0);
                $harga = (float)(isset($item['harga']) ? $item['harga'] : 0);
                $disc = (float)(isset($item['disc']) ? $item['disc'] : 0);
                $ppn = (float)(isset($item['ppn']) ? $item['ppn'] : 0);
                $jumlah = (float)(isset($item['jumlah']) ? $item['jumlah'] : 0);
                $model = isset($item['model']) ? $item['model'] : '';
                $merk = isset($item['merk']) ? $item['merk'] : '';
                $spec = isset($item['spec']) ? $item['spec'] : '';
                
                $stmt_det->bind_param("isssssdddddd", $sp_id, $real_no_sp, $item['barang'], $model, $merk, $spec, $qty, $harga, $disc, $jumlah, $jumlah, $ppn);
                if(!$stmt_det->execute()) throw new Exception("Insert detail failed: " . $stmt_det->error);
            }
            
            $conn->commit();
            http_response_code($id > 0 ? 200 : 201);
            echo json_encode(['success' => true, 'message' => 'Order saved successfully', 'id' => $sp_id]);
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    if ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = isset($input['id']) ? (int)$input['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'No Order ID provided']);
            exit;
        }
        
        $conn->begin_transaction();
        try {
            $conn->query("DELETE FROM sp_surat_jalan WHERE id_spu_h = $id");
            $conn->query("DELETE FROM spu_d WHERE id_sp = $id");
            $conn->query("DELETE FROM spu_h WHERE id = $id");
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Order deleted']);
        } catch (Exception $e) {
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
