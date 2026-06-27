<div class="card" style="margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div class="section-title" style="margin-bottom: 0; border: none;">Persetujuan Surat Pesanan (Approval)</div>
        <select id="filterStatus" class="form-control" style="width: 200px; display: inline-block;" onchange="loadSPs()">
            <option value="Pending">Menunggu Persetujuan</option>
            <option value="Approved">Disetujui</option>
            <option value="Rejected">Ditolak</option>
            <option value="">Semua Status</option>
        </select>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No. SP</th>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th>Gudang / Unit</th>
                    <th>Pembuat</th>
                    <th>Total Item</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="sp-table-body">
                <tr><td colspan="8" class="text-center">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Reject -->
<div id="rejectModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:20px; border-radius:8px; width:400px; max-width:90%;">
        <h3 style="margin-top:0; color:#d32f2f;">Tolak Pesanan</h3>
        <p>Anda akan menolak pesanan <strong id="reject-no-sp"></strong>.</p>
        <div class="form-group">
            <label class="form-label">Alasan Penolakan</label>
            <textarea id="reject-reason" class="form-control" style="height:80px;" placeholder="Tuliskan alasan..."></textarea>
        </div>
        <div style="text-align:right; margin-top:15px; gap:10px; display:flex; justify-content:flex-end;">
            <button class="btn btn-outline" onclick="$('#rejectModal').css('display','none')">Batal</button>
            <button class="btn btn-danger" onclick="submitReject()">Tolak SP</button>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div id="detailModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:20px; border-radius:8px; width:800px; max-width:90%; max-height:80vh; display:flex; flex-direction:column;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h3 style="margin:0; color:#2e7d32;">Detail Pesanan: <span id="detail-no-sp"></span></h3>
            <button onclick="$('#detailModal').css('display','none')" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <div style="overflow-y:auto; flex-grow:1;">
            <table class="data-table" style="font-size:12px;">
                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th>Barang</th>
                        <th>Merk / Model</th>
                        <th width="80">Qty</th>
                        <th width="100" class="text-right">Harga</th>
                        <th width="120" class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="detail-items-body">
                    <tr><td colspan="6" class="text-center">Memuat data...</td></tr>
                </tbody>
            </table>
            <div style="margin-top:10px; padding:10px; background:#f8f9fa; border-radius:4px; font-size:12px;">
                <strong>Supplier:</strong> <span id="detail-supplier"></span><br>
                <strong>Keterangan:</strong> <span id="detail-keterangan"></span>
            </div>
        </div>
        <div style="text-align:right; margin-top:15px;">
            <button class="btn btn-outline" onclick="$('#detailModal').css('display','none')">Tutup</button>
        </div>
    </div>
</div>

<script>
let currentRejectId = 0;
let currentRejectNoSP = '';

$(document).ready(function() {
    loadSPs();
});

function loadSPs() {
    const status = $('#filterStatus').val();
    $('#sp-table-body').html('<tr><td colspan="8" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>');
    
    $.ajax({
        url: 'api/orders.php?status_acc=' + encodeURIComponent(status),
        method: 'GET',
        success: function(res) {
            let html = '';
            if(res.data && res.data.length > 0) {
                res.data.forEach(sp => {
                    let badgeColor = '#f59e0b'; // pending
                    if(sp.status === 'Approved') badgeColor = '#10b981';
                    else if(sp.status === 'Rejected') badgeColor = '#ef4444';
                    
                    let badge = `<span style="background:${badgeColor}; color:#fff; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:600;">${sp.status}</span>`;
                    if(sp.status === 'Rejected' && sp.alasan_tolak) {
                        badge += `<div style="font-size:10px; color:#ef4444; margin-top:4px;">Alasan: ${sp.alasan_tolak}</div>`;
                    }

                    let actions = `
                        <button class="btn btn-outline" style="padding:4px 8px; font-size:11px; margin-right:4px; margin-bottom:4px;" onclick="openDetailModal(${sp.id}, '${sp.no_sp}')"><i class="fas fa-eye"></i> Detail</button>
                        <button class="btn btn-primary" style="padding:4px 8px; font-size:11px; margin-right:4px; margin-bottom:4px;" onclick="editOrder('${sp.id}')" title="Edit"><i class="fas fa-edit"></i> Edit</button>
                        <button class="btn btn-danger" style="padding:4px 8px; font-size:11px; margin-bottom:4px;" onclick="deleteOrder('${sp.id}')" title="Hapus Permanen"><i class="fas fa-trash"></i> Hapus</button>
                        <br>
                    `;

                    if(sp.status === 'Pending') {
                        actions += `
                            <button class="btn btn-success" style="padding:4px 8px; font-size:11px; margin-right:4px;" onclick="approveSP(${sp.id}, '${sp.no_sp}')"><i class="fas fa-check"></i> Setujui</button>
                            <button class="btn btn-danger" style="padding:4px 8px; font-size:11px;" onclick="openRejectModal(${sp.id}, '${sp.no_sp}')"><i class="fas fa-times"></i> Tolak</button>
                        `;
                    } else {
                        // If Approved or Rejected, allow resetting back to Pending
                        actions += `
                            <button class="btn btn-outline" style="padding:4px 8px; font-size:11px; background:#f59e0b; color:white; border:none;" onclick="resetStatus(${sp.id}, '${sp.no_sp}')"><i class="fas fa-undo"></i> Batal Status</button>
                        `;
                    }

                    html += `
                        <tr>
                            <td style="font-weight:600; color:var(--primary);">${sp.no_sp}</td>
                            <td>${sp.tgl_pesan}</td>
                            <td>${sp.namasup}</td>
                            <td><i class="fas fa-warehouse"></i> ${sp.unit}</td>
                            <td>${sp.user || '-'}</td>
                            <td>${sp.item_count} Item</td>
                            <td>${badge}</td>
                            <td class="text-center">${actions}</td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="8" class="text-center">Tidak ada data surat pesanan.</td></tr>';
            }
            $('#sp-table-body').html(html);
        },
        error: function() {
            $('#sp-table-body').html('<tr><td colspan="8" class="text-center" style="color:red;">Gagal memuat data.</td></tr>');
        }
    });
}

function formatCurrency(num) {
    return parseFloat(num).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function openDetailModal(id, no_sp) {
    $('#detail-no-sp').text(no_sp);
    $('#detail-items-body').html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>');
    $('#detail-keterangan').text('-');
    $('#detail-supplier').text('-');
    $('#detailModal').css('display', 'flex');

    $.ajax({
        url: 'api/orders.php?id=' + encodeURIComponent(id),
        method: 'GET',
        success: function(res) {
            if(res.header) {
                $('#detail-keterangan').text(res.header.keterangan || '-');
                $('#detail-supplier').text(res.header.namasup || '-');
                let html = '';
                let grandTotal = 0;
                if(res.items && res.items.length > 0) {
                    res.items.forEach((item, idx) => {
                        html += `<tr>
                            <td>${idx + 1}</td>
                            <td><span style="font-weight:600;">${item.barang}</span><br><small>${item.spec}</small></td>
                            <td>${item.merk} / ${item.model}</td>
                            <td>${item.qty}</td>
                            <td class="text-right">${formatCurrency(item.harga)}</td>
                            <td class="text-right" style="font-weight:bold;">${formatCurrency(item.jumlah)}</td>
                        </tr>`;
                        grandTotal += parseFloat(item.jumlah);
                    });
                    html += `<tr>
                        <td colspan="5" class="text-right" style="font-weight:bold;">GRAND TOTAL</td>
                        <td class="text-right" style="font-weight:bold; color:var(--primary);">${formatCurrency(grandTotal)}</td>
                    </tr>`;
                } else {
                    html = '<tr><td colspan="6" class="text-center">Tidak ada barang</td></tr>';
                }
                $('#detail-items-body').html(html);
            }
        },
        error: function() {
            $('#detail-items-body').html('<tr><td colspan="6" class="text-center" style="color:red;">Gagal memuat detail.</td></tr>');
        }
    });
}

function approveSP(id, no_sp) {
    if(!confirm(`Anda yakin menyetujui pesanan ${no_sp}?`)) return;
    
    $.ajax({
        url: 'api/orders.php',
        method: 'PATCH',
        contentType: 'application/json',
        data: JSON.stringify({ id: id, action: 'approve' }),
        success: function(res) {
            alert(res.message);
            loadSPs();
        },
        error: function(err) {
            alert('Gagal menyetujui pesanan. ' + (err.responseJSON ? err.responseJSON.error : ''));
        }
    });
}

function openRejectModal(id, no_sp) {
    currentRejectId = id;
    currentRejectNoSP = no_sp;
    $('#reject-no-sp').text(no_sp);
    $('#reject-reason').val('');
    $('#rejectModal').css('display', 'flex');
}

function submitReject() {
    const alasan = $('#reject-reason').val().trim();
    if(!alasan) return alert("Alasan penolakan wajib diisi!");
    
    $.ajax({
        url: 'api/orders.php',
        method: 'PATCH',
        contentType: 'application/json',
        data: JSON.stringify({ id: currentRejectId, action: 'reject', alasan: alasan }),
        success: function(res) {
            $('#rejectModal').css('display', 'none');
            alert(res.message);
            loadSPs();
        },
        error: function(err) {
            alert('Gagal menolak pesanan. ' + (err.responseJSON ? err.responseJSON.error : ''));
        }
    });
}

function editOrder(id) {
    window.location.href = 'dashboard.php?page=order_form&load=' + encodeURIComponent(id);
}

function deleteOrder(id) {
    if(confirm('Peringatan: Seluruh item pada pesanan ini akan dihapus secara permanen. Lanjutkan?')) {
        $.ajax({
            url: 'api/orders.php',
            method: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify({ id: id }),
            success: function(res) {
                if(res.success) {
                    loadSPs();
                } else {
                    alert('Gagal menghapus: ' + res.error);
                }
            }
        });
    }
}

function resetStatus(id, no_sp) {
    if(!confirm(`Anda yakin ingin membatalkan status pesanan ${no_sp} dan mengembalikannya ke "Menunggu Persetujuan"?`)) return;
    
    $.ajax({
        url: 'api/orders.php',
        method: 'PATCH',
        contentType: 'application/json',
        data: JSON.stringify({ id: id, action: 'reset' }),
        success: function(res) {
            alert(res.message);
            loadSPs();
        },
        error: function(err) {
            alert('Gagal me-reset status pesanan. ' + (err.responseJSON ? err.responseJSON.error : ''));
        }
    });
}
</script>
