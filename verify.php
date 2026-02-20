<?php
/**
 * Email verification endpoint
 * Usage: verify.php?token=...&email=...
 */
require_once __DIR__ . '/config.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : '';

include 'db_connect.php';

$msg = '';
if ($token && $email) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=? AND activation_token=? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('ss', $email, $token);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            $id = $row['id'];
            $up = $conn->prepare("UPDATE users SET is_active=1, activation_token=NULL WHERE id=?");
            if ($up) {
                $up->bind_param('i', $id);
                if ($up->execute()) {
                    $msg = 'Your email has been verified. You can now login.';
                } else {
                    $msg = 'Failed to activate account.';
                }
                $up->close();
            }
        } else {
            $msg = 'Invalid verification link or token already used.';
        }
        $stmt->close();
    } else {
        $msg = 'Database error.';
    }
} else {
    $msg = 'Missing token or email.';
}

$pageTitle = 'Email Verification | Meraki Coffee House';
$extraCss = '<style>
        .verification-container {
            max-width: 600px;
            margin: 150px auto 100px; /* Offset for fixed header */
            padding: 40px;
            background: white;
            border-radius: 16px;
            border: 1px solid var(--border);
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .verification-container h1 {
            font-family: \'Playfair Display\', serif;
            color: var(--primary);
            margin-bottom: 20px;
        }
        .verification-container p {
            font-size: 1.1rem;
            color: var(--text-main);
            margin-bottom: 30px;
        }
        .btn-link {
            display: inline-block;
            background: var(--accent);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }
        .btn-link:hover {
            background: var(--accent-dark);
        }
    </style>';
include 'header.php';
?>

    <div class="verification-container">
        <h1>Verification Status</h1>
        <p><?php echo htmlspecialchars($msg); ?></p>
        <a href="login.php" class="btn-link">Go to Login</a>
    </div>

<?php include 'footer.php'; ?>
