<?php
require_once '../auth.php';
checkAuth();
require_once '../config.php';

// Menentukan metode HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Mengambil input JSON jika ada
$input = json_decode(file_get_contents('php://input'), true);

// Header Response Default
header('Content-Type: application/json');

try {
    if ($method === 'GET') {
        $sql = "SELECT * FROM Kwit_manual_h ORDER BY date_created DESC LIMIT 100";
        $result = $conn->query($sql);
        $data = [];
        if($result) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode(["status" => "success", "data" => $data]);
        exit;
    }

    if ($method === 'POST') {
        $conn->begin_transaction();

        // 1. Dapatkan nomor terakhir
        $result = $conn->query("SELECT No_Kwt, No_Fkt FROM master_nomor LIMIT 1");
        $nomor = $result->fetch_assoc();
        
        $current_kwt = $nomor['No_Kwt'];
        $current_year = date('y'); // format 2 digit tahun, misal 26
        $kwt_year = substr($current_kwt, 0, 2);
        $kwt_seq = (int)substr($current_kwt, 2);

        if ($current_year === $kwt_year) {
            $next_seq = $kwt_seq + 1;
        } else {
            $next_seq = 1;
        }
        $next_kwt = $current_year . str_pad($next_seq, 3, '0', STR_PAD_LEFT);
        
        // Generate faktur default jika tidak disediakan
        $next_fkt = (isset($input['no_faktur']) && !empty($input['no_faktur'])) ? $input['no_faktur'] : 'FK' . $next_kwt;

        // 2. Insert Header
        $sqlH = "INSERT INTO Kwit_manual_h (no_kwitansi, no_faktur, terima_dari, Jumlah, keterangan1, keterangan2, user, tgl_kwitansi, jam, st) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtH = $conn->prepare($sqlH);
        
        $terima_dari = isset($input['terima_dari']) ? $input['terima_dari'] : '';
        $jumlah = isset($input['Jumlah']) ? (float)$input['Jumlah'] : 0;
        $ket1 = isset($input['keterangan1']) ? $input['keterangan1'] : '';
        $ket2 = isset($input['keterangan2']) ? $input['keterangan2'] : '';
        $tgl = isset($input['tgl_kwitansi']) ? $input['tgl_kwitansi'] : date('Y-m-d');
        $jam = date('H:i:s');
        $user = $_SESSION['username'];
        $st = 'LNS';
        
        $stmtH->bind_param("sssdssssss", $next_kwt, $next_fkt, $terima_dari, $jumlah, $ket1, $ket2, $user, $tgl, $jam, $st);
        $stmtH->execute();

        // 3. Insert Details
        if (isset($input['details']) && is_array($input['details'])) {
            $sqlD = "INSERT INTO Kwit_manual_d (no_faktur, Kd_brg, nama, jumlah) VALUES (?, ?, ?, ?)";
            $stmtD = $conn->prepare($sqlD);
            foreach ($input['details'] as $detail) {
                $kd = $detail['Kd_brg'];
                $nm = $detail['nama'];
                $jml = isset($detail['jumlah']) ? (float)$detail['jumlah'] : 0;
                $stmtD->bind_param("sssd", $next_fkt, $kd, $nm, $jml);
                $stmtD->execute();
            }
        }

        // 4. Update Nomor Master
        $stmtUpd = $conn->prepare("UPDATE master_nomor SET No_Kwt = ?, No_Fkt = ?");
        $stmtUpd->bind_param("ss", $next_kwt, $next_fkt);
        $stmtUpd->execute();

        $conn->commit();

        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Data berhasil ditambahkan", "data" => ["no_kwitansi" => $next_kwt]]);
        exit;
    }

    if ($method === 'PATCH' || $method === 'PUT') {
        if (!isset($input['no_kwitansi'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
            exit;
        }

        $sql = "UPDATE Kwit_manual_h SET terima_dari = ?, Jumlah = ? WHERE no_kwitansi = ?";
        $stmt = $conn->prepare($sql);
        
        $terima_dari = isset($input['terima_dari']) ? $input['terima_dari'] : '';
        $jumlah = isset($input['jumlah']) ? (float)$input['jumlah'] : 0;
        $no_kw = $input['no_kwitansi'];
        
        $stmt->bind_param("sds", $terima_dari, $jumlah, $no_kw);
        $stmt->execute();
        
        echo json_encode(["status" => "success", "message" => "Data berhasil diperbarui"]);
        exit;
    }

    if ($method === 'DELETE') {
        if (!isset($input['no_kwitansi'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
            exit;
        }

        $sql = "DELETE FROM Kwit_manual_h WHERE no_kwitansi = ?";
        $stmt = $conn->prepare($sql);
        $no_kw = $input['no_kwitansi'];
        $stmt->bind_param("s", $no_kw);
        $stmt->execute();
        
        echo json_encode(["status" => "success", "message" => "Data berhasil dihapus"]);
        exit;
    }

    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);

} catch (Exception $e) {
    if ($method === 'POST') {
        $conn->rollback();
    }
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
