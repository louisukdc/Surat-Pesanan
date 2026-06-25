<?php
// home.php - Dashboard stats
require_once 'config.php';

$filter_month = isset($_GET['month']) ? $conn->real_escape_string($_GET['month']) : date('Y-m');

// Get Total Suppliers
$res_sup = $conn->query("SELECT COUNT(*) as count FROM m_supplier");
$total_supplier = $res_sup->fetch_assoc()['count'];
if (empty($total_supplier)) {
    $total_supplier = 0;
}

// Get Total Orders for the selected month
$res_ord = $conn->query("SELECT COUNT(id) as count FROM spu_h WHERE tgl_pesan LIKE '$filter_month%'");
$total_orders = $res_ord ? $res_ord->fetch_assoc()['count'] : 0;
// if (empty($total_orders)) {
//     $total_orders = 0;
// }

// Get Total Value for the selected month
$res_val = $conn->query("SELECT SUM(d.jumlah) as total FROM spu_h h JOIN spu_d d ON h.id = d.id_sp WHERE h.tgl_pesan LIKE '$filter_month%'");
$total_value = $res_val ? $res_val->fetch_assoc()['total'] : 0;
if (empty($total_value)) {
    $total_value = 0;
}


// Get Recent 5 Orders for the selected month
$recent_orders = $conn->query("SELECT h.id, h.tgl_pesan as tgl_sp, s.NamaSupplier as namasup, 
    (SELECT SUM(jumlah) FROM spu_d WHERE id_sp = h.id) as total_nilai 
    FROM spu_h h LEFT JOIN m_supplier s ON h.id_supplier = s.KodeSupplier 
    WHERE h.tgl_pesan LIKE '$filter_month%' ORDER BY h.id DESC LIMIT 5");
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
                <?php if($recent_orders): while($row = $recent_orders->fetch_assoc()): ?>
                <tr>
                    <td><span style="font-weight: 600; color: var(--primary);">PO-<?php echo str_pad($row['id'], 5, '0', STR_PAD_LEFT); ?></span></td>
                    <td><?php echo htmlspecialchars($row['tgl_sp']); ?></td>
                    <td><?php echo htmlspecialchars($row['namasup']); ?></td>
                    <td class="text-right">Rp <?php echo number_format($row['total_nilai'], 2); ?></td>
                </tr>
                <?php endwhile; endif; ?>
                <?php if(!$recent_orders || $recent_orders->num_rows == 0): ?>
                <tr>
                    <td colspan="4" class="text-center">Belum ada data pesanan</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
