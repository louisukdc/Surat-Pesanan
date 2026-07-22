<?php
// views/monitoring.php
// Monitoring Purchase Orders list and detail view with Director approvals

if (!defined('FRONT_CONTROLLER')) {
    header("Location: ../home.php?page=monitoring" . ($_SERVER['QUERY_STRING'] !== '' ? '&' . $_SERVER['QUERY_STRING'] : ''));
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
    $pembayaran = isset($_POST['pembayaran']) ? trim($_POST['pembayaran']) : null;
    $pembayaran1 = isset($_POST['pembayaran1']) ? trim($_POST['pembayaran1']) : null;

    if ($po_id <= 0 || !in_array($status, array('acc', 'ditolak'))) {
        $error = 'Data persetujuan tidak valid.';
    } else {
        if ($pembayaran !== null) {
            $esc_p = db_escape($pembayaran);
            $esc_p1 = db_escape($pembayaran1);
            mysqli_query($GLOBALS['db_conn'], "UPDATE spu_h SET pembayaran = '$esc_p', pembayaran1 = '$esc_p1' WHERE id = $po_id");
        }

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

// Prioritaskan yang status DIAJUKAN
usort($orders, function($a, $b) {
    if ($a['status'] === 'diajukan' && $b['status'] !== 'diajukan') return -1;
    if ($a['status'] !== 'diajukan' && $b['status'] === 'diajukan') return 1;
    return $b['id'] < $a['id'] ? -1 : ($b['id'] > $a['id'] ? 1 : 0);
});

// Pagination Setup
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$limit = 20;
$total_data = count($orders);
$total_pages = ceil($total_data / $limit);
if ($total_pages == 0) $total_pages = 1;
if ($page > $total_pages) $page = $total_pages;

$offset = ($page - 1) * $limit;
$orders = array_slice($orders, $offset, $limit);

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
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: <?php echo json_encode($error); ?>,
            confirmButtonColor: '#3b82f6',
            background: '#ffffff',
            customClass: {
                popup: 'rounded-lg shadow-lg'
            }
        });
    });
    </script>
<?php endif; ?>

<?php if ($success !== ''): ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: <?php echo json_encode($success); ?>,
            confirmButtonColor: '#3b82f6',
            background: '#ffffff',
            customClass: {
                popup: 'rounded-lg shadow-lg'
            }
        });
    });
    </script>
<?php endif; ?>

<!-- DETAIL VIEW SECTION -->
<?php if ($selected_po): ?>
    <div class="bp-hero no-print" style="padding:0.5rem 1rem; margin-bottom:0.5rem; display:flex; justify-content:space-between; align-items:center;">
        <div>
            <div class="bp-hero-badge"><i class="fas fa-eye"></i> Detail Surat Pesanan</div>
            <h4 class="bp-hero-title" style="font-size:1rem; margin-bottom:0;"><?php echo htmlspecialchars($selected_po['no_sp']); ?></h4>
        </div>
        <div>
            <?php if (in_array($selected_po['status'], array('draft', 'ditolak', 'diajukan'))): ?>
                <a href="home.php?page=buat_pesanan&edit_id=<?php echo $selected_po['id']; ?>" class="btn btn-sm btn-warning mr-2" style="padding:0.2rem 0.6rem; font-size:0.75rem;">
                    <i class="fas fa-edit"></i> Edit
                </a>
            <?php endif; ?>
            <button type="button" class="btn btn-sm btn-premium-secondary mr-2" style="padding:0.2rem 0.6rem; font-size:0.75rem;" data-toggle="modal" data-target="#printPreviewModal">
                <i class="fas fa-print"></i> Preview & Cetak
            </button>
            <a href="home.php?page=monitoring" class="btn btn-sm btn-premium-secondary" style="padding:0.2rem 0.6rem; font-size:0.75rem;">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
    
    <div style="flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0; padding: 0 0.25rem;">
        <div style="flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0; overflow-y: auto;">
            <!-- Printable Invoice -->
            <div id="print-layout-source" class="d-none d-print-block" style="font-family: 'Times New Roman', Times, serif; color: #000; width: 100%;">
                <h3 class="text-center font-weight-bold" style="font-size: 16pt; text-decoration: underline; margin-bottom: 0.2rem;">SURAT PESANAN</h3>
                <p class="text-center" style="font-size: 12pt; margin-bottom: 2rem;">No. <?php echo htmlspecialchars($selected_po['no_sp']); ?></p>

                <div style="font-size: 12pt; margin-bottom: 2rem;">
                    <p class="mb-0">Kepada Yth :</p>
                    <p class="mb-0"><strong><?php echo htmlspecialchars($selected_po['nama_vendor']); ?></strong></p>
                    <?php if (!empty($selected_po['supplier_alamat'])): ?>
                        <p class="mb-0"><?php echo htmlspecialchars($selected_po['supplier_alamat']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($selected_po['supplier_kota'])): ?>
                        <p class="mb-0"><?php echo htmlspecialchars($selected_po['supplier_kota']); ?></p>
                    <?php endif; ?>
                </div>

                <div style="font-size: 12pt; margin-bottom: 1rem;">
                    <p>Berdasarkan Surat Penawaran Saudara No : <?php echo isset($selected_po['no_tawar']) ? htmlspecialchars((string)$selected_po['no_tawar']) : '-'; ?><br>
                    tertanggal <?php echo isset($selected_po['tgl_tawar']) && !empty($selected_po['tgl_tawar']) ? format_date($selected_po['tgl_tawar']) : '-'; ?> dengan ini kami memesan :</p>
                </div>

                <?php
                // Logic to check which columns have data
                $has_model = false;
                $has_merk  = false;
                $has_spec  = false;
                $has_disc  = false;

                foreach ($selected_po_items as $itm) {
                    if (trim((string)$itm['model']) !== '') $has_model = true;
                    if (trim((string)$itm['merk']) !== '') $has_merk = true;
                    if (trim((string)$itm['spec']) !== '') $has_spec = true;
                    if ((float)$itm['diskon_item'] > 0) $has_disc = true;
                }
                ?>

                <table class="table table-sm table-bordered" style="border-color: #000; font-size: 11pt; margin-bottom: 0.5rem;">
                    <thead style="border-bottom: 2px solid #000;">
                        <tr>
                            <th class="text-center align-middle" style="border-color:#000;">Barang</th>
                            <th class="text-center align-middle" style="border-color:#000; width: 40px;">Qty</th>
                            <th class="text-center align-middle" style="border-color:#000;">Satuan</th>
                            <?php if ($has_model): ?><th class="text-center align-middle" style="border-color:#000;">Tipe</th><?php endif; ?>
                            <?php if ($has_merk): ?><th class="text-center align-middle" style="border-color:#000;">Merk</th><?php endif; ?>
                            <?php if ($has_spec): ?><th class="text-center align-middle" style="border-color:#000;">Spec</th><?php endif; ?>
                            <th class="text-center align-middle" style="border-color:#000;">Harga</th>
                            <?php if ($has_disc): ?><th class="text-center align-middle" style="border-color:#000;">Disc</th><?php endif; ?>
                            <th class="text-center align-middle" style="border-color:#000;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($selected_po_items as $itm): ?>
                        <tr>
                            <td style="border-color:#000;"><?php echo htmlspecialchars($itm['nama_barang']); ?></td>
                            <td class="text-center align-middle" style="border-color:#000;"><?php echo htmlspecialchars($itm['jumlah']); ?></td>
                            <td class="text-center align-middle" style="border-color:#000;"><?php echo htmlspecialchars($itm['satuan']); ?></td>
                            <?php if ($has_model): ?><td class="align-middle" style="border-color:#000;"><?php echo htmlspecialchars($itm['model']); ?></td><?php endif; ?>
                            <?php if ($has_merk): ?><td class="align-middle" style="border-color:#000;"><?php echo htmlspecialchars($itm['merk']); ?></td><?php endif; ?>
                            <?php if ($has_spec): ?><td class="align-middle" style="border-color:#000;"><?php echo htmlspecialchars($itm['spec']); ?></td><?php endif; ?>
                            <td class="text-right align-middle" style="border-color:#000;"><?php echo number_format($itm['harga_satuan'], 0, ',', '.'); ?></td>
                            <?php if ($has_disc): ?><td class="text-right align-middle" style="border-color:#000;"><?php echo number_format($itm['diskon_item'], 0, ',', '.'); ?></td><?php endif; ?>
                            <td class="text-right align-middle" style="border-color:#000;"><?php echo number_format($itm['subtotal'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="text-right mt-2 mb-4" style="font-size: 11pt;">
                    <div style="display:inline-block; text-align:right;">
                        <?php 
                            $p_subtotal = isset($selected_po['total_setelah_diskon']) ? (float)$selected_po['total_setelah_diskon'] : (isset($selected_po['harga_vendor']) ? (float)$selected_po['harga_vendor'] : 0);
                            $p_ppn = isset($selected_po['ppn_nominal']) ? (float)$selected_po['ppn_nominal'] : 0;
                            $p_gtotal = isset($selected_po['grand_total']) ? (float)$selected_po['grand_total'] : ($p_subtotal + $p_ppn);
                        ?>
                        <div>Sub Total <span style="display:inline-block; width:30px; text-align:left; margin-left:10px;">Rp</span> <?php echo number_format($p_subtotal, 0, ',', '.'); ?></div>
                        <?php if ($p_ppn > 0): ?>
                        <div>PPN <span style="display:inline-block; width:30px; text-align:left; margin-left:10px;">Rp</span> <?php echo number_format($p_ppn, 0, ',', '.'); ?></div>
                        <?php endif; ?>
                        <div class="font-weight-bold mt-1 pt-1" style="border-top:1px solid #000;">
                            Grand Total <span style="display:inline-block; width:30px; text-align:left; margin-left:10px;">Rp</span> <?php echo number_format($p_gtotal, 0, ',', '.'); ?>
                        </div>
                    </div>
                </div>

                <div style="font-size: 12pt;">
                    <p class="mb-1">Dengan :</p>
                    <?php
                        $dir_note = '';
                        if (!empty($selected_po_logs)) {
                            foreach ($selected_po_logs as $log) {
                                if ($log['status'] === 'acc' && $log['user_role'] === 'direktur' && !empty(trim($log['catatan']))) {
                                    $dir_note = trim($log['catatan']);
                                    break;
                                }
                            }
                        }
                        
                        $cara_bayar = array();
                        if (!empty($selected_po['pembayaran'])) {
                            $cara_bayar[] = trim((string)$selected_po['pembayaran']);
                        }
                        if (!empty($selected_po['pembayaran1'])) {
                            $cara_bayar[] = trim((string)$selected_po['pembayaran1']);
                        }
                        $bayar_str = implode(' ', $cara_bayar);
                        if ($dir_note !== '') {
                            $bayar_str .= ' ' . $dir_note;
                        }
                        if (trim($bayar_str) === '') $bayar_str = '-';
                    ?>
                    <p class="mb-1">Cara Pembayaran : <?php echo htmlspecialchars($bayar_str); ?></p>
                    <p class="mb-1">Catatan :<br><?php echo nl2br(htmlspecialchars(isset($selected_po['noteout']) ? (string)$selected_po['noteout'] : '')); ?></p>
                </div>
                
                <div class="mt-4" style="font-size: 12pt;">
                    <p>Terima Kasih atas perhatian dan kerjasamanya.</p>
                </div>
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
                                    <td class="text-muted py-0" style="white-space: nowrap; width: 1%;">Nomor Pesanan</td>
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
                                <tr>
                                    <td class="text-muted py-0">Cara Pembayaran 1</td>
                                    <td class="py-0">: <?php echo isset($selected_po['pembayaran']) && $selected_po['pembayaran'] !== '' ? htmlspecialchars((string)$selected_po['pembayaran']) : '-'; ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-0">Cara Pembayaran 2</td>
                                    <td class="py-0">: <?php echo isset($selected_po['pembayaran1']) && $selected_po['pembayaran1'] !== '' ? htmlspecialchars((string)$selected_po['pembayaran1']) : '-'; ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-0">Catatan Internal</td>
                                    <td class="py-0 text-danger">: <?php echo isset($selected_po['notein']) && $selected_po['notein'] !== '' ? htmlspecialchars((string)$selected_po['notein']) : '-'; ?></td>
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
            <div class="bp-panel bp-panel-amber mb-2 no-print" style="flex: 1 1 auto; display: flex; flex-direction: column; min-height: 250px;">
                <div class="bp-panel-header"><i class="fas fa-boxes mr-2"></i> Rincian Barang Pesanan</div>
                <div class="bp-panel-body p-0" style="flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0;">
                    <div class="table-responsive-sticky" style="flex: 1 1 auto; overflow-y: auto;">
                    <table class="table table-sm table-bordered bp-items-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 2.5rem;">No</th>
                                <th>Nama Barang</th>
                                <th>Merk</th>
                                <th>Tipe</th>
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
                                                    <?php 
                                                        $safe_catatan = htmlspecialchars($log['catatan']);
                                                        // Highlight the specific text with a light yellow marker
                                                        $pattern = '/(\(Pembelian\) karena nominal pesanan di bawah 5 Juta\.?)/i';
                                                        $safe_catatan = preg_replace($pattern, '<mark style="background-color: #fef08a; padding: 0.1em 0.3em; border-radius: 0.2em; font-weight: 500;">$1</mark>', $safe_catatan);
                                                    ?>
                                                    <div class="mt-1 text-secondary bg-light p-1 rounded font-italic">
                                                        "<?php echo $safe_catatan; ?>"
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

                <!-- Approval Forms & Attachments -->
                <div class="col-md-6 no-print">
                    <?php if (!empty($selected_po['nama_lampiran'])): ?>
                    <div class="bp-panel bp-panel-slate mb-2">
                        <div class="bp-panel-header"><i class="fas fa-file-invoice mr-2"></i> Lampiran Surat Pesanan</div>
                        <div class="bp-panel-body" style="padding:1rem;">
                            <?php 
                            $lampiran_files = explode(',', $selected_po['nama_lampiran']);
                            foreach ($lampiran_files as $idx => $file): 
                                $file = trim($file);
                                if (empty($file)) continue;
                                // Anggap default folder uploads/lampiran/
                                $file_url = 'uploads/lampiran/' . $file;
                            ?>
                                <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank" class="btn btn-sm btn-outline-primary font-weight-bold mr-2 mb-2">
                                    <i class="fas fa-file-download mr-1"></i> Lampiran <?php echo $idx + 1; ?>
                                </a>
                            <?php endforeach; ?>
                            <p class="text-muted small mt-1 mb-0">Dokumen lampiran yang diunggah saat pesanan dibuat.</p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($selected_po['lampiran_penerimaan'])): ?>
                    <div class="bp-panel bp-panel-slate mb-0">
                        <div class="bp-panel-header"><i class="fas fa-paperclip mr-2"></i> Lampiran Penerimaan Barang</div>
                        <div class="bp-panel-body text-center" style="padding:1.5rem;">
                            <a href="<?php echo htmlspecialchars($selected_po['lampiran_penerimaan']); ?>" target="_blank" class="btn btn-info font-weight-bold">
                                <i class="fas fa-file-download mr-1"></i> Buka Lampiran
                            </a>
                            <p class="text-muted small mt-2 mb-0">File lampiran surat jalan atau bukti penerimaan.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div> <!-- end scrollable content -->
            
            <!-- Approval Action Bar for Direktur -->
            <div class="flex-shrink-0 mt-2">
                <?php if ($user_role === 'direktur' && $selected_po['status'] === 'diajukan'): ?>
                    <div class="card shadow-sm border-0 no-print mt-2" style="border-radius: 8px; background: linear-gradient(to right, #ffffff, #f8fafc); border-left: 4px solid #3b82f6 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2 pb-2" style="border-bottom: 1px solid #e2e8f0;">
                                <div class="icon-circle text-primary mr-2" style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #dbeafe;">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 font-weight-bold" style="color: #1e293b; font-size: 0.95rem;">Keputusan Direktur</h6>
                                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">
                                        <i class="fas fa-info-circle text-info mr-1"></i> Tinjau dan isi catatan sebelum ACC atau Tolak.
                                    </p>
                                </div>
                            </div>

                            <form action="home.php?page=monitoring&po_id=<?php echo $selected_po['id']; ?>" method="POST" id="form-approval">
                                <input type="hidden" name="po_approval_action" value="1">
                                <input type="hidden" name="po_id" value="<?php echo $selected_po['id']; ?>">
                                <input type="hidden" name="status" id="approval-status-input" value="">
                                
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.75rem; letter-spacing: 0.2px;">Cara Pembayaran</label>
                                            <select name="pembayaran" class="form-control form-control-sm bp-input" style="height: auto; padding: 0.3rem 0.5rem; font-size: 0.85rem; border-color: #cbd5e1;">
                                                <?php 
                                                $opts_bayar = array('Tunai / Cash','Transfer Bank','Kredit 30 Hari','Kredit 60 Hari','Kredit 90 Hari','Giro');
                                                foreach ($opts_bayar as $ob) {
                                                    $sel = (isset($selected_po['pembayaran']) && $selected_po['pembayaran'] === $ob) ? 'selected' : '';
                                                    echo "<option value=\"$ob\" $sel>$ob</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group mb-2 mb-md-0">
                                            <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.75rem; letter-spacing: 0.2px;">Keterangan (Opsional)</label>
                                            <input type="text" name="pembayaran1" class="form-control form-control-sm bp-input" placeholder="Contoh: DP 50%, termin 2..." value="<?php echo isset($selected_po['pembayaran1']) ? htmlspecialchars((string)$selected_po['pembayaran1']) : ''; ?>" style="padding: 0.3rem 0.5rem; font-size: 0.85rem; border-color: #cbd5e1;">
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="form-group mb-0" style="height: 100%; display: flex; flex-direction: column;">
                                            <label class="font-weight-bold text-secondary mb-1" style="font-size: 0.75rem; letter-spacing: 0.2px;">Catatan Keputusan</label>
                                            <textarea name="catatan" class="form-control bp-input" placeholder="Tuliskan catatan persetujuan atau alasan penolakan di sini..." style="flex: 1; min-height: 80px; resize: none; padding: 0.5rem; line-height: 1.4; font-size: 0.85rem; border-color: #cbd5e1;"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end align-items-center mt-3 pt-2" style="border-top: 1px dashed #cbd5e1; gap: 0.5rem;">
                                    <button type="submit" class="bp-btn-draft d-flex align-items-center justify-content-center" style="color:#ef4444; border-color:#ef4444; padding: 0.4rem 1rem; font-size: 0.85rem; border-width: 1px; font-weight: 600; background: white; border-radius: 6px; transition: all 0.2s ease;" onclick="document.getElementById('approval-status-input').value='ditolak';" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='white'">
                                        <i class="fas fa-times-circle mr-2"></i> Tolak Permintaan
                                    </button>
                                    <button type="submit" class="btn btn-premium d-flex align-items-center justify-content-center font-weight-bold" style="padding: 0.4rem 1.2rem; font-size: 0.85rem; border-radius: 6px; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3); transition: all 0.2s ease;" onclick="document.getElementById('approval-status-input').value='acc';" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                                        <i class="fas fa-check-circle mr-2"></i> ACC Permintaan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
<?php endif; ?>

<?php if (!$selected_po): ?>
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
                <!-- Baris 1: Status dan Tanggal -->
                <div class="col-md-4 col-sm-4 mb-1 px-1">
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
                
                <div class="col-md-4 col-sm-4 mb-1 px-1">
                    <label for="tgl_mulai" class="bp-field-label">Tgl Mulai</label>
                    <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control form-control-sm bp-input" value="<?php echo htmlspecialchars($f_tgl_mulai); ?>">
                </div>
                
                <div class="col-md-4 col-sm-4 mb-1 px-1">
                    <label for="tgl_selesai" class="bp-field-label">Tgl Selesai</label>
                    <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control form-control-sm bp-input" value="<?php echo htmlspecialchars($f_tgl_selesai); ?>">
                </div>
                
                <!-- Baris 2: Pencarian Vendor dan Tombol Cari -->
                <div class="col-md-8 col-sm-8 mb-1 px-1 mt-1">
                    <label for="vendor" class="bp-field-label">Pencarian</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="vendor" id="vendor" class="form-control bp-input border-left-0 pl-0" placeholder="Pencarian Universal: No SP / Nama Vendor / Nama Barang..." value="<?php echo htmlspecialchars($f_vendor); ?>">
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-4 mb-1 px-1 mt-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-premium flex-grow-1 m-0" style="padding:0.2rem 0.5rem; font-size:0.75rem;">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <a href="home.php?page=monitoring" class="btn btn-sm btn-premium-secondary ml-1" style="padding:0.2rem 0.5rem; font-size:0.75rem;">
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
                                <td class="text-center">
                                    <?php echo get_status_badge($po['status']); ?>
                                    <?php
                                    if (in_array($po['status'], ['acc', 'selesai'])) {
                                        $res_acc = mysqli_query($GLOBALS['db_conn'], "SELECT u.NamaUser FROM sp_log_persetujuan l LEFT JOIN sp_user u ON l.oleh = u.id WHERE l.surat_pesanan_id = {$po['id']} AND l.status = 'acc' AND (l.jenis = 'permintaan' OR l.jenis IS NULL OR l.jenis = '') ORDER BY l.id DESC LIMIT 1");
                                        if ($res_acc && $r = mysqli_fetch_assoc($res_acc)) {
                                            echo '<div class="small text-muted mt-1" style="font-size:0.65rem;">ACC: '.htmlspecialchars($r['NamaUser']).'</div>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php echo get_payment_badge($po['status_bayar']); ?>
                                    <?php
                                    if (in_array($po['status_bayar'], ['lunas', 'parsial', 'acc'])) {
                                        $res_pay = mysqli_query($GLOBALS['db_conn'], "SELECT u.NamaUser FROM sp_log_persetujuan l LEFT JOIN sp_user u ON l.oleh = u.id WHERE l.surat_pesanan_id = {$po['id']} AND l.status = 'acc' AND l.jenis = 'pembayaran' ORDER BY l.id DESC LIMIT 1");
                                        if ($res_pay && $r = mysqli_fetch_assoc($res_pay)) {
                                            echo '<div class="small text-muted mt-1" style="font-size:0.65rem;">ACC: '.htmlspecialchars($r['NamaUser']).'</div>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info py-1 px-2" data-toggle="collapse" data-target="#items-<?php echo $po['id']; ?>" title="Lihat Rincian Barang">
                                            <i class="fas fa-box-open"></i>
                                        </button>
                                        <a href="home.php?page=monitoring&po_id=<?php echo $po['id']; ?>" class="btn btn-sm btn-premium py-1 px-2" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (in_array($po['status'], array('draft', 'ditolak', 'diajukan'))): ?>
                                            <a href="home.php?page=buat_pesanan&edit_id=<?php echo $po['id']; ?>" class="btn btn-sm btn-warning py-1 px-2" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr id="items-<?php echo $po['id']; ?>" class="collapse">
                                <td colspan="8" class="p-0">
                                    <div class="p-3 bg-light" style="border-bottom: 2px solid #cbd5e1; box-shadow: inset 0 3px 6px rgba(0,0,0,0.03);">
                                        <h6 class="font-weight-bold text-secondary mb-2" style="font-size:0.85rem;"><i class="fas fa-boxes mr-1 text-info"></i> Rincian Barang Pesanan (Quick View)</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered bg-white mb-0 shadow-sm" style="font-size:0.8rem;">
                                                <thead style="background:#f1f5f9; color:#475569;">
                                                    <tr>
                                                        <th>Nama Barang</th>
                                                        <th>Merk / Tipe</th>
                                                        <th class="text-center">Qty</th>
                                                        <th>Satuan</th>
                                                        <th class="text-right">Harga Satuan</th>
                                                        <th class="text-right">Total Net</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $po_items = db_get_purchase_order_items($po['id']);
                                                    if(empty($po_items)):
                                                    ?>
                                                    <tr><td colspan="6" class="text-center text-muted">Belum ada rincian barang.</td></tr>
                                                    <?php else: foreach($po_items as $itm): ?>
                                                    <tr>
                                                        <td class="font-weight-bold" style="color:#1e293b;"><?php echo htmlspecialchars($itm['nama_barang']); ?></td>
                                                        <td class="text-muted"><?php echo htmlspecialchars($itm['merk']) . ($itm['model'] ? ' / '.htmlspecialchars($itm['model']) : ''); ?></td>
                                                        <td class="text-center font-weight-bold"><?php echo (float)$itm['jumlah']; ?></td>
                                                        <td class="text-muted"><?php echo htmlspecialchars($itm['satuan']); ?></td>
                                                        <td class="text-right"><?php echo format_rupiah($itm['harga_satuan']); ?></td>
                                                        <td class="text-right font-weight-bold" style="color:#059669;"><?php echo format_rupiah($itm['subtotal']); ?></td>
                                                    </tr>
                                                    <?php endforeach; endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-between align-items-center mt-3 px-3 pb-3">
            <span class="text-muted small font-weight-bold">Menampilkan <?php echo $offset + 1; ?> - <?php echo min($offset + $limit, $total_data); ?> dari <?php echo $total_data; ?> data</span>
            <ul class="pagination pagination-sm mb-0 shadow-sm">
                <?php
                $qs = $_GET;
                unset($qs['p']);
                $base_url = 'home.php?' . http_build_query($qs);
                ?>
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo $base_url . '&p=' . ($page - 1); ?>">Sebelumnya</a>
                </li>
                <?php
                $start_p = max(1, $page - 2);
                $end_p = min($total_pages, $page + 2);
                if ($start_p > 1) { echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; }
                for ($i = $start_p; $i <= $end_p; $i++) {
                    echo '<li class="page-item ' . ($i === $page ? 'active' : '') . '"><a class="page-link" href="' . $base_url . '&p=' . $i . '">' . $i . '</a></li>';
                }
                if ($end_p < $total_pages) { echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; }
                ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo $base_url . '&p=' . ($page + 1); ?>">Berikutnya</a>
                </li>
            </ul>
        </div>
        <?php elseif ($total_data > 0): ?>
        <div class="mt-3 px-3 pb-3 text-muted small font-weight-bold">
            Menampilkan total <?php echo $total_data; ?> data
        </div>
        <?php endif; ?>
        
    </div>
</div>
<?php endif; ?>

<!-- Print Preview Modal -->
<div class="modal fade no-print" id="printPreviewModal" tabindex="-1" role="dialog" aria-labelledby="printPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printPreviewModalLabel"><i class="fas fa-file-invoice mr-2"></i> Print Preview Surat Pesanan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="background: #e5e7eb; padding: 2rem; max-height: 70vh; overflow-y: auto;">
                <div id="printPreviewContent" style="background: #fff; padding: 3rem; max-width: 21cm; margin: 0 auto; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); min-height: 29.7cm;">
                    <!-- Content will be injected here via JS -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary font-weight-bold" onclick="window.print();">
                    <i class="fas fa-print mr-2"></i> Lanjutkan Print
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#printPreviewModal').on('show.bs.modal', function () {
        var printSource = document.getElementById('print-layout-source');
        if (printSource) {
            var clone = printSource.cloneNode(true);
            clone.classList.remove('d-none', 'd-print-block');
            document.getElementById('printPreviewContent').innerHTML = '';
            document.getElementById('printPreviewContent').appendChild(clone);
        }
    });
});
</script>

<?php
require_once dirname(__FILE__) . '/../includes/footer.php';
?>
