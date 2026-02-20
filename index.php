<?php
session_start();
define('SUPPRESS_DB_ERROR', true);
include 'db_connect.php';

$coffeeItems = [];
$dbError = null;

if ($conn->connect_errno) {
    $dbError = 'Database connection failed. Showing sample coffees only.';
    $coffeeItems = [
        ['id' => 1, 'name' => 'Tavern Signature Latte', 'price' => 180, 'image' => 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=400&q=80'],
        ['id' => 2, 'name' => 'Humble Morning Blend', 'price' => 220, 'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=400&q=80'],
        ['id' => 3, 'name' => 'Buckeye Cold Brew', 'price' => 150, 'image' => 'https://images.unsplash.com/photo-1517701604599-bb29b5dd7359?auto=format&fit=crop&w=400&q=80'],
    ];
} else {
    $result = $conn->query("SELECT id, name, price, image FROM coffees ORDER BY id DESC LIMIT 3");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $coffeeItems[] = $row;
        }
        $result->free();
    }
}
$pageTitle = 'Home | Meraki Coffee House';
$extraCss = '
<style>
    .menu-section {
        background-color: #0c0a09 !important;
        padding: 100px 0;
        position: relative;
    }
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px; /* Reduced gap */
        margin-top: 60px;
    }
    .coffee-card {
        background: rgba(28, 25, 23, 0.6); /* Glass effect */
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(212, 163, 115, 0.1);
        border-radius: 20px; /* Slightly smaller radius */
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        text-align: center;
        padding-bottom: 30px; /* Reduced padding */
    }
    .coffee-card:hover {
        transform: translateY(-10px);
        border-color: rgba(212, 163, 115, 0.3);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    }
    .card-img {
        height: 300px;
        overflow: hidden;
        position: relative;
        background: transparent;
    }
    .card-img img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        transition: transform 0.6s ease;
        padding: 20px;
    }
    .coffee-card:hover .card-img img {
        transform: scale(1.05);
    }
    .card-body {
        padding: 25px 20px;
    }
    .card-title {
        color: #fff;
        font-family: "Cormorant", serif;
        font-size: 1.5rem; /* Smaller title */
        font-weight: 500;
        margin-bottom: 12px;
        letter-spacing: 0.5px;
    }
    .card-price {
        color: #d4a373;
        font-size: 1.25rem; /* Smaller price */
        font-weight: 700;
        display: block;
        margin-bottom: 25px;
        font-family: "Inter", sans-serif;
    }
    .btn-add-cart {
        background-color: transparent;
        color: #d4a373;
        border: 1px solid #d4a373;
        padding: 8px 20px; /* Smaller padding */
        border-radius: 50px;
        font-size: 0.75rem; /* Smaller font */
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        margin: 0 auto; /* Ensure centering is reinforced */
        gap: 8px;
        box-shadow: none;
    }
    .btn-add-cart:hover {
        background-color: #d4a373;
        color: #fff;
        box-shadow: 0 5px 15px rgba(212, 163, 115, 0.3);
    }
    @media (max-width: 992px) {
        .menu-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
    }
    @media (max-width: 600px) {
        .menu-grid { grid-template-columns: 1fr; }
        .card-img { height: 260px; }
    }
</style>
';
?>
<?php include 'header.php'; ?>

<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content" data-aos="fade-up" data-aos-duration="1000">
        <!-- <div class="hero-badge" data-aos="fade-down" data-aos-delay="200">
                <i class="fas fa-award"></i>
                <span>Premium Coffee Experience</span>
            </div> -->
        <h1 class="hero-title">Brewing <span>Excellence</span><br>In Every Cup</h1>
        <p class="hero-text">
            Experience the art of coffee craftsmanship. Where passion meets perfection,<br>
            and every sip tells a story of quality and community.
        </p>
        <div data-aos="fade-up" data-aos-delay="400">
            <a href="shop.php" class="btn">Explore Menu</a>
            <a href="about.php" class="btn btn-outline">Our Journey</a>
        </div>
    </div>
    <div class="hero-scroll">
        <i class="fas fa-chevron-down"></i>
    </div>
</section>

<section class="section-padding story-section">
    <div class="container">
        <div class="story-grid">
            <div class="story-content" data-aos="fade-right" data-aos-duration="1000">
                <div class="section-label">Our Mission</div>
                <h2>Building Community Through Coffee</h2>
                <p>
                    What started as a vision has become a movement. We're more than a coffee house—we're a community
                    hub where transformation happens, one cup at a time.
                </p>
                <p>
                    Our commitment extends beyond exceptional coffee. Through our job training programs, we empower
                    young adults with skills, confidence, and opportunities to build meaningful careers and brighter
                    futures.
                </p>
                <a href="about.php" class="story-link">
                    Discover Our Story
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="story-img-wrapper" data-aos="fade-left" data-aos-duration="1000">
                <div class="story-img">
                    <img src="https://images.unsplash.com/photo-1497935586351-b67a49e012bf?auto=format&fit=crop&w=800&q=80"
                        alt="Coffee Shop Interior">
                </div>
                <div class="story-badge">
                    <strong>10+</strong>
                    <span>Years of Excellence</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding menu-section">
    <div class="container">
        <div class="section-header text-center" data-aos="fade-up">
            <div class="section-label">Our Signature Collection</div>
            <h2 style="color: white;">Handcrafted Perfection</h2>
            <p style="color: rgba(255,255,255,0.7);">Each blend carefully curated and expertly crafted to deliver an
                unforgettable experience</p>
        </div>

        <div class="menu-grid">
            <?php foreach ($coffeeItems as $index => $item):
                $img = $item['image'];
                $imgPath = (strpos($img, 'http') === 0) ? $img : "images/$img";
                ?>
                <div class="coffee-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="card-img">
                        <img src="<?php echo htmlspecialchars($imgPath ?? ''); ?>"
                            alt="<?php echo htmlspecialchars($item['name'] ?? ''); ?>">
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo htmlspecialchars($item['name'] ?? ''); ?></h3>
                        <span class="card-price">₹<?php echo number_format($item['price'], 2); ?></span>
                        <button class="btn-add-cart"
                            onclick="window.location.href='cart.php?action=add&id=<?php echo $item['id']; ?>&return=shop'">
                            <i class="fas fa-shopping-bag"></i> ADD TO CART
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center" style="margin-top: 60px;" data-aos="fade-up">
            <a href="shop.php" class="btn btn-outline">View Complete Menu</a>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="section-header text-center" data-aos="fade-up">
            <div class="section-label">What Sets Us Apart</div>
            <h2>The Meraki Difference</h2>
            <p>Excellence in every detail, care in every interaction</p>
        </div>

        <div class="features-row">
            <div class="feature-box" data-aos="zoom-in" data-aos-delay="0">
                <span class="feature-icon">🤝</span>
                <h3>Community Impact</h3>
                <p>Every purchase supports our job training programs, creating opportunities and transforming lives
                    in our community.</p>
            </div>
            <div class="feature-box" data-aos="zoom-in" data-aos-delay="100">
                <span class="feature-icon">🥐</span>
                <h3>Artisan Bakery</h3>
                <p>Partnered with local bakers to bring you fresh, handcrafted pastries and treats made with love
                    each morning.</p>
            </div>
            <div class="feature-box" data-aos="zoom-in" data-aos-delay="200">
                <span class="feature-icon">🌱</span>
                <h3>Sustainable Sourcing</h3>
                <p>Direct trade, ethically sourced beans from farmers who share our commitment to quality and
                    sustainability.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>