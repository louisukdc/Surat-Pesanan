<?php
// views/pembayaran_detail.php
// Director comparison screen: reviews payment request vs goods receipt and initial PO

if (!defined('FRONT_CONTROLLER')) {
    header("Location: /sp_umum/home.php?page=pembayaran_detail" . ($_SERVER['QUERY_STRING'] !== '' ? '&' . $_SERVER['QUERY_STRING'] : ''));
    exit;
}

$page_title = 'Review Pengajuan Pembayaran';
$active_menu = 'pembayaran';

require_once dirname(__FILE__) . '/../includes/auth.php';

$error = '';
$success = '';

// Local helper to fetch payment request by ID (supports dual-mode)
function get_payment_request_by_id($id) {
    $id = (int)$id;
    if ($GLOBALS['use_mock']) {
        if (isset($_SESSION['mock_pengajuan_pembayaran'][$id])) {
            $pr = $_SESSION['mock_pengajuan_pembayaran'][$id];
            
            // Map po_id for compatibility
            $pr['po_id'] = $pr['surat_pesanan_id'];
            
            // Enrich with PO details
            $po = db_get_purchase_order_by_id($pr['po_id']);
            if ($po) {
                $pr['no_pesanan'] = $po['no_pesanan'];
                $pr['nama_vendor'] = $po['nama_vendor'];
                $pr['tgl_pesanan'] = $po['tgl_pesanan'];
                $pr['total_setelah_diskon'] = $po['total_setelah_diskon'];
                $pr['harga_vendor'] = $po['harga_vendor'];
                $pr['diskon_vendor'] = $po['diskon_vendor'];
            }
            
            // Enrich with user name
            $user = db_get_user_by_id($pr['diajukan_oleh']);
            $pr['pengaju_nama'] = $user ? $user['nama'] : 'System';
            
            return $pr;
        }
        return null;
    } else {
        $query = "SELECT pr.*, po.no_sp as no_pesanan, po.namasup as nama_vendor, po.tgl_sp as tgl_pesanan, 
                         po.flag as total_setelah_diskon, (po.flag + po.potongan) as harga_vendor, po.potongan as diskon_vendor, u.NamaUser as pengaju_nama
                  FROM sp_pengajuan_pembayaran pr
                  JOIN spu_h po ON pr.surat_pesanan_id = po.id
                  JOIN sp_user u ON pr.diajukan_oleh = u.id
                  WHERE pr.id = $id LIMIT 1";
        $res = mysqli_query($GLOBALS['db_conn'], $query);
        if ($res && mysqli_num_rows($res) > 0) {
            $pr = mysqli_fetch_assoc($res);
            $pr['po_id'] = $pr['surat_pesanan_id']; // mapping for compatibility
            return $pr;
        }
        return null;
    }
}

// Fetch Payment Request
$pr_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pr = get_payment_request_by_id($pr_id);

if (!$pr) {
    header("Location: /sp_umum/home.php?page=pembayaran&err=notfound");
    exit;
}

// Process Director Decision
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment_approval'])) {
    sp_require_role('direktur');
    
    $status = isset($_POST['status']) ? $_POST['status'] : ''; // 'acc' or 'ditolak'
    $catatan = isset($_POST['catatan']) ? trim($_POST['catatan']) : '';
    
    if (!in_array($status, array('acc', 'ditolak'))) {
        $error = 'Pilihan persetujuan tidak valid.';
    } else {
        $msg_status = $status === 'acc' ? 'DISETUJUI (ACC)' : 'DITOLAK';
        if (db_approve_payment_request($pr_id, $status, $catatan, $_SESSION['user_id'])) {
            $success = "Pengajuan pembayaran berhasil di-update dengan status: $msg_status.";
            // Reload updated request details
            $pr = get_payment_request_by_id($pr_id);
        } else {
            $error = 'Gagal memproses persetujuan pembayaran.';
        }
    }
}

// Fetch PO Items and calculate delivery history
$items = db_get_purchase_order_items($pr['po_id']);

$earliest_receipt = null;
$latest_receipt = null;
$total_items = count($items);
$complete_items_count = 0;

foreach ($items as $item) {
    if ($item['status_terima'] === 'lengkap') {
        $complete_items_count++;
    }
    
    if (!empty($item['receipts'])) {
        foreach ($item['receipts'] as $rec) {
            $date = $rec['tgl_diterima'];
            if ($earliest_receipt === null || $date < $earliest_receipt) {
                $earliest_receipt = $date;
            }
            if ($latest_receipt === null || $date > $latest_receipt) {
                $latest_receipt = $date;
            }
        }
    }
}

// Calculate lead times
$lead_time_first = '-';
$lead_time_last = '-';

if ($earliest_receipt) {
    $diff = strtotime($earliest_receipt) - strtotime($pr['tgl_pesanan']);
    $days = round($diff / (60 * 60 * 24));
    $lead_time_first = format_date($earliest_receipt) . " ($days hari sejak pesanan)";
}
if ($latest_receipt) {
    $diff = strtotime($latest_receipt) - strtotime($pr['tgl_pesanan']);
    $days = round($diff / (60 * 60 * 24));
    $lead_time_last = format_date($latest_receipt) . " ($days hari sejak pesanan)";
}

// Price difference calculation
$po_total = (float)$pr['total_setelah_diskon'];
$pr_nominal = (float)$pr['nominal_diajukan'];
$price_diff = $pr_nominal - $po_total;

require_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="bp-hero no-print" style="padding:0.5rem 1rem; margin-bottom:0.5rem; display:flex; justify-content:space-between; align-items:center;">
    <div>
        <div class="bp-hero-badge"><i class="fas fa-file-invoice-dollar"></i> Menu Pembayaran</div>
        <h4 class="bp-hero-title" style="font-size:1rem; margin-bottom:0;">Review Pengajuan Pembayaran</h4>
    </div>
    <a href="/sp_umum/home.php?page=pembayaran" class="btn btn-sm btn-premium-secondary" style="padding:0.2rem 0.6rem; font-size:0.75rem;">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
    </a>
</div>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger alert-premium">
        <i class="fas fa-exclamation-circle fa-lg"></i>
        <span><?php echo htmlspecialchars($error); ?></span>
    </div>
<?php endif; ?>

<?php if ($success !== ''): ?>
    <div class="alert alert-success alert-premium">
        <i class="fas fa-check-circle fa-lg"></i>
        <span><?php echo htmlspecialchars($success); ?></span>
    </div>
<?php endif; ?>

<?php if ($price_diff != 0): ?>
    <?php if ($price_diff > 0): ?>
        <div class="alert alert-danger alert-premium border-danger mb-4">
            <i class="fas fa-exclamation-triangle fa-2x text-danger mr-3"></i>
            <div>
                <h6 class="font-weight-bold text-danger mb-1">SELISIH NOMINAL: PENGAJUAN MELEBIHI HARGA NET SP!</h6>
                <span>Nominal pembayaran yang diajukan (<?php echo format_rupiah($pr_nominal); ?>) <strong>lebih besar</strong> dari nilai bersih SP awal (<?php echo format_rupiah($po_total); ?>). Selisih: <strong>+<?php echo format_rupiah($price_diff); ?></strong>.</span>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning alert-premium border-warning mb-4">
            <i class="fas fa-exclamation-triangle fa-2x text-warning mr-3"></i>
            <div>
                <h6 class="font-weight-bold text-warning mb-1">SELISIH NOMINAL: PENGAJUAN DI BAWAH HARGA NET SP!</h6>
                <span>Nominal pembayaran yang diajukan (<?php echo format_rupiah($pr_nominal); ?>) <strong>lebih kecil</strong> dari nilai bersih SP awal (<?php echo format_rupiah($po_total); ?>). Selisih: <strong><?php echo format_rupiah($price_diff); ?></strong> (Kemungkinan DP / pembayaran parsial).</span>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row" style="flex: 1 1 auto; min-height: 0; margin-left:-0.25rem; margin-right:-0.25rem;">
    <!-- LEFT PANEL: Auto-calculated Comparison Stats -->
    <div class="col-md-7 mb-2 px-1 d-flex flex-column" style="height: 100%;">
        <div class="bp-panel bp-panel-teal mb-2 flex-shrink-0">
            <div class="bp-panel-header" style="padding:0.4rem 0.8rem;">
                <div><i class="fas fa-balance-scale mr-2"></i> Perbandingan Nilai Finansial</div>
            </div>
            
            <div class="bp-panel-body" style="padding: 0.5rem;">
                <div class="row text-center">
                    <div class="col-sm-6 border-right">
                        <span class="d-block text-muted small uppercase font-weight-bold mb-1">Total Net di SP Awal</span>
                        <h4 class="font-weight-bold text-secondary mb-0" style="font-size:1.1rem;"><?php echo format_rupiah($po_total); ?></h4>
                        <small class="text-muted" style="font-size:0.65rem;">(Gross: <?php echo format_rupiah($pr['harga_vendor']); ?> - Disc: <?php echo format_rupiah($pr['diskon_vendor']); ?>)</small>
                    </div>
                    
                    <div class="col-sm-6">
                        <span class="d-block text-muted small uppercase font-weight-bold mb-1">Nominal Pembayaran Diajukan</span>
                        <h4 class="font-weight-bold mb-0 <?php echo $price_diff != 0 ? 'text-danger' : 'text-primary'; ?>" style="font-size:1.1rem;">
                            <?php echo format_rupiah($pr_nominal); ?>
                        </h4>
                        <small class="text-muted" style="font-size:0.65rem;">(Diajukan oleh: <?php echo htmlspecialchars($pr['pengaju_nama']); ?>)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel 2: Lead Time Analysis -->
        <div class="bp-panel bp-panel-slate mb-2 flex-shrink-0">
            <div class="bp-panel-header" style="padding:0.4rem 0.8rem;">
                <div><i class="fas fa-shipping-fast mr-2"></i> Analisis Lead Time (Pengiriman)</div>
            </div>
            
            <div class="bp-panel-body" style="padding: 0.4rem;">
                <table class="table table-borderless table-sm mb-0 font-size-sm" style="line-height:1.2;">
                    <tbody>
                        <tr>
                            <td class="text-muted py-0" style="width: 12rem;">Tanggal Pesanan dibuat (PO)</td>
                            <td class="font-weight-bold py-0">: <?php echo format_date($pr['tgl_pesanan']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted py-0">Tanggal Pengiriman Pertama</td>
                            <td class="font-weight-bold text-info py-0">: <?php echo $lead_time_first; ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted py-0">Tanggal Pengiriman Terakhir</td>
                            <td class="font-weight-bold text-success py-0">: <?php echo $lead_time_last; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Panel 3: Item Completeness Checklist -->
        <div class="bp-panel bp-panel-amber mb-0" style="flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0;">
            <div class="bp-panel-header" style="padding:0.4rem 0.8rem; display:flex; justify-content:space-between; align-items:center;">
                <div><i class="fas fa-check-square mr-2"></i> Status Kelengkapan Barang</div>
                <span class="badge badge-success" style="font-size:0.65rem;"><?php echo $complete_items_count; ?> dari <?php echo $total_items; ?> Barang Lengkap</span>
            </div>
            
            <div class="bp-panel-body" style="padding:0; flex: 1 1 auto; overflow-y: auto;">
                <div class="table-responsive-sticky" style="height: 100%;">
                <table class="table table-bordered mb-0 bp-items-table">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th class="text-center" style="width: 5rem;">Pesan</th>
                            <th class="text-center" style="width: 5rem;">Diterima</th>
                            <th class="text-center" style="width: 7rem;">Status Terima</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="font-weight-bold text-dark"><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                                <td class="text-center"><?php echo $item['jumlah']; ?> unit</td>
                                <td class="text-center font-weight-bold text-info"><?php echo $item['jumlah_diterima']; ?> unit</td>
                                <td class="text-center"><?php echo get_receipt_badge($item['status_terima']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL: Header details, logs and Decision widget -->
    <div class="col-md-5 mb-2 px-1 d-flex flex-column" style="height: 100%;">
        <div class="bp-panel bp-panel-blue mb-2 flex-shrink-0">
            <div class="bp-panel-header" style="padding:0.4rem 0.8rem;">
                <div><i class="fas fa-file-invoice mr-2"></i> Data Surat Pesanan</div>
            </div>
            <div class="bp-panel-body" style="padding:0.4rem;">
                <table class="table table-borderless table-sm mb-0 font-size-sm" style="line-height:1.2;">
                    <tbody>
                        <tr>
                            <td class="text-muted py-0" style="width: 9rem;">No Surat Pesanan</td>
                            <td class="font-weight-bold py-0"><a href="/sp_umum/home.php?page=monitoring&po_id=<?php echo $pr['po_id']; ?>"><?php echo htmlspecialchars($pr['no_pesanan']); ?></a></td>
                        </tr>
                        <tr>
                            <td class="text-muted py-0">Nama Vendor</td>
                            <td class="font-weight-bold py-0"><?php echo htmlspecialchars($pr['nama_vendor']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted py-0">Tgl Diajukan Bayar</td>
                            <td class="py-0"><?php echo format_date($pr['tgl_pengajuan']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted py-0">Status Pengajuan</td>
                            <td class="py-0"><?php echo get_payment_badge($pr['status']); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bp-panel bp-panel-amber mb-0" style="flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0;">
            <div class="bp-panel-header" style="padding:0.4rem 0.8rem;">
                <div><i class="fas fa-gavel mr-2"></i> Tindakan Persetujuan</div>
            </div>
            
            <div class="bp-panel-body" style="padding: 0.6rem; flex: 1 1 auto; overflow-y: auto;">
                <?php if ($user_role === 'direktur' && $pr['status'] === 'diajukan'): ?>
                    <form action="home.php?page=pembayaran_detail&id=<?php echo $pr['id']; ?>" method="POST" id="form-pay-approve">
                        <input type="hidden" name="process_payment_approval" value="1">
                        <input type="hidden" name="status" id="pay-status-input" value="">
                        
                        <div class="form-group mb-3">
                            <label for="catatan" class="bp-field-label">Catatan / Keterangan Direktur:</label>
                            <textarea name="catatan" id="catatan" class="form-control form-control-sm bp-input" rows="3" placeholder="Masukkan instruksi transfer atau catatan penolakan..."></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="bp-btn-draft flex-fill mr-1 font-weight-bold" style="color:#ef4444; border-color:#ef4444;" onclick="document.getElementById('pay-status-input').value='ditolak';">
                                <i class="fas fa-times mr-1"></i> Tolak Pembayaran
                            </button>
                            <button type="submit" class="bp-btn-submit flex-fill font-weight-bold p-1 m-0" style="font-size:0.8rem; border-radius:0.4rem;" onclick="document.getElementById('pay-status-input').value='acc';">
                                <i class="fas fa-check mr-1"></i> ACC Pembayaran
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="status-panel-info <?php echo ($pr['status'] === 'acc') ? 'acc' : (($pr['status'] === 'ditolak') ? 'ditolak' : ''); ?>">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p class="mb-0">
                            <?php if ($pr['status'] === 'acc'): ?>
                                <span class="font-weight-bold" style="font-size: 1.05rem;">Pengajuan telah disetujui (ACC)</span><br>
                                Tanggal: <?php echo format_date($pr['tgl_acc']); ?>
                            <?php elseif ($pr['status'] === 'ditolak'): ?>
                                <span class="font-weight-bold" style="font-size: 1.05rem;">Pengajuan telah ditolak</span>
                            <?php else: ?>
                                Tidak ada aksi diperlukan.
                            <?php endif; ?>
                        </p>
                        <?php if ($pr['catatan_direktur'] !== ''): ?>
                            <div class="mt-3 text-secondary font-italic p-2 bg-white rounded border" style="box-shadow: var(--shadow-sm);">
                                "<?php echo htmlspecialchars($pr['catatan_direktur']); ?>"
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once dirname(__FILE__) . '/../includes/footer.php';
?>
