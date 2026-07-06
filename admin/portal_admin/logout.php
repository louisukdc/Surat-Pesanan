<?php
/**
 * Portal Admin Surat Pesanan
 * File: admin/portal_admin/logout.php
 * Identik dengan m_tarif/admin/logout.php
 * Hapus session admin dan redirect ke login portal admin
 */

if (session_id() == '') {
    session_start();
}

// Hapus hanya session admin portal (tidak hapus session user umum)
unset($_SESSION['sp_admin_username']);
unset($_SESSION['sp_admin_nama']);
unset($_SESSION['sp_admin_grup']);
unset($_SESSION['sp_admin_multigrup']);

header("Location: index.html");
exit();
