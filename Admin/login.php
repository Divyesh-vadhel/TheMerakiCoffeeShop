<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'kapetann');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $u = $conn->real_escape_string($_POST['username']);
    $p = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE username='$u' AND password='$p'";
    $res = $conn->query($sql);

    if ($res && $res->num_rows > 0) {
        $user_data = $res->fetch_assoc();
        $_SESSION['admin_id'] = $user_data['id'];
        $_SESSION['admin_username'] = $u;

        header('Location: index.php');
        exit();
    } else {
        $err = "Invalid Credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Login | Meraki</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #2C1810;
            --accent: #D4A373;
            --bg-body: #FAFAF8;
            --border: #E5E0D8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-body);
            min-height: 100vh;
            display: grid;
            place-items: center;
            color: var(--primary);
        }

        .split-screen {
            display: flex;
            width: 100%;
            height: 100vh;
            background: white;
            overflow: hidden;
        }

        .left-panel {
            flex: 1;
            background: url('https://images.unsplash.com/photo-1497935586351-b67a49e012bf?auto=format&fit=crop&w=800&q=80') center/cover no-repeat;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .left-panel::before {
            content: '';
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
            font-family: 'Playfair Display';
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
            font-family: 'Playfair Display';
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .auth-header p {
            color: #888;
        }

        .form-group {
            margin-bottom: 25px;
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
            border-radius: 8px;
        }

        .btn:hover {
            background: var(--accent);
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            background: #ffebee;
            color: #c62828;
        }

        /* Loader Animation */
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .loader-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid #E5E0D8;
            border-top: 5px solid #2C1810;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 900px) {
            .split-screen {
                flex-direction: column;
            }

            .left-panel {
                flex: 0 0 200px;
            }
        }
    </style>
</head>

<body>
    <!-- Loader -->
    <div class="loader-overlay" id="loader">
        <div class="spinner"></div>
    </div>

    <div class="split-screen">
        <div class="left-panel">
            <div class="brand-text">
                <h1>Meraki</h1>
                <p>Admin Portal</p>
            </div>
        </div>
        <div class="right-panel">
            <div class="auth-container">
                <div class="auth-header">
                    <h2>Welcome Back</h2>
                    <p>Enter your credentials to access the dashboard.</p>
                </div>

                <?php if ($err): ?>
                    <div class='alert'>
                        <i class="fas fa-exclamation-circle"></i> <?php echo $err; ?>
                    </div>
                <?php endif; ?>

                <form method="post" id="loginForm">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit" class="btn">Login</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            // Prevent immediate submission to allow UI update
            e.preventDefault();

            // Show loader
            document.getElementById('loader').classList.add('active');
            document.querySelector('.btn').innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Authenticating...';
            document.querySelector('.btn').style.opacity = '0.8';

            // Submit after short delay to let browser render the spinner
            setTimeout(() => {
                this.submit();
            }, 500);
        });
    </script>
</body>

</html>