<?php
/**
 * Fungsi-fungsi query database untuk Sistem Surat Pesanan
 * File: config/db_functions.php
 *
 * Semua fungsi menggunakan $GLOBALS['db_conn'] yang diset oleh database.php
 * Tidak ada mock data — semua query langsung ke MySQL
 */

// ============================================================
// HELPER UMUM
// ============================================================

/**
 * Escape input untuk query MySQL
 */
function db_escape($value) {
    if ($GLOBALS['db_conn']) {
        return mysqli_real_escape_string($GLOBALS['db_conn'], $value);
    }
    return strip_tags(trim($value));
}

// ============================================================
// MASTER DATA DARI DATABASE ASKES
// ============================================================

/**
 * Ambil daftar supplier dari askes.m_supplier
 * Mengembalikan array berisi id, kode, dan nama supplier
 */
function db_get_suppliers() {
    $conn = isset($GLOBALS['askes_conn']) ? $GLOBALS['askes_conn'] : null;
    if (!$conn) return array();

    $query = "SELECT IdSupplier, KodeSupplier, TRIM(NamaSupplier) as NamaSupplier 
              FROM m_supplier 
              ORDER BY NamaSupplier ASC";
    $result = mysqli_query($conn, $query);
    if (!$result) return array();

    $suppliers = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $suppliers[] = $row;
    }
    return $suppliers;
}

/**
 * Mengembalikan array gudang / unit
 */
function db_get_gudang() {
    $conn = isset($GLOBALS['db_conn']) ? $GLOBALS['db_conn'] : null;
    if (!$conn) return array();

    $query = "SELECT FGUDANG as KodeGudang, FNAMA as NamaGudang, FGUDANG, FNAMA 
              FROM godggudang 
              ORDER BY FNAMA ASC";
    $result = mysqli_query($conn, $query);
    if (!$result) return array();

    $gudang = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $gudang[] = $row;
    }
    return $gudang;
}

// ============================================================
// USER / AUTENTIKASI
// ============================================================

/**
 * Ambil data pengguna berdasarkan username (NIK)
 */
function db_get_user_by_username($username) {
    $username = db_escape($username);
    $res = mysqli_query($GLOBALS['db_conn'], "SELECT * FROM sp_user WHERE NIK = '$username' LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $sp_user = mysqli_fetch_assoc($res);
        
        // Cari peran/role dari sp_usermenu
        $grup_query = "SELECT id_usergrup FROM sp_usermenu WHERE nik = '$username' ORDER BY id_usergrup ASC";
        $grup_res = mysqli_query($GLOBALS['db_conn'], $grup_query);
        $role = 'staff';
        if ($grup_res) {
            while ($grow = mysqli_fetch_assoc($grup_res)) {
                if ((int)$grow['id_usergrup'] == 2) {
                    $role = 'direktur';
                    break;
                }
            }
        }
        
        return array(
            'id'       => (int)$sp_user['id'],
            'nama'     => $sp_user['NamaUser'],
            'username' => $sp_user['NIK'],
            'role'     => $role
        );
    }
    return null;
}

/**
 * Ambil data pengguna berdasarkan ID
 */
function db_get_user_by_id($id) {
    $id = (int)$id;
    $res = mysqli_query($GLOBALS['db_conn'], "SELECT * FROM sp_user WHERE id = $id LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $sp_user = mysqli_fetch_assoc($res);
        
        // Cari peran/role dari sp_usermenu
        $username = $sp_user['NIK'];
        $grup_query = "SELECT id_usergrup FROM sp_usermenu WHERE nik = '$username' ORDER BY id_usergrup ASC";
        $grup_res = mysqli_query($GLOBALS['db_conn'], $grup_query);
        $role = 'staff';
        if ($grup_res) {
            while ($grow = mysqli_fetch_assoc($grup_res)) {
                if ((int)$grow['id_usergrup'] == 2) {
                    $role = 'direktur';
                    break;
                }
            }
        }
        
        return array(
            'id'       => (int)$sp_user['id'],
            'nama'     => $sp_user['NamaUser'],
            'username' => $sp_user['NIK'],
            'role'     => $role
        );
    }
    return null;
}

/**
 * Autentikasi user:
 * - Cek password dari hrd.datadasar
 * - Verifikasi keaktifan karyawan di sp_user lokal
 */
function db_authenticate_user($username, $password) {
    $username = db_escape($username);
    $password = db_escape($password);
    $password_md5 = md5($password);

    // 1. Cek credentials di database HRD.datadasar
    $hrd_query = "SELECT * FROM hrd.datadasar WHERE NIP = '$username' AND (password = '$password' OR encrypt_pass = '$password_md5') LIMIT 1";
    $hrd_res = mysqli_query($GLOBALS['db_conn'], $hrd_query);
    if (!$hrd_res || mysqli_num_rows($hrd_res) == 0) {
        return null;
    }

    // 2. Cek keaktifan di sp_user lokal
    $res = mysqli_query($GLOBALS['db_conn'], "SELECT * FROM sp_user WHERE NIK = '$username' AND Status = 'Aktif' LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $sp_user = mysqli_fetch_assoc($res);
        
        // Cari peran/role dari sp_usermenu
        $grup_query = "SELECT id_usergrup FROM sp_usermenu WHERE nik = '$username' ORDER BY id_usergrup ASC";
        $grup_res = mysqli_query($GLOBALS['db_conn'], $grup_query);
        $role = 'staff';
        if ($grup_res) {
            while ($grow = mysqli_fetch_assoc($grup_res)) {
                if ((int)$grow['id_usergrup'] == 2) {
                    $role = 'direktur';
                    break;
                }
            }
        }
        
        return array(
            'id'       => (int)$sp_user['id'],
            'nama'     => $sp_user['NamaUser'],
            'username' => $sp_user['NIK'],
            'role'     => $role
        );
    }
    return null;
}

/**
 * Cari pengguna di tabel sp_user (untuk Portal Admin)
 */
function db_find_pengguna($username) {
    $username = db_escape($username);
    $res = mysqli_query($GLOBALS['db_conn'], "SELECT * FROM sp_user WHERE NIK = '$username' LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $sp_user = mysqli_fetch_assoc($res);
        
        // Cari peran/role dari sp_usermenu
        $grup_query = "SELECT id_usergrup FROM sp_usermenu WHERE nik = '$username' ORDER BY id_usergrup ASC";
        $grup_res = mysqli_query($GLOBALS['db_conn'], $grup_query);
        $role = 'staff';
        if ($grup_res) {
            while ($grow = mysqli_fetch_assoc($grup_res)) {
                if ((int)$grow['id_usergrup'] == 2) {
                    $role = 'direktur';
                    break;
                }
            }
        }
        
        return array(
            'id'       => (int)$sp_user['id'],
            'nama'     => $sp_user['NamaUser'],
            'username' => $sp_user['NIK'],
            'peran'    => $role
        );
    }
    return null;
}

// ============================================================
// SISTEM HAK AKSES (sp_usermenu & sp_usermenu_grup)
// Identik polanya dengan m_tarif: data.php, insert.php, delete.php
// ============================================================

/**
 * Ambil semua grup dari sp_usermenu_grup
 */
function db_get_all_grups() {
    $res = mysqli_query($GLOBALS['db_conn'], "SELECT id_usergrup, nama_grup FROM sp_usermenu_grup ORDER BY id_usergrup ASC");
    $list = array();
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $list[] = $row;
        }
    }
    return $list;
}

/**
 * Ambil semua grup yang dimiliki user (multi-role support)
 * Return: array of id_usergrup (integer)
 * Identik dengan query di m_tarif aksi_masuk.php: SELECT id_usergrup ... ORDER BY id_usergrup ASC
 */
function db_get_user_grups($username) {
    $username = db_escape($username);
    $res = mysqli_query($GLOBALS['db_conn'], "SELECT id_usergrup FROM sp_usermenu WHERE nik = '$username' ORDER BY id_usergrup ASC");
    $grups = array();
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $grups[] = (int)$row['id_usergrup'];
        }
    }
    return $grups;
}

/**
 * Berikan akses grup ke user — identik m_tarif/admin/insert.php
 */
function db_set_user_grup($username, $id_usergrup) {
    $username    = db_escape($username);
    $id_usergrup = (int)$id_usergrup;
    $sql = "INSERT INTO sp_usermenu (nik, id_usergrup, tgl_input) VALUES ('$username', $id_usergrup, NOW())";
    return (bool)mysqli_query($GLOBALS['db_conn'], $sql);
}

/**
 * Cabut akses grup dari user — identik m_tarif/admin/delete.php
 */
function db_remove_user_grup($username, $id_usergrup) {
    $username    = db_escape($username);
    $id_usergrup = (int)$id_usergrup;
    $sql = "DELETE FROM sp_usermenu WHERE nik = '$username' AND id_usergrup = $id_usergrup";
    return (bool)mysqli_query($GLOBALS['db_conn'], $sql);
}

/**
 * Ambil user + status akses setiap grup (untuk toggle di Portal Admin)
 */
function db_get_user_akses_with_grups($username) {
    $user = db_find_pengguna($username);
    if (!$user) return null;

    $grups_all      = db_get_all_grups();
    $grups_dimiliki = db_get_user_grups($username);

    $result = array();
    foreach ($grups_all as $g) {
        $g['has_access'] = in_array((int)$g['id_usergrup'], $grups_dimiliki);
        $result[] = $g;
    }
    return array('user' => $user, 'grups' => $result);
}

// ============================================================
// DASHBOARD STATISTIK
// ============================================================

/**
 * Statistik dashboard
 */
function db_get_dashboard_stats() {
    $stats = array(
        'count_draft'              => 0,
        'count_diajukan'           => 0,
        'count_review'             => 0,
        'count_acc'                => 0,
        'count_ditolak'            => 0,
        'total_value_month'        => 0.0,
        'pending_po_approvals'     => 0,
        'pending_payment_approvals'=> 0
    );

    $current_month = date('Y-m');

    $res = mysqli_query($GLOBALS['db_conn'], "SELECT status, COUNT(*) as qty FROM spu_h GROUP BY status");
    while ($res && $row = mysqli_fetch_assoc($res)) {
        if ($row['status'] == 'draft')    $stats['count_draft']    = (int)$row['qty'];
        if ($row['status'] == 'diajukan') { $stats['count_diajukan'] = (int)$row['qty']; $stats['pending_po_approvals'] = (int)$row['qty']; }
        if ($row['status'] == 'direview') $stats['count_review']   = (int)$row['qty'];
        if ($row['status'] == 'acc')      $stats['count_acc']      = (int)$row['qty'];
        if ($row['status'] == 'ditolak')  $stats['count_ditolak']  = (int)$row['qty'];
    }

    $res_val = mysqli_query($GLOBALS['db_conn'], "SELECT SUM(flag) as total FROM spu_h WHERE status = 'acc' AND DATE_FORMAT(tgl_sp, '%Y-%m') = '$current_month'");
    if ($res_val) {
        $row_val = mysqli_fetch_assoc($res_val);
        $stats['total_value_month'] = isset($row_val['total']) ? (float)$row_val['total'] : 0.0;
    }

    $res_pay = mysqli_query($GLOBALS['db_conn'], "SELECT COUNT(*) as qty FROM sp_pengajuan_pembayaran WHERE status = 'diajukan'");
    if ($res_pay) {
        $row_pay = mysqli_fetch_assoc($res_pay);
        $stats['pending_payment_approvals'] = (int)$row_pay['qty'];
    }

    return $stats;
}

// ============================================================
// SURAT PESANAN (PO)
// ============================================================

/**
 * Daftar surat pesanan dengan filter opsional
 */
function db_get_purchase_orders($status = '', $vendor = '', $tgl_mulai = '', $tgl_selesai = '') {
    $where = array("1=1");
    if ($status    !== '') $where[] = "po.status = '"    . db_escape($status)     . "'";
    if ($vendor    !== '') $where[] = "po.namasup LIKE '%" . db_escape($vendor) . "%'";
    if ($tgl_mulai !== '') $where[] = "po.tgl_sp >= '" . db_escape($tgl_mulai) . "'";
    if ($tgl_selesai !== '') $where[] = "po.tgl_sp <= '" . db_escape($tgl_selesai) . "'";

    $where_clause = implode(" AND ", $where);

    $query = "SELECT po.id, po.status, po.dibuat_oleh, po.dibuat_pada, 
                     po.no_sp, po.no_sp as no_pesanan, po.tgl_sp as tgl_pesanan, po.namasup as nama_vendor,
                     (po.flag + po.potongan) as harga_vendor, po.potongan as diskon_vendor, po.flag as total_setelah_diskon,
                     COALESCE(u.NamaUser, po.user) as pembuat_nama,
                     (SELECT pr.status FROM sp_pengajuan_pembayaran pr WHERE pr.surat_pesanan_id = po.id ORDER BY pr.id DESC LIMIT 1) as status_bayar
              FROM spu_h po
              LEFT JOIN sp_user u ON po.dibuat_oleh = u.id
              WHERE $where_clause
              ORDER BY po.id DESC";

    $res  = mysqli_query($GLOBALS['db_conn'], $query);
    $list = array();
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $row['status_bayar'] = isset($row['status_bayar']) ? $row['status_bayar'] : 'belum_bayar';
            $list[] = $row;
        }
    }
    return $list;
}

/**
 * Detail satu surat pesanan berdasarkan ID
 */
function db_get_purchase_order_by_id($id) {
    $id = (int)$id;
    $query = "SELECT po.id, po.status, po.dibuat_oleh, po.dibuat_pada, 
                     (SELECT no_sp FROM spu_d WHERE id_header = po.id LIMIT 1) as no_sp, (SELECT no_sp FROM spu_d WHERE id_header = po.id LIMIT 1) as no_pesanan, po.tgl_sp as tgl_pesanan, po.namasup as nama_vendor,
                     (po.flag + po.potongan) as harga_vendor, po.potongan as diskon_vendor, po.flag as total_setelah_diskon,
                     COALESCE(u.NamaUser, po.user) as pembuat_nama
              FROM spu_h po
              LEFT JOIN sp_user u ON po.dibuat_oleh = u.id
              WHERE po.id = $id LIMIT 1";
    $res = mysqli_query($GLOBALS['db_conn'], $query);
    if ($res && mysqli_num_rows($res) > 0) {
        $po = mysqli_fetch_assoc($res);

        $pay_res = mysqli_query($GLOBALS['db_conn'], "SELECT * FROM sp_pengajuan_pembayaran WHERE surat_pesanan_id = $id ORDER BY id DESC LIMIT 1");
        $po['payment_request'] = null;
        if ($pay_res && mysqli_num_rows($pay_res) > 0) {
            $pay = mysqli_fetch_assoc($pay_res);
            $pay['po_id'] = $pay['surat_pesanan_id'];
            $po['payment_request'] = $pay;
        }
        return $po;
    }
    return null;
}

/**
 * Item-item (barang) dalam satu surat pesanan
 */
function db_get_purchase_order_items($po_id) {
    $po_id = (int)$po_id;
    $query = "SELECT poi.id, poi.id_header as surat_pesanan_id, poi.no_sp,
              poi.barang as nama_barang, poi.model, poi.merk, poi.spec,
              poi.harga as harga_satuan, poi.qty as jumlah, poi.satuan, poi.disc as diskon_item, poi.total as subtotal, poi.status_terima,
              (SELECT COALESCE(SUM(gr.jumlah_diterima), 0) FROM sp_penerimaan_barang gr WHERE gr.detail_surat_pesanan_id = poi.id) as jumlah_diterima
              FROM spu_d poi
              WHERE poi.id_header = $po_id";
    $res   = mysqli_query($GLOBALS['db_conn'], $query);
    $items = array();
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $row['po_id']           = $row['surat_pesanan_id'];
            $row['jumlah_diterima'] = (int)$row['jumlah_diterima'];

            $gr_res = mysqli_query($GLOBALS['db_conn'],
                "SELECT gr.*, u.NamaUser as checker_nama
                 FROM sp_penerimaan_barang gr
                 JOIN sp_user u ON gr.dicek_oleh = u.id
                 WHERE gr.detail_surat_pesanan_id = " . (int)$row['id'] . " ORDER BY gr.id ASC");
            $row['receipts'] = array();
            if ($gr_res) {
                while ($gr_row = mysqli_fetch_assoc($gr_res)) {
                    $gr_row['po_item_id'] = $gr_row['detail_surat_pesanan_id'];
                    $row['receipts'][] = $gr_row;
                }
            }
            $items[] = $row;
        }
    }
    return $items;
}

/**
 * Generate nomor surat pesanan unik (SP/YYYY/MM/NNNN)
 */
function db_generate_po_number() {
    $prefix = "SP/" . date('Y') . "/" . date('m') . "/";
    $res = mysqli_query($GLOBALS['db_conn'], "SELECT no_sp as no_pesanan FROM spu_h WHERE no_sp LIKE '$prefix%' ORDER BY no_sp DESC LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $row      = mysqli_fetch_assoc($res);
        $next_num = (int)substr($row['no_pesanan'], -4) + 1;
    } else {
        $next_num = 1;
    }
    return $prefix . sprintf('%04d', $next_num);
}

/**
 * Simpan surat pesanan baru beserta item-itemnya
 */
function db_create_purchase_order($no_pesanan, $tgl_pesanan, $nama_vendor, $harga_vendor, $diskon_vendor, $total_setelah_diskon, $status, $dibuat_oleh, $items, $extra = array()) {
    $no_pesanan          = db_escape($no_pesanan);
    $tgl_pesanan         = db_escape($tgl_pesanan);
    $nama_vendor         = db_escape($nama_vendor);
    $harga_vendor        = (float)$harga_vendor;
    $diskon_vendor       = (float)$diskon_vendor;
    $total_setelah_diskon= (float)$total_setelah_diskon;
    $status              = db_escape($status);
    $dibuat_oleh         = (int)$dibuat_oleh;

    $no_permintaan  = db_escape(isset($extra['no_permintaan'])  ? $extra['no_permintaan']  : '');
    $nama_lampiran  = db_escape(isset($extra['nama_lampiran'])  ? $extra['nama_lampiran']  : '');
    $no_tawar       = db_escape(isset($extra['no_tawar'])       ? $extra['no_tawar']       : '');
    $tgl_tawar      = db_escape(isset($extra['tgl_tawar'])      ? $extra['tgl_tawar']      : '');
    $pembayaran     = db_escape(isset($extra['pembayaran'])     ? $extra['pembayaran']     : '');
    $pembayaran1    = db_escape(isset($extra['pembayaran1'])    ? $extra['pembayaran1']    : '');
    $notein         = db_escape(isset($extra['notein'])         ? $extra['notein']         : '');
    $unit           = db_escape(isset($extra['unit'])           ? $extra['unit']           : '');
    $tglkirim       = db_escape(isset($extra['tglkirim'])       ? $extra['tglkirim']       : '');
    $ppn_val        = (float)(isset($extra['ppn'])              ? $extra['ppn']            : 0);

    mysqli_begin_transaction($GLOBALS['db_conn']);
    try {
        $user_nik = '';
        $user_res = mysqli_query($GLOBALS['db_conn'], "SELECT NIK FROM sp_user WHERE id = $dibuat_oleh LIMIT 1");
        if ($user_res && mysqli_num_rows($user_res) > 0) {
            $r = mysqli_fetch_assoc($user_res);
            $user_nik = $r['NIK'];
        }

        $kodesup = '';
        $askes_conn = isset($GLOBALS['askes_conn']) ? $GLOBALS['askes_conn'] : null;
        if ($askes_conn) {
            $esc_nama = db_escape($nama_vendor);
            $sup_res = mysqli_query($askes_conn, "SELECT KodeSupplier FROM m_supplier WHERE TRIM(NamaSupplier) = '$esc_nama' LIMIT 1");
            if ($sup_res && mysqli_num_rows($sup_res) > 0) {
                $sup_row = mysqli_fetch_assoc($sup_res);
                $kodesup = trim($sup_row['KodeSupplier']);
            }
        }

        $tgl_tawar_val  = ($tgl_tawar  !== '') ? "'$tgl_tawar'"  : "'1900-01-01'";
        $tglkirim_val   = ($tglkirim   !== '') ? "'$tglkirim'"   : "'1900-01-01'";

        $q = "INSERT INTO spu_h (no_sp, no_permintaan, nama_lampiran, tgl_sp, namasup, kodesup,
                                  no_tawar, tgl_tawar, pembayaran, pembayaran1, notein,
                                  unit, tglkirim, ppn, flag, potongan,
                                  status, dibuat_oleh, dibuat_pada, user, created_at)
              VALUES ('$no_pesanan','$no_permintaan','$nama_lampiran','$tgl_pesanan','$nama_vendor','$kodesup',
                      '$no_tawar',$tgl_tawar_val,'$pembayaran','$pembayaran1','$notein',
                      '$unit',$tglkirim_val,$ppn_val,$total_setelah_diskon,$diskon_vendor,
                      '$status',$dibuat_oleh,NOW(),'$user_nik',NOW())";
        if (!mysqli_query($GLOBALS['db_conn'], $q)) throw new Exception(mysqli_error($GLOBALS['db_conn']));
        $po_id = mysqli_insert_id($GLOBALS['db_conn']);

        foreach ($items as $item) {
            $nama_b   = db_escape($item['nama_barang']);
            $merk     = db_escape(isset($item['merk'])   ? $item['merk']   : '');
            $model    = db_escape(isset($item['model'])  ? $item['model']  : '');
            $spec     = db_escape(isset($item['spec'])   ? $item['spec']   : '');
            $satuan   = db_escape(isset($item['satuan']) ? $item['satuan'] : 'pcs');
            $qty      = (float)$item['jumlah'];
            $harga_satuan = (float)$item['harga_satuan'];
            $disc_item    = (float)(isset($item['disc']) ? $item['disc'] : 0);
            $subtotal     = (float)$item['subtotal'];
            $iq = "INSERT INTO spu_d (id_header, no_sp, barang, merk, model, spec, harga, qty, satuan, disc, total, status_terima, created_at)
                   VALUES ($po_id,'$no_pesanan','$nama_b','$merk','$model','$spec',$harga_satuan,$qty,'$satuan',$disc_item,$subtotal,'belum_datang',NOW())";
            if (!mysqli_query($GLOBALS['db_conn'], $iq)) throw new Exception(mysqli_error($GLOBALS['db_conn']));
        }

        $note = $status === 'diajukan' ? 'Permintaan diajukan ke Direktur.' : 'Draft disimpan.';
        mysqli_query($GLOBALS['db_conn'], "INSERT INTO sp_log_persetujuan (surat_pesanan_id, jenis, status, catatan, oleh, tanggal) VALUES ($po_id,'permintaan','$status','$note',$dibuat_oleh,NOW())");

        mysqli_commit($GLOBALS['db_conn']);
        return $po_id;
    } catch (Exception $e) {
        mysqli_rollback($GLOBALS['db_conn']);
        return false;
    }
}



/**
 * Ambil log persetujuan surat pesanan
 */
function db_get_approval_logs($po_id) {
    $po_id = (int)$po_id;
    $query = "SELECT l.*, u.NamaUser as user_nama,
              (SELECT CASE WHEN id_usergrup = 2 THEN 'direktur' ELSE 'staff' END FROM sp_usermenu WHERE nik = u.NIK ORDER BY CASE WHEN id_usergrup = 2 THEN 0 ELSE 1 END LIMIT 1) as user_role
              FROM sp_log_persetujuan l
              JOIN sp_user u ON l.oleh = u.id
              WHERE l.surat_pesanan_id = $po_id
              ORDER BY l.id DESC";
    $res  = mysqli_query($GLOBALS['db_conn'], $query);
    $logs = array();
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $row['po_id'] = $row['surat_pesanan_id'];
            $logs[] = $row;
        }
    }
    return $logs;
}

/**
 * Direktur: setujui atau tolak surat pesanan
 */
function db_approve_po_request($po_id, $status, $catatan, $user_id) {
    $po_id   = (int)$po_id;
    $status  = db_escape($status);
    $catatan = db_escape($catatan);
    $user_id = (int)$user_id;

    mysqli_begin_transaction($GLOBALS['db_conn']);
    try {
        if (!mysqli_query($GLOBALS['db_conn'], "UPDATE spu_h SET status = '$status' WHERE id = $po_id")) throw new Exception(mysqli_error($GLOBALS['db_conn']));
        mysqli_query($GLOBALS['db_conn'], "INSERT INTO sp_log_persetujuan (surat_pesanan_id, jenis, status, catatan, oleh, tanggal) VALUES ($po_id,'permintaan','$status','$catatan',$user_id,NOW())");
        mysqli_commit($GLOBALS['db_conn']);
        return true;
    } catch (Exception $e) {
        mysqli_rollback($GLOBALS['db_conn']);
        return false;
    }
}

// ============================================================
// PENERIMAAN BARANG
// ============================================================

/**
 * Simpan penerimaan barang dan update status item
 */
function db_save_goods_receipt($po_item_id, $tgl_diterima, $jumlah_diterima, $keterangan, $user_id) {
    $po_item_id      = (int)$po_item_id;
    $tgl_diterima    = db_escape($tgl_diterima);
    $jumlah_diterima = (int)$jumlah_diterima;
    $keterangan      = db_escape($keterangan);
    $user_id         = (int)$user_id;

    mysqli_begin_transaction($GLOBALS['db_conn']);
    try {
        mysqli_query($GLOBALS['db_conn'],
            "INSERT INTO sp_penerimaan_barang (detail_surat_pesanan_id, tgl_diterima, jumlah_diterima, keterangan, dicek_oleh, dibuat_pada)
             VALUES ($po_item_id,'$tgl_diterima',$jumlah_diterima,'$keterangan',$user_id,NOW())");

        $item_row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db_conn'], "SELECT qty as jumlah FROM spu_d WHERE id = $po_item_id LIMIT 1"));
        $total_ordered = (int)$item_row['jumlah'];

        $gr_row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db_conn'], "SELECT COALESCE(SUM(jumlah_diterima),0) as total FROM sp_penerimaan_barang WHERE detail_surat_pesanan_id = $po_item_id"));
        $total_received = (int)$gr_row['total'];

        if ($total_received >= $total_ordered)     $st = 'lengkap';
        elseif ($total_received > 0)               $st = 'sebagian';
        else                                       $st = 'belum_datang';

        mysqli_query($GLOBALS['db_conn'], "UPDATE spu_d SET status_terima = '$st' WHERE id = $po_item_id");
        mysqli_commit($GLOBALS['db_conn']);
        return true;
    } catch (Exception $e) {
        mysqli_rollback($GLOBALS['db_conn']);
        return false;
    }
}

// ============================================================
// PENGAJUAN PEMBAYARAN
// ============================================================

/**
 * Buat pengajuan pembayaran baru
 */
function db_create_payment_request($po_id, $tgl_pengajuan, $nominal_diajukan, $user_id) {
    $po_id           = (int)$po_id;
    $tgl_pengajuan   = db_escape($tgl_pengajuan);
    $nominal_diajukan= (float)$nominal_diajukan;
    $user_id         = (int)$user_id;

    mysqli_begin_transaction($GLOBALS['db_conn']);
    try {
        mysqli_query($GLOBALS['db_conn'],
            "INSERT INTO sp_pengajuan_pembayaran (surat_pesanan_id, tgl_pengajuan, nominal_diajukan, status, diajukan_oleh, dibuat_pada)
             VALUES ($po_id,'$tgl_pengajuan',$nominal_diajukan,'diajukan',$user_id,NOW())");
        $pr_id = mysqli_insert_id($GLOBALS['db_conn']);
        mysqli_query($GLOBALS['db_conn'],
            "INSERT INTO sp_log_persetujuan (surat_pesanan_id, jenis, status, catatan, oleh, tanggal)
             VALUES ($po_id,'pembayaran','diajukan','Pengajuan pembayaran.',$user_id,NOW())");
        mysqli_commit($GLOBALS['db_conn']);
        return $pr_id;
    } catch (Exception $e) {
        mysqli_rollback($GLOBALS['db_conn']);
        return false;
    }
}

/**
 * Daftar semua pengajuan pembayaran
 */
function db_get_payment_requests() {
    $query = "SELECT pr.*, po.no_sp, po.no_sp as no_pesanan, po.namasup as nama_vendor, po.flag as total_setelah_diskon, u.NamaUser as pengaju_nama
              FROM sp_pengajuan_pembayaran pr
              JOIN spu_h po ON pr.surat_pesanan_id = po.id
              JOIN sp_user u ON pr.diajukan_oleh = u.id
              ORDER BY pr.id DESC";
    $res  = mysqli_query($GLOBALS['db_conn'], $query);
    $list = array();
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $row['po_id'] = $row['surat_pesanan_id'];
            $list[] = $row;
        }
    }
    return $list;
}

/**
 * Direktur: setujui atau tolak pengajuan pembayaran
 */
function db_approve_payment_request($payment_id, $status, $catatan, $user_id) {
    $payment_id = (int)$payment_id;
    $status     = db_escape($status);
    $catatan    = db_escape($catatan);
    $user_id    = (int)$user_id;

    mysqli_begin_transaction($GLOBALS['db_conn']);
    try {
        $pr_row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db_conn'], "SELECT surat_pesanan_id FROM sp_pengajuan_pembayaran WHERE id = $payment_id LIMIT 1"));
        $po_id  = (int)$pr_row['surat_pesanan_id'];
        $tgl_acc = ($status === 'acc') ? "'" . date('Y-m-d') . "'" : "NULL";

        mysqli_query($GLOBALS['db_conn'],
            "UPDATE sp_pengajuan_pembayaran SET status = '$status', catatan_direktur = '$catatan', tgl_acc = $tgl_acc WHERE id = $payment_id");
        mysqli_query($GLOBALS['db_conn'],
            "INSERT INTO sp_log_persetujuan (surat_pesanan_id, jenis, status, catatan, oleh, tanggal)
             VALUES ($po_id,'pembayaran','$status','$catatan',$user_id,NOW())");

        mysqli_commit($GLOBALS['db_conn']);
        return true;
    } catch (Exception $e) {
        mysqli_rollback($GLOBALS['db_conn']);
        return false;
    }
}
