<?php
// admin/aksi_switch_role.php
// Switch role aktif tanpa logout — hanya untuk user yang sudah login & punya multi-role

if (session_id() == '') {
    session_start();
}

header('Content-Type: application/json');

$role_id = isset($_POST['role_id']) ? (int)$_POST['role_id'] : 0;

// Pastikan user sudah login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['sp_multigrup'])) {
    echo json_encode(array('error', 'Anda belum login. Silakan login terlebih dahulu.'));
    exit;
}

$grups = $_SESSION['sp_multigrup'];

// Pastikan role yang dipilih memang dimiliki user
if ($role_id <= 0 || !in_array($role_id, $grups)) {
    echo json_encode(array('error', 'Role yang dipilih tidak valid atau tidak dimiliki akun Anda.'));
    exit;
}

// Ganti role aktif
$_SESSION['sp_usergrup'] = $role_id;

// Update user_role string untuk kompatibilitas
if ($role_id == 2) {
    $_SESSION['user_role'] = 'direktur';
} else {
    $_SESSION['user_role'] = 'staff';
}

echo json_encode(array('success', 'Role berhasil diganti.'));
exit;
