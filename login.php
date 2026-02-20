<?php
// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connect.php';

$err = '';
$msg = '';
$schema_hint = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['email']);
    $password_input = $_POST['password'];

    // Prepare SQL: include activation status only if the column exists
    $hasIsActive = false;
    $colRes = $conn->query("SHOW COLUMNS FROM users LIKE 'is_active'");
    if ($colRes && $colRes->num_rows === 1) {
        $hasIsActive = true;
    }

    if ($hasIsActive) {
        $sql = "SELECT id, password, is_active FROM users WHERE email=?";
    } else {
        $sql = "SELECT id, password FROM users WHERE email=?";
        $schema_hint = 'Note: Email verification is disabled until DB migration is applied.';
    }

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $login_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user_data = $result->fetch_assoc();
            $stored = $user_data['password'];

            if (isset($user_data['is_active']) && (int) $user_data['is_active'] === 0) {
                $err = 'Please verify your email address.';
            } else {
                $password_ok = false;
                if (password_verify($password_input, $stored)) {
                    $password_ok = true;
                } elseif (strlen($stored) === 32 && md5($password_input) === $stored) {
                    // Legacy MD5 handling
                    $newhash = password_hash($password_input, PASSWORD_DEFAULT);
                    $up = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                    if ($up) {
                        $up->bind_param('si', $newhash, $user_data['id']);
                        $up->execute();
                        $up->close();
                    }
                    $password_ok = true;
                }

                if ($password_ok) {
                    $_SESSION['user_id'] = $user_data['id'];

                    // --- START NEW REDIRECT LOGIC ---
                    if (!empty($_POST['redirect_url'])) {
                        $url = $_POST['redirect_url'];
                        if (!empty($_POST['book_id'])) {
                            // Append book_id to the redirect URL
                            $url .= (strpos($url, '?') === false ? '?' : '&') . 'book_id=' . urlencode($_POST['book_id']);
                        }
                        header("Location: " . $url);
                    } elseif (!empty($_POST['redirect_product_id'])) {
                        $pid = $_POST['redirect_product_id'];
                        header("Location: cart.php?action=add&id=" . $pid);
                    } else {
                        header('Location: index.php');
                    }
                    // --- END NEW REDIRECT LOGIC ---
                    exit();
                } else {
                    $err = 'Invalid credentials.';
                }
            }
        } else {
            $err = 'Invalid credentials.';
        }
        $stmt->close();
    }
}

if (isset($_GET['registered']))
    $msg = 'Account created.';
if (isset($_GET['checkemail']))
    $msg = 'Account created. Please verify your email.';


$pageTitle = 'Login | Meraki Coffee House';
$extraCss = '<style>
        /* LOGIN SPECIFIC */
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
            background: url(\'https://images.unsplash.com/photo-1497935586351-b67a49e012bf?auto=format&fit=crop&w=800&q=80\') center/cover no-repeat;
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
            border-color: var(--primary); /* Fallback to primary if accent not defined in this scope, but header.php defines them globally */
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
            justify-content: space-between;
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
                min-height: 250px;
                padding: 40px;
            }

            .brand-text h1 {
                font-size: 3rem;
            }

            .right-panel {
                padding: 60px 20px;
            }
        }
    </style>';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Login | Meraki Coffee House'; ?></title>
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
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background */
        .bg-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.8)),
                url('https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
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

        /* Floating Shapes for extra flair */
        .shape {
            position: absolute;
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

        .login-card {
            width: 100%;
            max-width: 450px;
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
            margin-bottom: 40px;
        }

        .logo-box h1 {
            font-family: 'Italiana', serif;
            color: var(--primary);
            font-size: 3rem;
            letter-spacing: 4px;
            margin-bottom: 5px;
        }

        .logo-box p {
            color: var(--text-muted);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .auth-header {
            margin-bottom: 35px;
            text-align: center;
        }

        .auth-header h2 {
            color: var(--text-main);
            font-family: 'Cormorant', serif;
            font-size: 2.2rem;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: var(--text-muted);
            font-size: 0.8rem;
            margin-bottom: 8px;
            font-weight: 500;
            transition: 0.3s;
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
            padding: 16px 48px;
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

        .btn-login {
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

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(212, 163, 115, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .form-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .form-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .forgot-pass {
            display: block;
            text-align: right;
            margin-top: 8px;
            color: var(--text-muted);
            font-size: 0.8rem;
            text-decoration: none;
            transition: 0.3s;
        }

        .forgot-pass:hover {
            color: var(--primary);
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

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #86efac;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        @media (max-width: 500px) {
            .login-card {
                margin: 20px;
                padding: 30px 20px;
            }
        }

        /* Back to Home */
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
    </style>
</head>

<body>

    <div class="bg-container"></div>
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <a href="index.php" class="back-home">
        <i class="fas fa-arrow-left"></i> Back to Meraki
    </a>

    <div class="login-card" data-aos="zoom-in" data-aos-duration="800">
        <div class="logo-box">
            <h1>Meraki.</h1>
            <p>Brewing Excellence</p>
        </div>

        <div class="auth-header">
            <h2>Welcome Back</h2>
        </div>

        <?php if ($err): ?>
            <div class='alert alert-error'>
                <i class="fas fa-exclamation-circle"></i> <?php echo $err; ?>
            </div>
        <?php endif; ?>

        <?php if ($msg): ?>
            <div class='alert alert-success'>
                <i class="fas fa-check-circle"></i> <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrapper">
                    <input type="email" name="email" placeholder="Enter your email" required autofocus>
                    <i class="fas fa-envelope"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <i class="fas fa-lock"></i>
                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                </div>
                <a href="forgot_password.php" class="forgot-pass">Forgot Password?</a>
            </div>

            <?php if (isset($_GET['add_id'])): ?>
                <input type="hidden" name="redirect_product_id" value="<?php echo htmlspecialchars($_GET['add_id']); ?>">
            <?php endif; ?>

            <?php if (isset($_GET['redirect'])): ?>
                <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_GET['redirect']); ?>">
            <?php endif; ?>

            <?php if (isset($_GET['book_id'])): ?>
                <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($_GET['book_id']); ?>">
            <?php endif; ?>

            <button type="submit" name="login_btn" class="btn-login">Sign In</button>

            <div class="form-footer">
                New here? <a href="register.php">Create an account</a>
            </div>
        </form>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true
        });

        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>