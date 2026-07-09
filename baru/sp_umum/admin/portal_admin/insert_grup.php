<?php
/**
 * File: admin/portal_admin/insert_grup.php
 * Deskripsi: Endpoint AJAX untuk insert hak akses grup ke menu
 */
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usergrup = isset($_POST['id_usergrup']) ? (int)$_POST['id_usergrup'] : 0;
    $id_menu     = isset($_POST['id_menu'])     ? (int)$_POST['id_menu']     : 0;

    if ($id_usergrup > 0 && $id_menu > 0) {
        $check = mysqli_query($conn, "SELECT id FROM sp_umum_grupakses WHERE id_usergrup = $id_usergrup AND id_menu = $id_menu");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO sp_umum_grupakses (id_usergrup, id_menu) VALUES ($id_usergrup, $id_menu)");
            echo json_encode(array('status' => 'success'));
        } else {
            echo json_encode(array('status' => 'exist'));
        }
    } else {
        echo json_encode(array('status' => 'invalid'));
    }
}
