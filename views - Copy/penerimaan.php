<?php
// views/penerimaan.php
// Goods Receipt Checklist module for Staff

if (!defined('FRONT_CONTROLLER')) {
    header("Location: /sp_umum/home.php?page=penerimaan" . ($_SERVER['QUERY_STRING'] !== '' ? '&' . $_SERVER['QUERY_STRING'] : ''));
    exit;
}

$page_title = 'Penerimaan Barang';
$active_menu = 'penerimaan';

require_once dirname(__FILE__) . '/../includes/auth.php';

$error = '';
$success = '';

// Handle Receipt Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_receipt_checklist'])) {
    sp_require_role('staff');
    
    $po_id = isset($_POST['po_id']) ? (int)$_POST['po_id'] : 0;
    $qtys = isset($_POST['qty_diterima']) ? $_POST['qty_diterima'] : array();
    $dates = isset($_POST['tgl_diterima']) ? $_POST['tgl_diterima'] : array();
    $notes = isset($_POST['keterangan']) ? $_POST['keterangan'] : array();
    
    $has_qty_to_save = false;
    foreach ($qtys as $item_id => $qty) {
        if ((int)$qty > 0) {
            $has_qty_to_save = true;
            break;
        }
    }
    
    $is_uploading = isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] === UPLOAD_ERR_OK;
    
    if ($has_qty_to_save && !$is_uploading) {
        $error = "Anda wajib mengunggah file lampiran bukti penerimaan barang (Surat Jalan/Faktur) saat mencatat barang masuk.";
    } else {
        $saved_count = 0;
    
    foreach ($qtys as $item_id => $qty) {
        $qty = (int)$qty;
        if ($qty > 0) {
            $item_id = (int)$item_id;
            $date = isset($dates[$item_id]) ? $dates[$item_id] : date('Y-m-d');
            $note = isset($notes[$item_id]) ? trim($notes[$item_id]) : '';
            
            if (db_save_goods_receipt($item_id, $date, $qty, $note, $_SESSION['user_id'])) {
                $saved_count++;
            }
        }
    }
    
    $lampiran_uploaded = false;
    if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] === UPLOAD_ERR_OK && $po_id > 0) {
        $upload_dir = 'uploads/penerimaan/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $tmp_name = $_FILES['lampiran']['tmp_name'];
        $original_name = basename($_FILES['lampiran']['name']);
        $clean_name = preg_replace("/[^a-zA-Z0-9.-]/", "_", $original_name);
        $file_name = time() . '_PO' . $po_id . '_' . $clean_name;
        $target_file = $upload_dir . $file_name;
        
        $ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        if (in_array($ext, $allowed)) {
            if (move_uploaded_file($tmp_name, $target_file)) {
                $escaped_target = db_escape($target_file);
                mysqli_query($GLOBALS['db_conn'], "UPDATE spu_h SET lampiran_penerimaan = '$escaped_target' WHERE id = $po_id");
                $lampiran_uploaded = true;
            } else {
                $error = "Gagal mengunggah lampiran.";
            }
        } else {
            $error = "Tipe file lampiran tidak diizinkan (Hanya PDF, JPG, PNG).";
        }
    }
    
        if ($saved_count > 0 && $lampiran_uploaded) {
            $success = "Berhasil mencatat $saved_count penerimaan barang dan lampiran diperbarui.";
        } elseif ($saved_count > 0) {
            $success = "Berhasil mencatat $saved_count penerimaan barang.";
        } elseif ($lampiran_uploaded) {
            $success = "Berhasil memperbarui lampiran surat pesanan.";
        } else {
            if (empty($error)) {
                $error = "Tidak ada barang baru yang dicentang / diinput dan tidak ada lampiran baru.";
            }
        }
    }
}

// Fetch all approved (ACC) Purchase Orders
$f_tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : '';
$f_tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : '';
$raw_approved_pos = db_get_purchase_orders('acc', '', $f_tgl_mulai, $f_tgl_selesai);
$filter_kelengkapan = isset($_GET['kelengkapan']) ? $_GET['kelengkapan'] : '';

$approved_pos = array();
foreach ($raw_approved_pos as $po) {
    $po_items = db_get_purchase_order_items($po['id']);
    $is_lengkap = true;
    if (count($po_items) === 0) {
        $is_lengkap = false;
    } else {
        foreach ($po_items as $item) {
            if ($item['status_terima'] !== 'lengkap') {
                $is_lengkap = false;
                break;
            }
        }
    }
    
    $po['is_lengkap'] = $is_lengkap;
    
    if ($filter_kelengkapan === 'lengkap' && !$is_lengkap) continue;
    if ($filter_kelengkapan === 'belum_lengkap' && $is_lengkap) continue;
    
    $approved_pos[] = $po;
}

// If a PO is selected, fetch its details
$selected_po = null;
$selected_po_items = array();
if (isset($_GET['po_id'])) {
    $po_id = (int)$_GET['po_id'];
    $temp_po = db_get_purchase_order_by_id($po_id);
    if ($temp_po && $temp_po['status'] === 'acc') {
        $selected_po = $temp_po;
        $selected_po_items = db_get_purchase_order_items($po_id);
    }
}

require_once dirname(__FILE__) . '/../includes/header.php';
?>

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

<div class="bp-hero no-print" style="padding:2.5rem 1.5rem; margin-bottom:1rem;">
    <div class="bp-hero-badge"><i class="fas fa-boxes"></i> Penerimaan Barang</div>
    <h4 class="bp-hero-title" style="font-size:1.2rem; margin-bottom:0;">Checklist Kedatangan Barang</h4>
</div>

<div class="row" style="flex: 1 1 auto; min-height: 0; margin-left:-0.25rem; margin-right:-0.25rem;">
    <!-- LEFT PANEL: Approved PO Selector -->
    <div class="col-md-3 mb-2 px-1">
        <div class="bp-panel bp-panel-violet" style="height: 100%; margin-bottom: 0; display: flex; flex-direction: column;">
            <div class="bp-panel-header" style="padding:0.4rem 0.8rem;">
                <div><i class="fas fa-check-double mr-2"></i> Pilih SP (Status ACC)</div>
            </div>
            
            <div style="padding: 0.5rem; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <form action="home.php" method="GET" class="m-0">
                    <input type="hidden" name="page" value="penerimaan">
                    <?php if (isset($_GET['po_id'])): ?>
                        <input type="hidden" name="po_id" value="<?php echo (int)$_GET['po_id']; ?>">
                    <?php endif; ?>
                    <div class="mb-1">
                        <select name="kelengkapan" class="form-control form-control-sm" style="font-size:0.8rem; height:auto; padding:0.2rem 0.4rem;">
                            <option value="">-- Semua Status --</option>
                            <option value="lengkap" <?php echo $filter_kelengkapan === 'lengkap' ? 'selected' : ''; ?>>Lengkap</option>
                            <option value="belum_lengkap" <?php echo $filter_kelengkapan === 'belum_lengkap' ? 'selected' : ''; ?>>Belum Lengkap</option>
                        </select>
                    </div>
                    <div class="row mx-0">
                        <div class="col-6 px-0 pr-1 mb-1">
                            <input type="date" name="tgl_mulai" class="form-control form-control-sm" style="font-size:0.75rem; height:auto; padding:0.2rem;" value="<?php echo htmlspecialchars($f_tgl_mulai); ?>" title="Tanggal Mulai">
                        </div>
                        <div class="col-6 px-0 pl-1 mb-1">
                            <input type="date" name="tgl_selesai" class="form-control form-control-sm" style="font-size:0.75rem; height:auto; padding:0.2rem;" value="<?php echo htmlspecialchars($f_tgl_selesai); ?>" title="Tanggal Selesai">
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-premium flex-grow-1 m-0" style="padding:0.2rem; font-size:0.75rem;">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="home.php?page=penerimaan" class="btn btn-sm btn-premium-secondary ml-1" style="padding:0.2rem 0.5rem; font-size:0.75rem;">
                            <i class="fas fa-sync"></i>
                        </a>
                    </div>
                </form>
            </div>
            
            <div class="bp-panel-body p-0" style="flex: 1 1 auto; overflow-y: auto;">
                <div class="list-group list-group-flush">
                    <?php if (empty($approved_pos)): ?>
                        <div class="text-center py-4 text-muted">
                            Tidak ada Surat Pesanan yang berstatus ACC saat ini.
                        </div>
                    <?php else: ?>
                        <?php foreach ($approved_pos as $po): 
                            $qs = $_GET;
                            $qs['po_id'] = $po['id'];
                            $link = 'home.php?' . http_build_query($qs);
                        ?>
                            <a href="<?php echo htmlspecialchars($link); ?>" class="list-group-item list-group-item-action p-2 <?php echo ($selected_po && $selected_po['id'] == $po['id']) ? 'active' : ''; ?>">
                                <div class="d-flex w-100 justify-content-between mb-0">
                                    <h6 class="mb-0 font-weight-bold <?php echo ($selected_po && $selected_po['id'] == $po['id']) ? 'text-white' : 'text-dark'; ?>" style="font-size:0.85rem;">
                                        <?php echo htmlspecialchars($po['no_pesanan']); ?>
                                    </h6>
                                    <small class="<?php echo ($selected_po && $selected_po['id'] == $po['id']) ? 'text-white-50' : 'text-muted'; ?>">
                                        <?php echo format_date($po['tgl_pesanan']); ?>
                                    </small>
                                </div>
                                <div class="d-flex w-100 justify-content-between align-items-center mt-1">
                                    <p class="mb-0 small <?php echo ($selected_po && $selected_po['id'] == $po['id']) ? 'text-white' : 'text-secondary'; ?>" style="font-size:0.7rem;">
                                        Vendor: <strong><?php echo htmlspecialchars($po['nama_vendor']); ?></strong>
                                    </p>
                                    <?php
                                    $acc_name_list = '-';
                                    if (in_array($po['status'], ['acc', 'selesai'])) {
                                        $acc_name_list = ($po['total_setelah_diskon'] < 5000000) ? 'Pembelian' : 'Direksi';
                                    }
                                    ?>
                                    <div class="text-right">
                                        <?php if ($po['is_lengkap']): ?>
                                            <span class="badge badge-success" style="font-size:0.6rem;"><i class="fas fa-check"></i> Lengkap</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning" style="font-size:0.6rem;"><i class="fas fa-clock"></i> Belum Lengkap</span>
                                        <?php endif; ?>
                                        <div class="<?php echo ($selected_po && $selected_po['id'] == $po['id']) ? 'text-white-50' : 'text-muted'; ?>" style="font-size:0.6rem; margin-top:2px;">
                                            ACC: <?php echo $acc_name_list; ?>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL: Checklist Form -->
    <div class="col-md-9 mb-2 px-1 d-flex flex-column" style="height: 100%;">
        <?php if ($selected_po): ?>
            <?php
            $total_items = count($selected_po_items);
            $complete_items = 0;
            foreach ($selected_po_items as $item) {
                if ($item['status_terima'] === 'lengkap') {
                    $complete_items++;
                }
            }
            $completion_percent = $total_items > 0 ? round(($complete_items / $total_items) * 100) : 0;
            ?>

            <div class="bp-panel bp-panel-amber mb-0" style="flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0;">
                <div class="bp-panel-header" style="padding:0.4rem 0.8rem; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <i class="fas fa-clipboard-check mr-2"></i> 
                        Checklist Penerimaan: <?php echo htmlspecialchars($selected_po['no_pesanan']); ?>
                    </div>
                    <span class="badge badge-success p-1" style="font-size: 0.65rem;">
                        <?php echo "$complete_items dari $total_items barang lengkap ($completion_percent%)"; ?>
                    </span>
                </div>
                
                <div class="bp-panel-body" style="padding:0.4rem; flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0;">
                    <?php
                    $acc_name = '-';
                    if (in_array($selected_po['status'], ['acc', 'selesai'])) {
                        $acc_name = ($selected_po['total_setelah_diskon'] < 5000000) ? 'Pembelian' : 'Direksi';
                    }
                    ?>
                    <div class="panel-info-highlight p-2 mb-2 flex-shrink-0" style="padding:0.4rem;">
                        <div class="row">
                            <div class="col-sm-2">
                                <span class="d-block text-muted small" style="font-size:0.65rem;">Vendor</span>
                                <strong class="text-dark" style="font-size:0.75rem;"><?php echo htmlspecialchars($selected_po['nama_vendor']); ?></strong>
                            </div>
                            <div class="col-sm-2">
                                <span class="d-block text-muted small" style="font-size:0.65rem;">Tanggal Pesan</span>
                                <strong style="font-size:0.75rem;"><?php echo format_date($selected_po['tgl_pesanan']); ?></strong>
                            </div>
                            <div class="col-sm-3">
                                <span class="d-block text-muted small" style="font-size:0.65rem;">Total Nilai</span>
                                <strong class="text-primary" style="font-size:0.75rem;"><?php echo format_rupiah($selected_po['total_setelah_diskon']); ?></strong>
                            </div>
                            <div class="col-sm-2">
                                <span class="d-block text-muted small" style="font-size:0.65rem;">Status SP</span>
                                <div style="font-size:0.75rem; line-height:1.2;">
                                    <?php echo get_status_badge($selected_po['status']); ?><br>
                                    <small class="text-success font-weight-bold">ACC: <?php echo $acc_name; ?></small>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <span class="d-block text-muted small" style="font-size:0.65rem;">Status Bayar</span>
                                <?php
                                $acc_bayar_name = '-';
                                $is_bayar_acc = false;
                                if (in_array($selected_po['status_bayar'], ['acc', 'lunas', 'parsial'])) {
                                    $acc_bayar_name = 'Direksi';
                                    $is_bayar_acc = true;
                                }
                                ?>
                                <div style="font-size:0.75rem; line-height:1.2;">
                                    <?php echo get_payment_badge($selected_po['status_bayar']); ?><br>
                                    <?php if ($is_bayar_acc): ?>
                                        <small class="text-success font-weight-bold">ACC: <?php echo $acc_bayar_name; ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">ACC: -</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Receipt Form -->
                     <form action="home.php?page=penerimaan&po_id=<?php echo $selected_po['id']; ?>" method="POST" enctype="multipart/form-data" style="flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0;" onsubmit="return validatePenerimaan();">
                        <input type="hidden" name="po_id" value="<?php echo $selected_po['id']; ?>">
                        <input type="hidden" name="save_receipt_checklist" value="1">
                        
                        <h6 class="font-weight-bold text-secondary mb-1 flex-shrink-0" style="font-size:0.75rem;"><i class="fas fa-boxes mr-1"></i> Rincian Status Penerimaan Barang</h6>
                        
                        <div class="table-responsive-sticky" style="flex: 1 1 auto; overflow-y: auto;">
                            <table class="table table-bordered bp-items-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th class="text-center" style="width: 5rem;">Pesan</th>
                                        <th class="text-center" style="width: 5rem;">Masuk</th>
                                        <th class="text-center" style="width: 6.5rem;">Status</th>
                                        <?php if ($_SESSION['user_role'] === 'staff'): ?>
                                            <th style="width: 6.5rem;">Qty Diterima</th>
                                            <th>Keterangan Penerimaan</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($selected_po_items as $item): ?>
                                        <?php 
                                        $remaining = $item['jumlah'] - $item['jumlah_diterima'];
                                        $is_complete = ($remaining <= 0);
                                        ?>
                                        <tr class="<?php echo $is_complete ? 'bg-light text-muted' : ''; ?>">
                                            <td class="font-weight-bold <?php echo $is_complete ? '' : 'text-dark'; ?>">
                                                <?php echo htmlspecialchars($item['nama_barang']); ?>
                                            </td>
                                            <td class="text-center"><?php echo $item['jumlah']; ?> unit</td>
                                            <td class="text-center font-weight-bold text-info"><?php echo $item['jumlah_diterima']; ?> unit</td>
                                            <td class="text-center"><?php echo get_receipt_badge($item['status_terima']); ?></td>
                                            
                                            <?php if ($_SESSION['user_role'] === 'staff'): ?>
                                                <td class="table-input-cell">
                                                    <?php if ($is_complete): ?>
                                                        <input type="text" class="form-control form-control-sm text-center bg-light border-0 text-success font-weight-bold px-1" style="font-size:0.7rem;" value="Selesai" disabled>
                                                    <?php else: ?>
                                                        <input type="number" 
                                                               name="qty_diterima[<?php echo $item['id']; ?>]" 
                                                               class="form-control form-control-sm text-center font-weight-bold text-dark border-primary px-1" 
                                                               style="font-size:0.8rem;"
                                                               min="0" 
                                                               max="<?php echo $remaining; ?>" 
                                                               placeholder="0">
                                                        <small class="d-block text-center text-muted mt-1" style="font-size:0.6rem;">Maks: <?php echo $remaining; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <td class="table-input-cell">
                                                    <?php if ($is_complete): ?>
                                                        <input type="text" class="form-control form-control-sm bg-light border-0 px-1" style="font-size:0.7rem;" value="Barang lengkap." disabled>
                                                    <?php else: ?>
                                                        <input type="text" 
                                                               name="keterangan[<?php echo $item['id']; ?>]" 
                                                               class="form-control form-control-sm px-1" 
                                                               style="font-size:0.75rem;"
                                                               placeholder="Catatan...">
                                                        
                                                        <input type="hidden" name="tgl_diterima[<?php echo $item['id']; ?>]" value="<?php echo date('Y-m-d'); ?>">
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($_SESSION['user_role'] === 'staff' && $completion_percent < 100): ?>
                            <div class="bp-action-bar flex-shrink-0 mt-2 d-flex justify-content-between align-items-center" style="padding:0.4rem 0.6rem;">
                                <div class="d-flex align-items-center">
                                    <label for="lampiran" class="mb-0 mr-2 font-weight-bold" style="font-size: 0.75rem;">Upload Lampiran:</label>
                                    <input type="file" name="lampiran" id="lampiran" class="form-control-file" style="font-size: 0.75rem; width: auto;" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                                <button type="submit" class="bp-btn-submit ml-auto font-weight-bold" style="padding:0.4rem 1rem; font-size:0.8rem;">
                                    <i class="fas fa-save mr-1"></i> Simpan Penerimaan
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($selected_po['lampiran_penerimaan'])): ?>
                            <div class="mt-2 text-right">
                                <a href="<?php echo htmlspecialchars($selected_po['lampiran_penerimaan']); ?>" target="_blank" class="btn btn-sm btn-info" style="font-size:0.75rem;">
                                    <i class="fas fa-paperclip"></i> Lihat Lampiran Saat Ini
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>

                    <!-- Receipt Log History -->
                    <div class="mt-2 flex-shrink-0">
                        <h6 class="font-weight-bold text-secondary mb-1" style="font-size:0.7rem;"><i class="fas fa-history mr-1"></i> Riwayat Logs Penerimaan Barang</h6>
                        
                        <?php
                        $gr_logs = array();
                        foreach ($selected_po_items as $item) {
                            if (!empty($item['receipts'])) {
                                foreach ($item['receipts'] as $rec) {
                                    $rec['barang_nama'] = $item['nama_barang'];
                                    $gr_logs[] = $rec;
                                }
                            }
                        }
                        
                        usort($gr_logs, function($a, $b) {
                            return strtotime($b['dibuat_pada']) - strtotime($a['dibuat_pada']);
                        });
                        ?>

                        <?php if (empty($gr_logs)): ?>
                            <p class="text-muted font-italic py-1 mb-0" style="font-size:0.65rem;">Belum ada catatan penerimaan barang yang disimpan.</p>
                        <?php else: ?>
                            <div class="table-responsive-sticky" style="max-height: 5rem; overflow-y: auto;">
                                <table class="table table-sm table-borderless table-striped font-size-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Waktu Terima</th>
                                            <th>Nama Barang</th>
                                            <th class="text-center">Jumlah Diterima</th>
                                            <th>Keterangan</th>
                                            <th>Checker</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($gr_logs as $log): ?>
                                            <?php
                                            $chk = db_get_user_by_id($log['dicek_oleh']);
                                            $chk_name = $chk ? $chk['nama'] : 'System';
                                            ?>
                                            <tr>
                                                <td class="py-1" style="font-size:0.65rem;"><?php echo date('d/m/Y H:i', strtotime($log['dibuat_pada'])); ?></td>
                                                <td class="font-weight-bold py-1" style="font-size:0.65rem;"><?php echo htmlspecialchars($log['barang_nama']); ?></td>
                                                <td class="text-center font-weight-bold text-info py-1" style="font-size:0.65rem;">+<?php echo $log['jumlah_diterima']; ?> unit</td>
                                                <td class="font-italic py-1" style="font-size:0.65rem;">"<?php echo htmlspecialchars($log['keterangan']); ?>"</td>
                                                <td class="py-1" style="font-size:0.65rem;"><?php echo htmlspecialchars($chk_name); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        <?php else: ?>
            <div class="bp-panel bp-panel-slate d-flex flex-column align-items-center justify-content-center text-center text-muted" style="height: 100%; margin-bottom:0;">
                <i class="fas fa-boxes fa-3x mb-2 text-black-50"></i>
                <h6 class="font-weight-bold">Pilih Surat Pesanan di panel kiri</h6>
                <p class="px-4" style="font-size:0.75rem;">Hanya Surat Pesanan yang telah disetujui (ACC) oleh Direktur yang dapat dimasukkan ke checklist penerimaan barang.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function validatePenerimaan() {
    let hasQty = false;
    $('input[name^="qty_diterima"]').each(function() {
        if (parseInt($(this).val()) > 0) hasQty = true;
    });
    
    if (hasQty) {
        let fileInput = document.getElementById('lampiran');
        if (fileInput && fileInput.files.length === 0) {
            alert("Anda wajib mengunggah file lampiran bukti penerimaan barang (Surat Jalan/Faktur) saat mencatat barang masuk.");
            return false;
        }
    }
    return true;
}
</script>

<?php
require_once dirname(__FILE__) . '/../includes/footer.php';
?>
