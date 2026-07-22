<?php
// views/master_unit.php
sp_require_login();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_unit'])) {
        $nama_unit = db_escape(strtoupper($_POST['nama_unit']));
        if (!empty($nama_unit)) {
            mysqli_query($GLOBALS['db_conn'], "INSERT INTO sp_unit (nama_unit) VALUES ('$nama_unit')");
        }
    } elseif (isset($_POST['delete_unit'])) {
        $id = (int)$_POST['unit_id'];
        mysqli_query($GLOBALS['db_conn'], "DELETE FROM sp_unit WHERE id = $id");
    }
    header("Location: home.php?page=master_unit");
    exit;
}

$res = mysqli_query($GLOBALS['db_conn'], "SELECT * FROM sp_unit ORDER BY nama_unit ASC");
$units = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $units[] = $row;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Master Unit / Bagian</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="bp-panel">
            <div class="bp-panel-header">
                Tambah Unit
            </div>
            <div class="bp-panel-body">
                <form method="post" action="">
                    <div class="form-group">
                        <label>Nama Unit / Bagian</label>
                        <input type="text" name="nama_unit" class="form-control" required placeholder="Contoh: IGD, FARMASI, GUDANG">
                    </div>
                    <button type="submit" name="add_unit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Unit</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="bp-panel">
            <div class="bp-panel-header">
                Daftar Unit
            </div>
            <div class="bp-panel-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Unit</th>
                                <th class="text-center" style="width:100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($units)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">Belum ada data unit.</td>
                            </tr>
                            <?php else: ?>
                                <?php $no=1; foreach($units as $u): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($u['nama_unit']); ?></td>
                                    <td class="text-center">
                                        <form method="post" action="" onsubmit="return confirm('Hapus unit ini?');">
                                            <input type="hidden" name="unit_id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" name="delete_unit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
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
