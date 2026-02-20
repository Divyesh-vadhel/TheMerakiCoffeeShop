<?php
// --- BEGIN ERROR REPORTING ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
// --- END ERROR REPORTING ---

session_start();
include 'db_connect.php';

$coffeeItems = [];
$categories = [];
$dbError = null;

// Check if user is logged in for JavaScript use later
$isLoggedIn = isset($_SESSION['user_id']) ? 'true' : 'false';

if ($conn->connect_errno) {
    $dbError = 'Database connection failed: ' . $conn->connect_error;
} else {
    // 1. Fetch categories
    $sql_categories = "SELECT category_id, name FROM categories ORDER BY category_id ASC";
    $result_categories = $conn->query($sql_categories);

    if ($result_categories) {
        while ($row = $result_categories->fetch_assoc()) {
            $categories[] = $row;
        }
        $result_categories->free();
    } else {
        $dbError = "SQL Error fetching categories: " . $conn->error;
    }

    // 2. Fetch products
    $sql_query = "SELECT id, name, price, old_price, description, image, category_id FROM coffees ORDER BY id DESC";
    $result = $conn->query($sql_query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $coffeeItems[] = $row;
        }
        $result->free();
    } else {
        $dbError = "SQL Error fetching products: " . $conn->error;
    }
}

$pageTitle = 'Menu | Meraki Coffee House';
$extraCss = '<style>
        /* ========== PAGE HERO ========== */
        .page-header {
            padding: 180px 0 80px;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url(\'images/premium_coffee_header.png\');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            text-align: center;
        }

        .section-label {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 3px;
            font-size: 0.813rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .section-label::before,
        .section-label::after {
            content: \'\';
            width: 40px;
            height: 1px;
            background: var(--accent);
        }

        .page-title {
            font-size: 4rem;
            color: var(--white);
            line-height: 1.1;
            margin-bottom: 20px;
        }

        .page-subtitle {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.9);
            max-width: 600px;
            margin: 0 auto;
        }

        /* ========== FILTERS ========== */
        .filters-wrapper {
            margin-bottom: 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }

        .search-box {
            position: relative;
            width: 100%;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 16px 30px;
            border-radius: 50px;
            border: 1px solid #e0dfdc;
            background: #fff;
            font-family: inherit;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--text-main);
            font-size: 0.95rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        }

        .search-box input::placeholder {
            color: #999;
            letter-spacing: 0.5px;
        }

        .search-box input:focus {
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 8px 30px rgba(201, 169, 110, 0.15);
            background: #fdfcfb;
            transform: translateY(-2px);
        }

        .category-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        .category-btn {
            background: transparent;
            color: var(--text-main);
            border: 1px solid var(--border);
            padding: 12px 28px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        .category-btn:hover,
        .category-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 5px 15px rgba(26, 15, 10, 0.2);
        }

        /* ========== MENU GRID ========== */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            padding-bottom: 80px;
        }
        .coffee-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            border: 1px solid var(--border);
            display: flex;
            flex-direction: column;
        }
        .coffee-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.06);
            border-color: var(--accent);
        }
        .card-img {
            height: 280px; /* Reduced from 420px */
            overflow: hidden;
            position: relative;
            background: transparent;
        }
        .card-img img {
            width: 100%;
            height: 100%;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            object-fit: contain;
            padding: 15px;
        }
        .coffee-card:hover .card-img img {
            transform: scale(1.05);
        }
        .card-body {
            padding: 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .card-title {
            font-size: 1.4rem; /* Reduced from 1.8rem */
            margin-bottom: 8px;
            color: var(--primary);
            font-family: \'Cormorant\', serif;
            font-weight: 600;
        }
        .card-description {
            font-size: 0.85rem; /* Smaller description */
            color: var(--text-light);
            margin-bottom: 20px;
            line-height: 1.5;
            min-height: 3em;
            opacity: 0.8;
        }
        .price-wrapper {
            margin-top: auto;
            margin-bottom: 20px;
        }
        .card-price {
            color: var(--accent-dark);
            font-weight: 700;
            font-size: 1.25rem; /* Reduced from 1.5rem */
            font-family: \'Inter\', sans-serif;
        }
        .card-old-price {
            color: var(--text-lighter);
            text-decoration: line-through;
            margin-right: 8px;
            font-size: 0.9rem;
        }
        .card-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .btn-action {
            padding: 10px;
            font-size: 0.7rem;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1px;
            border-radius: 50px;
            transition: 0.3s;
        }
        .btn-view {
            background: #f8f6f3;
            border: 1px solid var(--border);
            color: var(--primary);
        }
        .btn-view:hover {
            background: #eeeae4;
            border-color: var(--primary);
        }
        .btn-add {
            background: var(--primary);
            color: white;
            padding: 8px 15px; /* Smaller padding */
            font-size: 0.65rem; /* Smaller font */
            margin: 0 auto; /* Center in grid cell */
            width: fit-content;
        }
        .btn-add:hover {
            background: var(--accent);
            transform: scale(1.02);
        }
        @media (max-width: 1200px) { .menu-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 900px) { .menu-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px) { .menu-grid { grid-template-columns: 1fr; } .card-img { height: 240px; } }
    </style>';
include 'header.php';
?>

<div class="page-header" data-aos="fade-down">
    <div class="container">
        <span class="section-label">Original Blends</span>
        <h1 class="page-title">Curated Menu</h1>
        <p class="page-subtitle">Experience our hand-selected single origin coffees and artisanal pastries.</p>
    </div>
</div>

<main class="container">
    <!-- Error Message -->
    <?php if ($dbError): ?>
        <div
            style="background:#fee2e2; color:#b91c1c; padding:15px; border-radius:8px; margin-bottom:40px; text-align:center;">
            <strong>Database Error:</strong> <?php echo htmlspecialchars($dbError ?? ''); ?>
        </div>
    <?php endif; ?>

    <!-- Success Message -->
    <?php if (isset($_GET['added'])): ?>
        <div data-aos="fade-down"
            style="background:#d1fae5; color:#065f46; padding:15px; border-radius:8px; margin-bottom:40px; text-align:center; border: 1px solid #a7f3d0;">
            <i class="fas fa-check-circle" style="margin-right:8px;"></i>
            Item added to your cart successfully!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['limit'])): ?>
        <div data-aos="fade-down"
            style="background:#fee2e2; color:#b91c1c; padding:15px; border-radius:8px; margin-bottom:40px; text-align:center; border: 1px solid #fca5a5;">
            <i class="fas fa-exclamation-triangle" style="margin-right:8px;"></i>
            Maximum quantity of 20 reached for this item.
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="filters-wrapper" data-aos="fade-up">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search our selection..." oninput="filterCoffees()">
        </div>

        <div class="category-filter">
            <button type="button" class="category-btn active" data-category-id="all"
                onclick="filterByCategory(this, 'all')">All Products</button>
            <?php foreach ($categories as $cat): ?>
                <button type="button" class="category-btn" data-category-id="<?php echo $cat['category_id']; ?>"
                    onclick="filterByCategory(this, '<?php echo $cat['category_id']; ?>')">
                    <?php echo htmlspecialchars($cat['name'] ?? ''); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Grid -->
    <div class="menu-grid" id="coffeeGrid">
        <?php if (!empty($coffeeItems)): ?>
            <?php foreach ($coffeeItems as $index => $item): ?>
                <?php
                $imgPath = 'images/' . htmlspecialchars($item['image'] ?? '');
                $description = $item['description'] ? htmlspecialchars($item['description']) : 'A signature aromatic blend from Meraki.';
                $oldPrice = ($item['old_price'] > $item['price']) ? (float) $item['old_price'] : 0;
                $categoryId = $item['category_id'] ?? '0';
                ?>
                <div class="coffee-card" data-name="<?php echo htmlspecialchars(strtolower($item['name'] ?? '')); ?>"
                    data-category="<?php echo $categoryId; ?>" data-aos="fade-up" data-aos-delay="<?php echo $index * 50; ?>">
                    <div class="card-img">
                        <img src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($item['name'] ?? ''); ?>">
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo htmlspecialchars($item['name'] ?? ''); ?></h3>
                        <p class="card-description"><?php echo $description; ?></p>

                        <div class="price-wrapper">
                            <?php if ($oldPrice > 0): ?>
                                <span class="card-old-price">₹<?php echo number_format($oldPrice, 2); ?></span>
                            <?php endif; ?>
                            <span class="card-price">₹<?php echo number_format($item['price'], 2); ?></span>
                        </div>

                        <div class="card-actions">
                            <a href="product.php?id=<?php echo $item['id']; ?>" class="btn-action btn-view">Details</a>
                            <button class="btn-action btn-add" onclick="handleAddToCart('<?php echo $item['id']; ?>')">Add to
                                Cart</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; padding:40px; color:var(--text-light); width:100%; grid-column: 1/-1;">No
                products found.</p>
        <?php endif; ?>
    </div>
</main>

<script>
    // Pass the PHP login status to JavaScript
    const isLoggedIn = <?php echo $isLoggedIn; ?>;

    async function handleAddToCart(productId) {
        console.log('Adding to cart:', productId);
        if (!isLoggedIn) {
            // Redirect to login and pass the product ID in the URL
            window.location.href = 'login.php?add_id=' + productId;
            return;
        }

        try {
            const response = await fetch(`cart.php?action=add&id=${productId}&return=shop&ajax=1`);
            const data = await response.json();

            if (data.redirect) {
                window.location.href = data.redirect;
            } else if (data.success) {
                if (data.limitReached) {
                    showToast(data.message, 'error');
                } else {
                    showToast('Item added to cart successfully!', 'success');
                }
                // Optionally update a cart badge here if it existed
            } else {
                console.error('Failed to add item');
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            // Fallback to normal redirect if fetch fails
            window.location.href = 'cart.php?action=add&id=' + productId + '&return=shop';
        }
    }

    function showToast(message, type = 'success') {
        // Create toast container check
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = 'position: fixed; top: 100px; right: 20px; z-index: 9999;';
            document.body.appendChild(container);
        }

        const isError = type === 'error';
        const bgColor = isError ? '#fee2e2' : '#d1fae5';
        const textColor = isError ? '#b91c1c' : '#065f46';
        const borderColor = isError ? '#fca5a5' : '#a7f3d0';
        const icon = isError ? 'fa-exclamation-triangle' : 'fa-check-circle';

        const toast = document.createElement('div');
        toast.style.cssText = `
                background: ${bgColor};
                color: ${textColor};
                padding: 15px 25px;
                border: 1px solid ${borderColor};
                border-radius: 8px;
                margin-bottom: 10px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                display: flex;
                align-items: center;
                gap: 10px;
                font-weight: 500;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease;
            `;
        toast.innerHTML = `<i class="fas ${icon}"></i> ${message}`;

        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        });

        // Remove after 3 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function filterByCategory(clickedButton, categoryId) {
        document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
        clickedButton.classList.add('active');
        applyFilters();
    }

    function filterCoffees() {
        applyFilters();
    }

    function applyFilters() {
        const query = document.getElementById('searchInput').value.toLowerCase().trim();
        const activeCategory = document.querySelector('.category-btn.active').getAttribute('data-category-id');
        const grid = document.getElementById('coffeeGrid');
        const cards = document.querySelectorAll('.coffee-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const isNameMatch = card.dataset.name.includes(query);
            const isCategoryMatch = activeCategory === 'all' || card.dataset.category === activeCategory;

            if (isNameMatch && isCategoryMatch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>

<?php include 'footer.php'; ?>