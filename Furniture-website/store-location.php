<?php
require_once 'includes/db.php';
$page_title = "Humara Store";
require_once 'includes/header.php';
?>

<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Humara Store</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">

    <!-- Page Header -->
    <div class="text-center mb-5">
        <h2 style="font-family:'Playfair Display',serif; color:#8B4513;">
            <i class="fas fa-map-marker-alt me-2"></i>Hamare Store ka Location
        </h2>
        <p class="text-muted fs-5">Aao milne, personally furniture dekho aur apni pasand ka piece chunno!</p>
    </div>

    <div class="row g-4 align-items-start">

        <!-- Store Info Cards -->
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm mb-4" style="border-radius:18px; overflow:hidden;">
                <div style="background:linear-gradient(135deg,#8B4513,#A0522D); padding:25px; text-align:center;">
                    <div style="width:70px;height:70px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 15px;font-size:30px;color:white;">
                        🪑
                    </div>
                    <h4 style="color:white;font-family:'Playfair Display',serif;margin:0;">Santosh Furniture</h4>
                    <p style="color:rgba(255,255,255,0.8);margin:5px 0 0;">Premium Quality Furniture</p>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex gap-3 align-items-start mb-3">
                        <div style="width:40px;height:40px;background:#FFF8F0;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#8B4513;">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <strong>Address</strong>
                            <p class="mb-0 text-muted">Gopalganj, Bihar - 841428<br>India</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-start mb-3">
                        <div style="width:40px;height:40px;background:#FFF8F0;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#8B4513;">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <strong>Phone / WhatsApp</strong>
                            <p class="mb-0">
                                <a href="tel:+918210187952" style="color:#8B4513;text-decoration:none;">+91 82101 87952</a>
                            </p>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-start mb-3">
                        <div style="width:40px;height:40px;background:#FFF8F0;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#8B4513;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <strong>Email</strong>
                            <p class="mb-0">
                                <a href="mailto:Santoshfurniture@gmail.com" style="color:#8B4513;text-decoration:none;">Santoshfurniture@gmail.com</a>
                            </p>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-start">
                        <div style="width:40px;height:40px;background:#FFF8F0;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#8B4513;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <strong>Store Timing</strong>
                            <p class="mb-0 text-muted">
                                Somwar - Shaniwar: <strong>9:00 AM – 8:00 PM</strong><br>
                                Raviwar: <strong>10:00 AM – 6:00 PM</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Buttons -->
            <div class="d-grid gap-3">
                <a href="https://wa.me/918210187952?text=Namaste!%20Main%20aapke%20store%20aana%20chahta%20hun." 
                   target="_blank"
                   class="btn py-3 fw-bold" 
                   style="background:#25D366; color:white; border-radius:14px; font-size:16px;">
                    <i class="fab fa-whatsapp me-2"></i>WhatsApp par Message Karo
                </a>
                <a href="https://www.google.com/maps/dir/?api=1&destination=Gopalganj,Bihar,India" 
                   target="_blank"
                   class="btn py-3 fw-bold"
                   style="background:#4285F4; color:white; border-radius:14px; font-size:16px;">
                    <i class="fas fa-directions me-2"></i>Directions Lao
                </a>
                <a href="tel:+918210187952" 
                   class="btn py-3 fw-bold"
                   style="background:#8B4513; color:white; border-radius:14px; font-size:16px;">
                    <i class="fas fa-phone me-2"></i>Call Karo Abhi
                </a>
            </div>
        </div>

        <!-- Google Maps Embed -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:18px; overflow:hidden;">
                <!-- Map Header -->
                <div class="p-3" style="background:#FFF8F0; border-bottom:1px solid #f0e6da;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-map-marked-alt" style="color:#8B4513; font-size:18px;"></i>
                        <span class="fw-semibold">Santosh Furniture — Gopalganj, Bihar</span>
                        <a href="https://www.google.com/maps/search/Gopalganj+Bihar+Furniture+store" 
                           target="_blank"
                           class="btn btn-sm ms-auto"
                           style="background:#8B4513; color:white; border-radius:8px; font-size:12px;">
                            <i class="fas fa-external-link-alt me-1"></i>Full Map Dekho
                        </a>
                    </div>
                </div>

                <!-- Embedded Map (OpenStreetMap - no API key needed) -->
                <div style="position:relative;">
                    <iframe 
                        src="https://www.openstreetmap.org/export/embed.html?bbox=84.3878%2C26.4299%2C84.4878%2C26.5099&layer=mapnik&marker=26.4699%2C84.4378"
                        style="width:100%; height:450px; border:none; display:block;"
                        allowfullscreen
                        loading="lazy"
                        title="Santosh Furniture Store Location - Gopalganj Bihar">
                    </iframe>

                    <!-- Overlay marker label -->
                    <div style="position:absolute; top:15px; left:50%; transform:translateX(-50%);
                                background:white; padding:8px 16px; border-radius:25px;
                                box-shadow:0 4px 15px rgba(0,0,0,0.15); 
                                font-size:13px; font-weight:600; color:#8B4513;
                                pointer-events:none; white-space:nowrap;">
                        <i class="fas fa-store me-1"></i>Santosh Furniture, Gopalganj
                    </div>
                </div>

                <!-- Map Footer -->
                <div class="p-3" style="background:#f8f9fa; border-top:1px solid #e9ecef;">
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <small class="text-muted d-block">Latitude</small>
                            <strong style="font-size:13px;">26.4699° N</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Longitude</small>
                            <strong style="font-size:13px;">84.4378° E</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">District</small>
                            <strong style="font-size:13px;">Gopalganj, Bihar</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- How to reach section -->
            <div class="card border-0 shadow-sm mt-4" style="border-radius:18px;">
                <div class="card-body p-4">
                    <h5 style="font-family:'Playfair Display',serif; color:#8B4513; margin-bottom:20px;">
                        <i class="fas fa-route me-2"></i>Kaise Pahunche?
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 text-center" style="background:#FFF8F0; border-radius:12px;">
                                <i class="fas fa-train fa-2x mb-2" style="color:#8B4513;"></i>
                                <h6 class="fw-bold">Train se</h6>
                                <small class="text-muted">Gopalganj Railway Station se sirf 2 km dur</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 text-center" style="background:#FFF8F0; border-radius:12px;">
                                <i class="fas fa-bus fa-2x mb-2" style="color:#8B4513;"></i>
                                <h6 class="fw-bold">Bus se</h6>
                                <small class="text-muted">Gopalganj Bus Stand se 1 km andar</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 text-center" style="background:#FFF8F0; border-radius:12px;">
                                <i class="fas fa-car fa-2x mb-2" style="color:#8B4513;"></i>
                                <h6 class="fw-bold">Car / Bike se</h6>
                                <small class="text-muted">Google Maps mein "Santosh Furniture Gopalganj" search karo</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
