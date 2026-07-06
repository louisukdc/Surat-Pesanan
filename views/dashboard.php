<?php
// views/dashboard.php
// Main dashboard display

if (!defined('FRONT_CONTROLLER')) {
    header("Location: /sp_umum/home.php?page=dashboard" . ($_SERVER['QUERY_STRING'] !== '' ? '&' . $_SERVER['QUERY_STRING'] : ''));
    exit;
}

$page_title = 'Dashboard';
$active_menu = 'dashboard';

require_once dirname(__FILE__) . '/../includes/auth.php';
require_once dirname(__FILE__) . '/../includes/header.php';

// Fetch stats
$stats = db_get_dashboard_stats();
$latest_pos = array_slice(db_get_purchase_orders(), 0, 5); // Latest 5 orders
?>

<!-- Welcome Banner Card -->
<div class="welcome-banner-premium mb-2">
    <div class="card-body p-2 px-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap" style="position: relative; z-index: 2;">
            <div>
                <h4 class="font-weight-bold mb-1" style="font-family: var(--font-heading); font-weight: 800;">Selamat Datang Kembali, <?php echo htmlspecialchars($user_nama); ?>!</h4>
                <p class="mb-0 text-light opacity-75">Anda login sebagai <strong class="text-warning"><?php echo strtoupper($label_role_display); ?></strong> di Sistem Surat Pesanan.</p>
            </div>
            <div class="text-right">
                <span class="d-block text-white-50 small">Hari ini</span>
                <span class="font-weight-bold"><?php echo format_date(date('Y-m-d')); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Alert for pending approvals for Direktur -->
<?php if ($user_role === 'direktur' && ($stats['pending_po_approvals'] > 0 || $stats['pending_payment_approvals'] > 0)): ?>
    <div class="alert alert-warning alert-premium mb-2 border-warning">
        <i class="fas fa-bell fa-lg text-warning mr-2"></i>
        <div>
            <strong>Notifikasi Approval:</strong> Ada 
            <?php if ($stats['pending_po_approvals'] > 0): ?>
                <a href="/sp_umum/home.php?page=monitoring&status=diajukan" class="alert-link text-dark font-weight-bold">
                    <?php echo $stats['pending_po_approvals']; ?> permintaan pesanan (SP)
                </a>
            <?php endif; ?>
            <?php if ($stats['pending_po_approvals'] > 0 && $stats['pending_payment_approvals'] > 0) echo ' dan '; ?>
            <?php if ($stats['pending_payment_approvals'] > 0): ?>
                <a href="/sp_umum/home.php?page=pembayaran" class="alert-link text-dark font-weight-bold">
                    <?php echo $stats['pending_payment_approvals']; ?> pengajuan pembayaran
                </a>
            <?php endif; ?>
            yang membutuhkan persetujuan Anda.
        </div>
    </div>
<?php endif; ?>

<!-- Stat Cards Grid -->
<div class="row mb-2">
    <div class="col-xl-4 col-md-6 mb-2 mb-xl-0">
        <div class="stat-card">
            <div class="stat-card-info">
                <span class="stat-card-title">Nilai SP Disetujui Bulan Ini</span>
                <span class="stat-card-value text-success"><?php echo format_rupiah($stats['total_value_month']); ?></span>
            </div>
            <div class="stat-card-icon icon-acc">
                <i class="fas fa-wallet"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-6 mb-2 mb-xl-0">
        <div class="stat-card">
            <div class="stat-card-info">
                <span class="stat-card-title">Diajukan (Pending)</span>
                <span class="stat-card-value"><?php echo $stats['count_diajukan']; ?></span>
            </div>
            <div class="stat-card-icon icon-diajukan">
                <i class="fas fa-hourglass-half"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-6 mb-2 mb-xl-0">
        <div class="stat-card">
            <div class="stat-card-info">
                <span class="stat-card-title">Disetujui (ACC)</span>
                <span class="stat-card-value text-success"><?php echo $stats['count_acc']; ?></span>
            </div>
            <div class="stat-card-icon icon-acc">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-6 mb-2 mb-xl-0">
        <div class="stat-card">
            <div class="stat-card-info">
                <span class="stat-card-title">Ditolak</span>
                <span class="stat-card-value text-danger"><?php echo $stats['count_ditolak']; ?></span>
            </div>
            <div class="stat-card-icon icon-ditolak">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-6">
        <div class="stat-card">
            <div class="stat-card-info">
                <span class="stat-card-title">Draft SP</span>
                <span class="stat-card-value"><?php echo $stats['count_draft']; ?></span>
            </div>
            <div class="stat-card-icon icon-draft">
                <i class="fas fa-file-alt"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Latest Orders Table -->
    <div class="col-lg-8 mb-2">
        <div class="bp-panel bp-panel-amber" style="height: 100%; margin-bottom: 0;">
            <div class="bp-panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div><i class="fas fa-list-ul mr-2"></i> 5 Surat Pesanan Terbaru</div>
                <a href="/sp_umum/home.php?page=monitoring" class="btn btn-sm btn-premium-secondary" style="font-size: 0.7rem; padding: 0.2rem 0.6rem;">Lihat Semua</a>
            </div>
            <div class="bp-panel-body" style="padding: 0.5rem;">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 bp-items-table">
                        <thead>
                            <tr>
                                <th>No Pesanan</th>
                                <th>Tanggal</th>
                                <th>Vendor</th>
                                <th class="text-right">Total (Setelah Diskon)</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($latest_pos)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada data Surat Pesanan.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($latest_pos as $po): ?>
                                    <tr>
                                        <td class="font-weight-bold text-dark"><?php echo htmlspecialchars($po['no_pesanan']); ?></td>
                                        <td><?php echo format_date($po['tgl_pesanan']); ?></td>
                                        <td><?php echo htmlspecialchars($po['nama_vendor']); ?></td>
                                        <td class="text-right font-weight-bold"><?php echo format_rupiah($po['total_setelah_diskon']); ?></td>
                                        <td class="text-center"><?php echo get_status_badge($po['status']); ?></td>
                                        <td class="text-center">
                                            <a href="/sp_umum/home.php?page=monitoring&po_id=<?php echo $po['id']; ?>" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                                                <i class="fas fa-eye"></i> Detail
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
    </div>

    <!-- Quick Help & Shortcuts -->
    <div class="col-lg-4 mb-2">
        <div class="bp-panel bp-panel-slate" style="height: 100%; margin-bottom: 0;">
            <div class="bp-panel-header">
                <div><i class="fas fa-info-circle mr-2"></i> Tautan Singkat</div>
            </div>
            <div class="bp-panel-body">
                <div class="list-group list-group-flush" style="font-size: 0.75rem;">
                    <?php if ($user_role === 'staff'): ?>
                        <a href="/sp_umum/home.php?page=buat_pesanan" class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-plus-circle text-success mr-2"></i> Buat Surat Pesanan (SP)</span>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </a>
                        <a href="/sp_umum/home.php?page=penerimaan" class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-boxes text-info mr-2"></i> Input Barang Masuk</span>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </a>
                    <?php endif; ?>
                    
                    <a href="/sp_umum/home.php?page=monitoring" class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-eye text-primary mr-2"></i> Monitoring & Status SP</span>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    
                    <a href="/sp_umum/home.php?page=pembayaran" class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-file-invoice-dollar text-warning mr-2"></i> Pengajuan & Status Bayar</span>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                </div>
                
                <div class="mt-2 pt-2 border-top">
                    <h6>Petunjuk Alur Kerja:</h6>
                    <ol class="pl-3 text-muted" style="font-size: 0.7rem; line-height: 1.4; margin-bottom:0;">
                        <li><strong>Pembelian</strong> membuat Permintaan Surat Pesanan (SP).</li>
                        <li><strong>Direktur</strong> memeriksa dan memberikan ACC Permintaan.</li>
                        <li><strong>Pembelian</strong> mencentang checklist barang jika pesanan datang.</li>
                        <li><strong>Pembelian</strong> mengajukan pembayaran untuk SP tersebut.</li>
                        <li><strong>Direktur</strong> membandingkan pesanan vs penerimaan & nominal pembayaran lalu ACC.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once dirname(__FILE__) . '/../includes/footer.php';
?>
