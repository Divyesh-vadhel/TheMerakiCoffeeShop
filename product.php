<?php
// --- BEGIN ERROR REPORTING (Added for troubleshooting) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
// --- END ERROR REPORTING ---

session_start();
define('SUPPRESS_DB_ERROR', true);
include 'db_connect.php';

$product = null;
$dbError = null;

// 1. Check for valid ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: shop.php');
    exit();
}

$product_id = intval($_GET['id']);

if ($conn->connect_errno) {
    $dbError = 'Database connection failed: ' . $conn->connect_error;
} else {
    // 2. Fetch the specific product data
    $sql_query = "SELECT id, name, price, old_price, description, image FROM coffees WHERE id = " . $product_id;

    $result = $conn->query($sql_query);

    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $result->free();
    } else {
        $dbError = "Product with ID #{$product_id} not found or SQL Error: " . $conn->error;
    }
}

// Prepare variables for display
$item = $product;
$imgPath = $item ? 'images/' . htmlspecialchars($item['image'] ?? '') : '';
$oldPrice = $item && isset($item['old_price']) && is_numeric($item['old_price']) && $item['old_price'] > $item['price'] ? (float) $item['old_price'] : 0;

$pageTitle = ($item ? htmlspecialchars($item['name'] ?? '') . ' | ' : 'Product Not Found | ') . 'Meraki Coffee House';
$extraCss = '<style>
        /* PRODUCT PAGE STYLES */
        main {
            padding: 120px 0 60px; /* Adjusted for fixed header */
        }

        .product-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            background: var(--bg-card); /* Fallback */
            background-color: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .product-image-container {
            height: 400px;
            overflow: hidden;
            border-radius: 12px;
            background: transparent;
            padding: 20px;
        }

        .product-image-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .product-details {
            padding: 20px 0;
        }

        .product-title {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: var(--primary);
            font-family: \'Playfair Display\', serif; /* Or Cormorant if enforcing standard */
        }

        .product-description {
            font-size: 1rem;
            color: var(--text-main);
            line-height: 1.6;
            margin-bottom: 30px;
            border-top: 1px solid var(--border);
            padding-top: 20px;
        }

        /* PRICE STYLES */
        .price-wrapper {
            display: flex;
            align-items: baseline;
            margin-bottom: 30px;
            gap: 15px;
        }

        .current-price {
            color: var(--accent);
            font-weight: 700;
            font-size: 2.2rem;
        }

        .old-price {
            color: var(--text-muted); /* Fallback */
            color: #9d8f85;
            font-weight: 400;
            font-size: 1.2rem;
            text-decoration: line-through;
        }

        .btn-add-to-cart {
            display: block;
            width: fit-content; /* Size to content */
            margin: 30px auto 0; /* Center it */
            padding: 12px 35px; /* Smaller padding */
            font-size: 0.85rem; /* Smaller font */
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            transition: 0.3s;
            background: var(--accent);
            color: white;
            border-radius: 50px; /* More rounded */
        }
 
        .btn-add-to-cart:hover {
            background: var(--primary);
            transform: translateY(-2px);
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--accent);
            font-weight: 500;
        }

        @media (max-width: 992px) {
            .product-content {
                grid-template-columns: 1fr;
            }

            .product-details {
                padding: 0;
            }
        }
    </style>';
include 'header.php';
?>

    <main class="container">
        <a href="shop.php" class="back-link">← Back to Menu</a>

        <?php if ($dbError || !$item): ?>
            <div
                style="background:#fee2e2; color:#b91c1c; padding:20px; border-radius:8px; text-align:center; border: 1px solid #fca5a5;">
                <h2>Error</h2>
                <p><?php echo htmlspecialchars(($dbError ?? '') ?: 'The requested product could not be loaded.'); ?></p>
            </div>
        <?php else: ?>
            <?php if (isset($_GET['limit'])): ?>
                <div data-aos="fade-down"
                    style="background:#fee2e2; color:#b91c1c; padding:15px; border-radius:8px; margin-bottom:40px; text-align:center; border: 1px solid #fca5a5;">
                    <i class="fas fa-exclamation-triangle" style="margin-right:8px;"></i>
                    Maximum quantity of 20 reached for this item.
                </div>
            <?php endif; ?>
            <div class="product-content" data-aos="fade-up">
                <div class="product-image-container">
                    <img src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($item['name'] ?? ''); ?>">
                </div>
                <div class="product-details">
                    <h1 class="product-title"><?php echo htmlspecialchars($item['name'] ?? ''); ?></h1>

                    <div class="price-wrapper">
                        <?php if ($oldPrice > 0): ?>
                            <span class="old-price">₹<?php echo number_format($oldPrice, 2); ?></span>
                            <span class="current-price">₹<?php echo number_format($item['price'], 2); ?></span>
                        <?php else: ?>
                            <span class="current-price">₹<?php echo number_format($item['price'], 2); ?></span>
                        <?php endif; ?>
                    </div>

                    <p class="product-description">
                        <?php echo nl2br(htmlspecialchars($item['description'] ?? '')); ?>
                    </p>

                    <button class="btn-add-to-cart"
                        onclick="window.location.href='cart.php?action=add&id=<?php echo $item['id']; ?>&return=product&rid=<?php echo $item['id']; ?>'">
                        Add to Cart
                    </button>

                </div>
            </div>
        <?php endif; ?>
    </main>

<?php include 'footer.php'; ?>
