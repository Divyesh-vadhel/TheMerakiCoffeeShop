<?php
session_start();

// 1. TEMPORARILY SAVE THE CART DATA
$cart_data = [];
if (isset($_SESSION['cart'])) {
    $cart_data = $_SESSION['cart'];
}

// 2. DESTROY THE OLD SESSION (Removes user_id, cart, etc.)
session_unset();
session_destroy();

// 3. START A NEW SESSION
session_start();

// 4. RESTORE ONLY THE CART DATA
if (!empty($cart_data)) {
    $_SESSION['cart'] = $cart_data;
}

header('Location: login.php');
exit;