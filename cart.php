<?php
session_start();
define('SUPPRESS_DB_ERROR', true);
include 'db_connect.php';
if ($conn->connect_errno)
    $dbError = 'Connection failed.';
else
    $dbError = null;
if (!isset($_SESSION['cart']))
    $_SESSION['cart'] = [];

function get_cart_count()
{
    $c = 0;
    foreach ($_SESSION['cart'] as $i)
        $c += (int) ($i['qty'] ?? 0);
    return $c;
}
$action = $_GET['action'] ?? null;
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$return = $_GET['return'] ?? '';

// LOGOUT PERSISTENCE FIX: This logic should ideally be in logout.php, but it's fine here too.
// The current logic only redirects unauthenticated users. The fix for clearing the cart
// on logout was addressed in logout.php previously.
if ($action === 'add' && $id > 0 && !isset($_SESSION['user_id'])) {
    if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
        echo json_encode(['success' => false, 'redirect' => "login.php?redirect=" . urlencode("cart.php?action=add&id=$id&return=$return")]);
        exit;
    }
    header('Location: login.php?redirect=' . urlencode("cart.php?action=add&id=$id&return=$return"));
    exit;
}

if ($conn && !$conn->connect_errno && $id > 0 && in_array($action, ['add', 'inc', 'dec'], true) && isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT id,name,price,image FROM coffees WHERE id=?");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (!isset($_SESSION['cart'][$id]))
                $_SESSION['cart'][$id] = ['id' => $row['id'], 'name' => $row['name'], 'price' => (float) $row['price'], 'image' => $row['image'], 'qty' => 0];
            if ($action === 'add' || $action === 'inc') {
                if ($_SESSION['cart'][$id]['qty'] < 20) {
                    $_SESSION['cart'][$id]['qty']++;
                } else {
                    $limitReached = true;
                }
            } elseif ($action === 'dec') {
                $_SESSION['cart'][$id]['qty']--;
                if ($_SESSION['cart'][$id]['qty'] <= 0)
                    unset($_SESSION['cart'][$id]);
            }
        }
        $stmt->close();
    }
    if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
        $cartSubtotal = 0.0;
        foreach ($_SESSION['cart'] as $i)
            $cartSubtotal += ($i['price'] ?? 0) * ($i['qty'] ?? 0);
        
        echo json_encode([
            'success' => true,
            'qty' => $_SESSION['cart'][$id]['qty'] ?? 0,
            'itemTotal' => isset($_SESSION['cart'][$id]) ? number_format($_SESSION['cart'][$id]['price'] * $_SESSION['cart'][$id]['qty'], 2) : 0,
            'cartSubtotal' => number_format($cartSubtotal, 2),
            'cartCount' => get_cart_count(),
            'limitReached' => $limitReached ?? false,
            'message' => ($limitReached ?? false) ? 'Maximum quantity of 20 reached for this item.' : ''
        ]);
        exit;
    }
    if ($action === 'add') {
        $suffix = ($limitReached ?? false) ? '&limit=1' : '&added=1';
        if ($return === 'shop') {
            header('Location: shop.php?' . $suffix);
            exit;
        } elseif ($return === 'product') {
            $rid = isset($_GET['rid']) ? (int)$_GET['rid'] : $id;
            header('Location: product.php?id=' . $rid . $suffix);
            exit;
        }
    }
} elseif ($action === 'remove' && $id > 0) {
    unset($_SESSION['cart'][$id]);
    if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
        $cartSubtotal = 0.0;
        foreach ($_SESSION['cart'] as $i)
            $cartSubtotal += ($i['price'] ?? 0) * ($i['qty'] ?? 0);
        echo json_encode([
            'success' => true,
            'cartSubtotal' => number_format($cartSubtotal, 2),
            'cartCount' => get_cart_count(),
            'isEmpty' => empty($_SESSION['cart'])
        ]);
        exit;
    }
} elseif ($action === 'clear')
    $_SESSION['cart'] = [];

$cartItems = $_SESSION['cart'];
$cartCount = get_cart_count();
$cartSubtotal = 0.0;
foreach ($cartItems as $i)
    $cartSubtotal += ($i['price'] ?? 0) * ($i['qty'] ?? 0);


$pageTitle = 'Cart | The Meraki Coffee House';
$extraCss = '<style>
        /* CART SPECIFIC */
        main.container {
            max-width: 1200px;
            padding-top: 120px;
        }

        .cart-grid {
            display: grid;
            grid-template-columns: 1.5fr 0.8fr;
            gap: 50px;
            margin-top: 30px;
            align-items: start;
        }

        .cart-list {
            margin-bottom: 30px;
        }

        .cart-item {
            display: flex;
            gap: 30px;
            background: #fff;
            padding: 25px;
            border: 1px solid #f0efed;
            border-radius: 12px;
            margin-bottom: 25px;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
        }

        .item-img {
            width: 70px;
            height: 70px;
            background: transparent;
            border-radius: 10px;
            object-fit: contain;
            padding: 5px;
        }

        .item-name {
            font-weight: 600;
            font-size: 1.05rem;
            /* font-family: \'Playfair Display\', serif; */ 
        }

        .qty-controls {
            display: flex;
            align-items: center;
            gap: 15px;
            background-color: #f7f5f2;
            padding: 8px 18px;
            border-radius: 50px;
        }

        .qty-btn {
            text-decoration: none;
            color: var(--primary);
            font-weight: bold;
            font-size: 1.1rem;
        }

        .summary-card {
            background-color: white;
            padding: 40px;
            border-radius: 20px;
            border: 1px solid #f0efed;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            height: fit-content;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee; /* var(--bg-soft) */
            padding-bottom: 15px;
        }

        .btn-checkout {
            background: var(--primary);
            color: white;
            display: block;
            text-align: center;
            padding: 15px;
            border-radius: 0;
            margin-top: 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.2s;
            border: none;
            width: 100%;
            cursor: pointer;
        }

        .btn-checkout:hover {
            background: var(--accent);
        }

        @media (max-width: 768px) {
            .cart-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ANIMATIONS */
        @keyframes pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.4); color: var(--accent); }
            100% { transform: scale(1); }
        }

        .animate-pop {
            animation: pop 0.3s ease-out;
        }

        @keyframes valueChange {
            0% { transform: translateY(5px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        .animate-value {
            display: inline-block;
            animation: valueChange 0.3s ease-out;
        }

        .cart-item {
            transition: transform 0.3s ease, opacity 0.3s ease, box-shadow 0.3s ease;
        }

        .cart-item:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
    </style>';
include 'header.php';
?>

<main class="container">
    <h1 style="margin-bottom: 30px;">Your Cart</h1>
    <div class="cart-grid">
        <div class="cart-list" data-aos="fade-right">
            <?php if ($cartCount === 0): ?>
                <p style="margin-bottom: 40px; font-size: 1.1rem;">Your cart is empty. <a href="shop.php"
                        style="color:var(--accent); font-weight:600;">Browse Menu</a>
                </p>
            <?php else: ?>
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" id="cart-item-<?php echo $item['id']; ?>">
                        <img src="images/<?php echo htmlspecialchars($item['image']); ?>" class="item-img" alt="">
                        <div style="flex-grow: 1;">
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div style="color:var(--accent); font-weight:600;">
                                ₹<?php echo number_format($item['price'], 2); ?></div>
                        </div>
                        <div class="qty-controls">
                            <a href="cart.php?action=dec&id=<?php echo $item['id']; ?>" class="qty-btn ajax-cart-btn">-</a>
                            <span class="qty-val" style="font-weight:600;"><?php echo $item['qty']; ?></span>
                            <a href="cart.php?action=inc&id=<?php echo $item['id']; ?>" class="qty-btn ajax-cart-btn">+</a>
                        </div>
                        <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>"
                            class="ajax-cart-remove"
                            style="color:#b91c1c; font-size:1.2rem;">&times;</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="summary-card" data-aos="fade-left">
            <h3 style="margin-bottom:20px;">Order Summary</h3>
            <div class="summary-row"><span>Subtotal</span>
                <span id="cart-subtotal">₹<?php echo number_format($cartSubtotal, 2); ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; font-weight:700; font-size:1.2rem;">
                <span>Total</span> <span id="cart-total">₹<?php echo number_format($cartSubtotal, 2); ?></span>
            </div>
            <?php if ($cartCount > 0): ?>
                <button class="btn-checkout" onclick="window.location.href='checkout.php'">Proceed to Checkout</button>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('ajax-cart-btn') || e.target.classList.contains('ajax-cart-remove')) {
            e.preventDefault();
            const url = e.target.href + '&ajax=1';
            const isRemove = e.target.classList.contains('ajax-cart-remove');

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.limitReached) {
                            alert(data.message);
                        } else if (isRemove || data.qty <= 0) {
                            const item = e.target.closest('.cart-item');
                            item.style.opacity = '0';
                            setTimeout(() => {
                                item.remove();
                                if (data.isEmpty || data.cartCount === 0) {
                                    location.reload(); // Show empty cart message
                                }
                            }, 300);
                        } else {
                            const item = e.target.closest('.cart-item');
                            const qtyVal = item.querySelector('.qty-val');
                            qtyVal.innerText = data.qty;
                            
                            // Trigger pop animation
                            qtyVal.classList.remove('animate-pop');
                            void qtyVal.offsetWidth; // trigger reflow
                            qtyVal.classList.add('animate-pop');
                        }

                        // Update summary with value change animation
                        const subtotalEl = document.getElementById('cart-subtotal');
                        const totalEl = document.getElementById('cart-total');
                        
                        [subtotalEl, totalEl].forEach(el => {
                            el.innerHTML = `<span class="animate-value">₹${data.cartSubtotal}</span>`;
                        });
                    }
                })
                .catch(err => console.error('Error updating cart:', err));
        }
    });
</script>

<?php include 'footer.php'; ?>