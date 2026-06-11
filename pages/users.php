<?php
// pages/users.php
if ($_SESSION['role'] !== 'admin') {
    echo "<div class='alert alert-error'>Access Denied: You do not have permission to view this page.</div>";
    exit;
}
?>
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div class="section-title" style="margin-bottom: 0; border: none;">Manajemen User</div>
        <button class="btn btn-primary" onclick="showUserForm()"><i class="fas fa-plus"></i> Tambah User</button>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="usersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
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
<div id="userModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 400px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 id="userModalTitle">Tambah User</h3>
            <button class="btn btn-outline" style="padding: 5px 10px;" onclick="hideUserForm()"><i class="fas fa-times"></i></button>
        </div>
        <form id="userForm" onsubmit="saveUser(event)">
            <input type="hidden" id="userId" name="id">
            
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password <small id="pwdHelp"></small></label>
                <input type="password" id="password" name="password" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">Role</label>
                <select id="role" name="role" class="form-control">
                    <option value="umum">Umum</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div style="margin-top: 20px; text-align: right;">
                <button type="button" class="btn btn-outline" onclick="hideUserForm()">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function loadUsers() {
    $.ajax({
        url: 'api/users.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            let html = '';
            data.forEach(u => {
                let roleBadge = u.role === 'admin' 
                    ? '<span style="background: var(--primary); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">ADMIN</span>' 
                    : '<span style="background: var(--text-secondary); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">UMUM</span>';
                
                html += `<tr>
                    <td>${u.id}</td>
                    <td><strong>${u.username}</strong></td>
                    <td>${roleBadge}</td>
                    <td class="text-center">
                        <button class="btn btn-primary" style="padding: 4px 8px; font-size: 12px;" onclick="editUser(${u.id}, '${u.username}', '${u.role}')" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;" onclick="deleteUser(${u.id}, '${u.username}')" title="Hapus"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            });
            $('#usersTable tbody').html(html);
        }
    });
}

function showUserForm() {
    $('#userForm')[0].reset();
    $('#userId').val('');
    $('#username').prop('readonly', false);
    $('#password').prop('required', true);
    $('#pwdHelp').text('');
    $('#userModalTitle').text('Tambah User Baru');
    $('#userModal').css('display', 'flex');
}

function hideUserForm() {
    $('#userModal').hide();
}

function editUser(id, username, role) {
    $('#userId').val(id);
    $('#username').val(username).prop('readonly', true);
    $('#role').val(role);
    $('#password').val('').prop('required', false);
    $('#pwdHelp').text('(Kosongkan jika tidak ingin mengubah password)');
    
    $('#userModalTitle').text('Edit User');
    $('#userModal').css('display', 'flex');
}

function deleteUser(id, username) {
    if(confirm('Hapus user ' + username + '?')) {
        $.ajax({
            url: 'api/users.php',
            method: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify({ id: id }),
            success: function(res) {
                if(res.success) {
                    loadUsers();
                } else {
                    alert(res.error);
                }
            },
            error: function(err) {
                alert('Error: ' + (err.responseJSON ? err.responseJSON.error : 'Unknown error'));
            }
        });
    }
}

function saveUser(e) {
    e.preventDefault();
    let isEdit = $('#userId').val() !== '';
    
    let payload = {
        id: $('#userId').val(),
        username: $('#username').val(),
        password: $('#password').val(),
        role: $('#role').val()
    };

    $.ajax({
        url: 'api/users.php',
        method: isEdit ? 'PUT' : 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function(res) {
            if(res.success) {
                hideUserForm();
                loadUsers();
            } else {
                alert(res.error);
            }
        },
        error: function(err) {
            alert('Error: ' + (err.responseJSON ? err.responseJSON.error : 'Unknown error'));
        }
    });
}

$(document).ready(function() {
    loadUsers();
});
</script>
