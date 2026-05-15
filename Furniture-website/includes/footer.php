<!-- Footer -->
<footer class="footer mt-5 pt-5 pb-3">
    <div class="container">
        <div class="row">
            <!-- Brand -->
            <div class="col-md-4 mb-4">
                <h5 class="footer-brand">🪑 Santosh-Furniture</h5>
                <p class="text-muted">Quality furniture crafted with love and premium wood. Your home deserves the best.</p>
                <div class="social-links mt-3">
                    <a href=""><i class="fab fa-facebook"></i></a>
                    <a href=""><i class="fab fa-instagram"></i></a>
                    <a href=""><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <!-- Categories -->
            <div class="col-md-3 mb-4">
                <h6 class="footer-heading">Categories</h6>
                <ul class="footer-links">
                    <?php foreach($categories as $cat): ?>
                    <li>
                        <a href="/furniture-website/category.php?slug=<?= $cat['slug'] ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Quick Links -->
            <div class="col-md-2 mb-4">
                <h6 class="footer-heading">Quick Links</h6>
                <ul class="footer-links">
                    <li><a href="/furniture-website/index.php">Home</a></li>
                    <li><a href="/furniture-website/products.php">Products</a></li>
                    <li><a href="/furniture-website/quote.php">Get Quote</a></li>
                    <li><a href="/furniture-website/store-location.php">Our Store</a></li>
                    <li><a href="/furniture-website/my-orders.php">Track Order</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-md-3 mb-4">
                <h6 class="footer-heading">Contact Us</h6>
                <ul class="footer-links">
                    <li><i class="fas fa-phone me-2"></i>+91 8210187952</li>
                    <li><i class="fas fa-envelope me-2"></i>Santoshfurniture@gmail.com</li>
                    <li><i class="fas fa-map-marker-alt me-2"></i>Gopalgang, Bihar</li>
                </ul>
            </div>
        </div>

        <hr class="footer-divider">
        <div class="text-center">
            <small class="text-muted">© <?= date('Y') ?> Santosh Furniture. All rights reserved.</small>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Session State for JS Login Gate -->
<script>
    const IS_LOGGED_IN = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
</script>
<!-- Custom JS -->
<script src="/furniture-website/assets/js/main.js"></script>

</body>
</html>