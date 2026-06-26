<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$mysqli = new mysqli("db-svr.rkzsby.local", "dbkatrok", "jadul", "dbold");
if ($mysqli->connect_error) {
    echo json_encode([['file' => '', 'status' => 'db_connect_failed']]);
    exit;
}

$id_agenda   = $_POST['id_agenda'];
$usercreated = $_POST['usercreated'];

$uploadDir = "dokumen/";
$response = [];

foreach ($_FILES['files']['name'] as $key => $filename) {
    $tmp_name  = $_FILES['files']['tmp_name'][$key];
    $file_ext  = pathinfo($filename, PATHINFO_EXTENSION);

    // Insert row first to get ID
    $stmt = $mysqli->prepare("INSERT INTO marketing_agenda_dokumen 
        (id_agenda, tipe_dokumen, tipe_file, usercreated, datecreated, flag) 
        VALUES (?, 'AGENDA', ?, ?, NOW(), 'Y')");
    $stmt->bind_param('iss', $id_agenda, $file_ext, $usercreated);
    $success = $stmt->execute();
    $insert_id = $mysqli->insert_id;
    $stmt->close();

    // Use insert_id as file name
    $new_name = $insert_id . '.' . $file_ext;
    $destination = $uploadDir . $new_name;

    // Move the file
    if (move_uploaded_file($tmp_name, $destination)) {
        $response[] = ['file' => $new_name, 'status' => 'success'];
    } else {
        // File move failed, but DB record remains
        $response[] = ['file' => $new_name, 'status' => 'move_failed'];
    }
}

echo json_encode($response);
