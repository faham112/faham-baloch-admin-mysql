<?php
require_once __DIR__ . '/../config.php';
setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['valid' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$key = trim($input['key'] ?? '');

if (empty($key)) {
    echo json_encode(['valid' => false, 'message' => 'No license key provided']);
    exit;
}

$pdo = getDB();

// Check if key exists
$stmt = $pdo->prepare("SELECT * FROM licenses WHERE license_key = ?");
$stmt->execute([$key]);
$lic = $stmt->fetch();

if (!$lic) {
    // Auto-register as pending
    $stmt = $pdo->prepare("INSERT INTO licenses (license_key, status) VALUES (?, 'pending')");
    $stmt->execute([$key]);
    
    echo json_encode([
        'valid' => false,
        'message' => 'License key is pending approval. Send this key to admin.',
        'status' => 'pending'
    ]);
    exit;
}

if ($lic['status'] === 'approved') {
    // Check expiry
    if ($lic['expires_at'] && strtotime($lic['expires_at']) < time()) {
        echo json_encode([
            'valid' => false,
            'message' => 'Your license has expired. Contact admin to renew.',
            'expired' => true
        ]);
        exit;
    }
    
    echo json_encode([
        'valid' => true,
        'message' => 'License active',
        'status' => 'approved'
    ]);
    exit;
}

if ($lic['status'] === 'rejected') {
    echo json_encode([
        'valid' => false,
        'message' => 'License key has been rejected by admin.',
        'status' => 'rejected'
    ]);
    exit;
}

// pending
echo json_encode([
    'valid' => false,
    'message' => 'License key is pending approval. Send this key to admin.',
    'status' => 'pending'
]);
?>
