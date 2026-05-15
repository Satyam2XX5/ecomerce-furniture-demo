/* =====================================================
   SANTOSH FURNITURE - MAIN JS
   Features: Cart, Language Switcher, Login Gate
   ===================================================== */

/* ─────────────────────────────────────────────────────
   1. LANGUAGE SYSTEM
   ───────────────────────────────────────────────────── */
const TRANSLATIONS = {
    en: {
        name: "English", flag: "🇬🇧",
        addToCart:      "Added to cart! 🛒",
        loginRequired:  "Please Login First",
        loginDesc:      "To add products to cart, send WhatsApp inquiry, or get a free quote — please login or create a free account.",
        loginBtn:       "Login",
        registerBtn:    "Create Free Account",
        continueGuest:  "Continue as Guest",
        cartEmpty:      "Your cart is empty",
        whatsappMsg:    "Hello! I'm interested in \"{product}\". Please share more details.",
        langSaved:      "Language changed to English ✓",
        langTitle:      "Language / भाषा",
        langSubtitle:   "Choose your preferred language",
    },
    hi: {
        name: "हिंदी", flag: "🇮🇳",
        addToCart:      "कार्ट में जोड़ा गया! 🛒",
        loginRequired:  "पहले लॉगिन करें",
        loginDesc:      "प्रोडक्ट कार्ट में जोड़ने, व्हाट्सऐप पर पूछताछ करने या फ्री कोटेशन के लिए — कृपया लॉगिन करें या फ्री अकाउंट बनाएं।",
        loginBtn:       "लॉगिन करें",
        registerBtn:    "फ्री अकाउंट बनाएं",
        continueGuest:  "बिना लॉगिन जारी रखें",
        cartEmpty:      "आपकी कार्ट खाली है",
        whatsappMsg:    "नमस्ते! मुझे \"{product}\" में रुचि है। कृपया अधिक जानकारी दें।",
        langSaved:      "भाषा हिंदी में बदली ✓",
        langTitle:      "भाषा / Language",
        langSubtitle:   "अपनी पसंदीदा भाषा चुनें",
    },
    hinglish: {
        name: "Hinglish", flag: "🤝",
        addToCart:      "Cart mein add ho gaya! 🛒",
        loginRequired:  "Pehle Login Karo Bhai!",
        loginDesc:      "Cart mein product add karne ke liye, WhatsApp pe inquiry dene ke liye, ya free quote lene ke liye — pehle login karo ya free account banao!",
        loginBtn:       "Login Karo",
        registerBtn:    "Free Account Banao",
        continueGuest:  "Bina Login Jaari Rakho",
        cartEmpty:      "Teri cart khali hai yaar",
        whatsappMsg:    "Hello! Mujhe \"{product}\" mein interest hai. Please aur details bata do.",
        langSaved:      "Language Hinglish ho gaya ✓",
        langTitle:      "Language / भाषा",
        langSubtitle:   "Apni favourite language chunno",
    }
};

function getLang()  { return localStorage.getItem('sf_lang') || 'hinglish'; }
function t(key)     { const l = getLang(); return (TRANSLATIONS[l]||{})[key] || (TRANSLATIONS.hinglish||{})[key] || key; }

function setLanguage(lang) {
    if (!TRANSLATIONS[lang]) return;
    localStorage.setItem('sf_lang', lang);
    applyLanguage();
    document.querySelectorAll('.lang-option').forEach(btn => {
        const active = btn.dataset.lang === lang;
        btn.style.background  = active ? '#8B4513' : '#FFF8F0';
        btn.style.color       = active ? 'white'   : '#8B4513';
    });
    showToast(t('langSaved'));
}

function applyLanguage() {
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const v = t(el.dataset.i18n);
        if (v) el.textContent = v;
    });
}

/* ─────────────────────────────────────────────────────
   2. LOGIN GATE SYSTEM
   ───────────────────────────────────────────────────── */
function requireLogin(action, callback) {
    if (typeof IS_LOGGED_IN !== 'undefined' && IS_LOGGED_IN) { callback(); return; }
    showLoginModal(action, callback);
}

function showLoginModal(action) {
    const existing = document.getElementById('login-gate-modal');
    if (existing) existing.remove();
    const icons = { cart:'🛒', whatsapp:'💬', quote:'📋' };
    const icon  = icons[action] || '🔐';
    const currentUrl = encodeURIComponent(window.location.href);

    const modal = document.createElement('div');
    modal.id = 'login-gate-modal';
    modal.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.55);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;padding:16px;animation:fadeInBg 0.25s ease;';

    modal.innerHTML = `
      <div style="background:white;border-radius:24px;width:100%;max-width:420px;box-shadow:0 30px 80px rgba(0,0,0,0.25);animation:slideUpModal 0.3s ease;overflow:hidden;">
        <div style="background:linear-gradient(135deg,#8B4513,#C17B4A);padding:28px 28px 22px;text-align:center;position:relative;">
          <button onclick="closeLoginModal()" style="position:absolute;top:14px;right:16px;background:rgba(255,255,255,0.2);border:none;color:white;width:30px;height:30px;border-radius:50%;cursor:pointer;font-size:16px;">✕</button>
          <div style="font-size:48px;margin-bottom:10px;">${icon}</div>
          <h4 style="color:white;margin:0;font-family:'Playfair Display',serif;font-size:22px;">${t('loginRequired')}</h4>
        </div>
        <div style="padding:24px 28px 28px;">
          <p style="color:#666;text-align:center;font-size:14px;line-height:1.7;margin-bottom:22px;">${t('loginDesc')}</p>
          <a href="/furniture-website/login.php?redirect=${currentUrl}" style="display:block;width:100%;padding:13px;background:#8B4513;color:white;text-align:center;border-radius:12px;text-decoration:none;font-weight:700;font-size:15px;margin-bottom:10px;">
            <i class="fas fa-sign-in-alt" style="margin-right:8px;"></i>${t('loginBtn')}
          </a>
          <a href="/furniture-website/register.php?redirect=${currentUrl}" style="display:block;width:100%;padding:13px;background:#FFF8F0;color:#8B4513;text-align:center;border-radius:12px;text-decoration:none;font-weight:700;font-size:15px;margin-bottom:16px;border:2px solid #8B4513;">
            <i class="fas fa-user-plus" style="margin-right:8px;"></i>${t('registerBtn')}
          </a>
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div style="flex:1;height:1px;background:#eee;"></div>
            <span style="font-size:12px;color:#aaa;">ya phir</span>
            <div style="flex:1;height:1px;background:#eee;"></div>
          </div>
          <button onclick="closeLoginModal()" style="width:100%;padding:10px;background:none;border:1px solid #ddd;border-radius:12px;color:#999;cursor:pointer;font-size:13px;">
            ${t('continueGuest')}
          </button>
        </div>
      </div>`;

    document.body.appendChild(modal);
    modal.addEventListener('click', e => { if (e.target === modal) closeLoginModal(); });
}

function closeLoginModal() {
    const m = document.getElementById('login-gate-modal');
    if (m) { m.style.opacity = '0'; m.style.transition = 'opacity 0.2s'; setTimeout(() => m.remove(), 200); }
}

/* ─────────────────────────────────────────────────────
   3. CART SYSTEM
   ───────────────────────────────────────────────────── */
function getCart()      { return JSON.parse(localStorage.getItem('furniture_cart')) || []; }
function saveCart(cart) { localStorage.setItem('furniture_cart', JSON.stringify(cart)); updateCartCount(); }

function updateCartCount() {
    const count = getCart().reduce((total, item) => total + item.qty, 0);
    const badge = document.getElementById('cart-count');
    if (badge) badge.textContent = count;
}

function addToCart(id, name, price, image) {
    requireLogin('cart', function() {
        let cart = getCart();
        const existing = cart.find(item => item.id == id);
        if (existing) { existing.qty += 1; } else { cart.push({ id, name, price, image, qty: 1 }); }
        saveCart(cart);
        showToast(t('addToCart'));
    });
}

function removeFromCart(id) { saveCart(getCart().filter(item => item.id != id)); renderCart(); }

function updateQty(id, qty) {
    let cart = getCart();
    const item = cart.find(item => item.id == id);
    if (item) { item.qty = parseInt(qty); if (item.qty <= 0) cart = cart.filter(i => i.id != id); }
    saveCart(cart); renderCart();
}

/* ─────────────────────────────────────────────────────
   4. CART PAGE RENDER
   ───────────────────────────────────────────────────── */
function renderCart() {
    const container   = document.getElementById('cart-items-container');
    const summaryTotal= document.getElementById('cart-total-amount');
    const emptyMsg    = document.getElementById('cart-empty');
    const cartContent = document.getElementById('cart-content');
    if (!container) return;
    const cart = getCart();
    if (cart.length === 0) {
        if (emptyMsg)    emptyMsg.style.display    = 'block';
        if (cartContent) cartContent.style.display = 'none';
        return;
    }
    if (emptyMsg)    emptyMsg.style.display    = 'none';
    if (cartContent) cartContent.style.display = 'block';
    let html = '', total = 0;
    cart.forEach(item => {
        const sub = item.price * item.qty;
        total += sub;
        html += `
        <div class="cart-item">
          <img src="${item.image||'/furniture-website/assets/images/placeholder.jpg'}" alt="${item.name}">
          <div class="flex-grow-1">
            <h6 class="mb-1">${item.name}</h6>
            <p class="text-muted mb-2">₹${Number(item.price).toLocaleString('en-IN')}</p>
            <div class="d-flex align-items-center gap-3">
              <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="updateQty(${item.id},${item.qty-1})">-</button>
                <span class="fw-bold">${item.qty}</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="updateQty(${item.id},${item.qty+1})">+</button>
              </div>
              <button class="btn btn-sm btn-danger" onclick="removeFromCart(${item.id})"><i class="fas fa-trash"></i></button>
            </div>
          </div>
          <div class="text-end"><strong class="price-current">₹${sub.toLocaleString('en-IN')}</strong></div>
        </div>`;
    });
    container.innerHTML = html;
    if (summaryTotal) summaryTotal.textContent = '₹' + total.toLocaleString('en-IN');
}

/* ─────────────────────────────────────────────────────
   5. WHATSAPP  (with login gate)
   ───────────────────────────────────────────────────── */
function openWhatsApp(number, productName) {
    requireLogin('whatsapp', function() {
        const msg = t('whatsappMsg').replace('{product}', productName);
        window.open(`https://wa.me/${number}?text=${encodeURIComponent(msg)}`, '_blank');
    });
}

/* ─────────────────────────────────────────────────────
   6. QUOTE GATE
   ───────────────────────────────────────────────────── */
function goToQuote(url) {
    requireLogin('quote', function() { window.location.href = url; });
}

/* ─────────────────────────────────────────────────────
   7. IMAGE GALLERY / TOAST / ADMIN / SEARCH
   ───────────────────────────────────────────────────── */
function changeMainImage(src) {
    const mainImg = document.getElementById('main-product-img');
    if (mainImg) mainImg.src = src;
    document.querySelectorAll('.thumbnail-strip img').forEach(img => img.classList.toggle('active', img.src === src));
}

function showToast(message) {
    const ex = document.getElementById('wood-toast');
    if (ex) ex.remove();
    const toast = document.createElement('div');
    toast.id = 'wood-toast';
    toast.innerHTML = `<div style="position:fixed;bottom:24px;right:24px;background:#2C1810;color:#FDF6EE;padding:14px 22px;border-radius:12px;font-size:14px;font-weight:500;z-index:99998;box-shadow:0 8px 30px rgba(0,0,0,0.2);animation:slideInToast 0.3s ease;max-width:300px;">${message}</div>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function previewImages(input) {
    const preview = document.getElementById('image-preview');
    if (!preview) return;
    preview.innerHTML = '';
    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.style.cssText = 'position:relative;display:inline-block;margin:6px;';
            div.innerHTML = `<img src="${e.target.result}" style="width:90px;height:90px;object-fit:cover;border-radius:10px;border:2px solid #C4956A;">`;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function confirmDelete(url, itemName) {
    if (confirm(`"${itemName}" ko delete karna chahte ho? Yeh wapas nahi aayega!`)) window.location.href = url;
}

function filterProducts() {
    const search = document.getElementById('search-input')?.value.toLowerCase();
    document.querySelectorAll('.product-filter-card').forEach(card => {
        const name = card.dataset.name?.toLowerCase();
        const cat  = card.dataset.category?.toLowerCase();
        card.style.display = (name?.includes(search)||cat?.includes(search)) ? 'block' : 'none';
    });
}

/* ─────────────────────────────────────────────────────
   8. CSS ANIMATIONS
   ───────────────────────────────────────────────────── */
(function() {
    if (document.getElementById('sf-anim')) return;
    const s = document.createElement('style');
    s.id = 'sf-anim';
    s.textContent = `
        @keyframes slideInToast { from{transform:translateY(30px);opacity:0} to{transform:translateY(0);opacity:1} }
        @keyframes slideUpModal  { from{transform:translateY(40px);opacity:0} to{transform:translateY(0);opacity:1} }
        @keyframes fadeInBg      { from{opacity:0} to{opacity:1} }
        .lang-option { transition:all 0.2s ease; cursor:pointer; }
        .lang-option:hover { transform:translateY(-2px); box-shadow:0 4px 12px rgba(139,69,19,0.25) !important; }
    `;
    document.head.appendChild(s);
})();

/* ─────────────────────────────────────────────────────
   9. INIT
   ───────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    updateCartCount();
    renderCart();
    applyLanguage();

    // Highlight active lang button
    const curLang = getLang();
    document.querySelectorAll('.lang-option').forEach(btn => {
        if (btn.dataset.lang === curLang) {
            btn.style.background = '#8B4513';
            btn.style.color      = 'white';
        }
    });

    const searchInput = document.getElementById('search-input');
    if (searchInput) searchInput.addEventListener('keyup', filterProducts);
});
