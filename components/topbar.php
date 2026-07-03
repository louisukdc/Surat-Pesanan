<?php
// components/topbar.php
$titles = [
    'home' => 'Dashboard',
    'order_form' => 'Surat Pesanan',
    'list_pesanan' => 'Daftar Pesanan',
    'master_supplier' => 'Master Supplier',
    'laporan' => 'Laporan Transaksi',
    'users' => 'Manajemen User'
];
$page_title = isset($titles[$page]) ? $titles[$page] : 'RKZ System';
?>
<header class="topbar">
    <div>
        <h2 style="font-weight: 600; color: var(--primary); text-transform: capitalize;">
            <?php echo htmlspecialchars($page_title); ?>
        </h2>
        <p style="color: var(--text-secondary); font-size: 14px;">Manage health insurance system</p>
    </div>
    <div class="user-profile">
        <div class="avatar">
            <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
        </div>
        <div>
            <div style="font-size: 14px;"><?php echo htmlspecialchars($_SESSION['nama']); ?></div>
            <div style="font-size: 12px; color: var(--text-secondary); text-transform: capitalize;">NIK: <?php echo htmlspecialchars($_SESSION['nik']); ?></div>
        </div>
    </div>
</header>
