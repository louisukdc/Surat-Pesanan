<div class="toolbar">
    <button class="btn btn-primary" onclick="newOrder()"><i class="fas fa-plus"></i> BARU</button>
    <button class="btn btn-outline" onclick="searchOrder()"><i class="fas fa-search"></i> CARI DATA</button>
    <button class="btn btn-outline" onclick="printOrder()"><i class="fas fa-print"></i> CETAK</button>
</div>

<div class="card compact-form">
    <!-- Stepper Navigation -->
    <div class="stepper-wrapper">
        <div class="stepper-item active" id="step1-nav">
            <div class="step-counter">1</div>
            <div class="step-name">Info Dasar</div>
        </div>
        <div class="stepper-item" id="step2-nav">
            <div class="step-counter">2</div>
            <div class="step-name">Detail Barang</div>
        </div>
        <div class="stepper-item" id="step3-nav">
            <div class="step-counter">3</div>
            <div class="step-name">Ringkasan</div>
        </div>
    </div>

    <!-- Step 1: Informasi Dasar -->
    <div class="step-content active" id="step1">
        <div class="section-title">Informasi Dasar Pesanan</div>
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
        <div class="stepper-actions" style="justify-content: flex-end;">
            <button class="btn btn-primary" onclick="nextStep(2)">Lanjut <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>

    <!-- Step 2: Detail Barang -->
    <div class="step-content" id="step2">
        <div class="section-title">Detail Barang Pesanan</div>
        
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
                    <button class="btn btn-primary" onclick="addItem()"><i class="fas fa-plus"></i> TAMBAH</button>
                    <button class="btn btn-outline" onclick="clearItemForm()"><i class="fas fa-eraser"></i> BERSIHKAN</button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
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

        <div class="stepper-actions">
            <button class="btn btn-outline" onclick="prevStep(1)"><i class="fas fa-arrow-left"></i> Kembali</button>
            <button class="btn btn-primary" onclick="nextStep(3)">Lanjut <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>

    <!-- Step 3: Ringkasan & Catatan -->
    <div class="step-content" id="step3">
        <div class="section-title">Ringkasan & Catatan Akhir</div>
        
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

        <div class="stepper-actions">
            <button class="btn btn-outline" onclick="prevStep(2)"><i class="fas fa-arrow-left"></i> Kembali</button>
            <button class="btn btn-success" onclick="saveOrder()"><i class="fas fa-save"></i> SIMPAN PESANAN</button>
        </div>
    </div>
</div>

<script>
let currentStep = 1;
let orderItems = [];

// Navigation Logic
function nextStep(step) {
    if (step === 2) {
        if(!$('#kodesup').val() || !$('#no_sp').val()) {
            alert("Harap lengkapi No. SP dan pilih Supplier terlebih dahulu!");
            return;
        }
    }
    if (step === 3) {
        if(orderItems.length === 0) {
            if(!confirm("Anda belum menambahkan barang. Lanjut ke ringkasan?")) return;
        }
    }
    
    // Update UI
    $('.step-content').removeClass('active');
    $('#step' + step).addClass('active');
    
    // Update Stepper nav
    $('.stepper-item').removeClass('active completed');
    for(let i=1; i<step; i++) {
        $('#step' + i + '-nav').addClass('completed');
    }
    $('#step' + step + '-nav').addClass('active');
    
    currentStep = step;
}

function prevStep(step) {
    $('.step-content').removeClass('active');
    $('#step' + step).addClass('active');
    
    $('.stepper-item').removeClass('active completed');
    for(let i=1; i<step; i++) {
        $('#step' + i + '-nav').addClass('completed');
    }
    $('#step' + step + '-nav').addClass('active');
    
    currentStep = step;
}

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

function renderItemsTable() {
    let html = '';
    orderItems.forEach((item, idx) => {
        html += `<tr>
            <td>${item.barang}</td>
            <td>${item.merk}</td>
            <td>${item.model}</td>
            <td>${item.qty}</td>
            <td>${item.satuan}</td>
            <td class="text-right">${formatCurrency(item.harga)}</td>
            <td class="text-right">${formatCurrency(item.potongan)}</td>
            <td class="text-right">${formatCurrency(item.total)}</td>
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
        prevStep(1); // Go back to step 1
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
        
        // Go to Step 1 automatically so user can review from start
        prevStep(1);
    }, 'json');
}

function saveOrder() {
    if(!$('#no_sp').val() || !$('#kodesup').val() || orderItems.length === 0) {
        alert("No. SP, Supplier, dan Item tidak boleh kosong!");
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

function printOrder() {
    window.print();
}

$(document).ready(function() {
    <?php if(isset($_GET['load'])): ?>
    let loadSp = "<?php echo addslashes($_GET['load']); ?>";
    if(loadSp) {
        loadOrderData(loadSp);
    }
    <?php endif; ?>
});
</script>
