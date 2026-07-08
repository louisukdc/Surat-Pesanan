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

$gudang_list = db_get_gudang();
$gudang_json = json_encode($gudang_list);

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
    max-width: 600px;
    overflow: hidden;
}
.sp-modal-header { padding: 16px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
.sp-modal-body { padding: 16px; }
.sp-modal-footer { padding: 16px; border-top: 1px solid #e5e7eb; text-align: right; }
.btn-close-modal { background: none; border: none; font-size: 1.2rem; cursor: pointer; }
</style>

<!-- STYLED BOX WRAPPER -->
<div style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 20px; margin: 20px; display: flex; flex-direction: column; flex-grow: 1; overflow: visible; border: 1px solid #e5e7eb;">

<!-- HERO BANNER -->
<div class="bp-hero">
    <div class="bp-hero-badge"><i class="fas fa-star"></i> Form Buat Surat Pesanan</div>
    <h4 class="bp-hero-title">Surat Pesanan Baru</h4>
    <p class="bp-hero-sub">Lengkapi seluruh informasi pesanan, rincian barang, dan metode pembayaran sebelum diajukan.</p>
</div>

<form action="home.php?page=buat_pesanan" method="POST" id="form-po" style="flex-grow: 1; display: flex; flex-direction: column; min-height: 0;" enctype="multipart/form-data">
    <input type="hidden" name="action_status" id="action_status" value="draft">
    <input type="hidden" name="ppn_nilai" id="ppn_nilai" value="<?php echo isset($_POST['ppn_nilai']) ? $_POST['ppn_nilai'] : 0; ?>">

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
                                    value="<?php echo isset($_POST['no_pesanan']) ? htmlspecialchars($_POST['no_pesanan']) : $next_po_num; ?>" required>
                                <small class="text-muted" style="font-size:0.7rem;">Auto-generate, bisa diedit</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label class="bp-field-label">No Permintaan</label>
                                <input type="text" name="no_permintaan" id="no_permintaan"
                                    class="form-control form-control-sm bp-input"
                                    placeholder="No surat permintaan..."
                                    value="<?php echo isset($_POST['no_permintaan']) ? htmlspecialchars($_POST['no_permintaan']) : ''; ?>">
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
                                <label class="bp-field-label">Unit / Bagian</label>
                                <input type="text" name="unit" id="unit"
                                    class="form-control form-control-sm bp-input"
                                    placeholder="Cari unit..." autocomplete="off" oninput="searchGudang(this.value)"
                                    value="<?php echo isset($_POST['unit']) ? htmlspecialchars($_POST['unit']) : ''; ?>">
                                <div id="gudang-suggestions" class="suggestions-box" style="display:none; position:absolute; top:100%; left:0;"></div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label class="bp-field-label">Lampiran (PDF)</label>
                                <input type="file" name="lampiran_pdf[]" id="lampiran_pdf"
                                    class="form-control form-control-sm bp-input" accept="application/pdf" multiple style="padding-bottom:28px;">
                                <input type="hidden" name="nama_lampiran_existing" value="<?php echo isset($_POST['nama_lampiran']) ? htmlspecialchars($_POST['nama_lampiran']) : ''; ?>">
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
                        <input type="hidden" name="nama_vendor" id="nama_vendor" value="<?php echo isset($_POST['nama_vendor']) ? htmlspecialchars($_POST['nama_vendor']) : ''; ?>">
                        <input type="text" id="namasup_input" class="form-control form-control-sm bp-input" placeholder="Ketik nama vendor..." autocomplete="off" onkeyup="searchSupplier(this.value)" value="<?php echo isset($_POST['nama_vendor']) ? htmlspecialchars($_POST['nama_vendor']) : ''; ?>" required>
                        <div id="supplier-suggestions" class="suggestions-box"></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label class="bp-field-label">No Penawaran</label>
                                <input type="text" name="no_tawar" id="no_tawar"
                                    class="form-control form-control-sm bp-input"
                                    placeholder="No surat penawaran..."
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
                    <div class="form-group mb-0">
                        <label class="bp-field-label">Catatan</label>
                        <textarea name="notein" id="notein"
                            class="form-control form-control-sm bp-input" rows="2"
                            placeholder="Catatan tambahan pesanan..."><?php echo isset($_POST['notein']) ? htmlspecialchars($_POST['notein']) : ''; ?></textarea>
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
            <button type="button" class="btn btn-sm btn-light font-weight-bold" style="border:1px solid #fbbf24; color:#d97706;" onclick="openItemModal()">
                <i class="fas fa-plus mr-1"></i> Tambah Baris Modal
            </button>
        </div>
        <div class="bp-panel-body" style="padding:0.4rem; flex: 1 1 auto; overflow-y: auto;">
            <div class="table-responsive-sticky" style="height: 100%;">
                <table class="table table-bordered mb-0 bp-items-table" id="po-items-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:2.8rem;">#</th>
                            <th style="min-width:9rem;">Nama Barang <span style="color:#fbbf24;">*</span></th>
                            <th style="min-width:6rem;">Merk/Model</th>
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

<!-- Modal Add Item -->
<div id="itemModal" class="sp-modal-overlay">
    <div class="sp-modal-content">
        <div class="sp-modal-header">
            <h5 class="mb-0 font-weight-bold" style="color:#1f2937;"><i class="fas fa-box-open text-primary"></i> Detail Barang</h5>
            <button type="button" class="btn-close-modal" onclick="closeItemModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="sp-modal-body">
            <input type="hidden" id="edit_index" value="-1">
            <div class="row">
                <div class="col-md-12 mb-2">
                    <label>Nama Barang *</label>
                    <input type="text" id="item_barang" class="form-control form-control-sm font-weight-bold">
                </div>
                <div class="col-md-12 mb-2">
                    <label>Spesifikasi</label>
                    <input type="text" id="item_spec" class="form-control form-control-sm">
                </div>
                <div class="col-md-6 mb-2">
                    <label>Merk</label>
                    <input type="text" id="item_merk" class="form-control form-control-sm">
                </div>
                <div class="col-md-6 mb-2">
                    <label>Model</label>
                    <input type="text" id="item_model" class="form-control form-control-sm">
                </div>
                <div class="col-md-4 mb-2">
                    <label>Kuantitas (Qty)</label>
                    <input type="number" id="item_qty" class="form-control form-control-sm text-center font-weight-bold" value="1" step="0.01" min="0.01" onkeyup="calculateItemTotal()" onchange="calculateItemTotal()">
                </div>
                <div class="col-md-4 mb-2">
                    <label>Satuan</label>
                    <select id="item_satuan" class="form-control form-control-sm">
                        <?php foreach($satuans as $st) echo '<option value="'.$st.'">'.$st.'</option>'; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label>Diskon Item (Rp)</label>
                    <input type="number" id="item_disc" class="form-control form-control-sm text-right text-danger" value="0" onkeyup="calculateItemTotal()" onchange="calculateItemTotal()">
                </div>
                <div class="col-md-12 mb-2">
                    <label>Harga Satuan</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                        <input type="number" id="item_harga" class="form-control form-control-sm text-right font-weight-bold" value="0" onkeyup="calculateItemTotal()" onchange="calculateItemTotal()">
                    </div>
                </div>
            </div>
            <div class="mt-3 p-2 rounded" style="background:#f0fdfa; border:1px solid #ccfbf1; display:flex; justify-content:space-between; align-items:center;">
                <span class="font-weight-bold text-success" style="font-size:0.85rem;">SUBTOTAL ITEM</span>
                <div class="font-weight-bold text-success" style="font-size:1.1rem;">Rp <span id="item_jumlah">0</span></div>
            </div>
        </div>
        <div class="sp-modal-footer">
            <button type="button" class="btn btn-sm btn-secondary" onclick="closeItemModal()">Batal</button>
            <button type="button" class="btn btn-sm btn-primary" onclick="addItem()"><i class="fas fa-check"></i> Simpan Item</button>
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
    $('#item_jumlah').text(formatCurrency(total));
}

function clearItemForm() {
    $('#item_barang, #item_merk, #item_model, #item_spec').val('');
    $('#item_qty').val(1);
    $('#item_satuan').val('pcs');
    $('#item_harga, #item_disc').val(0);
    $('#item_jumlah').text('0');
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
        html = '<tr><td colspan="8" class="text-center text-muted font-italic" style="padding:16px;">Belum ada barang. Klik Tambah Baris Modal.</td></tr>';
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
                    <input type="hidden" name="model[]" value="${escapeHtml(item.model)}">
                    <input type="hidden" name="jumlah[]" value="${item.jumlah}">
                    <input type="hidden" name="satuan[]" value="${escapeHtml(item.satuan)}">
                    <input type="hidden" name="harga_satuan[]" value="${item.harga_satuan}">
                    <input type="hidden" name="disc_item[]" value="${item.disc}">
                </td>
                <td>
                    <div class="small">${item.merk || '-'}</div>
                    <div class="small text-muted">${item.model || '-'}</div>
                </td>
                <td class="text-center align-middle">
                    <span class="font-weight-bold">${item.jumlah}</span><br>
                    <span class="small text-muted">${item.satuan}</span>
                </td>
                <td class="text-right align-middle">Rp ${formatCurrency(item.harga_satuan)}</td>
                <td class="text-right align-middle text-danger">Rp ${formatCurrency(item.disc)}</td>
                <td class="text-right align-middle font-weight-bold text-success">Rp ${formatCurrency(item.subtotal)}</td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-light text-primary" style="padding:2px 6px;" onclick="openItemModal(${idx})" title="Edit"><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn btn-sm btn-light text-danger" style="padding:2px 6px;" onclick="removeItem(${idx})" title="Hapus"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
    }
    $('#po-items-body').html(html);
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
}

function submitAs(status) {
    $('#action_status').val(status);
    
    if (!$('#nama_vendor').val()) {
        alert('Silakan pilih Supplier/Vendor.');
        $('#namasup_input').focus();
        return;
    }
    if (orderItems.length === 0) {
        alert('Minimal harus ada 1 barang pesanan.');
        return;
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

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>
