CREATE TABLE IF NOT EXISTS `sp_penerimaan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_gr` varchar(50) NOT NULL,
  `tgl_gr` date NOT NULL,
  `no_sp` varchar(50) NOT NULL,
  `user` varchar(50) NOT NULL,
  `barang` varchar(255) NOT NULL,
  `qty_pesan` double NOT NULL,
  `qty_terima` double NOT NULL,
  `satuan` varchar(20) NOT NULL,
  `batch_no` varchar(50) DEFAULT NULL,
  `exp_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_no_gr` (`no_gr`),
  KEY `idx_no_sp` (`no_sp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sp_retur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_retur` varchar(50) NOT NULL,
  `tgl_retur` date NOT NULL,
  `no_sp` varchar(50) NOT NULL,
  `user` varchar(50) NOT NULL,
  `barang` varchar(255) NOT NULL,
  `qty_retur` double NOT NULL,
  `satuan` varchar(20) NOT NULL,
  `alasan` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_no_retur` (`no_retur`),
  KEY `idx_no_sp` (`no_sp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
