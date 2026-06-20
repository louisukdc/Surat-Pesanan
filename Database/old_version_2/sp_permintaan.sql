CREATE TABLE IF NOT EXISTS `sp_permintaan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_pr` varchar(50) NOT NULL,
  `tgl_pr` date NOT NULL,
  `unit` varchar(50) NOT NULL,
  `user` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `alasan_tolak` text DEFAULT NULL,
  `barang` varchar(255) NOT NULL,
  `qty` double NOT NULL,
  `satuan` varchar(20) NOT NULL,
  `keterangan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_no_pr` (`no_pr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
