<?php
// admin/logout.php
// Logout handler

require_once dirname(__FILE__) . '/../includes/auth.php';
sp_logout();
header("Location: /sp_umum/index.html");
exit;
