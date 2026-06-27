<style>
/* Re-use styling from order_form and approval */
.sp-panel {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    border: 1px solid #e5e7eb;
    margin-bottom: 24px;
}
.sp-panel-header {
    padding: 12px 16px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f9fafb;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}
.sp-panel-title {
    font-size: 16px;
    font-weight: 700;
    color: #1e40af;
    margin: 0;
}
.sp-table {
    width: 100%;
    border-collapse: collapse;
}
.sp-table th {
    background-color: #f9fafb;
    color: #374151;
    font-weight: 600;
    font-size: 13px;
    padding: 10px 12px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}
.sp-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 13px;
    vertical-align: top;
}
.sp-table tbody tr:hover { background-color: #f9fafb; }

.sp-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
}
.sp-btn-primary { background-color: #0d9488; color: white; }
.sp-btn-success { background-color: #059669; color: white; }
.sp-btn-danger { background-color: #ef4444; color: white; }
.sp-btn-outline { background-color: transparent; border: 1px solid #d1d5db; color: #374151; }

.sp-modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    backdrop-filter: blur(4px);
}
.sp-modal-content {
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    width: 100%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
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
}
.sp-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    box-sizing: border-box;
}
.sp-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px; }
.suggestions-box {
    position: absolute;
    width: 100%;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0 0 6px 6px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    z-index: 50;
    max-height: 240px;
    overflow-y: auto;
    display: none;
}
.suggestion-item { padding: 8px 16px; font-size: 14px; border-bottom: 1px solid #f3f4f6; cursor: pointer; }
.suggestion-item:hover { background-color: #f0fdfa; }
</style>

<div style="padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; color: #1e293b;"><i class="fas fa-box-open"></i> Penerimaan Barang (Surat Jalan)</h2>
        <button class="sp-btn sp-btn-primary" onclick="openSJModal()"><i class="fas fa-plus"></i> Terima Barang Baru</button>
    </div>

    <div class="sp-panel">
        <div class="sp-panel-header">
            <h3 class="sp-panel-title">Daftar Penerimaan Barang</h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="sp-table" id="sj-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No. Surat Jalan</th>
                        <th>Tanggal Terima</th>
                        <th>Terkait PO</th>
                        <th>Supplier</th>
                        <th>Kondisi/Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Populated by JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form Surat Jalan -->
<div id="sjModal" class="sp-modal-overlay">
    <div class="sp-modal-content">
        <div class="sp-modal-header">
            <h3 class="sp-panel-title" style="font-size:18px;"><i class="fas fa-truck-loading"></i> Form Penerimaan Barang</h3>
            <button onclick="closeSJModal()" style="background:none; border:none; cursor:pointer; font-size:20px; color:#9ca3af;"><i class="fas fa-times"></i></button>
        </div>
        <div class="sp-modal-body">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div style="position:relative; grid-column: span 2;">
                    <label class="sp-label">Cari & Pilih PO (Surat Pesanan) yang Disetujui <span style="color:red;">*</span></label>
                    <input type="hidden" id="sj_spu_h_id">
                    <input type="text" id="search_po" class="sp-input" placeholder="Ketik No PO, No Pesanan, atau Nama Supplier..." autocomplete="off" onkeyup="searchPO(this.value)">
                    <div id="po-suggestions" class="suggestions-box"></div>
                </div>

                <div>
                    <label class="sp-label">No. Surat Jalan (Fisik) <span style="color:red;">*</span></label>
                    <input type="text" id="nomor_surat_jalan" class="sp-input" placeholder="Ketik nomor SJ dari supplier...">
                </div>
                
                <div>
                    <label class="sp-label">Tanggal Terima <span style="color:red;">*</span></label>
                    <input type="date" id="tanggal_terima" class="sp-input" value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div>
                    <label class="sp-label">Status Pengecekan Barang <span style="color:red;">*</span></label>
                    <select id="status_pengecekan" class="sp-input">
                        <option value="Sesuai">Sesuai / Kondisi Baik</option>
                        <option value="Sebagian Rusak">Sebagian Rusak</option>
                        <option value="Tidak Sesuai Spesifikasi">Tidak Sesuai Spesifikasi</option>
                        <option value="Kurang">Jumlah Kurang</option>
                    </select>
                </div>
                
                <div>
                    <label class="sp-label">Kategori</label>
                    <input type="text" id="kategori" class="sp-input" value="Barang" readonly style="background:#f3f4f6;">
                </div>
            </div>

            <!-- Preview PO Items -->
            <div id="po-preview" style="display:none;">
                <h4 style="margin: 0 0 10px 0; font-size:14px; color:#4b5563;">Rincian Barang Pesanan (Referensi)</h4>
                <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; padding:12px; overflow-x:auto;">
                    <table class="sp-table" id="po-items-table" style="background:white;">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Spesifikasi</th>
                                <th style="text-align:center;">Qty Pesan</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <div style="font-size:11px; color:#6b7280; margin-top:8px; font-style:italic;">
                        * Catatan: Penerimaan parsial / input qty spesifik belum didukung di versi ini. Surat Jalan mencakup seluruh item dalam PO ini.
                    </div>
                </div>
            </div>

        </div>
        <div class="sp-modal-footer">
            <button class="sp-btn sp-btn-outline" onclick="closeSJModal()">Batal</button>
            <button class="sp-btn sp-btn-success" onclick="saveSJ()"><i class="fas fa-save"></i> Simpan Surat Jalan</button>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadSJs();
});

function loadSJs() {
    $.get('api/surat_jalan.php', function(res) {
        let html = '';
        if (res.data && res.data.length > 0) {
            res.data.forEach((sj, idx) => {
                let badgeColor = sj.status_pengecekan === 'Sesuai' ? '#10b981' : '#f59e0b';
                
                html += `<tr>
                    <td>${idx+1}</td>
                    <td><span style="font-weight:600;">${sj.nomor_surat_jalan}</span></td>
                    <td>${sj.tanggal_terima}</td>
                    <td>
                        <div style="font-weight:600; color:#1e40af;">${sj.no_sp}</div>
                        <div style="font-size:11px; color:#6b7280;">${sj.no_permintaan}</div>
                    </td>
                    <td>${sj.namasup}</td>
                    <td><span style="background:${badgeColor}; color:white; padding:2px 6px; border-radius:4px; font-size:11px;">${sj.status_pengecekan}</span></td>
                    <td>
                        <button class="sp-btn sp-btn-danger" style="padding:4px 8px; font-size:11px;" onclick="deleteSJ(${sj.id}, '${sj.nomor_surat_jalan}')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            });
        } else {
            html = '<tr><td colspan="7" style="text-align:center; padding:20px; color:#6b7280;">Belum ada data penerimaan barang.</td></tr>';
        }
        $('#sj-table tbody').html(html);
    }, 'json');
}

function openSJModal() {
    $('#sj_spu_h_id').val('');
    $('#search_po').val('');
    $('#nomor_surat_jalan').val('');
    $('#status_pengecekan').val('Sesuai');
    $('#po-preview').hide();
    $('#sjModal').css('display', 'flex');
}

function closeSJModal() {
    $('#sjModal').css('display', 'none');
}

let poTimeout;
function searchPO(query) {
    clearTimeout(poTimeout);
    if(query.length < 2) {
        $('#po-suggestions').hide();
        return;
    }
    poTimeout = setTimeout(() => {
        $.get('api/surat_jalan.php?search_po=' + encodeURIComponent(query), function(data) {
            let html = '';
            if(data.length > 0) {
                data.forEach(po => {
                    html += `<div class="suggestion-item" onclick="selectPO(${po.id}, '${po.no_sp}', '${po.namasup}')">
                                <strong>${po.no_sp}</strong> (${po.no_permintaan}) - ${po.namasup}
                             </div>`;
                });
                $('#po-suggestions').html(html).show();
            } else {
                $('#po-suggestions').html('<div class="suggestion-item" style="color:red;">Tidak ada PO Approved yang cocok.</div>').show();
            }
        });
    }, 300);
}

function selectPO(id, no_sp, namasup) {
    $('#sj_spu_h_id').val(id);
    $('#search_po').val(`${no_sp} - ${namasup}`);
    $('#po-suggestions').hide();
    
    // Load PO items preview
    $.get('api/surat_jalan.php?po_id=' + id, function(res) {
        if(res.items && res.items.length > 0) {
            let html = '';
            res.items.forEach(item => {
                html += `<tr>
                    <td><strong>${item.barang}</strong></td>
                    <td style="color:#4b5563;">${item.spec}</td>
                    <td style="text-align:center;">${parseFloat(item.qty)}</td>
                </tr>`;
            });
            $('#po-items-table tbody').html(html);
            $('#po-preview').show();
        }
    }, 'json');
}

$(document).click(function(e) {
    if (!$(e.target).closest('#search_po').length && !$(e.target).closest('#po-suggestions').length) {
        $('#po-suggestions').hide();
    }
});

function saveSJ() {
    let payload = {
        id_spu_h: $('#sj_spu_h_id').val(),
        nomor_surat_jalan: $('#nomor_surat_jalan').val().trim(),
        tanggal_terima: $('#tanggal_terima').val(),
        status_pengecekan: $('#status_pengecekan').val(),
        kategori: $('#kategori').val()
    };

    if(!payload.id_spu_h) return alert("Pilih PO (Surat Pesanan) terlebih dahulu!");
    if(!payload.nomor_surat_jalan) return alert("Nomor Surat Jalan wajib diisi!");

    $.ajax({
        url: 'api/surat_jalan.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function(res) {
            if(res.success) {
                alert(res.message);
                closeSJModal();
                loadSJs();
            }
        },
        error: function(err) {
            alert('Gagal menyimpan: ' + (err.responseJSON ? err.responseJSON.error : ''));
        }
    });
}

function deleteSJ(id, no_sj) {
    if(!confirm(`Hapus Surat Jalan ${no_sj}?`)) return;
    $.ajax({
        url: 'api/surat_jalan.php',
        method: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify({id: id}),
        success: function(res) {
            loadSJs();
        },
        error: function(err) {
            alert('Gagal menghapus: ' + (err.responseJSON ? err.responseJSON.error : ''));
        }
    });
}
</script>
