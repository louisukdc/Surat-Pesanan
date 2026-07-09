<?php
// views/pembayaran.php
// Payment Request list and submission page

if (!defined('FRONT_CONTROLLER')) {
    header("Location: /sp_umum/home.php?page=pembayaran" . ($_SERVER['QUERY_STRING'] !== '' ? '&' . $_SERVER['QUERY_STRING'] : ''));
    exit;
}

$page_title = 'Menu Pembayaran';
$active_menu = 'pembayaran';

require_once dirname(__FILE__) . '/../includes/auth.php';

$error = '';
$success = '';

// Sanitize Rupiah input helper
function clean_rupiah($str) {
    if (!$str) return 0.0;
    $clean = preg_replace('/[^\d]/', '', $str);
    return (float)$clean;
}

// Handle Payment Request Submission (Staff only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_payment_request'])) {
    sp_require_role('staff');
    
    $po_id = isset($_POST['po_id']) ? (int)$_POST['po_id'] : 0;
    $tgl_pengajuan = isset($_POST['tgl_pengajuan']) ? $_POST['tgl_pengajuan'] : date('Y-m-d');
    $nominal = isset($_POST['nominal_diajukan']) ? clean_rupiah($_POST['nominal_diajukan']) : 0.0;
    
    if ($po_id <= 0) {
        $error = 'Pilih Surat Pesanan terlebih dahulu.';
    } elseif ($nominal <= 0) {
        $error = 'Nominal pembayaran yang diajukan harus lebih besar dari 0.';
    } else {
        $po = db_get_purchase_order_by_id($po_id);
        if (!$po || $po['status'] !== 'acc') {
            $error = 'Status Surat Pesanan tidak valid untuk pembayaran.';
        } else {
            $pr_id = db_create_payment_request($po_id, $tgl_pengajuan, $nominal, $_SESSION['user_id']);
            if ($pr_id !== false) {
                $success = 'Pengajuan pembayaran berhasil dikirim ke Direktur.';
            } else {
                $error = 'Gagal mengajukan pembayaran. Harap coba lagi.';
            }
        }
    }
}

// Fetch all payment requests
$payment_requests = db_get_payment_requests();

// Fetch POs eligible for payment
$eligible_pos = array();
if ($_SESSION['user_role'] === 'staff') {
    $all_acc_pos = db_get_purchase_orders('acc');
    foreach ($all_acc_pos as $po) {
        $items = db_get_purchase_order_items($po['id']);
        $has_receipts = false;
        foreach ($items as $it) {
            if ($it['jumlah_diterima'] > 0) {
                $has_receipts = true;
                break;
            }
        }
        
        $already_requested = false;
        foreach ($payment_requests as $pr) {
            if ($pr['po_id'] == $po['id'] && in_array($pr['status'], array('diajukan', 'acc'))) {
                $already_requested = true;
                break;
            }
        }

        if ($has_receipts && !$already_requested) {
            $eligible_pos[] = $po;
        }
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

<div class="bp-hero no-print" style="padding:0.5rem 1rem; margin-bottom:0.5rem;">
    <div class="bp-hero-badge"><i class="fas fa-file-invoice-dollar"></i> Menu Pembayaran</div>
    <h4 class="bp-hero-title" style="font-size:1rem; margin-bottom:0;">Pengajuan & Monitoring Bayar</h4>
</div>

<div class="row" style="flex: 1 1 auto; min-height: 0; margin-left:-0.25rem; margin-right:-0.25rem;">
    <!-- STAFF INTERFACE: Create Payment Request Form -->
    <?php if ($_SESSION['user_role'] === 'staff'): ?>
        <div class="col-md-4 mb-2 px-1">
            <div class="bp-panel bp-panel-teal" style="height: 100%; margin-bottom: 0; display: flex; flex-direction: column;">
                <div class="bp-panel-header" style="padding:0.4rem 0.8rem;">
                    <div><i class="fas fa-plus-circle mr-2"></i> Ajukan Pembayaran</div>
                </div>
                
                <div class="bp-panel-body" style="padding:0.6rem; flex: 1 1 auto; overflow-y: auto;">
                    <?php if (empty($eligible_pos)): ?>
                        <p class="text-muted font-italic text-center py-3">Tidak ada Surat Pesanan yang siap diajukan pembayaran. SP harus berstatus ACC dan sudah menerima barang (minimal sebagian).</p>
                    <?php else: ?>
                        <form action="home.php?page=pembayaran" method="POST" id="form-pay-request">
                            <input type="hidden" name="submit_payment_request" value="1">
                            
                            <div class="form-group mb-2">
                                <label for="po_id" class="bp-field-label">Pilih Surat Pesanan (SP) <span class="text-danger">*</span></label>
                                <select name="po_id" id="po_id" class="form-control form-control-sm bp-input" required>
                                    <option value="" data-total="0">-- Pilih SP --</option>
                                    <?php foreach ($eligible_pos as $po): ?>
                                        <option value="<?php echo $po['id']; ?>" data-total="<?php echo $po['total_setelah_diskon']; ?>">
                                            <?php echo htmlspecialchars($po['no_pesanan']); ?> (<?php echo htmlspecialchars($po['nama_vendor']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group mb-2">
                                <label for="tgl_pengajuan" class="bp-field-label">Tanggal Pengajuan <span class="text-danger">*</span></label>
                                <input type="date" name="tgl_pengajuan" id="tgl_pengajuan" class="form-control form-control-sm bp-input" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="form-group mb-2">
                                <label for="nominal_diajukan" class="bp-field-label">Nominal yang Diajukan <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light font-weight-bold" style="padding:0.2rem 0.5rem; font-size:0.75rem;">Rp</span>
                                    </div>
                                    <input type="text" name="nominal_diajukan" id="nominal_diajukan" class="form-control form-control-sm bp-input input-rupiah font-weight-bold text-right text-dark" placeholder="0" required>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size:0.65rem; line-height:1.2;">Default diisi dengan total harga setelah diskon dari SP terpilih.</small>
                            </div>
                            
                            <button type="submit" class="bp-btn-submit w-100 mt-3" style="padding:0.5rem; font-size:0.8rem;">
                                <i class="fas fa-paper-plane mr-1"></i> Kirim Pengajuan
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- LIST OF REQUESTS SECTION -->
    <div class="<?php echo $_SESSION['user_role'] === 'staff' ? 'col-md-8' : 'col-md-12'; ?> mb-2 px-1">
        <div class="bp-panel bp-panel-slate" style="height: 100%; margin-bottom: 0; display: flex; flex-direction: column;">
            <div class="bp-panel-header" style="padding:0.4rem 0.8rem;">
                <div><i class="fas fa-list-alt mr-2"></i> Daftar Pengajuan Pembayaran</div>
            </div>
            
            <div class="bp-panel-body" style="padding: 0.4rem; flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0;">
                <div class="table-responsive-sticky" style="flex: 1 1 auto; overflow-y: auto;">
                    <table class="table table-bordered mb-0 bp-items-table">
                        <thead>
                            <tr>
                                <th>No SP</th>
                                <th>Nama Vendor</th>
                                <th>Tanggal Pengajuan</th>
                                <th class="text-right">Nominal Diajukan</th>
                                <th>Diajukan Oleh</th>
                                <th class="text-center">Status Bayar</th>
                                <th class="text-center" style="width: 8.125rem;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payment_requests)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada pengajuan pembayaran.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($payment_requests as $pr): ?>
                                    <tr>
                                        <td class="font-weight-bold text-dark"><?php echo htmlspecialchars($pr['no_pesanan']); ?></td>
                                        <td><?php echo htmlspecialchars($pr['nama_vendor']); ?></td>
                                        <td><?php echo format_date($pr['tgl_pengajuan']); ?></td>
                                        <td class="text-right font-weight-bold text-primary"><?php echo format_rupiah($pr['nominal_diajukan']); ?></td>
                                        <td><?php echo htmlspecialchars($pr['pengaju_nama']); ?></td>
                                        <td class="text-center"><?php echo get_payment_badge($pr['status']); ?></td>
                                        <td class="text-center">
                                            <?php if ($_SESSION['user_role'] === 'direktur' && $pr['status'] === 'diajukan'): ?>
                                                <a href="home.php?page=pembayaran_detail&id=<?php echo $pr['id']; ?>" class="btn btn-sm btn-warning font-weight-bold py-1 px-3">
                                                    <i class="fas fa-gavel mr-1"></i> Review Bayar
                                                </a>
                                            <?php else: ?>
                                                <a href="home.php?page=pembayaran_detail&id=<?php echo $pr['id']; ?>" class="btn btn-sm btn-premium-secondary py-1 px-3">
                                                    <i class="fas fa-eye mr-1"></i> Detail
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const poSelect = document.getElementById('po_id');
    const nominalInput = document.getElementById('nominal_diajukan');
    
    if (poSelect && nominalInput) {
        poSelect.addEventListener('change', function () {
            const selectedOption = poSelect.options[poSelect.selectedIndex];
            const total = parseFloat(selectedOption.getAttribute('data-total')) || 0;
            
            if (total > 0) {
                nominalInput.value = total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            } else {
                nominalInput.value = '';
            }
        });
    }
});
</script>

<?php
require_once dirname(__FILE__) . '/../includes/footer.php';
?>
