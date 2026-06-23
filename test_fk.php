<?php
require 'config.php';

$queries = [
    "ALTER TABLE `spu_d` ADD CONSTRAINT `fk_spu_d_to_spu_h` FOREIGN KEY (`id_sp`) REFERENCES `spu_h` (`id`) ON DELETE CASCADE ON UPDATE CASCADE",
    "ALTER TABLE `spu_h` ADD CONSTRAINT `fk_spu_h_to_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `m_supplier` (`KodeSupplier`) ON DELETE RESTRICT ON UPDATE CASCADE"
];

foreach ($queries as $q) {
    echo "Running: $q\n";
    if (!$conn->query($q)) {
        echo "Error: " . $conn->error . "\n\n";
    } else {
        echo "Success!\n\n";
    }
}
?>
