<style>
/* Custom Dropdown for Attachments */
.dropdown-attach {
    position: relative;
    display: inline-block;
}
.dropdown-attach-content {
    display: none;
    position: absolute;
    right: 0;
    bottom: 100%;
    margin-bottom: 5px;
    background-color: white;
    min-width: 200px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 999;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}
.dropdown-attach-content a {
    color: #374151;
    padding: 10px 16px;
    text-decoration: none;
    display: block;
    font-size: 13px;
    border-bottom: 1px solid #f3f4f6;
    text-align: left;
}
.dropdown-attach-content a:last-child {
    border-bottom: none;
}
.dropdown-attach-content a:hover {
    background-color: #f9fafb;
    color: var(--primary);
}
.dropdown-attach-content a.dl-all {
    background-color: #1e3a8a;
    color: white;
    font-weight: 600;
    text-align: center;
}
.dropdown-attach-content a.dl-all:hover {
    background-color: #1e40af;
}
.badge-count {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #ef4444;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 10px;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
</style>
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <div class="section-title" style="margin-bottom: 0; border: none;">Daftar Pesanan</div>
        <div style="display: flex; gap: 10px;">
            <button class="btn btn-success" onclick="exportToExcel()"><i class="fas fa-file-excel"></i> Export Excel</button>
            <a href="dashboard.php?page=order_form" class="btn btn-primary"><i class="fas fa-plus"></i> Buat Pesanan Baru</a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-size: 12px; margin-bottom: 5px; color: #666;">Cari (No SP / Supplier)</label>
            <input type="text" id="filterSearch" class="form-control" placeholder="Ketik kata kunci...">
        </div>
        <div>
            <label style="display: block; font-size: 12px; margin-bottom: 5px; color: #666;">Dari Tanggal</label>
            <input type="date" id="filterStartDate" class="form-control">
        </div>
        <div>
            <label style="display: block; font-size: 12px; margin-bottom: 5px; color: #666;">Sampai Tanggal</label>
            <input type="date" id="filterEndDate" class="form-control">
        </div>
        <div>
            <label style="display: block; font-size: 12px; margin-bottom: 5px; color: #666;">Status</label>
            <select id="filterStatus" class="form-control" style="min-width: 150px;">
                <option value="Approved">Approved (Disetujui)</option>
                <option value="All">Semua Status</option>
                <option value="Pending">Pending / Draft</option>
                <option value="Rejected">Ditolak</option>
            </select>
        </div>
        <div>
            <button class="btn btn-primary" onclick="currentPage=1; loadOrders();"><i class="fas fa-search"></i> Filter</button>
            <button class="btn btn-outline" onclick="resetFilter()"><i class="fas fa-undo"></i> Reset</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="ordersTable">
            <thead>
                <tr>
                    <th>No. SP</th>
                    <th>Tanggal Pesan</th>
                    <th>Tanggal Setuju</th>
                    <th>Supplier</th>
                    <th>Bagian</th>
                    <th class="text-right">Grand Total</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data loaded via AJAX -->
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
        <div id="pageInfo" style="font-size: 14px; color: #666;">Menampilkan 0 data</div>
        <div style="display: flex; gap: 5px;">
            <button class="btn btn-outline" id="btnPrev" onclick="changePage(-1)" style="padding: 5px 15px;">&laquo; Prev</button>
            <button class="btn btn-outline" id="btnNext" onclick="changePage(1)" style="padding: 5px 15px;">Next &raquo;</button>
        </div>
    </div>
</div>

<!-- Modal Atur Tanggal Setuju -->
<div id="dateAccModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
    <div style="background: white; padding: 20px; border-radius: 8px; width: 350px; max-width: 90%; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <h3 style="margin-top:0; margin-bottom: 20px; font-size: 16px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">Atur Tanggal Persetujuan</h3>
        <input type="hidden" id="dateAccId">
        
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <label style="font-size: 13px; font-weight: 600; color: #374151; margin: 0;">Tanggal Disetujui</label>
            <input type="date" id="dateAccInput" class="form-control" style="width: 180px; padding: 6px 10px;">
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid #e5e7eb; padding-top: 15px;">
            <button class="btn btn-outline" onclick="closeDateAccModal()" style="padding: 6px 12px;">Batal</button>
            <button class="btn btn-primary" onclick="saveDateAcc()" style="padding: 6px 12px;">Simpan</button>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
const limitPerPage = 25;
let totalPages = 1;

$(document).ready(function() {
    loadOrders();
    
    // Auto search on enter
    $('#filterSearch').on('keypress', function(e) {
        if(e.which == 13) { currentPage = 1; loadOrders(); }
    });
});

function loadOrders() {
    const search = $('#filterSearch').val();
    const startDate = $('#filterStartDate').val();
    const endDate = $('#filterEndDate').val();
    const statusFilter = $('#filterStatus').val();

    $.ajax({
        url: `api/orders.php?page=${currentPage}&limit=${limitPerPage}&search=${encodeURIComponent(search)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&status_filter=${encodeURIComponent(statusFilter)}`,
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            let html = '';
            if (res.data && res.data.length > 0) {
                res.data.forEach(o => {
                    let attachmentsHtml = '';
                    if (o.nama_lampiran && o.nama_lampiran !== '0' && o.nama_lampiran !== '') {
                        const files = o.nama_lampiran.split(',');
                        if (files.length > 0) {
                            attachmentsHtml += `<div class="dropdown-attach">`;
                            attachmentsHtml += `<button class="btn" style="padding: 4px 8px; font-size: 12px; background-color: #0d9488; color: white; margin-right: 2px; position:relative;" onclick="toggleDropdown(event, 'dd-${o.id}')" title="Lihat Lampiran">`;
                            attachmentsHtml += `<i class="fas fa-paperclip"></i>`;
                            attachmentsHtml += `<span class="badge-count">${files.length}</span>`;
                            attachmentsHtml += `</button>`;
                            
                            attachmentsHtml += `<div id="dd-${o.id}" class="dropdown-attach-content">`;
                            files.forEach((file, index) => {
                                let fName = file.trim();
                                if(fName.length > 20) fName = fName.substring(0, 18) + '...';
                                attachmentsHtml += `<a href="uploads/lampiran/${file.trim()}" target="_blank"><i class="fas fa-file-pdf" style="color:#ef4444; margin-right:6px;"></i> File ${index+1}: ${fName}</a>`;
                            });
                            attachmentsHtml += `<a href="api/download_zip.php?id=${o.id}" class="dl-all"><i class="fas fa-file-archive" style="margin-right:6px;"></i> Unduh Semua (.zip)</a>`;
                            attachmentsHtml += `</div></div>`;
                        }
                    }

                    // Format tanggal setuju to remove time
                    let formattedDateAcc = '-';
                    if (o.date_acc && !o.date_acc.startsWith('1900-01-01')) {
                        formattedDateAcc = o.date_acc.split(' ')[0];
                    }

                    html += `<tr>
                        <td><span style="font-weight: 600; color: var(--primary);">${o.no_sp}</span></td>
                        <td>${o.tgl_sp}</td>
                        <td>${formattedDateAcc}</td>
                        <td>${o.namasup}</td>
                        <td>${o.unit}</td>
                        <td class="text-right">Rp ${parseFloat(o.grand_total || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        <td class="text-center">
                            <a href="dashboard.php?page=detail_kwitansi&id=${encodeURIComponent(o.id)}" target="_blank" class="btn" style="padding: 4px 8px; font-size: 12px; background-color: #f1f5f9; color: var(--text-main); border: 1px solid var(--border); text-decoration: none;" title="Detail Kwitansi"><i class="fas fa-eye"></i></a>
                            ${attachmentsHtml}
                            <button class="btn btn-warning" style="padding: 4px 8px; font-size: 12px; color: white;" onclick="openDateAccModal('${o.id}', '${formattedDateAcc !== '' ? formattedDateAcc : ''}')" title="Atur Tanggal Setuju"><i class="fas fa-calendar-alt"></i></button>
                            <button class="btn btn-primary" style="padding: 4px 8px; font-size: 12px;" onclick="editOrder('${o.id}')" title="Edit"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;" onclick="deleteOrder('${o.id}')" title="Hapus"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>`;
                });
            } else {
                html = '<tr><td colspan="7" class="text-center">Tidak ada data pesanan ditemukan.</td></tr>';
            }
            $('#ordersTable tbody').html(html);

            // Update Pagination state
            totalPages = res.total_pages || 1;
            $('#pageInfo').html(`Total <strong>${res.total}</strong> Pesanan (Halaman ${currentPage} dari ${totalPages})`);
            $('#btnPrev').prop('disabled', currentPage <= 1);
            $('#btnNext').prop('disabled', currentPage >= totalPages);
        }
    });
}

function resetFilter() {
    $('#filterSearch').val('');
    $('#filterStartDate').val('');
    $('#filterEndDate').val('');
    $('#filterStatus').val('All');
    currentPage = 1;
    loadOrders();
}

function changePage(delta) {
    let newPage = currentPage + delta;
    if (newPage >= 1 && newPage <= totalPages) {
        currentPage = newPage;
        loadOrders();
    }
}

function editOrder(id) {
    window.location.href = 'dashboard.php?page=order_form&load=' + encodeURIComponent(id);
}

function deleteOrder(id) {
    if(confirm('Peringatan: Seluruh item pada pesanan ini akan dihapus. Lanjutkan?')) {
        $.ajax({
            url: 'api/orders.php',
            method: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify({ id: id }),
            success: function(res) {
                if(res.success) {
                    loadOrders();
                } else {
                    alert('Gagal menghapus: ' + res.error);
                }
            }
        });
    }
}

function exportToExcel() {
    const search = $('#filterSearch').val();
    const startDate = $('#filterStartDate').val();
    const endDate = $('#filterEndDate').val();
    
    // Disable button to prevent spam
    const btn = event.currentTarget;
    const oldText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
    btn.disabled = true;

    $.ajax({
        url: `api/orders.php?export=1&page=1&limit=1000000&search=${encodeURIComponent(search)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`,
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            btn.innerHTML = oldText;
            btn.disabled = false;
            
            if(res.data && res.data.length > 0) {
                const exportData = res.data.map(o => ({
                    "No SP": o.no_sp,
                    "Tanggal": o.tgl_sp,
                    "Supplier": o.namasup,
                    "Unit/Bagian": o.unit,
                    "Dibuat Oleh": o.user || '',
                    "Pembayaran": o.pembayaran || '',
                    "Nama Barang": o.barang || '',
                    "Qty": parseFloat(o.qty) || 0,
                    "Harga Satuan (Rp)": parseFloat(o.harga) || 0,
                    "Subtotal (Rp)": parseFloat(o.total) || 0,
                    "Grand Total SP (Rp)": parseFloat(o.grand_total) || 0
                }));
                
                const worksheet = XLSX.utils.json_to_sheet(exportData);
                const workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(workbook, worksheet, "Data_Pesanan_Detail");
                
                const d = new Date().toISOString().slice(0,10);
                XLSX.writeFile(workbook, `Rekap_Pesanan_Detail_${d}.xlsx`);
            } else {
                alert('Tidak ada data untuk diexport.');
            }
        },
        error: function() {
            btn.innerHTML = oldText;
            btn.disabled = false;
            alert('Gagal mengambil data untuk export.');
        }
    });
}

function openDateAccModal(id, currentDate) {
    $('#dateAccId').val(id);
    if(currentDate && currentDate !== '-') {
        $('#dateAccInput').val(currentDate);
    } else {
        const today = new Date();
        const yyyy = today.getFullYear();
        let mm = today.getMonth() + 1;
        let dd = today.getDate();
        if (dd < 10) dd = '0' + dd;
        if (mm < 10) mm = '0' + mm;
        $('#dateAccInput').val(yyyy + '-' + mm + '-' + dd);
    }
    $('#dateAccModal').css('display', 'flex');
}

function closeDateAccModal() {
    $('#dateAccModal').css('display', 'none');
}

function saveDateAcc() {
    const id = $('#dateAccId').val();
    const newDate = $('#dateAccInput').val();
    
    if(!newDate) {
        alert('Harap pilih tanggal terlebih dahulu.');
        return;
    }
    
    $.ajax({
        url: 'api/orders.php',
        method: 'PATCH',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'set_approval_date', id: id, date_acc: newDate }),
        success: function(res) {
            if(res.success) {
                closeDateAccModal();
                loadOrders();
            } else {
                alert('Gagal menyimpan tanggal: ' + res.error);
            }
        },
        error: function(err) {
            alert('Terjadi kesalahan server.');
        }
    });
}

// Dropdown logic
function toggleDropdown(e, id) {
    e.stopPropagation();
    $('.dropdown-attach-content').not('#'+id).hide();
    
    let dd = $('#'+id);
    let btn = $(e.currentTarget);
    
    if (dd.is(':hidden')) {
        dd.show();
        let rect = btn[0].getBoundingClientRect();
        
        dd.css({
            'position': 'fixed',
            'top': (rect.bottom + 5) + 'px',
            'left': 'auto',
            'right': ($(window).width() - rect.right) + 'px',
            'bottom': 'auto'
        });
    } else {
        dd.hide();
    }
}

$(document).click(function() {
    $('.dropdown-attach-content').hide();
});

</script>
