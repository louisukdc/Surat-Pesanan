<?php
require_once '../auth.php';
checkAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['lampiran'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Tidak ada file yang diunggah']);
        exit;
    }

    $file = $_FILES['lampiran'];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = 'Gagal mengunggah file. Error code: ' . $file['error'];
        if ($file['error'] === UPLOAD_ERR_INI_SIZE) {
            $errorMsg = 'Ukuran file PDF terlalu besar! Maksimal ukuran yang diizinkan server adalah ' . ini_get('upload_max_filesize');
        }
        http_response_code(400);
        echo json_encode(['error' => $errorMsg]);
        exit;
    }
    
    // Additional strict size check (misal: membatasi max 5MB secara sistem jika php.ini lebih besar)
    $maxSize = 5 * 1024 * 1024; // 5 MB
    if ($file['size'] > $maxSize) {
        http_response_code(400);
        echo json_encode(['error' => 'Ukuran file maksimal yang diperbolehkan adalah 5 MB.']);
        exit;
    }

    // Validate file type
    $fileType = $file['type'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if ($extension !== 'pdf' || $fileType !== 'application/pdf') {
        http_response_code(400);
        echo json_encode(['error' => 'Format file tidak didukung. Harap unggah file PDF.']);
        exit;
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFilename = 'lampiran_' . time() . '_' . uniqid() . '.' . $extension;
    $uploadDir = '../uploads/lampiran/';
    $uploadPath = $uploadDir . $newFilename;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        echo json_encode(['success' => true, 'filename' => $newFilename]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyimpan file ke server']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
