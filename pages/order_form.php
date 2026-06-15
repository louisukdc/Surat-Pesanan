<style>
.material-panel {
    background: #fff;
    border-top: 15px solid rgba(179, 178, 175, 0.12);
    border-bottom: 15px solid rgba(179, 178, 175, 0.12);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 15px; /* Reduced margin */
    border-left: 1px solid #ddd;
    border-right: 1px solid #ddd;
    border-radius: 4px;
}
.material-panel-title {
    color: #2e7d32;
    font-weight: 700;
    font-size: 15px;
    padding: 10px 15px 5px 15px; /* Reduced padding */
}
.material-panel-body {
    padding: 5px 15px 15px 15px; /* Reduced padding */
    max-height: 40vh;
    overflow-y: auto;
}
/* Compact form styling to reduce scrolling */
.material-panel .form-group {
    margin-bottom: 8px;
}
.material-panel .form-label {
    margin-bottom: 2px;
    font-size: 11px;
    font-weight: 600;
}
.material-panel .form-control {
    padding: 4px 8px;
    height: 30px;
    font-size: 12px;
}
.material-panel .grid-2 {
    gap: 15px; /* Reduce gap between grids */
}
.material-panel .grid-4 {
    gap: 10px; /* Reduce gap between grids */
}
.material-panel .btn {
    padding: 4px 12px;
    font-size: 12px;
}

/* Scrollbar styling for material panel */
.material-panel-body::-webkit-scrollbar {
    width: 8px;
}
.material-panel-body::-webkit-scrollbar-track {
    background: #f1f1f1; 
}
.material-panel-body::-webkit-scrollbar-thumb {
    background: rgba(179, 178, 175, 0.5); 
    border-radius: 4px;
}
.material-panel-body::-webkit-scrollbar-thumb:hover {
    background: rgba(179, 178, 175, 0.8); 
}
</style>

<!-- <div class="toolbar">
    <button class="btn btn-primary" onclick="newOrder()"><i class="fas fa-plus"></i> BARU</button>
    <button class="btn btn-outline" onclick="searchOrder()"><i class="fas fa-search"></i> CARI DATA</button>
    <button class="btn btn-outline" onclick="printOrder()"><i class="fas fa-print"></i> CETAK</button>
</div> -->

<!-- <div class="card" style="margin-bottom: 30px; background: #e3f2fd; border: 1px solid #90caf9;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <h4 style="margin:0; color: #1565c0;"><i class="fas fa-file-import"></i> Import Pesanan Massal (Excel/CSV/DBF)</h4>
            <p style="margin: 5px 0 0 0; font-size: 13px; color: #0d47a1;">Unggah file untuk membuat banyak surat pesanan sekaligus secara otomatis.</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button class="btn btn-outline" onclick="downloadTemplate()" style="background: white; border-color: #1565c0; color: #1565c0;"><i class="fas fa-download"></i> Unduh Template</button>
            <input type="file" id="importFile" accept=".xlsx, .xls, .csv, .dbf" style="display: none;" onchange="handleImport(event)">
            <button class="btn btn-primary" onclick="document.getElementById('importFile').click()"><i class="fas fa-upload"></i> Unggah File</button>
        </div>
    </div>
</div> -->

<!-- Panel 1: Informasi Dasar -->
<div class="material-panel">
    <div class="material-panel-title">Informasi Dasar Pesanan</div>
    <div class="material-panel-body">
        <div class="grid-2">
            <div>
                <div class="form-group position-relative">
                    <label class="form-label">Kepada Yth. (Supplier)</label>
                    <input type="hidden" id="kodesup">
                    <input type="text" id="namasup" class="form-control" placeholder="Ketik nama supplier..." onkeyup="searchSupplier(this.value)">
                    <div id="supplier-suggestions" class="suggestions-box"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Acc Pesanan</label>
                    <input type="text" id="user_acc" class="form-control" value="PEMBELIAN2 # ktr_adm">
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Surat Penawaran No.</label>
                        <input type="text" id="no_tawar" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tertanggal</label>
                        <input type="date" id="tgl_tawar" class="form-control">
                    </div>
                </div>
            </div>
            <div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">No. SP</label>
                        <input type="text" id="no_sp" class="form-control" placeholder="PO/XXXXX/XX/XX">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tgl. SP</label>
                        <input type="date" id="tgl_sp" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Unit / Bagian</label>
                    <select id="unit" class="form-control">
                        <option value="PRODIST. MAKANAN">PRODIST. MAKANAN</option>
                        <option value="MIRM">MIRM</option>
                        <option value="KERUMAHTANGGAAN">KERUMAHTANGGAAN</option>
                        <option value="HOSPITAL DEVELOPMENT">HOSPITAL DEVELOPMENT</option>
                        <option value="GIZI">GIZI</option>
                        <option value="UMUM">UMUM</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Cara pembayaran</label>
                    <input type="text" id="pembayaran" class="form-control">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Panel 2: Detail Barang & Ringkasan -->
<div class="material-panel">
    <div class="material-panel-title">Detail Barang Pesanan</div>
    <div class="material-panel-body">
        
        <div style="background: var(--input-bg); padding: 16px; border-radius: var(--radius-md); margin-bottom: 20px;">
            <div class="grid-4">
                <div class="form-group">
                    <label class="form-label">Nama Barang</label>
                    <input type="text" id="item_barang" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Merk</label>
                    <input type="text" id="item_merk" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Type / Model</label>
                    <input type="text" id="item_model" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Spesifikasi</label>
                    <input type="text" id="item_spec" class="form-control">
                </div>
            </div>
            <div class="grid-4">
                <div class="form-group">
                    <label class="form-label">Qty</label>
                    <input type="number" id="item_qty" class="form-control" value="1" onchange="calculateItemTotal()">
                </div>
                <div class="form-group">
                    <label class="form-label">Satuan</label>
                    <input type="text" id="item_satuan" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Satuan</label>
                    <input type="number" id="item_harga" class="form-control" value="0" onkeyup="calculateItemTotal()">
                </div>
                <div class="form-group">
                    <label class="form-label">Disc</label>
                    <input type="number" id="item_potongan" class="form-control" value="0" onkeyup="calculateItemTotal()">
                </div>
            </div>
            <div class="grid-4" style="align-items: end;">
                <div class="form-group">
                    <label class="form-label">Subtotal</label>
                    <input type="number" id="item_total" class="form-control" readonly style="background: #e2e8f0; font-weight: bold;">
                </div>
                <div class="form-group" style="grid-column: span 3;">
                    <button class="btn btn-primary" onclick="addItem()" style="background-color: #009688; border-color: #009688;"><i class="fas fa-plus"></i> TAMBAH</button>
                    <button class="btn btn-outline" onclick="clearItemForm()"><i class="fas fa-eraser"></i> BERSIHKAN</button>
                </div>
            </div>
        </div>

        <div class="table-responsive" style="margin-bottom: 10px;">
            <table class="data-table" id="itemsTable">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Merk</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Satuan</th>
                        <th class="text-right">Harga</th>
                        <th class="text-right">Disc</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Items will be added here -->
                </tbody>
            </table>
        </div>

        <div class="section-title" style="margin-top: 20px;">Ringkasan & Catatan Akhir</div>
        <div class="grid-2">
            <div>
                <div class="form-group">
                    <label class="form-label">Catatan Umum</label>
                    <textarea id="noteout" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">* Catatan Intern 1</label>
                    <input type="text" id="noteout1" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">* Catatan Intern 2</label>
                    <input type="text" id="noteout2" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">* Acc Pesanan Note (notein)</label>
                    <input type="text" id="notein" class="form-control">
                </div>
            </div>
            <div>
                <div class="totals-box">
                    <div class="totals-row">
                        <span>Sub Total:</span>
                        <span id="lbl_subtotal">0.00</span>
                    </div>
                    <div class="totals-row">
                        <span style="display: flex; align-items: center; gap: 10px;">
                            PPN: <input type="number" id="ppn_input" value="0" class="form-control input-small" style="width: 120px;" onkeyup="calculateGrandTotal()" onchange="calculateGrandTotal()">
                        </span>
                        <span id="lbl_ppn">0.00</span>
                    </div>
                    <div class="totals-row grand-total">
                        <span>Grand Total:</span>
                        <span id="lbl_grandtotal">0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="margin-top: 20px; text-align: right;">
    <button class="btn btn-success" onclick="saveOrder()" style="padding: 12px 24px; font-size: 16px;"><i class="fas fa-save"></i> SIMPAN PESANAN</button>
</div>

<script>
let orderItems = [];

// Format Currency
function formatCurrency(num) {
    return parseFloat(num).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Search Supplier Autocomplete
let searchTimeout;
function searchSupplier(query) {
    clearTimeout(searchTimeout);
    if(query.length < 2) {
        $('#supplier-suggestions').hide();
        return;
    }
    
    searchTimeout = setTimeout(() => {
        $.get('api/orders.php?search_supplier=' + encodeURIComponent(query), function(data) {
            let html = '';
            if(data.length > 0) {
                data.forEach(sup => {
                    html += `<div class="suggestion-item" onclick="selectSupplier('${sup.KodeSupplier.trim()}', '${sup.NamaSupplier.trim()}')">
                                <strong>${sup.KodeSupplier}</strong> - ${sup.NamaSupplier}
                             </div>`;
                });
                $('#supplier-suggestions').html(html).show();
            } else {
                $('#supplier-suggestions').hide();
            }
        });
    }, 300);
}

function selectSupplier(kode, nama) {
    $('#kodesup').val(kode);
    $('#namasup').val(nama);
    $('#supplier-suggestions').hide();
}

$(document).click(function(e) {
    if (!$(e.target).closest('.position-relative').length) {
        $('#supplier-suggestions').hide();
    }
});

// Item Calculations
function calculateItemTotal() {
    let qty = parseFloat($('#item_qty').val()) || 0;
    let harga = parseFloat($('#item_harga').val()) || 0;
    let disc = parseFloat($('#item_potongan').val()) || 0;
    let total = (qty * harga) - disc;
    $('#item_total').val(total);
}

function clearItemForm() {
    $('#item_barang, #item_merk, #item_model, #item_spec, #item_satuan').val('');
    $('#item_qty').val(1);
    $('#item_harga, #item_potongan, #item_total').val(0);
}

function addItem() {
    let item = {
        barang: $('#item_barang').val(),
        merk: $('#item_merk').val(),
        model: $('#item_model').val(),
        spec: $('#item_spec').val(),
        qty: parseFloat($('#item_qty').val()) || 0,
        satuan: $('#item_satuan').val(),
        harga: parseFloat($('#item_harga').val()) || 0,
        potongan: parseFloat($('#item_potongan').val()) || 0,
        total: parseFloat($('#item_total').val()) || 0
    };
    
    if(!item.barang) {
        alert("Nama barang harus diisi!");
        return;
    }
    
    orderItems.push(item);
    renderItemsTable();
    clearItemForm();
    calculateGrandTotal();
}

function removeItem(index) {
    orderItems.splice(index, 1);
    renderItemsTable();
    calculateGrandTotal();
}

function updateInlineItem(index, field, value) {
    orderItems[index][field] = parseFloat(value) || 0;
    // Auto calculate total
    orderItems[index].total = (orderItems[index].qty * orderItems[index].harga) - orderItems[index].potongan;
    renderItemsTable();
    calculateGrandTotal();
}

function renderItemsTable() {
    let html = '';
    orderItems.forEach((item, idx) => {
        html += `<tr>
            <td>${item.barang}</td>
            <td>${item.merk}</td>
            <td>${item.model}</td>
            <td>
                <input type="number" class="form-control" value="${item.qty}" onchange="updateInlineItem(${idx}, 'qty', this.value)" style="width:60px;">
            </td>
            <td>${item.satuan}</td>
            <td class="text-right">
                <input type="number" class="form-control text-right" value="${item.harga}" onchange="updateInlineItem(${idx}, 'harga', this.value)" style="width:100px;">
            </td>
            <td class="text-right">
                <input type="number" class="form-control text-right" value="${item.potongan}" onchange="updateInlineItem(${idx}, 'potongan', this.value)" style="width:80px;">
            </td>
            <td class="text-right" style="font-weight:bold; vertical-align:middle;">
                ${formatCurrency(item.total)}
            </td>
            <td class="text-center">
                <button class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;" onclick="removeItem(${idx})"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`;
    });
    $('#itemsTable tbody').html(html);
}

function calculateGrandTotal() {
    let subtotal = orderItems.reduce((sum, item) => sum + item.total, 0);
    $('#lbl_subtotal').text(formatCurrency(subtotal));
    
    let ppn = parseFloat($('#ppn_input').val()) || 0;
    $('#lbl_ppn').text(formatCurrency(ppn));
    
    let grandTotal = subtotal + ppn;
    $('#lbl_grandtotal').text(formatCurrency(grandTotal));
}

// Actions
function newOrder() {
    if(confirm("Buat pesanan baru? Data yang belum disimpan akan hilang.")) {
        $('input[type="text"]:not(#user_acc), input[type="hidden"], textarea').val('');
        $('#ppn_input').val(0);
        orderItems = [];
        renderItemsTable();
        calculateGrandTotal();
    }
}

function searchOrder() {
    let no_sp = prompt("Masukkan No. SP (misal: PO/10188/09/25):");
    if(no_sp) {
        loadOrderData(no_sp);
    }
}

function loadOrderData(no_sp) {
    $.get('api/orders.php?no_sp=' + encodeURIComponent(no_sp), function(res) {
        if(res.error) {
            alert(res.error);
            return;
        }
        
        let h = res.header;
        $('#no_sp').val(h.no_sp);
        $('#tgl_sp').val(h.tgl_sp);
        $('#kodesup').val(h.kodesup);
        $('#namasup').val(h.namasup);
        $('#user_acc').val(h.user);
        $('#no_tawar').val(h.no_tawar);
        $('#tgl_tawar').val(h.tgl_tawar);
        $('#unit').val(h.unit);
        $('#pembayaran').val(h.pembayaran);
        $('#noteout').val(h.noteout);
        $('#noteout1').val(h.noteout1);
        $('#noteout2').val(h.noteout2);
        $('#notein').val(h.notein);
        $('#ppn_input').val(h.ppn);
        
        orderItems = res.items;
        renderItemsTable();
        calculateGrandTotal();
        
    }, 'json');
}

function loadPRData(no_pr) {
    $.get('api/pr.php?no_pr=' + encodeURIComponent(no_pr), function(res) {
        if(res.header) {
            let suggestedPO = res.header.no_pr.replace(/^PR/i, 'PO');
            $('#no_sp').val(suggestedPO);
            $('#unit').val(res.header.unit);
            $('#noteout').val("Referensi PR: " + res.header.no_pr + "\nKeterangan: " + res.header.keterangan);
            
            orderItems = res.items.map(item => ({
                barang: item.barang,
                merk: '',
                model: '',
                spec: '',
                qty: parseFloat(item.qty) || 0,
                satuan: item.satuan,
                harga: 0,
                potongan: 0,
                total: 0
            }));
            
            renderItemsTable();
            calculateGrandTotal();
        }
    }, 'json');
}

function saveOrder() {
    let noSp = $('#no_sp').val().trim();
    if(!noSp || !$('#kodesup').val() || orderItems.length === 0) {
        alert("No. SP, Supplier, dan Item tidak boleh kosong!");
        return;
    }
    
    // Validasi format No SP
    const spFormat = /^PO\/\d+\/\d{2}\/\d{2}$/i;
    if (!spFormat.test(noSp)) {
        alert("Format No. SP salah!\nSilakan tulis ulang sesuai format: PO/XXX/XX/XX\nContoh: PO/001/01/26 atau PO/10188/09/25");
        $('#no_sp').focus();
        return;
    }
    
    let subtotal = orderItems.reduce((sum, item) => sum + item.total, 0);
    let ppn = parseFloat($('#ppn_input').val()) || 0;
    let grand_total = subtotal + ppn;
    
    let payload = {
        header: {
            no_sp: $('#no_sp').val(),
            tgl_sp: $('#tgl_sp').val(),
            namasup: $('#namasup').val(),
            kodesup: $('#kodesup').val(),
            no_tawar: $('#no_tawar').val(),
            tgl_tawar: $('#tgl_tawar').val(),
            unit: $('#unit').val(),
            pembayaran: $('#pembayaran').val(),
            noteout: $('#noteout').val(),
            noteout1: $('#noteout1').val(),
            noteout2: $('#noteout2').val(),
            notein: $('#notein').val(),
            user: $('#user_acc').val(),
            ppn: ppn,
            grand_total: grand_total
        },
        items: orderItems
    };
    
    $.ajax({
        url: 'api/orders.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function(res) {
            if(res.success) {
                alert("Pesanan berhasil disimpan!");
                window.location.href = 'dashboard.php?page=list_pesanan';
            } else {
                alert("Gagal menyimpan: " + res.error);
            }
        },
        error: function(err) {
            alert("Error: " + (err.responseJSON ? err.responseJSON.error : 'Unknown error'));
        }
    });
}

$(document).ready(function() {
    <?php if(isset($_GET['load'])): ?>
    let loadSp = "<?php echo addslashes($_GET['load']); ?>";
    if(loadSp) {
        loadOrderData(loadSp);
    }
    <?php endif; ?>

    <?php if(isset($_GET['loadpr'])): ?>
    let loadPr = "<?php echo addslashes($_GET['loadpr']); ?>";
    if(loadPr) {
        loadPRData(loadPr);
    }
    <?php endif; ?>
});

// Import Logic
function downloadTemplate() {
    const ws_data = [
        ["No_SP", "Tanggal_SP", "Kode_Supplier", "Nama_Supplier", "Unit", "Barang", "Qty", "Harga", "Satuan", "PPN_Persen"],
        ["PO/001/01/26", "2026-01-01", "S001", "PT Medika Sehat", "Farmasi", "Paracetamol", 100, 5000, "Box", 11],
        ["PO/001/01/26", "2026-01-01", "S001", "PT Medika Sehat", "Farmasi", "Amoxicillin", 50, 12000, "Box", 11],
        ["PO/002/01/26", "2026-01-02", "S002", "Apotek Maju", "IGD", "Jarum Suntik", 200, 1500, "Pcs", 0]
    ];
    const ws = XLSX.utils.aoa_to_sheet(ws_data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Template_Pesanan");
    XLSX.writeFile(wb, "Template_Import_Pesanan.xlsx");
}

async function handleImport(event) {
    const file = event.target.files[0];
    if(!file) return;
    
    const reader = new FileReader();
    reader.onload = async function(e) {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, {type: 'array'});
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            const json = XLSX.utils.sheet_to_json(worksheet);
            
            if(json.length === 0) {
                alert("File kosong!");
                return;
            }
            
            // Group by No_SP
            const orders = {};
            json.forEach(row => {
                const no_sp = row['No_SP'];
                if(!no_sp) return;
                
                if(!orders[no_sp]) {
                    orders[no_sp] = {
                        header: {
                            no_sp: no_sp,
                            tgl_sp: row['Tanggal_SP'] || new Date().toISOString().slice(0,10),
                            kodesup: row['Kode_Supplier'] || '',
                            namasup: row['Nama_Supplier'] || '',
                            unit: row['Unit'] || 'UMUM',
                            user: 'System Import',
                            ppn: 0,
                            grand_total: 0
                        },
                        items: [],
                        ppn_persen: parseFloat(row['PPN_Persen']) || 0
                    };
                }
                
                const qty = parseFloat(row['Qty']) || 0;
                const harga = parseFloat(row['Harga']) || 0;
                const total = qty * harga;
                
                orders[no_sp].items.push({
                    barang: row['Barang'] || 'Unknown Item',
                    qty: qty,
                    satuan: row['Satuan'] || 'Pcs',
                    harga: harga,
                    total: total,
                    potongan: 0,
                    merk: '', model: '', spec: ''
                });
            });
            
            let successCount = 0;
            const orderKeys = Object.keys(orders);
            
            if(!confirm(`Ditemukan ${orderKeys.length} pesanan. Mulai proses import?`)) {
                event.target.value = '';
                return;
            }
            
            for(let key of orderKeys) {
                let order = orders[key];
                let subtotal = order.items.reduce((sum, item) => sum + item.total, 0);
                order.header.ppn = subtotal * (order.ppn_persen / 100);
                order.header.grand_total = subtotal + order.header.ppn;
                
                // Ensure null safety for other fields
                order.header.no_tawar = '';
                order.header.tgl_tawar = '';
                order.header.pembayaran = '';
                order.header.noteout = '';
                order.header.noteout1 = '';
                order.header.noteout2 = '';
                order.header.notein = '';
                
                try {
                    await $.ajax({
                        url: 'api/orders.php',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            header: order.header,
                            items: order.items
                        })
                    });
                    successCount++;
                } catch(err) {
                    console.error("Gagal import SP: " + key, err);
                }
            }
            
            alert(`Import selesai! Berhasil: ${successCount} dari ${orderKeys.length} pesanan.`);
            window.location.href = 'dashboard.php?page=list_pesanan';
            
        } catch(err) {
            alert("Gagal membaca file: " + err.message);
        }
        event.target.value = ''; // reset file input
    };
    reader.readAsArrayBuffer(file);
}
</script>
