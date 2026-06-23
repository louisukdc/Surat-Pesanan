<?php
require_once 'config.php';

$queries = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT(11) NOT NULL AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'user',
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;",
    
    "INSERT IGNORE INTO users (username, password, role) VALUES ('admin', MD5('adminrkz'), 'admin');"
];

foreach ($queries as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Success: " . substr($sql, 0, 50) . "...<br>\n";
    } else {
        echo "Error creating table: " . $conn->error . "<br>\n";
    }
}
echo "Selesai! Silakan coba login kembali.";
?>
