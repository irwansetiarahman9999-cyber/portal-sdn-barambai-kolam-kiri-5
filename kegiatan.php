<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); exit();
}

require_once '../../config/database.php';
require_once '../../middleware/auth_middleware.php';
session_start();

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// Handle GET (Public or Admin)
if ($method === 'GET') {
    $query = "SELECT id, title, slug, content as description, category, image_path as photoUrl, video_url as videoUrl, status, published_at as date FROM articles WHERE category = 'KEGIATAN' ORDER BY published_at DESC, created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    exit();
}

// Require login for mutations
require_login();
require_role(['SUPER_ADMIN', 'ADMIN', 'EDITOR']);

$data = json_decode(file_get_contents("php://input"), true);

if ($method === 'POST') {
    $id = 'act-' . bin2hex(random_bytes(8));
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));
    $published_at = date('Y-m-d H:i:s');
    
    $stmt = $db->prepare("INSERT INTO articles (id, title, slug, content, category, image_path, video_url, status, author_id, published_at) VALUES (:id, :title, :slug, :content, 'KEGIATAN', :image_path, :video_url, :status, :author_id, :published_at)");
    
    $stmt->execute([
        'id' => $id,
        'title' => $data['title'],
        'slug' => $slug,
        'content' => $data['description'] ?? '',
        'image_path' => $data['photoUrl'] ?? null,
        'video_url' => $data['videoUrl'] ?? null,
        'status' => $data['status'] ?? 'PUBLISHED',
        'author_id' => $_SESSION['user_id'],
        'published_at' => $published_at
    ]);
    echo json_encode(["success" => true, "message" => "Kegiatan created", "id" => $id, "data" => $data]);
} elseif ($method === 'PUT') {
    $stmt = $db->prepare("UPDATE articles SET title=:title, content=:content, image_path=:image_path, video_url=:video_url, status=:status WHERE id=:id AND category='KEGIATAN'");
    $stmt->execute([
        'id' => $data['id'],
        'title' => $data['title'],
        'content' => $data['description'],
        'image_path' => $data['photoUrl'] ?? null,
        'video_url' => $data['videoUrl'] ?? null,
        'status' => $data['status'] ?? 'PUBLISHED'
    ]);
    echo json_encode(["success" => true, "message" => "Kegiatan updated", "data" => $data]);
} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $db->prepare("DELETE FROM articles WHERE id=:id AND category='KEGIATAN'");
        $stmt->execute(['id' => $id]);
        echo json_encode(["success" => true, "message" => "Kegiatan deleted"]);
    }
}
