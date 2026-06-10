<?php
// setup_db.php - Auto-setup database
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '123456789';

// Connect without selecting database
$conn = new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Create Database
echo "Creating database 'askes'...<br>";
if ($conn->query("CREATE DATABASE IF NOT EXISTS askes") === TRUE) {
    echo "Database created successfully.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select database
$conn->select_db('askes');

// Function to execute SQL file
function executeSqlFile($conn, $filepath) {
    if (!file_exists($filepath)) {
        echo "File $filepath not found.<br>";
        return;
    }
    
    $sql = file_get_contents($filepath);
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        echo "Successfully imported $filepath.<br>";
    } else {
        echo "Error importing $filepath: " . $conn->error . "<br>";
    }
}

// 2. Import Schemas and Data
echo "Importing database_init.sql (users)...<br>";
executeSqlFile($conn, 'database_init.sql');

echo "Importing data_askes.sql (schema m_supplier)...<br>";
executeSqlFile($conn, 'data_askes.sql');

echo "Importing m_supplier.sql (data m_supplier)...<br>";
executeSqlFile($conn, 'm_supplier.sql');

echo "Importing data_old.sql (schema sp_pesanan)...<br>";
executeSqlFile($conn, 'data_old.sql');

echo "Importing sp_pesanan.sql (data sp_pesanan)...<br>";
executeSqlFile($conn, 'sp_pesanan.sql');

echo "<br><b>Setup Complete!</b> <a href='index.php'>Go to Login</a>";
?>
