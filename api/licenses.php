<?php
require_once __DIR__ . '/../config.php';
setCorsHeaders();

// Simple auth check
function checkAuth() {
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    return $auth === 'Bearer ' . ADMIN_PASS;
}

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (!checkAuth()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $stmt = $pdo->query("SELECT * FROM licenses ORDER BY created_at DESC");
    $licenses = $stmt->fetchAll();
    
    // Format for frontend
    $result = [];
    foreach ($licenses as $l) {
        $result[] = [
            'key' => $l['license_key'],
            'status' => $l['status'],
            'created_at' => $l['created_at'],
            'approved_at' => $l['approved_at'],
            'expires_at' => $l['expires_at']
        ];
    }
    echo json_encode($result);
    exit;
}

if ($method === 'PATCH' || $method === 'POST') {
    if (!checkAuth()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $key = $input['key'] ?? '';
    $action = $input['action'] ?? '';
    
    if (empty($key)) {
        echo json_encode(['error' => 'Key required']);
        exit;
    }
    
    if ($action === 'approve') {
        $expires = date('Y-m-d H:i:s', strtotime('+1 year'));
        $stmt = $pdo->prepare("UPDATE licenses SET status='approved', approved_at=NOW(), expires_at=? WHERE license_key=?");
        $stmt->execute([$expires, $key]);
    } 
    elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE licenses SET status='rejected' WHERE license_key=?");
        $stmt->execute([$key]);
    } 
    elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM licenses WHERE license_key=?");
        $stmt->execute([$key]);
    }
    
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>
