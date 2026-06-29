<?php
require_once '../auth.php';
checkAuth();
require_once '../config.php';

if (!isset($_GET['id'])) {
    die("ID Pesanan tidak valid");
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT no_permintaan, nama_lampiran FROM spu_h WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Pesanan tidak ditemukan");
}

$row = $result->fetch_assoc();
if (empty($row['nama_lampiran']) || $row['nama_lampiran'] === '0') {
    die("Tidak ada lampiran untuk diunduh");
}

$files = explode(',', $row['nama_lampiran']);
if (count($files) === 0) {
    die("Tidak ada lampiran untuk diunduh");
}

$zip = new ZipArchive();
$zipFileName = 'Lampiran_PO_' . $id . '_' . time() . '.zip';
$zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Gagal membuat file ZIP");
}

$uploadDir = '../uploads/lampiran/';
$added = 0;

foreach ($files as $file) {
    $file = trim($file);
    if (empty($file)) continue;
    $filePath = $uploadDir . $file;
    
    if (file_exists($filePath)) {
        $zip->addFile($filePath, $file);
        $added++;
    }
}

$zip->close();

if ($added === 0) {
    die("File fisik lampiran tidak ditemukan di server");
}

// Stream the zip file to the browser
header('Content-Type: application/zip');
header('Content-disposition: attachment; filename=' . $zipFileName);
header('Content-Length: ' . filesize($zipFilePath));
readfile($zipFilePath);

// Delete the temporary file
unlink($zipFilePath);
exit;
?>
