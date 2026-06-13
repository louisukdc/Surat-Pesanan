<div class="card" style="margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div class="section-title" style="margin-bottom: 0; border: none;">Persetujuan Permintaan Barang (Approval)</div>
        <select id="filterStatus" class="form-control" style="width: 200px; display: inline-block;" onchange="loadPRs()">
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
                    <th>No. PR</th>
                    <th>Tanggal</th>
                    <th>Unit Peminta</th>
                    <th>Pembuat</th>
                    <th>Total Item</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="pr-table-body">
                <tr><td colspan="7" class="text-center">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Reject -->
<div id="rejectModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:20px; border-radius:8px; width:400px; max-width:90%;">
        <h3 style="margin-top:0; color:#d32f2f;">Tolak Permintaan</h3>
        <p>Anda akan menolak permintaan <strong id="reject-no-pr"></strong>.</p>
        <div class="form-group">
            <label class="form-label">Alasan Penolakan</label>
            <textarea id="reject-reason" class="form-control" style="height:80px;" placeholder="Tuliskan alasan..."></textarea>
        </div>
        <div style="text-align:right; margin-top:15px; gap:10px; display:flex; justify-content:flex-end;">
            <button class="btn btn-outline" onclick="$('#rejectModal').css('display','none')">Batal</button>
            <button class="btn btn-danger" onclick="submitReject()">Tolak PR</button>
        </div>
    </div>
</div>

<script>
let currentRejectPr = '';

$(document).ready(function() {
    loadPRs();
});

function loadPRs() {
    const status = $('#filterStatus').val();
    $('#pr-table-body').html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>');
    
    $.ajax({
        url: 'api/pr.php?status=' + encodeURIComponent(status),
        method: 'GET',
        success: function(res) {
            let html = '';
            if(res.data && res.data.length > 0) {
                res.data.forEach(pr => {
                    let badgeColor = '#f59e0b'; // pending
                    if(pr.status === 'Approved') badgeColor = '#10b981';
                    else if(pr.status === 'Rejected') badgeColor = '#ef4444';
                    
                    let badge = `<span style="background:${badgeColor}; color:#fff; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:600;">${pr.status}</span>`;
                    if(pr.status === 'Rejected' && pr.alasan_tolak) {
                        badge += `<div style="font-size:10px; color:#ef4444; margin-top:4px;">Alasan: ${pr.alasan_tolak}</div>`;
                    }

                    let actions = '';
                    if(pr.status === 'Pending') {
                        actions = `
                            <button class="btn btn-success" style="padding:4px 8px; font-size:11px; margin-right:4px;" onclick="approvePR('${pr.no_pr}')"><i class="fas fa-check"></i> Setujui</button>
                            <button class="btn btn-danger" style="padding:4px 8px; font-size:11px;" onclick="openRejectModal('${pr.no_pr}')"><i class="fas fa-times"></i> Tolak</button>
                        `;
                    } else if(pr.status === 'Approved') {
                        actions = `<button class="btn btn-primary" style="padding:4px 8px; font-size:11px;" onclick="window.location.href='dashboard.php?page=order_form&loadpr=${pr.no_pr}'"><i class="fas fa-file-import"></i> Buat SP</button>`;
                    } else {
                        actions = '-';
                    }

                    html += `
                        <tr>
                            <td style="font-weight:600; color:var(--primary);">${pr.no_pr}</td>
                            <td>${pr.tgl_pr}</td>
                            <td><i class="fas fa-hospital-user"></i> ${pr.unit}</td>
                            <td>${pr.user}</td>
                            <td>${pr.item_count} Item</td>
                            <td>${badge}</td>
                            <td class="text-center">${actions}</td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="7" class="text-center">Tidak ada data permintaan barang.</td></tr>';
            }
            $('#pr-table-body').html(html);
        },
        error: function() {
            $('#pr-table-body').html('<tr><td colspan="7" class="text-center" style="color:red;">Gagal memuat data.</td></tr>');
        }
    });
}

function approvePR(no_pr) {
    if(!confirm(`Anda yakin menyetujui permintaan ${no_pr}?`)) return;
    
    $.ajax({
        url: 'api/pr.php',
        method: 'PATCH',
        contentType: 'application/json',
        data: JSON.stringify({ no_pr: no_pr, action: 'approve' }),
        success: function(res) {
            alert(res.message);
            loadPRs();
        },
        error: function() {
            alert('Gagal menyetujui permintaan.');
        }
    });
}

function openRejectModal(no_pr) {
    currentRejectPr = no_pr;
    $('#reject-no-pr').text(no_pr);
    $('#reject-reason').val('');
    $('#rejectModal').css('display', 'flex');
}

function submitReject() {
    const alasan = $('#reject-reason').val().trim();
    if(!alasan) return alert("Alasan penolakan wajib diisi!");
    
    $.ajax({
        url: 'api/pr.php',
        method: 'PATCH',
        contentType: 'application/json',
        data: JSON.stringify({ no_pr: currentRejectPr, action: 'reject', alasan: alasan }),
        success: function(res) {
            $('#rejectModal').css('display', 'none');
            alert(res.message);
            loadPRs();
        },
        error: function() {
            alert('Gagal menolak permintaan.');
        }
    });
}
</script>
