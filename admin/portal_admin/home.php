<?php
/**
 * Portal Admin Surat Pesanan
 * File: admin/portal_admin/home.php
 * Identik tampilannya dengan m_tarif/admin/home.php
 * Panel admin: topbar + grid 2 kolom (Grup Otoritas + Set Role User)
 */

if (session_id() == '') {
    session_start();
}

// Cek akses admin
$is_admin = false;
if (isset($_SESSION['sp_admin_username'])) {
    $grups_admin = isset($_SESSION['sp_admin_multigrup']) ? $_SESSION['sp_admin_multigrup'] : array();
    if (in_array(1, $grups_admin)) {
        $is_admin = true;
    }
}

if (!$is_admin) {
    header("Location: index.html?pesan=akses_ditolak");
    exit();
}

require_once dirname(__FILE__) . '/../../config/database.php';

$nama_admin = isset($_SESSION['sp_admin_nama'])     ? $_SESSION['sp_admin_nama']     : 'Administrator';
$nik_admin  = isset($_SESSION['sp_admin_username']) ? $_SESSION['sp_admin_username'] : '-';
$initial    = strtoupper(substr($nama_admin, 0, 1));

// Ambil semua grup untuk ditampilkan di panel kiri
$semua_grup = db_get_all_grups();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal &mdash; Surat Pesanan IT Dept</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:      #1d4ed8;
            --primary-mid:  #2563eb;
            --primary-soft: #dbeafe;
            --primary-bg:   #eff6ff;
            --indigo:       #4f46e5;
            --amber:        #d97706;
            --danger:       #dc2626;
            --bg:           #f3f7ff;
            --surface:      #ffffff;
            --border:       #e9edf5;
            --text:         #0f172a;
            --muted:        #64748b;
            --light:        #94a3b8;
        }

        body { background: var(--bg); font-family: 'Inter', sans-serif; color: var(--text); min-height: 100vh; display: flex; flex-direction: column; }

        /* ===== TOP BAR ===== */
        .admin-topbar {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 200;
            box-shadow: 0 1px 8px rgba(37,99,235,0.05);
        }

        .topbar-brand { display: flex; align-items: center; gap: 13px; text-decoration: none; }
        .topbar-logo { width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, var(--primary-soft), var(--primary-bg)); border: 1px solid #bfdbfe; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(29,78,216,0.1); }
        .topbar-logo img { width: 26px; }
        .topbar-brand-text { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.1rem; color: var(--text); }
        .topbar-brand-sub { font-size: 0.68rem; color: var(--light); font-weight: 500; }
        .topbar-right { display: flex; align-items: center; gap: 14px; }

        .admin-badge-pill { display: inline-flex; align-items: center; gap: 7px; background: var(--primary-bg); border: 1px solid #bfdbfe; border-radius: 50px; padding: 5px 14px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--primary-mid); }
        .admin-badge-dot { width: 6px; height: 6px; background: var(--primary-mid); border-radius: 50%; animation: blink 2s ease-in-out infinite; }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }

        .user-info-block { text-align: right; }
        .user-info-name { font-size: 0.85rem; font-weight: 700; color: var(--text); }
        .user-info-nik { font-size: 0.7rem; color: var(--light); }

        .user-avatar-sm { width: 38px; height: 38px; background: linear-gradient(135deg, var(--primary-mid), var(--indigo)); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-family: 'Outfit', sans-serif; font-weight: 800; color: white; font-size: 1rem; flex-shrink: 0; }

        .btn-topbar-logout { display: inline-flex; align-items: center; gap: 7px; background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 10px; padding: 7px 14px; font-size: 0.8rem; font-weight: 700; color: var(--danger); cursor: pointer; transition: all 0.25s ease; }
        .btn-topbar-logout:hover { background: var(--danger); color: white; border-color: var(--danger); }

        /* ===== MAIN ===== */
        .admin-main { padding: 2rem; flex: 1; }
        .page-header { margin-bottom: 1.8rem; }
        .page-header h1 { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.5rem; color: var(--text); letter-spacing: -0.3px; margin-bottom: 0.2rem; }
        .page-header p { font-size: 0.84rem; color: var(--muted); }

        /* ===== GRID ===== */
        .admin-grid { display: grid; grid-template-columns: 1fr 420px; gap: 1.5rem; }

        /* ===== CARDS ===== */
        .admin-card { background: var(--surface); border: 1px solid var(--border); border-radius: 20px; overflow: hidden; box-shadow: 0 4px 16px rgba(37,99,235,0.05); }
        .admin-card-header { padding: 1.2rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 12px; }
        .card-header-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
        .card-header-title { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 0.95rem; color: var(--text); }
        .card-header-sub { font-size: 0.74rem; color: var(--muted); font-weight: 500; }
        .admin-card-body { padding: 1.5rem; }

        /* Grup list */
        .grup-list-item { display: flex; align-items: center; gap: 14px; padding: 14px 16px; background: var(--bg); border: 1px solid var(--border); border-radius: 14px; margin-bottom: 10px; }
        .grup-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
        .grup-name { font-weight: 700; font-size: 0.88rem; color: var(--text); }
        .grup-desc { font-size: 0.72rem; color: var(--muted); }
        .grup-badge { margin-left: auto; display: inline-flex; align-items: center; gap: 5px; background: var(--primary-bg); border: 1px solid #bfdbfe; border-radius: 50px; padding: 3px 10px; font-size: 0.68rem; font-weight: 700; color: var(--primary-mid); }

        /* ===== SEARCH BOX ===== */
        .search-wrap { position: relative; margin-bottom: 1.2rem; }
        .search-input { width: 100%; background: var(--bg); border: 1.5px solid var(--border); border-radius: 12px; padding: 12px 14px 12px 42px; font-size: 0.9rem; font-family: 'Inter', sans-serif; color: var(--text); transition: all 0.25s ease; outline: none; }
        .search-input::placeholder { color: var(--light); }
        .search-input:focus { background: white; border-color: var(--primary-mid); box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }
        .search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--light); font-size: 1rem; pointer-events: none; }
        .search-input:focus ~ .search-icon { color: var(--primary-mid); }

        .search-hint { background: var(--primary-bg); border: 1px solid var(--primary-soft); border-left: 3px solid var(--primary-mid); border-radius: 10px; padding: 0.7rem 1rem; font-size: 0.78rem; color: #1e40af; line-height: 1.5; }

        /* ===== RESULT AREA ===== */
        #id_div { margin-top: 1rem; }

        .spin-ring { width: 28px; height: 28px; border: 3px solid var(--primary-soft); border-top-color: var(--primary-mid); border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-row { display: flex; align-items: center; gap: 12px; padding: 1.2rem 0; color: var(--muted); font-size: 0.84rem; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1100px) { .admin-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<!-- ===== TOPBAR ===== -->
<header class="admin-topbar">
    <a href="home.php" class="topbar-brand">
        <div class="topbar-logo">
            <img src="../../assets/img/logo_rkz.png" alt="Logo RKZ">
        </div>
        <div>
            <div class="topbar-brand-text">Admin Portal</div>
            <div class="topbar-brand-sub">Surat Pesanan &mdash; IT Dept RKZ</div>
        </div>
    </a>

    <div class="topbar-right">
        <div class="admin-badge-pill d-none d-md-inline-flex">
            <div class="admin-badge-dot"></div>
            Admin IT
        </div>
        <div class="user-info-block d-none d-md-block">
            <div class="user-info-name"><?php echo htmlspecialchars($nama_admin); ?></div>
            <div class="user-info-nik">NIK: <?php echo htmlspecialchars($nik_admin); ?></div>
        </div>
        <div class="user-avatar-sm"><?php echo htmlspecialchars($initial); ?></div>
        <button class="btn-topbar-logout" onclick="confirmLogout()">
            <i class="bi bi-power"></i> Keluar
        </button>
    </div>
</header>

<!-- ===== MAIN ===== -->
<div class="admin-main">

    <div class="page-header">
        <h1><i class="bi bi-shield-lock me-2" style="color:#2563eb;"></i>Panel Administrasi</h1>
        <p>Kelola hak akses pengguna dan konfigurasi sistem Surat Pesanan</p>
    </div>

    <div class="admin-grid">

        <!-- Kolom 1: Grup Otoritas -->
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="card-header-icon" style="background:#eff6ff; color:#2563eb;">
                    <i class="bi bi-diagram-3-fill"></i>
                </div>
                <div>
                    <div class="card-header-title">Otoritas Grup Aplikasi</div>
                    <div class="card-header-sub">Daftar grup dan hak akses menu per grup</div>
                </div>
            </div>
            <div class="admin-card-body">
                <?php
                $grup_icons  = array(1 => 'bi-shield-check', 2 => 'bi-briefcase', 3 => 'bi-person-workspace');
                $grup_colors = array(1 => '#eff6ff;color:#2563eb', 2 => '#f5f3ff;color:#4f46e5', 3 => '#f0fdf4;color:#16a34a');
                $grup_descs  = array(
                    1 => 'Akses ke semua menu + Portal Admin',
                    2 => 'Approval SP & Pembayaran, Monitoring',
                    3 => 'Buat SP, Checklist Barang, Monitoring'
                );
                $menu_akses = array(
                    1 => 'Dashboard, Buat SP, Monitoring, Checklist, Pembayaran (semua)',
                    2 => 'Dashboard, Monitoring, Pembayaran & Review',
                    3 => 'Dashboard, Buat SP, Monitoring, Checklist Barang, Pembayaran'
                );
                foreach ($semua_grup as $g):
                    $gid = (int)$g['id_usergrup'];
                    $ic  = isset($grup_icons[$gid])  ? $grup_icons[$gid]  : 'bi-people';
                    $cl  = isset($grup_colors[$gid]) ? $grup_colors[$gid] : '#f8fafc;color:#64748b';
                    $ds  = isset($grup_descs[$gid])  ? $grup_descs[$gid]  : '';
                    $ma  = isset($menu_akses[$gid])  ? $menu_akses[$gid]  : '-';
                ?>
                <div class="grup-list-item">
                    <div class="grup-icon" style="background:<?php echo $cl; ?>;">
                        <i class="bi <?php echo $ic; ?>"></i>
                    </div>
                    <div>
                        <div class="grup-name"><?php echo htmlspecialchars($g['nama_grup']); ?></div>
                        <div class="grup-desc"><?php echo $ds; ?></div>
                        <div class="grup-desc mt-1" style="color:#2563eb; font-size:0.68rem;">
                            <i class="bi bi-check2-circle me-1"></i><?php echo $ma; ?>
                        </div>
                    </div>
                    <div class="grup-badge">
                        ID <?php echo $gid; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Kolom 2: Set Role User -->
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="card-header-icon" style="background:#f5f3ff; color:#4f46e5;">
                    <i class="bi bi-person-gear"></i>
                </div>
                <div>
                    <div class="card-header-title">Set Role User</div>
                    <div class="card-header-sub">Berikan atau cabut hak akses per karyawan</div>
                </div>
            </div>
            <div class="admin-card-body">
                <div class="search-wrap">
                    <input type="text" class="search-input" id="id_nik"
                           placeholder="Ketik Username/NIP lalu tekan Enter..." autocomplete="off">
                    <i class="bi bi-search search-icon" id="search-icon-el"></i>
                </div>

                <div class="search-hint">
                    <i class="bi bi-info-circle me-1"></i>
                    Masukkan Username atau NIP karyawan dan tekan <strong>Enter</strong> untuk mengelola hak akses.
                </div>

                <div id="id_div"></div>
            </div>
        </div>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js"></script>

<script>
function confirmLogout() {
    Swal.fire({
        title: 'Keluar dari Panel Admin?',
        text: 'Sesi administrasi Anda akan diakhiri.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Keluar',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            window.location = 'logout.php';
        }
    });
}

$('#id_nik').on('focus', function() {
    $('#search-icon-el').css('color', '#2563eb');
}).on('blur', function() {
    $('#search-icon-el').css('color', '');
});

$('#id_nik').on('keypress', function(event) {
    if (event.keyCode == 13) {
        var id_nik = $(this).val().trim();
        if (id_nik == '') return;

        $('#id_div').html(
            '<div class="loading-row"><div class="spin-ring"></div><span>Mencari data pengguna...</span></div>'
        );

        $.post('data.php', { id_nik: id_nik }, function(data) {
            $('#id_div').hide().html(data).fadeIn(300);
        }).fail(function() {
            $('#id_div').html('<div class="alert alert-danger small rounded-3 mt-2">Gagal menghubungi server.</div>');
        });
    }
});
</script>
</body>
</html>
