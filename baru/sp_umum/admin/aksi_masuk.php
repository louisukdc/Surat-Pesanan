<?php
// admin/aksi_masuk.php
// Autentikasi User Admin / Staff / Direktur via HRD datadasar

if (session_id() == '') {
    session_start();
}

header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../config/database.php';

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($username === '' || $password === '') {
    echo json_encode(array('Username dan Password wajib diisi!'));
    exit;
}

// Authenticate via HRD and local sp_user checks
$user = db_authenticate_user($username, $password);

if ($user) {
    // Ambil daftar grup user dari sp_usermenu
    $grups = db_get_user_grups($user['username']);
    
    if (empty($grups)) {
        echo json_encode(array('NIP Anda tidak memiliki hak akses ke sistem Surat Pesanan.'));
        exit;
    }

    // Simpan data sementara ke session
    $_SESSION['temp_user_id']       = $user['id'];
    $_SESSION['temp_user_nama']     = $user['nama'];
    $_SESSION['temp_user_username'] = $user['username'];
    $_SESSION['temp_user_role']     = $user['role'];
    $_SESSION['temp_sp_multigrup']  = $grups;

    // Jika user hanya memiliki 1 role, bypass pilihan dan set langsung
    if (count($grups) == 1) {
        $_SESSION['user_id']       = $_SESSION['temp_user_id'];
        $_SESSION['user_nama']     = $_SESSION['temp_user_nama'];
        $_SESSION['user_username'] = $_SESSION['temp_user_username'];
        $_SESSION['sp_multigrup']  = $_SESSION['temp_sp_multigrup'];
        $_SESSION['sp_usergrup']   = $grups[0];

        // Tentukan user_role string ('staff' atau 'direktur') berdasarkan role
        if ($grups[0] == 2) {
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
        
        echo json_encode(array('berhak', 'home.php'));
        exit;
    }

    // Jika lebih dari 1 role, kirim list role ke front-end
    $grup_labels = array(1 => 'Admin IT', 2 => 'Direktur', 3 => 'Pembelian');
    $roles_list = array();
    foreach ($grups as $gid) {
        $roles_list[] = array(
            'id' => $gid,
            'nama' => isset($grup_labels[$gid]) ? $grup_labels[$gid] : 'Grup ' . $gid
        );
    }

    echo json_encode(array('pilih_role', $roles_list));
    exit;
} else {
    echo json_encode(array('NIP atau Kata Sandi Salah, atau Anda tidak memiliki akses ke sistem ini.'));
    exit;
}
