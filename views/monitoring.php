<?php
// views/monitoring.php
// Monitoring Purchase Orders list and detail view with Director approvals

if (!defined('FRONT_CONTROLLER')) {
    header("Location: /sp_umum/home.php?page=monitoring" . ($_SERVER['QUERY_STRING'] !== '' ? '&' . $_SERVER['QUERY_STRING'] : ''));
    exit;
}

$page_title = 'Monitoring Surat Pesanan';
$active_menu = 'monitoring';

require_once dirname(__FILE__) . '/../includes/auth.php';

$error = '';
$success = '';

// Handle Director PO Approval Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['po_approval_action'])) {
    sp_require_role('direktur');
    
    $po_id = isset($_POST['po_id']) ? (int)$_POST['po_id'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : ''; // 'acc' or 'ditolak'
    $catatan = isset($_POST['catatan']) ? trim($_POST['catatan']) : '';

    if ($po_id <= 0 || !in_array($status, array('acc', 'ditolak'))) {
        $error = 'Data persetujuan tidak valid.';
    } else {
        $msg_status = $status === 'acc' ? 'DISETUJUI (ACC)' : 'DITOLAK';
        if (db_approve_po_request($po_id, $status, $catatan, $_SESSION['user_id'])) {
            $success = "Surat Pesanan berhasil di-update dengan status: $msg_status.";
        } else {
            $error = 'Gagal memproses approval Surat Pesanan.';
        }
    }
}

// Fetch filter options from GET
$f_status = isset($_GET['status']) ? $_GET['status'] : '';
$f_vendor = isset($_GET['vendor']) ? $_GET['vendor'] : '';
$f_tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : '';
$f_tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : '';

// Fetch POs
$orders = db_get_purchase_orders($f_status, $f_vendor, $f_tgl_mulai, $f_tgl_selesai);

// Detail view if po_id parameter is set
$selected_po = null;
$selected_po_items = array();
$selected_po_logs = array();
if (isset($_GET['po_id'])) {
    $po_id = (int)$_GET['po_id'];
    $selected_po = db_get_purchase_order_by_id($po_id);
    if ($selected_po) {
        $selected_po_items = db_get_purchase_order_items($po_id);
        $selected_po_logs = db_get_approval_logs($po_id);
    }
}

require_once dirname(__FILE__) . '/../includes/header.php';
?>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger alert-premium no-print">
        <i class="fas fa-exclamation-circle fa-lg"></i>
        <span><?php echo htmlspecialchars($error); ?></span>
    </div>
<?php endif; ?>

<?php if ($success !== ''): ?>
    <div class="alert alert-success alert-premium no-print">
        <i class="fas fa-check-circle fa-lg"></i>
        <span><?php echo htmlspecialchars($success); ?></span>
    </div>
<?php endif; ?>

<!-- DETAIL VIEW SECTION -->
<?php if ($selected_po): ?>
    <div class="bp-hero no-print" style="padding:0.5rem 1rem; margin-bottom:0.5rem; display:flex; justify-content:space-between; align-items:center;">
        <div>
            <div class="bp-hero-badge"><i class="fas fa-eye"></i> Detail Surat Pesanan</div>
            <h4 class="bp-hero-title" style="font-size:1rem; margin-bottom:0;"><?php echo htmlspecialchars($selected_po['no_sp']); ?></h4>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-premium-secondary mr-2" style="padding:0.2rem 0.6rem; font-size:0.75rem;" onclick="window.print();">
                <i class="fas fa-print"></i> Cetak / PDF
            </button>
            <a href="/sp_umum/home.php?page=monitoring" class="btn btn-sm btn-premium-secondary" style="padding:0.2rem 0.6rem; font-size:0.75rem;">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
    
    <div style="flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0; padding: 0 0.25rem;">
        <div style="flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0; overflow-y: auto;">
            <!-- Printable Invoice Header -->
            <div class="d-none d-print-block text-center mb-4">
                <h3 class="font-weight-bold mb-1">SURAT PESANAN (PURCHASE ORDER)</h3>
                <p class="mb-0">No Pesanan: <strong><?php echo htmlspecialchars($selected_po['no_sp']); ?></strong></p>
                <hr style="border-top: 2px solid #000000; margin-top: 0.625rem;">
            </div>

            <!-- Visual Progress Tracker Tracker (No Print) -->
            <div class="no-print" style="transform: scale(0.9); transform-origin: left top; margin-bottom: -1rem;">
                <h6 class="font-weight-bold text-secondary mb-1"><i class="fas fa-map-signs mr-1"></i> Progres Persetujuan SP:</h6>
                <?php
                $percent = 0;
                $step1 = 'completed';
                $step2 = 'completed';
                $step3 = '';
                $step4 = '';
                
                if ($selected_po['status'] === 'draft') {
                    $percent = 25;
                    $step2 = 'active';
                } elseif ($selected_po['status'] === 'diajukan') {
                    $percent = 50;
                    $step2 = 'completed';
                    $step3 = 'active';
                } elseif ($selected_po['status'] === 'direview') {
                    $percent = 75;
                    $step2 = 'completed';
                    $step3 = 'completed';
                    $step4 = 'active';
                } elseif ($selected_po['status'] === 'acc') {
                    $percent = 100;
                    $step2 = 'completed';
                    $step3 = 'completed';
                    $step4 = 'completed active';
                } elseif ($selected_po['status'] === 'ditolak') {
                    $percent = 100;
                    $step2 = 'completed';
                    $step3 = 'completed';
                    $step4 = 'active';
                }
                ?>
                <div class="progress-tracker">
                    <div class="progress-tracker-line" style="width: <?php echo $percent; ?>%;"></div>
                    
                    <div class="progress-step completed">
                        <div class="progress-dot">1</div>
                        <span class="progress-step-label">Draft SP</span>
                    </div>
                    <div class="progress-step <?php echo $step2; ?>">
                        <div class="progress-dot">2</div>
                        <span class="progress-step-label">Diajukan</span>
                    </div>
                    <div class="progress-step <?php echo $step3; ?>">
                        <div class="progress-dot">3</div>
                        <span class="progress-step-label">Direview</span>
                    </div>
                    <div class="progress-step <?php echo $step4; ?>">
                        <div class="progress-dot">
                            <?php if ($selected_po['status'] === 'ditolak'): ?>
                                <i class="fas fa-times"></i>
                            <?php else: ?>
                                4
                            <?php endif; ?>
                        </div>
                        <span class="progress-step-label <?php echo $selected_po['status'] === 'ditolak' ? 'text-danger' : ''; ?>">
                            <?php echo $selected_po['status'] === 'ditolak' ? 'Ditolak' : 'ACC / Selesai'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Detail Grid Info -->
            <div class="row pt-2 mb-2 flex-shrink-0 no-print" style="margin-left:-0.25rem; margin-right:-0.25rem;">
                <div class="col-md-6 px-1 mb-2 mb-md-0">
                    <div class="bp-panel bp-panel-violet mb-0" style="height:100%;">
                        <div class="bp-panel-header"><i class="fas fa-file-contract mr-2"></i> Informasi Header SP</div>
                        <div class="bp-panel-body" style="padding: 0.4rem;">
                            <table class="table table-sm table-borderless font-size-sm mb-0" style="line-height:1.2;">
                                <tr>
                                    <td class="text-muted py-0" style="width: 7.5rem;">Nomor Pesanan</td>
                                    <td class="py-0">: <strong class="text-dark"><?php echo htmlspecialchars($selected_po['no_sp']); ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-0">Tanggal Pesanan</td>
                                    <td class="py-0">: <?php echo format_date($selected_po['tgl_pesanan']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-0">Nama Vendor</td>
                                    <td class="py-0">: <strong class="text-secondary"><?php echo htmlspecialchars($selected_po['nama_vendor']); ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-0">Dibuat Oleh</td>
                                    <td class="py-0">: <?php echo htmlspecialchars($selected_po['pembuat_nama']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 px-1">
                    <div class="bp-panel bp-panel-teal mb-0" style="height:100%;">
                        <div class="bp-panel-header"><i class="fas fa-calculator mr-2"></i> Ringkasan Biaya</div>
                        <div class="bp-panel-body" style="padding: 0.4rem;">
                            <table class="table table-sm table-borderless font-size-sm mb-0" style="line-height:1.2;">
                                <tr>
                                    <td class="text-muted py-0" style="width: 7.5rem;">Harga Vendor</td>
                                    <td class="text-right font-weight-bold py-0">: <?php echo format_rupiah($selected_po['harga_vendor']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-0">Potongan Diskon</td>
                                    <td class="text-right text-danger font-weight-bold py-0">: - <?php echo format_rupiah($selected_po['diskon_vendor']); ?></td>
                                </tr>
                                <tr class="border-top">
                                    <td class="font-weight-bold text-primary py-1">Total Harga Net</td>
                                    <td class="text-right font-weight-bold text-primary py-1" style="font-size: 0.85rem;">: <?php echo format_rupiah($selected_po['total_setelah_diskon']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-0">Status Approval</td>
                                    <td class="text-right py-0">: <?php echo get_status_badge($selected_po['status']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table detail -->
            <div class="bp-panel bp-panel-amber mb-2" style="flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0;">
                <div class="bp-panel-header"><i class="fas fa-boxes mr-2"></i> Rincian Barang Pesanan</div>
                <div class="bp-panel-body p-0" style="flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0;">
                    <div class="table-responsive-sticky" style="flex: 1 1 auto; overflow-y: auto;">
                    <table class="table table-sm table-bordered bp-items-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 2.5rem;">No</th>
                                <th>Nama Barang</th>
                                <th>Merk</th>
                                <th>Model</th>
                                <th>Spec</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Satuan</th>
                                <th class="text-right">Harga Satuan</th>
                                <th class="text-right">Diskon</th>
                                <th class="text-right">Subtotal</th>
                                <th class="text-center" style="width: 9rem;">Status Terima</th>
                                <th class="text-center" style="width: 7rem;">Diterima</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $item_idx = 1; foreach ($selected_po_items as $item): ?>
                                <tr>
                                    <td class="text-center"><?php echo $item_idx++; ?></td>
                                    <td class="font-weight-bold text-dark"><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                                    <td><?php echo htmlspecialchars($item['merk']); ?></td>
                                    <td><?php echo htmlspecialchars($item['model']); ?></td>
                                    <td><?php echo htmlspecialchars($item['spec']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['jumlah']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['satuan']); ?></td>
                                    <td class="text-right"><?php echo format_rupiah($item['harga_satuan']); ?></td>
                                    <td class="text-right text-danger"><?php echo $item['diskon_item'] > 0 ? '- ' . format_rupiah($item['diskon_item']) : '-'; ?></td>
                                    <td class="text-right font-weight-bold"><?php echo format_rupiah($item['subtotal']); ?></td>
                                    <td class="text-center"><?php echo get_receipt_badge($item['status_terima']); ?></td>
                                    <td class="text-center font-weight-bold text-info"><?php echo $item['jumlah_diterima']; ?> / <?php echo $item['jumlah']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>


            <!-- Split Panel: Logs and Approval Forms -->
            <div class="row mt-2 flex-shrink-0 no-print" style="margin-left:-0.25rem; margin-right:-0.25rem;">
                <!-- Audit Trail Logs -->
                <div class="col-md-6 px-1">
                    <div class="bp-panel bp-panel-slate mb-0">
                        <div class="bp-panel-header"><i class="fas fa-history mr-2"></i> Log Riwayat Persetujuan (Audit Trail)</div>
                        <div class="bp-panel-body" style="padding:0.4rem;">
                            <?php if (empty($selected_po_logs)): ?>
                                <p class="text-muted font-italic mb-0" style="font-size:0.75rem;">Belum ada riwayat persetujuan untuk pesanan ini.</p>
                            <?php else: ?>
                                <div style="max-height: 8rem; overflow-y: auto;">
                                    <ul class="list-group list-group-flush" style="font-size: 0.75rem;">
                                        <?php foreach ($selected_po_logs as $log): ?>
                                            <li class="list-group-item px-1 pb-1 pt-1 border-bottom">
                                                <div class="d-flex justify-content-between">
                                                    <strong class="text-dark"><?php echo htmlspecialchars($log['user_nama']); ?> (<?php echo strtoupper($log['user_role']); ?>)</strong>
                                                    <span class="text-muted"><?php echo date('d M Y H:i', strtotime($log['tanggal'])); ?></span>
                                                </div>
                                                <div class="mt-1">
                                                    Status: <?php echo get_status_badge($log['status']); ?>
                                                </div>
                                                <?php if ($log['catatan'] !== ''): ?>
                                                    <div class="mt-1 text-secondary bg-light p-1 rounded font-italic">
                                                        "<?php echo htmlspecialchars($log['catatan']); ?>"
                                                    </div>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Approval Forms -->
                <div class="col-md-6 no-print">
                </div>
            </div>
        </div> <!-- end scrollable content -->
            
            <!-- Approval Action Bar for Direktur -->
            <div class="flex-shrink-0 mt-2">
                <?php if ($user_role === 'direktur' && $selected_po['status'] === 'diajukan'): ?>
                    <div class="bp-action-bar no-print">
                        <div class="bp-action-hint">
                            <i class="fas fa-exclamation-triangle text-warning mr-1"></i>
                            <strong>Tindakan Dibutuhkan:</strong> Tinjau rincian SP ini. Anda dapat menyetujui (ACC) atau menolak permintaan ini.
                        </div>
                        <div style="flex: 1; margin-left: 1rem;">
                            <form action="home.php?page=monitoring&po_id=<?php echo $selected_po['id']; ?>" method="POST" id="form-approval" class="d-flex align-items-center justify-content-end gap-2" style="gap:0.5rem;">
                                <input type="hidden" name="po_approval_action" value="1">
                                <input type="hidden" name="po_id" value="<?php echo $selected_po['id']; ?>">
                                <input type="hidden" name="status" id="approval-status-input" value="">
                                
                                <input type="text" name="catatan" class="form-control form-control-sm bp-input mr-2" placeholder="Catatan opsional..." style="max-width: 15rem; font-size:0.75rem;">
                                
                                <button type="submit" class="bp-btn-draft flex-shrink-0" style="color:#ef4444; border-color:#ef4444;" onclick="document.getElementById('approval-status-input').value='ditolak';">
                                    <i class="fas fa-times mr-1"></i> Tolak Permintaan
                                </button>
                                <button type="submit" class="btn btn-sm btn-premium flex-shrink-0 font-weight-bold" onclick="document.getElementById('approval-status-input').value='acc';">
                                    <i class="fas fa-check mr-1"></i> ACC Permintaan
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
<?php endif; ?>

<!-- PO LIST & FILTER BOARD SECTION -->
<div class="bp-hero no-print" style="padding:0.5rem 1rem; margin-bottom:0.5rem;">
    <div class="bp-hero-badge"><i class="fas fa-desktop"></i> Monitoring SP</div>
    <h4 class="bp-hero-title" style="font-size:1rem; margin-bottom:0;">Daftar Surat Pesanan</h4>
</div>

<div class="bp-panel bp-panel-slate no-print" style="flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0; margin-bottom: 0;">
    <div class="bp-panel-body" style="padding: 0.5rem; flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0;">
        <!-- Filter Form -->
        <form action="home.php" method="GET" class="mb-2 flex-shrink-0">
            <input type="hidden" name="page" value="monitoring">
            <div class="row align-items-end" style="margin-left:-0.25rem; margin-right:-0.25rem;">
                <div class="col-md-3 col-sm-6 mb-1 px-1">
                    <label for="status" class="bp-field-label">Status Pesanan</label>
                    <select name="status" id="status" class="form-control form-control-sm bp-input">
                        <option value="">-- Semua Status --</option>
                        <option value="draft" <?php echo $f_status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="diajukan" <?php echo $f_status === 'diajukan' ? 'selected' : ''; ?>>Diajukan</option>
                        <option value="direview" <?php echo $f_status === 'direview' ? 'selected' : ''; ?>>Direview</option>
                        <option value="acc" <?php echo $f_status === 'acc' ? 'selected' : ''; ?>>Disetujui (ACC)</option>
                        <option value="ditolak" <?php echo $f_status === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-1 px-1">
                    <label for="vendor" class="bp-field-label">Nama Vendor</label>
                    <input type="text" name="vendor" id="vendor" class="form-control form-control-sm bp-input" placeholder="Cari nama vendor..." value="<?php echo htmlspecialchars($f_vendor); ?>">
                </div>
                
                <div class="col-md-2 col-sm-6 mb-1 px-1">
                    <label for="tgl_mulai" class="bp-field-label">Tgl Mulai</label>
                    <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control form-control-sm bp-input" value="<?php echo htmlspecialchars($f_tgl_mulai); ?>">
                </div>
                
                <div class="col-md-2 col-sm-6 mb-1 px-1">
                    <label for="tgl_selesai" class="bp-field-label">Tgl Selesai</label>
                    <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control form-control-sm bp-input" value="<?php echo htmlspecialchars($f_tgl_selesai); ?>">
                </div>
                
                <div class="col-md-2 col-sm-12 mb-1 px-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-premium btn-block m-0" style="padding:0.2rem 0.5rem; font-size:0.75rem;">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <a href="/sp_umum/home.php?page=monitoring" class="btn btn-sm btn-premium-secondary ml-1" style="padding:0.2rem 0.5rem; font-size:0.75rem;">
                        <i class="fas fa-sync"></i>
                    </a>
                </div>
            </div>
        </form>
        
        <!-- Table List -->
        <div class="table-responsive-sticky" style="flex: 1 1 auto; overflow-y: auto;">
            <table class="table table-bordered mb-0 bp-items-table">
                <thead>
                    <tr>
                        <th>No Pesanan</th>
                        <th>Tanggal SP</th>
                        <th>Vendor</th>
                        <th class="text-right">Total Net</th>
                        <th>Dibuat Oleh</th>
                        <th class="text-center">Status SP</th>
                        <th class="text-center">Status Bayar</th>
                        <th class="text-center" style="width: 6.25rem;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Data Surat Pesanan tidak ditemukan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $po): ?>
                            <tr>
                                <td class="font-weight-bold text-dark"><?php echo htmlspecialchars($po['no_sp']); ?></td>
                                <td><?php echo format_date($po['tgl_pesanan']); ?></td>
                                <td><?php echo htmlspecialchars($po['nama_vendor']); ?></td>
                                <td class="text-right font-weight-bold"><?php echo format_rupiah($po['total_setelah_diskon']); ?></td>
                                <td><?php echo htmlspecialchars($po['pembuat_nama']); ?></td>
                                <td class="text-center"><?php echo get_status_badge($po['status']); ?></td>
                                <td class="text-center"><?php echo get_payment_badge($po['status_bayar']); ?></td>
                                <td class="text-center">
                                    <a href="/sp_umum/home.php?page=monitoring&po_id=<?php echo $po['id']; ?>" class="btn btn-sm btn-premium py-1 px-3">
                                        <i class="fas fa-eye mr-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once dirname(__FILE__) . '/../includes/footer.php';
?>
