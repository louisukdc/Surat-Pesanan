<?php
// api/dashboard.php
require_once '../auth.php';
checkAuth();
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $data = [
            'widgets' => [],
            'chart' => [],
            'top_suppliers' => []
        ];

        $current_month = date('Y-m');
        
        // 1. Total Pesanan Bulan Ini
        $stmt1 = $conn->query("SELECT COUNT(DISTINCT no_sp) as total FROM sp_pesanan WHERE tgl_sp LIKE '$current_month%'");
        $data['widgets']['total_orders'] = $stmt1->fetch_assoc()['total'] ?? 0;

        // 2. Total Pengeluaran Bulan Ini
        $stmt2 = $conn->query("SELECT SUM(flag) as total_spend FROM (SELECT DISTINCT no_sp, flag FROM sp_pesanan WHERE tgl_sp LIKE '$current_month%') as sub");
        $data['widgets']['total_spend'] = $stmt2->fetch_assoc()['total_spend'] ?? 0;

        // 3. Total Supplier Aktif
        $stmt3 = $conn->query("SELECT COUNT(*) as total FROM m_supplier");
        $data['widgets']['total_suppliers'] = $stmt3->fetch_assoc()['total'] ?? 0;

        // 4. Chart Data (Last 30 Days)
        $chart_sql = "
            SELECT tgl_sp, SUM(flag) as daily_spend, COUNT(DISTINCT no_sp) as order_count 
            FROM (SELECT DISTINCT no_sp, tgl_sp, flag FROM sp_pesanan) as sub
            WHERE tgl_sp >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY tgl_sp 
            ORDER BY tgl_sp ASC
        ";
        $stmt4 = $conn->query($chart_sql);
        $labels = [];
        $spending = [];
        $orders = [];
        while($row = $stmt4->fetch_assoc()) {
            $labels[] = date('d M', strtotime($row['tgl_sp']));
            $spending[] = (float)$row['daily_spend'];
            $orders[] = (int)$row['order_count'];
        }
        $data['chart'] = [
            'labels' => $labels,
            'spending' => $spending,
            'orders' => $orders
        ];

        // 5. Top 5 Suppliers by Spend All Time
        $top_sql = "
            SELECT namasup, SUM(flag) as total_spend, COUNT(DISTINCT no_sp) as total_orders
            FROM (SELECT DISTINCT no_sp, namasup, flag FROM sp_pesanan) as sub
            GROUP BY namasup
            ORDER BY total_spend DESC
            LIMIT 5
        ";
        $stmt5 = $conn->query($top_sql);
        while($row = $stmt5->fetch_assoc()) {
            $data['top_suppliers'][] = $row;
        }

        echo json_encode(['success' => true, 'data' => $data]);
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
