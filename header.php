<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo isset($pageTitle) ? $pageTitle : 'MERAKI Coffee Roasters | Brewing Excellence'; ?></title>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Italiana&family=Cormorant:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <?php if (isset($extraCss)) echo $extraCss; ?>
</head>

<body>

    <header id="navbar">
        <div class="container nav-wrapper">
            <a href="index.php" class="logo">
                <img src="/Project-2/images/logo1.png" alt="MERAKI" class="logo-img">
            </a>

            <nav class="nav-menu">
                <a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a>
                <a href="shop.php" class="nav-link <?php echo ($current_page == 'shop.php' || $current_page == 'product.php') ? 'active' : ''; ?>">Menu</a>
                <a href="about.php" class="nav-link <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">Story</a>
                <a href="table.php" class="nav-link <?php echo ($current_page == 'table.php' || $current_page == 'table-book.php') ? 'active' : ''; ?>">Reservation</a>
                <a href="contact.php" class="nav-link <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">Contact</a>
            </nav>

            <div class="nav-icons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="status.php" class="nav-link <?php echo ($current_page == 'status.php') ? 'active' : ''; ?>" style="font-size:0.75rem;">Status</a>
                    <a href="logout.php" class="nav-link" style="font-size:0.75rem;">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link" style="font-size:0.75rem;">Login</a>
                <?php endif; ?>
                <button class="icon-btn" onclick="window.location.href='cart.php'">
                    <i class="fas fa-shopping-bag"></i>
                </button>
            </div>
        </div>
    </header>
