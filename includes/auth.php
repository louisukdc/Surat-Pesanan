<?php
// includes/auth.php
// Session check, authentication helpers, and formatting utilities for PHP 5.4

if (session_id() == '') {
    session_start();
}

// Ensure database connection is loaded
require_once dirname(__FILE__) . '/../config/database.php';

define('SP_SALT', 'SP_SALT_2026');

/**
 * Hash password using SHA-256 and salt
 */
function sp_hash_password($password) {
    return hash('sha256', SP_SALT . $password);
}

/**
 * Verify password
 */
function sp_verify_password($password, $hashed_password) {
    return sp_hash_password($password) === $hashed_password;
}

/**
 * Attempt user login
 */
function sp_login($username, $password) {
    $user = db_authenticate_user($username, $password);
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nama'] = $user['nama'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];

        // Load sp_usermenu grups (identik pola m_tarif: set sp_usergrup & sp_multigrup)
        $grups = db_get_user_grups($user['username']);
        $_SESSION['sp_multigrup'] = $grups;
        $_SESSION['sp_usergrup'] = !empty($grups) ? $grups[0] : 0;

        return true;
    }
    return false;
}

/**
 * Logout user
 */
function sp_logout() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Check if user is logged in
 */
function sp_is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Require login, redirect if not logged in
 */
function sp_require_login() {
    if (!sp_is_logged_in()) {
        header("Location: /sp_umum/index.html");
        exit;
    }
}

/**
 * Require specific role (legacy — gunakan sp_cek_akses_menu untuk akses berbasis grup)
 */
function sp_require_role($role) {
    sp_require_login();
    if ($_SESSION['user_role'] !== $role) {
        // Redirect to unauthorized page or dashboard
        header("Location: /sp_umum/home.php?page=dashboard&err=unauthorized");
        exit;
    }
}

/**
 * Cek akses halaman berdasarkan grup di sp_usermenu
 * Identik polanya dengan m_tarif: cek id_usergrup di session
 *
 * Mapping halaman -> grup yang diizinkan:
 *   1 = Admin IT (akses semua)
 *   2 = Direktur
 *   3 = Pembelian
 */
function sp_cek_akses_menu($page) {
    // Mapping: page => array of id_usergrup yang boleh akses
    $akses_menu = array(
        'dashboard'          => array(1, 2, 3),
        'buat_pesanan'       => array(1, 3),         // Admin IT & Pembelian
        'monitoring'         => array(1, 2, 3),
        'penerimaan'         => array(1, 3),         // Admin IT & Pembelian
        'pembayaran'         => array(1, 2, 3),
        'pembayaran_detail'  => array(1, 2),          // Admin IT & Direktur
    );

    if (!isset($akses_menu[$page])) return; // Halaman tidak dikenal, biarkan lolos

    // Gunakan grup aktif (sp_usergrup) jika diset, jika tidak fallback ke sp_multigrup atau role lama
    $grup_aktif = 0;
    if (isset($_SESSION['sp_usergrup']) && $_SESSION['sp_usergrup'] > 0) {
        $grup_aktif = (int)$_SESSION['sp_usergrup'];
    } else {
        $grups_user = isset($_SESSION['sp_multigrup']) ? $_SESSION['sp_multigrup'] : array();
        if (empty($grups_user)) {
            $role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
            if ($role === 'direktur')      $grup_aktif = 2;
            else                           $grup_aktif = 3; // default Pembelian
        } else {
            $grup_aktif = (int)$grups_user[0];
        }
    }

    $boleh_akses = $akses_menu[$page];
    if (!in_array($grup_aktif, $boleh_akses)) {
        header("Location: /sp_umum/home.php?page=dashboard&err=unauthorized");
        exit;
    }
}

/**
 * Format currency to Rupiah
 */
function format_rupiah($amount) {
    return 'Rp ' . number_format((float)$amount, 0, ',', '.');
}

/**
 * Format dates beautifully
 */
function format_date($date_string) {
    if (!$date_string) return '-';
    $timestamp = strtotime($date_string);
    if (!$timestamp) return $date_string;
    
    $months = array(
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $d = date('d', $timestamp);
    $m = $months[(int)date('m', $timestamp) - 1];
    $y = date('Y', $timestamp);
    
    return "$d $m $y";
}

/**
 * Get Status Badge CSS Class and label
 */
function get_status_badge($status) {
    switch ($status) {
        case 'draft':
            return '<span class="badge badge-status badge-draft">Draft</span>';
        case 'diajukan':
            return '<span class="badge badge-status badge-diajukan">Diajukan</span>';
        case 'direview':
            return '<span class="badge badge-status badge-review">Direview</span>';
        case 'acc':
            return '<span class="badge badge-status badge-acc">Disetujui (ACC)</span>';
        case 'ditolak':
            return '<span class="badge badge-status badge-ditolak">Ditolak</span>';
        default:
            return '<span class="badge badge-status badge-draft">' . htmlspecialchars($status) . '</span>';
    }
}

/**
 * Get Payment Request Status Badge
 */
function get_payment_badge($status) {
    switch ($status) {
        case 'diajukan':
            return '<span class="badge badge-status badge-diajukan">Diajukan</span>';
        case 'acc':
            return '<span class="badge badge-status badge-acc">Lunas (ACC)</span>';
        case 'ditolak':
            return '<span class="badge badge-status badge-ditolak">Ditolak</span>';
        case 'belum_bayar':
        default:
            return '<span class="badge badge-status badge-draft">Belum Diajukan</span>';
    }
}

/**
 * Get Receipt Status Badge for Items
 */
function get_receipt_badge($status) {
    switch ($status) {
        case 'belum_datang':
            return '<span class="badge badge-status badge-ditolak">Belum Datang</span>';
        case 'sebagian':
            return '<span class="badge badge-status badge-review">Sebagian</span>';
        case 'lengkap':
            return '<span class="badge badge-status badge-acc">Lengkap</span>';
        default:
            return '<span class="badge badge-status badge-draft">' . htmlspecialchars($status) . '</span>';
    }
}
