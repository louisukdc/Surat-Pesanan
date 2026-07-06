<?php
// includes/header.php
// Common header layout for Sistem Surat Pesanan

require_once dirname(__FILE__) . '/auth.php';
sp_require_login();

// Fallback page title and active menu
$page_title  = isset($page_title)  ? $page_title  : 'Sistem Surat Pesanan';
$active_menu = isset($active_menu) ? $active_menu : 'dashboard';

// User info
$user_id       = $_SESSION['user_id'];
$user_nama     = $_SESSION['user_nama'];
$user_username = $_SESSION['user_username'];
$user_role     = $_SESSION['user_role'];

// === HAK AKSES BERBASIS GRUP (pola m_tarif) ===
// Ambil multigrup dan grup aktif dari session
$sp_multigrup = isset($_SESSION['sp_multigrup']) ? $_SESSION['sp_multigrup'] : array();
$sp_usergrup_utama = isset($_SESSION['sp_usergrup']) ? (int)$_SESSION['sp_usergrup'] : 0;

// Fallback kompatibilitas: konversi role string lama ke id_usergrup
if ($sp_usergrup_utama === 0) {
    if (empty($sp_multigrup)) {
        if ($user_role === 'direktur') {
            $sp_multigrup = array(2);
            $sp_usergrup_utama = 2;
        } else {
            $sp_multigrup = array(3);
            $sp_usergrup_utama = 3;
        }
    } else {
        $sp_usergrup_utama = (int)$sp_multigrup[0];
    }
}

// Helper: cek apakah user punya salah satu dari grup yang diberikan
function _header_boleh($grup_aktif, $grups_diizinkan) {
    return in_array((int)$grup_aktif, $grups_diizinkan);
}

$boleh_buat_pesanan = _header_boleh($sp_usergrup_utama, array(1, 3));
$boleh_penerimaan   = _header_boleh($sp_usergrup_utama, array(1, 3));
$boleh_pembayaran   = _header_boleh($sp_usergrup_utama, array(1, 2, 3));

// Count pending items for badge (hanya jika punya akses)
$pending_po_count  = 0;
$pending_pay_count = 0;
if (_header_boleh($sp_usergrup_utama, array(1, 2))) {
    $stats = db_get_dashboard_stats();
    $pending_po_count  = $stats['pending_po_approvals'];
    $pending_pay_count = $stats['pending_payment_approvals'];
}
$total_pending = $pending_po_count + $pending_pay_count;

// Get initials for avatar
$name_parts = explode(' ', $user_nama);
$initials = '';
if (count($name_parts) > 0) { $initials .= strtoupper(substr($name_parts[0], 0, 1)); }
if (count($name_parts) > 1) { $initials .= strtoupper(substr($name_parts[1], 0, 1)); }
if ($initials === '') $initials = 'U';

// Nama grup untuk ditampilkan di sidebar
$nama_grup_label = array(1 => 'Admin IT', 2 => 'Direktur', 3 => 'Pembelian');
$label_role_display = isset($nama_grup_label[$sp_usergrup_utama]) ? $nama_grup_label[$sp_usergrup_utama] : $user_role;

// Siapkan data role untuk switch role JS
$switch_roles_json = array();
if (count($sp_multigrup) > 1) {
    foreach ($sp_multigrup as $gid) {
        $switch_roles_json[] = array(
            'id' => (int)$gid,
            'nama' => isset($nama_grup_label[$gid]) ? $nama_grup_label[$gid] : 'Grup ' . $gid,
            'aktif' => ((int)$gid === $sp_usergrup_utama)
        );
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Sistem Surat Pesanan</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/sp_umum/assets/css/style.css?v=5">
</head>
<body>

    <div id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar">
            <div class="sidebar-header">
                <img src="/sp_umum/assets/img/logo_rkz.png" alt="Logo RKZ" class="sidebar-logo-img" style="width: 2.5rem; height: auto; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
                <div class="sidebar-brand">
                    Surat Pesanan
                    <span>Sistem Administrasi</span>
                </div>
            </div>
            
            <ul class="nav-menu">
                <!-- Dashboard — semua grup -->
                <li class="nav-item">
                    <a href="/sp_umum/home.php?page=dashboard" class="nav-link <?php echo $active_menu == 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <!-- Buat Pesanan — Admin (1) & Pembelian (3) -->
                <?php if ($boleh_buat_pesanan): ?>
                    <li class="nav-item">
                        <a href="/sp_umum/home.php?page=buat_pesanan" class="nav-link <?php echo $active_menu == 'buat_pesanan' ? 'active' : ''; ?>">
                            <i class="fas fa-plus-circle"></i>
                            <span>Buat Pesanan (SP)</span>
                        </a>
                    </li>
                <?php endif; ?>
                
                <!-- Monitoring — semua grup, badge untuk grup 1 & 2 -->
                <li class="nav-item">
                    <a href="/sp_umum/home.php?page=monitoring" class="nav-link <?php echo $active_menu == 'monitoring' ? 'active' : ''; ?>">
                        <i class="fas fa-list-alt"></i>
                        <span>Monitoring SP</span>
                        <?php if ($pending_po_count > 0): ?>
                            <span class="badge badge-danger ml-auto"><?php echo $pending_po_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <!-- Penerimaan Barang — Admin (1) & Pembelian (3) -->
                <?php if ($boleh_penerimaan): ?>
                    <li class="nav-item">
                        <a href="/sp_umum/home.php?page=penerimaan" class="nav-link <?php echo $active_menu == 'penerimaan' ? 'active' : ''; ?>">
                            <i class="fas fa-boxes"></i>
                            <span>Penerimaan Barang</span>
                        </a>
                    </li>
                <?php endif; ?>
                
                <!-- Menu Bayar — semua grup, badge untuk grup 1 & 2 -->
                <?php if ($boleh_pembayaran): ?>
                    <li class="nav-item">
                        <a href="/sp_umum/home.php?page=pembayaran" class="nav-link <?php echo $active_menu == 'pembayaran' ? 'active' : ''; ?>">
                            <i class="fas fa-credit-card"></i>
                            <span>Menu Bayar</span>
                            <?php if ($pending_pay_count > 0): ?>
                                <span class="badge badge-danger ml-auto"><?php echo $pending_pay_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php if (count($sp_multigrup) > 1): ?>
                <li class="nav-item mt-3" style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 10px;">
                    <a href="javascript:void(0)" class="nav-link" id="btnSwitchRole" style="color: #818cf8;">
                        <i class="fas fa-exchange-alt" style="color: #818cf8;"></i>
                        <span>Ganti Role</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item <?php echo count($sp_multigrup) > 1 ? '' : 'mt-4'; ?>" style="<?php echo count($sp_multigrup) > 1 ? '' : 'border-top: 1px solid rgba(255,255,255,0.08); padding-top: 10px;'; ?>">
                    <a href="/sp_umum/admin/logout.php" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt text-danger"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>

            
            <div class="sidebar-footer">
                v1.0.0 (PHP 5.4 Native)
            </div>
        </div>
        <!-- /#sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper">
            
            <!-- Topbar -->
            <div class="topbar">
                <h2 class="page-title"><?php echo $page_title; ?></h2>
                
                <div class="topbar-user">
                    <div class="user-info text-right">
                        <span class="user-name"><?php echo htmlspecialchars($user_nama); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($label_role_display); ?></span>
                    </div>
                    <div class="user-avatar" title="<?php echo htmlspecialchars($user_nama); ?>">
                        <?php echo $initials; ?>
                    </div>
                </div>
            </div>
            <!-- /Topbar -->

            <!-- Main Content Area -->
            <div class="main-content">
