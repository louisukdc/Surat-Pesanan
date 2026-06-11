<?php
// index.php - Login Page
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Health Insurance System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card glass">
            <div class="login-header">
                <img src="img/logo.svg" alt="RKZ Logo" style="width: 94px; height: auto; margin-bottom: 16px;">
                <h1>RKZ Askes System</h1>
                <p>Welcome back! Please login to your account.</p>
            </div>
            
            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            ?>

            <form action="auth.php" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group text-left" style="text-align: left;">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required placeholder="admin / umum">
                </div>
                
                <div class="form-group text-left" style="text-align: left; margin-bottom: 30px; position: relative;">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="myPassword" class="form-control" required placeholder="••••••••" style="padding-right: 40px;">
                    <span id="togglePassword" style="position: absolute; right: 15px; top: 38px; cursor: pointer; color: #666;">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </span>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 16px;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
        </div>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('myPassword');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function () {
            // Toggle the input type
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            // Toggle the eye icon
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
