<?php
// admin/aksi_pilih_role.php
// Memproses pilihan role setelah login jika user memiliki lebih dari 1 role

if (session_id() == '') {
    session_start();
}

header('Content-Type: application/json');

$role_id = isset($_POST['role_id']) ? (int)$_POST['role_id'] : 0;

if ($role_id > 0 && isset($_SESSION['temp_user_id'])) {
    // Pindahkan data user dari session sementara ke session aktif
    $_SESSION['user_id']       = $_SESSION['temp_user_id'];
    $_SESSION['user_nama']     = $_SESSION['temp_user_nama'];
    $_SESSION['user_username'] = $_SESSION['temp_user_username'];
    $_SESSION['user_role']     = $_SESSION['temp_user_role'];
    $_SESSION['sp_multigrup']  = $_SESSION['temp_sp_multigrup'];
    $_SESSION['sp_usergrup']   = $role_id;

    // Tentukan user_role string ('staff' atau 'direktur') berdasarkan role pilihan
    if ($role_id == 2) {
        $_SESSION['user_role'] = 'direktur';
    } else {
        $_SESSION['user_role'] = 'staff';
    }

    // Bersihkan session sementara
    unset(
        $_SESSION['temp_user_id'],
        $_SESSION['temp_user_nama'],
        $_SESSION['temp_user_username'],
        $_SESSION['temp_user_role'],
        $_SESSION['temp_sp_multigrup']
    );

    echo json_encode(array('success', 'home.php'));
    exit;
} else {
    echo json_encode(array('error', 'Sesi login tidak valid atau kadaluarsa. Silakan login kembali.'));
    exit;
}
