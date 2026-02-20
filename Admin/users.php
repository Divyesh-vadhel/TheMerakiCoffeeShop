<?php
require_once 'auth_check.php';
$host = 'localhost'; $user = 'root'; $password = ''; $database = 'kapetann';
$conn = @new mysqli($host, $user, $password, $database);

$msg = '';
// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $id = (int)$_POST['id'];
    $conn->query("DELETE FROM users WHERE id = $id");
    $msg = "User deleted successfully.";
}

// Get Users
$users = [];
if (!$conn->connect_errno) {
    $result = $conn->query("SELECT * FROM users ORDER BY id DESC");
    if ($result) { while ($row = $result->fetch_assoc()) $users[] = $row; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Users | Tavern Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <?php include 'styles.php'; ?>
    <style>
        .user-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .user-card { display: flex; align-items: center; gap: 15px; }
        .user-avatar { width: 50px; height: 50px; border-radius: 50%; background: var(--accent); color: white; display: grid; place-items: center; font-weight: bold; font-size: 1.2rem; }
        .user-meta { margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <h1>Registered Users (<?php echo count($users); ?>)</h1>
        </div>

        <?php if($msg): ?><div class="alert alert-success"><?php echo $msg; ?></div><?php endif; ?>

        <div class="user-grid">
            <?php foreach($users as $u): ?>
            <div class="card">
                <div class="user-card">
                    <div class="user-avatar"><?php echo strtoupper(substr($u['username'],0,1)); ?></div>
                    <div>
                        <h4 style="margin:0; color:var(--text-primary);"><?php echo htmlspecialchars($u['username']); ?></h4>
                        <small style="color:var(--text-secondary);"><?php echo htmlspecialchars($u['email']); ?></small>
                    </div>
                </div>
                <div class="user-meta">
                    <small>ID: #<?php echo $u['id']; ?></small>
                    <form method="POST" onsubmit="return confirm('Delete this user?');">
                        <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                        <button type="submit" name="delete_user" class="btn-small btn-delete">Remove</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>