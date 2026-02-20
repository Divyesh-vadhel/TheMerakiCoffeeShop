<?php
define('SUPPRESS_DB_ERROR', true);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';
// Check DB connection early so we can show a friendly message
$db_ok = true;
if ($conn->connect_error) {
    $db_ok = false;
    error_log('DB connection error: ' . $conn->connect_error);
    // Do not show raw DB errors to users
    $err = 'Database connection failed. Please try again later.';
}
// Load app SMTP config (optional but recommended)
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}
// Load PHPMailer classes if available
if (file_exists(__DIR__ . '/PHPMailer-master/src/PHPMailer.php')) {
    require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
    require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';
}
// Do not overwrite any earlier DB error message
if (!isset($err))
    $err = '';
if (!$db_ok) {
    // If DB is not available, we can't proceed
    $err = 'Database connection failed. Please try again later.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$db_ok) {
        // Stop early if DB is down
        // $err already set above
    } else {
        $name = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $passwordRaw = $_POST['password'];

        // --- UPDATED PASSWORD VALIDATION START ---
        $password_pattern = '/^
        (?=.{6,})              # Minimum 6 characters length (already checked below, but good practice)
        [a-zA-Z]               # First character must be an alphabet
        (?=.*[0-9])            # Must contain at least one digit
        (?=.*[^a-zA-Z0-9\s])   # Must contain at least one special character (not a letter, number, or whitespace)
        .*
    $/ix';

        // Basic validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = 'Please enter a valid email address.';
        } elseif (strlen($passwordRaw) < 6) {
            // Keeps the explicit length check separate for a clearer error message
            $err = 'Password must be at least 6 characters.';
        } elseif (!preg_match($password_pattern, $passwordRaw)) {
            // New combined validation check
            $err = 'Password must start with a letter, and contain at least one number and one special character.';
        } else {
            // --- UPDATED PASSWORD VALIDATION END ---

            // Determine DB schema capabilities
            $colRes = $conn->query("SHOW COLUMNS FROM users LIKE 'password'");
            $usePasswordHash = true;
            $password_col_too_small = false;

            if ($colRes && $colRes->num_rows === 1) {
                $col = $colRes->fetch_assoc();
                if (preg_match('/varchar\((\d+)\)/i', $col['Type'], $m)) {
                    $len = (int) $m[1];
                    if ($len < 60) {
                        $usePasswordHash = false;
                        $password_col_too_small = true;
                    }
                }
            }

            // SECURITY FIX: BLOCK REGISTRATION IF PASSWORD COLUMN IS TOO SMALL
            if ($password_col_too_small) {
                $err = 'System Error: The user database column for passwords is too small for secure hashing. Please contact the administrator to fix this issue.';
                error_log('Registration blocked: DB password column too small for secure hash. Length is ' . $len);

            } else {
                // Original registration logic starts here (secure path)

                $hasIsActive = ($conn->query("SHOW COLUMNS FROM users LIKE 'is_active'") && $conn->query("SHOW COLUMNS FROM users LIKE 'is_active'")->num_rows === 1);
                $hasActivationToken = ($conn->query("SHOW COLUMNS FROM users LIKE 'activation_token'") && $conn->query("SHOW COLUMNS FROM users LIKE 'activation_token'")->num_rows === 1);

                $token = bin2hex(random_bytes(16));
                $created = date('Y-m-d H:i:s');

                // Use password_hash (MD5 fallback logic removed)
                $storeHash = password_hash($passwordRaw, PASSWORD_DEFAULT);


                // If table supports activation columns, insert with activation
                if ($hasIsActive && $hasActivationToken && $usePasswordHash) {
                    $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password, create_datetime, is_active, activation_token) VALUES (?, ?, ?, ?, ?, 0, ?)");
                    if (!$stmt) {
                        error_log('Prepare failed (activation insert): ' . $conn->error);
                        $err = 'Database error. Please contact the administrator.';
                    } else {
                        $stmt->bind_param('ssssss', $name, $email, $phone, $storeHash, $created, $token);
                        if ($stmt->execute()) {
                            // Send verification email if configured
                            if (class_exists('PHPMailer\\PHPMailer\\PHPMailer') && defined('SMTP_HOST') && defined('SMTP_USERNAME') && defined('SMTP_PASSWORD')) {
                                try {
                                    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                                    $mail->isSMTP();
                                    $mail->Host = SMTP_HOST;
                                    $mail->SMTPAuth = true;
                                    $mail->Username = SMTP_USERNAME;
                                    $mail->Password = SMTP_PASSWORD;
                                    $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
                                    $mail->Port = defined('SMTP_PORT') ? SMTP_PORT : 587;

                                    $fromAddress = defined('EMAIL_FROM_ADDRESS') ? EMAIL_FROM_ADDRESS : SMTP_USERNAME;
                                    $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Website';

                                    $mail->setFrom($fromAddress, $fromName);
                                    $mail->addAddress($email, $name);
                                    $mail->isHTML(true);
                                    $mail->Subject = 'Please verify your email for ' . (defined('APP_NAME') ? APP_NAME : 'Our Site');

                                    $base = defined('APP_URL') ? rtrim(APP_URL, '/') : ((isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
                                    $verifyUrl = $base . '/verify.php?token=' . urlencode($token) . '&email=' . urlencode($email);

                                    $mail->Body = "<p>Hi " . htmlspecialchars($name) . ",</p><p>Thanks for registering. Please <a href=\"" . $verifyUrl . "\">click here to verify your email</a>.</p><p>If you didn't sign up, ignore this message.</p>";

                                    $mail->send();
                                } catch (\PHPMailer\PHPMailer\Exception $e) {
                                    error_log('Mail error: ' . $e->getMessage());
                                }
                            }

                            header('Location: login.php?registered=1&checkemail=1');
                            exit;
                        } else {
                            error_log('Execute failed (activation insert): ' . $stmt->error);
                            $err = 'Error registering. Please try again later.';
                        }
                        $stmt->close();
                    }
                } else {
                    // fallback insert without activation (mark active immediately)
                    $stmt2 = $conn->prepare("INSERT INTO users (username, email, phone, password, create_datetime) VALUES (?, ?, ?, ?, ?)");
                    if (!$stmt2) {
                        error_log('Prepare failed (fallback insert): ' . $conn->error);
                        $err = 'Database error. Please contact the administrator.';
                    } else {
                        $stmt2->bind_param('sssss', $name, $email, $phone, $storeHash, $created);
                        if ($stmt2->execute()) {
                            header('Location: login.php?registered=1');
                            exit;
                        } else {
                            error_log('Execute failed (fallback insert): ' . $stmt2->error);
                            $err = 'Error registering. Please try again later.';
                        }
                        $stmt2->close();
                    }
                }
            } // End of secure registration logic block
        }
    }
}

$pageTitle = 'Register | Meraki Coffee House';
$extraCss = '<style>
        /* REGISTER SPECIFIC */
        .split-screen {
            display: flex;
            width: 100%;
            min-height: 100vh;
            background: white;
            overflow: hidden;
            /* margin-top: 90px; Removed for standalone page */
        }

        .left-panel {
            flex: 1;
            background: url(\'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=800&q=80\') center/cover no-repeat;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .left-panel::before {
            content: \'\';
            position: absolute;
            inset: 0;
            background: linear-gradient(rgba(44, 24, 16, 0.5), rgba(44, 24, 16, 0.8));
        }

        .brand-text {
            position: relative;
            z-index: 2;
            color: white;
            text-align: center;
        }

        .brand-text h1 {
            font-family: \'Playfair Display\';
            font-size: 4rem;
            margin-bottom: 10px;
        }

        .brand-text p {
            font-size: 1.2rem;
            opacity: 0.9;
            letter-spacing: 1px;
            font-weight: 300;
        }

        .right-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: white;
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
        }


        .auth-header {
            margin-bottom: 40px;
            text-align: left;
        }

        .auth-header h2 {
            font-family: \'Playfair Display\';
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .auth-header p {
            color: #888;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--primary);
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            transition: 0.3s;
            background: #FAFAF8;
        }

        .form-group input:focus {
            border-color: var(--primary);
            outline: none;
            background: white;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            color: white;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn:hover {
            background: var(--accent);
        }

        .links {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            font-size: 0.9rem;
        }

        .links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: 0.2s;
        }

        .links a:hover {
            color: var(--accent);
        }

        .links span {
            color: #888;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        @media (max-width: 900px) {
            .split-screen {
                flex-direction: column;
                height: auto;
            }

            .left-panel {
                min-height: 200px;
                padding: 40px;
            }

            .brand-text h1 {
                font-size: 3rem;
            }

            .right-panel {
                padding: 40px 20px;
            }

            .nav-home {
                top: 20px;
                left: 20px;
            }
        }
    </style>';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Register | Meraki Coffee House'; ?></title>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Italiana&family=Cormorant:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #d4a373;
            --primary-dark: #bfa07d;
            --dark: #0c0a09;
            --glass: rgba(12, 10, 9, 0.7);
            --glass-border: rgba(212, 163, 115, 0.2);
            --text-main: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.6);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
            padding: 40px 20px;
        }

        /* Animated Background */
        .bg-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.8)),
                url('https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
            filter: blur(4px);
            transform: scale(1.1);
            animation: bgZoom 20s infinite alternate ease-in-out;
        }

        @keyframes bgZoom {
            from {
                transform: scale(1.1);
            }

            to {
                transform: scale(1.2);
            }
        }

        .shape {
            position: fixed;
            background: radial-gradient(circle, var(--primary) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.15;
            z-index: -1;
            animation: float 15s infinite alternate ease-in-out;
        }

        .shape-1 {
            width: 500px;
            height: 500px;
            top: -10%;
            left: -10%;
        }

        .shape-2 {
            width: 400px;
            height: 400px;
            bottom: -10%;
            right: -10%;
            animation-delay: -5s;
        }

        @keyframes float {
            0% {
                transform: translateY(0) translateX(0);
            }

            100% {
                transform: translateY(50px) translateX(30px);
            }
        }

        .auth-card {
            width: 100%;
            max-width: 500px;
            padding: 50px;
            background: var(--glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 10;
        }

        .logo-box {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-box h1 {
            font-family: 'Italiana', serif;
            color: var(--primary);
            font-size: 2.8rem;
            letter-spacing: 4px;
            margin-bottom: 5px;
        }

        .logo-box p {
            color: var(--text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .auth-header {
            margin-bottom: 30px;
            text-align: center;
        }

        .auth-header h2 {
            color: var(--text-main);
            font-family: 'Cormorant', serif;
            font-size: 2.2rem;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 22px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: var(--text-muted);
            font-size: 0.8rem;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .form-group input {
            width: 100%;
            padding: 14px 48px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 4px rgba(212, 163, 115, 0.1);
        }

        .form-group input:focus+i {
            color: var(--primary);
        }

        .input-wrapper .toggle-password {
            position: absolute;
            right: 18px;
            left: auto;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            cursor: pointer;
            transition: 0.3s;
            z-index: 10;
        }

        .input-wrapper i.fa-lock {
            left: 18px;
            right: auto;
        }

        .toggle-password:hover {
            color: var(--primary);
        }

        .btn-auth {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 10px;
            box-shadow: 0 10px 20px rgba(212, 163, 115, 0.2);
        }

        .btn-auth:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(212, 163, 115, 0.3);
        }

        .form-footer {
            margin-top: 25px;
            text-align: center;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .back-home {
            position: absolute;
            top: 40px;
            left: 40px;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            opacity: 0.7;
            transition: 0.3s;
            z-index: 100;
        }

        .back-home:hover {
            opacity: 1;
            transform: translateX(-5px);
        }

        @media (max-width: 500px) {
            .auth-card {
                padding: 30px 20px;
            }

            .back-home {
                top: 20px;
                left: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="bg-container"></div>
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <a href="index.php" class="back-home">
        <i class="fas fa-arrow-left"></i> Back to Meraki
    </a>

    <div class="auth-card" data-aos="fade-up" data-aos-duration="800">
        <div class="logo-box">
            <h1>Meraki.</h1>
            <p>Join our Community</p>
        </div>

        <div class="auth-header">
            <h2>Create Account</h2>
        </div>

        <?php if ($err): ?>
            <div class='alert alert-error'>
                <i class="fas fa-exclamation-circle"></i> <?php echo $err; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <div class="input-wrapper">
                    <input type="text" name="username" placeholder="Full Name" required autofocus>
                    <i class="fas fa-user"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrapper">
                    <input type="email" name="email" placeholder="abc@example.com" required>
                    <i class="fas fa-envelope"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Mobile Number</label>
                <div class="input-wrapper">
                    <input type="tel" name="phone" placeholder="1234567890" required>
                    <i class="fas fa-phone"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <i class="fas fa-lock"></i>
                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                </div>
                <p style="color: var(--text-muted); font-size: 0.7rem; margin-top: 5px;">
                    Min 6 chars, must include letter, number & special char.
                </p>
            </div>

            <button type="submit" class="btn-auth">Register</button>

            <div class="form-footer">
                Already a member? <a href="login.php">Sign In</a>
            </div>
        </form>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true
        });

        // Toggle Password Visibility
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            // toggle the eye slash icon
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>