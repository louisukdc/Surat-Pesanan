<?php
// components/sidebar.php
?>
<aside class="sidebar">
    <div class="sidebar-brand" style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 62px; height: 62px; border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
            <img src="img/logo.svg" alt="Logo" style="width: 60px; height: auto;">
        </div>
        <span>RKZ Askes</span>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php?page=home" class="<?php echo ($page == 'home' || empty($page)) ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <?php if (checkMenuAccess(99)): // Menu Users Admin ?>
        <li>
            <a href="dashboard.php?page=users" class="<?php echo $page == 'users' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Users
            </a>
        </li>
        <?php endif; ?>
        <?php if (checkMenuAccess(3444)): // Sesuaikan ID dengan ID Permohonan di m_menu ?>
        <li>
            <a href="dashboard.php?page=order_form" class="<?php echo $page == 'order_form' ? 'active' : ''; ?>">
                <i class="fas fa-plus-circle"></i> Permohonan Pesanan
            </a>
        </li>
        <?php endif; ?>
        <?php if (checkMenuAccess(3445)): // Sesuaikan ID dengan ID Persetujuan di m_menu ?>
        <li>
            <a href="dashboard.php?page=approval" class="<?php echo $page == 'approval' ? 'active' : ''; ?>">
                <i class="fas fa-check-double"></i> Persetujuan Pesanan
            </a>
        </li>
        <?php endif; ?>
        <?php if (checkMenuAccess(3443)): // Sesuaikan ID dengan ID Monitoring di m_menu ?>
        <li>
            <a href="dashboard.php?page=list_pesanan" class="<?php echo $page == 'list_pesanan' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> Monitoring Pesanan
            </a>
        </li>
        <?php endif; ?>
        <?php if (checkMenuAccess(3446)): // Sesuaikan ID dengan ID Penerimaan di m_menu ?>
        <li>
            <a href="dashboard.php?page=penerimaan_barang" class="<?php echo $page == 'penerimaan_barang' ? 'active' : ''; ?>">
                <i class="fas fa-box-open"></i> Penerimaan Pesanan
            </a>
        </li>
        <?php endif; ?>
    </ul>
    <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid var(--border);">
        <a href="auth.php?action=logout" style="color: var(--danger); text-decoration: none; display: flex; align-items: center; gap: 10px; font-weight: 500;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</aside>
