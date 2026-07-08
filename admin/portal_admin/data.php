<?php
/**
 * PROJECT: Surat Pesanan - Portal Admin
 * File: data.php
 * Deskripsi: Menampilkan nama karyawan dan daftar checklist hak akses personal (Kompatibel PHP 5.4)
 */
require_once '../../config/database.php';

$id_nik = isset($_POST['id_nik']) ? trim($_POST['id_nik']) : '';

if (empty($id_nik)) {
    die("<div class='alert alert-warning'>Username tidak boleh kosong.</div>");
}

/**
 * 1. PENCARIAN NAMA
 */
$hrd_conn = $GLOBALS['hrd_conn'];
if (!$hrd_conn) {
    die("<div class='alert alert-danger'>Koneksi ke database HRD gagal.</div>");
}

$qry_nama = "SELECT Nama FROM datadasar WHERE NIP = ? AND Keaktifan IN ('Ya', 'Y')";
$stmt_nama = mysqli_prepare($hrd_conn, $qry_nama);

if ($stmt_nama) {
    mysqli_stmt_bind_param($stmt_nama, "s", $id_nik);
    mysqli_stmt_execute($stmt_nama);
    mysqli_stmt_store_result($stmt_nama);
    
    if (mysqli_stmt_num_rows($stmt_nama) > 0) { 
        mysqli_stmt_bind_result($stmt_nama, $res_nama_karyawan);
        mysqli_stmt_fetch($stmt_nama);

        echo "<div class='alert alert-info py-2 mb-3 shadow-sm'><i class='bi bi-person-check-fill mr-2'></i><strong>" . htmlspecialchars($res_nama_karyawan) . "</strong></div>";

        /**
         * 2. AMBIL DAFTAR GRUP + STATUS AKSES (OPTIMASI JOIN)
         */
        $qry_grup = "SELECT g.id_usergrup as id, g.nama_grup as grup, 'Akses Surat Pesanan' as keterangan, m.id as access_id
                    FROM sp_usermenu_grup g 
                    LEFT JOIN sp_usermenu m ON g.id_usergrup = m.id_usergrup AND m.nik = ?
                    ORDER BY g.nama_grup ASC";
        
        $stmt_grup = mysqli_prepare($GLOBALS['db_conn'], $qry_grup);
        mysqli_stmt_bind_param($stmt_grup, "s", $id_nik);
        mysqli_stmt_execute($stmt_grup);
        mysqli_stmt_store_result($stmt_grup);
        mysqli_stmt_bind_result($stmt_grup, $b_id, $b_grup, $b_ket, $b_access);

        echo "<div class='px-2'><p class='mb-3 font-weight-bold text-secondary small'>CENTANG UNTUK MEMBERI AKSES:</p>";

        while (mysqli_stmt_fetch($stmt_grup)) { 
            $id_grup    = $b_id;
            $grup       = $b_grup;
            $keterangan = $b_ket;
            $is_checked = !empty($b_access) ? 'checked' : '';
    ?>
            <div class="custom-control custom-switch mb-3">
                <input type="checkbox" class="custom-control-input user_grup" 
                       id="grup_<?php echo $id_grup; ?>" 
                       data-id="<?php echo $id_grup; ?>" 
                       <?php echo $is_checked; ?>>
                <label class="custom-control-label cursor-pointer" for="grup_<?php echo $id_grup; ?>" style="cursor:pointer;">
                    <span class="font-weight-bold text-dark"><?php echo htmlspecialchars($grup); ?></span> 
                    <small class="text-muted d-block" style="font-size: 10px; line-height: 1.2;">
                        <?php echo htmlspecialchars($keterangan); ?>
                    </small>
                </label>
            </div>
    <?php
        }
        echo "</div>";
    } else {
        // Jika NIK tidak ditemukan di database HRD
        echo "<div class='alert alert-danger shadow-sm'>
                <i class='bi bi-exclamation-triangle mr-2'></i>
                Username <b>" . htmlspecialchars($id_nik) . "</b> tidak ditemukan atau tidak aktif di database HRD.
              </div>";
    }
    mysqli_stmt_close($stmt_nama);
} else {
    echo "Gagal menyiapkan query: " . mysqli_error($hrd_conn);
}
?>

<script type="text/javascript">
$(document).ready(function() {
    // Gunakan .off() agar event tidak terdaftar berulang kali saat AJAX reload
    $('.user_grup').off('change').on('change', function() {
        var $checkbox = $(this);
        var id_nik = $('#id_nik').val().trim(); 
        var id_usergrup = $(this).data('id');
        var is_checked = $(this).prop('checked');
        
        var target_url = is_checked ? 'insert.php' : 'delete.php';

        $.ajax({
            url: target_url,
            type: 'POST',
            data: {
                id_nik: id_nik, 
                id_usergrup: id_usergrup 
            },
            success: function(response) {
                if (typeof $.notify === "function") {
                    $checkbox.parent().notify(
                        (is_checked ? "Akses Aktif" : "Akses Dicabut"), 
                        { 
                            position: "right",
                            className: (is_checked ? "success" : "info"),
                            autoHideDelay: 1000,
                        }
                    );
                }
            },
            error: function(xhr) {
                $checkbox.prop('checked', !is_checked);
                if (typeof $.notify === "function") {
                    $.notify("Gagal memperbarui database", "error");
                }
            }
        });
    });
});
</script>
