<?php
require_once 'config.php';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($_POST['username'] === ADMIN_USER && $_POST['password'] === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Handle actions
if (isset($_SESSION['admin_logged_in']) && isset($_GET['action']) && isset($_GET['key'])) {
    $pdo = getDB();
    $key = $_GET['key'];
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        $expires = date('Y-m-d H:i:s', strtotime('+1 year'));
        $stmt = $pdo->prepare("UPDATE licenses SET status='approved', approved_at=NOW(), expires_at=? WHERE license_key=?");
        $stmt->execute([$expires, $key]);
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE licenses SET status='rejected' WHERE license_key=?");
        $stmt->execute([$key]);
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM licenses WHERE license_key=?");
        $stmt->execute([$key]);
    }
    header('Location: index.php');
    exit;
}

$loggedIn = isset($_SESSION['admin_logged_in']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAHAM BALOCH - Admin Panel</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #0f0c29;
            color: #eee;
            min-height: 100vh;
        }
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
        }
        .card {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .card h1 {
            text-align: center;
            margin-bottom: 8px;
            font-size: 1.8rem;
            background: linear-gradient(90deg, #00d2ff, #3a7bd5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .sub { text-align: center; color: #aaa; margin-bottom: 30px; }
        input {
            width: 100%;
            padding: 14px 16px;
            margin-bottom: 16px;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 10px;
            background: rgba(0,0,0,0.3);
            color: #fff;
            font-size: 1rem;
        }
        input:focus { outline: none; border-color: #00d2ff; }
        button, .btn {
            padding: 14px;
            background: linear-gradient(90deg, #00d2ff, #3a7bd5);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }
        .error {
            background: #ff4757;
            color: #fff;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 16px;
            text-align: center;
        }
        header {
            background: linear-gradient(90deg, #1a1a2e, #16213e);
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        header h1 {
            font-size: 1.4rem;
            background: linear-gradient(90deg, #00d2ff, #3a7bd5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .logout { color: #ff6b6b; text-decoration: none; font-size: 0.9rem; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
            margin-bottom: 30px;
        }
        .stat {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .stat .num { font-size: 2rem; font-weight: 700; color: #00d2ff; }
        .stat .label { color: #aaa; font-size: 0.85rem; margin-top: 4px; }
        h2 { margin: 20px 0 16px; color: #00d2ff; font-size: 1.2rem; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255,255,255,0.03);
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        th { background: rgba(0,0,0,0.3); color: #aaa; font-size: 0.85rem; }
        .key { font-family: monospace; font-size: 0.85rem; color: #00d2ff; }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .pending { background: #f39c12; color: #000; }
        .approved { background: #2ecc71; color: #000; }
        .rejected { background: #e74c3c; color: #fff; }
        .btn-sm {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            margin-right: 6px;
            width: auto;
        }
        .btn-approve { background: #2ecc71; color: #000; }
        .btn-reject { background: #e74c3c; color: #fff; }
        .btn-delete { background: #555; color: #fff; }
        .empty { text-align: center; padding: 40px; color: #666; }
    </style>
</head>
<body>

<?php if (!$loggedIn): ?>
    <div class="login-page">
        <div class="card">
            <h1>FAHAM BALOCH</h1>
            <p class="sub">Admin Panel</p>
            <?php if (isset($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required autofocus>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    </div>
<?php else: 
    $pdo = getDB();
    $licenses = $pdo->query("SELECT * FROM licenses ORDER BY created_at DESC")->fetchAll();
    $total = count($licenses);
    $pending = $pdo->query("SELECT COUNT(*) FROM licenses WHERE status='pending'")->fetchColumn();
    $approved = $pdo->query("SELECT COUNT(*) FROM licenses WHERE status='approved'")->fetchColumn();
    $posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
?>
    <header>
        <h1>FAHAM BALOCH Admin</h1>
        <a class="logout" href="?logout=1">Logout</a>
    </header>

    <div class="container">
        <div class="stats">
            <div class="stat">
                <div class="num"><?= $total ?></div>
                <div class="label">Total Keys</div>
            </div>
            <div class="stat">
                <div class="num" style="color:#f39c12"><?= $pending ?></div>
                <div class="label">Pending</div>
            </div>
            <div class="stat">
                <div class="num" style="color:#2ecc71"><?= $approved ?></div>
                <div class="label">Approved</div>
            </div>
            <div class="stat">
                <div class="num"><?= $posts ?></div>
                <div class="label">Posts Logged</div>
            </div>
        </div>

        <h2>License Keys</h2>
        <?php if (empty($licenses)): ?>
            <div class="empty">No license keys yet. Keys appear when users run the tool.</div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Key</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Expires</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($licenses as $lic): ?>
                <tr>
                    <td class="key"><?= htmlspecialchars($lic['license_key']) ?></td>
                    <td><span class="badge <?= $lic['status'] ?>"><?= $lic['status'] ?></span></td>
                    <td><?= substr($lic['created_at'], 0, 16) ?></td>
                    <td><?= $lic['expires_at'] ? substr($lic['expires_at'], 0, 10) : 'Never' ?></td>
                    <td>
                        <?php if ($lic['status'] !== 'approved'): ?>
                            <a class="btn-sm btn-approve" href="?action=approve&key=<?= urlencode($lic['license_key']) ?>">Approve</a>
                        <?php endif; ?>
                        <?php if ($lic['status'] !== 'rejected'): ?>
                            <a class="btn-sm btn-reject" href="?action=reject&key=<?= urlencode($lic['license_key']) ?>">Reject</a>
                        <?php endif; ?>
                        <a class="btn-sm btn-delete" href="?action=delete&key=<?= urlencode($lic['license_key']) ?>" onclick="return confirm('Delete this key?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
<?php endif; ?>

</body>
</html>
