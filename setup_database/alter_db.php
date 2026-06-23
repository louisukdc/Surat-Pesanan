<?php
require_once 'config.php';

$queries = [
    "ALTER TABLE spu_h ADD COLUMN status_acc ENUM('Pending','Approved','Rejected') DEFAULT 'Pending';",
    "ALTER TABLE spu_h ADD COLUMN alasan_tolak TEXT NULL;"
];

foreach ($queries as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Success: $sql<br>\n";
    } else {
        // If 'IF NOT EXISTS' is not supported in this MySQL/MariaDB version for ALTER TABLE, try manually
        if (strpos($conn->error, "Duplicate column name") !== false) {
            echo "Column already exists, skipping.<br>\n";
        } else {
            echo "Error: " . $conn->error . "<br>\n";
        }
    }
}
echo "Selesai!";
?>
