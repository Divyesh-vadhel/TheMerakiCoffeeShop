<?php
require_once 'auth_check.php';
$conn = new mysqli('localhost', 'root', '', 'kapetann');
$msg = '';
$msg_type = 'success';

require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ======================================================
   EMAIL FUNCTIONS
====================================================== */

function send_email($data, $type = 'confirm')
{
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(EMAIL_FROM_ADDRESS, SMTP_FROM_NAME);
        $mail->addAddress($data['email'], $data['name']);
        $mail->isHTML(true);

        // Common styles
        $style_container = "font-family: 'Poppins', sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; background-color: #fafaf8; color: #2c1810;";
        $style_header = "text-align: center; border-bottom: 2px solid #d4a373; padding-bottom: 15px; margin-bottom: 20px;";
        $style_heading = "font-family: 'Playfair Display', serif; font-size: 28px; color: #2c1810; margin: 0;";
        $style_subheading = "color: #d4a373; font-style: italic; margin-top: 5px;";
        $style_content = "padding: 0 10px; line-height: 1.6;";
        $style_table = "width: 100%; border-collapse: collapse; margin: 20px 0; background: white; border-radius: 8px; overflow: hidden;";
        $style_td = "padding: 12px 15px; border-bottom: 1px solid #eee;";
        $style_footer = "text-align: center; font-size: 12px; color: #888; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;";

        if ($type === 'confirm') {
            $mail->Subject = '✨ Reservation Confirmed! See you at Meraki';
            $mail->Body = "
                <div style=\"$style_container\">
                    <div style=\"$style_header\">
                        <h1 style=\"$style_heading\">Meraki Coffee House</h1>
                        <p style=\"$style_subheading\">Where every cup tells a story</p>
                    </div>
                    <div style=\"$style_content\">
                        <h2 style=\"color: #2e7d32;\">Reservation Confirmed! 🎉</h2>
                        <p>Dear <strong>{$data['name']}</strong>,</p>
                        <p>We are absolutely delighted to confirm your table reservation! Our team is preparing to provide you with a wonderful dining experience.</p>
                        
                        <table style=\"$style_table\">
                            <tr><td style=\"$style_td\"><strong>📅 Date:</strong></td><td style=\"$style_td\">{$data['date']}</td></tr>
                            <tr><td style=\"$style_td\"><strong>⏰ Time:</strong></td><td style=\"$style_td\">{$data['time']}</td></tr>
                            <tr><td style=\"$style_td\"><strong>👥 Guests:</strong></td><td style=\"$style_td\">{$data['person']}</td></tr>
                            <tr><td style=\"$style_td\"><strong>⏱️ Duration:</strong></td><td style=\"$style_td\">{$data['duration_mins']} Minutes</td></tr>
                            <tr><td style=\"$style_td\"><strong>💰 Booking Fee:</strong></td><td style=\"$style_td\">₹{$data['booking_fee']}</td></tr>
                        </table>

                        <p>Please arrive 5-10 minutes early so we can seat you comfortably. If you need to make any changes, just let us know.</p>
                        <p><em>We can't wait to serve you! ☕</em></p>
                    </div>
                    <div style=\"$style_footer\">
                        <p>Meraki Coffee House using • Darshan University, Rajkot</p>
                        <p>Need help? Reply to this email.</p>
                    </div>
                </div>
            ";
        } else {
            $mail->Subject = 'Reservation Update - Meraki Coffee House';
            $mail->Body = "
                <div style=\"$style_container\">
                    <div style=\"$style_header\">
                        <h1 style=\"$style_heading\">Meraki Coffee House</h1>
                    </div>
                    <div style=\"$style_content\">
                        <h2 style=\"color: #c62828;\">Reservation Cancelled</h2>
                        <p>Dear <strong>{$data['name']}</strong>,</p>
                        <p>We're writing to verify that your reservation for <strong>{$data['date']}</strong> has been cancelled.</p>
                        <p>We're sorry we won't be seeing you this time, but we hope to welcome you to Meraki very soon!</p>
                        <p>If this was a mistake, please book again on our website or contact us.</p>
                    </div>
                    <div style=\"$style_footer\">
                        <p>Meraki Coffee House • Darshan University, Rajkot</p>
                    </div>
                </div>
            ";
        }

        $mail->send();
        return [true, 'Email sent successfully'];

    } catch (Exception $e) {
        error_log($e->getMessage());
        return [false, $mail->ErrorInfo];
    }
}

/* ======================================================
   POST HANDLING
====================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_start();
    $isAjax = isset($_POST['ajax']) && $_POST['ajax'] == '1';

    // Update reservation status
    if (isset($_POST['id'], $_POST['status'])) {

        $id = (int) $_POST['id'];
        $status = $_POST['status'];

        $up = $conn->prepare("UPDATE reservations SET status=? WHERE id=?");
        $up->bind_param('si', $status, $id);

        if ($up->execute()) {

            // Fetch reservation details
            $s = $conn->prepare("SELECT name,email,date,time,person,booking_fee,duration_mins FROM reservations WHERE id=?");
            $s->bind_param('i', $id);
            $s->execute();
            $data = $s->get_result()->fetch_assoc();

            $msg = "Reservation status updated to $status";
            $msg_type = ($status === 'Cancelled') ? 'warning' : 'success';

            if ($isAjax) {
                // CRITICAL: Unlock the session file!
                session_write_close();

                $response = json_encode(['status' => 'success', 'message' => $msg . " (Email processing in background)", 'new_status' => $status]);

                if (ob_get_length())
                    ob_end_clean();

                header('Content-Type: application/json');
                header('Content-Length: ' . strlen($response));
                header('Connection: close');

                echo $response;
                flush();

                ignore_user_abort(true);
                set_time_limit(120);

                if (isset($conn))
                    $conn->close();

                if ($status === 'Confirmed')
                    send_email($data, 'confirm');
                if ($status === 'Cancelled')
                    send_email($data, 'cancel');
                exit;
            }

            if ($status === 'Confirmed') {
                $res = send_email($data, 'confirm');
                if ($res[0]) {
                    $msg = 'Reservation confirmed & email sent';
                } else {
                    $msg = 'Reservation confirmed but EMAIL FAILED: ' . $res[1];
                    $msg_type = 'warning';
                }
            }

            if ($status === 'Cancelled') {
                $res = send_email($data, 'cancel');
                if ($res[0]) {
                    $msg = 'Reservation cancelled & email sent';
                    $msg_type = 'warning';
                } else {
                    $msg = 'Reservation cancelled but EMAIL FAILED: ' . $res[1];
                    $msg_type = 'danger';
                }
            }
        } else {
            if ($isAjax) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
                exit;
            }
            $msg = 'Failed to update status';
            $msg_type = 'danger';
        }
    }

    // Resend confirmation email
    if (isset($_POST['resend_id'])) {
        $id = (int) $_POST['resend_id'];
        $s = $conn->prepare("SELECT name,email,date,time,person,booking_fee,duration_mins FROM reservations WHERE id=?");
        $s->bind_param('i', $id);
        $s->execute();
        $data = $s->get_result()->fetch_assoc();

        $msg = 'Confirmation email is being resent';

        if ($isAjax) {
            session_write_close();
            $response = json_encode(['status' => 'success', 'message' => $msg . " (in background)"]);
            if (ob_get_length())
                ob_end_clean();
            header('Content-Type: application/json');
            header('Content-Length: ' . strlen($response));
            header('Connection: close');
            echo $response;
            flush();
            ignore_user_abort(true);
            set_time_limit(120);
            if (isset($conn))
                $conn->close();
            send_email($data, 'confirm');
            exit;
        }

        send_email($data, 'confirm');
        $msg = 'Confirmation email resent';
    }

    // Delete Reservation

}

/* ======================================================
   FETCH RESERVATIONS
====================================================== */

$reservations = [];
$q = $conn->query("SELECT r.*, u.username 
                   FROM reservations r 
                   LEFT JOIN users u ON r.user_id=u.id 
                   ORDER BY r.date DESC");
while ($row = $q->fetch_assoc()) {
    $reservations[] = $row;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Reservations</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <?php include 'styles.php'; ?>
    <style>
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .toast-msg {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <h1>Reservations</h1>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
        <?php endif; ?>

        <?php foreach ($reservations as $r): ?>
            <div class="card" id="reservation-<?= $r['id'] ?>">
                <div style="display:flex; justify-content:space-between; align-items:start;">
                    <div>
                        <h3 style="margin:0;"><?= $r['name'] ?></h3>
                        <p style="color:var(--text-secondary); margin:5px 0;"><?= $r['date'] ?> at <?= $r['time'] ?> |
                            <?= $r['person'] ?> guests | Location: <?= htmlspecialchars($r['location']) ?> | Duration: <?= $r['duration_mins'] ?> Mins | Fee: ₹<?= number_format($r['booking_fee'], 2) ?>
                        </p>
                        <p style="font-size:0.9rem;">Email: <?= $r['email'] ?></p>
                    </div>
                    <?php
                    $st = $r['status'] ?? 'Pending';
                    $badgeColor = ($st === 'Confirmed') ? '#2e7d32' : (($st === 'Cancelled') ? '#c62828' : '#f57c00');
                    ?>
                    <span class="status-badge" style="background: <?= $badgeColor ?>;"><?= $st ?></span>
                </div>

                <div style="margin-top:15px; display:flex; gap:10px; align-items:center;">




                    <?php if ($r['status'] == 'Confirmed'): ?>
                        <form method="post" style="display:inline; margin:0;">
                            <input type="hidden" name="resend_id" value="<?= $r['id'] ?>">
                            <button class="btn-secondary" style="padding: 10px 20px; font-size: 0.9rem; cursor: pointer;">Resend
                                Email</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </main>

    <script>


        // --- Delete AJAX ---
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to delete this reservation?')) return;

                const btn = form.querySelector('button[type="submit"]');
                const originalText = btn.textContent;
                btn.classList.add('loading');
                btn.textContent = 'Deleting...';

                const formData = new FormData(form);
                formData.append('ajax', '1');

                fetch('table.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showToast(data.message, '#f57c00');
                            form.closest('.card').style.opacity = '0';
                            setTimeout(() => form.closest('.card').remove(), 500);
                        } else {
                            showToast(data.message, '#c62828');
                        }
                    })
                    .finally(() => {
                        btn.classList.remove('loading');
                        btn.textContent = originalText;
                    });
            });
        });

        // --- Resend AJAX ---
        document.querySelectorAll('.resend-form').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const btn = form.querySelector('button[type="submit"]');
                const originalText = btn.textContent;
                btn.classList.add('loading');
                btn.textContent = 'Sending...';

                const formData = new FormData(form);
                formData.append('ajax', '1');

                fetch('table.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showToast(data.message, '#2e7d32');
                        } else {
                            showToast(data.message, '#c62828');
                        }
                    })
                    .finally(() => {
                        btn.classList.remove('loading');
                        btn.textContent = originalText;
                    });
            });
        });

        function showToast(msg, color) {
            const toast = document.createElement('div');
            toast.className = 'toast-msg';
            toast.style.background = color;
            toast.textContent = msg;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        }
    </script>
</body>

</html>