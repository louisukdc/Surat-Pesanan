<?php
// views/buat_pesanan.php
// Create Purchase Order form and processing

if (!defined('FRONT_CONTROLLER')) {
    header("Location: /sp_umum/home.php?page=buat_pesanan" . ($_SERVER['QUERY_STRING'] !== '' ? '&' . $_SERVER['QUERY_STRING'] : ''));
    exit;
}

$page_title = 'Buat Permintaan Pesanan (SP)';
$active_menu = 'buat_pesanan';

require_once dirname(__FILE__) . '/../includes/auth.php';
sp_require_role('staff');

$error = '';
$success = '';

function clean_rupiah($str) {
    if (!$str) return 0.0;
    $clean = preg_replace('/[^\d]/', '', $str);
    return (float)$clean;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents(dirname(__FILE__) . '/../debug_post.log', date('Y-m-d H:i:s') . " - POST Data:\n" . print_r($_POST, true) . "\n\n", FILE_APPEND);
    $no_pesanan    = isset($_POST['no_pesanan'])    ? trim($_POST['no_pesanan'])    : '';
    $tgl_pesanan   = isset($_POST['tgl_pesanan'])   ? trim($_POST['tgl_pesanan'])   : '';
    $nama_vendor   = isset($_POST['nama_vendor'])   ? trim($_POST['nama_vendor'])   : '';
    $action_status = isset($_POST['action_status']) ? $_POST['action_status']       : 'draft';

    $harga_vendor      = isset($_POST['harga_vendor'])   ? clean_rupiah($_POST['harga_vendor'])  : 0.0;
    $diskon_vendor_raw = isset($_POST['diskon_vendor'])  ? clean_rupiah($_POST['diskon_vendor']) : 0.0;
    $diskon_type       = isset($_POST['diskon_type'])    ? $_POST['diskon_type']                 : 'rp';

    $diskon_vendor = 0.0;
    if ($diskon_type === 'percent') {
        if ($diskon_vendor_raw > 100) $diskon_vendor_raw = 100;
        $diskon_vendor = $harga_vendor * ($diskon_vendor_raw / 100.0);
    } else {
        $diskon_vendor = min($diskon_vendor_raw, $harga_vendor);
    }
    $total_setelah_diskon = max(0.0, $harga_vendor - $diskon_vendor);

    // File upload logic
    $nama_lampiran_arr = array();
    if(isset($_POST['nama_lampiran_existing']) && trim($_POST['nama_lampiran_existing']) !== '') {
        $nama_lampiran_arr = array_map('trim', explode(',', $_POST['nama_lampiran_existing']));
    }
    
    if (isset($_FILES['lampiran_pdf'])) {
        $upload_dir = dirname(__FILE__) . '/../uploads/lampiran/';
        if (!is_dir($upload_dir)) {
            @mkdir($upload_dir, 0755, true);
        }
        $file_count = count($_FILES['lampiran_pdf']['name']);
        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['lampiran_pdf']['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['lampiran_pdf']['tmp_name'][$i];
                $name = basename($_FILES['lampiran_pdf']['name'][$i]);
                $safe_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $name);
                if (move_uploaded_file($tmp_name, $upload_dir . $safe_name)) {
                    $nama_lampiran_arr[] = $safe_name;
                }
            }
        }
    }
    $final_nama_lampiran = implode(',', $nama_lampiran_arr);

    $extra = array(
        'no_permintaan' => isset($_POST['no_permintaan']) ? trim($_POST['no_permintaan']) : '',
        'nama_lampiran' => $final_nama_lampiran,
        'no_tawar'      => isset($_POST['no_tawar'])      ? trim($_POST['no_tawar'])      : '',
        'tgl_tawar'     => isset($_POST['tgl_tawar'])     ? trim($_POST['tgl_tawar'])     : '',
        'pembayaran'    => isset($_POST['pembayaran'])    ? trim($_POST['pembayaran'])    : '',
        'pembayaran1'   => isset($_POST['pembayaran1'])   ? trim($_POST['pembayaran1'])   : '',
        'notein'        => isset($_POST['notein'])        ? trim($_POST['notein'])        : '',
        'noteout'       => isset($_POST['noteout'])       ? trim($_POST['noteout'])       : '',
        'unit'          => isset($_POST['unit'])          ? trim($_POST['unit'])          : '',
        'tglkirim'      => isset($_POST['tglkirim'])      ? trim($_POST['tglkirim'])      : '',
        'ppn'           => isset($_POST['ppn_nilai'])     ? (float)$_POST['ppn_nilai']   : 0.0,
    );

    $item_names  = isset($_POST['nama_barang'])  ? $_POST['nama_barang']  : array();
    $item_qtys   = isset($_POST['jumlah'])        ? $_POST['jumlah']        : array();
    $item_prices = isset($_POST['harga_satuan']) ? $_POST['harga_satuan'] : array();
    $item_merks  = isset($_POST['merk'])         ? $_POST['merk']         : array();
    $item_Tipes = isset($_POST['Tipe'])        ? $_POST['Tipe']        : array();
    $item_specs  = isset($_POST['spec'])         ? $_POST['spec']         : array();
    $item_satuans= isset($_POST['satuan'])       ? $_POST['satuan']       : array();
    $item_discs  = isset($_POST['disc_item'])    ? $_POST['disc_item']    : array();

    if ($no_pesanan === '' || $tgl_pesanan === '' || $nama_vendor === '') {
        $error = 'Nomor pesanan, tanggal, dan nama vendor wajib diisi.';
    } elseif ($harga_vendor <= 0) {
        $error = 'Harga dari vendor harus lebih besar dari 0.';
    } elseif (empty($item_names)) {
        $error = 'Minimal harus memasukkan 1 barang pesanan.';
    } elseif ($action_status === 'diajukan' && empty($nama_lampiran_arr)) {
        $error = 'Lampiran Surat Pesanan wajib diunggah sebelum dapat diajukan.';
    } else {
        $items = array();
        for ($i = 0; $i < count($item_names); $i++) {
            $name   = trim($item_names[$i]);
            $qty    = (float)(isset($item_qtys[$i])   ? $item_qtys[$i]   : 0);
            $price  = isset($item_prices[$i]) ? clean_rupiah($item_prices[$i]) : 0;
            $disc_i = isset($item_discs[$i])  ? clean_rupiah($item_discs[$i])  : 0;
            $subtotal = max(0, ($qty * $price) - $disc_i);
            if ($name !== '' && $qty > 0) {
                $items[] = array(
                    'nama_barang'  => $name,
                    'merk'         => isset($item_merks[$i])  ? trim($item_merks[$i])  : '',
                    'Tipe'        => isset($item_Tipes[$i]) ? trim($item_Tipes[$i]) : '',
                    'spec'         => isset($item_specs[$i])  ? trim($item_specs[$i])  : '',
                    'satuan'       => isset($item_satuans[$i])? trim($item_satuans[$i]): 'pcs',
                    'jumlah'       => $qty,
                    'harga_satuan' => $price,
                    'disc'         => $disc_i,
                    'subtotal'     => $subtotal,
                );
            }
        }

        if (empty($items)) {
            $error = 'Semua baris barang kosong atau jumlah tidak valid.';
        } else {
            $is_edit = isset($_POST['edit_id']) && (int)$_POST['edit_id'] > 0;
            
            $is_auto_acc = false;
            if ($action_status === 'diajukan' && $total_setelah_diskon < 5000000) {
                $action_status = 'acc';
                $is_auto_acc = true;
            }

            if ($is_edit) {
                $po_id = db_update_purchase_order(
                    (int)$_POST['edit_id'], $tgl_pesanan, $nama_vendor,
                    $harga_vendor, $diskon_vendor, $total_setelah_diskon,
                    $action_status, $_SESSION['user_id'], $items, $extra
                );
                $success_msg = 'Surat Pesanan ' . htmlspecialchars($no_pesanan) . ' berhasil diedit dan disimpan sebagai ' . ($action_status === 'acc' ? ($is_auto_acc ? 'Disetujui Otomatis (Oleh Pembelian)' : 'Diajukan ke Direktur') : 'Draft') . '.';
            } else {
                $po_id = db_create_purchase_order(
                    $no_pesanan, $tgl_pesanan, $nama_vendor,
                    $harga_vendor, $diskon_vendor, $total_setelah_diskon,
                    $action_status, $_SESSION['user_id'], $items, $extra
                );
                $success_msg = 'Surat Pesanan ' . htmlspecialchars($no_pesanan) . ' berhasil dibuat dan disimpan sebagai ' . ($action_status === 'acc' ? ($is_auto_acc ? 'Disetujui Otomatis (Oleh Pembelian)' : 'Diajukan ke Direktur') : 'Draft') . '.';
            }

            if ($po_id !== false) {
                if ($is_auto_acc) {
                    $u_name = 'Pembelian';
                    if (function_exists('db_get_user_by_id')) {
                        $usr = db_get_user_by_id($_SESSION['user_id']);
                        if ($usr) $u_name = $usr['nama'];
                    } else {
                        $u_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Pembelian';
                    }
                    $catatan = db_escape("Telah disetujui otomatis oleh " . $u_name . " (Pembelian) karena nominal pesanan di bawah 5 Juta.");
                    mysqli_query($GLOBALS['db_conn'], "INSERT INTO sp_log_persetujuan (surat_pesanan_id, jenis, status, catatan, oleh, tanggal) VALUES ($po_id, 'permintaan', 'acc', '$catatan', {$_SESSION['user_id']}, NOW())");
                }
                
                $success = $success_msg;
                $_POST = array(); // Clear post data after success
                if ($is_edit) {
                    // Redirect to monitoring after successful edit
                    header("Location: home.php?page=monitoring");
                    exit;
                }
            } else {
                global $last_db_error;
                $error = 'Gagal menyimpan Surat Pesanan. Error DB: ' . (isset($last_db_error) ? htmlspecialchars($last_db_error) : 'Unknown error');
            }
        }
    }
}

// Logic to load existing PO for editing
$is_editing = false;
$edit_po = null;
$edit_items = array();

if (isset($_GET['edit_id']) && (int)$_GET['edit_id'] > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $edit_id = (int)$_GET['edit_id'];
    // Ambil data PO
    $res = mysqli_query($GLOBALS['db_conn'], "SELECT * FROM spu_h WHERE id = $edit_id LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $edit_po = mysqli_fetch_assoc($res);
        if (in_array($edit_po['status'], array('draft', 'ditolak', 'diajukan'))) {
            $is_editing = true;
            // Ambil items
            $res_items = mysqli_query($GLOBALS['db_conn'], "SELECT * FROM spu_d WHERE id_header = $edit_id ORDER BY id ASC");
            if ($res_items) {
                while($ritem = mysqli_fetch_assoc($res_items)) {
                    $edit_items[] = $ritem;
                }
            }
            
            // Populate $_POST so the form fields show these values
            $_POST['edit_id'] = $edit_id;
            $_POST['no_pesanan'] = $edit_po['no_sp'];
            $_POST['tgl_pesanan'] = $edit_po['tgl_sp'];
            $_POST['nama_vendor'] = $edit_po['namasup'];
            $_POST['no_permintaan'] = $edit_po['no_permintaan'];
            $_POST['nama_lampiran_existing'] = $edit_po['nama_lampiran'];
            $_POST['no_tawar'] = $edit_po['no_tawar'];
            $_POST['tgl_tawar'] = ($edit_po['tgl_tawar'] === '1900-01-01' || $edit_po['tgl_tawar'] === '0000-00-00') ? '' : $edit_po['tgl_tawar'];
            $_POST['pembayaran'] = $edit_po['pembayaran'];
            $_POST['pembayaran1'] = $edit_po['pembayaran1'];
            $_POST['notein'] = $edit_po['notein'];
            $_POST['noteout'] = $edit_po['noteout'];
            $_POST['unit'] = $edit_po['unit'];
            $_POST['tglkirim'] = ($edit_po['tglkirim'] === '1900-01-01' || $edit_po['tglkirim'] === '0000-00-00') ? '' : $edit_po['tglkirim'];
            $_POST['ppn_nilai'] = $edit_po['ppn'];
            
            // Calculate base harga vendor and diskon
            $flag_total = (float)$edit_po['flag']; // Harga setelah diskon
            $potongan = (float)$edit_po['potongan'];
            $_POST['harga_vendor'] = $flag_total + $potongan;
            $_POST['diskon_vendor'] = $potongan;
            $_POST['diskon_type'] = 'rp';
            
            // Populate items array
            $_POST['nama_barang'] = array_column($edit_items, 'barang');
            $_POST['merk'] = array_column($edit_items, 'merk');
            $_POST['Tipe'] = array_column($edit_items, 'model');
            $_POST['spec'] = array_column($edit_items, 'spec');
            $_POST['satuan'] = array_column($edit_items, 'satuan');
            $_POST['jumlah'] = array_column($edit_items, 'qty');
            $_POST['harga_satuan'] = array_column($edit_items, 'harga');
            $_POST['disc_item'] = array_column($edit_items, 'disc');
        } else {
            $error = 'Pesanan tidak bisa diedit karena sudah berstatus: ' . $edit_po['status'];
        }
    } else {
        $error = 'Surat pesanan tidak ditemukan.';
    }
}

$next_po_num = db_generate_po_number();
$today = date('Y-m-d');
$suppliers = db_get_suppliers();
$suppliers_json = json_encode($suppliers);

$gudang_list = db_get_gudang();
$gudang_json = json_encode($gudang_list);

$unit = db_get_units();
$master_pengadaans = db_get_pengadaan();

require_once dirname(__FILE__) . '/../includes/header.php';

$satuans = array('pcs','unit','lusin','kodi','rim','roll','box','set','kg','ltr','m','cm');
$satuans_json = json_encode($satuans);
$opts_bayar = array('Tunai / Cash','Transfer Bank','Kredit 30 Hari','Kredit 60 Hari','Kredit 90 Hari','Giro');
?>

<?php if ($error !== ''): ?>
    <div class="bp-alert-error">
        <i class="fas fa-exclamation-circle fa-lg"></i>
        <span><?php echo htmlspecialchars($error); ?></span>
    </div>
<?php endif; ?>

<?php if ($success !== ''): ?>
    <div class="bp-alert-success">
        <i class="fas fa-check-circle fa-lg"></i>
        <span><?php echo htmlspecialchars($success); ?></span>
    </div>
<?php endif; ?>

<style>
/* Additional minimal styling for autocomplete & modal integrated into old layout */
.suggestions-box {
    position: absolute;
    width: 100%;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0 0 6px 6px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    z-index: 9999;
    max-height: 240px;
    overflow-y: auto;
    display: none;
}
.suggestion-item {
    padding: 6px 12px;
    font-size: 11px;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
}
.suggestion-item:hover { background-color: #f0fdfa; }

.excel-input {
    width: 100%;
    border: none;
    background: transparent;
    padding: 6px;
    font-size: 0.85rem;
    outline: none;
    box-shadow: none;
}
.excel-input:focus {
    background-color: #f0fdfa;
    border: 1px solid #14b8a6;
    border-radius: 4px;
}
.excel-select {
    padding: 4px;
    border: none;
    background: transparent;
    font-size: 0.85rem;
    outline: none;
}
.excel-select:focus {
    background-color: #f0fdfa;
    border: 1px solid #14b8a6;
    border-radius: 4px;
}
.excel-cell {
    padding: 0 !important;
    vertical-align: middle !important;
}

.sp-modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.sp-modal-content {
    background-color: #ffffff;
    border-radius: 8px;
    width: 100%;
    max-width: 98vw;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.sp-modal-header { padding: 16px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
.sp-modal-body { padding: 16px; flex: 1 1 auto; overflow-y: auto; }
.sp-modal-footer { padding: 16px; border-top: 1px solid #e5e7eb; text-align: right; }
.btn-close-modal { background: none; border: none; font-size: 1.2rem; cursor: pointer; }
</style>

<!-- STYLED BOX WRAPPER -->
<div style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 20px; margin: 20px; display: flex; flex-direction: column; flex-grow: 1; overflow: visible; border: 1px solid #e5e7eb;">

<!-- HERO BANNER -->
<div class="bp-hero">
    <div class="bp-hero-badge"><i class="fas fa-star"></i> <?php echo $is_editing ? 'Edit Surat Pesanan' : 'Form Buat Surat Pesanan'; ?></div>
    <h4 class="bp-hero-title"><?php echo $is_editing ? 'Edit SP: ' . htmlspecialchars($edit_po['no_sp']) : 'Surat Pesanan Baru'; ?></h4>
    <p class="bp-hero-sub">Lengkapi seluruh informasi pesanan, rincian barang, dan metode pembayaran sebelum diajukan.</p>
</div>

<form action="home.php?page=buat_pesanan" method="POST" id="form-po" style="flex-grow: 1; display: flex; flex-direction: column; min-height: 0;" enctype="multipart/form-data">
    <?php if (isset($_POST['edit_id'])): ?>
        <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($_POST['edit_id']); ?>">
    <?php endif; ?>
    <input type="hidden" name="action_status" id="action_status" value="draft">
    <input type="hidden" name="ppn_nilai" id="ppn_nilai" value="<?php echo isset($_POST['ppn_nilai']) ? htmlspecialchars($_POST['ppn_nilai']) : 0; ?>">

    <div class="row flex-shrink-0">

        <!-- ===== PANEL KIRI: IDENTITAS SP ===== -->
        <div class="col-lg-4 mb-2">
            <div class="bp-panel bp-panel-blue" style="height: 100%; margin-bottom: 0;">
                <div class="bp-panel-header">
                    <div class="bp-panel-icon"><i class="fas fa-id-card"></i></div>
                    Identitas Surat Pesanan
                </div>
                <div class="bp-panel-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label class="bp-field-label">No SP <span class="req">*</span></label>
                                <input type="text" name="no_pesanan" id="no_pesanan"
                                    class="form-control form-control-sm bp-input font-weight-bold"
                                    placeholder="Contoh: PO/xxxxx/mm/yy"
                                    maxlength="25"
                                    value="<?php echo isset($_POST['no_pesanan']) ? htmlspecialchars($_POST['no_pesanan']) : $next_po_num; ?>" required readonly style="background-color: #f8f9fa;">
                                <small class="text-muted" style="font-size:0.7rem;">Otomatis oleh sistem</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label class="bp-field-label">No Permintaan</label>
                                <select name="no_permintaan" id="no_permintaan" class="form-control form-control-sm bp-input select2-field">
                                    <option value="">-- Pilih No Surat --</option>
                                    <?php foreach ($master_pengadaans as $p): ?>
                                        <option value="<?php echo htmlspecialchars($p['notiket']); ?>" <?php echo (isset($_POST['no_permintaan']) && $_POST['no_permintaan'] == $p['notiket']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($p['notiket'] . ' - ' . $p['bagian'] . ' (' . $p['diminta'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label class="bp-field-label">Tanggal SP <span class="req">*</span></label>
                                <input type="date" name="tgl_pesanan" id="tgl_pesanan"
                                    class="form-control form-control-sm bp-input"
                                    value="<?php echo isset($_POST['tgl_pesanan']) ? htmlspecialchars($_POST['tgl_pesanan']) : $today; ?>" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label class="bp-field-label">Tgl Kirim Diharapkan</label>
                                <input type="date" name="tglkirim" id="tglkirim"
                                    class="form-control form-control-sm bp-input"
                                    value="<?php echo isset($_POST['tglkirim']) ? htmlspecialchars($_POST['tglkirim']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-3" style="position:relative;">
                                <label class="bp-field-label">Unit/Bagian <span class="req">*</span></label>
                                <select name="unit" id="unit" class="form-control form-control-sm bp-input select2-field" required>
                                    <option value="">-- Pilih Unit/Bagian --</option>
                                    <?php foreach ($gudang_list as $gudang): ?>
                                        <option value="<?php echo htmlspecialchars($gudang['NamaGudang']); ?>" <?php echo (isset($_POST['unit']) && $_POST['unit'] == $gudang['NamaGudang']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($gudang['NamaGudang']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label class="bp-field-label">Lampiran (PDF)</label>
                                <input type="file" name="lampiran_pdf[]" id="lampiran_pdf"
                                    class="form-control form-control-sm bp-input" accept="application/pdf" multiple style="padding-bottom:28px;">
                                <input type="hidden" name="nama_lampiran_existing" value="<?php echo isset($_POST['nama_lampiran_existing']) ? htmlspecialchars($_POST['nama_lampiran_existing']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== PANEL TENGAH: VENDOR & PENAWARAN ===== -->
        <div class="col-lg-4 mb-2">
            <div class="bp-panel bp-panel-violet" style="height: 100%; margin-bottom: 0;">
                <div class="bp-panel-header">
                    <div class="bp-panel-icon"><i class="fas fa-store"></i></div>
                    Vendor &amp; Penawaran
                </div>
                <div class="bp-panel-body">
                    <div class="form-group mb-3" style="position:relative;">
                        <label class="bp-field-label">Nama Vendor <span class="req">*</span></label>
                                                <select name="nama_vendor" id="nama_vendor" class="form-control form-control-sm bp-input select2-field" required>
                            <option value="">-- Pilih Vendor --</option>
                            <?php foreach ($suppliers as $sup): ?>
                                <option value="<?php echo htmlspecialchars($sup['NamaSupplier']); ?>" <?php echo (isset($_POST['nama_vendor']) && $_POST['nama_vendor'] == $sup['NamaSupplier']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sup['NamaSupplier']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label class="bp-field-label">No Penawaran</label>
                                <input type="text" name="no_tawar" id="no_tawar"
                                    class="form-control form-control-sm bp-input"
                                    placeholder="No surat penawaran..."
                                    maxlength="25"
                                    value="<?php echo isset($_POST['no_tawar']) ? htmlspecialchars($_POST['no_tawar']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label class="bp-field-label">Tgl Penawaran</label>
                                <input type="date" name="tgl_tawar" id="tgl_tawar"
                                    class="form-control form-control-sm bp-input"
                                    value="<?php echo isset($_POST['tgl_tawar']) ? htmlspecialchars($_POST['tgl_tawar']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label class="bp-field-label">Cara Bayar 1</label>
                                <select name="pembayaran" id="pembayaran"
                                    class="form-control form-control-sm bp-input" style="height:auto; padding:0.35rem 0.6rem;">
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($opts_bayar as $ob): ?>
                                        <option value="<?php echo $ob; ?>" <?php echo (isset($_POST['pembayaran']) && $_POST['pembayaran']===$ob) ? 'selected' : ''; ?>><?php echo $ob; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label class="bp-field-label">Cara Bayar 2</label>
                                <input type="text" name="pembayaran1" id="pembayaran1"
                                    class="form-control form-control-sm bp-input"
                                    placeholder="Keterangan tambahan..."
                                    value="<?php echo isset($_POST['pembayaran1']) ? htmlspecialchars($_POST['pembayaran1']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group mb-0">
                                <label class="bp-field-label">Catatan Internal (Note In)</label>
                                <textarea name="notein" id="notein"
                                    class="form-control form-control-sm bp-input" rows="4"
                                    placeholder="Catatan internal..."><?php echo isset($_POST['notein']) ? htmlspecialchars($_POST['notein']) : ''; ?></textarea>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-0">
                                <label class="bp-field-label">Catatan Eksternal (Note Out)</label>
                                <textarea name="noteout" id="noteout"
                                    class="form-control form-control-sm bp-input" rows="4"
                                    placeholder="Catatan untuk vendor/eksternal..."><?php echo isset($_POST['noteout']) ? htmlspecialchars($_POST['noteout']) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== PANEL KANAN: BIAYA ===== -->
        <div class="col-lg-4 mb-2">
            <div class="bp-panel bp-panel-teal" style="height: 100%; margin-bottom: 0;">
                <div class="bp-panel-header">
                    <div class="bp-panel-icon"><i class="fas fa-calculator"></i></div>
                    Perhitungan Biaya
                </div>
                <div class="bp-panel-body">
                    <!-- Total Harga -->
                    <div class="mb-2">
                        <label class="bp-field-label">Total Harga Vendor <span class="req">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text font-weight-bold bg-light">Rp</span></div>
                            <input type="text" name="harga_vendor" id="harga_vendor"
                                class="form-control form-control-sm bp-input input-rupiah font-weight-bold text-right"
                                placeholder="0" value="<?php echo isset($_POST['harga_vendor']) ? htmlspecialchars($_POST['harga_vendor']) : '0'; ?>" readonly required>
                        </div>
                        <small class="text-muted" style="font-size:0.7rem;">Dihitung otomatis</small>
                    </div>

                    <!-- PPN -->
                    <div class="mb-2">
                        <label class="bp-field-label">PPN 11%</label>
                        <div class="d-flex align-items-center mb-1">
                            <label class="ppn-switch mb-0 mr-2" for="ppn_toggle">
                                <input type="checkbox" id="ppn_toggle" onchange="calculateGlobal()">
                                <span class="ppn-slider"></span>
                            </label>
                            <span id="ppn_label" class="text-muted" style="font-size:0.7rem;">Tanpa PPN</span>
                        </div>
                        <div id="row_ppn_nominal" style="display:none;">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text font-weight-bold" style="background:#059669;color:#fff;border-color:#059669;padding:0.2rem 0.5rem;font-size:0.75rem;">Rp</span></div>
                                <input type="text" id="ppn_nominal" class="form-control form-control-sm text-right font-weight-bold"
                                    style="color:#059669;background:#ecfdf5;border-color:#a7f3d0;" value="0" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Diskon -->
                    <div class="mb-2">
                        <label class="bp-field-label">Diskon Global</label>
                        <div class="input-group input-group-sm">
                            <select name="diskon_type" id="diskon_type" class="form-control form-control-sm bp-input" style="max-width:4rem; height:auto; padding:0.2rem 0.3rem; border-right:0; border-radius:0.35rem 0 0 0.35rem; font-size:0.7rem;" onchange="calculateGlobal()">
                                <option value="rp" <?php echo (isset($_POST['diskon_type']) && $_POST['diskon_type']==='rp') ? 'selected' : ''; ?>>Rp</option>
                                <option value="percent" <?php echo (!isset($_POST['diskon_type']) || $_POST['diskon_type']==='percent') ? 'selected' : 'selected'; ?>>%</option>
                            </select>
                            <input type="number" name="diskon_vendor" id="diskon_vendor"
                                class="form-control form-control-sm bp-input text-right"
                                placeholder="0"
                                value="<?php echo isset($_POST['diskon_vendor']) ? htmlspecialchars($_POST['diskon_vendor']) : '0'; ?>" onkeyup="calculateGlobal()" onchange="calculateGlobal()">
                        </div>
                    </div>

                    <!-- Total Net -->
                    <div class="mb-2">
                        <label class="bp-field-label">Total Harga Net</label>
                        <div class="bp-biaya-total">
                            <span id="total_setelah_diskon">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.row -->

    <!-- ===== PANEL RINCIAN BARANG ===== -->
    <div class="bp-panel bp-panel-amber" style="flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0; margin-bottom: 0;">
        <div class="bp-panel-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:0.6rem;">
                <div class="bp-panel-icon"><i class="fas fa-boxes"></i></div>
                Rincian Barang Pesanan
            </div>
            <button type="button" class="btn btn-sm btn-light font-weight-bold" style="border:1px solid #fbbf24; color:#d97706;" onclick="openGridModal()">
                <i class="fas fa-table mr-1"></i> Tambah Rincian Barang (Data Grid)
            </button>
        </div>
        <div class="bp-panel-body" style="padding:0.4rem; flex: 1 1 auto; overflow-y: auto;">
            <div class="table-responsive-sticky" style="height: 100%;">
                <table class="table table-bordered mb-0 bp-items-table" id="po-items-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:2.8rem;">#</th>
                            <th style="min-width:9rem;">Nama Barang <span style="color:#fbbf24;">*</span></th>
                            <th style="min-width:6rem;">Merk/Tipe</th>
                            <th class="text-center" style="width:5rem;">Qty</th>
                            <th class="text-right" style="width:9rem;">Harga Satuan</th>
                            <th class="text-right" style="width:7.5rem;">Diskon Item</th>
                            <th class="text-right" style="width:8.5rem;">Subtotal</th>
                            <th class="text-center" style="width:4.5rem;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="po-items-body">
                        <!-- Filled by Javascript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===== SIGNATURE PREVIEW ===== -->
    <div class="bp-panel mt-3 mb-2" style="background-color: #f8f9fa;">
        <div class="bp-panel-body" style="padding: 15px;">
            <div class="row text-center" style="font-size: 0.9rem;">
                <div class="col-4">
                    <p class="mb-4 text-muted">Dibuat Oleh,</p>
                    <p class="font-weight-bold mb-0"><u>( <?php echo htmlspecialchars($_SESSION['NamaUser'] ?? 'Staff Pembelian'); ?> )</u></p>
                    <p class="small text-muted">Pembelian</p>
                </div>
                <div class="col-4">
                    <p class="mb-4 text-muted">Disetujui Oleh,</p>
                    <p class="font-weight-bold mb-0" id="preview_acc_sp"><u>( Direktur )</u></p>
                    <p class="small text-muted" id="preview_acc_jabatan">Direktur Utama</p>
                </div>
                <div class="col-4">
                    <p class="mb-4 text-muted">Mengetahui (Bayar),</p>
                    <p class="font-weight-bold mb-0"><u>( Direktur )</u></p>
                    <p class="small text-muted">Direktur Utama</p>
                </div>
            </div>
            <div class="text-center mt-2">
                <small class="text-info"><i class="fas fa-info-circle"></i> <i>Penyetuju SP otomatis menyesuaikan total nilai pesanan ( > 5 Juta oleh Direktur, < 5 Juta oleh Pembelian )</i></small>
            </div>
        </div>
    </div>

    <!-- ===== ACTION BAR ===== -->
    <div class="bp-action-bar">
        <div class="bp-action-hint">
            <strong><i class="fas fa-info-circle mr-1"></i> Draft</strong> — simpan tanpa mengajukan.<br>
            <strong><i class="fas fa-paper-plane mr-1"></i> Ajukan</strong> — langsung dikirim ke Direktur untuk persetujuan.
        </div>
        <div>
            <button type="button" class="bp-btn-draft" onclick="submitAs('draft')">
                <i class="fas fa-save mr-1"></i> Simpan Draft
            </button>
            <button type="button" class="bp-btn-submit" onclick="submitAs('diajukan')">
                <i class="fas fa-paper-plane mr-1"></i> Ajukan ke Direktur
            </button>
        </div>
    </div>

</form>

</div> <!-- /STYLED BOX WRAPPER -->

<!-- Modal Data Grid -->
<div id="gridModal" class="sp-modal-overlay">
    <div class="sp-modal-content">
        <div class="sp-modal-header">
            <h5 class="mb-0 font-weight-bold" style="color:#1f2937;"><i class="fas fa-table text-primary"></i> Data Grid Rincian Barang</h5>
            <button type="button" class="btn-close-modal" onclick="closeGridModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="sp-modal-body" style="background-color: #f9fafb;">
            <div class="mb-2">
                <button type="button" class="btn btn-sm btn-success font-weight-bold" onclick="addEmptyGridRow()">
                    <i class="fas fa-plus mr-1"></i> Tambah Baris Baru
                </button>
            </div>
            <div class="table-responsive" style="background: white; border: 1px solid #e5e7eb; border-radius: 4px;">
                <table class="table table-bordered mb-0 bp-items-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:2.8rem;">#</th>
                            <th style="min-width:12rem;">Nama Barang <span style="color:#fbbf24;">*</span></th>
                            <th style="min-width:10rem;">Spesifikasi</th>
                            <th style="min-width:8rem;">Merk</th>
                            <th style="min-width:8rem;">Tipe</th>
                            <th class="text-center" style="width:6rem;">Qty</th>
                            <th class="text-center" style="width:7rem;">Satuan</th>
                            <th class="text-right" style="width:9rem;">Harga Satuan</th>
                            <th class="text-right" style="width:8rem;">Diskon</th>
                            <th class="text-right" style="width:9rem;">Subtotal</th>
                            <th class="text-center" style="width:4rem;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="grid-items-body">
                        <!-- Filled by Javascript Grid -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="sp-modal-footer">
            <button type="button" class="btn btn-sm btn-primary" onclick="closeGridModal()"><i class="fas fa-check"></i> Selesai & Terapkan</button>
        </div>
    </div>
</div>

<script>
window.onerror = function(msg, url, line, col, error) {
    alert("GLOBAL JS ERROR:\n" + msg + "\nLine: " + line);
};
</script>

<script>
// ============================================
// LOCAL SEARCH LOGIC (NO EXTERNAL API NEEDED)
// ============================================

const masterSuppliers = <?php echo $suppliers_json; ?>;
const masterGudang = <?php echo $gudang_json; ?>;

let orderItems = [];

function formatCurrency(num) {
    return parseFloat(num).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
}

// Vendor Autocomplete via Local Array
let supplierTimeout;
function searchSupplier(query) {
    clearTimeout(supplierTimeout);
    let $suggestions = $('#supplier-suggestions');
    if(query.length < 1) {
        $suggestions.hide();
        if(query.trim() === '') $('#nama_vendor').val('');
        return;
    }
    
    supplierTimeout = setTimeout(() => {
        let q = query.toLowerCase();
        let matches = masterSuppliers.filter(s => 
            s.NamaSupplier.toLowerCase().includes(q) || 
            s.KodeSupplier.toLowerCase().includes(q)
        ).slice(0, 10);
        
        if(matches.length > 0) {
            let html = '';
            matches.forEach(s => {
                html += `<div class="suggestion-item" onclick="selectSupplier('${s.NamaSupplier}', '${s.KodeSupplier}')">
                            <strong>${s.KodeSupplier}</strong> - ${s.NamaSupplier}
                         </div>`;
            });
            $suggestions.html(html).show();
        } else {
            $suggestions.hide();
            $('#nama_vendor').val(query);
        }
    }, 200);
}

function selectSupplier(nama, kode) {
    $('#namasup_input').val(nama);
    $('#nama_vendor').val(nama); // This goes to the hidden or final input
    $('#supplier-suggestions').hide();
}

// Unit / Gudang Autocomplete via Local Array
let gudangTimeout;
function searchGudang(query) {
    clearTimeout(gudangTimeout);
    let $suggestions = $('#gudang-suggestions');
    if(query.length < 1) {
        $suggestions.hide();
        return;
    }
    
    gudangTimeout = setTimeout(() => {
        let q = query.toLowerCase();
        let matches = masterGudang.filter(g => 
            (g.NamaGudang && g.NamaGudang.toLowerCase().includes(q)) || 
            (g.KodeGudang && g.KodeGudang.toLowerCase().includes(q)) ||
            (g.FNAMA && g.FNAMA.toLowerCase().includes(q))
        ).slice(0, 10);
        
        if(matches.length > 0) {
            let html = '';
            matches.forEach(g => {
                let kode = g.KodeGudang || g.FGUDANG;
                let nama = g.NamaGudang || g.FNAMA;
                html += `<div class="suggestion-item" onclick="selectGudang('${kode}', '${nama}')">
                            <strong>${kode}</strong> - ${nama}
                         </div>`;
            });
            $suggestions.html(html).show();
        } else {
            $suggestions.hide();
        }
    }, 200);
}

function selectGudang(kode, nama) {
    $('#unit').val(`${kode} - ${nama}`);
    $('#gudang-suggestions').hide();
}

$(document).click(function(e) {
    if (!$(e.target).closest('#namasup_input').length && !$(e.target).closest('#supplier-suggestions').length) {
        $('#supplier-suggestions').hide();
    }
    if (!$(e.target).closest('#unit').length && !$(e.target).closest('#gudang-suggestions').length) {
        $('#gudang-suggestions').hide();
    }
});

// Modal Logic
function openItemModal(index = -1) {
    if(index >= 0) {
        let itm = orderItems[index];
        $('#item_barang').val(itm.nama_barang);
        $('#item_spec').val(itm.spec);
        $('#item_merk').val(itm.merk);
        $('#item_Tipe').val(itm.Tipe);
        $('#item_qty').val(itm.jumlah);
        $('#item_satuan').val(itm.satuan);
        $('#item_harga').val(itm.harga_satuan);
        $('#item_disc').val(itm.disc);
        $('#edit_index').val(index);
        calculateItemTotal();
    } else {
        clearItemForm();
        $('#edit_index').val(-1);
    }
    $('#itemModal').css('display', 'flex');
    setTimeout(() => $('#item_barang').focus(), 100);
}

function closeItemModal() {
    $('#itemModal').css('display', 'none');
}

function calculateItemTotal() {
    let qty = parseFloat($('#item_qty').val()) || 0;
    let harga = parseFloat($('#item_harga').val()) || 0;
    let disc = parseFloat($('#item_disc').val()) || 0;
    let total = Math.max(0, (qty * harga) - disc);
    $('#item_jumlah').text(formatCurrency(total));
}

function clearItemForm() {
    $('#item_barang, #item_merk, #item_Tipe, #item_spec').val('');
    $('#item_qty').val(1);
    $('#item_satuan').val('pcs');
    $('#item_harga, #item_disc').val(0);
    $('#item_jumlah').text('0');
}

function openGridModal() {
    renderGridTable();
    $('#gridModal').css('display', 'flex');
}

function closeGridModal() {
    renderItemsTable();
    $('#gridModal').css('display', 'none');
}

function updateItem(idx, field, value) {
    if(field === 'jumlah' || field === 'harga_satuan' || field === 'disc') {
        orderItems[idx][field] = parseFloat(value) || 0;
        orderItems[idx].subtotal = Math.max(0, (orderItems[idx].jumlah * orderItems[idx].harga_satuan) - orderItems[idx].disc);
        $('#grid_subtotal_' + idx).text('Rp ' + formatCurrency(orderItems[idx].subtotal));
        calculateGlobal(); // To keep background total in sync if needed
    } else {
        orderItems[idx][field] = value;
    }
}

function addEmptyGridRow() {
    orderItems.push({
        nama_barang: '',
        merk: '',
        Tipe: '',
        spec: '',
        jumlah: 1,
        satuan: 'pcs',
        harga_satuan: 0,
        disc: 0,
        subtotal: 0
    });
    renderGridTable();
    // Focus the newly created row's first input
    setTimeout(() => {
        $(`#grid_nama_barang_${orderItems.length - 1}`).focus();
    }, 100);
}

function removeGridItem(index) {
    if(confirm("Hapus baris ini?")) {
        orderItems.splice(index, 1);
        renderGridTable();
        calculateGlobal();
    }
}

function unformatCurrency(val) {
    if(typeof val === 'number') return val;
    if(!val) return 0;
    return parseFloat(val.replace(/\./g, '').replace(/,/g, '.')) || 0;
}

function updateItemGrid(idx, field, elm) {
    let value = elm.value;
    if(field === 'jumlah' || field === 'harga_satuan' || field === 'disc') {
        let numVal = unformatCurrency(value);
        if(field !== 'jumlah') {
            elm.value = formatCurrency(numVal); // Re-format input field
        }
        orderItems[idx][field] = numVal;
        orderItems[idx].subtotal = Math.max(0, (orderItems[idx].jumlah * orderItems[idx].harga_satuan) - orderItems[idx].disc);
        $('#grid_subtotal_' + idx).text('Rp ' + formatCurrency(orderItems[idx].subtotal));
        calculateGlobal(); // To keep background total in sync if needed
    } else {
        orderItems[idx][field] = value;
    }
}

function renderGridTable() {
    let html = '';
    if(orderItems.length === 0) {
        html = '<tr><td colspan="11" class="text-center text-muted font-italic" style="padding:16px;">Belum ada barang. Klik Tambah Baris Baru.</td></tr>';
    } else {
        const satuanOptions = <?php echo $satuans_json; ?>;
        orderItems.forEach((item, idx) => {
            let selectSatuanHtml = `<select class="excel-select" style="width:100%;" onchange="updateItemGrid(${idx}, 'satuan', this)">`;
            satuanOptions.forEach(st => {
                selectSatuanHtml += `<option value="${st}" ${item.satuan === st ? 'selected' : ''}>${st}</option>`;
            });
            selectSatuanHtml += `</select>`;

            html += `<tr>
                <td class="text-center align-middle"><span class="badge badge-secondary">${idx + 1}</span></td>
                <td class="excel-cell">
                    <input type="text" class="excel-input font-weight-bold" id="grid_nama_barang_${idx}" value="${escapeHtml(item.nama_barang)}" placeholder="Nama..." onchange="updateItemGrid(${idx}, 'nama_barang', this)">
                </td>
                <td class="excel-cell">
                    <input type="text" class="excel-input" value="${escapeHtml(item.spec)}" placeholder="Spesifikasi..." onchange="updateItemGrid(${idx}, 'spec', this)">
                </td>
                <td class="excel-cell">
                    <input type="text" class="excel-input" value="${escapeHtml(item.merk)}" placeholder="Merk..." onchange="updateItemGrid(${idx}, 'merk', this)">
                </td>
                <td class="excel-cell">
                    <input type="text" class="excel-input" value="${escapeHtml(item.Tipe)}" placeholder="Tipe..." onchange="updateItemGrid(${idx}, 'Tipe', this)">
                </td>
                <td class="excel-cell">
                    <input type="number" class="excel-input text-center font-weight-bold" value="${item.jumlah}" min="0.01" step="0.01" onchange="updateItemGrid(${idx}, 'jumlah', this)" oninput="updateItemGrid(${idx}, 'jumlah', this)">
                </td>
                <td class="excel-cell">
                    ${selectSatuanHtml}
                </td>
                <td class="excel-cell align-middle">
                    <input type="text" class="excel-input text-right font-weight-bold" value="${formatCurrency(item.harga_satuan)}" onchange="updateItemGrid(${idx}, 'harga_satuan', this)" onfocus="this.select()">
                </td>
                <td class="excel-cell align-middle">
                    <input type="text" class="excel-input text-right text-danger" value="${formatCurrency(item.disc)}" onchange="updateItemGrid(${idx}, 'disc', this)" onfocus="this.select()">
                </td>
                <td class="text-right align-middle font-weight-bold text-success" id="grid_subtotal_${idx}">Rp ${formatCurrency(item.subtotal)}</td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-light text-danger" style="padding:4px 8px;" onclick="removeGridItem(${idx})" title="Hapus Baris"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
    }
    $('#grid-items-body').html(html);
}

function renderItemsTable() {
    let html = '';
    if(orderItems.length === 0) {
        html = '<tr><td colspan="8" class="text-center text-muted font-italic" style="padding:16px;">Belum ada barang. Klik Edit Rincian Barang (Data Grid).</td></tr>';
    } else {
        orderItems.forEach((item, idx) => {
            html += `<tr>
                <td class="text-center align-middle"><span class="badge badge-secondary">${idx + 1}</span></td>
                <td>
                    <div class="font-weight-bold">${item.nama_barang}</div>
                    <div class="small text-muted">${item.spec}</div>
                    <input type="hidden" name="nama_barang[]" value="${escapeHtml(item.nama_barang)}">
                    <input type="hidden" name="spec[]" value="${escapeHtml(item.spec)}">
                    <input type="hidden" name="merk[]" value="${escapeHtml(item.merk)}">
                    <input type="hidden" name="Tipe[]" value="${escapeHtml(item.Tipe)}">
                    <input type="hidden" name="jumlah[]" value="${item.jumlah}">
                    <input type="hidden" name="satuan[]" value="${escapeHtml(item.satuan)}">
                    <input type="hidden" name="harga_satuan[]" value="${item.harga_satuan}">
                    <input type="hidden" name="disc_item[]" value="${item.disc}">
                </td>
                <td>
                    <div class="small">${item.merk || '-'}</div>
                    <div class="small text-muted">${item.Tipe || '-'}</div>
                </td>
                <td class="text-center align-middle">
                    <span class="font-weight-bold">${item.jumlah}</span><br>
                    <span class="small text-muted">${item.satuan}</span>
                </td>
                <td class="text-right align-middle">Rp ${formatCurrency(item.harga_satuan)}</td>
                <td class="text-right align-middle text-danger">Rp ${formatCurrency(item.disc)}</td>
                <td class="text-right align-middle font-weight-bold text-success">Rp ${formatCurrency(item.subtotal)}</td>
                <td class="text-center align-middle">
                    <!-- Removed inline edit/delete since it's managed via modal now -->
                </td>
            </tr>`;
        });
    }
    $('#po-items-body').html(html);
    calculateGlobal();
}

function escapeHtml(text) {
  if(!text) return '';
  return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
}

function calculateGlobal() {
    let harga_vendor = orderItems.reduce((sum, item) => sum + (item.jumlah * item.harga_satuan) - item.disc, 0);
    
    // Formatting total harga_vendor as string for display
    $('#harga_vendor').val(harga_vendor); // hidden val could be string with , or raw number, but logic below parses it
    
    let diskon_raw = parseFloat($('#diskon_vendor').val()) || 0;
    let diskon_type = $('#diskon_type').val();
    let diskon_rp = 0;
    
    if (diskon_type === 'percent') {
        diskon_rp = harga_vendor * (Math.min(diskon_raw, 100) / 100.0);
    } else {
        diskon_rp = Math.min(diskon_raw, harga_vendor);
    }
    
    let subtotal_setelah_diskon = Math.max(0, harga_vendor - diskon_rp);
    
    let isPpn = $('#ppn_toggle').is(':checked');
    let ppn_nilai = 0;
    if(isPpn) {
        ppn_nilai = subtotal_setelah_diskon * 0.11;
        $('#ppn_nilai').val(ppn_nilai);
        $('#row_ppn_nominal').show();
        $('#ppn_nominal').val(formatCurrency(ppn_nilai));
        $('#ppn_label').text('Pakai PPN 11%').css('color', '#059669');
    } else {
        $('#ppn_nilai').val(0);
        $('#row_ppn_nominal').hide();
        $('#ppn_label').text('Tanpa PPN').css('color', '#6c757d');
    }
    
    let grand_total = subtotal_setelah_diskon + ppn_nilai;
    $('#total_setelah_diskon').text('Rp ' + formatCurrency(grand_total));
    
    // update harga_vendor formatted value using original input-rupiah class styling if needed, but since it's readonly now:
    let formatted_harga_vendor = formatCurrency(harga_vendor);
    $('#harga_vendor').val(formatted_harga_vendor);
    
    // Dynamic Signature
    if (grand_total < 5000000) {
        $('#preview_acc_sp').html('<u>( Pembelian )</u>');
        $('#preview_acc_jabatan').text('Bagian Pembelian');
    } else {
        $('#preview_acc_sp').html('<u>( Direktur )</u>');
        $('#preview_acc_jabatan').text('Direktur Utama');
    }
}

function submitAs(status) {
    $('#action_status').val(status);
    
    if (!$('#nama_vendor').val()) {
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: 'Silakan pilih Supplier/Vendor.',
            confirmButtonColor: '#3b82f6'
        }).then(() => {
            $('#namasup_input').focus();
        });
        return;
    }
    if (orderItems.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: 'Minimal harus ada 1 barang pesanan.',
            confirmButtonColor: '#3b82f6'
        });
        return;
    }
    
    if (status === 'diajukan') {
        let hasNewFile = document.getElementById('lampiran_pdf').files.length > 0;
        let hasExistingFile = $('input[name="nama_lampiran_existing"]').val().trim() !== '';
        if (!hasNewFile && !hasExistingFile) {
            Swal.fire({
                icon: 'warning',
                title: 'Dokumen Diperlukan',
                text: 'Lampiran Surat Pesanan (PDF/Gambar) wajib diunggah sebelum dapat diajukan.',
                confirmButtonColor: '#3b82f6'
            });
            return;
        }
    }
    
    // Since harga_vendor is formatted as "1,200,000", we should strip commas before submitting
    $('#harga_vendor').val($('#harga_vendor').val().replace(/,/g, ''));
    $('#diskon_vendor').val($('#diskon_vendor').val().replace(/,/g, ''));
    
    $('#form-po').submit();
}

$(document).ready(function() {
    <?php
    $post_items_json = '[]';
    if(isset($_POST['nama_barang']) && is_array($_POST['nama_barang']) && count($_POST['nama_barang']) > 0) {
        $p_items = array();
        for($i=0; $i<count($_POST['nama_barang']); $i++) {
            if(trim($_POST['nama_barang'][$i]) !== '') {
                $qty = (float)$_POST['jumlah'][$i];
                $price = (float)$_POST['harga_satuan'][$i];
                $disc = (float)$_POST['disc_item'][$i];
                $p_items[] = array(
                    'nama_barang' => $_POST['nama_barang'][$i],
                    'merk' => $_POST['merk'][$i],
                    'Tipe' => $_POST['Tipe'][$i],
                    'spec' => $_POST['spec'][$i],
                    'satuan' => $_POST['satuan'][$i],
                    'jumlah' => $qty,
                    'harga_satuan' => $price,
                    'disc' => $disc,
                    'subtotal' => max(0, ($qty * $price) - $disc)
                );
            }
        }
        $post_items_json = json_encode($p_items, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        if ($post_items_json === false) {
            $post_items_json = '[]';
        }
    }
    ?>
    let initItemsRaw = '<?php echo addslashes($post_items_json); ?>';

    let initItems = [];
    try {
        initItems = JSON.parse(initItemsRaw);
    } catch(e) {
        console.error("Gagal parse JSON items:", e);
    }
    if(initItems.length > 0) {
        orderItems = initItems;
        renderItemsTable();
        calculateGlobal();
    } else {
        renderItemsTable();
    }
    
    let ppn_val = parseFloat($('#ppn_nilai').val()) || 0;
    if(ppn_val > 0) {
        $('#ppn_toggle').prop('checked', true);
        calculateGlobal();
    }
});
</script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-field').select2({
        width: '100%',
        dropdownAutoWidth: true
    });

    // Enter key navigation in grid
    $(document).on('keydown', '#gridModal input, #gridModal select', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var inputs = $('#gridModal').find('.excel-input, .excel-select');
            var index = inputs.index(this);
            if (index > -1 && index < inputs.length - 1) {
                inputs.eq(index + 1).focus();
            }
        }
    });
});
</script>
<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>
