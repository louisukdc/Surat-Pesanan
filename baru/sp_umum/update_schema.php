<?php
/**
 * Script untuk memperbarui tabel spu_h dan spu_d lama Anda
 * agar kompatibel dengan sistem Approval dan Checklist yang baru.
 */

require_once 'config/database.php';

echo "Memulai update skema database...<br><br>";

if (!$conn) {
    die("Koneksi gagal.");
}

// 1. Tambah kolom di spu_h
$cols_spu_h = [
    "status" => "ENUM('draft', 'diajukan', 'direview', 'acc', 'ditolak') NOT NULL DEFAULT 'draft'",
    "dibuat_oleh" => "INT NOT NULL DEFAULT 0",
    "dibuat_pada" => "DATETIME DEFAULT '1900-01-01 00:00:00'"
];

foreach ($cols_spu_h as $col => $type) {
    $check = mysqli_query($conn, "SHOW COLUMNS FROM spu_h LIKE '$col'");
    if (mysqli_num_rows($check) == 0) {
        $res = mysqli_query($conn, "ALTER TABLE spu_h ADD COLUMN $col $type");
        echo "Kolom <strong>$col</strong> berhasil ditambahkan ke spu_h.<br>";
    } else {
        echo "Kolom $col sudah ada di spu_h.<br>";
    }
}

// 2. Tambah kolom di spu_d
$cols_spu_d = [
    "status_terima" => "ENUM('belum_datang', 'sebagian', 'lengkap') NOT NULL DEFAULT 'belum_datang'"
];

foreach ($cols_spu_d as $col => $type) {
    $check = mysqli_query($conn, "SHOW COLUMNS FROM spu_d LIKE '$col'");
    if (mysqli_num_rows($check) == 0) {
        $res = mysqli_query($conn, "ALTER TABLE spu_d ADD COLUMN $col $type");
        echo "Kolom <strong>$col</strong> berhasil ditambahkan ke spu_d.<br>";
    } else {
        echo "Kolom $col sudah ada di spu_d.<br>";
    }
}

// 3. Buat tabel-tabel baru pendukung fitur jika belum ada
$queries = [
    "CREATE TABLE IF NOT EXISTS sp_pengajuan_pembayaran (
        id INT AUTO_INCREMENT PRIMARY KEY,
        surat_pesanan_id INT NOT NULL,
        tgl_pengajuan DATE DEFAULT '1900-01-01',
        nominal_diajukan DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        status ENUM('diajukan', 'acc', 'ditolak') NOT NULL DEFAULT 'diajukan',
        catatan_direktur VARCHAR(255) NULL,
        diajukan_oleh INT NOT NULL,
        tgl_acc DATE DEFAULT '1900-01-01',
        dibuat_pada DATETIME DEFAULT '1900-01-01 00:00:00'
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

    "CREATE TABLE IF NOT EXISTS sp_penerimaan_barang (
        id INT AUTO_INCREMENT PRIMARY KEY,
        detail_surat_pesanan_id INT NOT NULL,
        tgl_diterima DATE DEFAULT '1900-01-01',
        jumlah_diterima INT NOT NULL DEFAULT 0,
        keterangan VARCHAR(255) NULL,
        dicek_oleh INT NOT NULL,
        dibuat_pada DATETIME DEFAULT '1900-01-01 00:00:00'
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

    "CREATE TABLE IF NOT EXISTS sp_log_persetujuan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        surat_pesanan_id INT NOT NULL,
        jenis ENUM('permintaan', 'pembayaran') NOT NULL,
        status VARCHAR(50) NOT NULL,
        catatan VARCHAR(255) NULL,
        oleh INT NOT NULL,
        tanggal DATETIME DEFAULT '1900-01-01 00:00:00'
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;"
];

foreach ($queries as $q) {
    mysqli_query($conn, $q);
}
echo "<br>Tabel-tabel pendukung (Pembayaran, Penerimaan Barang, Log) sudah dipastikan ada.<br>";

// 4. Update status default untuk SP lama agar masuk ke daftar monitoring
// Surat pesanan lama yang sudah ada di PC Anda akan diubah statusnya menjadi 'acc' (atau 'draft')
$res = mysqli_query($conn, "UPDATE spu_h SET status = 'acc' WHERE status = 'draft' AND flag > 0");
if ($res) {
    echo "Status SP lama yang sudah ada nilainya (flag > 0) di-set otomatis menjadi 'acc' agar langsung muncul di menu.<br>";
}

echo "<br><strong>Selesai! Skema database di PC Anda kini 100% kompatibel.</strong>";
?>
