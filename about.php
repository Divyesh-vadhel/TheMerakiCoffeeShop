<?php
session_start();
$pageTitle = 'Our Story | Meraki Coffee House';
$extraCss = '<style>
        /* ABOUT SPECIFIC */
        .hero-mini {
            background: var(--primary);
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .story-section {
            padding: 80px 0;
        }

        .story-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            margin-bottom: 60px;
        }

        .story-img img {
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 50px;
        }

        .value-card {
            background: var(--bg-soft); /* Note: --bg-soft not defined in style.css, might fallback or need definition. Using var(--bg-body) or white? */
            background-color: white; /* Fallback */
            padding: 40px;
            text-align: center;
            border-radius: 16px;
            transition: 0.3s;
        }

        .value-card:hover {
            transform: translateY(-5px);
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        /* OLD LEADERSHIP STYLES (Kept but section removed) */
        .leadership-section {
            padding: 80px 0;
            background: var(--bg-card); /* Undefined var */
            text-align: center;
        }

        .leader-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 40px;
        }

        .leader-card {
            text-align: center;
        }

        .leader-photo {
            width: 180px;
            height: 180px;
            margin: 0 auto 15px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid var(--accent);
        }

        .leader-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .leader-card h4 {
            /* font-family: \'Playfair Display\', serif; Overridden to match index */
            margin-bottom: 5px;
            font-size: 1.3rem;
            color: var(--primary);
        }

        .leader-card p {
            font-size: 0.9rem;
            color: var(--text-muted); /* Undefined var, using text-light */
            color: var(--text-light);
            font-weight: 500;
        }

        /* HISTORY/IMPACT STYLES */
        .impact-section {
            padding: 80px 0;
        }

        .impact-stats {
            display: flex;
            justify-content: space-around;
            text-align: center;
            margin-bottom: 60px;
        }

        .stat h3 {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 5px;
        }

        .stat p {
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
            color: var(--primary);
        }

        .timeline {
            border-left: 3px solid var(--border);
            padding-left: 20px;
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }

        .timeline-event {
            margin-bottom: 40px;
            position: relative;
        }

        .timeline-event h4 {
            /* font-family: \'Playfair Display\', serif; */
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .timeline-event .date {
            font-weight: 600;
            color: var(--accent);
            font-size: 0.9rem;
            margin-bottom: 10px;
            display: block;
        }

        .timeline-event::before {
            content: \'\';
            position: absolute;
            left: -26px;
            top: 5px;
            width: 16px;
            height: 16px;
            background: var(--accent);
            border-radius: 50%;
            border: 3px solid var(--bg-body);
        }


        @media (max-width: 1024px) {
            .leader-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .story-grid {
                grid-template-columns: 1fr;
            }

            .values-grid {
                grid-template-columns: 1fr;
            }

            .leader-grid {
                grid-template-columns: 1fr;
            }
            
            .impact-stats {
                flex-direction: column;
                gap: 40px;
            }

            .hero-mini h1 {
                font-size: 2.5rem;
            }
        }
</style>';
include 'header.php';
?>

    <div class="hero-mini">
        <div class="container" data-aos="fade-up">
            <span style="color:var(--accent); text-transform:uppercase; letter-spacing:2px; font-weight:600;">Our
                Mission</span>
            <h1 style="font-size: 3.5rem; margin-top: 10px;">Passion, Quality, and Community</h1>
        </div>
    </div>

    <section class="story-section container">
        <div class="story-grid">
            <div data-aos="fade-right">
                <h2 style="font-size: 2.5rem; color: var(--primary); margin-bottom: 20px;">The Meraki Story</h2>
                <p style="margin-bottom: 20px; font-size: 1.1rem; color: var(--text-muted);">
                    Meraki is more than just a coffee house. Our name means to do something with soul, creativity, or
                    love—to put 'something of yourself' into your work.
                </p>
                <p style="margin-bottom: 20px; color: var(--text-muted);">
                    We started with a simple idea: serve exceptional coffee while actively empowering our local
                    community. We source high-quality beans ethically and invest in training local youth, ensuring every
                    purchase makes a positive impact.
                </p>
                <p style="color: var(--text-muted);">
                    Our commitment is to transparency and passion in every step, from bean to cup.
                </p>
            </div>
            <div class="story-img" data-aos="fade-left">
                <img src="https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=800&q=80"
                    alt="Barista working">
            </div>
        </div>

        <h2 style="font-size: 2.5rem; color: var(--primary); margin-bottom: 20px; text-align: center;"
            data-aos="fade-up">Core Values</h2>
        <div class="values-grid">
            <div class="value-card" data-aos="zoom-in" data-aos-delay="0">
                <div style="font-size: 3rem; margin-bottom: 15px;">🌱</div>
                <h3>Ethical Sourcing</h3>
                <p>We choose partners who prioritize sustainable farming and fair trade practices.</p>
            </div>
            <div class="value-card" data-aos="zoom-in" data-aos-delay="100">
                <div style="font-size: 3rem; margin-bottom: 15px;">🤝</div>
                <h3>Community First</h3>
                <p>Focused on youth employment, mentorship, and local engagement initiatives.</p>
            </div>
            <div class="value-card" data-aos="zoom-in" data-aos-delay="200">
                <div style="font-size: 3rem; margin-bottom: 15px;">⭐</div>
                <h3>Uncompromising Quality</h3>
                <p>Commitment to the highest quality in every ingredient, process, and service interaction.</p>
            </div>
        </div>
    </section>

    <section class="impact-section container">
        <h2 style="font-size: 2.5rem; color: var(--primary); text-align: center; margin-bottom: 40px;"
            data-aos="fade-up">Our Impact (Since Launch)</h2>

        <div class="impact-stats" data-aos="fade-up" data-aos-delay="100">
            <div class="stat">
                <h3>95%</h3>
                <p>Ethically Sourced Beans</p>
            </div>
            <div class="stat">
                <h3>42+</h3>
                <p>Youth Trained & Employed</p>
            </div>
            <!-- <div class="stat">
                <h3>₹15,000+</h3>
                <p>Annual Community Fund</p>
            </div> -->
        </div>

        <h3 style="font-family: 'Playfair Display', serif; font-size: 2rem; color: var(--primary); text-align: center; margin-bottom: 40px;"
            data-aos="fade-up">Milestones</h3>

        <div class="timeline" data-aos="fade-up" data-aos-delay="150">

            <div class="timeline-event">
                <span class="date">2024</span>
                <h4>The Idea of Meraki Born</h4>
                <p>The vision for high-quality coffee with a strong social mission is established.</p>
            </div>

            <div class="timeline-event">
                <span class="date">2025 Q1</span>
                <h4>Flagship Store Opens</h4>
                <p>Opened our first location and welcomed our inaugural cohort of apprentice baristas.</p>
            </div>

            <div class="timeline-event">
                <span class="date">2025 Q3</span>
                <h4>Sustainability Goals Met</h4>
                <p>Achieved 95% sustainable sourcing by partnering with ethical cooperatives.</p>
            </div>

            <div class="timeline-event">
                <span class="date">Future</span>
                <h4>Planning for Growth</h4>
                <p>A second location is planned to expand our reach and double our training capacity.</p>
            </div>

        </div>
    </section>

<?php include 'footer.php'; ?>
