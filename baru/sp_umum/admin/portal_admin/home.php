<?php
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

$nama_admin = isset($_SESSION['sp_admin_nama']) ? $_SESSION['sp_admin_nama'] : 'Administrator';
$nik_admin  = isset($_SESSION['sp_admin_username']) ? $_SESSION['sp_admin_username'] : '-';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Admin Panel - Surat Pesanan</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link rel="icon" type="image/png" href="../../assets/img/logo_rkz.png">
    
    <!-- Bootstrap 4.6 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            font-size: 13px;
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        .navbar {
            backdrop-filter: blur(10px);
            background-color: rgba(23, 162, 184, 0.9) !important;
        }
        .navbar-brand {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .main-container {
            margin-top: 80px;
            padding: 0 30px;
        }
        .card-custom {
            background: white;
            padding: 20px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            min-height: 550px;
            transition: 0.3s;
            margin-bottom: 20px;
        }
        label b {
            color: #087990;
            display: block;
            margin-bottom: 15px;
            border-left: 4px solid #17a2b8;
            padding-left: 10px;
            text-transform: uppercase;
            font-size: 11px;
        }
        .input-group-text {
            border-radius: 8px 0 0 8px;
            background-color: #f8f9fa;
        }
        #id_nik {
            border-radius: 0 8px 8px 0;
            height: 40px;
            border-color: #dee2e6;
        }
        #id_nik:focus {
            box-shadow: none;
            border-color: #17a2b8;
        }
        .btn-logout {
            border-radius: 8px;
            padding: 5px 15px;
            transition: 0.2s;
        }
        .btn-logout:hover {
            transform: scale(1.05);
        }
        /* Style untuk Loading Spinner */
        .spinner-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 250px;
        }
        /* Tab Styling untuk data_jenis */
        .nav-tabs .nav-link.active {
            font-weight: bold;
            border-bottom: 3px solid #17a2b8;
        }
    </style>
</head>
<body>
    <nav class="navbar fixed-top navbar-dark bg-info shadow p-2">
        <div class="container-fluid">
            <a class="navbar-brand text-white d-flex align-items-center" href="home.php">
                <i class="bi bi-shield-check-fill mr-2" style="font-size: 20px;"></i>
                PANEL ADMINISTRATOR - SURAT PESANAN
            </a>
            <div class="d-flex align-items-center">
                <div class="text-right mr-3 border-right pr-3 d-none d-sm-block">
                    <span class="text-white-50 d-block small" style="line-height: 1;">Logged in as:</span>
                    <span class="text-white font-weight-bold small"><?php echo htmlspecialchars($nama_admin); ?> (<?php echo htmlspecialchars($nik_admin); ?>)</span>
                </div>
                <button type="button" class="btn btn-sm btn-danger btn-logout font-weight-bold shadow-sm"
                        onclick="confirmLogout()">
                    <i class="bi bi-power"></i> LOGOUT
                </button>
            </div>
        </div>
    </nav>

    <div class="container-fluid main-container">
        <div class="row">
            <div class="col-md-8">
                <div class="card-custom">
                    <label><b>KONFIGURASI HAK AKSES GRUP (MASTER ROLE)</b></label>
                    <div id="div_jenis">
                        <div class="spinner-container">
                            <div class="spinner-border text-info" role="status"></div>
                            <p class="mt-2 text-muted">Sinkronisasi struktur menu pusat...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-custom">
                    <label><b>MANAJEMEN USER PERSONAL</b></label>
                    <div class="input-group mb-4 shadow-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text border-right-0"><i class="bi bi-search text-info"></i></span>
                        </div>
                        <input type="text" class="form-control border-left-0" id="id_nik"
                               placeholder="Masukkan Username dan tekan Enter..."
                               autocomplete="off" autofocus>
                    </div>
                    
                    <div id="id_div">
                        <div class="alert alert-info border-0 text-center small shadow-sm" style="border-radius: 10px; background-color: #e7f3f5;">
                            <i class="bi bi-info-circle-fill mr-1 text-info"></i>
                            Silakan masukkan Username Karyawan untuk mengelola akses individu secara spesifik di luar grup.
                        </div>
                    </div>
                </div>
            </div>
        </div>      
    </div>

    <!-- Gunakan jQuery 3.5.1 (mirip SIAK) -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            load_grup_list();
        });

        // Logout dengan SweetAlert2
        function confirmLogout() {
            Swal.fire({
                title: 'Keluar dari Panel Admin?',
                text: "Pastikan semua perubahan hak akses telah disimpan.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = 'logout.php';
                }
            })
        }

        // Load data_jenis.php (Pohon Menu Grup)
        function load_grup_list() {
            $.get('data_jenis.php', function(data) {
                $('#div_jenis').hide().html(data).fadeIn();
            }).fail(function() {
                $('#div_jenis').html('<div class="alert alert-danger">Gagal memuat data grup. Periksa koneksi database.</div>');
            });
        }

        // Search NIK (data.php)
        $("#id_nik").on('keypress', function(event) {
            if (event.keyCode == 13) {
                var id_nik = $(this).val().trim();
                if(id_nik == "") {
                    if (typeof $.notify === "function") {
                        $.notify("Silakan masukkan Username dahulu", "warn");
                    }
                    return;
                }
                
                $('#id_div').html('<div class="spinner-container" style="height:100px"><div class="spinner-border text-info spinner-border-sm"></div><p class="mt-2 small text-muted">Mencari di database...</p></div>');
                
                $.post('data.php', {id_nik: id_nik}, function(data) {
                    $('#id_div').hide().html(data).fadeIn();
                }).fail(function() {
                    $('#id_div').html('<div class="alert alert-danger small">Gagal menghubungi file data.php</div>');
                });
            }
        });
    </script>
</body>
</html>
