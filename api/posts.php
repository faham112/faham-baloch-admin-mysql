<?php
require_once __DIR__ . '/../config.php';
setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$pdo = getDB();
$stmt = $pdo->prepare("
    INSERT INTO posts 
    (license_key, page_name, page_id, peek_link, image_url, permalink, story_id, status, error_msg)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $input['key'] ?? '',
    $input['pageName'] ?? '',
    $input['pageId'] ?? '',
    $input['peekLink'] ?? '',
    $input['imageUrl'] ?? '',
    $input['permalink'] ?? '',
    $input['storyId'] ?? '',
    $input['status'] ?? '',
    $input['errorMsg'] ?? ''
]);

http_response_code(201);
echo json_encode(['ok' => true]);
?>
