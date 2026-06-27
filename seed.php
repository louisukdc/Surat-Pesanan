<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require 'config.php';

echo "Memulai pembuatan 25 data dummy...\n";

$conn->begin_transaction();

try {
    $inserted_suppliers = [];
    $inserted_spu_h = [];
    $inserted_surat_jalan = [];

    // 1. m_supplier
    for ($i = 1; $i <= 25; $i++) {
        $kode = 'DUM' . str_pad($i, 3, '0', STR_PAD_LEFT);
        $nama = 'PT. Dummy Supplier ' . $i;
        $npwp = '01.234.567.8-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) . '.000';
        $alamat = 'Jalan Dummy No ' . rand(1, 100);
        
        $stmt = $conn->prepare("INSERT INTO m_supplier (KodeSupplier, NamaSupplier, NPWP, Alamat1, Kota1, Telp1, ContactPerson, Status, CaraPembayaran, tanda, NamaInvoice) VALUES (?, ?, ?, ?, 'Jakarta', '021-1234567', 'Budi', 'Aktif', 'Tunai', 'P', ?)");
        $stmt->bind_param("sssss", $kode, $nama, $npwp, $alamat, $nama);
        $stmt->execute();
        
        $inserted_suppliers[] = $kode;
    }
    echo "25 Supplier ditambahkan.\n";

    // 2. spu_h
    for ($i = 1; $i <= 25; $i++) {
        $supplier = $inserted_suppliers[array_rand($inserted_suppliers)];
        $tgl_pesan = date('Y-m-d', strtotime('-' . rand(1, 30) . ' days'));
        $tgl_kirim = date('Y-m-d', strtotime($tgl_pesan . ' + ' . rand(1, 5) . ' days'));
        
        $stmt = $conn->prepare("INSERT INTO spu_h (no_permintaan, tgl_pesan, id_supplier, no_penawaran, tgl_penawaran, tgl_kirim, gudang, jenis_bayar, keterangan, user_created, dtime_created, user_acc, date_acc, flag, status_acc) VALUES (?, ?, ?, '', '1900-01-01', ?, 'G01', 'Tunai', 'Dummy Order', 'admin', NOW(), 'ADM', NOW(), 'y', 'Approved')");
        $no_permintaan = 'REQ-DUMMY-' . $i;
        $stmt->bind_param("ssss", $no_permintaan, $tgl_pesan, $supplier, $tgl_kirim);
        $stmt->execute();
        
        $inserted_spu_h[] = $conn->insert_id;
    }
    echo "25 Header Pesanan ditambahkan.\n";

    // 3. spu_d
    foreach ($inserted_spu_h as $spu_h_id) {
        $num_items = rand(1, 3);
        for ($j = 0; $j < $num_items; $j++) {
            $barang = 'Barang Dummy ' . rand(100, 999);
            $qty = rand(1, 10);
            $harga = rand(10, 100) * 1000;
            $jumlah = $qty * $harga;
            
            $stmt = $conn->prepare("INSERT INTO spu_d (id_sp, barang, qty, harga, jumlah, date_created) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("isidd", $spu_h_id, $barang, $qty, $harga, $jumlah);
            $stmt->execute();
        }
    }
    echo "Detail Pesanan ditambahkan.\n";

    // 4. surat_jalan
    foreach ($inserted_spu_h as $i => $spu_h_id) {
        $no_sj = 'SJ-DUMMY-' . ($i + 1) . '-' . rand(1000, 9999);
        $tgl_terima = date('Y-m-d H:i:s', strtotime('-' . rand(1, 15) . ' days'));
        
        $stmt = $conn->prepare("INSERT INTO surat_jalan (id_spu_h, nomor_surat_jalan, tanggal_terima, teknisi_penerima_id, kategori, status_pengecekan, created_at) VALUES (?, ?, ?, 1, 'Barang', 'Sesuai', ?)");
        $stmt->bind_param("isss", $spu_h_id, $no_sj, $tgl_terima, $tgl_terima);
        $stmt->execute();
        
        $inserted_surat_jalan[] = $conn->insert_id;
    }
    echo "25 Surat Jalan ditambahkan.\n";

    // 5. berita_acara
    foreach ($inserted_surat_jalan as $i => $sj_id) {
        $no_ba = 'BA-DUMMY-' . ($i + 1) . '-' . rand(1000, 9999);
        
        $stmt = $conn->prepare("INSERT INTO berita_acara (surat_jalan_id, nomor_ba, tanggal_generate, keterangan, status_dokumen) VALUES (?, ?, NOW(), 'BAST Dummy', 'Selesai')");
        $stmt->bind_param("is", $sj_id, $no_ba);
        $stmt->execute();
    }
    echo "25 Berita Acara ditambahkan.\n";

    // 6. laporan_kerja
    foreach ($inserted_surat_jalan as $i => $sj_id) {
        $no_lk = 'LK-DUMMY-' . ($i + 1) . '-' . rand(1000, 9999);
        
        $stmt = $conn->prepare("INSERT INTO laporan_kerja (surat_jalan_id, nomor_lk, tanggal_generate, rincian_pekerjaan, status_dokumen) VALUES (?, ?, NOW(), 'Pengecekan Barang Dummy', 'Selesai')");
        $stmt->bind_param("is", $sj_id, $no_lk);
        $stmt->execute();
    }
    echo "25 Laporan Kerja ditambahkan.\n";

    // 7. pembayaran
    foreach ($inserted_surat_jalan as $i => $sj_id) {
        $no_bayar = 'PAY-DUMMY-' . ($i + 1) . '-' . rand(1000, 9999);
        $jumlah = rand(100, 500) * 10000;
        
        $stmt = $conn->prepare("INSERT INTO pembayaran (surat_jalan_id, keuangan_validator_id, nomor_bukti_bayar, jumlah_bayar, tanggal_validasi, tanggal_bayar, status_bayar) VALUES (?, 1, ?, ?, NOW(), NOW(), 'Lunas')");
        $stmt->bind_param("isd", $sj_id, $no_bayar, $jumlah);
        $stmt->execute();
    }
    echo "25 Pembayaran ditambahkan.\n";

    $conn->commit();
    echo "Semua data dummy berhasil disimpan!";

} catch (Exception $e) {
    $conn->rollback();
    echo "Gagal: " . $e->getMessage();
}
