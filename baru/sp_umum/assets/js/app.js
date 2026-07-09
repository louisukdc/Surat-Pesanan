// assets/js/app.js
// JavaScript helper functions for Sistem Surat Pesanan

document.addEventListener('DOMContentLoaded', function () {
    // Initialize standard event listeners
    initRupiahInputs();
    initPurchaseOrderCalculations();
});

/**
 * Format number to Rupiah string
 */
function formatRupiah(number) {
    if (isNaN(number)) return 'Rp 0';
    return 'Rp ' + Math.round(number).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

/**
 * Parse Rupiah string or numeric string into float
 */
function parseRupiah(value) {
    if (!value) return 0;
    // Remove Rp, dots, spaces, and percent signs
    const clean = value.toString().replace(/[^\d]/g, '');
    const num = parseFloat(clean);
    return isNaN(num) ? 0 : num;
}

/**
 * Clean formatting for input fields when submitting
 */
function cleanNumericInputBeforeSubmit(inputElement) {
    inputElement.value = parseRupiah(inputElement.value);
}

/**
 * Apply live formatting to text inputs with class .input-rupiah
 * Uses setTimeout to allow calculation to run AFTER formatting completes
 */
function initRupiahInputs() {
    document.body.addEventListener('input', function (e) {
        if (e.target && e.target.classList.contains('input-rupiah')) {
            var val = parseRupiah(e.target.value);
            if (val === 0) {
                e.target.value = '';
            } else {
                var formatted = val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                e.target.value = formatted;
            }
        }
        // After formatting, recalculate row if it's a price or disc input
        if (e.target && (e.target.classList.contains('row-price') || e.target.classList.contains('row-disc'))) {
            setTimeout(function() { recalcRow(e.target); }, 0);
        }
    });

    // Delegation for qty inputs
    document.body.addEventListener('input', function (e) {
        if (e.target && e.target.classList.contains('row-qty')) {
            recalcRow(e.target);
        }
    });
}

/**
 * Handle Purchase Order Form Math
 */
function initPurchaseOrderCalculations() {
    var hargaInput = document.getElementById('harga_vendor');
    var diskonInput = document.getElementById('diskon_vendor');
    var totalInput = document.getElementById('total_setelah_diskon');
    
    if (!hargaInput || !diskonInput || !totalInput) return;

    function calculateTotal() {
        var harga = parseRupiah(hargaInput.value);
        var diskonRaw = parseRupiah(diskonInput.value);
        var diskonNominal = 0;
        
        var type = 'rp';
        var typeSelect = document.getElementById('diskon_type');
        if (typeSelect) {
            type = typeSelect.value;
        } else {
            var checkedRadio = document.querySelector('input[name="diskon_type"]:checked');
            if (checkedRadio) type = checkedRadio.value;
        }

        if (type === 'percent') {
            if (diskonRaw > 100) {
                diskonRaw = 100;
                diskonInput.value = '100';
            }
            diskonNominal = harga * (diskonRaw / 100);
        } else {
            if (diskonRaw > harga) {
                diskonRaw = harga;
                diskonInput.value = harga.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
            diskonNominal = diskonRaw;
        }

        var total = Math.max(0, harga - diskonNominal);
        totalInput.value = formatRupiah(total);
        
        var rawHarga = document.getElementById('harga_vendor_raw');
        if (rawHarga) rawHarga.value = harga;
        var rawDiskon = document.getElementById('diskon_vendor_raw');
        if (rawDiskon) rawDiskon.value = diskonNominal;
        var rawTotal = document.getElementById('total_setelah_diskon_raw');
        if (rawTotal) rawTotal.value = total;
    }

    hargaInput.addEventListener('input', calculateTotal);
    diskonInput.addEventListener('input', calculateTotal);
    
    var typeSelect2 = document.getElementById('diskon_type');
    if (typeSelect2) {
        typeSelect2.addEventListener('change', function() {
            diskonInput.value = '';
            calculateTotal();
        });
    }

    var radios = document.querySelectorAll('input[name="diskon_type"]');
    for (var ri = 0; ri < radios.length; ri++) {
        radios[ri].addEventListener('change', function() {
            diskonInput.value = '';
            calculateTotal();
        });
    }

    // Run once at start
    calculateTotal();
}

/**
 * Adds a new row in purchase order items dynamic grid
 */
function addPurchaseOrderItemRow() {
    var tableBody = document.getElementById('po-items-body');
    if (!tableBody) return;

    var rowCount = tableBody.rows.length;
    var newRow = document.createElement('tr');

    var satuanOptions = ['pcs','unit','lusin','kodi','rim','roll','box','set','kg','ltr','m','cm'];
    var satHtml = '';
    for (var s = 0; s < satuanOptions.length; s++) {
        satHtml += '<option value="' + satuanOptions[s] + '">' + satuanOptions[s] + '</option>';
    }

    newRow.innerHTML =
        '<td class="text-center align-middle row-number">' + (rowCount + 1) + '</td>' +
        '<td class="table-input-cell"><input type="text" name="nama_barang[]" class="form-control form-control-sm" placeholder="Nama Barang" required></td>' +
        '<td class="table-input-cell"><input type="text" name="merk[]" class="form-control form-control-sm" placeholder="Merk"></td>' +
        '<td class="table-input-cell"><input type="text" name="model[]" class="form-control form-control-sm" placeholder="Model"></td>' +
        '<td class="table-input-cell"><input type="text" name="spec[]" class="form-control form-control-sm" placeholder="Spesifikasi"></td>' +
        '<td class="table-input-cell"><input type="number" name="jumlah[]" class="form-control form-control-sm text-center row-qty" min="0.01" step="0.01" value="1" oninput="calculateRowTotal(this)" required></td>' +
        '<td class="table-input-cell"><select name="satuan[]" class="form-control form-control-sm row-satuan">' + satHtml + '</select></td>' +
        '<td class="table-input-cell">' +
            '<div class="input-group input-group-sm">' +
                '<div class="input-group-prepend"><span class="input-group-text bg-light border-0" style="padding-right:3px;">Rp</span></div>' +
                '<input type="text" name="harga_satuan[]" class="form-control form-control-sm input-rupiah text-right row-price" placeholder="0" oninput="calculateRowTotal(this)" required>' +
            '</div>' +
        '</td>' +
        '<td class="table-input-cell">' +
            '<div class="input-group input-group-sm">' +
                '<div class="input-group-prepend"><span class="input-group-text bg-light border-0" style="padding-right:3px;">Rp</span></div>' +
                '<input type="text" name="disc_item[]" class="form-control form-control-sm input-rupiah text-right row-disc" placeholder="0" oninput="calculateRowTotal(this)">' +
            '</div>' +
        '</td>' +
        '<td class="table-input-cell">' +
            '<div class="input-group input-group-sm">' +
                '<div class="input-group-prepend"><span class="input-group-text bg-transparent border-0 font-weight-bold" style="padding-right:3px;">Rp</span></div>' +
                '<input type="text" class="form-control form-control-sm text-right row-total font-weight-bold bg-transparent border-0" style="padding-left:0;" value="0" readonly>' +
            '</div>' +
        '</td>' +
        '<td class="text-center align-middle">' +
            '<button type="button" class="btn btn-sm btn-danger btn-remove-row" onclick="removePurchaseOrderItemRow(this)"><i class="fa fa-trash"></i></button>' +
        '</td>';

    tableBody.appendChild(newRow);
    renumberPurchaseOrderRows();
}

/**
 * Recalculates total for the row containing the given input element
 * Subtotal = (harga * qty) - diskon_item
 */
function recalcRow(element) {
    var row = element.closest ? element.closest('tr') : (function(el) {
        while (el && el.tagName !== 'TR') el = el.parentNode;
        return el;
    })(element);
    if (!row) return;

    var priceInput = row.querySelector('.row-price');
    var qtyInput   = row.querySelector('.row-qty');
    var discInput  = row.querySelector('.row-disc');
    var totalInput = row.querySelector('.row-total');

    if (!priceInput || !qtyInput || !totalInput) return;

    var price    = parseRupiah(priceInput.value);
    var qty      = parseFloat(qtyInput.value) || 0;
    var disc     = discInput ? parseRupiah(discInput.value) : 0;
    var subtotal = Math.max(0, (price * qty) - disc);

    totalInput.value = subtotal > 0
        ? subtotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
        : '0';

    recalcGrandTotal();
}

/** Kept for backward compat (called from oninput inline HTML if any) */
function calculateRowTotal(element) { recalcRow(element); }

/**
 * Sums all row totals, applies PPN if active, and pushes to Harga Vendor
 */
function recalcGrandTotal() {
    var subtotalBarang = 0;
    var totals = document.querySelectorAll('.row-total');
    for (var t = 0; t < totals.length; t++) {
        subtotalBarang += parseRupiah(totals[t].value);
    }

    // Check if PPN is active
    var ppnToggle = document.getElementById('ppn_toggle');
    var isPPN = ppnToggle && ppnToggle.checked;
    var PPN_RATE = 0.11;

    var ppnNominal = 0;
    var grandTotal  = subtotalBarang;

    if (isPPN) {
        ppnNominal = Math.round(subtotalBarang * PPN_RATE);
        grandTotal = subtotalBarang + ppnNominal;
    }

    // Update PPN nominal display
    var ppnNominalInput = document.getElementById('ppn_nominal');
    if (ppnNominalInput) {
        ppnNominalInput.value = ppnNominal > 0
            ? ppnNominal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
            : '0';
    }

    // Sync ppn nilai hidden
    var ppnNilaiInput = document.getElementById('ppn_nilai');
    if (ppnNilaiInput) ppnNilaiInput.value = ppnNominal;

    var vendorInput = document.getElementById('harga_vendor');
    if (vendorInput) {
        vendorInput.value = grandTotal > 0
            ? grandTotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
            : '0';
        // Trigger the existing discount calculation listener
        var evt = document.createEvent('Event');
        evt.initEvent('input', true, true);
        vendorInput.dispatchEvent(evt);
    }
}

/** Kept for backward compat */
function calculateTotalVendor() { recalcGrandTotal(); }

/**
 * Toggle PPN 11% on/off
 */
function togglePPN(checkbox) {
    const labelEl   = document.getElementById('ppn_label');
    const rowNominal = document.getElementById('row_ppn_nominal');
    const hiddenPPN  = document.getElementById('ppn_aktif');

    if (checkbox.checked) {
        if (labelEl)   labelEl.textContent = 'PPN 11% Dikenakan';
        if (labelEl)   labelEl.className   = 'ml-3 font-weight-bold';
        if (labelEl)   labelEl.style.color  = '#059669';
        if (rowNominal) rowNominal.style.display = '';
        if (hiddenPPN)  hiddenPPN.value = '1';
    } else {
        if (labelEl)   labelEl.textContent = 'Tidak Dikenakan PPN';
        if (labelEl)   labelEl.className   = 'ml-3 text-muted';
        if (labelEl)   labelEl.style.color  = '';
        if (rowNominal) rowNominal.style.display = 'none';
        if (hiddenPPN)  hiddenPPN.value = '0';
    }

    // Recalc immediately
    recalcGrandTotal();
}

// (duplicate parseRupiah removed — using the canonical version defined at top of file)

/**
 * Removes a row in purchase order items dynamic grid
 */
function removePurchaseOrderItemRow(button) {
    const tableBody = document.getElementById('po-items-body');
    if (!tableBody) return;

    if (tableBody.rows.length <= 1) {
        alert('Minimal harus ada 1 barang dalam pesanan.');
        return;
    }

    const row = button.closest('tr');
    row.remove();
    renumberPurchaseOrderRows();
}

/**
 * Renumbers the indices of rows in PO grid
 */
function renumberPurchaseOrderRows() {
    var tableBody = document.getElementById('po-items-body');
    if (!tableBody) return;

    var rows = tableBody.rows;
    for (var i = 0; i < rows.length; i++) {
        var numCell = rows[i].querySelector('.row-number');
        if (numCell) {
            numCell.textContent = i + 1;
        }
    }
}