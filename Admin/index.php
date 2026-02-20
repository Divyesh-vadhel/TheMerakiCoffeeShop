<?php
require_once 'auth_check.php';
$conn = @new mysqli('localhost', 'root', '', 'kapetann');
$stats = ['coffees'=>0, 'orders'=>0, 'users'=>0, 'rev'=>0, 'categories'=>0];
if(!$conn->connect_errno){
    $stats['coffees'] = $conn->query("SELECT COUNT(*) c FROM coffees")->fetch_assoc()['c'];
    $stats['orders'] = $conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'];
    $stats['users'] = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
    $stats['rev'] = $conn->query("SELECT SUM(subtotal_amount) s FROM orders")->fetch_assoc()['s'] ?? 0;
    // NEW: Fetch categories count
    $stats['categories'] = $conn->query("SELECT COUNT(*) c FROM categories")->fetch_assoc()['c'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Dashboard | Meraki Coffee Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <?php include 'styles.php'; ?>
    <style>
        /* DASHBOARD STYLING */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; border: 1px solid var(--border); box-shadow: var(--shadow); display:flex; flex-direction:column; }
        .stat-val { font-size: 2.5rem; font-weight: 700; color: var(--accent); font-family: 'Playfair Display'; }
        .stat-label { color: var(--text-secondary); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }

        /* QUICK ACTIONS STYLING (Refined for better layout) */
        .quick-actions-container {
            display: flex; 
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .quick-actions-container a {
            flex-grow: 1; /* Allow buttons to expand slightly */
            max-width: 200px; /* Limit button width for a cleaner look on large screens */
            text-align: center;
            padding: 10px 15px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .quick-actions-container a:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Welcome back, Admin.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><span class="stat-label">Revenue</span><span class="stat-val">₹<?php echo number_format($stats['rev']); ?></span></div>
            <div class="stat-card"><span class="stat-label">Orders</span><span class="stat-val"><?php echo $stats['orders']; ?></span></div>
            <div class="stat-card"><span class="stat-label">Coffees</span><span class="stat-val"><?php echo $stats['coffees']; ?></span></div>
            <div class="stat-card"><span class="stat-label">Categories</span><span class="stat-val"><?php echo $stats['categories']; ?></span></div>
        </div>
        
        <div class="card">
            <h3>Quick Actions</h3>
            <div class="quick-actions-container">
                
                <a href="shop.php" class="btn-primary" style="text-decoration:none;">Add Coffee</a>
                
                <a href="categories.php" class="btn-primary" style="text-decoration:none; background:white; color:var(--text-primary); border:1px solid var(--border);">Manage Categories</a>
                
                <a href="orders.php" class="btn-primary" style="text-decoration:none; background:white; color:var(--text-primary); border:1px solid var(--border);">View Orders</a>
            </div>
        </div>
    </main>
</body>
</html>