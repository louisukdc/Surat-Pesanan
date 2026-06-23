<?php
// laporan.php
require_once 'config.php';

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Query for reports
$query = "SELECT DISTINCT no_sp, tgl_sp, namasup, unit, flag as grand_total FROM sp_pesanan WHERE tgl_sp BETWEEN ? AND ? ORDER BY tgl_sp ASC";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query error: " . $conn->error);
}
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$total_semua = 0;
?>

<div class="card no-print">
    <div class="section-title">Filter Laporan Transaksi</div>
    <form action="dashboard.php" method="GET" class="grid-4" style="align-items: end;">
        <input type="hidden" name="page" value="laporan">
        <div class="form-group">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
        </div>
        <div class="form-group" style="grid-column: span 2;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Tampilkan</button>
            <button type="button" class="btn btn-outline" onclick="window.print()"><i class="fas fa-print"></i> Cetak Laporan</button>
        </div>
    </form>
</div>

<div class="card print-area">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: var(--primary);">Laporan Pembelian Askes RKZ</h2>
        <p>Periode: <?php echo htmlspecialchars($start_date); ?> s/d <?php echo htmlspecialchars($end_date); ?></p>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>No. Surat Pesanan</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th>Unit / Bagian</th>
                <th class="text-right">Grand Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while($row = $result->fetch_assoc()): 
                $total_semua += $row['grand_total'];
            ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><strong><?php echo htmlspecialchars($row['no_sp']); ?></strong></td>
                <td><?php echo htmlspecialchars($row['tgl_sp']); ?></td>
                <td><?php echo htmlspecialchars($row['namasup']); ?></td>
                <td><?php echo htmlspecialchars($row['unit']); ?></td>
                <td class="text-right">Rp <?php echo number_format($row['grand_total'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
            
            <?php if($result->num_rows == 0): ?>
            <tr>
                <td colspan="6" class="text-center">Tidak ada data transaksi pada periode ini.</td>
            </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right" style="font-size: 16px; padding: 15px;">TOTAL KESELURUHAN:</th>
                <th class="text-right" style="font-size: 16px; padding: 15px; color: var(--primary);">Rp <?php echo number_format($total_semua, 2); ?></th>
            </tr>
        </tfoot>
    </table>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .print-area, .print-area * {
        visibility: visible;
    }
    .print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .sidebar, .topbar, .no-print {
        display: none !important;
    }
    .main-content {
        padding: 0;
        background: white;
    }
    .card {
        box-shadow: none;
        border: none;
    }
}
</style>
