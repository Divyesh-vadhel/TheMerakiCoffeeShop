<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($action === 'cancel_booking' && isset($_GET['id'])) {
    $booking_id = (int)$_GET['id'];
    
    // Fetch fee and time first
    $check_sql = "SELECT booking_fee, date, time FROM reservations WHERE id = ? AND user_id = ?";
    $f_stmt = $conn->prepare($check_sql);
    $f_stmt->bind_param("ii", $booking_id, $uid);
    $f_stmt->execute();
    $b_data = $f_stmt->get_result()->fetch_assoc();

    if (!$b_data) {
        $_SESSION['flash_error'] = "Booking not found.";
        header("Location: status.php");
        exit();
    }

    $booking_time = strtotime($b_data['date'] . ' ' . $b_data['time']);
    if ($booking_time <= time()) {
        $_SESSION['flash_error'] = "You can only cancel future bookings.";
        header("Location: status.php");
        exit();
    }

    $refund_amount = $b_data['booking_fee'] * 0.75;

    // Ensure the booking belongs to the current user
    $stmt = $conn->prepare("UPDATE reservations SET status = 'Cancelled' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $uid);
    
    if ($stmt->execute()) {
        $_SESSION['flash_msg'] = "Booking cancelled successfully. A 75% refund (₹" . number_format($refund_amount, 2) . ") has been initiated.";
    } else {
        $_SESSION['flash_error'] = "Failed to cancel booking.";
    }
}

if ($action === 'delete_order' && isset($_GET['invoice'])) {
    $invoice = $_GET['invoice'];
    
    // Fetch order details first
    $check_sql = "SELECT subtotal_amount, date, status FROM orders WHERE invoice_number = ? AND user_id = ?";
    $f_stmt = $conn->prepare($check_sql);
    $f_stmt->bind_param("si", $invoice, $uid);
    $f_stmt->execute();
    $o_data = $f_stmt->get_result()->fetch_assoc();

    if (!$o_data) {
        $_SESSION['flash_error'] = "Order not found.";
        header("Location: status.php");
        exit();
    }

    $order_time = strtotime($o_data['date']);
    if (($o_data['status'] !== 'Pending' && $o_data['status'] !== 'pending') || (time() - $order_time) > 86400) {
        $_SESSION['flash_error'] = "This order can no longer be cancelled (only Pending orders within 24h).";
        header("Location: status.php");
        exit();
    }

    $refund_amount = $o_data['subtotal_amount'] * 0.75;

    // Ensure the order belongs to the current user
    // We update status to 'Cancelled' instead of physical delete
    $stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE invoice_number = ? AND user_id = ?");
    $stmt->bind_param("si", $invoice, $uid);
    
    if ($stmt->execute()) {
        $_SESSION['flash_msg'] = "Order cancelled successfully. A 75% refund (₹" . number_format($refund_amount, 2) . ") has been initiated.";
    } else {
        $_SESSION['flash_error'] = "Failed to cancel order.";
    }
}

header("Location: status.php");
exit();
