<?php
/**
 * Konfigurasi Koneksi Database
 * Project: Surat Pesanan
 * Identik strukturnya dengan m_tarif/config/mysql_connection.php
 */

$host     = "192.168.2.12";
$username = "anugrah";
$password = "anugrah";

// Set default timezone untuk menghilangkan warning date() di PHP lokal
date_default_timezone_set('Asia/Jakarta');

// Matikan Exception Mode (memaksa PHP 8+ berperilaku persis seperti PHP 5.4)
mysqli_report(MYSQLI_REPORT_OFF);

// Database utama Surat Pesanan
$conn = @mysqli_connect($host, $username, $password, "dbold");
$err_conn = $conn ? '' : mysqli_connect_error();

if (!$conn) {
    die("Kesalahan Koneksi Database (dbold): " . $err_conn . ". Pastikan database 'dbold' sudah ada di server Ubuntu Anda dan username/password root sudah benar.");
}

// Database HRD (opsional — untuk verifikasi karyawan aktif)
$conn_hrd = @mysqli_connect($host, $username, $password, "hrd");

// Database Askes (untuk master data supplier, dll)
$conn_askes = @mysqli_connect($host, $username, $password, "askes");

// Cek koneksi utama
if (!$conn) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode(array('status' => 'error', 'message' => 'Koneksi database gagal: ' . $err_conn));
    exit;
}

// Set charset
mysqli_set_charset($conn, "utf8");
if ($conn_hrd) mysqli_set_charset($conn_hrd, "utf8");
if ($conn_askes) mysqli_set_charset($conn_askes, "utf8");

// Expose ke global scope (digunakan oleh db_functions.php)
$GLOBALS['db_conn']    = $conn;
$GLOBALS['hrd_conn']   = $conn_hrd ? $conn_hrd : null;
$GLOBALS['askes_conn'] = $conn_askes ? $conn_askes : null;

// Load semua fungsi query database
require_once dirname(__FILE__) . '/db_functions.php';
