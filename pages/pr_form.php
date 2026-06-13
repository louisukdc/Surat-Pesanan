<style>
/* Re-use material-panel CSS from order_form.php */
.material-panel {
    background: #fff;
    border-top: 15px solid rgba(179, 178, 175, 0.12);
    border-bottom: 15px solid rgba(179, 178, 175, 0.12);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 15px; 
    border-left: 1px solid #ddd;
    border-right: 1px solid #ddd;
    border-radius: 4px;
}
.material-panel-title {
    color: #2e7d32;
    font-weight: 700;
    font-size: 15px;
    padding: 10px 15px 5px 15px;
}
.material-panel-body {
    padding: 5px 15px 15px 15px;
    max-height: 50vh;
    overflow-y: auto;
}
.material-panel .form-group { margin-bottom: 8px; }
.material-panel .form-label { margin-bottom: 2px; font-size: 11px; font-weight: 600; }
.material-panel .form-control { padding: 4px 8px; height: 30px; font-size: 12px; }
.material-panel .grid-2 { gap: 15px; }
.material-panel .btn { padding: 4px 12px; font-size: 12px; }

.material-panel-body::-webkit-scrollbar { width: 8px; }
.material-panel-body::-webkit-scrollbar-track { background: #f1f1f1; }
.material-panel-body::-webkit-scrollbar-thumb { background: rgba(179, 178, 175, 0.5); border-radius: 4px; }
.material-panel-body::-webkit-scrollbar-thumb:hover { background: rgba(179, 178, 175, 0.8); }
</style>

<div class="toolbar">
    <button class="btn btn-primary" onclick="newPR()"><i class="fas fa-plus"></i> PR BARU</button>
</div>

<!-- Panel 1: Informasi Dasar PR -->
<div class="material-panel">
    <div class="material-panel-title">Informasi Permintaan Barang (PR)</div>
    <div class="material-panel-body">
        <div class="grid-3" style="gap:15px;">
            <div class="form-group">
                <label class="form-label">No. PR</label>
                <input type="text" id="no_pr" class="form-control" placeholder="PR/001/01/26">
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal</label>
                <input type="date" id="tgl_pr" class="form-control" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Unit Peminta</label>
                <select id="unit" class="form-control">
                    <option value="IGD">IGD</option>
                    <option value="FARMASI">FARMASI</option>
                    <option value="GIZI">GIZI</option>
                    <option value="LABORATORIUM">LABORATORIUM</option>
                    <option value="UMUM">UMUM</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Keterangan / Alasan Permintaan</label>
            <input type="text" id="keterangan" class="form-control" placeholder="Misal: Restock bulanan...">
        </div>
    </div>
</div>

<!-- Panel 2: Detail Barang -->
<div class="material-panel">
    <div class="material-panel-title">Daftar Barang yang Diminta</div>
    <div class="material-panel-body">
        <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #ddd; margin-bottom: 10px;">
            <div class="grid-4" style="gap:10px; align-items: end;">
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Nama Barang</label>
                    <input type="text" id="item_nama" class="form-control" placeholder="Ketik nama barang...">
                </div>
                <div class="form-group">
                    <label class="form-label">Qty</label>
                    <input type="number" id="item_qty" class="form-control" value="1" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Satuan</label>
                    <input type="text" id="item_satuan" class="form-control" placeholder="Pcs, Box, dll">
                </div>
            </div>
            <div style="text-align: right; margin-top: 8px;">
                <button class="btn btn-primary" onclick="addItem()"><i class="fas fa-plus"></i> TAMBAH KE DAFTAR</button>
            </div>
        </div>

        <table class="data-table" style="font-size: 12px;">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>Nama Barang</th>
                    <th width="100">Qty</th>
                    <th width="150">Satuan</th>
                    <th width="80">Aksi</th>
                </tr>
            </thead>
            <tbody id="pr-items">
                <tr id="empty-row"><td colspan="5" class="text-center">Belum ada barang ditambahkan</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 20px; text-align: right;">
    <button class="btn btn-success" onclick="savePR()" style="padding: 12px 24px; font-size: 16px;"><i class="fas fa-paper-plane"></i> AJUKAN PERMINTAAN</button>
</div>

<script>
let prItems = [];

function newPR() {
    $('#no_pr').val('');
    $('#keterangan').val('');
    prItems = [];
    renderItems();
}

function addItem() {
    const nama = $('#item_nama').val().trim();
    const qty = parseFloat($('#item_qty').val());
    const satuan = $('#item_satuan').val().trim();

    if(!nama) return alert('Nama barang harus diisi');
    if(!qty || qty <= 0) return alert('Qty tidak valid');
    if(!satuan) return alert('Satuan harus diisi');

    prItems.push({
        barang: nama,
        qty: qty,
        satuan: satuan
    });

    // Reset form
    $('#item_nama').val('');
    $('#item_qty').val('1');
    $('#item_satuan').val('');
    $('#item_nama').focus();

    renderItems();
}

function removeItem(index) {
    prItems.splice(index, 1);
    renderItems();
}

function renderItems() {
    const tbody = $('#pr-items');
    tbody.empty();

    if(prItems.length === 0) {
        tbody.append('<tr id="empty-row"><td colspan="5" class="text-center">Belum ada barang ditambahkan</td></tr>');
        return;
    }

    prItems.forEach((item, index) => {
        tbody.append(`
            <tr>
                <td>${index + 1}</td>
                <td><span style="font-weight:600;">${item.barang}</span></td>
                <td>${item.qty}</td>
                <td>${item.satuan}</td>
                <td>
                    <button class="btn btn-danger" style="padding: 2px 6px;" onclick="removeItem(${index})"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `);
    });
}

function savePR() {
    const no_pr = $('#no_pr').val().trim();
    const tgl_pr = $('#tgl_pr').val();
    const unit = $('#unit').val();
    const keterangan = $('#keterangan').val().trim();

    if(!no_pr) return alert("Silakan isi No. PR");
    if(prItems.length === 0) return alert("Tambahkan minimal 1 barang");

    const payload = {
        no_pr: no_pr,
        tgl_pr: tgl_pr,
        unit: unit,
        keterangan: keterangan,
        items: prItems
    };

    const btn = event.currentTarget;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    btn.disabled = true;

    $.ajax({
        url: 'api/pr.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function(res) {
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> AJUKAN PERMINTAAN';
            btn.disabled = false;
            if(res.success) {
                alert('Permintaan berhasil diajukan dan menunggu persetujuan atasan.');
                newPR();
            }
        },
        error: function(xhr) {
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> AJUKAN PERMINTAAN';
            btn.disabled = false;
            let err = 'Gagal menyimpan';
            try { err = JSON.parse(xhr.responseText).error; } catch(e){}
            alert(err);
        }
    });
}
</script>
