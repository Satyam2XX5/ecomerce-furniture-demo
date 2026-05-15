<?php
$page_title = "My Cart";
require_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">My Cart</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container pb-5">

    <!-- Empty Cart State -->
    <div id="cart-empty" style="display:none;">
        <div class="empty-state py-5">
            <i class="fas fa-shopping-cart"></i>
            <h4>Aapka cart khali hai!</h4>
            <p>Koi product abhi tak add nahi kiya</p>
            <a href="products.php" class="btn-primary-wood mt-3">
                <i class="fas fa-couch me-2"></i>Products Dekho
            </a>
        </div>
    </div>

    <!-- Cart Content -->
    <div id="cart-content" style="display:none;">
        <div class="row g-4">

            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 style="font-family:'Playfair Display',serif; margin:0;">
                        🛒 My Cart
                    </h4>
                    <button onclick="clearCart()" 
                            style="background:none; border:1px solid #E74C3C; color:#E74C3C; 
                                   padding:8px 16px; border-radius:50px; cursor:pointer; font-size:13px;">
                        <i class="fas fa-trash me-1"></i> Clear All
                    </button>
                </div>

                <!-- Items render here by JS -->
                <div id="cart-items-container"></div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="cart-summary">
                    <h4>Order Summary</h4>

                    <div class="cart-summary-row">
                        <span class="text-muted">Subtotal</span>
                        <span id="cart-total-amount" class="fw-bold">₹0</span>
                    </div>
                    <div class="cart-summary-row">
                        <span class="text-muted">Delivery</span>
                        <span style="color:#27AE60; font-weight:600;">FREE</span>
                    </div>
                    <div class="cart-summary-row">
                        <span class="text-muted">GST (18%)</span>
                        <span id="cart-gst">₹0</span>
                    </div>

                    <div class="cart-summary-row cart-total">
                        <span>Total</span>
                        <span id="cart-grand-total">₹0</span>
                    </div>

                    <!-- WhatsApp Order -->
                    <button onclick="orderOnWhatsApp()" class="btn-whatsapp w-100 mt-4 text-center"
                            style="padding:14px; font-size:16px; border-radius:12px;">
                        <i class="fab fa-whatsapp me-2"></i>
                        Order via WhatsApp
                    </button>

                    <!-- Get Quote -->
                    <a href="quote.php" class="btn-outline-wood d-block text-center mt-3"
                       style="padding:13px; font-size:15px; border-radius:12px;">
                        <i class="fas fa-file-alt me-2"></i>
                        Get Formal Quote
                    </a>

                    <!-- Continue Shopping -->
                    <a href="products.php" class="d-block text-center mt-3 text-muted" 
                       style="font-size:14px;">
                        ← Continue Shopping
                    </a>

                    <!-- Secure Info -->
                    <div class="mt-4 pt-3" style="border-top:2px solid var(--bg-cream);">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-shield-alt" style="color:var(--primary);"></i>
                            <small class="text-muted">Secure & Trusted</small>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-truck" style="color:var(--primary);"></i>
                            <small class="text-muted">Free Delivery in City</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-undo" style="color:var(--primary);"></i>
                            <small class="text-muted">Easy Returns Policy</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Clear entire cart
function clearCart() {
    if (confirm('Saara cart clear karna chahte ho?')) {
        localStorage.removeItem('furniture_cart');
        updateCartCount();
        renderCart();
    }
}

// WhatsApp order
function orderOnWhatsApp() {
    const cart = getCart();
    if (cart.length === 0) return;

    let msg = '🪑 *Furniture Order Inquiry*\n\n';
    let total = 0;

    cart.forEach(item => {
        const subtotal = item.price * item.qty;
        total += subtotal;
        msg += `• *${item.name}*\n`;
        msg += `  Qty: ${item.qty} × ₹${Number(item.price).toLocaleString('en-IN')} = ₹${subtotal.toLocaleString('en-IN')}\n\n`;
    });

    msg += `*Total: ₹${total.toLocaleString('en-IN')}*\n`;
    msg += `\nKripya delivery aur payment details share karein.`;

    window.open(`https://wa.me/919876543210?text=${encodeURIComponent(msg)}`, '_blank');
}

// Override renderCart to also update GST + Grand Total
const _originalRenderCart = renderCart;
document.addEventListener('DOMContentLoaded', function () {
    // Extend render to calculate GST
    const origRender = window.renderCart;
    window.renderCart = function () {
        origRender();
        const cart = getCart();
        let total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        const gst = Math.round(total * 0.18);
        const grand = total + gst;

        const gstEl = document.getElementById('cart-gst');
        const grandEl = document.getElementById('cart-grand-total');
        if (gstEl) gstEl.textContent = '₹' + gst.toLocaleString('en-IN');
        if (grandEl) grandEl.textContent = '₹' + grand.toLocaleString('en-IN');
    };

    renderCart();
});
</script>

<?php require_once 'includes/footer.php'; ?>