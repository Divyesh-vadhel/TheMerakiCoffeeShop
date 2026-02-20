<?php
require_once 'auth_check.php';
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Email Function ---
function send_order_email($invoice, $email, $name, $status, $items)
{
    global $conn;
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
        $mail->addAddress($email, $name);
        $mail->isHTML(true);

        $subject = ($status === 'Confirmed') ? '✨ Order Connected! - Meraki' : 'Order Update - Meraki';

        // Styles
        $style_container = "font-family: 'Poppins', sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 12px; background-color: #ffffff; color: #2c1810; overflow: hidden;";
        $style_header = "text-align: center; background: #1a0f0a; padding: 30px; color: white;";
        $style_heading = "font-family: 'Playfair Display', serif; font-size: 32px; color: #d4af37; margin: 0; letter-spacing: 1px;";
        $style_subheading = "color: #e8e3dd; font-style: italic; margin-top: 5px; font-weight: 300;";
        $style_content = "padding: 30px;";
        $style_footer = "text-align: center; font-size: 12px; color: #888; background: #f8f6f3; padding: 20px;";

        // Table Styles
        $style_table = "width: 100%; border-collapse: collapse; margin: 20px 0;";
        $style_th = "text-align: left; padding: 12px; border-bottom: 2px solid #eee; color: #6b5d52; font-size: 0.85rem; text-transform: uppercase;";
        $style_td = "padding: 15px 10px; border-bottom: 1px solid #f3f3f3; vertical-align: middle;";
        $style_img = "width: 50px; height: 50px; border-radius: 6px; object-fit: cover;";

        $status_color = ($status === 'Confirmed') ? '#2e7d32' : '#c62828';
        $status_msg = ($status === 'Confirmed') ? "We've received your order and it's being prepared with love! ☕" : "Your order has been cancelled.";

        // Build Items Table
        $items_html = '';
        $grand_total = 0;
        $i = 0;

        foreach ($items as $item) {
            $i++;
            $cid = 'img_item_' . $i;
            $img_src = 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=100&q=80'; // Default online fallback

            if (!empty($item['image'])) {
                // Check if it's already a URL
                if (strpos($item['image'], 'http') === 0) {
                    $img_src = $item['image'];
                } else {
                    // It's a local file. We MUST embed it for it to show in Gmail users from Localhost
                    $local_path = __DIR__ . '/../images/' . $item['image'];
                    if (file_exists($local_path)) {
                        $mail->addEmbeddedImage($local_path, $cid, $item['image']);
                        $img_src = 'cid:' . $cid;
                    }
                }
            }

            $subtotal = $item['price'] * $item['quantity'];
            $grand_total += $subtotal;

            $items_html .= "
            <tr>
                <td style=\"$style_td width: 60px;\"><img src=\"$img_src\" style=\"$style_img\" alt=\"Item\"></td>
                <td style=\"$style_td\">
                    <div style=\"font-weight: 600; font-size: 1rem;\">{$item['title']}</div>
                    <div style=\"font-size: 0.85rem; color: #888;\">Qty: {$item['quantity']}</div>
                </td>
                <td style=\"$style_td text-align: right; white-space: nowrap;\">
                    <div style=\"font-weight: 600;\">₹" . number_format($subtotal, 2) . "</div>
                </td>
            </tr>";
        }

        $mail->Subject = $subject;
        $mail->Body = "
            <div style=\"$style_container\">
                <div style=\"$style_header\">
                    <h1 style=\"$style_heading\">Meraki Coffee Shop</h1>
                    <p style=\"$style_subheading\">Fresh Brews & Good Vibes</p>
                </div>
                <div style=\"$style_content\">
                    <h2 style=\"color: $status_color; margin-top: 0;\">Order $status</h2>
                    <p style=\"font-size: 1.1rem;\">Hello <strong>$name</strong>,</p>
                    <p style=\"color: #555; line-height: 1.6;\">$status_msg</p>
                    
                    <div style=\"background: #fcfcfc; border: 1px solid #eee; border-radius: 8px; padding: 20px; margin-top: 30px;\">
                        <div style=\"display: flex; justify-content: space-between; margin-bottom: 20px;\">
                            <div><strong>Invoice:</strong> <span style=\"color: #888;\">$invoice</span></div>
                            <div><strong>Date:</strong> <span style=\"color: #888;\">" . date('M d, Y') . "</span></div>
                        </div>
                        
                        <table style=\"$style_table\">
                            <thead>
                                <tr>
                                    <th style=\"$style_th\">Item</th>
                                    <th style=\"$style_th\">Details</th>
                                    <th style=\"$style_th text-align: right;\">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                $items_html
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan=\"2\" style=\"padding: 15px; text-align: right; font-weight: 600; color: #6b5d52;\">Total Amount</td>
                                    <td style=\"padding: 15px; text-align: right; font-weight: 700; font-size: 1.2rem; color: #d4af37;\">₹" . number_format($grand_total, 2) . "</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div style=\"text-align: center; margin-top: 30px;\">
                        <a href=\"" . APP_URL . "/Project-2/status.php\" style=\"background-color: #d4af37; color: white; padding: 12px 24px; text-decoration: none; border-radius: 50px; font-weight: 600;\">View Order Status</a>
                    </div>
                </div>
                <div style=\"$style_footer\">
                    <p>Meraki Coffee Shop • Darshan University, Rajkot, Gujarat 363030</p>
                    <p style=\"color: #bbb;\">© " . date('Y') . " Meraki Coffee Shop. All rights reserved.</p>
                </div>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// --- POST Handling ---
include '../db_connect.php'; // Standardized connection
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invoice'], $_POST['status'])) {
    ob_start(); // Start buffering to catch any stray output
    $inv = $_POST['invoice'];
    $status = $_POST['status'];
    $isAjax = isset($_POST['ajax']) && $_POST['ajax'] == '1';

    // Update status for all items in this invoice
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE invoice_number = ?");
    $stmt->bind_param("ss", $status, $inv);

    if ($stmt->execute()) {
        // Fetch user email details
        $u_query = $conn->prepare("SELECT u.email, u.username, o.address FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.invoice_number = ? LIMIT 1");
        $u_query->bind_param("s", $inv);
        $u_query->execute();
        $userData = $u_query->get_result()->fetch_assoc();

        // Fetch Order Items AND link with coffees table to get the image
        $i_query = $conn->prepare("
            SELECT o.title, o.quantity, o.price, c.image 
            FROM orders o 
            LEFT JOIN coffees c ON o.title = c.name 
            WHERE o.invoice_number = ?
        ");
        $i_query->bind_param("s", $inv);
        $i_query->execute();
        $itemsRes = $i_query->get_result();
        $orderItems = [];
        while ($row = $itemsRes->fetch_assoc()) {
            $orderItems[] = $row;
        }

        $email = $userData['email'] ?? '';
        $name = $userData['username'] ?? 'Guest';

        if (!$email || $email == '') {
            if (preg_match('/Email:\s*([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', $userData['address'], $matches)) {
                $email = $matches[1];
            }
        }

        $msg = "Order updated to $status";

        if ($isAjax) {
            // CRITICAL: Unlock the session file!
            session_write_close();
            
            // Prepare response
            $response = json_encode([
                'status' => 'success',
                'message' => $msg . " (Email processing in background)",
                'new_status' => $status
            ]);
            
            // Clean ANY previous output (warnings, notices, etc.)
            if (ob_get_length()) ob_end_clean();
            
            // Set headers
            header('Content-Type: application/json');
            header('Content-Length: ' . strlen($response));
            header('Connection: close');
            
            echo $response;
            flush();

            // Now the browser should be disconnected. We continue in the background.
            ignore_user_abort(true);
            set_time_limit(120); // Give it enough time to send email

            // Close DB to be safe
            if (isset($conn))
                $conn->close();

            // SEND EMAIL IN BACKGROUND
            if ($email && ($status === 'Confirmed' || $status === 'Cancelled')) {
                send_order_email($inv, $email, $name, $status, $orderItems);
            }
            exit;
        }

        $emailSent = false;
        if ($email && ($status === 'Confirmed' || $status === 'Cancelled')) {
            $emailSent = send_order_email($inv, $email, $name, $status, $orderItems);
        }

        if ($emailSent) {
            $msg .= " & Email Notification Sent.";
        } elseif ($email && ($status === 'Confirmed' || $status === 'Cancelled')) {
            $msg .= " but Email Sending Failed.";
        }

        $_SESSION['flash_msg'] = $msg;
        header("Location: orders.php");
        exit;
    }
}

// --- Fetch Orders ---
$orders = [];
if (!$conn->connect_errno) {
    // Optimization: Added LIMIT 100 to prevent slowness if database grows large
    $res = $conn->query("SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.date DESC, o.invoice_number DESC LIMIT 100");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $inv = $row['invoice_number'];
            if (!isset($orders[$inv])) {
                $orders[$inv] = [
                    'invoice' => $inv,
                    'date' => $row['date'],
                    'user' => $row['username'] ?? 'Guest',
                    'address' => $row['address'],
                    'total' => 0,
                    'items' => [],
                    'status' => $row['status']
                ];
            }
            $orders[$inv]['items'][] = $row;
            $orders[$inv]['total'] += (float) $row['subtotal_amount'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Orders | Tavern Admin</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <?php include 'styles.php'; ?>
    <style>
        .order-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .order-item-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 10px;
            font-size: 0.9rem;
            padding: 5px 0;
            border-bottom: 1px solid #f3f3f3;
        }

        .total-badge {
            background: var(--text-primary);
            color: var(--accent);
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }

        .btn-status {
            padding: 6px 14px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: white;
            font-size: 0.85rem;
            font-weight: 500;
            transition: 0.2s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-status:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-pending {
            background: #f57c00;
        }

        .btn-confirm {
            background: #2e7d32;
        }

        .btn-cancel {
            background: #c62828;
        }

        /* Loading Spinner */
        .btn-status.loading {
            opacity: 0.7;
            pointer-events: none;
            position: relative;
        }

        .btn-status.loading i {
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
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <h1>Recent Orders</h1>
        </div>

        <?php if (isset($_SESSION['flash_msg'])): ?>
            <div
                style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #a7f3d0;">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['flash_msg']; ?>
            </div>
            <?php unset($_SESSION['flash_msg']); ?>
        <?php endif; ?>

        <?php foreach ($orders as $o): ?>
            <div class="card">
                <div class="order-header">
                    <div>
                        <h3 style="font-size:1.1rem;">#<?php echo $o['invoice']; ?></h3>
                        <small style="color:var(--text-secondary);"><?php echo $o['date']; ?> •
                            <?php echo $o['user']; ?></small>
                        <div style="font-size:0.8rem; margin-top:5px; color:var(--text-secondary);">📍
                            <?php echo htmlspecialchars($o['address']); ?>
                        </div>
                    </div>
                    <div>
                        <div style="display:flex; align-items:center; gap:15px;">
                            <?php
                            $order_time = strtotime($o['date']);
                            $is_recent = (time() - $order_time) < 86400;
                            $dbStatus = $o['status'] ?? 'Pending';
                            
                            $displayStatus = $dbStatus;
                            if (($dbStatus === 'Pending' || $dbStatus === 'Confirmed') && !$is_recent) {
                                $displayStatus = 'Completed';
                            }
                            
                            $badgeColor = ($displayStatus === 'Completed' || $displayStatus === 'Confirmed') ? '#2e7d32' : (($displayStatus === 'Cancelled') ? '#c62828' : '#f57c00');
                            ?>
                            <span
                                style="padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; background: <?php echo $badgeColor; ?>; color: white;">
                                <?php echo $displayStatus; ?>
                            </span>


                        </div>
                        <div style="text-align:right; margin-top:12px;"><span
                                class="total-badge">₹<?php echo number_format($o['total'], 2); ?></span></div>
                    </div>
                </div>
                <div>
                    <div class="order-item-row" style="font-weight:600; color:var(--text-secondary);">
                        <div>Item</div>
                        <div>Price</div>
                        <div>Qty</div>
                        <div>Subtotal</div>
                    </div>
                    <?php foreach ($o['items'] as $item): ?>
                        <div class="order-item-row">
                            <div><?php echo $item['title']; ?></div>
                            <div>₹<?php echo $item['price']; ?></div>
                            <div><?php echo $item['quantity']; ?></div>
                            <div>₹<?php echo $item['subtotal_amount']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </main>
    <script>


        function showToast(msg, color) {
            const toast = document.createElement('div');
            toast.className = 'toast-msg';
            toast.style.background = color;
            toast.innerHTML = `<i class="fas fa-info-circle"></i> ${msg}`;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-20px)';
                toast.style.transition = '0.5s';
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        }
    </script>
</body>

</html>```