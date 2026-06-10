<?php
// home.php - Dashboard stats
require_once 'config.php';

// Get Total Suppliers
$res_sup = $conn->query("SELECT COUNT(*) as count FROM m_supplier");
$total_supplier = $res_sup->fetch_assoc()['count'];

// Get Total Orders (Unique no_sp)
$res_ord = $conn->query("SELECT COUNT(DISTINCT no_sp) as count FROM sp_pesanan");
$total_orders = $res_ord->fetch_assoc()['count'];

// Get Total Value
$res_val = $conn->query("SELECT SUM(flag) as total FROM (SELECT DISTINCT no_sp, flag FROM sp_pesanan) as unique_orders");
$total_value = $res_val->fetch_assoc()['total'];

// Get Recent 5 Orders
$recent_orders = $conn->query("SELECT DISTINCT no_sp, tgl_sp, namasup, flag FROM sp_pesanan ORDER BY tgl_sp DESC LIMIT 5");
?>
<div class="grid-3" style="margin-bottom: 30px;">
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

<div class="card">
    <div class="section-title">Pesanan Terakhir</div>
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
