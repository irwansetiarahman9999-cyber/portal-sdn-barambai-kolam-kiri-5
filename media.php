<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); exit();
}

require_once '../../config/database.php';
require_once '../../middleware/auth_middleware.php';
session_start();

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->prepare("SELECT id, file_name, file_path, file_type, file_size, created_at FROM media ORDER BY created_at DESC");
    $stmt->execute();
    echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    exit();
}

require_login();
require_role(['SUPER_ADMIN', 'ADMIN', 'EDITOR']);

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $db->prepare("SELECT file_path FROM media WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $media = $stmt->fetch();
        
        if ($media) {
            $path = $media['file_path'];
            
            // Check if media is in use in articles
            $checkStmt = $db->prepare("SELECT title FROM articles WHERE image_path = :path OR video_url = :path LIMIT 1");
            $checkStmt->execute(['path' => $path]);
            $usedBy = $checkStmt->fetch();
            
            if ($usedBy) {
                http_response_code(400);
                echo json_encode([
                    "success" => false, 
                    "message" => "Media ini masih digunakan oleh konten lain sehingga tidak dapat dihapus. Digunakan oleh: " . $usedBy['title']
                ]);
                exit();
            }
            
            $baseDir = dirname(dirname(dirname(__DIR__))); // Project root
            if (!filter_var($path, FILTER_VALIDATE_URL)) {
                $absolutePath = $baseDir . DIRECTORY_SEPARATOR . 'public' . $path;
                if (file_exists($absolutePath) && is_file($absolutePath)) {
                    unlink($absolutePath);
                }
            }
            
            $delStmt = $db->prepare("DELETE FROM media WHERE id=:id");
            $delStmt->execute(['id' => $id]);
            echo json_encode(["success" => true, "message" => "Media berhasil dihapus."]);
        } else {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Media tidak ditemukan."]);
        }
    }
}
