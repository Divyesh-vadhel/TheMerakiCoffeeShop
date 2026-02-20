<?php
include 'db_connect.php';
session_start();

$user_data = null;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $u_res = $conn->query("SELECT username, email FROM users WHERE id = $uid");
    if ($u_res && $u_res->num_rows > 0) {
        $user_data = $u_res->fetch_assoc();
    }
}


$pageTitle = 'Exquisite Dining | Meraki Coffee House';
$extraCss = '
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
<style>
    :root {
        --bg-light: #fcfaf8;
        --accent-gold: #c08457;
        --accent-brown: #4a342e;
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-border: rgba(192, 132, 87, 0.15);
        --card-bg: linear-gradient(145deg, #ffffff, #f9f6f2);
        --text-main: #2c1810;
        --text-muted: #8d7d77;
        --transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        background-color: var(--bg-light);
        color: var(--text-main);
        font-family: "Outfit", sans-serif;
        background-image: 
            radial-gradient(circle at 10% 10%, rgba(192, 132, 87, 0.05) 0%, transparent 40%),
            radial-gradient(circle at 90% 90%, rgba(93, 64, 55, 0.05) 0%, transparent 40%);
        background-attachment: fixed;
    }

    .hero-section {
        padding: 120px 0 60px;
        text-align: center;
        position: relative;
    }

    .section-title {
        font-family: "Playfair Display", serif;
        font-size: 4rem;
        font-weight: 800;
        margin-bottom: 20px;
        letter-spacing: -1.5px;
        color: var(--text-main);
    }

    .section-title span {
        font-style: italic;
        color: var(--accent-gold);
        position: relative;
        display: inline-block;
    }

    .section-title span::after {
        content: "";
        position: absolute;
        bottom: 8px;
        left: 0;
        width: 100%;
        height: 4px;
        background: var(--accent-gold);
        opacity: 0.2;
        border-radius: 2px;
    }

    .subtitle {
        color: var(--accent-gold);
        font-size: 0.9rem;
        letter-spacing: 5px;
        text-transform: uppercase;
        margin-bottom: 30px;
        font-weight: 700;
        opacity: 0.9;
    }

    .table-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
        gap: 40px;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 40px 120px;
    }

    .table-card {
        background: var(--card-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 40px;
        padding: 50px;
        display: flex;
        flex-direction: column;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        min-height: 580px;
        box-shadow: 
            0 10px 40px rgba(74, 52, 46, 0.04),
            0 2px 4px rgba(192, 132, 87, 0.02);
    }

    .table-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, transparent, var(--accent-gold), transparent);
        opacity: 0;
        transition: var(--transition);
    }

    .table-card:hover {
        transform: translateY(-12px);
        border-color: rgba(192, 132, 87, 0.4);
        box-shadow: 
            0 40px 80px rgba(74, 52, 46, 0.08),
            0 10px 20px rgba(192, 132, 87, 0.05);
    }

    .table-card:hover::before {
        opacity: 1;
    }

    /* Architectural Table Visual */
    .table-visual-v2 {
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 40px;
        position: relative;
        background: rgba(192, 132, 87, 0.02);
        border-radius: 24px;
    }

    .blueprint-circle {
        width: 110px;
        height: 110px;
        border: 1px dashed var(--accent-gold);
        border-radius: 50%;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        z-index: 2;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    .blueprint-circle::after {
        content: "";
        position: absolute;
        width: 130px;
        height: 130px;
        border: 1px solid rgba(192, 132, 87, 0.1);
        border-radius: 50%;
    }

    .chair-v2 {
        width: 24px;
        height: 24px;
        background: #fff;
        border: 2px solid var(--accent-gold);
        border-radius: 8px;
        position: absolute;
        z-index: 1;
        transition: var(--transition);
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    /* Seats Positioning (Updated for larger icons) */
    .seats-2 .chair-v2:nth-child(2) { transform: translate(-85px, 0); }
    .seats-2 .chair-v2:nth-child(3) { transform: translate(85px, 0); }

    .seats-4 .chair-v2:nth-child(2) { transform: translate(0, -85px); }
    .seats-4 .chair-v2:nth-child(3) { transform: translate(85px, 0); }
    .seats-4 .chair-v2:nth-child(4) { transform: translate(0, 85px); }
    .seats-4 .chair-v2:nth-child(5) { transform: translate(-85px, 0); }

    /* Higher Seat Counts ... */
    .seats-5 .chair-v2:nth-child(2) { transform: translate(0, -85px); }
    .seats-5 .chair-v2:nth-child(3) { transform: translate(80px, -30px) rotate(72deg); }
    .seats-5 .chair-v2:nth-child(4) { transform: translate(50px, 75px) rotate(144deg); }
    .seats-5 .chair-v2:nth-child(5) { transform: translate(-50px, 75px) rotate(216deg); }
    .seats-5 .chair-v2:nth-child(6) { transform: translate(-80px, -30px) rotate(288deg); }

    .seats-6 .chair-v2:nth-child(2) { transform: translate(0, -90px); }
    .seats-6 .chair-v2:nth-child(3) { transform: translate(80px, -45px) rotate(60deg); }
    .seats-6 .chair-v2:nth-child(4) { transform: translate(80px, 45px) rotate(120deg); }
    .seats-6 .chair-v2:nth-child(5) { transform: translate(0, 90px) rotate(180deg); }
    .seats-6 .chair-v2:nth-child(6) { transform: translate(-80px, 45px) rotate(240deg); }
    .seats-6 .chair-v2:nth-child(7) { transform: translate(-80px, -45px) rotate(300deg); }

    .table-info h3 {
        font-family: "Playfair Display", serif;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 20px;
        letter-spacing: 0.5px;
        color: var(--text-main);
    }

    .tag-container {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-bottom: 40px;
        flex-wrap: wrap;
    }

    .tag {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        padding: 8px 20px;
        border-radius: 100px;
        background: #fff;
        border: 1px solid #efefef;
        color: var(--text-muted);
        font-weight: 700;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    .tag.highlight {
        background: rgba(192, 132, 87, 0.08);
        border-color: rgba(192, 132, 87, 0.1);
        color: var(--accent-gold);
    }

    .action-button {
        background: var(--text-main);
        color: #fff;
        border: none;
        padding: 20px 40px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: var(--transition);
        margin-top: auto;
        text-transform: uppercase;
        letter-spacing: 2px;
        box-shadow: 0 10px 25px rgba(44, 24, 16, 0.15);
        position: relative;
        overflow: hidden;
    }

    .action-button::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        transition: 0.5s;
    }

    .action-button:hover {
        background: var(--accent-brown);
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(44, 24, 16, 0.25);
    }

    .action-button:hover::before {
        left: 100%;
    }

    /* Form Styles */
    .glass-form {
        display: none;
        text-align: left;
    }

    .glass-form.active {
        display: block;
        animation: fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .input-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .input-group label {
        display: block;
        font-size: 0.75rem;
        color: var(--accent-gold);
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 700;
    }

    .input-group input, .input-group select {
        width: 100%;
        background: #fff;
        border: 1px solid #eee;
        border-radius: 16px;
        padding: 16px 20px;
        color: var(--text-main);
        font-family: inherit;
        transition: var(--transition);
        font-weight: 500;
    }

    .input-group input:focus {
        border-color: var(--accent-gold);
        outline: none;
        box-shadow: 0 0 0 4px rgba(192, 132, 87, 0.08);
    }

    .confirm-booking {
        width: 100%;
        background: var(--text-main);
        color: #fff;
        border: none;
        padding: 18px;
        border-radius: 16px;
        font-weight: 700;
        margin-top: 30px;
        cursor: pointer;
        transition: var(--transition);
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .confirm-booking:hover {
        background: var(--accent-gold);
        transform: translateY(-2px);
    }

    .dismiss-button {
        width: 100%;
        background: transparent;
        color: var(--text-muted);
        border: none;
        padding: 15px;
        font-size: 0.85rem;
        margin-top: 10px;
        cursor: pointer;
        text-decoration: underline;
        font-weight: 600;
    }

    /* Alerts */
    .alert-premium {
        max-width: 700px;
        margin: 0 auto 60px;
        padding: 30px;
        border-radius: 24px;
        text-align: center;
        background: #fff;
        border: 1px solid rgba(192, 132, 87, 0.1);
        box-shadow: 0 15px 45px rgba(0,0,0,0.05);
    }
    
    .alert-premium.success { border-left: 6px solid #43a047; color: #1b5e20; }
    .alert-premium.error { border-left: 6px solid #e53935; color: #b71c1c; }

    /* Slider Styles */
    .duration-slider-container {
        background: #fff;
        border: 1px solid #f0f0f0;
        border-radius: 24px;
        padding: 25px;
        margin-top: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.02);
    }

    .slider-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .slider-value-display .time {
        display: block;
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--text-main);
    }

    .slider-value-display .price {
        display: block;
        font-size: 1rem;
        color: var(--accent-gold);
        font-weight: 700;
    }

    .custom-slider {
        -webkit-appearance: none;
        width: 100%;
        height: 6px;
        border-radius: 10px;
        background: #f0f0f0;
        outline: none;
        margin: 25px 0;
    }

    .custom-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--accent-gold);
        cursor: pointer;
        border: 5px solid #fff;
        box-shadow: 0 4px 12px rgba(192, 132, 87, 0.3);
        transition: var(--transition);
    }

    .custom-slider::-webkit-slider-thumb:hover {
        transform: scale(1.15);
        background: var(--accent-brown);
    }

    .slider-labels {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        }

    }

    /* RESERVED SLOTS STYLING */
    .reserved-slots {
        margin-top: 15px;
        font-size: 0.75rem;
        background: #fff;
        border-radius: 12px;
        padding: 10px;
        border: 1px solid #eee;
    }

    .slot-badge {
        display: inline-block;
        padding: 2px 8px;
        background: rgba(185, 28, 28, 0.05);
        color: #b91c1c;
        border-radius: 4px;
        margin: 2px;
        font-weight: 600;
    }

    /* FLOATING INSTAGRAM BUTTON */
    .floating-insta {
        position: fixed;
        bottom: 40px;
        right: 40px;
        width: 60px;
        height: 60px;
        background: var(--text-main);
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        z-index: 1000;
        transition: var(--transition);
        border: 1px solid var(--accent-gold);
    }

    .floating-insta:hover {
        transform: scale(1.1) rotate(15deg);
        background: var(--accent-gold);
        box-shadow: 0 15px 40px rgba(145, 107, 66, 0.3);
    }

    .insta-hero-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 15px;
        padding: 10px 20px;
        background: #fff;
        border: 1px solid var(--glass-border);
        border-radius: 100px;
        color: var(--text-main);
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: var(--transition);
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }

    .insta-hero-link:hover {
        background: var(--bg-light);
        border-color: var(--accent-gold);
        color: var(--accent-gold);
        transform: translateY(-2px);
    }

</style>';

include 'header.php';
?>

<div class="hero-section">
    <div class="container text-center">
        <div class="subtitle animate__animated animate__fadeIn">Timeless Elegance</div>
        <h1 class="section-title animate__animated animate__fadeInUp">Reserve Your <span>Space</span></h1>
        <p style="margin-top:-20px; font-size:0.9rem; color:var(--text-muted);">
            <i class="fas fa-clock"></i> Bookings require at least 5 minutes advance notice.
            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                <br><a href="checkout.php" style="color:var(--accent-gold); font-weight:600; text-decoration:underline; display:inline-block; margin-top:10px;">Return to Checkout</a>
            <?php endif; ?>
        </p>

    </div>
</div>
 

<div class="container">
    <?php if (isset($_GET['requested'])): ?>
        <div class="alert-premium success animate__animated animate__fadeIn">
            <i class="fas fa-check-circle" style="margin-right: 10px;"></i> Your reservation has been successfully
            requested.
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <?php
        $errorMsg = "We encountered an issue. Please attempt your reservation again.";
        if ($_GET['error'] == 'booked')
            $errorMsg = "This Sanctuary is already reserved for the selected timeframe.";
        elseif ($_GET['error'] == 'locked')
            $errorMsg = "This Space is currently being held by another guest. Please try again in 10 minutes.";
        elseif ($_GET['error'] == 'past')
            $errorMsg = "The selected time must be at least 5 minutes in the future. Please choose a later moment.";
        elseif ($_GET['error'] == 'hours')
            $errorMsg = "Our Sanctuary is closed at the selected time. Please honor our operating hours.";
        ?>
        <div class="alert-premium error animate__animated animate__fadeIn">
            <i class="fas fa-exclamation-circle" style="margin-right: 10px;"></i> <?php echo $errorMsg; ?>
        </div>
    <?php endif; ?>

    <div class="table-grid">
        <?php
        $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        $today = date('Y-m-d');
        $sql = "SELECT t.*, 
                       (SELECT COUNT(*) FROM table_locks l WHERE l.table_id = t.id AND l.expires_at > NOW() AND l.session_id != '" . session_id() . "') as is_locked
                FROM tables t 
                ORDER BY t.id ASC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($table = $result->fetch_assoc()) {
                $capacity = (int) $table['capacity'];
                $tableId = $table['id'];
                $tableName = "Residency " . $table['table_number'];
                $location = $table['location'];
                $status = $table['status'];
                $isLocked = (int)$table['is_locked'] > 0;

                if ($isLocked) {
                    $status = "Held (10 min)";
                }

                $seatClass = "seats-" . $capacity;
                if ($capacity > 6)
                    $seatClass = "seats-6";
                ?>

                <div class="table-card animate__animated animate__fadeIn"
                    style="animation-delay: <?php echo $tableId * 0.1; ?>s">
                    <div class="card-content-main" id="main-<?php echo $tableId; ?>">
                        <div class="table-visual-v2 <?php echo $seatClass; ?>">
                            <div class="blueprint-circle">
                                <i class="fas fa-leaf" style="color: var(--accent-gold); opacity: 0.6; font-size: 1.4rem;"></i>
                            </div>
                            <?php for ($i = 0; $i < $capacity; $i++): ?>
                                <div class="chair-v2"></div>
                            <?php endfor; ?>
                        </div>

                        <div class="text-center">
                            <h3><?php echo $tableName; ?></h3>
                            <div class="tag-container" style="margin-bottom: 20px;">
                                <span class="tag highlight"><?php echo $status; ?></span>
                                <span class="tag"><?php echo $capacity; ?> Guests</span>
                                <span class="tag"><?php echo $location; ?></span>
                            </div>

                            <?php
                            // Aggregate today's reservations for JS check
                            $today = date('Y-m-d');
                            $slots_sql = "SELECT time, duration_mins FROM reservations WHERE table_id = $tableId AND date = '$today' AND status = 'Confirmed' ORDER BY time ASC";
                            $slots_res = $conn->query($slots_sql);
                            $js_slots = [];
                            if ($slots_res && $slots_res->num_rows > 0) {
                                while($slot = $slots_res->fetch_assoc()) {
                                    $js_slots[] = [
                                        'start' => $slot['time'],
                                        'mins' => (int)$slot['duration_mins']
                                    ];
                                }
                            }
                            ?>
                            <script>
                                if(!window.reservedSlots) window.reservedSlots = {};
                                window.reservedSlots[<?php echo $tableId; ?>] = <?php echo json_encode($js_slots); ?>;
                            </script>

                             <button class="action-button" <?php echo $isLocked ? 'disabled style="background:#888; cursor:not-allowed;"' : ''; ?> onclick="handleBookingClick(<?php echo $tableId; ?>)">
                                <?php echo $isLocked ? 'Currently Held' : 'Book This Space'; ?>
                             </button>
                        </div>
                    </div>

                    <div class="glass-form" id="form-<?php echo $tableId; ?>">
                        <div class="subtitle text-center" style="margin-bottom: 25px; font-size: 0.8rem;">Exclusive Reservation
                        </div>
                        <form action="table-book.php" method="post">
                            <input type="hidden" name="table_id" value="<?php echo $tableId; ?>">
                            <input type="hidden" name="location" value="<?php echo $location; ?>">
                            <?php if (isset($_GET['reschedule'])): ?>
                                <input type="hidden" name="reschedule_id" value="<?php echo (int)$_GET['reschedule']; ?>">
                            <?php endif; ?>

                            <div class="input-row">
                                <div class="input-group">
                                    <label>Full Name</label>
                                    <input type="text" name="name" required placeholder="Name" value="<?php echo $user_data ? htmlspecialchars($user_data['username']) : ''; ?>" readonly>
                                </div>
                                <div class="input-group">
                                    <label>Email Address</label>
                                    <input type="email" name="email" required placeholder="Email" value="<?php echo $user_data ? htmlspecialchars($user_data['email']) : ''; ?>" readonly>
                                </div>
                            </div>

                            <div class="input-row">
                                <div class="input-group">
                                    <label>Guests</label>
                                    <select name="person" required>
                                        <?php for ($p = 1; $p <= $capacity; $p++): ?>
                                            <option value="<?php echo $p; ?>"><?php echo $p; ?> Guests</option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label>Date</label>
                                    <input type="date" id="res-date-<?php echo $tableId; ?>" name="date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" required onchange="handleDateChange(<?php echo $tableId; ?>)">
                                </div>
                            </div>

                            <div class="input-group">
                                <label>Preferred Time</label>
                                <input type="time" id="res-time-<?php echo $tableId; ?>" name="time" required onchange="checkConflicts(<?php echo $tableId; ?>)">
                                <small id="time-hint-<?php echo $tableId; ?>" style="display: block; margin-top: 5px; color: var(--text-muted); font-size: 0.7rem;"></small>
                                <div id="conflict-msg-<?php echo $tableId; ?>" style="display: none; margin-top: 10px; color: #b71c1c; font-size: 0.8rem; font-weight: 600; background: #fce8e6; padding: 10px; border-radius: 8px; border-left: 3px solid #c62828;">
                                    <i class="fas fa-exclamation-triangle"></i> This time is already reserved. Please book the next table or choose a different time.
                                </div>
                            </div>

                            <div class="duration-slider-container">
                                <label style="display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 10px;">Select Duration & Price</label>
                                
                                <div class="slider-header text-center">
                                    <div class="slider-value-display" style="width: 100%;">
                                        <span class="time" id="duration-val-<?php echo $tableId; ?>">10 Minutes</span>
                                        <span class="price" id="price-val-<?php echo $tableId; ?>">₹100.00</span>
                                    </div>
                                </div>

                                <input type="range" name="duration_mins" 
                                    class="custom-slider" 
                                    min="10" max="60" step="5" value="10"
                                    oninput="updateSlider(<?php echo $tableId; ?>, this.value); checkConflicts(<?php echo $tableId; ?>)">
                                
                                <input type="hidden" name="booking_fee" id="fee-input-<?php echo $tableId; ?>" value="100.00">

                                <div class="slider-labels">
                                    <span>10 Min</span>
                                    <span>60 Min Limit</span>
                                </div>

                                <div style="display: flex; align-items: center; gap: 6px; margin-top: 15px; justify-content: center;">
                                    <i class="fas fa-shield-alt" style="font-size: 0.7rem; color: var(--accent-gold);"></i>
                                    <small style="color: var(--text-muted); font-size: 0.75rem;">Premium Placement Guarantee</small>
                                </div>
                            </div>

                            <button type="submit" class="confirm-booking" id="submit-btn-<?php echo $tableId; ?>">Confirm Reservation</button>
                            <button type="button" class="dismiss-button" onclick="closeBooking(<?php echo $tableId; ?>)">Return
                                to Gallery</button>
                        </form>
                    </div>
                </div>

                <?php
            }
        }
        ?>
    </div>
</div>

<script>
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

    function handleBookingClick(id) {
        if (!isLoggedIn) {
            window.location.href = 'login.php?redirect=table.php&book_id=' + id;
            return;
        }
        
        // AJAX lock
        const formData = new FormData();
        formData.append('table_id', id);
        
        fetch('ajax_lock_table.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                openBooking(id);
            } else {
                alert(data.message);
                if (data.message.includes('someone else')) {
                    location.reload();
                }
            }
        })
        .catch(err => alert('Error locking table.'));
    }

    function openBooking(id) {
        document.getElementById('main-' + id).style.display = 'none';
        const form = document.getElementById('form-' + id);
        form.style.display = 'block';
        
        // Initialize time for today
        const now = new Date();
        const todayStr = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
        const dateInput = document.getElementById('res-date-' + id);
        if(dateInput.value === todayStr) {
            now.setMinutes(now.getMinutes() + 5); // Add 5 minutes buffer as requested
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('res-time-' + id).value = `${hours}:${minutes}`;
        }
        handleDateChange(id);
        checkConflicts(id);
    }

    // Handle Reschedule Auto-Open
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const rescheduleId = urlParams.get('reschedule');
        if (rescheduleId) {
            // Find the table for this booking
            <?php
            if (isset($_GET['reschedule'])) {
                $rid = (int)$_GET['reschedule'];
                $r_res = $conn->query("SELECT table_id, date, time, person, duration_mins FROM reservations WHERE id = $rid AND user_id = $uid");
                if ($r_res && $r_res->num_rows > 0) {
                    $r_data = $r_res->fetch_assoc();
                    echo "const rTableId = " . $r_data['table_id'] . ";\n";
                    echo "const rDate = '" . $r_data['date'] . "';\n";
                    echo "const rTime = '" . $r_data['time'] . "';\n";
                    echo "const rPerson = " . $r_data['person'] . ";\n";
                    echo "const rDuration = " . $r_data['duration_mins'] . ";\n";
                }
            }
            ?>
            if (typeof rTableId !== 'undefined') {
                handleBookingClick(rTableId);
                // Pre-fill values
                setTimeout(() => {
                    const dateInput = document.getElementById('res-date-' + rTableId);
                    const timeInput = document.getElementById('res-time-' + rTableId);
                    const personSelect = document.querySelector(`#form-${rTableId} select[name="person"]`);
                    const durationSlider = document.querySelector(`#form-${rTableId} .custom-slider`);
                    
                    if (dateInput) dateInput.value = rDate;
                    if (timeInput) timeInput.value = rTime;
                    if (personSelect) personSelect.value = rPerson;
                    if (durationSlider) {
                        durationSlider.value = rDuration;
                        updateSlider(rTableId, rDuration);
                    }
                    checkConflicts(rTableId);
                }, 500);
            }
        }
    });

    function handleDateChange(id) {
        const dateInput = document.getElementById('res-date-' + id);
        const timeInput = document.getElementById('res-time-' + id);
        const hint = document.getElementById('time-hint-' + id);
        
        const date = new Date(dateInput.value);
        const day = date.getDay(); // 0 = Sunday, 6 = Saturday
        
        let start = "07:00";
        let end = (day === 0 || day === 6) ? "23:00" : "21:00";
        
        timeInput.min = start;
        timeInput.max = end;
        
        const dayLabel = (day === 0 || day === 6) ? "Weekend" : "Weekday";
        hint.innerHTML = `<i class="fas fa-info-circle"></i> ${dayLabel} Hours: 7:00 AM - ${end === "23:00" ? "11:00 PM" : "9:00 PM"}`;
        
        // If today, ensure time isn't in past (allow 5 min buffer)
        const now = new Date();
        const todayStr = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
        
        if(dateInput.value === todayStr) {
            now.setMinutes(now.getMinutes() + 5); 
            const curH = String(now.getHours()).padStart(2, '0');
            const curM = String(now.getMinutes()).padStart(2, '0');
            const currentTime = `${curH}:${curM}`;
            if(currentTime > start) {
                timeInput.min = currentTime;
            }
        }
        checkConflicts(id);
    }

    function checkConflicts(id) {
        const timeInput = document.getElementById('res-time-' + id);
        const dateInput = document.getElementById('res-date-' + id);
        const slider = document.querySelector(`#form-${id} input[name="duration_mins"]`);
        const msg = document.getElementById('conflict-msg-' + id);
        const btn = document.getElementById('submit-btn-' + id);
        
        const selectedDate = dateInput.value;
        const todayStr = new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0') + '-' + String(new Date().getDate()).padStart(2, '0');
        
        // Only check today's conflicts in JS for simplicity (server handles all dates)
        if(selectedDate !== todayStr || !window.reservedSlots[id]) {
            msg.style.display = 'none';
            btn.disabled = false;
            btn.style.opacity = '1';
            return;
        }

        const selectedStart = timeInput.value;
        if(!selectedStart) return;

        const duration = parseInt(slider.value);
        const [h1, m1] = selectedStart.split(':').map(Number);
        const startSec = h1 * 3600 + m1 * 60;
        const endSec = startSec + (duration * 60);

        let conflict = false;
        window.reservedSlots[id].forEach(slot => {
            const [h2, m2] = slot.start.split(':').map(Number);
            const slotStartSec = h2 * 3600 + m2 * 60;
            const slotEndSec = slotStartSec + (slot.mins * 60);

            // Overlap check
            if (startSec < slotEndSec && endSec > slotStartSec) {
                conflict = true;
            }
        });

        if(conflict) {
            msg.style.display = 'block';
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.cursor = 'not-allowed';
        } else {
            msg.style.display = 'none';
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
        }
    }

    function updateSlider(id, mins) {
        // Price: ₹10 per minute (10 min => 100, 20 min => 200, ..., 60 min => 600)
        let price = mins * 10;

        document.getElementById('duration-val-' + id).textContent = mins + ' Minutes';
        document.getElementById('price-val-' + id).textContent = '₹' + price.toFixed(2);
        document.getElementById('fee-input-' + id).value = price.toFixed(2);
    }

    function closeBooking(id) {
        document.getElementById('main-' + id).style.display = 'block';
        const form = document.getElementById('form-' + id);
        form.style.display = 'none';
    }

    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const bookId = urlParams.get('book_id');
        if (bookId && isLoggedIn) {
            handleBookingClick(bookId);
        }
    });
</script>

<?php include 'footer.php'; ?>