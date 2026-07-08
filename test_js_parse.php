<?php
$p_items = [
    [
        'nama_barang' => 'Test Barang',
        'merk' => 'MedisOne',
        'model' => 'Model1',
        'spec' => 'Spec1',
        'satuan' => 'pcs',
        'jumlah' => 1,
        'harga_satuan' => 5000,
        'disc' => 0,
        'subtotal' => 5000
    ]
];
$post_items_json = json_encode($p_items, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
?>
<script>
    let initItemsRaw = '<?php echo addslashes($post_items_json); ?>';
    console.log("Raw output:", initItemsRaw);
    let initItems = [];
    try {
        initItems = JSON.parse(initItemsRaw);
        console.log("Parsed output:", initItems);
    } catch(e) {
        console.error("Gagal parse JSON items:", e);
    }
</script>
