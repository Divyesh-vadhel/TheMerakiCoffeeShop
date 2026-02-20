<?php $cp = basename($_SERVER['PHP_SELF']); ?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <span>MERAKI</span> <br><small style="font-size:0.7rem; font-family:'Poppins'; opacity:0.7;"></small>
    </div>

    <nav class="sidebar-nav">
        <a href="index.php" class="nav-link <?php echo $cp=='index.php'?'active':''; ?>">
            <span>📊</span> Dashboard
        </a>
        <a href="shop.php" class="nav-link <?php echo $cp=='shop.php'?'active':''; ?>">
            <span>☕</span> Coffees
        </a>
        <a href="orders.php" class="nav-link <?php echo $cp=='orders.php'?'active':''; ?>">
            <span>📦</span> Orders
        </a>
        <a href="table.php" class="nav-link <?php echo $cp=='table.php'?'active':''; ?>">
            <span>📅</span> Reservations
        </a>
        <a href="categories.php" class="nav-link <?php echo $cp=='categories.php'?'active':''; ?>">
            <span>🏷️</span> Categories
        </a>
        <a href="users.php" class="nav-link <?php echo $cp=='users.php'?'active':''; ?>">
            <span>👥</span> Users
        </a>
        <a href="contact.php" class="nav-link <?php echo $cp=='contact.php'?'active':''; ?>">
            <span>💬</span> Messages
        </a>
    </nav>

    <div class="sidebar-footer">
        <button class="btn-logout" onclick="window.location.href='logout.php'">Logout</button>
    </div>
</aside>