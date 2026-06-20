CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','umum') NOT NULL DEFAULT 'umum',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Default password is 'admin123' and 'umum123' (hashed using md5 for PHP 5 simplicity)
INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin', md5('admin123'), 'admin'),
('umum', md5('umum123'), 'umum');
