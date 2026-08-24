<?php
header("Access-Control-Allow-Origin: http://localhost:3000"); // Allow React frontend
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config/database.php';
session_start();

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->username) && !empty($data->password)) {
    $username = htmlspecialchars(strip_tags($data->username));
    
    // Check user with roles
    $query = "SELECT u.id, u.username, u.password_hash, u.status, r.role_name 
              FROM users u 
              JOIN roles r ON u.role_id = r.id 
              WHERE u.username = :username LIMIT 1";
              
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row['status'] !== 'ACTIVE') {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Account is inactive or banned."]);
            exit();
        }
        
        if (password_verify($data->password, $row['password_hash'])) {
            // Prevent Session Fixation
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role_name'];
            
            // Generate CSRF token
            $csrf_token = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrf_token;
            
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "Login successful.",
                "user" => [
                    "id" => $row['id'],
                    "username" => $row['username'],
                    "role" => $row['role_name']
                ],
                "csrf_token" => $csrf_token
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Invalid credentials."]);
        }
    } else {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Invalid credentials."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Incomplete data."]);
}
