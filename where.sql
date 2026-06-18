$no_sp = "PO/202506/01/10";
$stmt = $pdo->prepare("SELECT * FROM m_pesanan WHERE no_sp = ?");
$stmt->execute([$no_sp]);
$row = $stmt->fetch();
