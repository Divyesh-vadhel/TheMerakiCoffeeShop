<?php
session_start();

include 'db_connect.php';

$err = '';
$msg = '';

// --- PHPMailer Integration ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$err) {
    $email = trim($_POST['email']);

    // 1. Check if the email exists
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $user_row = $result->fetch_assoc();
    $stmt_check->close();

    if ($user_row) {
        $user_id = $user_row['id'];

        // 2. Generate 6-digit OTP
        $otp = rand(100000, 999999);

        // 3. Set expiration (15 minutes)
        $expires = date("Y-m-d H:i:s", time() + 900);

        // 4. Store OTP in password_resets
        $conn->query("DELETE FROM password_resets WHERE user_id = $user_id");
        $stmt_insert = $conn->prepare("INSERT INTO password_resets (user_id, token, expires) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("iss", $user_id, $otp, $expires);
        $stmt_insert->execute();
        $stmt_insert->close();

        // 5. Send Email using Config Settings
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
            $mail->Password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
            $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = defined('SMTP_PORT') ? SMTP_PORT : 587;

            // Sender and Recipient
            $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Meraki Support';
            $fromEmail = defined('EMAIL_FROM_ADDRESS') ? EMAIL_FROM_ADDRESS : $mail->Username;

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body = "
                <div style='font-family: sans-serif; padding: 20px; color: #2C1810;'>
                    <h2>Reset Your Password</h2>
                    <p>Your One-Time Password (OTP) for password reset is:</p>
                    <h1 style='font-size: 32px; letter-spacing: 5px; color: #D4A373;'>$otp</h1>
                    <p>This OTP is valid for 15 minutes.</p>
                    <p>If you did not request this, please ignore this email.</p>
                </div>
            ";

            $mail->send();

            // Redirect to Verify OTP page
            $_SESSION['reset_email'] = $email; // Pass email to next page
            header('Location: verify_otp.php');
            exit();

        } catch (Exception $e) {
            // Show detailed error for debugging since the user asked to solve it
            $err = "Mailer Error: " . $mail->ErrorInfo;
        }
    } else {
        $err = "We couldn't find an account with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Forgot Password | Meraki Coffee House'; ?></title>
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

        .auth-card {
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

        .auth-header {
            margin-bottom: 35px;
            text-align: center;
        }

        .auth-header h2 {
            color: var(--text-main);
            font-family: 'Cormorant', serif;
            font-size: 2.2rem;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .auth-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
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
        }

        .form-group input {
            width: 100%;
            padding: 16px 16px 16px 48px;
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
        }

        .btn-auth:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(212, 163, 115, 0.3);
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

        .back-btn {
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

        .back-btn:hover {
            opacity: 1;
            transform: translateX(-5px);
        }

        @media (max-width: 500px) {
            .auth-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>

    <div class="bg-container"></div>
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <a href="login.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back to Login
    </a>

    <div class="auth-card" data-aos="zoom-in" data-aos-duration="800">
        <div class="logo-box">
            <h1>Meraki.</h1>
        </div>

        <div class="auth-header">
            <h2>Reset Password</h2>
            <p>Enter your email to receive an OTP.</p>
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

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrapper">
                    <input type="email" name="email" placeholder="Enter your registered email" required autofocus>
                    <i class="fas fa-envelope"></i>
                </div>
            </div>

            <button type="submit" class="btn-auth">Send OTP</button>

            <div class="form-footer">
                Remember your password? <a href="login.php">Sign In</a>
            </div>
        </form>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true
        });
    </script>
</body>

</html>