<?php
/**
 * Portal Admin Surat Pesanan
 * File: admin/portal_admin/aksi_masuk.php
 * Identik polanya dengan m_tarif/admin/aksi_masuk.php
 * Autentikasi dari tabel sp_user lokal + cek sp_usermenu grup 1 (Admin IT)
 */

if (session_id() == '') {
    session_start();
}

header('Content-Type: application/json');

// Load database connection
require_once dirname(__FILE__) . '/../../config/database.php';

$username_input = isset($_POST['username']) ? trim($_POST['username']) : '';
$pass_input     = isset($_POST['password']) ? trim($_POST['password']) : '';

if (empty($username_input) || empty($pass_input)) {
    echo json_encode(array('Username dan Password wajib diisi!'));
    exit;
}

// 1. Cek credentials dari tabel sp_user lokal
$user = db_authenticate_user($username_input, $pass_input);

if (!$user) {
    echo json_encode(array('Username atau Kata Sandi salah.'));
    exit;
}

// 2. Cek apakah user punya akses Admin IT (id_usergrup = 1) di sp_usermenu
//    Identik dengan m_tarif: cek tabel m_tarif_usermenu WHERE id_usergrup IN (1)
$grups = db_get_user_grups($user['username']);

if (!in_array(1, $grups)) {
    echo json_encode(array('Username "' . htmlspecialchars($user['username']) . '" tidak memiliki hak akses Admin Portal Surat Pesanan.'));
    exit;
}

// 3. Set session (prefix sp_admin_ untuk membedakan dari session user biasa)
//    Identik pola m_tarif: mtarif_nik, mtarif_nama, mtarif_grup, mtarif_multigrup
$_SESSION['sp_admin_username'] = $user['username'];
$_SESSION['sp_admin_nama']     = $user['nama'];
$_SESSION['sp_admin_grup']     = $grups[0];
$_SESSION['sp_admin_multigrup']= $grups;

// 4. Return success
echo json_encode(array('berhak', 'home.php'));
exit;
