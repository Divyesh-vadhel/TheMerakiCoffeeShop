<?php
require_once 'auth_check.php';
$conn = @new mysqli('localhost', 'root', '', 'kapetann');
$msgs = [];
$q = $conn->query("SELECT * FROM messages ORDER BY created_at DESC");
if($q) while($r = $q->fetch_assoc()) $msgs[] = $r;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Messages | Tavern Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <?php include 'styles.php'; ?>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header"><h1>Messages</h1></div>
        <?php foreach($msgs as $m): ?>
        <div class="card">
            <h3><?php echo htmlspecialchars($m['name']); ?></h3>
            <small style="color:var(--text-secondary);"><?php echo $m['email']; ?> • <?php echo $m['created_at']; ?></small>
            <p style="margin-top:10px; background:#f9f9f9; padding:15px; border-radius:8px;">
                <?php echo nl2br(htmlspecialchars($m['message'])); ?>
            </p>
        </div>
        <?php endforeach; ?>
    </main>
</body>
</html>