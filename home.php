<?php
// home.php
// Single Entry Point / Front Controller untuk Sistem Surat Pesanan

if (session_id() == '') {
    session_start();
}

define('FRONT_CONTROLLER', true);

// Include database and authentication helpers
require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/auth.php';

// Force login check
sp_require_login();

$page = isset($_GET['page']) ? trim($_GET['page']) : 'dashboard';

$allowed_pages = array(
    'dashboard'         => 'views/dashboard.php',
    'buat_pesanan'      => 'views/buat_pesanan.php',
    'monitoring'        => 'views/monitoring.php',
    'penerimaan'        => 'views/penerimaan.php',
    'pembayaran'        => 'views/pembayaran.php',
    'pembayaran_detail' => 'views/pembayaran_detail.php'
);

if (!array_key_exists($page, $allowed_pages)) {
    $page = 'dashboard';
}

// Cek hak akses halaman berdasarkan sp_usermenu grup (pola m_tarif)
sp_cek_akses_menu($page);

// Include the view page (it will render its own header/footer)
include dirname(__FILE__) . '/' . $allowed_pages[$page];

