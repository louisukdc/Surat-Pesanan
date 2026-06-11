<?php
// api/users.php
require_once '../auth.php';
checkAuth();

// Only admin can access user API
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden: Admins only']);
    exit;
}

require_once '../config.php';
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $result = $conn->query("SELECT id, username, role FROM users ORDER BY id ASC");
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = trim(isset($input['username']) ? $input['username'] : '');
        $password = isset($input['password']) ? $input['password'] : '';
        $role = isset($input['role']) ? $input['role'] : 'umum';

        if(empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Username and Password are required']);
            exit;
        }

        $hashed_password = md5($password);

        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $role);
        
        if($stmt->execute()) {
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'User created']);
        } else {
            if ($conn->errno == 1062) {
                http_response_code(409);
                throw new Exception("Username already exists");
            }
            throw new Exception($conn->error);
        }
        exit;
    }

    if ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = isset($input['id']) ? $input['id'] : '';
        $role = isset($input['role']) ? $input['role'] : '';
        $password = isset($input['password']) ? $input['password'] : ''; // optional

        if(empty($id) || empty($role)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID and Role are required for update']);
            exit;
        }

        if (!empty($password)) {
            $hashed_password = md5($password);
            $stmt = $conn->prepare("UPDATE users SET role=?, password=? WHERE id=?");
            $stmt->bind_param("ssi", $role, $hashed_password, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET role=? WHERE id=?");
            $stmt->bind_param("si", $role, $id);
        }
        
        if($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User updated']);
        } else {
            throw new Exception($conn->error);
        }
        exit;
    }

    if ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = isset($input['id']) ? $input['id'] : '';
        if(empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required for deletion']);
            exit;
        }
        
        if ($id == $_SESSION['user_id']) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete your own account']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User deleted']);
        } else {
            throw new Exception($conn->error);
        }
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
