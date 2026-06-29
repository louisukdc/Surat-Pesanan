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

    $uploadedFiles = [];
    $errors = [];
    
    // Check if multiple files or single file structure
    $isMultiple = is_array($_FILES['lampiran']['name']);
    $fileCount = $isMultiple ? count($_FILES['lampiran']['name']) : 1;

    for ($i = 0; $i < $fileCount; $i++) {
        $name = $isMultiple ? $_FILES['lampiran']['name'][$i] : $_FILES['lampiran']['name'];
        $type = $isMultiple ? $_FILES['lampiran']['type'][$i] : $_FILES['lampiran']['type'];
        $tmp_name = $isMultiple ? $_FILES['lampiran']['tmp_name'][$i] : $_FILES['lampiran']['tmp_name'];
        $error = $isMultiple ? $_FILES['lampiran']['error'][$i] : $_FILES['lampiran']['error'];
        $size = $isMultiple ? $_FILES['lampiran']['size'][$i] : $_FILES['lampiran']['size'];

        // Skip empty slots if any
        if ($error === UPLOAD_ERR_NO_FILE) continue;

        if ($error !== UPLOAD_ERR_OK) {
            $errorMsg = "Gagal mengunggah file $name. Error code: $error";
            if ($error === UPLOAD_ERR_INI_SIZE) {
                $errorMsg = "Ukuran file $name terlalu besar! Maksimal " . ini_get('upload_max_filesize');
            }
            $errors[] = $errorMsg;
            continue;
        }

        $maxSize = 5 * 1024 * 1024; // 5 MB
        if ($size > $maxSize) {
            $errors[] = "File $name melebihi 5 MB.";
            continue;
        }

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($extension !== 'pdf' || $type !== 'application/pdf') {
            $errors[] = "Format file $name tidak didukung. Harap unggah PDF.";
            continue;
        }

        $newFilename = 'lampiran_' . time() . '_' . uniqid() . '.' . $extension;
        $uploadDir = '../uploads/lampiran/';
        $uploadPath = $uploadDir . $newFilename;

        if (move_uploaded_file($tmp_name, $uploadPath)) {
            $uploadedFiles[] = $newFilename;
        } else {
            $errors[] = "Gagal menyimpan file $name ke server.";
        }
    }

    if (count($uploadedFiles) > 0) {
        // Return comma separated string of filenames for the db
        echo json_encode([
            'success' => true, 
            'filename' => implode(',', $uploadedFiles),
            'errors' => $errors
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => implode(' | ', $errors)]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
