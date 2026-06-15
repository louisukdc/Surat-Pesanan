<style>
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
}
.form-label { margin-bottom: 2px; font-size: 11px; font-weight: 600; }
.form-control { padding: 4px 8px; height: 30px; font-size: 12px; }
.btn { padding: 4px 12px; font-size: 12px; }
</style>

<div class="toolbar">
    <button class="btn btn-primary" onclick="newGR()"><i class="fas fa-plus"></i> PENERIMAAN BARU</button>
</div>

<!-- Header Panel -->
<div class="material-panel">
    <div class="material-panel-title">Informasi Penerimaan Barang (GR)</div>
    <div class="material-panel-body">
        <div class="grid-4" style="gap:15px; margin-bottom: 10px;">
            <div class="form-group">
                <label class="form-label">Cari No. SP</label>
                <div style="display:flex; gap:5px;">
                    <input type="text" id="search_sp" class="form-control" placeholder="Ketik No SP...">
                    <button class="btn btn-primary" onclick="loadSP()"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">No. Terima (GR)</label>
                <input type="text" id="no_gr" class="form-control" placeholder="GR/...">
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Terima</label>
                <input type="date" id="tgl_gr" class="form-control" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Supplier</label>
                <input type="text" id="namasup" class="form-control" readonly style="background:#eee;">
            </div>
        </div>
    </div>
</div>

<!-- Detail Panel -->
<div class="material-panel">
    <div class="material-panel-title">Rincian Barang Diterima</div>
    <div class="material-panel-body">
        <div class="table-responsive">
            <table class="data-table" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th>Nama Barang</th>
                        <th width="80">Qty Pesan</th>
                        <th width="100">Qty Terima</th>
                        <th width="80">Satuan</th>
                        <th width="150">No. Batch</th>
                        <th width="150">Exp Date</th>
                    </tr>
                </thead>
                <tbody id="gr-items">
                    <tr id="empty-row"><td colspan="7" class="text-center">Silakan cari No. SP terlebih dahulu</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top: 20px; text-align: right;">
    <button class="btn btn-success" onclick="saveGR()" style="padding: 12px 24px; font-size: 16px;"><i class="fas fa-save"></i> SIMPAN PENERIMAAN</button>
</div>

<script>
let grItems = [];

function newGR() {
    $('#search_sp').val('');
    $('#no_gr').val('');
    $('#namasup').val('');
    grItems = [];
    renderItems();
}

function loadSP() {
    const no_sp = $('#search_sp').val().trim();
    if(!no_sp) return alert("Ketik No. SP");

    const btn = event.currentTarget;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    $.ajax({
        url: 'api/gr.php?no_sp=' + encodeURIComponent(no_sp),
        method: 'GET',
        success: function(res) {
            btn.innerHTML = '<i class="fas fa-search"></i>';
            if(res.header) {
                $('#namasup').val(res.header.namasup);
                grItems = res.items;
                renderItems();
            } else {
                alert("SP tidak ditemukan");
                newGR();
            }
        },
        error: function() {
            btn.innerHTML = '<i class="fas fa-search"></i>';
            alert("Gagal mencari SP");
        }
    });
}

function updateQty(index, val) {
    grItems[index].qty_terima = val;
}
function updateBatch(index, val) {
    grItems[index].batch_no = val;
}
function updateExp(index, val) {
    grItems[index].exp_date = val;
}

function renderItems() {
    const tbody = $('#gr-items');
    tbody.empty();

    if(grItems.length === 0) {
        tbody.append('<tr id="empty-row"><td colspan="7" class="text-center">Tidak ada barang</td></tr>');
        return;
    }

    grItems.forEach((item, index) => {
        tbody.append(`
            <tr>
                <td>${index + 1}</td>
                <td><span style="font-weight:600;">${item.barang}</span></td>
                <td>${item.qty_pesan}</td>
                <td>
                    <input type="number" class="form-control" value="${item.qty_terima}" onchange="updateQty(${index}, this.value)" style="width:80px; text-align:center;">
                </td>
                <td>${item.satuan}</td>
                <td>
                    <input type="text" class="form-control" value="${item.batch_no}" onchange="updateBatch(${index}, this.value)" placeholder="No Batch">
                </td>
                <td>
                    <input type="date" class="form-control" value="${item.exp_date}" onchange="updateExp(${index}, this.value)">
                </td>
            </tr>
        `);
    });
}

function saveGR() {
    const no_gr = $('#no_gr').val().trim();
    const tgl_gr = $('#tgl_gr').val();
    const no_sp = $('#search_sp').val().trim();

    if(!no_gr) return alert("Silakan isi No. Penerimaan (GR)");
    if(grItems.length === 0) return alert("Daftar barang kosong");

    const payload = {
        no_gr: no_gr,
        tgl_gr: tgl_gr,
        no_sp: no_sp,
        items: grItems
    };

    const btn = event.currentTarget;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    btn.disabled = true;

    $.ajax({
        url: 'api/gr.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function(res) {
            btn.innerHTML = '<i class="fas fa-save"></i> SIMPAN PENERIMAAN';
            btn.disabled = false;
            if(res.success) {
                alert('Penerimaan barang berhasil disimpan!');
                newGR();
            }
        },
        error: function(xhr) {
            btn.innerHTML = '<i class="fas fa-save"></i> SIMPAN PENERIMAAN';
            btn.disabled = false;
            let err = 'Gagal menyimpan';
            try { err = JSON.parse(xhr.responseText).error; } catch(e){}
            alert(err);
        }
    });
}
</script>
