<?php
// Script to apply the changes to buat_pesanan.php safely
$file = 'c:/Users/louis/Documents/aan/sp_umum/views/buat_pesanan.php';
$content = file_get_contents($file);

// 1. Fetch sp_unit and sp_pengadaan at the top
$fetch_code = <<<'EOD'
$gudang_json = json_encode($gudang_list);

$res_unit = mysqli_query($GLOBALS['db_conn'], "SELECT * FROM sp_unit ORDER BY nama_unit ASC");
$master_units = [];
if ($res_unit) {
    while ($row = mysqli_fetch_assoc($res_unit)) {
        $master_units[] = $row;
    }
}

$res_pengadaan = mysqli_query($GLOBALS['db_conn'], "SELECT * FROM sp_pengadaan ORDER BY id DESC LIMIT 200");
$master_pengadaans = [];
if ($res_pengadaan) {
    while ($row = mysqli_fetch_assoc($res_pengadaan)) {
        $master_pengadaans[] = $row;
    }
}

require_once dirname(__FILE__) . '/../includes/header.php';
EOD;
$content = str_replace("\$gudang_json = json_encode(\$gudang_list);\n\nrequire_once dirname(__FILE__) . '/../includes/header.php';", $fetch_code, $content);

// 2. Replace no_permintaan input
$no_permintaan_html = <<<'EOD'
                                <select name="no_permintaan" id="no_permintaan" class="form-control form-control-sm bp-input select2-field">
                                    <option value="">-- Pilih No Surat --</option>
                                    <?php foreach ($master_pengadaans as $p): ?>
                                        <option value="<?php echo htmlspecialchars($p['no_permintaan']); ?>" <?php echo (isset($_POST['no_permintaan']) && $_POST['no_permintaan'] == $p['no_permintaan']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($p['no_permintaan'] . ' - ' . $p['perihal']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
EOD;
$content = preg_replace('/<input type="text" name="no_permintaan".*?value="[^"]*">/s', $no_permintaan_html, $content);

// 3. Replace unit input
$unit_html = <<<'EOD'
                                <select name="unit" id="unit" class="form-control form-control-sm bp-input select2-field">
                                    <option value="">-- Pilih Unit / Bagian --</option>
                                    <?php foreach ($master_units as $u): ?>
                                        <option value="<?php echo htmlspecialchars($u['nama_unit']); ?>" <?php echo (isset($_POST['unit']) && $_POST['unit'] == $u['nama_unit']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($u['nama_unit']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
EOD;
$content = preg_replace('/<input type="text" name="unit".*?id="unit".*?value="[^"]*">/s', $unit_html, $content);
$content = preg_replace('/<div id="gudang-suggestions".*?<\/div>/s', '', $content); // Remove old suggestions div

// 4. Replace Vendor input
$vendor_html = <<<'EOD'
                        <select name="nama_vendor" id="nama_vendor" class="form-control form-control-sm bp-input select2-field" required>
                            <option value="">-- Pilih Vendor --</option>
                            <?php foreach ($suppliers as $sup): ?>
                                <option value="<?php echo htmlspecialchars($sup['NamaSupplier']); ?>" <?php echo (isset($_POST['nama_vendor']) && $_POST['nama_vendor'] == $sup['NamaSupplier']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sup['NamaSupplier']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
EOD;
$content = preg_replace('/<input type="hidden" name="nama_vendor".*?id="supplier-suggestions".*?<\/div>/s', $vendor_html, $content);


// 5. Add Select2 initialization and Enter key logic
$js_code = <<<'EOD'
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-field').select2({
        width: '100%',
        dropdownAutoWidth: true
    });

    // Enter key navigation in grid
    $(document).on('keydown', '#gridModal input, #gridModal select', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var inputs = $('#gridModal').find('.excel-input, .excel-select');
            var index = inputs.index(this);
            if (index > -1 && index < inputs.length - 1) {
                inputs.eq(index + 1).focus();
            }
        }
    });
});
</script>
<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>
EOD;
$content = str_replace("<?php require_once dirname(__FILE__) . '/../includes/footer.php'; ?>", $js_code, $content);

file_put_contents($file, $content);
echo "buat_pesanan updated.";
?>
