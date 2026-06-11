<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div class="section-title" style="margin-bottom: 0; border: none;">Data Supplier</div>
        <button class="btn btn-primary" onclick="showForm()"><i class="fas fa-plus"></i> Tambah Supplier</button>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="supplierTable">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Supplier</th>
                    <th>Kontak Person</th>
                    <th>Telp</th>
                    <th>Kota</th>
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
<div id="supplierModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 id="modalTitle">Tambah Supplier</h3>
            <button class="btn btn-outline" style="padding: 5px 10px;" onclick="hideForm()"><i class="fas fa-times"></i></button>
        </div>
        <form id="supplierForm" onsubmit="saveSupplier(event)">
            <input type="hidden" id="IdSupplier" name="IdSupplier">
            
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Kode Supplier</label>
                    <input type="text" id="KodeSupplier" name="KodeSupplier" class="form-control" required maxlength="6">
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Supplier</label>
                    <input type="text" id="NamaSupplier" name="NamaSupplier" class="form-control" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Alamat</label>
                <input type="text" id="Alamat1" name="Alamat1" class="form-control">
            </div>
            
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Kota</label>
                    <input type="text" id="Kota1" name="Kota1" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">NPWP</label>
                    <input type="text" id="NPWP" name="NPWP" class="form-control">
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Telp 1</label>
                    <input type="text" id="Telp1" name="Telp1" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Person</label>
                    <input type="text" id="ContactPerson" name="ContactPerson" class="form-control">
                </div>
            </div>

            <div style="margin-top: 20px; text-align: right;">
                <button type="button" class="btn btn-outline" onclick="hideForm()">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function loadSuppliers() {
    $.ajax({
        url: 'api/supplier.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            let html = '';
            data.forEach(s => {
                html += `<tr>
                    <td><strong>${s.KodeSupplier}</strong></td>
                    <td>${s.NamaSupplier}</td>
                    <td>${s.ContactPerson || '-'}</td>
                    <td>${s.Telp1 || '-'}</td>
                    <td>${s.Kota1 || '-'}</td>
                    <td class="text-center">
                        <button class="btn btn-primary" style="padding: 4px 8px; font-size: 12px;" onclick="editSupplier('${s.KodeSupplier}')" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;" onclick="deleteSupplier('${s.KodeSupplier}')" title="Hapus"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            });
            $('#supplierTable tbody').html(html);
        }
    });
}

function showForm() {
    $('#supplierForm')[0].reset();
    $('#IdSupplier').val('');
    $('#KodeSupplier').prop('readonly', false);
    $('#modalTitle').text('Tambah Supplier');
    $('#supplierModal').css('display', 'flex');
}

function hideForm() {
    $('#supplierModal').hide();
}

function editSupplier(kode) {
    $.ajax({
        url: 'api/supplier.php?kode=' + encodeURIComponent(kode),
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if(data.error) {
                alert(data.error);
                return;
            }
            $('#IdSupplier').val(data.IdSupplier);
            $('#KodeSupplier').val(data.KodeSupplier.trim()).prop('readonly', true);
            $('#NamaSupplier').val(data.NamaSupplier ? data.NamaSupplier.trim() : '');
            $('#Alamat1').val(data.Alamat1 ? data.Alamat1.trim() : '');
            $('#Kota1').val(data.Kota1 ? data.Kota1.trim() : '');
            $('#NPWP').val(data.NPWP ? data.NPWP.trim() : '');
            $('#Telp1').val(data.Telp1 ? data.Telp1.trim() : '');
            $('#ContactPerson').val(data.ContactPerson ? data.ContactPerson.trim() : '');
            
            $('#modalTitle').text('Edit Supplier');
            $('#supplierModal').css('display', 'flex');
        }
    });
}

function deleteSupplier(kode) {
    if(confirm('Apakah Anda yakin ingin menghapus supplier ini?')) {
        $.ajax({
            url: 'api/supplier.php',
            method: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify({ KodeSupplier: kode }),
            success: function(res) {
                if(res.success) {
                    loadSuppliers();
                } else {
                    alert(res.error);
                }
            }
        });
    }
}

function saveSupplier(e) {
    e.preventDefault();
    let isEdit = $('#IdSupplier').val() !== '';
    
    // Serialize to JSON object
    let formDataArray = $('#supplierForm').serializeArray();
    let payload = {};
    formDataArray.forEach(item => { payload[item.name] = item.value; });

    $.ajax({
        url: 'api/supplier.php',
        method: isEdit ? 'PUT' : 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function(res) {
            if(res.success) {
                hideForm();
                loadSuppliers();
            } else {
                alert(res.error);
            }
        },
        error: function(err) {
            alert('Error: ' + (err.responseJSON ? err.responseJSON.error : 'Network/Server Error'));
        }
    });
}

$(document).ready(function() {
    loadSuppliers();
});
</script>
