<?php
// views/master_pengadaan.php
sp_require_login();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_pengadaan'])) {
        $no_permintaan = db_escape(strtoupper($_POST['no_permintaan']));
        $perihal = db_escape($_POST['perihal']);
        $tanggal = db_escape($_POST['tanggal']);
        if (!empty($no_permintaan)) {
            mysqli_query($GLOBALS['db_conn'], "INSERT INTO sp_pengadaan (no_permintaan, perihal, tanggal) VALUES ('$no_permintaan', '$perihal', '$tanggal')");
        }
    } elseif (isset($_POST['delete_pengadaan'])) {
        $id = (int)$_POST['pengadaan_id'];
        mysqli_query($GLOBALS['db_conn'], "DELETE FROM sp_pengadaan WHERE id = $id");
    }
    header("Location: home.php?page=master_pengadaan");
    exit;
}

$res = mysqli_query($GLOBALS['db_conn'], "SELECT * FROM sp_pengadaan ORDER BY id DESC LIMIT 100");
$pengadaan = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $pengadaan[] = $row;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Master Data Pengadaan (Sisuper)</h1>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="bp-panel">
            <div class="bp-panel-header">
                Tambah Data Pengadaan
            </div>
            <div class="bp-panel-body">
                <form method="post" action="">
                    <div class="form-group">
                        <label>No. Permintaan / Pengadaan</label>
                        <input type="text" name="no_permintaan" class="form-control" required placeholder="Contoh: REQ-123/2026">
                    </div>
                    <div class="form-group">
                        <label>Perihal / Keterangan</label>
                        <input type="text" name="perihal" class="form-control" placeholder="Pengadaan ATK">
                    </div>
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <button type="submit" name="add_pengadaan" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Data</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-7">
        <div class="bp-panel">
            <div class="bp-panel-header">
                Daftar Pengadaan
            </div>
            <div class="bp-panel-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No. Permintaan</th>
                                <th>Perihal</th>
                                <th>Tanggal</th>
                                <th class="text-center" style="width:80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pengadaan)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada data pengadaan.</td>
                            </tr>
                            <?php else: ?>
                                <?php $no=1; foreach($pengadaan as $p): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($p['no_permintaan']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($p['perihal']); ?></td>
                                    <td><?php echo $p['tanggal'] ? date('d M Y', strtotime($p['tanggal'])) : '-'; ?></td>
                                    <td class="text-center">
                                        <form method="post" action="" onsubmit="return confirm('Hapus data ini?');">
                                            <input type="hidden" name="pengadaan_id" value="<?php echo $p['id']; ?>">
                                            <button type="submit" name="delete_pengadaan" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
