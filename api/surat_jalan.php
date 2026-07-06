<?php
// api/surat_jalan.php
require_once '../auth.php';
checkAuth();
require_once '../config.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Search PO that are approved and ready to receive
        if (isset($_GET['search_po'])) {
            $q = $conn->real_escape_string($_GET['search_po']);
            if (empty($q)) {
                echo json_encode([]);
                exit;
            }
            
            // Search POs that are Approved. We also optionally check if it's already fully received, but for simplicity we list all Approved.
            $sql = "SELECT h.id, h.no_sp, h.no_permintaan, h.tgl_pesan, s.NamaSupplier as namasup 
                    FROM spu_h h 
                    LEFT JOIN askes.m_supplier s ON h.id_supplier = s.KodeSupplier 
                    WHERE h.status_acc = 'Approved' 
                    AND (h.no_permintaan LIKE '%$q%' OR h.no_sp LIKE '%$q%' OR s.NamaSupplier LIKE '%$q%' OR h.id LIKE '%$q%') 
                    ORDER BY h.id DESC LIMIT 20";
            $result = $conn->query($sql);
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $row['no_sp'] = $row['no_sp'] ? $row['no_sp'] : ($row['no_permintaan'] ? $row['no_permintaan'] : 'PO-' . str_pad($row['id'], 5, '0', STR_PAD_LEFT));
                $data[] = $row;
            }
            echo json_encode($data);
            exit;
        }

        // Get single SJ Detail
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $sql = "SELECT sj.*, h.no_permintaan, s.NamaSupplier as namasup, h.id as sp_id
                    FROM sp_surat_jalan sj
                    JOIN spu_h h ON sj.id_spu_h = h.id
                    LEFT JOIN askes.m_supplier s ON h.id_supplier = s.KodeSupplier
                    WHERE sj.id = $id";
            $result = $conn->query($sql);
            if ($row = $result->fetch_assoc()) {
                $row['no_sp'] = $row['no_permintaan'] ? $row['no_permintaan'] : 'PO-' . str_pad($row['sp_id'], 5, '0', STR_PAD_LEFT);
                
                // Fetch items from spu_d for this PO just for display
                $items = [];
                $stmt_d = $conn->query("SELECT * FROM spu_d WHERE id_sp = " . $row['sp_id']);
                while ($d = $stmt_d->fetch_assoc()) {
                    $items[] = $d;
                }
                echo json_encode(['header' => $row, 'items' => $items]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Surat Jalan tidak ditemukan']);
            }
            exit;
        }

        // Get PO detail by ID for when user selects a PO to create new SJ
        if (isset($_GET['po_id'])) {
            $po_id = (int)$_GET['po_id'];
            $sql = "SELECT h.id, h.no_sp, h.no_permintaan, h.tgl_pesan, s.NamaSupplier as namasup 
                    FROM spu_h h 
                    LEFT JOIN askes.m_supplier s ON h.id_supplier = s.KodeSupplier 
                    WHERE h.id = $po_id AND h.status_acc = 'Approved'";
            $result = $conn->query($sql);
            if ($row = $result->fetch_assoc()) {
                $row['no_sp'] = $row['no_sp'] ? $row['no_sp'] : ($row['no_permintaan'] ? $row['no_permintaan'] : 'PO-' . str_pad($row['id'], 5, '0', STR_PAD_LEFT));
                $items = [];
                $stmt_d = $conn->query("SELECT * FROM spu_d WHERE id_sp = $po_id");
                while ($d = $stmt_d->fetch_assoc()) {
                    $items[] = $d;
                }
                echo json_encode(['header' => $row, 'items' => $items]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'PO tidak valid atau belum di-Approve']);
            }
            exit;
        }

        // List all Surat Jalan (Penerimaan)
        $sql = "SELECT sj.id, sj.nomor_surat_jalan, sj.tanggal_terima, sj.status_pengecekan, sj.kategori, sj.catatan, sj.created_at, h.no_permintaan, s.NamaSupplier as namasup, h.id as sp_id
                FROM sp_surat_jalan sj
                JOIN spu_h h ON sj.id_spu_h = h.id
                LEFT JOIN askes.m_supplier s ON h.id_supplier = s.KodeSupplier
                ORDER BY sj.id DESC LIMIT 100";
        $result = $conn->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['no_sp'] = $row['no_permintaan'] ? $row['no_permintaan'] : 'PO-' . str_pad($row['sp_id'], 5, '0', STR_PAD_LEFT);
                $data[] = $row;
            }
        }
        echo json_encode(['data' => $data]);
        exit;
    }

    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || empty($data['id_spu_h']) || empty($data['nomor_surat_jalan'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Data tidak lengkap']);
            exit;
        }

        $id_spu_h = (int)$data['id_spu_h'];
        $nomor_surat_jalan = $conn->real_escape_string($data['nomor_surat_jalan']);
        $tanggal_terima = $conn->real_escape_string($data['tanggal_terima']);
        $kategori = isset($data['kategori']) ? $conn->real_escape_string($data['kategori']) : 'Barang';
        $status_pengecekan = isset($data['status_pengecekan']) ? $conn->real_escape_string($data['status_pengecekan']) : 'Sesuai';
        $catatan = isset($data['catatan']) ? $conn->real_escape_string($data['catatan']) : '';
        $teknisi_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1; // fallback if user_id is not set in session, use 1

        // Check if SJ already exists for this number
        $cek = $conn->query("SELECT id FROM sp_surat_jalan WHERE nomor_surat_jalan = '$nomor_surat_jalan'");
        if ($cek->num_rows > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Nomor Surat Jalan ini sudah pernah diinput!']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO sp_surat_jalan (id_spu_h, nomor_surat_jalan, tanggal_terima, teknisi_penerima_id, kategori, status_pengecekan, catatan, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ississs", $id_spu_h, $nomor_surat_jalan, $tanggal_terima, $teknisi_id, $kategori, $status_pengecekan, $catatan);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Surat Jalan berhasil disimpan', 'id' => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal menyimpan: ' . $conn->error]);
        }
        exit;
    }

    if ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID tidak diberikan']);
            exit;
        }
        
        $id = (int)$data['id'];
        $stmt = $conn->prepare("DELETE FROM sp_surat_jalan WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Surat Jalan berhasil dihapus']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal menghapus: ' . $conn->error]);
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
