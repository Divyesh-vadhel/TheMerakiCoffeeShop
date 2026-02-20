    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div style="padding-right: 40px;">
                    <h3>Meraki Coffee</h3>
                    <p class="footer-text">
                        Where passion meets purpose. Join us in brewing a better tomorrow, one exceptional cup at a
                        time.
                    </p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.instagram.com/the_meraki_coffee_shop?igsh=MXU2dnlpb3dib3BvZw==" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div>
                    <h3 class="footer-col-title">Quick Links</h3>
                    <ul class="footer-list">
                        <li><a href="shop.php" class="footer-link"><span class="link-arrow">></span> Order Online</a>
                        </li>
                        <li><a href="about.php" class="footer-link"><span class="link-arrow">></span> Our Mission</a>
                        </li>
                        <li><a href="table.php" class="footer-link"><span class="link-arrow">></span> Book a Table</a>
                        </li>
                        <li><a href="contact.php" class="footer-link"><span class="link-arrow">></span> Contact Us</a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="footer-col-title">Hours</h3>
                    <div class="hours-group">
                        <span class="hours-label">Mon - Fri</span>
                        <span class="hours-value">7:00 AM - 9:00 PM</span>
                    </div>
                    <div class="hours-group">
                        <span class="hours-label">Sat - Sun</span>
                        <span class="hours-value">7:00 AM - 11:00 PM</span>
                    </div>
                </div>

                <div>
                    <h3 class="footer-col-title">Visit Us</h3>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt contact-icon"></i>
                        <p style="line-height: 1.6;">Darshan University at Hadala<br>Rajkot-Morbi Highway<br>Gujarat
                            363030</p>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone-alt contact-icon"></i>
                        <p>+91 6355241317</p>
                    </div>
                </div>
            </div>

            <div class="copyright">
                © 2025 Meraki Coffee Roasters. Crafted with ♥ and Coffee.Designed by Dangar Jay,Divyesh Vadhel,Krish Chikani; All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            offset: 100,
            duration: 1000,
            easing: 'ease-out-cubic'
        });

        window.addEventListener('scroll', function () {
            const header = document.getElementById('navbar');
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>

</html>
