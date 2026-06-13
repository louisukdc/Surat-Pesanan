<?php
// home.php - Dashboard stats
require_once 'config.php';

$filter_month = isset($_GET['month']) ? $conn->real_escape_string($_GET['month']) : date('Y-m');

// Get Total Suppliers
$res_sup = $conn->query("SELECT COUNT(*) as count FROM m_supplier");
$total_supplier = $res_sup->fetch_assoc()['count'];

// Get Total Orders (Unique no_sp) for the selected month
$res_ord = $conn->query("SELECT COUNT(DISTINCT no_sp) as count FROM sp_pesanan WHERE tgl_sp LIKE '$filter_month%'");
$total_orders = $res_ord->fetch_assoc()['count'];

// Get Total Value for the selected month
$res_val = $conn->query("SELECT SUM(flag) as total FROM (SELECT DISTINCT no_sp, flag FROM sp_pesanan WHERE tgl_sp LIKE '$filter_month%') as unique_orders");
$total_value = $res_val->fetch_assoc()['total'];
// $total_value = $res_val->fetch_assoc()['total'];
// if (empty($total_value)) {
//     $total_value = 0;
// }


// Get Recent 5 Orders for the selected month
$recent_orders = $conn->query("SELECT DISTINCT no_sp, tgl_sp, namasup, flag FROM sp_pesanan WHERE tgl_sp LIKE '$filter_month%' ORDER BY tgl_sp DESC LIMIT 5");
?>

<div class="grid-3" style="margin-bottom: 10px;">
    <div class="card" style="border-left: 4px">
        <div style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Total Supplier</div>
        <div style="font-size: 28px; font-weight: 700;"><?php echo number_format($total_supplier); ?></div>
    </div>
    <div class="card" style="border-left: 4px">
        <div style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Total Pesanan</div>
        <div style="font-size: 28px; font-weight: 700;"><?php echo number_format($total_orders); ?></div>
    </div>
    <div class="card" style="border-left: 4px">
        <div style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Total Nilai Transaksi</div>
        <div style="font-size: 28px; font-weight: 700;">Rp <?php echo number_format($total_value, 2); ?></div>
    </div>
</div>


<div class="card-title" style="display: flex; justify-content: space-between; align-items: center; gap: 30%;">
    <div class="card" style=" margin-bottom: 0; padding: 10px 20px; font-weight: bold; font-size: 16px;">    
        Pesanan Terakhir
    </div>
    <div class="card" style="border-left: 4px solid margin-bottom: 0; padding: 10px 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-calendar"></i>
        <input type="month" id="monthFilter" class="form-control" value="<?php echo $filter_month; ?>" style="border: none; outline: none; background: transparent; font-weight: 600; color: var(--text);" onchange="filterDashboard()">
    </div>    
</div>

<script>
function filterDashboard() {
    const month = document.getElementById('monthFilter').value;
    window.location.href = 'dashboard.php?page=home&month=' + month;
}
</script>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No. SP</th>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th class="text-right">Grand Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $recent_orders->fetch_assoc()): ?>
                <tr>
                    <td><span style="font-weight: 600; color: var(--primary);"><?php echo htmlspecialchars($row['no_sp']); ?></span></td>
                    <td><?php echo htmlspecialchars($row['tgl_sp']); ?></td>
                    <td><?php echo htmlspecialchars($row['namasup']); ?></td>
                    <td class="text-right">Rp <?php echo number_format($row['flag'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
                <?php if($recent_orders->num_rows == 0): ?>
                <tr>
                    <td colspan="4" class="text-center">Belum ada data pesanan</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
