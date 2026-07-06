<?php
/**
 * Portal Admin Surat Pesanan
 * File: admin/portal_admin/data.php
 * Identik polanya dengan m_tarif/admin/data.php
 * AJAX: cari user + tampilkan toggle switch per grup
 */

require_once dirname(__FILE__) . '/../../config/database.php';

$id_nik = isset($_POST['id_nik']) ? trim($_POST['id_nik']) : '';

if (empty($id_nik)) {
    die("<div class='alert alert-warning border-0 shadow-sm' style='border-radius:12px; font-size:0.85rem;'>Username tidak boleh kosong.</div>");
}

// 1. Cari user di tabel sp_user (identik dengan pencarian di m_tarif data.php: cari di HRD)
$user = db_find_pengguna($id_nik);

if (!$user) {
    echo "<div style='text-align:center; padding:3rem 1.5rem;'>
            <div style='width:64px; height:64px; background:#fef2f2; border-radius:20px; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;'>
                <i class='bi bi-person-x' style='font-size:1.8rem; color:#dc2626;'></i>
            </div>
            <h6 style='font-family:\"Outfit\", sans-serif; font-weight:800; color:#0f172a;'>User Tidak Ditemukan</h6>
            <p style='font-size:0.8rem; color:#64748b; margin:0;'>Username <strong>" . htmlspecialchars($id_nik) . "</strong> tidak ditemukan di database.</p>
          </div>";
    exit;
}

// 2. Tampilkan avatar + info user
$initial = strtoupper(substr($user['nama'], 0, 1));
echo "<div class='d-flex align-items-center p-3 mb-4' style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px;'>
        <div style='width:44px; height:44px; border-radius:12px; background: linear-gradient(135deg,#2563eb,#4f46e5); display:flex; align-items:center; justify-content:center; color:white; font-weight:800; font-family:\"Outfit\", sans-serif; box-shadow: 0 4px 10px rgba(37,99,235,0.15); flex-shrink:0;'>" . $initial . "</div>
        <div class='ms-3' style='overflow:hidden;'>
            <div style='font-weight:800; color:#0f172a; font-size:0.92rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;'>" . htmlspecialchars($user['nama']) . "</div>
            <div style='color:#64748b; font-size:0.75rem; font-weight:600;'>Username: " . htmlspecialchars($id_nik) . " &mdash; Role: " . htmlspecialchars($user['peran']) . "</div>
        </div>
      </div>";

// 3. Ambil semua grup + status akses user (identik dengan LEFT JOIN di m_tarif data.php)
$akses_data = db_get_user_akses_with_grups($id_nik);
$grups = $akses_data ? $akses_data['grups'] : array();

$grup_icons = array(1 => 'bi-shield-check', 2 => 'bi-briefcase', 3 => 'bi-person-workspace');

echo "<div class='px-1'>
        <p style='font-size:0.65rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:1.2px; margin-bottom:1rem;'>Pilih Role Surat Pesanan:</p>";

foreach ($grups as $g) {
    $gid        = (int)$g['id_usergrup'];
    $is_checked = $g['has_access'] ? 'checked' : '';
    $icon       = isset($grup_icons[$gid]) ? $grup_icons[$gid] : 'bi-people';
    ?>
    <div class="role-selector-item" style="display:flex; align-items:center; justify-content:space-between; padding:12px 14px; background:#fff; border:1.5px solid #f1f5f9; border-radius:14px; margin-bottom:10px; transition:all 0.2s ease;">
        <div style="display:flex; align-items:center; gap:12px; flex:1;">
            <div style="width:36px; height:36px; border-radius:10px; background:#eff6ff; display:flex; align-items:center; justify-content:center; color:#2563eb; flex-shrink:0;">
                <i class="bi <?php echo $icon; ?>"></i>
            </div>
            <label for="grup_<?php echo $gid; ?>" style="cursor:pointer; flex: 1; margin:0;">
                <div style="font-weight:700; color:#1e293b; font-size:0.85rem;"><?php echo htmlspecialchars($g['nama_grup']); ?></div>
                <div style="color:#94a3b8; font-size:0.7rem; font-weight:500;">Berikan akses sebagai <?php echo htmlspecialchars($g['nama_grup']); ?></div>
            </label>
        </div>
        <div class="form-check form-switch m-0 p-0">
            <input class="form-check-input user_grup" type="checkbox" role="switch"
                   id="grup_<?php echo $gid; ?>"
                   data-id="<?php echo $gid; ?>"
                   style="width: 2.8em; height: 1.4em; cursor:pointer;"
                   <?php echo $is_checked; ?>>
        </div>
    </div>
    <?php
}

echo "</div>";
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    // Styling hover effect (identik dengan m_tarif data.php)
    $('.role-selector-item').hover(function() {
        $(this).css({ 'border-color': '#bfdbfe', 'background': '#f0f9ff' });
    }, function() {
        if (!$(this).find('input').is(':checked')) {
            $(this).css({ 'border-color': '#f1f5f9', 'background': '#fff' });
        } else {
            $(this).css({ 'border-color': '#bfdbfe', 'background': '#f0f9ff' });
        }
    });

    // Initial state styling
    $('.role-selector-item').each(function() {
        if ($(this).find('input').is(':checked')) {
            $(this).css({ 'border-color': '#bfdbfe', 'background': '#f0f9ff' });
        }
    });

    // Toggle change handler (identik dengan m_tarif: call insert.php atau delete.php)
    $('.user_grup').off('change').on('change', function() {
        var $checkbox    = $(this);
        var $parent      = $checkbox.closest('.role-selector-item');
        var id_nik       = $('#id_nik').val().trim();
        var id_usergrup  = $(this).data('id');
        var is_checked   = $(this).prop('checked');
        var target_url   = is_checked ? 'insert.php' : 'delete.php';

        if (is_checked) {
            $parent.css({ 'border-color': '#bfdbfe', 'background': '#f0f9ff' });
        } else {
            $parent.css({ 'border-color': '#f1f5f9', 'background': '#fff' });
        }

        $.ajax({
            url: target_url,
            type: 'POST',
            data: { id_nik: id_nik, id_usergrup: id_usergrup },
            success: function(response) {
                $.notify(
                    (is_checked ? "✓ Akses Diberikan" : "✕ Akses Dicabut"),
                    { position: "bottom center", className: (is_checked ? "success" : "info") }
                );
            },
            error: function() {
                $checkbox.prop('checked', !is_checked);
                if (!is_checked) {
                    $parent.css({ 'border-color': '#bfdbfe', 'background': '#f0f9ff' });
                } else {
                    $parent.css({ 'border-color': '#f1f5f9', 'background': '#fff' });
                }
                $.notify("Gagal memperbarui data", "error");
            }
        });
    });
});
</script>
