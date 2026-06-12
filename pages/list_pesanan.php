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
            <button class="btn btn-primary" onclick="currentPage=1; loadOrders();"><i class="fas fa-search"></i> Filter</button>
            <button class="btn btn-outline" onclick="resetFilter()"><i class="fas fa-undo"></i> Reset</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="ordersTable">
            <thead>
                <tr>
                    <th>No. SP</th>
                    <th>Tanggal</th>
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

<script>
let currentPage = 1;
const limitPerPage = 25;
let totalPages = 1;

function loadOrders() {
    const search = $('#filterSearch').val();
    const startDate = $('#filterStartDate').val();
    const endDate = $('#filterEndDate').val();

    $.ajax({
        url: `api/orders.php?page=${currentPage}&limit=${limitPerPage}&search=${encodeURIComponent(search)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`,
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            let html = '';
            if (res.data && res.data.length > 0) {
                res.data.forEach(o => {
                    html += `<tr>
                        <td><span style="font-weight: 600; color: var(--primary);">${o.no_sp}</span></td>
                        <td>${o.tgl_sp}</td>
                        <td>${o.namasup}</td>
                        <td>${o.unit}</td>
                        <td class="text-right">Rp ${parseFloat(o.flag).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        <td class="text-center">
                            <a href="detail_kwitansi.php?id=${encodeURIComponent(o.no_sp)}" target="_blank" class="btn" style="padding: 4px 8px; font-size: 12px; background-color: #f1f5f9; color: var(--text-main); border: 1px solid var(--border); text-decoration: none;" title="Detail Kwitansi"><i class="fas fa-eye"></i> Detail</a>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <button class="btn btn-primary" style="padding: 4px 8px; font-size: 12px;" onclick="editOrder('${o.no_sp}')" title="Edit"><i class="fas fa-edit"></i></button>
                            <?php endif; ?>
                            <button class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;" onclick="deleteOrder('${o.no_sp}')" title="Hapus"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>`;
                });
            } else {
                html = '<tr><td colspan="6" class="text-center">Tidak ada data pesanan ditemukan.</td></tr>';
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

function editOrder(no_sp) {
    window.location.href = 'dashboard.php?page=order_form&load=' + encodeURIComponent(no_sp);
}

function deleteOrder(no_sp) {
    if(confirm('Peringatan: Seluruh item pada pesanan ' + no_sp + ' akan dihapus. Lanjutkan?')) {
        $.ajax({
            url: 'api/orders.php',
            method: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify({ no_sp: no_sp }),
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
        url: `api/orders.php?page=1&limit=1000000&search=${encodeURIComponent(search)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`,
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
                    "Kode Supplier": o.kodesup,
                    "Unit/Bagian": o.unit,
                    "Total (Rp)": parseFloat(o.flag)
                }));
                
                const worksheet = XLSX.utils.json_to_sheet(exportData);
                const workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(workbook, worksheet, "Data_Pesanan");
                
                const d = new Date().toISOString().slice(0,10);
                XLSX.writeFile(workbook, `Pesanan_RKZ_${d}.xlsx`);
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

$(document).ready(function() {
    loadOrders();
    
    // Auto search on enter
    $('#filterSearch').on('keypress', function(e) {
        if(e.which == 13) { currentPage = 1; loadOrders(); }
    });
});
</script>
