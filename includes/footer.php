<footer class="bg-black text-white py-5">
    <div class="container">
        <div class="row">

            <!-- Branding -->
            <div class="col-md-4 mb-4 d-flex flex-column align-items-start">
                <a href="index.php" class="mb-3">
                    <img src="assets/images/logotranswhite.png" alt="Biofuel Logo" style="height:70px;">
                </a>
                <p class="text-muted">Clean Energy. Real Focus.</p>
            </div>

            <!-- Quick Links -->
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold border-bottom border-secondary pb-1 mb-3">Quick Links</h5>
                <div class="row">
                    <div class="col-6">
                        <ul class="list-unstyled">
                            <li><a href="index.php" class="footer-link mb-2 d-block">Home</a></li>
                            <li><a href="products.php" class="footer-link mb-2 d-block">Products</a></li>
                            <li><a href="about.php" class="footer-link mb-2 d-block">About Us</a></li>
                        </ul>
                    </div>
                    <div class="col-6">
                        <ul class="list-unstyled">
                            <li><a href="contact.php" class="footer-link mb-2 d-block">Contact</a></li>
                            <li><a href="privacy.php" class="footer-link mb-2 d-block">Privacy Policy</a></li>
                            <li><a href="refund.php" class="footer-link mb-2 d-block">Refund Policy</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold border-bottom border-secondary pb-1 mb-3">Contact Us</h5>
                <p class="mb-1">ADDRESS:
314 Imam Haron Road,
Lansdowne, Cape Town,
South Africa, 7780</p>
<br>
                <p class="mb-1">Email: biofuel.capetown@gmail.com</p>
                <br>
                <p class="mb-2">Phone: +27 23-789-4567</p>
                <div class="d-flex">
                    <a href="https://www.tiktok.com/@biofuel_official" class="social-icon me-3"  target="_blank"><i class="fab fa-tiktok fa-lg"></i></a>
                    <a href="https://www.instagram.com/_biofuel?igsh=dzdmemYweWg1cWo5" class="social-icon me-3" target="_blank"><i class="fab fa-instagram fa-lg"></i></a>
                </div>
            </div>

        </div>

        <div class="text-center mt-4 border-top border-secondary pt-3 text-muted small">
            &copy; <?= date('Y') ?> Biofuel. All rights reserved.
            <br>
            <a href="terms.php" class="footer-link text-decoration-underline">Terms & Conditions</a>
        </div>
    </div>
</footer>

<style>
    /* Footer link hover */
    .footer-link {
        color: #ffffff;
        transition: color 0.3s;
    }
    .footer-link:hover {
        color: #ffc107; /* Bootstrap warning yellow */
        text-decoration: none;
    }

    /* Social icons hover */
    .social-icon {
        color: #ffffff;
        transition: color 0.3s, transform 0.3s;
    }
    .social-icon:hover {
        color: #ffc107;
        transform: scale(1.2);
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
