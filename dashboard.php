<?php
// dashboard.php - Main Shell Layout
require_once 'auth.php';
checkAuth();

$page = isset($_GET['page']) ? $_GET['page'] : 'order_form';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - RKZ Askes System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
</head>
<body>
    <div class="app-container">
        <?php include 'components/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php include 'components/topbar.php'; ?>

            <div class="content-wrapper">
                <?php
                // Include the requested page
                $allowed_pages = ['home', 'order_form', 'list_pesanan', 'master_supplier', 'laporan', 'users'];
                if (in_array($page, $allowed_pages)) {
                    include 'pages/' . $page . '.php';
                } else {
                    echo "<div class='card'><h2>Page not found</h2></div>";
                }
                ?>
            </div>
        </main>
    </div>
</body>
</html>
