<?php
// pages/users.php
if (!checkMenuAccess(99)) {
    echo "<div class='alert alert-error'>Access Denied: You do not have permission to view this page.</div>";
    exit;
}
?>
<style>
.sp-modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 1000;
    display: none;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
}
.sp-modal-content {
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    width: 100%;
    max-width: 500px;
}
.sp-modal-header {
    padding: 16px 24px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.sp-modal-body { padding: 24px; }
.sp-modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #f3f4f6;
    background-color: #f9fafb;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    border-radius: 0 0 12px 12px;
}
.checkbox-list label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    cursor: pointer;
    font-size: 14px;
}
.menu-badge {
    background: #e0f2fe;
    color: #0369a1;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    display: inline-block;
    margin: 2px;
    font-weight: 500;
}
.menu-badge.admin {
    background: #fce7f3;
    color: #be185d;
}
</style>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div class="section-title" style="margin-bottom: 0; border: none;">Manajemen Hak Akses User</div>
        <button class="btn btn-primary" onclick="showUserForm()"><i class="fas fa-plus"></i> Berikan Akses Baru</button>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="usersTable">
            <thead>
                <tr>
                    <th>NIK</th>
                    <th>Nama Karyawan</th>
                    <th>Hak Akses Menu</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data loaded via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form -->
<div id="userModal" class="sp-modal-overlay">
    <div class="sp-modal-content">
        <div class="sp-modal-header">
            <h3 id="userModalTitle" style="margin:0; font-size:18px;">Atur Hak Akses Karyawan</h3>
            <button class="btn btn-outline" style="padding: 5px 10px; border:none;" onclick="hideUserForm()"><i class="fas fa-times"></i></button>
        </div>
        <form id="userForm" onsubmit="saveUser(event)">
            <div class="sp-modal-body">
                
                <div class="form-group">
                    <label class="form-label">Cari Karyawan (NIK / Nama)</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="search_nik_input" class="form-control" placeholder="Ketik NIK atau Nama lalu Cari...">
                        <button type="button" class="btn btn-outline" onclick="searchKaryawan()"><i class="fas fa-search"></i> Cari</button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Pilih Karyawan <span style="color:red;">*</span></label>
                    <select id="user_nik" class="form-control" required>
                        <option value="">-- Hasil Pencarian Akan Muncul Disini --</option>
                    </select>
                </div>
                
                <div class="form-group" style="position: relative;">
                    <label class="form-label">Password <small id="pwdHelp" style="color:#6b7280; font-weight:normal;"></small></label>
                    <input type="password" id="user_password" class="form-control" placeholder="Ketik sandi rahasia..." style="padding-right: 40px;">
                    <span style="position: absolute; right: 15px; top: 38px; cursor: pointer; color: #666;" onclick="togglePwd()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </span>
                </div>

                <div class="form-group">
                    <label class="form-label">Akses Menu <span style="color:red;">*</span></label>
                    <div class="checkbox-list" style="background:#f9fafb; padding:12px; border-radius:6px; border:1px solid #e5e7eb;">
                        <label><input type="checkbox" name="menu_akses[]" value="3444"> 3444. Permohonan Pesanan</label>
                        <label><input type="checkbox" name="menu_akses[]" value="3445"> 3445. Persetujuan Pesanan</label>
                        <label><input type="checkbox" name="menu_akses[]" value="3443"> 3443. Monitoring Pesanan</label>
                        <label><input type="checkbox" name="menu_akses[]" value="3446"> 3446. Penerimaan Barang (SJ)</label>
                        <hr style="margin:10px 0; border:0; border-top:1px solid #d1d5db;">
                        <label style="color:#be185d; font-weight:bold;"><input type="checkbox" name="menu_akses[]" value="99"> 99. Administrator (Manajemen User)</label>
                    </div>
                </div>

            </div>
            <div class="sp-modal-footer">
                <button type="button" class="btn btn-outline" onclick="hideUserForm()">Batal</button>
                <button type="submit" class="btn btn-success" id="btnSaveUser"><i class="fas fa-save"></i> Simpan Akses</button>
            </div>
        </form>
    </div>
</div>

<script>
const menuNames = {
    "3444": "Permohonan Pesanan",
    "3445": "Persetujuan Pesanan",
    "3443": "Monitoring Pesanan",
    "3446": "Penerimaan Barang",
    "99": "Administrator"
};

function loadUsers() {
    $.ajax({
        url: 'api/users.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            let html = '';
            data.forEach(u => {
                let badges = '';
                if(u.menus && u.menus.length > 0) {
                    u.menus.forEach(m => {
                        let name = menuNames[m] || 'Menu ' + m;
                        let cls = (m === "99") ? 'menu-badge admin' : 'menu-badge';
                        badges += `<span class="${cls}">${name}</span>`;
                    });
                } else {
                    badges = '<span style="color:#9ca3af; font-size:12px; font-style:italic;">Tidak ada akses aktif</span>';
                }

                html += `<tr>
                    <td><strong>${u.NIK}</strong></td>
                    <td>${u.Nama || 'Unknown'}</td>
                    <td>${badges}</td>
                    <td class="text-center" style="white-space: nowrap;">
                        <button class="btn btn-primary" style="padding: 4px 8px; font-size: 12px;" onclick="editUser('${u.NIK}', '${u.Nama}', '${u.menus.join(',')}')" title="Edit Akses"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;" onclick="deleteUser('${u.NIK}', '${u.Nama}')" title="Cabut Semua Akses"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            });
            $('#usersTable tbody').html(html);
        }
    });
}

function searchKaryawan() {
    let q = $('#search_nik_input').val();
    if(!q || q.length < 2) return alert('Ketik minimal 2 huruf/angka.');
    
    $.get('api/users.php?search_nik=' + encodeURIComponent(q), function(res) {
        let html = '<option value="">-- Pilih Karyawan --</option>';
        res.forEach(r => {
            html += `<option value="${r.nik}">${r.nik} - ${r.nama}</option>`;
        });
        $('#user_nik').html(html);
        if(res.length === 1) {
            $('#user_nik').val(res[0].nik);
        } else if (res.length === 0) {
            alert('Karyawan tidak ditemukan di database (datadasar).');
        }
    }, 'json');
}

function showUserForm() {
    $('#userModalTitle').text('Tambah Akses Baru');
    $('#userForm')[0].reset();
    $('#user_nik').html('<option value="">-- Cari NIK Dahulu --</option>');
    $('#user_nik').prop('disabled', false);
    $('#search_nik_input').prop('disabled', false);
    $('#pwdHelp').text('(Wajib diisi untuk akun baru)');
    $('#user_password').prop('required', true);
    
    $('input[name="menu_akses[]"]').prop('checked', false);
    
    $('#userModal').css('display', 'flex');
}

function editUser(nik, nama, menusStr) {
    $('#userModalTitle').text('Edit Akses: ' + nama);
    $('#userForm')[0].reset();
    
    // Inject option and select it
    $('#user_nik').html(`<option value="${nik}">${nik} - ${nama}</option>`);
    $('#user_nik').val(nik);
    $('#user_nik').prop('disabled', true); // Don't let them change the NIK while editing
    $('#search_nik_input').prop('disabled', true);
    
    $('#pwdHelp').text('(Kosongkan jika tidak ingin ganti password)');
    $('#user_password').prop('required', false);
    
    $('input[name="menu_akses[]"]').prop('checked', false);
    if(menusStr) {
        let mArr = menusStr.split(',');
        mArr.forEach(m => {
            $(`input[name="menu_akses[]"][value="${m}"]`).prop('checked', true);
        });
    }
    
    $('#userModal').css('display', 'flex');
}

function hideUserForm() {
    $('#userModal').css('display', 'none');
}

function saveUser(e) {
    e.preventDefault();
    
    let nik = $('#user_nik').val();
    if(!nik) return alert('Silakan cari dan pilih karyawan terlebih dahulu!');
    
    let password = $('#user_password').val();
    let menus = [];
    $('input[name="menu_akses[]"]:checked').each(function() {
        menus.push($(this).val());
    });
    
    if(menus.length === 0) {
        if(!confirm('Anda tidak mencentang menu apa pun. Akun ini tidak akan bisa membuka apa-apa. Lanjutkan?')) return;
    }
    
    $('#btnSaveUser').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
    
    $.ajax({
        url: 'api/users.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ nik: nik, password: password, menus: menus }),
        success: function(res) {
            hideUserForm();
            loadUsers();
            alert('Akses berhasil disimpan!');
        },
        error: function(err) {
            alert('Error: ' + (err.responseJSON ? err.responseJSON.error : 'Gagal menyimpan data'));
        },
        complete: function() {
            $('#btnSaveUser').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Akses');
        }
    });
}

function deleteUser(nik, nama) {
    if(!confirm(`Apakah Anda yakin ingin MENCABUT SEMUA AKSES untuk karyawan ${nama} (${nik})?`)) return;
    
    $.ajax({
        url: 'api/users.php',
        type: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify({ nik: nik }),
        success: function(res) {
            loadUsers();
        },
        error: function(err) {
            alert('Error: ' + (err.responseJSON ? err.responseJSON.error : 'Gagal mencabut akses'));
        }
    });
}

function togglePwd() {
    const pwdInput = document.getElementById('user_password');
    const eyeIcon = document.getElementById('eyeIcon');
    if (pwdInput.type === 'password') {
        pwdInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        pwdInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}

$(document).ready(function() {
    loadUsers();
});
</script>
