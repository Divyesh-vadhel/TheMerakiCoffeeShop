<?php
include 'db_connect.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];

// 1. Fetch Table Bookings (Join with tables to get table_number)
$user_bookings = [];
$book_sql = "SELECT r.*, t.table_number as t_num, t.location as t_loc 
             FROM reservations r 
             LEFT JOIN tables t ON r.table_id = t.id 
             WHERE r.user_id = ? 
             ORDER BY r.created_at DESC";
$stmt1 = $conn->prepare($book_sql);
$stmt1->bind_param("i", $uid);
$stmt1->execute();
$res_bookings = $stmt1->get_result();
while ($row = $res_bookings->fetch_assoc()) {
    $user_bookings[] = $row;
}

// 2. Fetch Shop Orders
$user_orders = [];
$order_sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY date DESC";
$stmt2 = $conn->prepare($order_sql);
if ($stmt2) {
    $stmt2->bind_param("i", $uid);
    $stmt2->execute();
    $res_orders = $stmt2->get_result();
    while ($row = $res_orders->fetch_assoc()) {
        $user_orders[] = $row;
    }
}

$pageTitle = 'Activity Dashboard | Meraki Coffee House';
$extraCss = '<style>
        /* STATUS DASHBOARD SPECIFIC */
        :root {
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
            --radius: 16px;
        }

        /* Dashboard Header */
        .dashboard-header {
            text-align: center;
            padding: 60px 0 40px;
            margin-top: 40px; /* Offset for header */
        }

        .dashboard-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: var(--primary);
            font-family: \'Playfair Display\', serif;
        }

        .section-title {
            margin: 50px 0 25px;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--primary);
            border-bottom: 2px solid var(--border);
            padding-bottom: 10px;
        }

        .section-title i {
            color: var(--accent);
        }

        /* Card Grid UI */
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }

        .activity-card {
            background: var(--bg-card); /* Fallback */
            background-color: white;
            border-radius: var(--radius);
            padding: 0;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .activity-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            border-color: var(--accent);
        }

        .card-header {
            padding: 20px 25px;
            background: #fff;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .booking-id {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted); /* Fallback */
            color: #6b5d52;
            letter-spacing: 0.5px;
        }

        .card-body {
            padding: 25px;
        }

        .date-display {
            text-align: center;
            margin-bottom: 25px;
            padding: 15px;
            background: var(--bg-body); /* Fallback */
            background-color: #f8f6f3;
            border-radius: 12px;
        }

        .date-day {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
            display: block;
        }

        .date-month {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--accent);
            font-weight: 600;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 0.95rem;
            color: var(--text-main);
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: rgba(212, 163, 115, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
            font-size: 0.9rem;
        }

        /* Status Badges */
        .status-badge {
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .status-confirmed {
            background: #e6f4ea;
            color: #1e7e34;
        }

        .status-pending {
            background: #fff8e1;
            color: #f57c00;
        }

        .status-cancelled {
            background: #fce8e6;
            color: #c62828;
        }

        .price-footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px dashed var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: var(--radius);
            border: 2px dashed var(--border);
            color: var(--text-muted); /* Fallback */
            color: #6b5d52;
        }

        .empty-state i {
            font-size: 3rem;
            color: #e0e0e0;
            margin-bottom: 15px;
        }

        /* ACTION BUTTONS */
        .card-actions {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-action {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid transparent;
            text-decoration: none !important;
        }

        .btn-cancel {
            background: #fffafa;
            color: #c62828;
            border-color: #fce8e6;
        }

        .btn-cancel:hover {
            background: #fce8e6;
        }

        .btn-reschedule {
            background: #f0f7ff;
            color: #007bff;
            border-color: #e1f0ff;
        }

        .btn-reschedule:hover {
            background: #e1f0ff;
        }

        .btn-delete {
            background: #fffafa;
            color: #c62828;
            border-color: #fce8e6;
        }

        .btn-delete:hover {
            background: #fce8e6;
        }
    </style>
    <script>
        function confirmAction(message, url) {
            if (confirm(message)) {
                window.location.href = url;
            }
        }
    </script>';
include 'header.php';
?>

    <div class="container">
        <?php if (isset($_SESSION['flash_msg'])): ?>
            <div style="background: #e6f4ea; color: #1e7e34; padding: 15px; border-radius: 12px; margin-top: 30px; text-align: center; border: 1px solid #c3e6cb;">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['flash_msg']; unset($_SESSION['flash_msg']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div style="background: #fce8e6; color: #c62828; padding: 15px; border-radius: 12px; margin-top: 30px; text-align: center; border: 1px solid #f5c6cb;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-header" data-aos="fade-down">
            <h1>Activity Dashboard</h1>
            <p style="color: var(--text-muted, #6b5d52);">Track your brewing experiences and upcoming visits.</p>
        </div>

        <h2 class="section-title" data-aos="fade-right"><i class="fas fa-calendar-check"></i> Your Table Bookings</h2>
        <?php if (empty($user_bookings)): ?>
            <div class="empty-state" data-aos="zoom-in">
                <i class="fas fa-chair"></i>
                <p>No table bookings yet. Ready for a coffee date?</p>
                <a href="table.php" style="color:var(--accent); text-decoration:underline;">Book Now</a>
            </div>
        <?php else: ?>
            <div class="status-grid">
                <?php foreach ($user_bookings as $booking):
                    $s = strtolower($booking['status']);
                    $cls = ($s == 'confirmed') ? 'status-confirmed' : (($s == 'cancelled') ? 'status-cancelled' : 'status-pending');
                    $date = strtotime($booking['date']);
                    ?>
                    <div class="activity-card" data-aos="fade-up">
                        <div class="card-header">
                            <span class="booking-id">#<?php echo $booking['id']; ?></span>
                            <span class="status-badge <?php echo $cls; ?>"><?php echo ucfirst($s); ?></span>
                        </div>
                        <div class="card-body">
                            <div class="date-display">
                                <span class="date-day"><?php echo date('d', $date); ?></span>
                                <span class="date-month"><?php echo date('F Y', $date); ?></span>
                            </div>

                            <div class="info-row">
                                <div class="info-icon"><i class="far fa-clock"></i></div>
                                <span><?php echo date('h:i A', strtotime($booking['time'])); ?> (<?php echo $booking['duration_mins']; ?> Mins)</span>
                            </div>
                            <div class="info-row">
                                <div class="info-icon"><i class="fas fa-user-friends"></i></div>
                                <span><?php echo $booking['person']; ?> Persons</span>
                            </div>
                            <div class="info-row">
                                <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                                <span>
                                    <?php 
                                    $loc_text = !empty($booking['location']) ? $booking['location'] : ($booking['t_loc'] ?? 'Standard Area');
                                    if(!empty($booking['t_num'])) {
                                        echo "Residency " . $booking['t_num'] . " (" . htmlspecialchars($loc_text) . ")";
                                    } else {
                                        echo htmlspecialchars($loc_text);
                                    }
                                    ?>
                                </span>
                            </div>

                            <?php 
                            $booking_timestamp = strtotime($booking['date'] . ' ' . $booking['time']);
                            $can_cancel = ($s !== 'cancelled' && $booking_timestamp > time());
                            if ($can_cancel || $s !== 'cancelled'): ?>
                            <div class="card-actions">
                                <?php if ($booking_timestamp > (time() + 300)): ?>
                                <a href="table.php?reschedule=<?php echo $booking['id']; ?>" class="btn-action btn-reschedule">
                                    <i class="fas fa-calendar-alt"></i> Reschedule
                                </a>
                                <?php endif; ?>

                                <?php if ($can_cancel): ?>
                                <button onclick="confirmAction('CANCELLATION POLICY: Only 75% of your booking fee will be refunded. Are you sure you want to cancel this reservation?', 'status_action.php?action=cancel_booking&id=<?php echo $booking['id']; ?>')" class="btn-action btn-cancel">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h2 class="section-title" data-aos="fade-right" style="margin-top:60px;"><i class="fas fa-box"></i> Recent
            Orders</h2>
        <?php if (empty($user_orders)): ?>
            <div class="empty-state" data-aos="zoom-in">
                <i class="fas fa-shopping-bag"></i>
                <p>Your shopping bag has been quiet lately.</p>
                <a href="shop.php" style="color:var(--accent); text-decoration:underline;">Browse Menu</a>
            </div>
        <?php else: ?>
            <div class="status-grid">
                <?php foreach ($user_orders as $order): 
                    $order_time = strtotime($order['date']);
                    $is_recent = (time() - $order_time) < 86400; // Within 24 hours
                    
                    $dbStatus = strtolower($order['status'] ?? 'pending');
                    
                    // Logic: If Pending and > 24h, show as Completed. If Cancelled, stay Cancelled.
                    if ($dbStatus === 'cancelled') {
                        $orderStatus = 'cancelled';
                        $statusClass = 'status-cancelled';
                    } elseif ($dbStatus === 'pending' && !$is_recent) {
                        $orderStatus = 'completed';
                        $statusClass = 'status-confirmed'; // Keep color class but change text
                    } elseif ($dbStatus === 'confirmed' || $dbStatus === 'completed') {
                        $orderStatus = 'completed';
                        $statusClass = 'status-confirmed';
                    } else {
                        $orderStatus = 'pending';
                        $statusClass = 'status-pending';
                    }
                    ?>
                    <div class="activity-card" data-aos="fade-up">
                        <div class="card-header">
                            <span class="booking-id">Order #<?php echo $order['invoice_number'] ?? $order['id']; ?></span>
                            <span class="status-badge <?php echo $statusClass; ?>"><?php echo ucfirst($orderStatus); ?></span>
                        </div>
                        <div class="card-body">
                            <div class="date-display">
                                <span class="date-day"><?php echo date('d', strtotime($order['date'])); ?></span>
                                <span class="date-month"><?php echo date('M Y', strtotime($order['date'])); ?></span>
                            </div>
                            <div class="info-row">
                                <div class="info-icon"><i class="fas fa-truck-loading"></i></div>
                                <span><?php echo $order['title']; ?> (x<?php echo $order['quantity']; ?>)</span>
                            </div>
                            <div class="price-footer">
                                <span style="font-size:0.9rem; color:var(--text-muted, #6b5d52);">Total Amount</span>
                                <span class="total-price">₹<?php echo number_format($order['subtotal_amount'], 2); ?></span>
                            </div>

                            <?php 
                            $order_time = strtotime($order['date']);
                            $is_recent = (time() - $order_time) < 86400; // Within 24 hours
                            if ($orderStatus === 'pending' && $is_recent): ?>
                            <div class="card-actions">
                                <button onclick="confirmAction('CANCELLATION POLICY: Only 75% of your amount will be refunded. Are you sure you want to cancel this order?', 'status_action.php?action=delete_order&invoice=<?php echo $order['invoice_number']; ?>')" class="btn-action btn-cancel">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php include 'footer.php'; ?>