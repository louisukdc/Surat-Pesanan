<?php
require_once '../config.php';

// Find 3 approved POs
$res = $conn->query("SELECT id FROM spu_h WHERE status_acc = 'Approved' ORDER BY id DESC LIMIT 3");
$pos = [];
while ($row = $res->fetch_assoc()) {
    $pos[] = $row['id'];
}

if (count($pos) < 3) {
    echo "Tidak cukup PO yang Approved untuk membuat 3 data.";
    exit;
}

$dummyData = [
    [
        'id_spu_h' => $pos[0],
        'nomor_surat_jalan' => 'SJ-DUMMY-' . rand(1000, 9999),
        'tanggal_terima' => date('Y-m-d H:i:s', strtotime('-1 days')),
        'teknisi_penerima_id' => 1,
        'kategori' => 'Barang',
        'status_pengecekan' => 'Sesuai',
        'catatan' => 'Barang diterima lengkap dan segel utuh.'
    ],
    [
        'id_spu_h' => $pos[1],
        'nomor_surat_jalan' => 'SJ-DUMMY-' . rand(1000, 9999),
        'tanggal_terima' => date('Y-m-d H:i:s', strtotime('-2 days')),
        'teknisi_penerima_id' => 1,
        'kategori' => 'Barang',
        'status_pengecekan' => 'Sebagian Rusak',
        'catatan' => 'Satu kotak penyok, tapi isi masih bisa digunakan.'
    ],
    [
        'id_spu_h' => $pos[2],
        'nomor_surat_jalan' => 'SJ-DUMMY-' . rand(1000, 9999),
        'tanggal_terima' => date('Y-m-d H:i:s', strtotime('-3 days')),
        'teknisi_penerima_id' => 1,
        'kategori' => 'Barang',
        'status_pengecekan' => 'Sesuai',
        'catatan' => 'Sudah dicek oleh QA, sesuai dengan spesifikasi.'
    ]
];

$stmt = $conn->prepare("INSERT INTO surat_jalan (id_spu_h, nomor_surat_jalan, tanggal_terima, teknisi_penerima_id, kategori, status_pengecekan, catatan, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

$count = 0;
foreach ($dummyData as $data) {
    // Check if already exists just in case
    $check = $conn->query("SELECT id FROM surat_jalan WHERE id_spu_h = " . $data['id_spu_h']);
    if ($check->num_rows == 0) {
        $stmt->bind_param("ississs", $data['id_spu_h'], $data['nomor_surat_jalan'], $data['tanggal_terima'], $data['teknisi_penerima_id'], $data['kategori'], $data['status_pengecekan'], $data['catatan']);
        if ($stmt->execute()) {
            $count++;
        } else {
            echo "Error: " . $stmt->error . "\n";
        }
    }
}

echo "$count data dummy surat jalan berhasil ditambahkan.";
?>
