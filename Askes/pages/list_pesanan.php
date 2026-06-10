<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div class="section-title" style="margin-bottom: 0; border: none;">Daftar Pesanan</div>
        <a href="dashboard.php?page=order_form" class="btn btn-primary"><i class="fas fa-plus"></i> Buat Pesanan Baru</a>
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
</div>

<script>
function loadOrders() {
    $.ajax({
        url: 'api/orders.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            let html = '';
            data.forEach(o => {
                html += `<tr>
                    <td><span style="font-weight: 600; color: var(--primary);">${o.no_sp}</span></td>
                    <td>${o.tgl_sp}</td>
                    <td>${o.namasup}</td>
                    <td>${o.unit}</td>
                    <td class="text-right">Rp ${parseFloat(o.flag).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    <td class="text-center">
                        <button class="btn btn-primary" style="padding: 4px 8px; font-size: 12px;" onclick="editOrder('${o.no_sp}')" title="Edit/Lihat"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;" onclick="deleteOrder('${o.no_sp}')" title="Hapus"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            });
            $('#ordersTable tbody').html(html);
        }
    });
}

function editOrder(no_sp) {
    // We redirect to the order form. We can modify order_form.php to auto-load if a parameter is passed.
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

$(document).ready(function() {
    loadOrders();
});
</script>
