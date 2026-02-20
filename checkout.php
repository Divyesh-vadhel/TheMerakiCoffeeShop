<?php
session_start();
define('SUPPRESS_DB_ERROR', true);
include 'db_connect.php';

// Enable mysqli to throw exceptions on errors so we can catch them and rollback safely
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// Error Handling
$dbError = $conn->connect_errno ? 'Database connection failed.' : null;

// Initialize Cart
if (!isset($_SESSION['cart']))
    $_SESSION['cart'] = [];
$cartItems = $_SESSION['cart'];

// Fetch User Data for Auto-fill
$user_data = null;
$userReservations = [];
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $u_res = $conn->query("SELECT username, email, phone FROM users WHERE id = $uid");
    if ($u_res && $u_res->num_rows > 0) {
        $user_data = $u_res->fetch_assoc();
    }

    // Fetch reservations for today/future that haven't been completed yet
    $today = date('Y-m-d');
    $res_query = $conn->query("SELECT r.id, r.table_id, r.date, r.time, r.booking_fee, r.duration_mins, t.table_number 
                               FROM reservations r 
                               JOIN tables t ON r.table_id = t.id 
                               WHERE r.user_id = $uid 
                               AND r.status = 'Confirmed' 
                               AND r.date >= '$today'
                               ORDER BY r.id DESC");
    if ($res_query) {
        while ($row = $res_query->fetch_assoc()) {
            $userReservations[] = $row;
        }
    }
}



// Initialize Print/Display Variables
$printItems = $cartItems;
$cartCount = 0;
$cartSubtotal = 0.0;
$printName = '';
$printEmail = '';
$printPhone = '';
$printAddress = '';
$printSubtotal = 0.0;

// Calculate Totals
foreach ($cartItems as $item) {
    $qty = (int) ($item['qty'] ?? 0);
    $price = (float) ($item['price'] ?? 0);
    $cartCount += $qty;
    $cartSubtotal += $qty * $price;
}
$printSubtotal = $cartSubtotal;

$successMessage = '';
$errorMessage = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = trim($_POST['customer_name'] ?? '');
    $customerEmail = trim($_POST['customer_email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($cartCount === 0) {
        $errorMessage = 'Your cart is empty.';
    } elseif (empty($customerName) || empty($customerEmail) || empty($phone)) {
        $errorMessage = 'Please fill in all required fields.';
    } elseif ($conn && !$conn->connect_errno) {
        $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
        $today = date('Y-m-d');
        $invoiceBase = 'INV-' . substr(uniqid('', true), -12);
        $status = 'Pending'; // Default status set back to Pending

        // Combine details for the address field as per your requirements
        $serviceType = $_POST['service_type'] ?? 'Takeaway';
        $tableInfo = "";
        if ($serviceType === 'Dining' && !empty($_POST['res_id'])) {
            $tableInfo = "\nReservation/Table: " . $_POST['res_id'];
        }
        $storedAddress = "Service: $serviceType$tableInfo\nName: $customerName\nEmail: $customerEmail";

        $conn->begin_transaction();
        try {
            // Prepared statement matching your database columns (Status is set to Pending)
            $stmt = $conn->prepare("INSERT INTO orders (price, title, quantity, subtotal_amount, date, invoice_number, user_id, address, phone, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt === false) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }

            $insertedCount = 0;
            file_put_contents('debug_order.log', "Starting Loop. Count: " . count($cartItems) . "\n", FILE_APPEND);

            foreach ($cartItems as $item) {
                file_put_contents('debug_order.log', "Processing Item: " . print_r($item, true) . "\n", FILE_APPEND);

                if (!isset($item['qty']) || $item['qty'] <= 0) {
                    file_put_contents('debug_order.log', "Skipping item due to invalid qty.\n", FILE_APPEND);
                    continue;
                }

                $title = $item['name'];
                $price = (float) $item['price'];
                $qty = (int) $item['qty'];
                $sub = $price * $qty;

                // Bind status as 'Pending'
                if (!$stmt->bind_param('dsidssisss', $price, $title, $qty, $sub, $today, $invoiceBase, $userId, $storedAddress, $phone, $status)) {
                    throw new Exception('Bind failed: ' . $stmt->error);
                }
                if (!$stmt->execute()) {
                    throw new Exception('Execute failed: ' . $stmt->error);
                }
                $insertedCount++;
                file_put_contents('debug_order.log', "Item inserted successfully.\n", FILE_APPEND);
            }

            // CHECK FOR RESERVATION FEE - MERGE INTO ORDER
            $resFee = 0.0;
            $resDetails = "";
            if ($serviceType === 'Dining' && !empty($_POST['res_id'])) {
                $res_id = (int)$_POST['res_id'];
                // Fetch reservation details from DB for security/accuracy
                $res_data_query = $conn->query("SELECT r.*, t.table_number FROM reservations r JOIN tables t ON r.table_id = t.id WHERE r.id = $res_id AND r.user_id = $userId");
                if ($res_data_query && $res_data_query->num_rows > 0) {
                    $r = $res_data_query->fetch_assoc();
                    $resFee = (float)$r['booking_fee'];
                    $timeFormatted = date('g:i A', strtotime($r['time']));
                    $resDetails = "Table #{$r['table_number']} Stay ({$timeFormatted} for {$r['duration_mins']} mins)";
                }
            }

            if ($resFee > 0) {
                    $qty = 1;
                    if (!$stmt->bind_param('dsidssiss', $resFee, $resDetails, $qty, $resFee, $today, $invoiceBase, $userId, $storedAddress, $phone)) {
                        throw new Exception('Bind failed (Res Fee): ' . $stmt->error);
                    }
                    if (!$stmt->execute()) {
                        throw new Exception('Execute failed (Res Fee): ' . $stmt->error);
                    }
                    // Update print subtotal to include fee
                    $cartSubtotal += $resFee;
                    
                    // Add to print items for the receipt list
                    $cartItems[] = [
                        'name' => $resDetails,
                        'price' => $resFee,
                        'qty' => 1
                    ];
                    // Mark as Completed so it's removed from future selection
                    $conn->query("UPDATE reservations SET status = 'Completed' WHERE id = $res_id");
                }
            
            $stmt->close();

            if ($insertedCount === 0 && $resFee === 0) {
                file_put_contents('debug_order.log', "WARNING: No items were inserted.\n", FILE_APPEND);
            }

            $conn->commit();
            file_put_contents('debug_order.log', "Transaction Committed. Inserted Count: $insertedCount\n", FILE_APPEND);

            // Set variables for Receipt
            $printName = $customerName;
            $printEmail = $customerEmail;
            $printPhone = $phone;
            $printItems = $cartItems;
            $printSubtotal = $cartSubtotal;

            // Clear Cart after success
            $_SESSION['cart'] = [];
            $cartItems = [];
            $cartCount = 0;
            $cartSubtotal = 0.0;

            $successMessage = 'Thank you for brewing with us!';
        } catch (mysqli_sql_exception $e) {
            // Database exception (thrown because mysqli_report is enabled)
            $conn->rollback();
            error_log('Order DB error: ' . $e->getMessage());
            $errorMessage = 'Order failed due to a database error. Please try again later.';
        } catch (Exception $e) {
            // Generic exception
            $conn->rollback();
            error_log('Order error: ' . $e->getMessage());
            $errorMessage = 'Order failed. Please try again.';
        }
    }
}

$pageTitle = 'Checkout | Meraki Coffee House';
$extraCss = "<style>
        /* CHECKOUT SPECIFIC */
        .cart-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: var(--accent);
            color: white;
            font-size: 10px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: grid;
            place-items: center;
        }

        .page-content {
            padding: 120px 0 60px; /* Adjusted for fixed header */
            flex: 1;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 0.8fr;
            gap: 40px;
        }

        .card {
            background: var(--bg-card); /* Fallback */
            background-color: white;
            padding: 40px;
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary);
            display: block;
            margin-top: 15px;
        }

        input,
        textarea {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg-soft); /* Fallback */
            background-color: #fcfcfc;
            font-family: inherit;
        }

        .btn-pay {
            width: 100%;
            background: var(--primary);
            color: white;
            padding: 15px;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 20px;
        }

        .btn-pay.btn-book-mode {
            background: var(--accent);
            letter-spacing: 2px;
            animation: pulse-gold 2s infinite;
        }

        @keyframes pulse-gold {
            0% { box-shadow: 0 0 0 0 rgba(212, 163, 115, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(212, 163, 115, 0); }
            100% { box-shadow: 0 0 0 0 rgba(212, 163, 115, 0); }
        }

        .summary-res-animate {
            animation: slideInSummary 0.5s ease-out forwards;
        }

        @keyframes slideInSummary {
            from { transform: translateX(20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* DINING OPTIONS */
        .option-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }

        .option-btn {
            padding: 15px;
            border: 2px solid var(--border);
            border-radius: 12px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            background: #fcfcfc;
        }

        .option-btn.active {
            border-color: var(--accent);
            background: rgba(212, 163, 115, 0.05);
            color: var(--accent);
        }

        .option-btn i {
            display: block;
            font-size: 1.5rem;
            margin-bottom: 8px;
        }

        #reservationSelect {
            display: none;
            margin-top: 15px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            header,
            footer,
            .page-content {
                display: none !important;
            }

            #printArea {
                display: block !important;
                width: 210mm;
                margin: 0;
                padding: 0;
            }
        }

        #printArea {
            display: none;
            padding: 40px;
            background: white;
            color: black;
            font-family: 'Inter', sans-serif;
        }

        .receipt-card {
            background: #fff;
            border-radius: 24px;
            padding: 50px;
            box-shadow: 0 25px 70px rgba(0,0,0,0.07);
            border: 1px solid rgba(212, 163, 115, 0.2);
            text-align: left;
            max-width: 650px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        .receipt-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 10px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid #f0f0f0;
        }

        .receipt-brand h1 {
            font-family: 'Italiana', serif;
            font-size: 2.8rem;
            color: var(--primary);
            margin: 0;
            line-height: 1;
        }

        .receipt-brand p {
            font-size: 0.75rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--accent);
            margin: 5px 0 0;
            font-weight: 700;
        }

        .receipt-meta {
            text-align: right;
        }

        .receipt-id {
            display: inline-block;
            background: #fff;
            color: #000;
            padding: 6px 16px;
            border-radius: 100px;
            font-weight: 700;
            font-size: 0.85rem;
            border: 2px solid #000;
            margin-bottom: 8px;
        }

        .receipt-date {
            font-size: 0.85rem;
            color: #000;
            font-weight: 500;
        }

        .detail-section {
            margin-bottom: 35px;
        }

        .detail-title {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #000;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #f0f0f0;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .detail-item label {
            display: block;
            font-size: 0.7rem;
            color: #000;
            margin: 0 0 4px;
            text-transform: uppercase;
        }

        .detail-item span {
            font-weight: 600;
            color: #000;
            font-size: 0.95rem;
        }

        .receipt-table {
            width: 100%;
            margin: 30px 0;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .receipt-table th {
            text-align: left;
            color: #000;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            padding-bottom: 15px;
            border-bottom: 2px solid #fafafa;
        }

        .receipt-table td {
            padding: 20px 0;
            border-bottom: 1px solid #f9f9f9;
            vertical-align: middle;
        }

        .item-name {
            font-weight: 700;
            color: #000;
            font-size: 1rem;
            margin-bottom: 3px;
        }

        .item-price {
            font-size: 0.8rem;
            color: #000;
        }

        .grand-total-box {
            background: #1a1a1a;
            color: white;
            padding: 30px;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 40px;
            position: relative;
            overflow: hidden;
        }

        .grand-total-box::after {
            content: 'MERAKI';
            position: absolute;
            right: -20px;
            bottom: -10px;
            font-size: 5rem;
            font-family: 'Italiana';
            opacity: 0.05;
            pointer-events: none;
        }

        .total-label {
            display: flex;
            flex-direction: column;
        }

        .total-label span:first-child {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            opacity: 0.6;
            margin-bottom: 5px;
        }

        .total-amount {
            font-size: 2.2rem;
            font-weight: 800;
            color: #fff;
            font-family: 'Outfit';
        }

        @media print {
            header, footer, .page-content { display: none !important; }
            body { background: white !important; margin: 0 !important; }
            #printArea {
                display: block !important;
                padding: 0 !important;
            }
            .print-receipt-container {
                max-width: 800px;
                margin: 0 auto;
                padding: 60px;
                background: white;
            }
        }
        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>";
include 'header.php';
?>

    <main class="container page-content">
        <div class="grid">
            <div data-aos="fade-right">
                <h2 style="font-family:'Playfair Display'; margin-bottom:20px;">Shipping Details</h2>
                <?php if ($successMessage): ?>
                    <div style="background:#d1fae5; color:#065f46; padding:15px; margin-bottom:20px; border-radius:8px;">
                        <?php echo $successMessage; ?>
                    </div>
                <?php endif; ?>
                <?php if ($errorMessage): ?>
                    <div style="background:#fee2e2; color:#b91c1c; padding:15px; margin-bottom:20px; border-radius:8px;">
                        <?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['res_success'])): ?>
                    <div style="background:#d1fae5; color:#065f46; padding:15px; margin-bottom:20px; border-radius:8px;">
                        <i class="fas fa-check-circle"></i> Table reserved successfully! Please select it below to complete your order.
                    </div>
                <?php endif; ?>

                <?php if (!$successMessage): ?>
                    <form method="post" class="card" id="checkoutForm">
                        <h4 style="margin-bottom:15px; color:var(--primary);">Dining Option</h4>
                        <div class="option-group">
                            <div class="option-btn <?php echo isset($_GET['res_success']) ? '' : 'active'; ?>" onclick="setService('Takeaway', this)">
                                <i class="fas fa-shopping-bag"></i>
                                <strong>Takeaway / Home</strong>
                            </div>
                            <div class="option-btn <?php echo isset($_GET['res_success']) ? 'active' : ''; ?>" onclick="setService('Dining', this)">
                                <i class="fas fa-chair"></i>
                                <strong>At My Table</strong>
                            </div>
                        </div>
                        <input type="hidden" name="service_type" id="service_type" value="<?php echo isset($_GET['res_success']) ? 'Dining' : 'Takeaway'; ?>">

                        <?php 
                        $resFeeValue = 0;
                        $resDetailsText = "";
                        $passed_id = (isset($_GET['res_success']) && is_numeric($_GET['res_success'])) ? (int)$_GET['res_success'] : 0;
                        $selected_res_id = 0;

                        if (!empty($userReservations)) {
                            $target = $userReservations[0];
                            if ($passed_id > 0) {
                                foreach ($userReservations as $r) {
                                    if ($r['id'] == $passed_id) { $target = $r; break; }
                                }
                            }
                            $selected_res_id = $target['id'];
                            $resFeeValue = (float)$target['booking_fee'];
                            $timeFmt = date('g:i A', strtotime($target['time']));
                            $resDetailsText = "Table #{$target['table_number']} Stay ({$timeFmt} for {$target['duration_mins']} mins)";
                        }
                        ?>
                        <?php if ($selected_res_id > 0): ?>
                            <input type="hidden" name="res_id" id="res_id_hidden" value="<?php echo $selected_res_id; ?>">
                        <?php endif; ?>



                        <h4 style="margin: 25px 0 15px; color:var(--primary);">Customer Details</h4>
                        <label>Full Name</label>
                        <input type="text" name="customer_name" required value="<?php echo $user_data ? htmlspecialchars($user_data['username']) : ''; ?>">
                        
                        <label>Email Address</label>
                        <input type="email" name="customer_email" required value="<?php echo $user_data ? htmlspecialchars($user_data['email']) : ''; ?>">
                        
                        <label>Phone Number</label>
                        <input type="tel" name="phone" required value="<?php echo $user_data ? htmlspecialchars($user_data['phone']) : ''; ?>">
                        
                        <button type="submit" class="btn-pay" id="submitBtn">Place Order</button>
                    </form>

                    <script>
                        const cartSubtotal = <?php echo $cartSubtotal; ?>;
                        const hasReservations = <?php echo empty($userReservations) ? 'false' : 'true'; ?>;

                        function setService(type, el) {
                            document.getElementById('service_type').value = type;
                            document.querySelectorAll('.option-btn').forEach(btn => btn.classList.remove('active'));
                            el.classList.add('active');
                            updateTotal();
                        }


                        const reservationFee = <?php echo $resFeeValue; ?>;
                        const reservationDetails = "<?php echo $resDetailsText; ?>";
                        function updateTotal() {
                            let total = cartSubtotal;
                            const serviceType = document.getElementById('service_type').value;
                            const submitBtn = document.getElementById('submitBtn');
                            
                            let resFee = 0;
                            let resSelected = false;

                            // 1. Calculate Reservation Fee if applicable
                            if (serviceType === 'Dining' && reservationFee > 0) {
                                resFee = reservationFee;
                                total += resFee;
                                resSelected = true;
                            }

                            // 2. Button Logic (Cart MUST NOT be empty)
                            if (cartSubtotal === 0) {
                                submitBtn.textContent = 'Select Item Coffee';
                                submitBtn.type = 'button';
                                submitBtn.classList.add('btn-book-mode');
                                submitBtn.onclick = () => window.location.href = 'shop.php';
                            } else if (serviceType === 'Dining') {
                                if (!resSelected) {
                                    submitBtn.textContent = 'Book a Table First';
                                    submitBtn.type = 'button';
                                    submitBtn.classList.add('btn-book-mode');
                                    submitBtn.onclick = () => window.location.href = 'table.php';
                                } else {
                                    submitBtn.textContent = 'Place Order';
                                    submitBtn.type = 'submit';
                                    submitBtn.classList.remove('btn-book-mode');
                                    submitBtn.onclick = null;
                                }
                            } else {
                                // Takeaway mode with items
                                submitBtn.textContent = 'Place Order';
                                submitBtn.type = 'submit';
                                submitBtn.classList.remove('btn-book-mode');
                                submitBtn.onclick = null;
                            }

                            // Update Display Totals
                            const totalDisplays = document.querySelectorAll('.summary-total-val');
                            totalDisplays.forEach(el => {
                                el.textContent = '₹' + total.toLocaleString('en-IN', {minimumFractionDigits: 2});
                            });

                            // Update Summary Label
                            const optRow = document.getElementById('summary-option-row');
                            const optLabel = document.getElementById('summary-option-label');
                            const optValue = document.getElementById('summary-option-value');

                            if (optRow && optLabel && optValue) {
                                if (serviceType === 'Dining' && resFee > 0) {
                                    optLabel.textContent = reservationDetails;
                                    optValue.textContent = '₹' + resFee.toFixed(2);
                                } else {
                                    optLabel.textContent = "Takeaway / Home";
                                    optValue.textContent = "Free";
                                }
                            }
                        }

                        function clearResSelection() {
                            const select = document.getElementById('res_id_select');
                            if (select) {
                                select.value = "";
                                updateTotal();
                            }
                        }

                        function clearResSelection() {
                            const select = document.getElementById('res_id_select');
                            if (select) {
                                select.value = "";
                                updateTotal();
                            }
                        }
                        
                        // Initialize totals on load
                        window.addEventListener('DOMContentLoaded', updateTotal);
                    </script>
                <?php else: ?>
                    <div class="receipt-card animate__animated animate__fadeInUp">
                        <div class="receipt-header">
                            <div class="receipt-brand">
                                <h1>Meraki.</h1>
                                <p>Coffee House</p>
                            </div>
                            <div class="receipt-meta">
                                <div class="receipt-id"><?php echo $invoiceBase; ?></div>
                                <div class="receipt-date"><?php echo date('d M, Y'); ?></div>
                            </div>
                        </div>

                        <div class="detail-section">
                            <div class="detail-title">Customer Details</div>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <label>Recipient Name</label>
                                    <span><?php echo htmlspecialchars($printName); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Phone Contact</label>
                                    <span><?php echo htmlspecialchars($printPhone); ?></span>
                                </div>
                                <div class="detail-item" style="grid-column: span 2;">
                                    <label>Email Address</label>
                                    <span><?php echo htmlspecialchars($printEmail); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="detail-section">
                            <div class="detail-title">Order Summary</div>
                            <table class="receipt-table">
                                <thead>
                                    <tr>
                                        <th>Item Description</th>
                                        <th style="text-align:center;">Qty</th>
                                        <th style="text-align:right;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($printItems as $item): 
                                        $isRes = (strpos($item['name'], 'Table #') !== false);
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="item-name" style="color: #000;">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </div>
                                                <div class="item-price">
                                                    ₹<?php echo number_format($item['price'], 2); ?> 
                                                    <?php echo $isRes ? 'booking fee' : 'per unit'; ?>
                                                </div>
                                            </td>
                                            <td style="text-align:center; font-weight:600; color:#000;"><?php echo $item['qty']; ?></td>
                                            <td style="text-align:right; font-weight:700; color:#000;">₹<?php echo number_format($item['price'] * $item['qty'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="grand-total-box">
                            <div class="total-label">
                                <span>Grand Total</span>
                                <div style="width: 40px; height: 2px; background: var(--accent); margin-bottom: 5px;"></div>
                            </div>
                            <div class="total-amount">₹<?php echo number_format($printSubtotal, 2); ?></div>
                        </div>

                        <div style="margin-top:40px; text-align:center; padding-top:30px; border-top:1px dashed #eee;">
                            <p style="margin:0; font-size:1.4rem; color:var(--primary); font-family:'Italiana', serif; font-weight:700; letter-spacing:1px;">Thank You!</p>
                            <p style="margin:8px 0 0; font-size:0.8rem; color:#000; text-transform:uppercase; letter-spacing:2px;">We hope to see you again soon</p>
                            
                            <div style="margin-top:25px; display:flex; justify-content:center; gap:20px; opacity:0.3;">
                                <i class="fas fa-coffee"></i>
                                <i class="fas fa-heart"></i>
                                <i class="fas fa-seedling"></i>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div data-aos="fade-left">
                <h2 style="font-family:'Playfair Display'; margin-bottom:20px;">Order Summary</h2>
                <div class="card">
                        <div style="display:flex; flex-direction:column; gap:0;">
                            <div id="summary-items-container">
                                <?php 
                                $displayItems = !empty($cartItems) ? $cartItems : $printItems;
                                if (empty($displayItems)): ?>
                                    <div id="empty-cart-msg" style="display:flex; justify-content:space-between; align-items:center; padding:20px 0; border-bottom:1px solid #f0f0f0;">
                                        <div style="font-weight:700; font-size:1.05rem; color:#888;">No Items Selected</div>
                                        <div style="font-weight:700; color:#888; font-size:1.1rem;">₹0.00</div>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($displayItems as $item): 
                                        $isRes = (strpos($item['name'], 'Table #') !== false);
                                    ?>
                                        <div style="display:flex; justify-content:space-between; align-items:center; padding:20px 0; border-bottom:1px solid #f0f0f0;">
                                            <div>
                                                <div style="font-weight:700; font-size:1.05rem; color:var(--primary); margin-bottom:4px;">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </div>
                                                <?php if(!$isRes): ?>
                                                    <div style="font-size:0.85rem; color:#888;">Qty: <?php echo $item['qty']; ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div style="font-weight:700; color:var(--primary); font-size:1.1rem;">
                                                ₹<?php echo number_format($item['price'] * $item['qty'], 2); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div id="summary-option-row" style="display:flex; justify-content:space-between; align-items:center; padding:20px 0; border-bottom:1px solid #f0f0f0; color:var(--primary);">
                                <div>
                                    <div id="summary-option-label" style="font-weight:700; font-size:1.05rem;">Service Option</div>
                                </div>
                                <div id="summary-option-value" style="font-weight:700; font-size:1.1rem;">₹0.00</div>
                            </div>
                            
                            <div style="display:flex; justify-content:space-between; align-items:center; padding-top:25px; margin-top:5px;">
                                <span style="font-weight:800; font-size:1.4rem; color:var(--primary); font-family:'Playfair Display', serif;">Total</span>
                                <span class="summary-total-val" style="font-weight:800; font-size:1.5rem; color:var(--primary); font-family:'Outfit', sans-serif;">₹<?php echo number_format($printSubtotal, 2); ?></span>
                            </div>

                            <?php if ($successMessage): ?>
                                <button onclick="window.print()"
                                    style="width:100%; margin-top:30px; padding:18px; background:var(--accent); color:white; border:none; border-radius:10px; cursor:pointer; font-weight:700; text-transform:uppercase; letter-spacing:2px; font-family:'Outfit', sans-serif; transition: all 0.3s ease;">
                                    Print Receipt
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Hidden Print Area -->
    <?php if ($successMessage): ?>
        <div id="printArea">
            <div class="print-receipt-container" style="width: 210mm; background: #fff; margin: 0 auto; box-sizing: border-box;">
                <div style="padding: 40px; font-family:'Outfit', sans-serif; color:#000;">
                    
                    <!-- Premium Header -->
                    <div style="text-align:center; margin-bottom:40px;">
                        <h1 style="font-family:'Italiana', serif; font-size:3.5rem; margin:0; line-height:0.9; letter-spacing:-2px; text-transform:uppercase; color:#000;">MERAKI.</h1>
                        <p style="text-transform:uppercase; font-size:12px; letter-spacing:8px; margin:10px 0; color:#000; font-weight:700;">Coffee House & Roastery</p>
                        <div style="width:50px; height:2px; background:#000; margin:15px auto;"></div>
                    </div>

                    <!-- Receipt Info & Customer Details -->
                    <div style="display:flex; justify-content:space-between; margin-bottom:40px; border-top:2px solid #000; border-bottom:1px solid #eee; padding:25px 0;">
                        <div style="flex:1;">
                            <p style="font-size:14px; color:#000; text-transform:uppercase; font-weight:800; letter-spacing:2px; margin-bottom:10px;">Customer Details</p>
                            <p style="font-size:14px; font-weight:800; margin:0; color:#000;"><?php echo htmlspecialchars($printName); ?></p>
                            <p style="margin:5px 0; font-size:13px; color:#000; font-weight:500;"><?php echo htmlspecialchars($printPhone); ?></p>
                            <p style="margin:0; font-size:13px; color:#000; font-weight:500; font-style:italic; opacity:1;"><?php echo htmlspecialchars($printEmail); ?></p>
                        </div>
                        <div style="text-align:right; flex:1;">
                            <p style="font-size:14px; color:#000; text-transform:uppercase; font-weight:800; letter-spacing:2px; margin-bottom:10px;">Order Information</p>
                            <p style="font-size:14px; font-weight:800; margin:0; color:#000; text-transform:uppercase;"><?php echo $invoiceBase; ?></p>
                            <p style="margin:5px 0; font-size:13px; color:#000; font-weight:500;"><?php echo date('d F, Y'); ?></p>
                            <p style="margin:0; font-size:12px; color:#000; font-weight:700; text-transform:uppercase; letter-spacing:1px;">TIMESTAMP: <?php echo date('h:i A'); ?></p>
                        </div>
                    </div>

                    <!-- Itemized Order Table -->
                    <table style="width:100%; border-collapse:collapse; margin-bottom:40px;">
                        <thead>
                            <tr style="border-bottom:3px solid #000;">
                                <th style="padding:15px 0; text-align:left; text-transform:uppercase; font-size:14px; color:#000; letter-spacing:2px; font-weight:900;">Item / Description</th>
                                <th style="padding:15px 0; text-align:center; text-transform:uppercase; font-size:14px; color:#000; letter-spacing:2px; font-weight:900; width:60px;">Qty</th>
                                <th style="padding:15px 0; text-align:right; text-transform:uppercase; font-size:14px; color:#000; letter-spacing:2px; font-weight:900; width:140px;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($printItems as $item): 
                                $isRes = (strpos($item['name'], 'Table #') !== false);
                            ?>
                                <tr style="border-bottom:1px solid #f5f5f5;">
                                    <td style="padding:20px 0;">
                                        <div style="font-weight:800; font-size:14px; color:#000; margin-bottom:4px;"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div style="font-size:12px; color:#000; font-weight:600; letter-spacing:0.5px; text-transform:uppercase;">
                                            <?php if($isRes): ?>
                                                Type: Reservation • Fee: ₹<?php echo number_format($item['price'], 2); ?>
                                            <?php else: ?>
                                                Type: Item • Rate: ₹<?php echo number_format($item['price'], 2); ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="padding:20px 0; text-align:center; font-weight:800; font-size:14px; color:#000;"><?php echo $item['qty']; ?></td>
                                    <td style="padding:20px 0; text-align:right; font-weight:900; font-size:14px; color:#000;">₹<?php echo number_format($item['price'] * $item['qty'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Totals Section -->
                    <div style="display:flex; justify-content:flex-end;">
                        <div style="width:340px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:14px; color:#000; font-weight:600;">
                                <span style="text-transform:uppercase;">Subtotal</span>
                                <span style="font-weight:800; color:#000;">₹<?php echo number_format($printSubtotal, 2); ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:14px; color:#000; font-weight:600;">
                                <span style="text-transform:uppercase;">Tax (GST 0%)</span>
                                <span style="font-weight:800; color:#000;">₹0.00</span>
                            </div>
                            <div style="border-top:6px solid #000; margin-top:20px; padding-top:20px; display:flex; justify-content:space-between; align-items: baseline;">
                                <span style="font-family:'Italiana', serif; font-weight:950; font-size:16px; color:#000; letter-spacing:1px; text-transform:uppercase;">Grand Total</span>
                                <span style="font-weight:950; font-size:2.8rem; color:#000; letter-spacing:-2px;">₹<?php echo number_format($printSubtotal, 2); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Receipt Footer -->
                    <div style="margin-top:60px; text-align:center; border-top:2px dashed #eee; padding-top:40px;">
                        <p style="font-family:'Italiana', serif; font-size:2rem; margin:0 0 10px; color:#000; font-weight:800;">Thank You for Brewing!</p>
                        <p style="color:#000; font-size:12px; letter-spacing:4px; text-transform:uppercase; font-weight:800; margin:0;">Digital Receipt • Meraki Coffee House • Mumbai, IN</p>
                        <div style="margin-top:25px; display:flex; justify-content:center; gap:30px; color:#f8f8f8; font-size:2rem;">
                            <i class="fas fa-coffee"></i>
                            <i class="fas fa-leaf"></i>
                            <i class="fas fa-mug-hot"></i>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <script>
            // Automatically open print dialog on success
            window.onload = function() {
                // Short delay to ensure styles are loaded
                setTimeout(function() {
                    // window.print(); 
                }, 500);
            };
        </script>
    <?php endif; ?>

<?php include 'footer.php'; ?>
