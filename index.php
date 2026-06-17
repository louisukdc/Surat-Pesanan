<?php
// index.php - Login Page & API Endpoint
session_start();

// Handle REST API JSON Login Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Always return JSON if it's an API request
    header('Content-Type: application/json');
    
    if (!$data || !is_array($data)) {
        http_response_code(400);
        echo json_encode([
            "success" => false, 
            "message" => "Invalid JSON payload or empty body.",
            "debug_raw_input" => $input
        ]);
        exit;
    }
    
    if (isset($data['username']) && isset($data['password'])) {
        require_once 'config.php';
        
        $username = $data['username'];
        $password = md5($data['password']);
        
        $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            echo json_encode(["success" => true, "message" => "Login successful", "data" => $row]);
        } else {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Invalid username or password"]);
        }
        exit;
    } else {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Missing username or password in JSON"]);
        exit;
    }
}

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
