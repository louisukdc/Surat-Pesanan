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

<!-- ========================================================== -->
<!-- DETAIL VIEW SECTION                                         -->
<!-- ========================================================== -->
<?php if ($selected_po):
    /* â”€â”€ Pre-compute variables yang dipakai berulang â”€â”€ */
    $sp_no_sp        = isset($selected_po['no_sp'])            ? htmlspecialchars((string)$selected_po['no_sp'])          : '-';
    $sp_status       = isset($selected_po['status'])           ? $selected_po['status']                                   : '';
    $sp_tgl_sp       = (isset($selected_po['tgl_sp']) && $selected_po['tgl_sp'] != '' && $selected_po['tgl_sp'] != '0000-00-00') ? format_date($selected_po['tgl_sp']) : '-';
    $sp_vendor       = isset($selected_po['nama_vendor'])      ? htmlspecialchars((string)$selected_po['nama_vendor'])    : '-';
    
    // Fetch Alamat1 and Kota1 from m_supplier
    $sp_alamat_vendor = '';
    $sp_kota_vendor = '';
    if (isset($GLOBALS['askes_conn']) && $sp_vendor !== '-') {
        $vendor_esc = mysqli_real_escape_string($GLOBALS['askes_conn'], $selected_po['nama_vendor']);
        $res_sup = mysqli_query($GLOBALS['askes_conn'], "SELECT Alamat1, Kota1 FROM m_supplier WHERE NamaSupplier = '$vendor_esc' LIMIT 1");
        if ($res_sup && $rsup = mysqli_fetch_assoc($res_sup)) {
            $sp_alamat_vendor = htmlspecialchars($rsup['Alamat1']);
            $sp_kota_vendor = htmlspecialchars($rsup['Kota1']);
        }
    }

    $sp_pembuat      = isset($selected_po['pembuat_nama'])     ? htmlspecialchars((string)$selected_po['pembuat_nama'])   : '-';
    $sp_no_permintaan= (isset($selected_po['no_permintaan'])   && $selected_po['no_permintaan'] !== '') ? htmlspecialchars((string)$selected_po['no_permintaan']) : '-';
    $sp_unit         = (isset($selected_po['unit'])            && $selected_po['unit'] !== '')            ? htmlspecialchars((string)$selected_po['unit'])            : '-';
    $sp_no_tawar     = (isset($selected_po['no_tawar'])        && $selected_po['no_tawar'] !== '')        ? htmlspecialchars((string)$selected_po['no_tawar'])        : '-';
    $sp_tgl_tawar    = (isset($selected_po['tgl_tawar'])       && $selected_po['tgl_tawar'] !== '' && $selected_po['tgl_tawar'] != '0000-00-00' && $selected_po['tgl_tawar'] != '1900-01-01') ? format_date($selected_po['tgl_tawar']) : '-';
    $sp_tglkirim     = (isset($selected_po['tglkirim'])        && $selected_po['tglkirim'] !== '' && $selected_po['tglkirim'] != '0000-00-00' && $selected_po['tglkirim'] != '1900-01-01') ? format_date($selected_po['tglkirim']) : '-';
    $sp_pembayaran   = (isset($selected_po['pembayaran'])      && $selected_po['pembayaran'] !== '')      ? htmlspecialchars((string)$selected_po['pembayaran'])      : '-';
    $sp_pembayaran1  = (isset($selected_po['pembayaran1'])     && $selected_po['pembayaran1'] !== '')     ? htmlspecialchars((string)$selected_po['pembayaran1'])     : '-';
    $sp_notein       = (isset($selected_po['notein'])          && $selected_po['notein'] !== '')          ? htmlspecialchars((string)$selected_po['notein'])          : '-';
    $sp_noteout      = (isset($selected_po['noteout'])         && $selected_po['noteout'] !== '')         ? nl2br(htmlspecialchars((string)$selected_po['noteout']))  : '-';
    $sp_ppn_pct      = (isset($selected_po['ppn'])             && (float)$selected_po['ppn'] > 0)        ? (float)$selected_po['ppn']                                : 0;
    $sp_harga_vendor     = isset($selected_po['harga_vendor'])         ? (float)$selected_po['harga_vendor']         : 0;
    $sp_diskon_vendor    = isset($selected_po['diskon_vendor'])        ? (float)$selected_po['diskon_vendor']        : 0;
    $sp_total_net        = isset($selected_po['total_setelah_diskon']) ? (float)$selected_po['total_setelah_diskon'] : 0;
    $sp_ppn_nominal      = ($sp_ppn_pct > 0) ? ($sp_total_net * $sp_ppn_pct / 100) : (isset($selected_po['ppn_nominal']) ? (float)$selected_po['ppn_nominal'] : 0);
    $sp_grand_total      = isset($selected_po['grand_total'])          ? (float)$selected_po['grand_total']          : ($sp_total_net + $sp_ppn_nominal);

    /* â”€â”€ Status label & colour â”€â”€ */
    $status_map = array(
        'draft'    => array('label' => 'Draft',           'color' => '#64748b', 'bg' => '#f1f5f9', 'icon' => 'fa-pencil-alt'),
        'diajukan' => array('label' => 'Menunggu ACC',    'color' => '#b45309', 'bg' => '#fef3c7', 'icon' => 'fa-clock'),
        'direview' => array('label' => 'Sedang Direview', 'color' => '#1d4ed8', 'bg' => '#dbeafe', 'icon' => 'fa-search'),
        'acc'      => array('label' => 'Disetujui (ACC)', 'color' => '#065f46', 'bg' => '#d1fae5', 'icon' => 'fa-check-circle'),
        'ditolak'  => array('label' => 'Ditolak',         'color' => '#991b1b', 'bg' => '#fee2e2', 'icon' => 'fa-times-circle'),
    );
    $st = isset($status_map[$sp_status]) ? $status_map[$sp_status] : array('label' => strtoupper($sp_status), 'color' => '#374151', 'bg' => '#f3f4f6', 'icon' => 'fa-circle');

    /* â”€â”€ Progress tracker step states â”€â”€ */
    $percent = 0;
    $step2_cls = ''; $step3_cls = ''; $step4_cls = '';
    if ($sp_status === 'draft')    { $percent = 25;  $step2_cls = 'active'; }
    elseif ($sp_status === 'diajukan') { $percent = 50;  $step2_cls = 'completed'; $step3_cls = 'active'; }
    elseif ($sp_status === 'direview'){ $percent = 75;  $step2_cls = 'completed'; $step3_cls = 'completed'; $step4_cls = 'active'; }
    elseif ($sp_status === 'acc')  { $percent = 100; $step2_cls = 'completed'; $step3_cls = 'completed'; $step4_cls = 'completed active'; }
    elseif ($sp_status === 'ditolak'){ $percent = 100; $step2_cls = 'completed'; $step3_cls = 'completed'; $step4_cls = 'active'; }

    /* â”€â”€ Print: pembayaran + catatan direktur â”€â”€ */
    $dir_note = '';
    if (!empty($selected_po_logs)) {
        foreach ($selected_po_logs as $lg) {
            if ($lg['status'] === 'acc' && $lg['user_role'] === 'direktur' && !empty(trim($lg['catatan']))) {
                $dir_note = trim($lg['catatan']);
                break;
            }
        }
    }
    $bayar_parts = array();
    if (!empty($selected_po['pembayaran']))  { $bayar_parts[] = (string)$selected_po['pembayaran']; }
    if (!empty($selected_po['pembayaran1'])) { $bayar_parts[] = (string)$selected_po['pembayaran1']; }
    $bayar_str_print = implode(' + ', $bayar_parts);
    if ($dir_note !== '') { $bayar_str_print .= ($bayar_str_print !== '' ? ' â€” ' : '') . $dir_note; }
    if (trim($bayar_str_print) === '') { $bayar_str_print = '-'; }

    /* â”€â”€ Print: column visibility â”€â”€ */
    $has_model = false; $has_merk = false; $has_spec = false; $has_disc = false;
    foreach ($selected_po_items as $itm2) {
        if (trim((string)$itm2['model']) !== '')       $has_model = true;
        if (trim((string)$itm2['merk'])  !== '')       $has_merk  = true;
        if (trim((string)$itm2['spec'])  !== '')       $has_spec  = true;
        if ((float)$itm2['diskon_item']  > 0)          $has_disc  = true;
    }

    $p_subtotal = $sp_total_net;
    $p_ppn      = $sp_ppn_nominal;
    $p_gtotal   = $sp_grand_total;
    $is_direktur_sign = ($p_gtotal >= 5000000);
    $acc_sp_name  = $is_direktur_sign ? '( Direktur )' : '( Pembelian )';
    $acc_sp_title = $is_direktur_sign ? 'Direktur Utama' : 'Bagian Pembelian';
    $tgl_sp_formatted_print = isset($selected_po['tgl_sp']) ? format_date($selected_po['tgl_sp']) : format_date(date('Y-m-d'));
?>

<style>
/* â”€â”€ Detail SP Styles â”€â”€ */
.spd-wrap { padding: 0 0.25rem; }
.spd-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.65rem 1rem;
    margin-bottom: 0.6rem;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
    border-left: 4px solid #3b82f6;
}
.spd-hero-left { display: flex; align-items: center; gap: 0.75rem; }
.spd-hero-badge {
    display: inline-flex; align-items: center; gap: 0.3rem;
    background: #dbeafe; color: #1d4ed8;
    font-size: 0.68rem; font-weight: 700; letter-spacing: 0.4px;
    padding: 0.2rem 0.55rem; border-radius: 20px; text-transform: uppercase;
}
.spd-hero-no {
    font-size: 1.05rem; font-weight: 800; color: #0f172a; letter-spacing: -0.3px;
    margin: 0;
}
.spd-hero-meta { font-size: 0.72rem; color: #64748b; margin: 0; margin-top: 1px; }
.spd-status-pill {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.28rem 0.75rem; border-radius: 20px;
    font-size: 0.72rem; font-weight: 700;
}
.spd-hero-actions { display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap; }
.spd-btn {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.3rem 0.7rem; border-radius: 6px;
    font-size: 0.75rem; font-weight: 600; text-decoration: none;
    border: 1px solid transparent; cursor: pointer; transition: all 0.15s ease;
    white-space: nowrap;
}
.spd-btn-back  { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
.spd-btn-back:hover  { background: #e2e8f0; color: #1e293b; text-decoration: none; }
.spd-btn-edit  { background: #fef3c7; color: #92400e; border-color: #fde68a; }
.spd-btn-edit:hover  { background: #fde68a; color: #78350f; text-decoration: none; }
.spd-btn-print { background: #ede9fe; color: #5b21b6; border-color: #c4b5fd; }
.spd-btn-print:hover { background: #c4b5fd; color: #3b0764; }

/* Progress tracker */
.spd-progress-wrap {
    background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    padding: 0.6rem 1rem 0.5rem; margin-bottom: 0.6rem;
}
.spd-progress-title { font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; }

/* Info Cards Grid */
.spd-cards-grid {
    display: -webkit-box; display: -ms-flexbox; display: flex;
    -ms-flex-wrap: wrap; flex-wrap: wrap;
    gap: 0.5rem; margin-bottom: 0.6rem;
}
.spd-card {
    background: #fff; border-radius: 10px; border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0,0,0,0.07);
    -webkit-box-flex: 1; -ms-flex: 1 1 280px; flex: 1 1 280px;
    min-width: 0; overflow: hidden;
}
.spd-card-header {
    display: flex; align-items: center; gap: 0.45rem;
    padding: 0.45rem 0.8rem;
    font-size: 0.72rem; font-weight: 700; letter-spacing: 0.3px;
    text-transform: uppercase; border-bottom: 1px solid #f0f0f0;
}
.spd-card-header.hdr-blue  { background: #eff6ff; color: #1d4ed8; }
.spd-card-header.hdr-teal  { background: #f0fdfa; color: #0f766e; }
.spd-card-header.hdr-amber { background: #fffbeb; color: #b45309; }
.spd-card-header.hdr-slate { background: #f8fafc; color: #475569; }
.spd-card-body { padding: 0.5rem 0.8rem; }
.spd-field { display: flex; align-items: flex-start; padding: 0.22rem 0; border-bottom: 1px solid #f5f5f5; }
.spd-field:last-child { border-bottom: none; }
.spd-field-label {
    width: 9rem; min-width: 9rem;
    font-size: 0.7rem; color: #94a3b8; font-weight: 600;
    padding-top: 0.05rem; padding-right: 0.5rem; line-height: 1.4;
    flex-shrink: 0;
}
.spd-field-value {
    font-size: 0.78rem; color: #1e293b; font-weight: 500;
    word-break: break-word; line-height: 1.4;
}
.spd-field-value.bold { font-weight: 700; }
.spd-field-value.danger { color: #dc2626; }
.spd-field-value.success { color: #059669; }
.spd-field-value.muted { color: #94a3b8; font-style: italic; }
.spd-divider { border: none; border-top: 1px dashed #e2e8f0; margin: 0.3rem 0; }

/* Items table */
.spd-items-wrap {
    background: #fff; border-radius: 10px; border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0,0,0,0.07);
    margin-bottom: 0.6rem; overflow: hidden;
}
.spd-items-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.spd-items-table { width: 100%; border-collapse: collapse; font-size: 0.75rem; }
.spd-items-table thead tr th {
    background: #f8fafc; color: #475569; font-weight: 700;
    padding: 0.45rem 0.6rem; border-bottom: 2px solid #e5e7eb;
    white-space: nowrap; text-align: left;
}
.spd-items-table tbody tr td {
    padding: 0.4rem 0.6rem; border-bottom: 1px solid #f0f0f0;
    color: #1e293b; vertical-align: middle;
}
.spd-items-table tbody tr:hover td { background: #fafbfc; }
.spd-items-table tbody tr:last-child td { border-bottom: none; }
.spd-items-table .td-no { text-align: center; color: #94a3b8; font-weight: 700; width: 2rem; }
.spd-items-table .td-barang { font-weight: 700; }
.spd-items-table .td-center { text-align: center; }
.spd-items-table .td-right { text-align: right; }
.spd-items-table .td-disc { text-align: right; color: #dc2626; }
.spd-items-table .td-total { text-align: right; font-weight: 700; color: #059669; }

/* Summary row in items footer */
.spd-summary-row {
    display: flex; justify-content: flex-end;
    padding: 0.5rem 0.8rem;
    border-top: 2px solid #e5e7eb;
    background: #f8fafc;
}
.spd-summary-inner { text-align: right; }
.spd-summary-line { display: flex; justify-content: space-between; gap: 2rem; font-size: 0.78rem; padding: 0.12rem 0; color: #475569; }
.spd-summary-line .slbl { color: #94a3b8; }
.spd-summary-line .sval { font-weight: 600; color: #1e293b; }
.spd-summary-line.total-line { font-size: 0.85rem; border-top: 1px solid #cbd5e1; padding-top: 0.25rem; margin-top: 0.15rem; }
.spd-summary-line.total-line .slbl { color: #1d4ed8; font-weight: 700; }
.spd-summary-line.total-line .sval { color: #1d4ed8; font-weight: 800; }

/* Bottom panels row */
.spd-bottom-row {
    display: -webkit-box; display: -ms-flexbox; display: flex;
    -ms-flex-wrap: wrap; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.6rem;
}
.spd-bottom-panel {
    -webkit-box-flex: 1; -ms-flex: 1 1 260px; flex: 1 1 260px;
    background: #fff; border-radius: 10px; border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0,0,0,0.07); overflow: hidden;
}

/* Audit trail timeline */
.spd-timeline { list-style: none; padding: 0; margin: 0; }
.spd-timeline li {
    position: relative; padding: 0.45rem 0.5rem 0.45rem 2rem;
    border-bottom: 1px solid #f0f0f0; font-size: 0.73rem;
}
.spd-timeline li:last-child { border-bottom: none; }
.spd-timeline li::before {
    content: '';
    position: absolute; left: 0.6rem; top: 0.7rem;
    width: 8px; height: 8px;
    border-radius: 50%; background: #3b82f6;
    border: 2px solid #bfdbfe;
}
.spd-timeline li.tl-acc::before  { background: #10b981; border-color: #a7f3d0; }
.spd-timeline li.tl-tolak::before{ background: #ef4444; border-color: #fca5a5; }
.tl-user   { font-weight: 700; color: #1e293b; }
.tl-role   { font-size: 0.65rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; margin-left: 0.3rem; }
.tl-date   { float: right; color: #94a3b8; font-size: 0.66rem; }
.tl-note   { margin-top: 0.2rem; padding: 0.2rem 0.4rem; background: #f8fafc; border-left: 2px solid #e2e8f0; border-radius: 0 4px 4px 0; color: #64748b; font-style: italic; }

/* Attachment buttons */
.spd-attach-btn {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.3rem 0.7rem; border-radius: 6px; font-size: 0.73rem;
    font-weight: 600; border: 1px solid #bfdbfe; color: #1d4ed8;
    background: #eff6ff; text-decoration: none; margin: 0.2rem 0.2rem 0.2rem 0;
    transition: background 0.15s;
}
.spd-attach-btn:hover { background: #dbeafe; text-decoration: none; }

/* Approval action bar */
.spd-approval-bar {
    background: linear-gradient(to right, #ffffff, #f8fafc);
    border-radius: 10px; border: 1px solid #e2e8f0;
    border-left: 4px solid #3b82f6;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    overflow: hidden; margin-bottom: 0.6rem;
}
.spd-approval-bar-header {
    display: flex; align-items: center; gap: 0.6rem;
    padding: 0.6rem 0.9rem; border-bottom: 1px solid #e2e8f0;
}
.spd-approval-icon {
    width: 2rem; height: 2rem; border-radius: 50%;
    background: #dbeafe; color: #1d4ed8;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem; flex-shrink: 0;
}
.spd-approval-body { padding: 0.75rem 0.9rem; }

@media print {
    .no-print { display: none !important; }
    .d-print-block { display: block !important; }
}
@media (max-width: 640px) {
    .spd-hero { flex-direction: column; align-items: flex-start; }
    .spd-field-label { width: 7.5rem; min-width: 7.5rem; }
    .spd-card { -ms-flex: 1 1 100%; flex: 1 1 100%; }
    .spd-bottom-panel { -ms-flex: 1 1 100%; flex: 1 1 100%; }
}
</style>

<!-- â”€â”€ SCREEN VIEW (no-print) â”€â”€ -->
<div class="spd-wrap no-print">

    <!-- 1. HERO HEADER -->
    <div class="spd-hero">
        <div class="spd-hero-left">
            <div>
                <div class="spd-hero-badge"><i class="fas fa-file-contract"></i> Detail Surat Pesanan</div>
                <h4 class="spd-hero-no mt-1"><?php echo $sp_no_sp; ?></h4>
                <p class="spd-hero-meta">
                    <i class="fas fa-calendar-alt mr-1"></i><?php echo $sp_tgl_sp; ?>
                    &nbsp;&bull;&nbsp;
                    <i class="fas fa-building mr-1"></i><?php echo $sp_vendor; ?>
                    &nbsp;&bull;&nbsp;
                    <i class="fas fa-user mr-1"></i><?php echo $sp_pembuat; ?>
                </p>
            </div>
            <span class="spd-status-pill" style="background:<?php echo $st['bg']; ?>;color:<?php echo $st['color']; ?>;">
                <i class="fas <?php echo $st['icon']; ?>"></i>
                <?php echo $st['label']; ?>
            </span>
        </div>
        <div class="spd-hero-actions">
            <?php if (in_array($sp_status, array('draft', 'ditolak', 'diajukan'))): ?>
                <a href="home.php?page=buat_pesanan&edit_id=<?php echo $selected_po['id']; ?>" class="spd-btn spd-btn-edit">
                    <i class="fas fa-edit"></i> Edit
                </a>
            <?php endif; ?>
            <a href="cetak_sp.php?id=<?php echo $selected_po['id']; ?>" target="_blank" class="spd-btn spd-btn-print">
                <i class="fas fa-print"></i> Preview &amp; Cetak
            </a>
            <a href="home.php?page=monitoring" class="spd-btn spd-btn-back">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- 2. PROGRESS TRACKER -->
    <div class="spd-progress-wrap">
        <div class="spd-progress-title"><i class="fas fa-map-signs mr-1"></i> Progres Persetujuan</div>
        <div class="progress-tracker" style="margin-bottom:0;">
            <div class="progress-tracker-line" style="width:<?php echo $percent; ?>%;"></div>
            <div class="progress-step completed">
                <div class="progress-dot">1</div>
                <span class="progress-step-label">Draft SP</span>
            </div>
            <div class="progress-step <?php echo $step2_cls; ?>">
                <div class="progress-dot">2</div>
                <span class="progress-step-label">Diajukan</span>
            </div>
            <div class="progress-step <?php echo $step3_cls; ?>">
                <div class="progress-dot">3</div>
                <span class="progress-step-label">Direview</span>
            </div>
            <div class="progress-step <?php echo $step4_cls; ?>">
                <div class="progress-dot">
                    <?php if ($sp_status === 'ditolak'): ?>
                        <i class="fas fa-times"></i>
                    <?php else: ?>4<?php endif; ?>
                </div>
                <span class="progress-step-label <?php echo ($sp_status === 'ditolak' ? 'text-danger' : ''); ?>">
                    <?php echo ($sp_status === 'ditolak' ? 'Ditolak' : 'ACC / Selesai'); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- 3. INFO CARDS GRID -->
    <div class="spd-cards-grid">

        <!-- Card: Informasi Umum -->
        <div class="spd-card">
            <div class="spd-card-header hdr-blue">
                <i class="fas fa-info-circle"></i> Informasi Umum
            </div>
            <div class="spd-card-body">
                <div class="spd-field">
                    <span class="spd-field-label">Nomor SP</span>
                    <span class="spd-field-value bold"><?php echo $sp_no_sp; ?></span>
                </div>
                <div class="spd-field">
                    <span class="spd-field-label">Tanggal SP</span>
                    <span class="spd-field-value"><?php echo $sp_tgl_sp; ?></span>
                </div>
                <div class="spd-field">
                    <span class="spd-field-label">No. Permintaan</span>
                    <span class="spd-field-value <?php echo ($sp_no_permintaan === '-' ? 'muted' : ''); ?>"><?php echo $sp_no_permintaan; ?></span>
                </div>
                <div class="spd-field">
                    <span class="spd-field-label">Unit / Divisi</span>
                    <span class="spd-field-value <?php echo ($sp_unit === '-' ? 'muted' : ''); ?>"><?php echo $sp_unit; ?></span>
                </div>
                <div class="spd-field">
                    <span class="spd-field-label">Dibuat Oleh</span>
                    <span class="spd-field-value"><?php echo $sp_pembuat; ?></span>
                </div>
                <div class="spd-field">
                    <span class="spd-field-label">Status</span>
                    <span class="spd-field-value">
                        <span class="spd-status-pill" style="background:<?php echo $st['bg']; ?>;color:<?php echo $st['color']; ?>;padding:0.15rem 0.5rem;font-size:0.68rem;">
                            <i class="fas <?php echo $st['icon']; ?>"></i> <?php echo $st['label']; ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Card: Data Vendor & Penawaran -->
        <div class="spd-card">
            <div class="spd-card-header hdr-teal">
                <i class="fas fa-building"></i> Data Vendor &amp; Penawaran
            </div>
            <div class="spd-card-body">
                <div class="spd-field">
                    <span class="spd-field-label">Nama Vendor</span>
                    <span class="spd-field-value bold"><?php echo $sp_vendor; ?></span>
                </div>
                <div class="spd-field">
                    <span class="spd-field-label">No. Penawaran</span>
                    <span class="spd-field-value <?php echo ($sp_no_tawar === '-' ? 'muted' : ''); ?>"><?php echo $sp_no_tawar; ?></span>
                </div>
                <div class="spd-field">
                    <span class="spd-field-label">Tgl. Penawaran</span>
                    <span class="spd-field-value <?php echo ($sp_tgl_tawar === '-' ? 'muted' : ''); ?>"><?php echo $sp_tgl_tawar; ?></span>
                </div>
                <div class="spd-field">
                    <span class="spd-field-label">Tgl. Kirim Harap</span>
                    <span class="spd-field-value <?php echo ($sp_tglkirim === '-' ? 'muted' : ''); ?>"><?php echo $sp_tglkirim; ?></span>
                </div>
                <div class="spd-field">
                    <span class="spd-field-label">Cara Bayar 1</span>
                    <span class="spd-field-value <?php echo ($sp_pembayaran === '-' ? 'muted' : ''); ?>"><?php echo $sp_pembayaran; ?></span>
                </div>
                <div class="spd-field">
                    <span class="spd-field-label">Cara Bayar 2</span>
                    <span class="spd-field-value <?php echo ($sp_pembayaran1 === '-' ? 'muted' : ''); ?>"><?php echo $sp_pembayaran1; ?></span>
                </div>
            </div>
        </div>

        <!-- Card: Ringkasan Biaya -->
        <div class="spd-card">
            <div class="spd-card-header hdr-amber">
                <i class="fas fa-calculator"></i> Ringkasan Biaya
            </div>
            <div class="spd-card-body">
                <div class="spd-field">
                    <span class="spd-field-label">Harga Vendor</span>
                    <span class="spd-field-value"><?php echo format_rupiah($sp_harga_vendor); ?></span>
                </div>
                <div class="spd-field">
                    <span class="spd-field-label">Potongan Diskon</span>
                    <span class="spd-field-value danger">
                        <?php echo ($sp_diskon_vendor > 0 ? '- ' . format_rupiah($sp_diskon_vendor) : '-'); ?>
                    </span>
                </div>
                <div class="spd-field" style="background:#f0fdfa;border-radius:4px;padding:0.25rem 0.4rem;margin:0.1rem -0.4rem;">
                    <span class="spd-field-label" style="color:#0f766e;font-weight:700;">Total Harga Net</span>
                    <span class="spd-field-value bold" style="color:#0f766e;"><?php echo format_rupiah($sp_total_net); ?></span>
                </div>
                <?php if ($sp_ppn_pct > 0): ?>
                <div class="spd-field">
                    <span class="spd-field-label">PPN <?php echo $sp_ppn_pct; ?>%</span>
                    <span class="spd-field-value"><?php echo format_rupiah($sp_ppn_nominal); ?></span>
                </div>
                <?php endif; ?>
                <div class="spd-field" style="background:#eff6ff;border-radius:4px;padding:0.25rem 0.4rem;margin:0.1rem -0.4rem;">
                    <span class="spd-field-label" style="color:#1d4ed8;font-weight:700;">Grand Total</span>
                    <span class="spd-field-value bold" style="color:#1d4ed8;font-size:0.85rem;"><?php echo format_rupiah($sp_grand_total); ?></span>
                </div>
            </div>
        </div>

        <!-- Card: Catatan -->
        <div class="spd-card">
            <div class="spd-card-header hdr-slate">
                <i class="fas fa-sticky-note"></i> Catatan
            </div>
            <div class="spd-card-body">
                <div style="margin-bottom:0.5rem;">
                    <div style="font-size:0.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.4px;margin-bottom:0.25rem;">
                        <i class="fas fa-lock mr-1"></i>Catatan Internal (Rahasia)
                    </div>
                    <div style="font-size:0.78rem;color:<?php echo ($sp_notein === '-' ? '#94a3b8' : '#dc2626'); ?>;font-weight:<?php echo ($sp_notein === '-' ? '400' : '600'); ?>;padding:0.3rem 0.5rem;background:<?php echo ($sp_notein === '-' ? 'transparent' : '#fef2f2'); ?>;border-radius:5px;<?php echo ($sp_notein !== '-' ? 'border-left:3px solid #fca5a5;' : ''); ?>">
                        <?php echo $sp_notein; ?>
                    </div>
                </div>
                <hr class="spd-divider">
                <div>
                    <div style="font-size:0.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.4px;margin-bottom:0.25rem;">
                        <i class="fas fa-envelope-open-text mr-1"></i>Catatan untuk Vendor
                    </div>
                    <div style="font-size:0.78rem;color:<?php echo ($sp_noteout === '-' ? '#94a3b8' : '#1e293b'); ?>;padding:0.3rem 0.5rem;background:<?php echo ($sp_noteout === '-' ? 'transparent' : '#f8fafc'); ?>;border-radius:5px;<?php echo ($sp_noteout !== '-' ? 'border-left:3px solid #bfdbfe;' : ''); ?>">
                        <?php echo $sp_noteout; ?>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /spd-cards-grid -->

    <!-- 4. TABEL RINCIAN BARANG -->
    <div class="spd-items-wrap">
        <div class="spd-card-header hdr-amber" style="border-radius:0;">
            <i class="fas fa-boxes"></i> Rincian Barang Pesanan
            <span style="font-size:0.68rem;font-weight:500;margin-left:auto;color:#92400e;"><?php echo count($selected_po_items); ?> item</span>
        </div>
        <div class="spd-items-table-wrap">
            <table class="spd-items-table">
                <thead>
                    <tr>
                        <th class="td-no">No</th>
                        <th>Nama Barang</th>
                        <th>Merk</th>
                        <th>Tipe / Model</th>
                        <th>Spesifikasi</th>
                        <th class="td-center">Qty</th>
                        <th class="td-center">Satuan</th>
                        <th class="td-right">Harga Satuan</th>
                        <th class="td-right">Diskon (%)</th>
                        <th class="td-right">Subtotal</th>
                        <th class="td-center" style="min-width:8rem;">Status Terima</th>
                        <th class="td-center" style="min-width:6rem;">Diterima</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($selected_po_items)): ?>
                        <tr><td colspan="12" style="text-align:center;color:#94a3b8;padding:1.5rem;font-style:italic;">Tidak ada item barang.</td></tr>
                    <?php else: ?>
                        <?php $item_idx = 1; foreach ($selected_po_items as $item): ?>
                        <tr>
                            <td class="td-no"><?php echo $item_idx++; ?></td>
                            <td class="td-barang"><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                            <td><?php echo (trim($item['merk']) !== '' ? htmlspecialchars($item['merk']) : '<span style="color:#cbd5e1;">-</span>'); ?></td>
                            <td><?php echo (trim($item['model']) !== '' ? htmlspecialchars($item['model']) : '<span style="color:#cbd5e1;">-</span>'); ?></td>
                            <td><?php echo (trim($item['spec']) !== '' ? htmlspecialchars($item['spec']) : '<span style="color:#cbd5e1;">-</span>'); ?></td>
                            <td class="td-center font-weight-bold"><?php echo htmlspecialchars((string)$item['jumlah']); ?></td>
                            <td class="td-center"><?php echo htmlspecialchars($item['satuan']); ?></td>
                            <td class="td-right"><?php echo format_rupiah($item['harga_satuan']); ?></td>
                            <td class="td-disc"><?php echo ((float)$item['diskon_item'] > 0 ? (float)$item['diskon_item'] . '%' : '<span style="color:#cbd5e1;">-</span>'); ?></td>
                            <td class="td-total"><?php echo format_rupiah($item['subtotal']); ?></td>
                            <td class="td-center"><?php echo get_receipt_badge($item['status_terima']); ?></td>
                            <td class="td-center" style="font-weight:700;color:#0369a1;">
                                <?php echo (int)$item['jumlah_diterima']; ?> / <?php echo htmlspecialchars((string)$item['jumlah']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Summary footer -->
        <div class="spd-summary-row">
            <div class="spd-summary-inner">
                <div class="spd-summary-line">
                    <span class="slbl">Sub Total</span>
                    <span class="sval"><?php echo format_rupiah($sp_total_net); ?></span>
                </div>
                <?php if ($sp_ppn_nominal > 0): ?>
                <div class="spd-summary-line">
                    <span class="slbl">PPN <?php echo $sp_ppn_pct; ?>%</span>
                    <span class="sval"><?php echo format_rupiah($sp_ppn_nominal); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($sp_diskon_vendor > 0): ?>
                <div class="spd-summary-line">
                    <span class="slbl">Diskon Vendor</span>
                    <span class="sval" style="color:#dc2626;">- <?php echo format_rupiah($sp_diskon_vendor); ?></span>
                </div>
                <?php endif; ?>
                <div class="spd-summary-line total-line">
                    <span class="slbl">Grand Total</span>
                    <span class="sval"><?php echo format_rupiah($sp_grand_total); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. BOTTOM ROW: Lampiran + Audit Trail -->
    <div class="spd-bottom-row">

        <!-- Lampiran -->
        <div class="spd-bottom-panel">
            <div class="spd-card-header hdr-slate">
                <i class="fas fa-paperclip"></i> Lampiran Dokumen
            </div>
            <div class="spd-card-body">
                <?php
                $has_any_lampiran = false;
                if (!empty($selected_po['nama_lampiran'])) {
                    $lampiran_files = explode(',', $selected_po['nama_lampiran']);
                    $lmp_idx = 1;
                    foreach ($lampiran_files as $lmp_file) {
                        $lmp_file = trim($lmp_file);
                        if ($lmp_file === '') continue;
                        $has_any_lampiran = true;
                        $actual_file = $lmp_file;
                        // Jika nama file terpotong (tidak ada ekstensi), cari file aslinya
                        if (strpos($lmp_file, '.') === false) {
                            $matches = glob(dirname(__FILE__) . '/../uploads/lampiran/' . $lmp_file . '*');
                            if (!empty($matches)) {
                                $actual_file = basename($matches[0]);
                            }
                        }
                        
                        if (strpos($actual_file, 'PO_') === 0) {
                            $url = 'http://192.168.2.234/upload/sp_umum/dokumen/' . htmlspecialchars($actual_file);
                        } else {
                            $url = 'uploads/lampiran/' . htmlspecialchars($actual_file);
                        }
                        
                        echo '<a href="' . $url . '" target="_blank" class="spd-attach-btn">'
                           . '<i class="fas fa-file-download"></i> Lampiran SP #' . $lmp_idx . '</a>';
                        $lmp_idx++;
                    }
                }
                if (!empty($selected_po['lampiran_penerimaan'])) {
                    $has_any_lampiran = true;
                    $lampiran_penerimaan = $selected_po['lampiran_penerimaan'];
                    if (strpos($lampiran_penerimaan, 'PENERIMAAN_') === 0) {
                        $url_penerimaan = 'http://192.168.2.234/upload/sp_umum/dokumen/' . htmlspecialchars($lampiran_penerimaan);
                    } else {
                        $url_penerimaan = htmlspecialchars($lampiran_penerimaan);
                    }
                    echo '<a href="' . $url_penerimaan . '" target="_blank" class="spd-attach-btn" style="border-color:#a7f3d0;color:#065f46;background:#ecfdf5;">'
                       . '<i class="fas fa-file-check"></i> Bukti Penerimaan</a>';
                }
                if (!$has_any_lampiran) {
                    echo '<p style="color:#94a3b8;font-style:italic;font-size:0.75rem;margin:0;">Belum ada lampiran yang diunggah.</p>';
                }
                ?>
                <p class="mt-2 mb-0" style="font-size:0.68rem;color:#94a3b8;">
                    <i class="fas fa-info-circle mr-1"></i>Klik untuk membuka atau mengunduh file lampiran.
                </p>
            </div>
        </div>

        <!-- Audit Trail -->
        <div class="spd-bottom-panel">
            <div class="spd-card-header hdr-slate">
                <i class="fas fa-history"></i> Riwayat Persetujuan (Audit Trail)
            </div>
            <div class="spd-card-body" style="padding:0.4rem 0.6rem;max-height:14rem;overflow-y:auto;">
                <?php if (empty($selected_po_logs)): ?>
                    <p style="color:#94a3b8;font-style:italic;font-size:0.75rem;margin:0;">Belum ada riwayat persetujuan.</p>
                <?php else: ?>
                    <ul class="spd-timeline">
                        <?php foreach ($selected_po_logs as $log):
                            $tl_cls = '';
                            if ($log['status'] === 'acc')     $tl_cls = 'tl-acc';
                            if ($log['status'] === 'ditolak') $tl_cls = 'tl-tolak';
                            $safe_catatan_tl = htmlspecialchars((string)$log['catatan']);
                            $safe_catatan_tl = preg_replace('/(\(Pembelian\) karena nominal pesanan di bawah 5 Juta\.?)/i', '<mark style="background:#fef9c3;padding:0.05em 0.25em;border-radius:3px;">$1</mark>', $safe_catatan_tl);
                        ?>
                        <li class="<?php echo $tl_cls; ?>">
                            <span class="tl-date"><?php echo date('d M Y H:i', strtotime($log['tanggal'])); ?></span>
                            <span class="tl-user"><?php echo htmlspecialchars($log['user_nama']); ?></span>
                            <span class="tl-role">(<?php echo strtoupper($log['user_role']); ?>)</span>
                            <div style="margin-top:0.15rem;"><?php echo get_status_badge($log['status']); ?></div>
                            <?php if (trim($log['catatan']) !== ''): ?>
                            <div class="tl-note"><?php echo $safe_catatan_tl; ?></div>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /spd-bottom-row -->

    <!-- 6. APPROVAL ACTION BAR (Direktur only) -->
    <?php if (isset($user_role) && $user_role === 'direktur' && $sp_status === 'diajukan'): ?>
    <div class="spd-approval-bar no-print">
        <div class="spd-approval-bar-header">
            <div class="spd-approval-icon"><i class="fas fa-clipboard-check"></i></div>
            <div>
                <div style="font-weight:700;font-size:0.9rem;color:#1e293b;">Keputusan Direktur</div>
                <div style="font-size:0.72rem;color:#64748b;"><i class="fas fa-info-circle mr-1" style="color:#3b82f6;"></i>Tinjau detail SP di atas, lalu isi catatan sebelum ACC atau Tolak.</div>
            </div>
        </div>
        <div class="spd-approval-body">
            <form action="home.php?page=monitoring&po_id=<?php echo $selected_po['id']; ?>" method="POST" id="form-approval">
                <input type="hidden" name="po_approval_action" value="1">
                <input type="hidden" name="po_id" value="<?php echo $selected_po['id']; ?>">
                <input type="hidden" name="status" id="approval-status-input" value="">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group mb-2">
                            <label class="font-weight-bold text-secondary mb-1" style="font-size:0.72rem;letter-spacing:0.2px;">Cara Pembayaran</label>
                            <select name="pembayaran" class="form-control form-control-sm bp-input" style="height:auto;padding:0.3rem 0.5rem;font-size:0.82rem;border-color:#cbd5e1;">
                                <?php
                                $opts_bayar_appr = array('Tunai / Cash','Transfer Bank','Kredit 30 Hari','Kredit 60 Hari','Kredit 90 Hari','Giro');
                                foreach ($opts_bayar_appr as $ob_appr) {
                                    $sel_appr = (isset($selected_po['pembayaran']) && $selected_po['pembayaran'] === $ob_appr) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($ob_appr) . '" ' . $sel_appr . '>' . htmlspecialchars($ob_appr) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group mb-2 mb-md-0">
                            <label class="font-weight-bold text-secondary mb-1" style="font-size:0.72rem;letter-spacing:0.2px;">Keterangan Tambahan (Opsional)</label>
                            <input type="text" name="pembayaran1" class="form-control form-control-sm bp-input"
                                placeholder="Contoh: DP 50%, termin 2..."
                                value="<?php echo (isset($selected_po['pembayaran1']) ? htmlspecialchars((string)$selected_po['pembayaran1']) : ''); ?>"
                                style="padding:0.3rem 0.5rem;font-size:0.82rem;border-color:#cbd5e1;">
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="form-group mb-0" style="height:100%;display:flex;flex-direction:column;">
                            <label class="font-weight-bold text-secondary mb-1" style="font-size:0.72rem;letter-spacing:0.2px;">Catatan Keputusan</label>
                            <textarea name="catatan" class="form-control bp-input"
                                placeholder="Tuliskan catatan persetujuan atau alasan penolakan di sini..."
                                style="flex:1;min-height:72px;resize:vertical;padding:0.45rem 0.5rem;line-height:1.4;font-size:0.82rem;border-color:#cbd5e1;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end align-items-center mt-3 pt-2" style="border-top:1px dashed #cbd5e1;gap:0.5rem;">
                    <button type="submit" class="spd-btn" style="color:#dc2626;border-color:#fca5a5;background:#fff5f5;font-weight:700;"
                        onclick="document.getElementById('approval-status-input').value='ditolak';"
                        onmouseover="this.style.background='#fee2e2';" onmouseout="this.style.background='#fff5f5';">
                        <i class="fas fa-times-circle"></i> Tolak Permintaan
                    </button>
                    <button type="submit" class="spd-btn" style="color:#fff;background:#2563eb;border-color:#1d4ed8;font-weight:700;box-shadow:0 2px 8px rgba(37,99,235,0.3);"
                        onclick="document.getElementById('approval-status-input').value='acc';"
                        onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
                        <i class="fas fa-check-circle"></i> ACC Permintaan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /spd-wrap -->

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



<?php
require_once dirname(__FILE__) . '/../includes/footer.php';
?>
