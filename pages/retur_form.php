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
    color: #d32f2f;
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
    <button class="btn btn-danger" onclick="newRetur()"><i class="fas fa-plus"></i> RETUR BARU</button>
</div>

<!-- Header Panel -->
<div class="material-panel">
    <div class="material-panel-title">Informasi Retur Pembelian</div>
    <div class="material-panel-body">
        <div class="grid-4" style="gap:15px; margin-bottom: 10px;">
            <div class="form-group">
                <label class="form-label">Cari No. SP (Yang bermasalah)</label>
                <div style="display:flex; gap:5px;">
                    <input type="text" id="search_sp" class="form-control" placeholder="Ketik No SP...">
                    <button class="btn btn-danger" onclick="loadSP()"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">No. Retur</label>
                <input type="text" id="no_retur" class="form-control" placeholder="RTR/...">
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Retur</label>
                <input type="date" id="tgl_retur" class="form-control" value="<?php echo date('Y-m-d'); ?>">
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
    <div class="material-panel-title" style="color:#444;">Barang yang Dikembalikan</div>
    <div class="material-panel-body">
        <div class="table-responsive">
            <table class="data-table" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th>Nama Barang</th>
                        <th width="80">Qty Pesan</th>
                        <th width="100">Qty Retur</th>
                        <th width="80">Satuan</th>
                        <th width="300">Alasan Retur</th>
                    </tr>
                </thead>
                <tbody id="retur-items">
                    <tr id="empty-row"><td colspan="6" class="text-center">Silakan cari No. SP terlebih dahulu</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top: 20px; text-align: right;">
    <button class="btn btn-danger" onclick="saveRetur()" style="padding: 12px 24px; font-size: 16px;"><i class="fas fa-undo"></i> PROSES RETUR BARANG</button>
</div>

<script>
let returItems = [];

function newRetur() {
    $('#search_sp').val('');
    $('#no_retur').val('');
    $('#namasup').val('');
    returItems = [];
    renderItems();
}

function loadSP() {
    const no_sp = $('#search_sp').val().trim();
    if(!no_sp) return alert("Ketik No. SP");

    const btn = event.currentTarget;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    $.ajax({
        url: 'api/retur.php?no_sp=' + encodeURIComponent(no_sp),
        method: 'GET',
        success: function(res) {
            btn.innerHTML = '<i class="fas fa-search"></i>';
            if(res.header) {
                $('#namasup').val(res.header.namasup);
                returItems = res.items;
                renderItems();
            } else {
                alert("SP tidak ditemukan");
                newRetur();
            }
        },
        error: function() {
            btn.innerHTML = '<i class="fas fa-search"></i>';
            alert("Gagal mencari SP");
        }
    });
}

function updateQtyRetur(index, val) {
    returItems[index].qty_retur = val;
}
function updateAlasan(index, val) {
    returItems[index].alasan = val;
}

function renderItems() {
    const tbody = $('#retur-items');
    tbody.empty();

    if(returItems.length === 0) {
        tbody.append('<tr id="empty-row"><td colspan="6" class="text-center">Tidak ada barang</td></tr>');
        return;
    }

    returItems.forEach((item, index) => {
        tbody.append(`
            <tr>
                <td>${index + 1}</td>
                <td><span style="font-weight:600;">${item.barang}</span></td>
                <td>${item.qty_pesan}</td>
                <td>
                    <input type="number" class="form-control" value="${item.qty_retur}" onchange="updateQtyRetur(${index}, this.value)" style="width:80px; text-align:center;" min="0" placeholder="0">
                </td>
                <td>${item.satuan}</td>
                <td>
                    <input type="text" class="form-control" value="${item.alasan}" onchange="updateAlasan(${index}, this.value)" placeholder="Tulis alasan jika retur...">
                </td>
            </tr>
        `);
    });
}

function saveRetur() {
    const no_retur = $('#no_retur').val().trim();
    const tgl_retur = $('#tgl_retur').val();
    const no_sp = $('#search_sp').val().trim();

    if(!no_retur) return alert("Silakan isi No. Retur");
    if(returItems.length === 0) return alert("Daftar barang kosong");

    // Validasi apakah ada yg diretur
    let hasRetur = false;
    for(let i=0; i<returItems.length; i++) {
        if(returItems[i].qty_retur > 0) {
            hasRetur = true;
            if(!returItems[i].alasan) return alert("Barang yang diretur wajib diisi alasannya!");
        }
    }

    if(!hasRetur) return alert("Isi Qty Retur (minimal 1 barang harus diretur)");

    const payload = {
        no_retur: no_retur,
        tgl_retur: tgl_retur,
        no_sp: no_sp,
        items: returItems
    };

    const btn = event.currentTarget;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    btn.disabled = true;

    $.ajax({
        url: 'api/retur.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function(res) {
            btn.innerHTML = '<i class="fas fa-undo"></i> PROSES RETUR BARANG';
            btn.disabled = false;
            if(res.success) {
                alert('Retur barang berhasil disimpan!');
                newRetur();
            }
        },
        error: function(xhr) {
            btn.innerHTML = '<i class="fas fa-undo"></i> PROSES RETUR BARANG';
            btn.disabled = false;
            let err = 'Gagal memproses';
            try { err = JSON.parse(xhr.responseText).error; } catch(e){}
            alert(err);
        }
    });
}
</script>
