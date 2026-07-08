<?php
require_once '../../config/database.php';

// 1. Ambil SEMUA Master Menu sekaligus
$all_menus = array();
$sql_m = mysqli_query($conn, "SELECT * FROM sp_umum_menu ORDER BY level ASC, urut ASC");
while ($row_m = mysqli_fetch_assoc($sql_m)) {
    $parent = (int)$row_m['id_header'];
    $lvl    = (int)$row_m['level'];
    if (!isset($all_menus[$lvl])) $all_menus[$lvl] = array();
    if (!isset($all_menus[$lvl][$parent])) $all_menus[$lvl][$parent] = array();
    $all_menus[$lvl][$parent][] = $row_m;
}

// 2. Ambil Master Grup
$qry_usergrup = "SELECT id_usergrup, nama_grup FROM sp_usermenu_grup ORDER BY id_usergrup ASC";
$sql_usergrup = mysqli_query($conn, $qry_usergrup);

$tabhead = '';
$tabbody = '';
$index = 0;

if ($sql_usergrup) {
    while ($row_usergrup = mysqli_fetch_assoc($sql_usergrup)) {
        $id_usergrup = $row_usergrup['id_usergrup'];
        $grup_usergrup = $row_usergrup['nama_grup'];
        $active = ($index == 0) ? 'active' : '';

        // Ambil semua akses grup ini sekaligus
        $group_access = array();
        $sql_acc = mysqli_query($conn, "SELECT id_menu FROM sp_umum_grupakses WHERE id_usergrup = '$id_usergrup'");
        if ($sql_acc) {
            while ($row_acc = mysqli_fetch_assoc($sql_acc)) {
                $group_access[] = $row_acc['id_menu'];
            }
        }

        // Tab Navigasi
        $tabhead .= '<a class="nav-item nav-link '.$active.'" id="nav-'.$id_usergrup.'-tab" data-toggle="tab" href="#nav-'.$id_usergrup.'" role="tab">'.$grup_usergrup.'</a>';

        // Fix duplicate tabs visible issue: use 'show active' ONLY if active, otherwise just 'fade'
        $active_pane = ($index == 0) ? 'show active' : '';
        $tabbody .= '<div class="tab-pane fade '.$active_pane.'" id="nav-'.$id_usergrup.'" role="tabpanel">';
        $tabbody .= '<div class="tree-container p-3 bg-white border"><ul class="list-unstyled">';

        // --- LEVEL 1 (Menu Utama) ---
        if (isset($all_menus[1][0])) {
            foreach ($all_menus[1][0] as $row_l1) {
                $id_l1 = $row_l1['id'];
                $checked1 = in_array($id_l1, $group_access) ? 'checked' : '';
                
                $tabbody .= '<li class="mb-2"><div class="custom-control custom-checkbox">';
                $tabbody .= '<input type="checkbox" class="custom-control-input menu_cek" id="m'.$id_usergrup.'_'.$id_l1.'" usergrup="'.$id_usergrup.'" menu_id="'.$id_l1.'" '.$checked1.'>';
                $tabbody .= '<label class="custom-control-label font-weight-bold" for="m'.$id_usergrup.'_'.$id_l1.'"><i class="bi bi-folder"></i> '.$row_l1['urut'].'. '.htmlspecialchars($row_l1['menu']).'</label>';
                $tabbody .= '</div><ul class="ml-4 list-unstyled border-left">';

                // --- LEVEL 2 (Sub Menu) ---
                if (isset($all_menus[2][$id_l1])) {
                    foreach ($all_menus[2][$id_l1] as $row_l2) {
                        $id_l2 = $row_l2['id'];
                        $checked2 = in_array($id_l2, $group_access) ? 'checked' : '';

                        $tabbody .= '<li class="my-1"><div class="custom-control custom-checkbox">';
                        $tabbody .= '<input type="checkbox" class="custom-control-input menu_cek" id="m'.$id_usergrup.'_'.$id_l2.'" usergrup="'.$id_usergrup.'" menu_id="'.$id_l2.'" '.$checked2.'>';
                        $tabbody .= '<label class="custom-control-label" for="m'.$id_usergrup.'_'.$id_l2.'">'.$row_l2['urut'].'. '.htmlspecialchars($row_l2['menu']).'</label>';
                        $tabbody .= '</div><ul class="ml-4 list-unstyled border-left">';

                        // --- LEVEL 3 (Action/Tombol) ---
                        if (isset($all_menus[3][$id_l2])) {
                            foreach ($all_menus[3][$id_l2] as $row_l3) {
                                $id_l3 = $row_l3['id'];
                                $checked3 = in_array($id_l3, $group_access) ? 'checked' : '';

                                $tabbody .= '<li class="py-1"><div class="custom-control custom-checkbox">';
                                $tabbody .= '<input type="checkbox" class="custom-control-input menu_cek" id="m'.$id_usergrup.'_'.$id_l3.'" usergrup="'.$id_usergrup.'" menu_id="'.$id_l3.'" '.$checked3.'>';
                                $tabbody .= '<label class="custom-control-label text-info" for="m'.$id_usergrup.'_'.$id_l3.'"><i class="bi bi-dot"></i> '.htmlspecialchars($row_l3['menu']).'</label>';
                                $tabbody .= '</div></li>';
                            }
                        }
                        $tabbody .= '</ul></li>';
                    }
                }
                $tabbody .= '</ul></li>';
            }
        } else {
            $tabbody .= '<li><div class="alert alert-warning">Belum ada data struktur menu di tabel <b>sp_umum_menu</b>.</div></li>';
        }
        $tabbody .= '</ul></div></div>';
        $index++;
    }
}
?>

<nav><div class="nav nav-tabs shadow-sm" id="nav-tab" role="tablist"><?php echo $tabhead; ?></div></nav>
<div class="tab-content pt-2" id="nav-tabContent"><?php echo $tabbody; ?></div>

<script type="text/javascript">
$(document).ready(function() {
    $(document).off('change', '.menu_cek').on('change', '.menu_cek', function() {
        var $el = $(this);
        var id_usergrup = $el.attr('usergrup');
        var id_menu = $el.attr('menu_id');
        var is_checked = $el.prop('checked');

        var target_url = is_checked ? 'insert_grup.php' : 'delete_grup.php';

        $.ajax({
            url: target_url,
            type: 'POST',
            data: { id_usergrup: id_usergrup, id_menu: id_menu },
            success: function(res) {
                if (typeof $.notify === "function") {
                    $el.parent().notify((is_checked ? "Akses Disimpan" : "Akses Dihapus"), 
                        { position: "right", className: (is_checked ? "success" : "info"), autoHideDelay: 1500 });
                }
            }
        });
    });
});
</script>
