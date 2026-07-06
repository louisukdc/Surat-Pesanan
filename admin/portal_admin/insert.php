<?php
/**
 * Portal Admin Surat Pesanan
 * File: admin/portal_admin/insert.php
 * Identik dengan m_tarif/admin/insert.php
 * AJAX: berikan akses grup ke user (INSERT sp_usermenu)
 */

require_once dirname(__FILE__) . '/../../config/database.php';

$id_nik     = isset($_POST['id_nik'])     ? trim($_POST['id_nik'])     : '';
$id_usergrup = isset($_POST['id_usergrup']) ? (int)$_POST['id_usergrup'] : 0;

if (!empty($id_nik) && $id_usergrup > 0) {
    db_set_user_grup($id_nik, $id_usergrup);
}
