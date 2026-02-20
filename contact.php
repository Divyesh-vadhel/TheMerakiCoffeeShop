<?php
session_start();
include 'db_connect.php';

$user_data = null;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $u_res = $conn->query("SELECT username, email FROM users WHERE id = $uid");
    if ($u_res && $u_res->num_rows > 0) {
        $user_data = $u_res->fetch_assoc();
    }
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logic to handle message saving would go here
    $msg = 'Thank you! Your message has been sent successfully.';
}

$pageTitle = 'Contact | The Tavern Coffee House';
$extraCss = '<style>
        /* CONTACT SPECIFIC */
        .layout {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 40px;
            padding: 60px 0;
            padding-top: 140px; /* Adjusted for fixed header */
        }

        .card {
            background: var(--bg-card); /* Undefined, fallback */
            background-color: white; 
            padding: 40px;
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        h2,
        h3 {
            /* font-family: \'Playfair Display\'; */
            margin-bottom: 20px;
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 15px;
        }

        label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary);
        }

        input,
        textarea {
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg-soft); /* Undefined */
            background-color: #fcfcfc;
            font-family: inherit;
            font-size: 0.95rem;
            width: 100%;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: var(--accent);
            background: #fff;
        }

        .btn-submit {
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px;
            width: 100%;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.2s;
        }

        .btn-submit:hover {
            background: var(--accent);
        }

        @media (max-width: 768px) {
            .layout {
                grid-template-columns: 1fr;
            }
        }
    </style>';
include 'header.php';
?>

    <main class="container layout">
        <div class="card" data-aos="fade-right">
            <h2>Get in Touch</h2>
            <?php if ($msg): ?>
                <div style="background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i> <?php echo $msg; ?>
                </div>
            <?php endif; ?>
            <form action="contact.php" method="post">
                <div class="field-group">
                    <label>Name</label>
                    <input type="text" name="name" placeholder="Your name" value="<?php echo $user_data ? htmlspecialchars($user_data['username']) : ''; ?>" required>
                </div>
                <div class="field-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="your@email.com" value="<?php echo $user_data ? htmlspecialchars($user_data['email']) : ''; ?>" required>
                </div>
                <div class="field-group">
                    <label>Message</label>
                    <textarea name="message" rows="5" placeholder="How can we help?" required></textarea>
                </div>
                <button type="submit" class="btn-submit">Send Message</button>
            </form>
        </div>

        <div class="card" style="background: var(--bg-soft); border:none;" data-aos="fade-left">
            <h3>Visit Us</h3>
            <p style="margin-bottom:20px; color:var(--text-muted);">Darshan University at Hadala<br>Rajkot-Morbi Highway
                3630003</p>
            <div style="border-top:1px solid #ddd; padding-top:20px; color:var(--text-muted);">
                <p style="margin-bottom:10px;"><strong>Email:</strong> info@merakicoffee.com</p>
                <p><strong>Phone:</strong> +91 6355241317</p>
            </div>
        </div>
    </main>

<?php include 'footer.php'; ?>