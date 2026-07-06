<?php
// views/buat_pesanan.php
// Create Purchase Order form and processing (Upgraded with order_form.php UI)

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
    if(isset($_POST['existing_lampiran']) && trim($_POST['existing_lampiran']) !== '') {
        $nama_lampiran_arr = array_map('trim', explode(',', $_POST['existing_lampiran']));
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
        'unit'          => isset($_POST['unit'])          ? trim($_POST['unit'])          : '',
        'tglkirim'      => isset($_POST['tglkirim'])      ? trim($_POST['tglkirim'])      : '',
        'ppn'           => isset($_POST['ppn_nilai'])     ? (float)$_POST['ppn_nilai']   : 0.0,
    );

    $item_names  = isset($_POST['nama_barang'])  ? $_POST['nama_barang']  : array();
    $item_qtys   = isset($_POST['jumlah'])        ? $_POST['jumlah']        : array();
    $item_prices = isset($_POST['harga_satuan']) ? $_POST['harga_satuan'] : array();
    $item_merks  = isset($_POST['merk'])         ? $_POST['merk']         : array();
    $item_models = isset($_POST['model'])        ? $_POST['model']        : array();
    $item_specs  = isset($_POST['spec'])         ? $_POST['spec']         : array();
    $item_satuans= isset($_POST['satuan'])       ? $_POST['satuan']       : array();
    $item_discs  = isset($_POST['disc_item'])    ? $_POST['disc_item']    : array();

    if ($no_pesanan === '' || $tgl_pesanan === '' || $nama_vendor === '') {
        $error = 'Nomor pesanan, tanggal, dan nama vendor wajib diisi.';
    } elseif ($harga_vendor <= 0) {
        $error = 'Harga dari vendor harus lebih besar dari 0.';
    } elseif (empty($item_names)) {
        $error = 'Minimal harus memasukkan 1 barang pesanan.';
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
                    'model'        => isset($item_models[$i]) ? trim($item_models[$i]) : '',
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
            $po_id = db_create_purchase_order(
                $no_pesanan, $tgl_pesanan, $nama_vendor,
                $harga_vendor, $diskon_vendor, $total_setelah_diskon,
                $action_status, $_SESSION['user_id'], $items, $extra
            );
            if ($po_id !== false) {
                $success = 'Surat Pesanan ' . htmlspecialchars($no_pesanan) . ' berhasil disimpan sebagai ' . ($action_status === 'diajukan' ? 'Diajukan ke Direktur' : 'Draft') . '.';
                $_POST = array(); // Clear post data after success
            } else {
                $error = 'Gagal menyimpan Surat Pesanan. Kemungkinan nomor pesanan sudah terpakai.';
            }
        }
    }
}

$next_po_num = db_generate_po_number();
$today = date('Y-m-d');
$suppliers = db_get_suppliers();
$suppliers_json = json_encode($suppliers);

require_once dirname(__FILE__) . '/../includes/header.php';

$satuans = array('pcs','unit','lusin','kodi','rim','roll','box','set','kg','ltr','m','cm');
$satuans_json = json_encode($satuans);
$opts_bayar = array('Tunai / Cash','Transfer Bank','Kredit 30 Hari','Kredit 60 Hari','Kredit 90 Hari','Giro');
?>

<style>
/* Custom Offline CSS matching the requested enterprise layout */
.sp-container {
    width: 100%;
    position: relative;
    padding-bottom: 96px; /* space for sticky bar */
    font-family: 'Inter', sans-serif, inherit;
    box-sizing: border-box;
}

.sp-panel {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    border: 1px solid #e5e7eb;
    margin-bottom: 24px;
}

.sp-panel-header {
    padding: 10px 14px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f9fafb;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

.sp-panel-title {
    font-size: 18px;
    font-weight: 700;
    color: #0f766e;
    margin: 0;
    display: flex;
    align-items: center;
}
.sp-panel-title i { margin-right: 8px; }

.sp-panel-body { padding: 16px; }

/* Grid Systems */
.sp-grid { display: grid; gap: 16px; }
.sp-grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
.sp-grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
.sp-grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
.sp-grid-cols-1 { grid-template-columns: repeat(1, 1fr); }

@media (min-width: 1024px) {
    .lg-grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
    .lg-col-span-3 { grid-column: span 3 / span 3; }
    .lg-col-span-1 { grid-column: span 1 / span 1; }
}
@media (min-width: 1280px) {
    .xl-grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
    .xl-col-span-2 { grid-column: span 2 / span 2; }
    .xl-col-span-4 { grid-column: span 4 / span 4; }
}

/* Flexbox utilities */
.sp-flex { display: flex; }
.sp-flex-col { flex-direction: column; }
.sp-items-center { align-items: center; }
.sp-justify-between { justify-content: space-between; }
.sp-justify-end { justify-content: flex-end; }
.sp-justify-center { justify-content: center; }
.sp-gap-2 { gap: 8px; }
.sp-gap-3 { gap: 12px; }
.sp-gap-4 { gap: 16px; }
.sp-gap-6 { gap: 24px; }
.sp-flex-grow { flex-grow: 1; }

/* Inputs and Labels */
.sp-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
}
.sp-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 15px;
    line-height: 20px;
    transition: all 0.2s;
    box-sizing: border-box;
    font-family: inherit;
    background-color: #fff;
}
.sp-input:focus {
    outline: none;
    border-color: #0d9488;
    box-shadow: 0 0 0 2px rgba(13, 148, 136, 0.2);
}
.sp-input-readonly {
    background-color: transparent;
    border: none;
    font-size: 24px;
    font-weight: 900;
    color: #134e4a;
    text-align: right;
    width: 160px;
}
.sp-input-readonly:focus { outline: none; box-shadow: none; }

/* Buttons */
.sp-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}
.sp-btn-primary {
    background-color: #0d9488;
    color: white;
    font-weight: 500;
    padding: 8px 16px;
    font-size: 14px;
}
.sp-btn-primary:hover { background-color: #0f766e; color: white; text-decoration: none;}

.sp-btn-success {
    background-color: #059669;
    color: white;
    font-weight: 700;
    padding: 12px 24px;
    font-size: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
.sp-btn-success:hover { background-color: #047857; color: white; text-decoration: none;}

.sp-btn-outline {
    background-color: transparent;
    border: 1px solid #d1d5db;
    color: #374151;
    font-weight: 500;
    padding: 8px 16px;
    font-size: 14px;
}
.sp-btn-outline:hover { background-color: #f9fafb; text-decoration: none;}

.sp-btn-danger {
    background-color: #ef4444;
    color: white;
    padding: 8px;
    border-radius: 6px;
}
.sp-btn-danger:hover { background-color: #dc2626; color: white; text-decoration: none;}

.sp-btn-icon {
    background: transparent;
    border: none;
    color: #9ca3af;
    font-size: 20px;
    cursor: pointer;
}
.sp-btn-icon:hover { color: #ef4444; }

/* Table */
.sp-table-container {
    overflow-x: auto;
}
.sp-table {
    width: 100%;
    min-width: 800px;
    border-collapse: collapse;
}
.sp-table th {
    background-color: #f9fafb;
    color: #374151;
    font-weight: 600;
    font-size: 15px;
    padding: 10px 12px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}
.sp-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 15px;
    vertical-align: middle;
}
.sp-table tbody tr:hover { background-color: #f9fafb; }

/* Utility Classes */
.sp-text-center { text-align: center; }
.sp-text-right { text-align: right; }
.sp-font-bold { font-weight: 700; }
.sp-font-semibold { font-weight: 600; }
.sp-text-sm { font-size: 15px; }
.sp-text-xs { font-size: 13px; }
.sp-text-lg { font-size: 18px; }
.sp-text-2xl { font-size: 26px; font-weight: 900; }
.sp-text-gray { color: #6b7280; }
.sp-text-teal { color: #0f766e; }
.sp-text-red { color: #ef4444; }
.sp-mt-2 { margin-top: 8px; }
.sp-mt-4 { margin-top: 16px; }
.sp-mb-0 { margin-bottom: 0 !important; }
.sp-pt-4 { padding-top: 16px; }
.sp-w-full { width: 100%; }
.sp-italic { font-style: italic; }

.sp-border-t { border-top: 1px solid #e5e7eb; }
.sp-border-l { border-left: 1px solid #f3f4f6; padding-left: 24px; }

/* Widgets */
.sp-widget {
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
}
.sp-widget-blue { background-color: #eff6ff; border: 1px solid #dbeafe; }
.sp-widget-gray { background-color: #f9fafb; border: 1px solid #f3f4f6; flex-grow: 1; }
.sp-widget-teal { background-color: #f0fdfa; border: 1px solid #ccfbf1; flex-grow: 1; }
.sp-widget-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin: 0 0 8px 0;
}
.sp-widget-blue .sp-widget-title { color: #1e40af; }
.sp-widget-gray .sp-widget-title { color: #6b7280; }
.sp-widget-teal .sp-widget-title { color: #0f766e; }

.sp-pulse-dot {
    width: 12px;
    height: 12px;
    background-color: #3b82f6;
    border-radius: 50%;
    animation: sp-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes sp-pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }

/* Sticky Bar */
.sp-sticky-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: #ffffff;
    border-top: 1px solid #e5e7eb;
    box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
    z-index: 40;
    padding: 16px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-left: 260px; /* Sidebar offset */
    transition: margin-left 0.3s;
}

/* Modal */
.sp-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    backdrop-filter: blur(4px);
}
.sp-modal-content {
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    width: 100%;
    max-width: 672px;
    overflow: hidden;
}
.sp-modal-header {
    padding: 16px 24px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f9fafb;
}
.sp-modal-body { padding: 24px; max-height: 70vh; overflow-y: auto;}
.sp-modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #f3f4f6;
    background-color: #f9fafb;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.sp-input-group {
    position: relative;
    display: flex;
    align-items: center;
}
.sp-input-prefix {
    position: absolute;
    left: 12px;
    color: #6b7280;
    font-weight: 500;
}
.sp-input-with-prefix {
    padding-left: 40px;
}

.sp-highlight-box {
    background-color: #f0fdfa;
    border: 1px solid #ccfbf1;
    border-radius: 8px;
    padding: 16px;
    margin-top: 8px;
}

/* Suggestion Box */
.suggestions-box {
    position: absolute;
    width: 100%;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0 0 6px 6px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    z-index: 50;
    max-height: 240px;
    overflow-y: auto;
    display: none;
}
.suggestion-item {
    padding: 8px 16px;
    font-size: 14px;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
}
.suggestion-item:hover { background-color: #f0fdfa; }

/* Toggle Switch */
.sp-toggle-switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
}
.sp-toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.sp-toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 24px;
}
.sp-toggle-slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}
input:checked + .sp-toggle-slider { background-color: #0d9488; }
input:checked + .sp-toggle-slider:before { transform: translateX(20px); }

@media (max-width: 768px) {
    .sp-sticky-bar { margin-left: 0; }
    .sp-border-l { border-left: none; padding-left: 0; }
}

.alert-message {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 16px;
    font-weight: 500;
}
.alert-error { background-color: #fef2f2; border: 1px solid #f87171; color: #b91c1c; }
.alert-success { background-color: #f0fdf4; border: 1px solid #4ade80; color: #15803d; }
</style>

<?php if ($error !== ''): ?>
    <div class="alert-message alert-error">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if ($success !== ''): ?>
    <div class="alert-message alert-success">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<form action="home.php?page=buat_pesanan" method="POST" id="form-po" enctype="multipart/form-data">
<input type="hidden" name="action_status" id="action_status" value="draft">
<input type="hidden" name="ppn_nilai" id="ppn_nilai" value="<?php echo isset($_POST['ppn_nilai']) ? $_POST['ppn_nilai'] : 0; ?>">

<div class="sp-container">
    
    <!-- Header Section -->
    <div class="sp-panel">
        <div class="sp-panel-header">
            <h2 class="sp-panel-title"><i class="fas fa-file-invoice"></i> Informasi Dasar Pesanan (spu_h)</h2>
        </div>
        <div class="sp-panel-body">
            
            <div class="sp-grid sp-grid-cols-1 lg-grid-cols-4 sp-gap-6">
                
                <!-- Form Inputs -->
                <div class="lg-col-span-3">
                    <div class="sp-grid sp-grid-cols-1 sp-grid-cols-2 xl-grid-cols-4 sp-gap-4">
                        
                        <div style="position:relative;" class="xl-col-span-2">
                            <label class="sp-label">Kepada Yth. (Supplier) <span class="sp-text-red">*</span></label>
                            <input type="hidden" id="nama_vendor" name="nama_vendor" value="<?php echo isset($_POST['nama_vendor']) ? htmlspecialchars($_POST['nama_vendor']) : ''; ?>">
                            <input type="text" id="namasup_input" class="sp-input" placeholder="Ketik nama supplier..." autocomplete="off" onkeyup="searchSupplier(this.value)" value="<?php echo isset($_POST['nama_vendor']) ? htmlspecialchars($_POST['nama_vendor']) : ''; ?>" required>
                            <div id="supplier-suggestions" class="suggestions-box"></div>
                        </div>

                        <div class="xl-col-span-2">
                            <label class="sp-label">No. Surat Pesanan <span class="sp-text-red">*</span></label>
                            <input type="text" name="no_pesanan" id="no_pesanan" class="sp-input sp-font-bold" value="<?php echo isset($_POST['no_pesanan']) ? htmlspecialchars($_POST['no_pesanan']) : $next_po_num; ?>" required>
                        </div>

                        <div>
                            <label class="sp-label">Tgl. Pesan <span class="sp-text-red">*</span></label>
                            <input type="date" name="tgl_pesanan" id="tgl_pesanan" class="sp-input" value="<?php echo isset($_POST['tgl_pesanan']) ? htmlspecialchars($_POST['tgl_pesanan']) : $today; ?>" required>
                        </div>

                        <div>
                            <label class="sp-label">Tgl. Kirim</label>
                            <input type="date" name="tglkirim" id="tglkirim" class="sp-input" value="<?php echo isset($_POST['tglkirim']) ? htmlspecialchars($_POST['tglkirim']) : ''; ?>">
                        </div>

                        <div class="xl-col-span-2">
                            <label class="sp-label">Gudang / Unit</label>
                            <input type="text" name="unit" id="unit" class="sp-input" placeholder="Ketik nama gudang/unit..." value="<?php echo isset($_POST['unit']) ? htmlspecialchars($_POST['unit']) : ''; ?>">
                        </div>

                        <div class="xl-col-span-2">
                            <label class="sp-label">Jenis Bayar</label>
                            <select name="pembayaran" id="pembayaran" class="sp-input">
                                <option value="">-- Pilih --</option>
                                <?php foreach ($opts_bayar as $ob): ?>
                                    <option value="<?php echo $ob; ?>" <?php echo (isset($_POST['pembayaran']) && $_POST['pembayaran']===$ob) ? 'selected' : ''; ?>><?php echo $ob; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="xl-col-span-2">
                            <label class="sp-label">Surat Penawaran No.</label>
                            <input type="text" name="no_tawar" id="no_tawar" class="sp-input" placeholder="Contoh: xxx/PEN/PBU/mm/yyyy" value="<?php echo isset($_POST['no_tawar']) ? htmlspecialchars($_POST['no_tawar']) : ''; ?>">
                        </div>

                        <div>
                            <label class="sp-label">Tgl. Penawaran</label>
                            <input type="date" name="tgl_tawar" id="tgl_tawar" class="sp-input" value="<?php echo isset($_POST['tgl_tawar']) ? htmlspecialchars($_POST['tgl_tawar']) : ''; ?>">
                        </div>
                        
                        <div>
                            <label class="sp-label">No. Surat Permintaan</label>
                            <input type="text" name="no_permintaan" id="no_permintaan" class="sp-input" placeholder="No permintaan..." value="<?php echo isset($_POST['no_permintaan']) ? htmlspecialchars($_POST['no_permintaan']) : ''; ?>">
                        </div>

                        <div class="xl-col-span-4 sp-mt-2">
                            <label class="sp-label">Keterangan Umum</label>
                            <textarea name="notein" id="notein" class="sp-input" rows="2" placeholder="Catatan tambahan..."><?php echo isset($_POST['notein']) ? htmlspecialchars($_POST['notein']) : ''; ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Right Widget Area -->
                <div class="lg-col-span-1 sp-border-l sp-flex sp-flex-col">
                    <div class="sp-widget sp-widget-blue">
                        <h3 class="sp-widget-title">Status Dokumen</h3>
                        <div class="sp-flex sp-items-center sp-gap-2">
                            <div class="sp-pulse-dot"></div>
                            <span class="sp-text-sm sp-font-semibold" style="color: #1d4ed8;">Draft / Pembuatan</span>
                        </div>
                    </div>

                    <div class="sp-widget sp-widget-gray">
                        <h3 class="sp-widget-title">Info Supplier</h3>
                        <div id="widget-supplier-info" class="sp-text-sm sp-text-gray sp-mt-2">
                            <i class="fas fa-info-circle" style="color:#9ca3af; margin-right:4px;"></i> Pilih supplier untuk melihat info.
                        </div>
                    </div>
                    
                    <div class="sp-widget sp-widget-teal">
                        <h3 class="sp-widget-title">Biaya Global</h3>
                        <div class="sp-mt-2">
                            <label class="sp-label sp-text-xs">Harga Vendor</label>
                            <div class="sp-input-group sp-mb-2">
                                <span class="sp-input-prefix">Rp</span>
                                <input type="text" name="harga_vendor" id="harga_vendor_display" class="sp-input sp-input-with-prefix sp-text-right sp-font-semibold" value="0" readonly>
                                <input type="hidden" name="harga_vendor" id="harga_vendor" value="<?php echo isset($_POST['harga_vendor']) ? htmlspecialchars($_POST['harga_vendor']) : '0'; ?>">
                            </div>
                            
                            <label class="sp-label sp-text-xs">Diskon Global</label>
                            <div class="sp-flex sp-mb-2">
                                <select name="diskon_type" id="diskon_type" class="sp-input" style="width:60px; border-top-right-radius:0; border-bottom-right-radius:0; padding:8px 4px;" onchange="calculateGlobal()">
                                    <option value="rp" <?php echo (!isset($_POST['diskon_type']) || $_POST['diskon_type']==='rp') ? 'selected' : ''; ?>>Rp</option>
                                    <option value="percent" <?php echo (isset($_POST['diskon_type']) && $_POST['diskon_type']==='percent') ? 'selected' : ''; ?>>%</option>
                                </select>
                                <input type="number" name="diskon_vendor" id="diskon_vendor" class="sp-input" style="border-top-left-radius:0; border-bottom-left-radius:0; border-left:0; text-align:right;" value="<?php echo isset($_POST['diskon_vendor']) ? htmlspecialchars($_POST['diskon_vendor']) : '0'; ?>" onkeyup="calculateGlobal()" onchange="calculateGlobal()">
                            </div>
                            
                            <div class="sp-flex sp-items-center sp-justify-between sp-mt-4">
                                <label class="sp-label sp-mb-0">Pakai PPN 11%</label>
                                <label class="sp-toggle-switch">
                                    <input type="checkbox" id="ppn_toggle" onchange="calculateGlobal()">
                                    <span class="sp-toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Items Section -->
    <div class="sp-panel sp-mb-0" style="overflow:hidden;">
        <div class="sp-panel-header">
            <h2 class="sp-panel-title"><i class="fas fa-boxes"></i> Detail Barang Pesanan (spu_d)</h2>
            <button type="button" class="sp-btn sp-btn-primary" onclick="openItemModal()"><i class="fas fa-plus"></i> Tambah Item Barang</button>
        </div>
        <div class="sp-table-container">
            <table class="sp-table" id="itemsTable">
                <thead>
                    <tr>
                        <th class="sp-text-center" style="width:48px;">No</th>
                        <th style="width:25%;">Nama Barang</th>
                        <th style="width:16%;">Merk / Model</th>
                        <th class="sp-text-center" style="width:96px;">Qty</th>
                        <th class="sp-text-right" style="width:128px;">Harga Satuan</th>
                        <th class="sp-text-right" style="width:128px;">Diskon Item</th>
                        <th class="sp-text-right" style="width:128px;">Subtotal</th>
                        <th class="sp-text-center" style="width:64px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="po-items-body">
                    <!-- Javascript will populate this and insert hidden inputs for POST -->
                </tbody>
            </table>
        </div>
        
        <!-- Lampiran Section -->
        <div class="sp-panel-body" style="border-top: 1px solid #e5e7eb; background-color: #f9fafb;">
            <div class="sp-flex sp-justify-between sp-items-end sp-mb-4 sp-pb-4">
                <div>
                    <h1 class="sp-panel-title" style="color:#374151; font-size:16px;"><i class="fas fa-paperclip"></i> Dokumen Lampiran</h1>
                    <p class="sp-text-sm sp-text-gray sp-mt-1">Pilih file PDF untuk diunggah bersama form pesanan.</p>
                </div>
                <div class="sp-flex sp-gap-2 sp-items-center">
                    <input type="file" name="lampiran_pdf[]" id="lampiran_pdf" accept="application/pdf" multiple class="sp-input" style="max-width: 300px; padding: 4px;" onchange="handleFileSelect(event)">
                </div>
            </div>
            <div id="file_list" class="sp-text-sm" style="display:flex; flex-direction:column; gap:4px;"></div>
        </div>
    </div>

</div>

<!-- Sticky Bottom Bar -->
<div class="sp-sticky-bar">
    <div class="sp-flex sp-items-center sp-gap-4">
        <div class="sp-text-gray sp-font-semibold">Total Item: <span id="lbl_totalitem" class="sp-text-lg" style="color:#1f2937;">0</span></div>
    </div>
    <div class="sp-flex sp-items-center sp-gap-6">
        <div class="sp-text-right">
            <div class="sp-text-xs sp-text-gray sp-font-bold" style="text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Grand Total Net</div>
            <div class="sp-text-2xl sp-text-teal">Rp <span id="lbl_grandtotal">0.00</span></div>
        </div>
        <div>
            <button type="button" class="sp-btn sp-btn-outline" style="margin-right:8px;" onclick="submitAs('draft')"><i class="fas fa-save"></i> Simpan Draft</button>
            <button type="button" class="sp-btn sp-btn-success" onclick="submitAs('diajukan')"><i class="fas fa-paper-plane" style="font-size:16px;"></i> AJUKAN KE DIREKTUR</button>
        </div>
    </div>
</div>

</form>

<!-- Modal Tambah/Edit Item -->
<div id="itemModal" class="sp-modal-overlay">
    <div class="sp-modal-content">
        <!-- Modal Header -->
        <div class="sp-modal-header">
            <h3 class="sp-panel-title" style="font-size:18px;"><i class="fas fa-box-open"></i> Form Detail Barang</h3>
            <button type="button" onclick="closeItemModal()" class="sp-btn-icon"><i class="fas fa-times"></i></button>
        </div>
        
        <!-- Modal Body -->
        <div class="sp-modal-body">
            <input type="hidden" id="edit_index" value="-1">
            <div class="sp-grid sp-grid-cols-1 sp-gap-4">
                
                <div class="sp-flex sp-items-center sp-gap-4">
                    <label class="sp-label" style="width: 150px; margin-bottom: 0;">Nama Barang <span class="sp-text-red">*</span></label>
                    <input type="text" id="item_barang" class="sp-input sp-font-semibold" style="flex-grow: 1; font-size:16px; padding:10px 12px;">
                </div>
                
                <div class="sp-flex sp-items-center sp-gap-4">
                    <label class="sp-label" style="width: 150px; margin-bottom: 0;">Spesifikasi</label>
                    <input type="text" id="item_spec" class="sp-input" style="flex-grow: 1;">
                </div>
                
                <div class="sp-flex sp-items-center sp-gap-4">
                    <label class="sp-label" style="width: 150px; margin-bottom: 0;">Merk</label>
                    <input type="text" id="item_merk" class="sp-input" style="flex-grow: 1;">
                </div>
                
                <div class="sp-flex sp-items-center sp-gap-4">
                    <label class="sp-label" style="width: 150px; margin-bottom: 0;">Model</label>
                    <input type="text" id="item_model" class="sp-input" style="flex-grow: 1;">
                </div>
                
                <div class="sp-grid sp-grid-cols-4 sp-gap-3 sp-border-t sp-pt-4 sp-mt-2">
                    <div>
                        <label class="sp-label">Kuantitas (Qty)</label>
                        <input type="number" id="item_qty" class="sp-input sp-text-center sp-text-lg sp-font-semibold" value="1" step="0.01" min="0.01" onkeyup="calculateItemTotal()" onchange="calculateItemTotal()">
                    </div>
                    <div>
                        <label class="sp-label">Satuan</label>
                        <select id="item_satuan" class="sp-input">
                            <?php foreach($satuans as $st) echo '<option value="'.$st.'">'.$st.'</option>'; ?>
                        </select>
                    </div>
                    <div>
                        <label class="sp-label">Harga Satuan</label>
                        <div class="sp-input-group">
                            <span class="sp-input-prefix">Rp</span>
                            <input type="number" id="item_harga" class="sp-input sp-input-with-prefix sp-text-right sp-font-semibold" value="0" onkeyup="calculateItemTotal()" onchange="calculateItemTotal()">
                        </div>
                    </div>
                    <div>
                        <label class="sp-label">Diskon Item</label>
                        <div class="sp-input-group">
                            <span class="sp-input-prefix">Rp</span>
                            <input type="number" id="item_disc" class="sp-input sp-input-with-prefix sp-text-right sp-font-semibold sp-text-red" value="0" onkeyup="calculateItemTotal()" onchange="calculateItemTotal()">
                        </div>
                    </div>
                </div>
                
                <div class="sp-highlight-box sp-flex sp-justify-between sp-items-center sp-mt-2">
                    <span class="sp-text-sm sp-font-bold sp-text-teal" style="text-transform:uppercase; letter-spacing:0.05em;">Subtotal Item</span>
                    <div class="sp-flex sp-items-center sp-gap-2">
                        <span class="sp-font-bold sp-text-teal">Rp</span>
                        <input type="text" id="item_jumlah" class="sp-input-readonly" readonly value="0">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="sp-modal-footer">
            <button type="button" class="sp-btn sp-btn-outline" onclick="closeItemModal()">Batal</button>
            <button type="button" class="sp-btn sp-btn-primary" onclick="addItem()"><i class="fas fa-check"></i> Simpan ke Tabel</button>
        </div>
    </div>
</div>

<script>
const masterSuppliers = <?php echo $suppliers_json; ?>;
let orderItems = [];

// Helper currency format
function formatCurrency(num) {
    return parseFloat(num).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Vendor Autocomplete
let supplierTimeout;
function searchSupplier(query) {
    clearTimeout(supplierTimeout);
    let $suggestions = $('#supplier-suggestions');
    if(query.length < 1) {
        $suggestions.hide();
        // Clear value if empty
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
            // If they type a custom vendor not in list, we still allow it
            $('#nama_vendor').val(query);
            $('#widget-supplier-info').html(`<div class="sp-font-bold" style="color:#1f2937;">${query}</div><div class="sp-text-xs sp-text-gray sp-mt-2">Vendor Baru/Kustom</div>`);
        }
    }, 200);
}

function selectSupplier(nama, kode) {
    $('#namasup_input').val(nama);
    $('#nama_vendor').val(nama); // This is what gets POSTed
    $('#supplier-suggestions').hide();
    
    // Update Widget Supplier Info
    $('#widget-supplier-info').html(`
        <div class="sp-font-bold" style="color:#1f2937;">${nama}</div>
        <div class="sp-text-xs sp-text-gray sp-mt-2">Kode: ${kode}</div>
    `);
}

// Close suggestion box on outside click
$(document).click(function(e) {
    if (!$(e.target).closest('#namasup_input').length && !$(e.target).closest('#supplier-suggestions').length) {
        $('#supplier-suggestions').hide();
    }
});

// File UI handler
function handleFileSelect(event) {
    const files = event.target.files;
    let html = '';
    for(let i=0; i<files.length; i++) {
        html += `<div style="display:inline-flex; align-items:center; background:#e5e7eb; padding:4px 8px; border-radius:4px;">
            <i class="fas fa-file-pdf" style="color:#dc2626; margin-right:6px;"></i> ${files[i].name}
        </div>`;
    }
    $('#file_list').html(html);
}

// Modal Logic
function openItemModal(index = -1) {
    if(index >= 0) {
        let itm = orderItems[index];
        $('#item_barang').val(itm.nama_barang);
        $('#item_spec').val(itm.spec);
        $('#item_merk').val(itm.merk);
        $('#item_model').val(itm.model);
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
    $('#item_jumlah').val(formatCurrency(total));
}

function clearItemForm() {
    $('#item_barang, #item_merk, #item_model, #item_spec').val('');
    $('#item_qty').val(1);
    $('#item_satuan').val('pcs');
    $('#item_harga, #item_disc').val(0);
    $('#item_jumlah').val('0.00');
}

function addItem() {
    let item = {
        nama_barang: $('#item_barang').val().trim(),
        merk: $('#item_merk').val().trim(),
        model: $('#item_model').val().trim(),
        spec: $('#item_spec').val().trim(),
        jumlah: parseFloat($('#item_qty').val()) || 0,
        satuan: $('#item_satuan').val(),
        harga_satuan: parseFloat($('#item_harga').val()) || 0,
        disc: parseFloat($('#item_disc').val()) || 0
    };
    
    item.subtotal = Math.max(0, (item.jumlah * item.harga_satuan) - item.disc);
    
    if(!item.nama_barang) {
        alert("Nama barang harus diisi!");
        return;
    }
    if(item.jumlah <= 0) {
        alert("Kuantitas harus lebih dari 0!");
        return;
    }
    
    let idx = parseInt($('#edit_index').val());
    if(idx >= 0) {
        orderItems[idx] = item;
    } else {
        orderItems.push(item);
    }
    
    renderItemsTable();
    closeItemModal();
    calculateGlobal();
}

function removeItem(index) {
    if(confirm("Hapus barang ini dari pesanan?")) {
        orderItems.splice(index, 1);
        renderItemsTable();
        calculateGlobal();
    }
}

function renderItemsTable() {
    let html = '';
    if(orderItems.length === 0) {
        html = '<tr><td colspan="8" class="sp-text-center sp-text-gray sp-italic" style="padding:32px;">Belum ada barang yang ditambahkan.</td></tr>';
    } else {
        orderItems.forEach((item, idx) => {
            html += `<tr>
                <td class="sp-text-center">${idx + 1}</td>
                <td>
                    <div class="sp-font-semibold" style="color:#1f2937;">${item.nama_barang}</div>
                    <div class="sp-text-xs sp-text-gray">${item.spec}</div>
                    <!-- Hidden inputs for POST form submission -->
                    <input type="hidden" name="nama_barang[]" value="${escapeHtml(item.nama_barang)}">
                    <input type="hidden" name="spec[]" value="${escapeHtml(item.spec)}">
                    <input type="hidden" name="merk[]" value="${escapeHtml(item.merk)}">
                    <input type="hidden" name="model[]" value="${escapeHtml(item.model)}">
                    <input type="hidden" name="jumlah[]" value="${item.jumlah}">
                    <input type="hidden" name="satuan[]" value="${escapeHtml(item.satuan)}">
                    <input type="hidden" name="harga_satuan[]" value="${item.harga_satuan}">
                    <input type="hidden" name="disc_item[]" value="${item.disc}">
                </td>
                <td>
                    <div class="sp-text-sm">${item.merk || '-'}</div>
                    <div class="sp-text-xs sp-text-gray">${item.model || '-'}</div>
                </td>
                <td class="sp-text-center">
                    <div class="sp-font-semibold">${item.jumlah}</div>
                    <div class="sp-text-xs sp-text-gray">${item.satuan}</div>
                </td>
                <td class="sp-text-right">Rp ${formatCurrency(item.harga_satuan)}</td>
                <td class="sp-text-right sp-text-red">Rp ${formatCurrency(item.disc)}</td>
                <td class="sp-text-right sp-font-bold sp-text-teal">Rp ${formatCurrency(item.subtotal)}</td>
                <td class="sp-text-center">
                    <button type="button" class="sp-btn-icon" style="margin-right:8px; color:#3b82f6;" onclick="openItemModal(${idx})" title="Edit"><i class="fas fa-edit"></i></button>
                    <button type="button" class="sp-btn-icon" onclick="removeItem(${idx})" title="Hapus"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
    }
    $('#po-items-body').html(html);
}

// Escape HTML for hidden inputs
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
    
    $('#harga_vendor_display').val(formatCurrency(harga_vendor));
    $('#harga_vendor').val(harga_vendor);
    
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
    } else {
        $('#ppn_nilai').val(0);
    }
    
    let grand_total = subtotal_setelah_diskon + ppn_nilai;
    $('#lbl_grandtotal').text(formatCurrency(grand_total));
    $('#lbl_totalitem').text(orderItems.length);
}

function submitAs(status) {
    $('#action_status').val(status);
    
    // Basic validation
    if (!$('#nama_vendor').val()) {
        alert('Silakan pilih Supplier/Vendor.');
        $('#namasup_input').focus();
        return;
    }
    if (orderItems.length === 0) {
        alert('Minimal harus ada 1 barang pesanan.');
        return;
    }
    
    $('#form-po').submit();
}

$(document).ready(function() {
    // If there is existing POST data (due to validation error), repopulate items
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
                    'model' => $_POST['model'][$i],
                    'spec' => $_POST['spec'][$i],
                    'satuan' => $_POST['satuan'][$i],
                    'jumlah' => $qty,
                    'harga_satuan' => $price,
                    'disc' => $disc,
                    'subtotal' => max(0, ($qty * $price) - $disc)
                );
            }
        }
        $post_items_json = json_encode($p_items);
    }
    ?>
    let initItems = <?php echo $post_items_json; ?>;
    if(initItems.length > 0) {
        orderItems = initItems;
        renderItemsTable();
        calculateGlobal();
    }
    
    // Check PPN if there's a POST value
    let ppn_val = parseFloat($('#ppn_nilai').val()) || 0;
    if(ppn_val > 0) {
        $('#ppn_toggle').prop('checked', true);
    }
});

// Adjust sticky bottom bar on window resize if sidebar toggles
window.addEventListener('resize', () => {
    const bottomBar = document.querySelector('.sp-sticky-bar');
    if(bottomBar) {
        if(window.innerWidth < 768) {
            bottomBar.style.marginLeft = '0px';
        } else {
            bottomBar.style.marginLeft = '260px'; // Matching sidebar width
        }
    }
});
</script>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>
