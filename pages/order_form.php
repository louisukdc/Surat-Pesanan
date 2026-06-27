<style>
/* Custom Offline CSS matching the requested enterprise layout */
.sp-container {
    width: 100%;
    position: relative;
    padding-bottom: 96px; /* space for sticky bar */
    font-family: inherit;
    box-sizing: border-box;
}

.sp-panel {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    border: 1px solid #e5e7eb;
    margin-bottom: 24px;
}

.sp-panel-header {
    padding: 10px 14px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f9fafb;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

.sp-panel-title {
    font-size: 18px;
    font-weight: 700;
    color: #0f766e;
    margin: 0;
    display: flex;
    align-items: center;
}
.sp-panel-title i { margin-right: 8px; }

.sp-panel-body { padding: 16px; }

/* Grid Systems */
.sp-grid { display: grid; gap: 16px; }
.sp-grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
.sp-grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
.sp-grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
.sp-grid-cols-1 { grid-template-columns: repeat(1, 1fr); }

@media (min-width: 1024px) {
    .lg-grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
    .lg-col-span-3 { grid-column: span 3 / span 3; }
    .lg-col-span-1 { grid-column: span 1 / span 1; }
}
@media (min-width: 1280px) {
    .xl-grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
    .xl-col-span-2 { grid-column: span 2 / span 2; }
    .xl-col-span-4 { grid-column: span 4 / span 4; }
}

/* Flexbox utilities */
.sp-flex { display: flex; }
.sp-flex-col { flex-direction: column; }
.sp-items-center { align-items: center; }
.sp-justify-between { justify-content: space-between; }
.sp-justify-end { justify-content: flex-end; }
.sp-justify-center { justify-content: center; }
.sp-gap-2 { gap: 8px; }
.sp-gap-3 { gap: 12px; }
.sp-gap-4 { gap: 16px; }
.sp-gap-6 { gap: 24px; }
.sp-flex-grow { flex-grow: 1; }

/* Inputs and Labels */
.sp-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
}
.sp-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 15px;
    line-height: 20px;
    transition: all 0.2s;
    box-sizing: border-box;
    font-family: inherit;
}
.sp-input:focus {
    outline: none;
    border-color: #0d9488;
    box-shadow: 0 0 0 2px rgba(13, 148, 136, 0.2);
}
.sp-input-readonly {
    background-color: transparent;
    border: none;
    font-size: 24px;
    font-weight: 900;
    color: #134e4a;
    text-align: right;
    width: 160px;
}
.sp-input-readonly:focus { outline: none; box-shadow: none; }

/* Buttons */
.sp-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}
.sp-btn-primary {
    background-color: #0d9488;
    color: white;
    font-weight: 500;
    padding: 8px 16px;
    font-size: 14px;
}
.sp-btn-primary:hover { background-color: #0f766e; }

.sp-btn-success {
    background-color: #059669;
    color: white;
    font-weight: 700;
    padding: 12px 24px;
    font-size: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
.sp-btn-success:hover { background-color: #047857; }

.sp-btn-outline {
    background-color: transparent;
    border: 1px solid #d1d5db;
    color: #374151;
    font-weight: 500;
    padding: 8px 16px;
    font-size: 14px;
}
.sp-btn-outline:hover { background-color: #f9fafb; }

.sp-btn-danger {
    background-color: #ef4444;
    color: white;
    padding: 8px;
    border-radius: 6px;
}
.sp-btn-danger:hover { background-color: #dc2626; }

.sp-btn-icon {
    background: transparent;
    border: none;
    color: #9ca3af;
    font-size: 20px;
    cursor: pointer;
}
.sp-btn-icon:hover { color: #ef4444; }

/* Table */
.sp-table-container {
    overflow-x: auto;
}
.sp-table {
    width: 100%;
    min-width: 800px;
    border-collapse: collapse;
}
.sp-table th {
    background-color: #f9fafb;
    color: #374151;
    font-weight: 600;
    font-size: 15px;
    padding: 10px 12px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}
.sp-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 15px;
    vertical-align: middle;
}
.sp-table tbody tr:hover { background-color: #f9fafb; }

/* Utility Classes */
.sp-text-center { text-align: center; }
.sp-text-right { text-align: right; }
.sp-font-bold { font-weight: 700; }
.sp-font-semibold { font-weight: 600; }
.sp-text-sm { font-size: 15px; }
.sp-text-xs { font-size: 13px; }
.sp-text-lg { font-size: 18px; }
.sp-text-2xl { font-size: 26px; font-weight: 900; }
.sp-text-gray { color: #6b7280; }
.sp-text-teal { color: #0f766e; }
.sp-text-red { color: #ef4444; }
.sp-mt-2 { margin-top: 8px; }
.sp-mt-4 { margin-top: 16px; }
.sp-mb-0 { margin-bottom: 0 !important; }
.sp-pt-4 { padding-top: 16px; }
.sp-w-full { width: 100%; }
.sp-italic { font-style: italic; }

.sp-border-t { border-top: 1px solid #e5e7eb; }
.sp-border-l { border-left: 1px solid #f3f4f6; padding-left: 24px; }

/* Widgets */
.sp-widget {
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
}
.sp-widget-blue { background-color: #eff6ff; border: 1px solid #dbeafe; }
.sp-widget-gray { background-color: #f9fafb; border: 1px solid #f3f4f6; flex-grow: 1; }
.sp-widget-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin: 0 0 8px 0;
}
.sp-widget-blue .sp-widget-title { color: #1e40af; }
.sp-widget-gray .sp-widget-title { color: #6b7280; }

.sp-pulse-dot {
    width: 12px;
    height: 12px;
    background-color: #3b82f6;
    border-radius: 50%;
    animation: sp-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes sp-pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }

/* Sticky Bar */
.sp-sticky-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: #ffffff;
    border-top: 1px solid #e5e7eb;
    box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
    z-index: 40;
    padding: 16px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-left: 260px; /* Sidebar offset */
    transition: margin-left 0.3s;
}

/* Modal */
.sp-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    backdrop-filter: blur(4px);
}
.sp-modal-content {
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    width: 100%;
    max-width: 672px;
    overflow: hidden;
}
.sp-modal-header {
    padding: 16px 24px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f9fafb;
}
.sp-modal-body { padding: 24px; }
.sp-modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #f3f4f6;
    background-color: #f9fafb;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.sp-input-group {
    position: relative;
    display: flex;
    align-items: center;
}
.sp-input-prefix {
    position: absolute;
    left: 12px;
    color: #6b7280;
    font-weight: 500;
}
.sp-input-with-prefix {
    padding-left: 40px;
}

.sp-highlight-box {
    background-color: #f0fdfa;
    border: 1px solid #ccfbf1;
    border-radius: 8px;
    padding: 16px;
    margin-top: 8px;
}

/* Suggestion Box */
.suggestions-box {
    position: absolute;
    width: 100%;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0 0 6px 6px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    z-index: 50;
    max-height: 240px;
    overflow-y: auto;
    display: none;
}
.suggestion-item {
    padding: 8px 16px;
    font-size: 14px;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
}
.suggestion-item:hover { background-color: #f0fdfa; }

@media (max-width: 768px) {
    .sp-sticky-bar { margin-left: 0; }
    .sp-border-l { border-left: none; padding-left: 0; }
}
</style>

<div class="sp-container">
    
    <!-- Header Section -->
    <div class="sp-panel">
        <div class="sp-panel-header">
            <h2 class="sp-panel-title"><i class="fas fa-file-invoice"></i> Informasi Dasar Pesanan (spu_h)</h2>
        </div>
        <div class="sp-panel-body">
            <input type="hidden" id="order_id" value="0">
            
            <div class="sp-grid sp-grid-cols-1 lg-grid-cols-4 sp-gap-6">
                
                <!-- Form Inputs -->
                <div class="lg-col-span-3">
                    <div class="sp-grid sp-grid-cols-1 sp-grid-cols-2 xl-grid-cols-4 sp-gap-4">
                        
                        <div style="position:relative;" class="xl-col-span-2">
                            <label class="sp-label">Kepada Yth. (Supplier)</label>
                            <input type="hidden" id="id_supplier">
                            <input type="text" id="namasup" class="sp-input" placeholder="Ketik nama supplier..." autocomplete="off" onkeyup="searchSupplier(this.value)">
                            <div id="supplier-suggestions" class="suggestions-box"></div>
                        </div>

                        <div class="xl-col-span-2">
                            <label class="sp-label">No. Surat Pesanan</label>
                            <input type="text" id="no_permintaan" class="sp-input" placeholder="Contoh: PO/xxxxx/mm/yy">
                        </div>

                        <div>
                            <label class="sp-label">Tgl. Pesan</label>
                            <input type="date" id="tgl_pesan" class="sp-input" value="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div>
                            <label class="sp-label">Tgl. Kirim</label>
                            <input type="date" id="tgl_kirim" class="sp-input" value="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="xl-col-span-2">
                            <label class="sp-label">Gudang / Unit</label>
                            <input type="text" id="gudang" class="sp-input" placeholder="Masukkan nama gudang...">
                        </div>

                        <div class="xl-col-span-2">
                            <label class="sp-label">Jenis Bayar</label>
                            <select id="jenis_bayar" class="sp-input">
                                <option value="Tunai">Tunai</option>
                                <option value="Kredit">Kredit</option>
                            </select>
                        </div>

                        <div class="xl-col-span-2">
                            <label class="sp-label">Surat Penawaran No.</label>
                            <input type="text" id="no_penawaran" class="sp-input" placeholder="Contoh: xxx/PEN/PBU/mm/yyyy atau S0xxxx">
                        </div>

                        <div class="xl-col-span-2">
                            <label class="sp-label">Tgl. Penawaran</label>
                            <input type="date" id="tgl_penawaran" class="sp-input">
                        </div>

                        <div class="xl-col-span-4 sp-mt-2">
                            <label class="sp-label">Keterangan Umum</label>
                            <textarea id="keterangan" class="sp-input" rows="2" placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Right Widget Area -->
                <div class="lg-col-span-1 sp-border-l sp-flex sp-flex-col">
                    <div class="sp-widget sp-widget-blue">
                        <h3 class="sp-widget-title">Status Dokumen</h3>
                        <div class="sp-flex sp-items-center sp-gap-2">
                            <div class="sp-pulse-dot"></div>
                            <span class="sp-text-sm sp-font-semibold" style="color: #1d4ed8;">Draft / Pembuatan</span>
                        </div>
                    </div>

                    <div class="sp-widget sp-widget-gray">
                        <h3 class="sp-widget-title">Info Supplier</h3>
                        <div id="widget-supplier-info" class="sp-text-sm sp-text-gray sp-mt-2">
                            <i class="fas fa-info-circle" style="color:#9ca3af; margin-right:4px;"></i> Pilih supplier untuk melihat info.
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Items Section -->
    <div class="sp-panel sp-mb-0" style="overflow:hidden;">
        <div class="sp-panel-header">
            <h2 class="sp-panel-title"><i class="fas fa-boxes"></i> Detail Barang Pesanan (spu_d)</h2>
            <button class="sp-btn sp-btn-primary" onclick="openItemModal()"><i class="fas fa-plus"></i> Tambah Item Barang</button>
        </div>
        <div class="sp-table-container">
            <table class="sp-table" id="itemsTable">
                <thead>
                    <tr>
                        <th class="sp-text-center" style="width:48px;">No</th>
                        <th style="width:25%;">Nama Barang</th>
                        <th style="width:16%;">Merk / Model</th>
                        <th class="sp-text-center" style="width:96px;">Qty</th>
                        <th class="sp-text-right" style="width:128px;">Harga Satuan</th>
                        <th class="sp-text-right" style="width:128px;">Diskon</th>
                        <th class="sp-text-right" style="width:100px;">PPN</th>
                        <th class="sp-text-right" style="width:128px;">Subtotal</th>
                        <th class="sp-text-center" style="width:64px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="8" class="sp-text-center sp-text-gray sp-italic" style="padding:32px;">Belum ada barang yang ditambahkan.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Sticky Bottom Bar -->
<div class="sp-sticky-bar">
    <div class="sp-flex sp-items-center sp-gap-4">
        <div class="sp-text-gray sp-font-semibold">Total Item: <span id="lbl_totalitem" class="sp-text-lg" style="color:#1f2937;">0</span></div>
    </div>
    <div class="sp-flex sp-items-center sp-gap-6">
        <div class="sp-text-right">
            <div class="sp-text-xs sp-text-gray sp-font-bold" style="text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Grand Total</div>
            <div class="sp-text-2xl sp-text-teal">Rp <span id="lbl_grandtotal">0.00</span></div>
        </div>
        <button class="sp-btn sp-btn-success" onclick="saveOrder()"><i class="fas fa-save" style="font-size:20px;"></i> SIMPAN PESANAN</button>
    </div>
</div>

<!-- Modal Tambah/Edit Item -->
<div id="itemModal" class="sp-modal-overlay">
    <div class="sp-modal-content">
        <!-- Modal Header -->
        <div class="sp-modal-header">
            <h3 class="sp-panel-title" style="font-size:18px;"><i class="fas fa-box-open"></i> Form Detail Barang</h3>
            <button onclick="closeItemModal()" class="sp-btn-icon"><i class="fas fa-times"></i></button>
        </div>
        
        <!-- Modal Body -->
        <div class="sp-modal-body">
            <div class="sp-grid sp-grid-cols-1 sp-gap-4">
                
                <div class="sp-flex sp-items-center sp-gap-4">
                    <label class="sp-label" style="width: 150px; margin-bottom: 0;">Nama Barang <span class="sp-text-red">*</span></label>
                    <input type="text" id="item_barang" class="sp-input sp-font-semibold" style="flex-grow: 1; font-size:16px; padding:10px 12px;">
                </div>
                
                <div class="sp-flex sp-items-center sp-gap-4">
                    <label class="sp-label" style="width: 150px; margin-bottom: 0;">Spesifikasi</label>
                    <input type="text" id="item_spec" class="sp-input" style="flex-grow: 1;">
                </div>
                
                <div class="sp-flex sp-items-center sp-gap-4">
                    <label class="sp-label" style="width: 150px; margin-bottom: 0;">Merk</label>
                    <input type="text" id="item_merk" class="sp-input" style="flex-grow: 1;">
                </div>
                
                <div class="sp-flex sp-items-center sp-gap-4">
                    <label class="sp-label" style="width: 150px; margin-bottom: 0;">Model</label>
                    <input type="text" id="item_model" class="sp-input" style="flex-grow: 1;">
                </div>
                
                <div class="sp-grid sp-grid-cols-4 sp-gap-3 sp-border-t sp-pt-4 sp-mt-2">
                    <div>
                        <label class="sp-label">Kuantitas (Qty)</label>
                        <input type="number" id="item_qty" class="sp-input sp-text-center sp-text-lg sp-font-semibold" value="1" onchange="calculateItemTotal()">
                    </div>
                    <div>
                        <label class="sp-label">Harga Satuan</label>
                        <div class="sp-input-group">
                            <span class="sp-input-prefix">Rp</span>
                            <input type="number" id="item_harga" class="sp-input sp-input-with-prefix sp-text-right sp-font-semibold" value="0" onkeyup="calculateItemTotal()">
                        </div>
                    </div>
                    <div>
                        <label class="sp-label">Diskon</label>
                        <div class="sp-input-group">
                            <span class="sp-input-prefix">Rp</span>
                            <input type="number" id="item_disc" class="sp-input sp-input-with-prefix sp-text-right sp-font-semibold sp-text-red" value="0" onkeyup="calculateItemTotal()">
                        </div>
                    </div>
                    <div>
                        <label class="sp-label">PPN (%)</label>
                        <div class="sp-input-group">
                            <input type="number" id="item_ppn_persen" class="sp-input sp-text-right sp-font-semibold sp-text-teal" value="11" onkeyup="calculateItemTotal()">
                            <span style="position: absolute; right: 12px; color: #6b7280; font-weight: 500;">%</span>
                        </div>
                        <input type="hidden" id="item_ppn_nominal" value="0">
                    </div>
                </div>
                
                <div class="sp-highlight-box sp-flex sp-justify-between sp-items-center sp-mt-2">
                    <span class="sp-text-sm sp-font-bold sp-text-teal" style="text-transform:uppercase; letter-spacing:0.05em;">Subtotal Item</span>
                    <div class="sp-flex sp-items-center sp-gap-2">
                        <span class="sp-font-bold sp-text-teal">Rp</span>
                        <input type="text" id="item_jumlah" class="sp-input-readonly" readonly value="0">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="sp-modal-footer">
            <button class="sp-btn sp-btn-outline" onclick="closeItemModal()">Batal</button>
            <button class="sp-btn sp-btn-primary" onclick="addItem()"><i class="fas fa-check"></i> Simpan ke Tabel</button>
        </div>
    </div>
</div>

<script>
let orderItems = [];

function formatCurrency(num) {
    return parseFloat(num).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

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
    $('#id_supplier').val(kode);
    $('#namasup').val(nama);
    $('#supplier-suggestions').hide();
    
    // Update Widget Supplier Info
    $('#widget-supplier-info').html(`
        <div class="sp-font-bold" style="color:#1f2937;">${nama}</div>
        <div class="sp-text-xs sp-text-gray sp-mt-2">Kode: ${kode}</div>
    `);
}

$(document).click(function(e) {
    if (!$(e.target).closest('.xl-col-span-2').length) {
        $('#supplier-suggestions').hide();
    }
});

function openItemModal() {
    clearItemForm();
    $('#itemModal').css('display', 'flex');
}

function closeItemModal() {
    $('#itemModal').css('display', 'none');
}

function calculateItemTotal() {
    let qty = parseFloat($('#item_qty').val()) || 0;
    let harga = parseFloat($('#item_harga').val()) || 0;
    let disc = parseFloat($('#item_disc').val()) || 0;
    let ppn_persen = parseFloat($('#item_ppn_persen').val()) || 0;
    
    let dpp = (qty * harga) - disc;
    let ppn_nominal = dpp * (ppn_persen / 100);
    $('#item_ppn_nominal').val(ppn_nominal);
    
    let total = dpp + ppn_nominal;
    $('#item_jumlah').val(formatCurrency(total));
}

function clearItemForm() {
    $('#item_barang, #item_merk, #item_model, #item_spec').val('');
    $('#item_qty').val(1);
    $('#item_harga, #item_disc').val(0);
    $('#item_ppn_persen').val(11);
    $('#item_ppn_nominal').val(0);
    $('#item_jumlah').val('0.00');
}

function addItem() {
    let item = {
        barang: $('#item_barang').val(),
        merk: $('#item_merk').val(),
        model: $('#item_model').val(),
        spec: $('#item_spec').val(),
        qty: parseFloat($('#item_qty').val()) || 0,
        harga: parseFloat($('#item_harga').val()) || 0,
        disc: parseFloat($('#item_disc').val()) || 0,
        ppn_persen: parseFloat($('#item_ppn_persen').val()) || 0,
        ppn: parseFloat($('#item_ppn_nominal').val()) || 0
    };
    item.jumlah = (item.qty * item.harga) - item.disc + item.ppn;
    
    if(!item.barang) {
        alert("Nama barang harus diisi!");
        return;
    }
    
    orderItems.push(item);
    renderItemsTable();
    closeItemModal();
    calculateGrandTotal();
}

function removeItem(index) {
    orderItems.splice(index, 1);
    renderItemsTable();
    calculateGrandTotal();
}

function updateInlineItem(index, field, value) {
    orderItems[index][field] = parseFloat(value) || 0;
    
    // Recalculate PPN and Jumlah
    let dpp = (orderItems[index].qty * orderItems[index].harga) - orderItems[index].disc;
    orderItems[index].ppn = dpp * ((orderItems[index].ppn_persen || 0) / 100);
    orderItems[index].jumlah = dpp + orderItems[index].ppn;
    
    renderItemsTable();
    calculateGrandTotal();
}

function renderItemsTable() {
    let html = '';
    if(orderItems.length === 0) {
        html = '<tr><td colspan="8" class="sp-text-center sp-text-gray sp-italic" style="padding:32px;">Belum ada barang yang ditambahkan.</td></tr>';
    } else {
        orderItems.forEach((item, idx) => {
            html += `<tr>
                <td class="sp-text-center">${idx + 1}</td>
                <td>
                    <div class="sp-font-semibold" style="color:#1f2937;">${item.barang}</div>
                    <div class="sp-text-xs sp-text-gray">${item.spec}</div>
                </td>
                <td>
                    <div class="sp-text-sm">${item.merk}</div>
                    <div class="sp-text-xs sp-text-gray">${item.model}</div>
                </td>
                <td class="sp-text-center">
                    <input type="number" class="sp-input sp-text-center" style="width:70px; padding:4px 8px;" value="${item.qty}" onchange="updateInlineItem(${idx}, 'qty', this.value)">
                </td>
                <td class="sp-text-right">
                    <input type="number" class="sp-input sp-text-right" style="width:110px; padding:4px 8px;" value="${item.harga}" onchange="updateInlineItem(${idx}, 'harga', this.value)">
                </td>
                <td class="sp-text-right">
                    <input type="number" class="sp-input sp-text-right sp-text-red" style="width:90px; padding:4px 8px;" value="${item.disc}" onchange="updateInlineItem(${idx}, 'disc', this.value)">
                </td>
                <td class="sp-text-right">
                    <div style="display:flex; align-items:center; justify-content:flex-end;">
                        <input type="number" class="sp-input sp-text-right sp-text-teal" style="width:60px; padding:4px 8px;" value="${item.ppn_persen || 0}" onchange="updateInlineItem(${idx}, 'ppn_persen', this.value)" title="PPN Persen">
                        <span style="margin-left:4px; font-weight:bold;">%</span>
                    </div>
                    <div class="sp-text-xs sp-text-gray sp-mt-2">Rp ${formatCurrency(item.ppn)}</div>
                </td>
                <td class="sp-text-right sp-font-bold sp-text-teal">
                    ${formatCurrency(item.jumlah)}
                </td>
                <td class="sp-text-center">
                    <button class="sp-btn sp-btn-danger" style="padding:6px 10px;" onclick="removeItem(${idx})" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        });
    }
    $('#itemsTable tbody').html(html);
}

function calculateGrandTotal() {
    let grandTotal = orderItems.reduce((sum, item) => sum + item.jumlah, 0);
    $('#lbl_grandtotal').text(formatCurrency(grandTotal));
    $('#lbl_totalitem').text(orderItems.length);
}

function loadOrderData(id) {
    $.get('api/orders.php?id=' + encodeURIComponent(id), function(res) {
        if(res.error) {
            alert(res.error);
            return;
        }
        
        let h = res.header;
        $('#order_id').val(h.id);
        $('#tgl_pesan').val(h.tgl_pesan);
        $('#tgl_kirim').val(h.tgl_kirim);
        $('#id_supplier').val(h.id_supplier);
        selectSupplier(h.id_supplier, h.namasup);
        $('#gudang').val(h.gudang);
        $('#jenis_bayar').val(h.jenis_bayar);
        $('#no_penawaran').val(h.no_penawaran);
        $('#no_permintaan').val(h.no_permintaan);
        $('#tgl_penawaran').val(h.tgl_penawaran);
        $('#keterangan').val(h.keterangan);
        
        res.items.forEach(item => {
            item.qty = parseFloat(item.qty) || 0;
            item.harga = parseFloat(item.harga) || 0;
            item.disc = parseFloat(item.disc) || 0;
            item.ppn = parseFloat(item.ppn) || 0;
            let dpp = (item.qty * item.harga) - item.disc;
            item.ppn_persen = (dpp > 0 && item.ppn > 0) ? Math.round((item.ppn / dpp) * 100) : 0;
            item.jumlah = dpp + item.ppn;
        });
        orderItems = res.items;
        renderItemsTable();
        calculateGrandTotal();
        
    }, 'json');
}

function saveOrder() {
    let requiredFields = [
        { id: 'id_supplier', name: 'Supplier' },
        { id: 'no_permintaan', name: 'No. Surat Pesanan' },
        { id: 'tgl_pesan', name: 'Tanggal Pesan' },
        { id: 'tgl_kirim', name: 'Tanggal Kirim' },
        { id: 'gudang', name: 'Gudang / Unit' },
        { id: 'no_penawaran', name: 'Surat Penawaran No.' },
        { id: 'tgl_penawaran', name: 'Tanggal Penawaran' }
    ];
    
    for (let field of requiredFields) {
        if (!$('#' + field.id).val() || $('#' + field.id).val().trim() === '') {
            alert("Harap mengisi kolom " + field.name + " yang belum terisi!");
            // Focus on the field (if it's a hidden id_supplier, focus on namasup)
            if(field.id === 'id_supplier') $('#namasup').focus();
            else $('#' + field.id).focus();
            return;
        }
    }
    
    if(orderItems.length === 0) {
        alert("Barang pesanan tidak boleh kosong! Harap tambah minimal 1 item.");
        return;
    }
    
    let payload = {
        header: {
            id: $('#order_id').val(),
            tgl_pesan: $('#tgl_pesan').val(),
            tgl_kirim: $('#tgl_kirim').val(),
            id_supplier: $('#id_supplier').val(),
            gudang: $('#gudang').val(),
            jenis_bayar: $('#jenis_bayar').val(),
            no_penawaran: $('#no_penawaran').val(),
            no_permintaan: $('#no_permintaan').val(),
            tgl_penawaran: $('#tgl_penawaran').val(),
            keterangan: $('#keterangan').val()
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
                alert("Pesanan berhasil disimpan dengan ID: " + res.id);
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
    let loadId = "<?php echo addslashes($_GET['load']); ?>";
    if(loadId) {
        loadOrderData(loadId);
    }
    <?php endif; ?>
});

// Adjust sticky bottom bar on window resize if sidebar toggles
window.addEventListener('resize', () => {
    const bottomBar = document.querySelector('.sp-sticky-bar');
    if(bottomBar) {
        if(window.innerWidth < 768) {
            bottomBar.style.marginLeft = '0px';
        } else {
            bottomBar.style.marginLeft = '260px'; // Matching sidebar width
        }
    }
});
</script>
