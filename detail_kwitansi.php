<?php
require_once 'auth.php';
checkAuth();
require_once 'config.php';

$no_sp = isset($_GET['id']) ? $_GET['id'] : '';

if(empty($no_sp)) {
    die("ID Pesanan tidak valid.");
}

// Fetch header
$stmt = $conn->prepare("SELECT * FROM sp_pesanan WHERE no_sp = ? LIMIT 1");
$stmt->bind_param("s", $no_sp);
$stmt->execute();
$result = $stmt->get_result();
$header = $result->fetch_assoc();

if(!$header) {
    die("Pesanan tidak ditemukan.");
}

// Fetch items
$stmt_items = $conn->prepare("SELECT * FROM sp_pesanan WHERE no_sp = ?");
$stmt_items->bind_param("s", $no_sp);
$stmt_items->execute();
$res_items = $stmt_items->get_result();
$items = [];
while($row = $res_items->fetch_assoc()) {
    $items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Pesanan - <?= htmlspecialchars($no_sp) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-radius: 8px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2e7d32;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #2e7d32;
            font-size: 24px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 14px;
        }
        .info-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-col {
            flex: 1;
        }
        .info-col table {
            width: 100%;
        }
        .info-col table td {
            padding: 4px 0;
            font-size: 14px;
        }
        .info-col table td:first-child {
            font-weight: bold;
            width: 120px;
            color: #555;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .table-items th, .table-items td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }
        .table-items th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: bold;
        }
        .table-items td.text-right, .table-items th.text-right {
            text-align: right;
        }
        .table-items td.text-center, .table-items th.text-center {
            text-align: center;
        }
        .total-row td {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            text-align: center;
        }
        .signature-box {
            width: 200px;
        }
        .signature-box p {
            margin: 0 0 70px;
            font-size: 14px;
        }
        .signature-line {
            border-top: 1px solid #000;
            padding-top: 5px;
            font-size: 14px;
            font-weight: bold;
        }
        .btn-print {
            display: block;
            width: 200px;
            margin: 30px auto 0;
            padding: 10px 20px;
            background: #2e7d32;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }
        .btn-print:hover {
            background: #1b5e20;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .print-container { box-shadow: none; border-radius: 0; padding: 0; max-width: 100%; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

<div class="print-container">
    <div class="header">
        <h1>Surat Pesanan / Bukti Transaksi</h1>
        <p>Sistem Informasi Askes RKZ</p>
    </div>

    <div class="info-grid">
        <div class="info-col">
            <table>
                <tr>
                    <td>No. Pesanan</td>
                    <td>: <?= htmlspecialchars($header['no_sp']) ?></td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: <?= htmlspecialchars($header['tgl_sp']) ?></td>
                </tr>
                <tr>
                    <td>Supplier</td>
                    <td>: <?= htmlspecialchars($header['namasup']) ?></td>
                </tr>
            </table>
        </div>
        <div class="info-col" style="margin-left: 20px;">
            <table>
                <tr>
                    <td>Bagian / Unit</td>
                    <td>: <?= htmlspecialchars($header['unit']) ?></td>
                </tr>
                <tr>
                    <td>Dibuat Oleh</td>
                    <td>: <?= htmlspecialchars($header['user']) ?></td>
                </tr>
                <tr>
                    <td>Pembayaran</td>
                    <td>: <?= htmlspecialchars($header['pembayaran']) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <table class="table-items">
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Nama Barang</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Harga Satuan</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $grand_total = 0;
            foreach($items as $item): 
                $sub = $item['qty'] * $item['harga'];
                $grand_total += $sub;
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td>
                    <?= htmlspecialchars($item['barang']) ?>
                    <?php if(!empty($item['merk'])) echo '<br><small>Merk: '.htmlspecialchars($item['merk']).'</small>'; ?>
                </td>
                <td class="text-center"><?= floatval($item['qty']) ?> <?= htmlspecialchars($item['satuan']) ?></td>
                <td class="text-right">Rp <?= number_format($item['harga'], 2, ',', '.') ?></td>
                <td class="text-right">Rp <?= number_format($sub, 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="4" class="text-right">Total Sebelum PPN</td>
                <td class="text-right">Rp <?= number_format($grand_total, 2, ',', '.') ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="4" class="text-right">PPN</td>
                <td class="text-right">Rp <?= number_format($header['ppn'], 2, ',', '.') ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="4" class="text-right" style="font-size: 16px;">GRAND TOTAL</td>
                <td class="text-right" style="font-size: 16px; color: #2e7d32;">Rp <?= number_format($header['flag'], 2, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">
            <p>Dibuat Oleh,</p>
            <div class="signature-line"><?= htmlspecialchars($header['user']) ?></div>
        </div>
        <div class="signature-box">
            <p>Disetujui Oleh,</p>
            <div class="signature-line">Manajer / Direktur</div>
        </div>
        <div class="signature-box">
            <p>Penerima,</p>
            <div class="signature-line">Supplier</div>
        </div>
    </div>

    <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Cetak Dokumen</button>
</div>

</body>
</html>
