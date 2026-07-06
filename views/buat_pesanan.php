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

    $extra = array(
        'no_permintaan' => isset($_POST['no_permintaan']) ? trim($_POST['no_permintaan']) : '',
        'nama_lampiran' => isset($_POST['nama_lampiran']) ? trim($_POST['nama_lampiran']) : '',
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
                $_POST = array();
            } else {
                $error = 'Gagal menyimpan Surat Pesanan. Kemungkinan nomor pesanan sudah terpakai.';
            }
        }
    }
}

$next_po_num = db_generate_po_number();
$today = date('Y-m-d');
$suppliers = db_get_suppliers();

require_once dirname(__FILE__) . '/../includes/header.php';

$satuans = array('pcs','unit','lusin','kodi','rim','roll','box','set','kg','ltr','m','cm');
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

<!-- HERO BANNER -->
<div class="bp-hero">
    <div class="bp-hero-badge"><i class="fas fa-star"></i> Form Buat Surat Pesanan</div>
    <h4 class="bp-hero-title">Surat Pesanan Baru</h4>
    <p class="bp-hero-sub">Lengkapi seluruh informasi pesanan, rincian barang, dan metode pembayaran sebelum diajukan.</p>
</div>

<form action="home.php?page=buat_pesanan" method="POST" id="form-po" style="flex-grow: 1; display: flex; flex-direction: column; min-height: 0;">
    <input type="hidden" name="action_status" id="action_status" value="draft">
    <input type="hidden" name="ppn_nilai" id="ppn_nilai" value="0">

    <div class="row flex-shrink-0">

        <!-- ===== PANEL KIRI: IDENTITAS SP ===== -->
        <div class="col-lg-4 mb-2">
            <div class="bp-panel bp-panel-blue">
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
                            <div class="form-group mb-3">
                                <label class="bp-field-label">Unit / Bagian</label>
                                <input type="text" name="unit" id="unit"
                                    class="form-control form-control-sm bp-input"
                                    placeholder="Unit yang memesan..."
                                    value="<?php echo isset($_POST['unit']) ? htmlspecialchars($_POST['unit']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label class="bp-field-label">Nama Lampiran</label>
                                <input type="text" name="nama_lampiran" id="nama_lampiran"
                                    class="form-control form-control-sm bp-input"
                                    placeholder="Nama lampiran..."
                                    value="<?php echo isset($_POST['nama_lampiran']) ? htmlspecialchars($_POST['nama_lampiran']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== PANEL TENGAH: VENDOR & PENAWARAN ===== -->
        <div class="col-lg-4 mb-2">
            <div class="bp-panel bp-panel-violet">
                <div class="bp-panel-header">
                    <div class="bp-panel-icon"><i class="fas fa-store"></i></div>
                    Vendor &amp; Penawaran
                </div>
                <div class="bp-panel-body">
                    <div class="form-group mb-3">
                        <label class="bp-field-label">Nama Vendor <span class="req">*</span></label>
                        <select name="nama_vendor" id="nama_vendor"
                            class="form-control form-control-sm bp-input" required style="height:auto; padding:0.35rem 0.6rem;">
                            <option value="">-- Pilih Vendor --</option>
                            <?php foreach ($suppliers as $sup): ?>
                                <option value="<?php echo htmlspecialchars(trim($sup['NamaSupplier'])); ?>"
                                    <?php echo (isset($_POST['nama_vendor']) && $_POST['nama_vendor'] === trim($sup['NamaSupplier'])) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(trim($sup['KodeSupplier']) . ' — ' . trim($sup['NamaSupplier'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted" style="font-size:0.7rem;">Data dari master supplier (askes)</small>
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
                                placeholder="0" value="<?php echo isset($_POST['harga_vendor']) ? htmlspecialchars($_POST['harga_vendor']) : ''; ?>" readonly required>
                        </div>
                        <small class="text-muted" style="font-size:0.7rem;">Dihitung otomatis</small>
                    </div>

                    <!-- PPN -->
                    <div class="mb-2">
                        <label class="bp-field-label">PPN 11%</label>
                        <div class="d-flex align-items-center mb-1">
                            <label class="ppn-switch mb-0 mr-2" for="ppn_toggle">
                                <input type="checkbox" id="ppn_toggle" onchange="togglePPN(this)">
                                <span class="ppn-slider"></span>
                            </label>
                            <span id="ppn_label" class="text-muted" style="font-size:0.7rem;">Tanpa PPN</span>
                            <input type="hidden" name="ppn_aktif" id="ppn_aktif" value="0">
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
                        <label class="bp-field-label">Diskon Vendor</label>
                        <div class="input-group input-group-sm">
                            <select name="diskon_type" id="diskon_type" class="form-control form-control-sm bp-input" style="max-width:4rem; height:auto; padding:0.2rem 0.3rem; border-right:0; border-radius:0.35rem 0 0 0.35rem; font-size:0.7rem;">
                                <option value="rp" <?php echo (!isset($_POST['diskon_type']) || $_POST['diskon_type']==='rp') ? 'selected' : ''; ?>>Rp</option>
                                <option value="percent" <?php echo (isset($_POST['diskon_type']) && $_POST['diskon_type']==='percent') ? 'selected' : ''; ?>>%</option>
                            </select>
                            <input type="text" name="diskon_vendor" id="diskon_vendor"
                                class="form-control form-control-sm bp-input text-right"
                                placeholder="0"
                                value="<?php echo isset($_POST['diskon_vendor']) ? htmlspecialchars($_POST['diskon_vendor']) : ''; ?>">
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
            <button type="button" class="bp-add-row-btn" onclick="addPurchaseOrderItemRow()">
                <i class="fas fa-plus mr-1"></i> Tambah Baris
            </button>
        </div>
        <div class="bp-panel-body" style="padding:0.4rem; flex: 1 1 auto; overflow-y: auto;">
            <div class="table-responsive-sticky" style="height: 100%;">
                <table class="table table-bordered mb-0 bp-items-table" id="po-items-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:2.8rem;">#</th>
                            <th style="min-width:9rem;">Nama Barang <span style="color:#fbbf24;">*</span></th>
                            <th style="min-width:6rem;">Merk</th>
                            <th style="min-width:6rem;">Model</th>
                            <th style="min-width:7rem;">Spesifikasi</th>
                            <th class="text-center" style="width:5rem;">Qty <span style="color:#fbbf24;">*</span></th>
                            <th style="width:5.5rem;">Satuan</th>
                            <th class="text-right" style="width:9rem;">Harga Satuan <span style="color:#fbbf24;">*</span></th>
                            <th class="text-right" style="width:7.5rem;">Diskon Item</th>
                            <th class="text-right" style="width:8.5rem;">Subtotal</th>
                            <th class="text-center" style="width:3.5rem;">Del</th>
                        </tr>
                    </thead>
                    <tbody id="po-items-body">
                        <?php if (!empty($_POST['nama_barang'])): ?>
                            <?php for($i=0; $i<count($_POST['nama_barang']); $i++): ?>
                                <tr>
                                    <td class="text-center align-middle"><span class="row-number-cell row-number"><?php echo $i+1; ?></span></td>
                                    <td><input type="text" name="nama_barang[]" class="form-control form-control-sm" placeholder="Nama Barang" value="<?php echo htmlspecialchars($_POST['nama_barang'][$i]); ?>" required></td>
                                    <td><input type="text" name="merk[]" class="form-control form-control-sm" placeholder="Merk" value="<?php echo isset($_POST['merk'][$i]) ? htmlspecialchars($_POST['merk'][$i]) : ''; ?>"></td>
                                    <td><input type="text" name="model[]" class="form-control form-control-sm" placeholder="Model" value="<?php echo isset($_POST['model'][$i]) ? htmlspecialchars($_POST['model'][$i]) : ''; ?>"></td>
                                    <td><input type="text" name="spec[]" class="form-control form-control-sm" placeholder="Spesifikasi" value="<?php echo isset($_POST['spec'][$i]) ? htmlspecialchars($_POST['spec'][$i]) : ''; ?>"></td>
                                    <td><input type="number" name="jumlah[]" class="form-control form-control-sm text-center row-qty" min="0.01" step="0.01" value="<?php echo (float)$_POST['jumlah'][$i]; ?>" oninput="calculateRowTotal(this)" required></td>
                                    <td>
                                        <select name="satuan[]" class="form-control form-control-sm row-satuan">
                                            <?php foreach($satuans as $st): $sel_s = (isset($_POST['satuan'][$i]) && $_POST['satuan'][$i]===$st) ? 'selected' : ''; echo '<option value="'.$st.'" '.$sel_s.'>'.$st.'</option>'; endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text bg-light border-0" style="padding-right:3px; font-size:0.72rem;">Rp</span></div>
                                            <input type="text" name="harga_satuan[]" class="form-control form-control-sm input-rupiah text-right row-price" placeholder="0" value="<?php echo isset($_POST['harga_satuan'][$i]) ? htmlspecialchars($_POST['harga_satuan'][$i]) : ''; ?>" oninput="calculateRowTotal(this)" required>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text bg-light border-0" style="padding-right:3px; font-size:0.72rem;">Rp</span></div>
                                            <input type="text" name="disc_item[]" class="form-control form-control-sm input-rupiah text-right row-disc" placeholder="0" value="<?php echo isset($_POST['disc_item'][$i]) ? htmlspecialchars($_POST['disc_item'][$i]) : ''; ?>" oninput="calculateRowTotal(this)">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm text-right row-total input-subtotal" value="0" readonly>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn-remove-bp" onclick="removePurchaseOrderItemRow(this)" title="Hapus baris"><i class="fas fa-times"></i></button>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        <?php else: ?>
                            <tr>
                                <td class="text-center align-middle"><span class="row-number-cell row-number">1</span></td>
                                <td><input type="text" name="nama_barang[]" class="form-control form-control-sm" placeholder="Nama Barang" required></td>
                                <td><input type="text" name="merk[]" class="form-control form-control-sm" placeholder="Merk"></td>
                                <td><input type="text" name="model[]" class="form-control form-control-sm" placeholder="Model"></td>
                                <td><input type="text" name="spec[]" class="form-control form-control-sm" placeholder="Spesifikasi"></td>
                                <td><input type="number" name="jumlah[]" class="form-control form-control-sm text-center row-qty" min="0.01" step="0.01" value="1" oninput="calculateRowTotal(this)" required></td>
                                <td>
                                    <select name="satuan[]" class="form-control form-control-sm row-satuan">
                                        <?php foreach($satuans as $st) echo '<option value="'.$st.'">'.$st.'</option>'; ?>
                                    </select>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text bg-light border-0" style="padding-right:3px; font-size:0.72rem;">Rp</span></div>
                                        <input type="text" name="harga_satuan[]" class="form-control form-control-sm input-rupiah text-right row-price" placeholder="0" oninput="calculateRowTotal(this)" required>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text bg-light border-0" style="padding-right:3px; font-size:0.72rem;">Rp</span></div>
                                        <input type="text" name="disc_item[]" class="form-control form-control-sm input-rupiah text-right row-disc" placeholder="0" oninput="calculateRowTotal(this)">
                                    </div>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm text-right row-total input-subtotal" value="0" readonly>
                                </td>
                                <td class="text-center align-middle">
                                    <button type="button" class="btn-remove-bp" onclick="removePurchaseOrderItemRow(this)" title="Hapus baris"><i class="fas fa-times"></i></button>
                                </td>
                            </tr>
                        <?php endif; ?>
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
            <button type="submit" class="bp-btn-draft"
                onclick="document.getElementById('action_status').value='draft';">
                <i class="fas fa-save mr-1"></i> Simpan Draft
            </button>
            <button type="submit" class="bp-btn-submit"
                onclick="document.getElementById('action_status').value='diajukan';">
                <i class="fas fa-paper-plane mr-1"></i> Ajukan ke Direktur
            </button>
        </div>
    </div>

</form>

<script>
/* Override addPurchaseOrderItemRow agar pakai template baru */
function addPurchaseOrderItemRow() {
    var tbody = document.getElementById('po-items-body');
    if (!tbody) return;
    var rowCount = tbody.rows.length;
    var satOpts = ['pcs','unit','lusin','kodi','rim','roll','box','set','kg','ltr','m','cm'];
    var satHtml = '';
    for (var s = 0; s < satOpts.length; s++) {
        satHtml += '<option value="' + satOpts[s] + '">' + satOpts[s] + '</option>';
    }
    var newRow = document.createElement('tr');
    newRow.innerHTML =
        '<td class="text-center align-middle"><span class="row-number-cell row-number">' + (rowCount + 1) + '</span></td>' +
        '<td><input type="text" name="nama_barang[]" class="form-control form-control-sm" placeholder="Nama Barang" required></td>' +
        '<td><input type="text" name="merk[]" class="form-control form-control-sm" placeholder="Merk"></td>' +
        '<td><input type="text" name="model[]" class="form-control form-control-sm" placeholder="Model"></td>' +
        '<td><input type="text" name="spec[]" class="form-control form-control-sm" placeholder="Spesifikasi"></td>' +
        '<td><input type="number" name="jumlah[]" class="form-control form-control-sm text-center row-qty" min="0.01" step="0.01" value="1" oninput="calculateRowTotal(this)" required></td>' +
        '<td><select name="satuan[]" class="form-control form-control-sm row-satuan">' + satHtml + '</select></td>' +
        '<td><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text bg-light border-0" style="padding-right:3px;font-size:0.72rem;">Rp</span></div><input type="text" name="harga_satuan[]" class="form-control form-control-sm input-rupiah text-right row-price" placeholder="0" oninput="calculateRowTotal(this)" required></div></td>' +
        '<td><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text bg-light border-0" style="padding-right:3px;font-size:0.72rem;">Rp</span></div><input type="text" name="disc_item[]" class="form-control form-control-sm input-rupiah text-right row-disc" placeholder="0" oninput="calculateRowTotal(this)"></div></td>' +
        '<td><input type="text" class="form-control form-control-sm text-right row-total input-subtotal" value="0" readonly></td>' +
        '<td class="text-center align-middle"><button type="button" class="btn-remove-bp" onclick="removePurchaseOrderItemRow(this)" title="Hapus baris"><i class="fas fa-times"></i></button></td>';
    tbody.appendChild(newRow);
    renumberPurchaseOrderRows();
}

/* Override renumber agar update row-number-cell */
function renumberPurchaseOrderRows() {
    var tbody = document.getElementById('po-items-body');
    if (!tbody) return;
    var rows = tbody.rows;
    for (var i = 0; i < rows.length; i++) {
        var cell = rows[i].querySelector('.row-number');
        if (cell) cell.textContent = i + 1;
    }
}
</script>

<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>
