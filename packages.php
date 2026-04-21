<?php
// packages.php
require_once 'config/database.php';
require_once 'models/Package.php';
require_once 'models/Settings.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get category from URL
$category = isset($_GET['category']) ? $_GET['category'] : null;

// Get packages
$package = new Package($db);
if ($category && $category !== 'all') {
    $packages = $package->getByCategory($category);
    $currentCategory = $category;
} else {
    $packages = $package->getActive();
    $currentCategory = null;
}

// Get all categories for filter
$categories = $package->getCategories();

// Get settings
$settings = new Settings($db);
$settingsArray = $settings->getAllAsArray();

$siteTitle = $settingsArray['site_title'] ?? 'Framer Photography';
$contactEmail = $settingsArray['contact_email'] ?? 'framer.wedding@gmail.com';
$contactPhone = $settingsArray['contact_phone'] ?? '+8801829093616';
$whatsappNumber = $settingsArray['whatsapp_number'] ?? '8801829093616';
$address = $settingsArray['address'] ?? 'Rajonigondha Vally, 178/B Khilgaon Chowdhurypara, Matirmoshjheed jheelpar, Dhaka 1219';
$businessHours = $settingsArray['business_hours'] ?? 'Monday - Saturday (9am to 7pm)';
$facebookUrl = $settingsArray['facebook_url'] ?? 'https://www.facebook.com/profile.php?id=100091517055172';
$instagramUrl = $settingsArray['instagram_url'] ?? 'https://www.instagram.com/framer.wedding/';
$youtubeUrl = $settingsArray['youtube_url'] ?? 'https://www.youtube.com/channel/UCmAlhSDX7kyi2eYllgitGRw';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packages - <?php echo htmlspecialchars($siteTitle); ?></title>
    <!-- Bootstrap 5 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet">
    <!-- Google Fonts for Bengali text -->
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .bengali-text, .bengali-font {
            font-family: 'Hind Siliguri', sans-serif;
        }
        body {
            background-color: #ffffff;
            color: #111111;
        }
        /* navbar – pure black */
        .navbar-frame {
            background-color: #000000 !important;
            padding: 0.4rem 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1030;
        }
        
        .brand-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo-img {
            height: 60px;
            width: auto;
            display: block;
        }
        
        .company-name {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            letter-spacing: 1px;
            line-height: 1;
        }
        
        .company-name-bengali {
            font-family: 'Hind Siliguri', sans-serif;
            font-size: 1.4rem;
            font-weight: 600;
            color: #cccccc;
            line-height: 1;
        }
        
        .dash-menu {
            background: transparent;
            border: none;
            font-size: 2rem;
            line-height: 1;
            color: white;
            padding: 0 4px;
        }
        .dash-menu i { 
            font-size: 2.2rem; 
            color: white; 
        }

        /* Offcanvas menu */
        .offcanvas.bg-black {
            background-color: #000000 !important;
            color: #ffffff;
        }
        .offcanvas-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        .offcanvas-body .nav {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .offcanvas-body .nav-link {
            color: #dddddd !important;
            font-size: 1.3rem;
            font-weight: 500;
            padding: 0.8rem 2rem;
            margin: 0.2rem 0;
            transition: all 0.3s ease-in-out;
            text-align: center;
        }
        .offcanvas-body .nav-link:hover {
            color: #ffffff !important;
            transform: scale(1.05);
            letter-spacing: 1.2px;
        }

        /* Page Title */
        .page-title {
            text-align: center;
            margin: 120px 0 30px;
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            position: relative;
        }
        .page-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: black;
            margin: 15px auto 0;
        }

        /* Category Filter Bar */
        .category-bar {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin: 30px 0 40px;
            padding: 15px 0;
            border-top: 2px solid #eee;
            border-bottom: 2px solid #eee;
        }
        
        .category-btn {
            background: transparent;
            border: 2px solid transparent;
            color: #555;
            padding: 10px 25px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0;
            position: relative;
        }
        
        .category-btn:hover {
            color: #000;
            border-bottom-color: #000;
        }
        
        .category-btn.active {
            color: #000;
            border-bottom: 2px solid #000;
            font-weight: 700;
        }
        
        .category-btn .count {
            display: inline-block;
            background: #f0f0f0;
            color: #555;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: 8px;
        }

        /* Package Cards */
        .package-card {
            background: white;
            border: 2px solid #111;
            padding: 2rem;
            box-shadow: 8px 8px 0 rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            margin-bottom: 30px;
        }
        
        .package-card:hover {
            transform: translate(-5px, -5px);
            box-shadow: 12px 12px 0 rgba(0,0,0,0.15);
        }
        
        .package-card.featured {
            border: 3px solid #000;
            background: white;
            box-shadow: 10px 10px 0 rgba(0,0,0,0.2);
        }
        
        .featured-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: gold;
            color: black;
            padding: 5px 15px;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }
        
        .package-category {
            display: inline-block;
            background: #f0f0f0;
            color: #555;
            padding: 3px 10px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }
        
        .package-name {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .package-price {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.2rem;
            color: #000;
        }
        
        .package-duration {
            font-size: 1rem;
            color: #666;
            margin-bottom: 1.5rem;
        }
        
        .package-description {
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        .package-features {
            list-style: none;
            padding: 0;
            margin-bottom: 2rem;
            flex-grow: 1;
        }
        
        .package-features li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
        }
        
        .package-features li:before {
            content: "✓";
            margin-right: 10px;
            color: #111;
            font-weight: 700;
        }
        
        .btn-package {
            border: 2px solid #111;
            background: #111;
            color: white;
            padding: 12px;
            font-weight: 700;
            transition: all 0.3s;
            width: 100%;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-package:hover {
            background: white;
            color: #111;
            text-decoration: none;
        }
        
        .btn-package i {
            margin-right: 8px;
        }

        /* Footer */
        .framer-footer {
            background-color: #000000;
            color: #ffffff;
            padding: 4rem 0 0 0;
            border-top: 4px solid #222;
            margin-top: 60px;
        }
        
        .footer-logo-area {
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .footer-logo {
            height: 100px;
            width: auto;
        }
        
        .footer-company-name {
            font-size: 3rem;
            font-weight: 800;
            letter-spacing: 2px;
            line-height: 1;
            color: white;
            margin-bottom: 0.5rem;
        }
        
        .footer-company-name-bengali {
            font-family: 'Hind Siliguri', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            line-height: 1.2;
        }
        
        .footer-address {
            color: #cccccc;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1rem;
            max-width: 400px;
        }
        
        .footer-hours {
            color: #ffffff;
            font-weight: 600;
            background: rgba(255,255,255,0.1);
            padding: 0.5rem 1rem;
            display: inline-block;
            margin-bottom: 1rem;
        }
        
        .footer-map {
            border: 2px solid #333;
            margin-bottom: 2rem;
            height: 200px;
            overflow: hidden;
        }
        
        .footer-map iframe {
            width: 100%;
            height: 100%;
            filter: grayscale(100%) invert(90%);
        }
        
        .footer-heading {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: white;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        .footer-heading::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: white;
        }
        
        .footer-social-links {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .footer-social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: transparent;
            border: 2px solid #444;
            color: white;
            font-size: 1.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
            border-radius: 50%;
        }
        
        .footer-social-link:hover {
            background: white;
            color: black;
            border-color: white;
            transform: translateY(-5px);
        }
        
        .footer-contact-info {
            margin-bottom: 2rem;
        }
        
        .footer-contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            color: #cccccc;
        }
        
        .footer-contact-item i {
            font-size: 1.3rem;
            color: white;
            width: 30px;
        }
        
        .footer-contact-item a {
            color: #cccccc;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-contact-item a:hover {
            color: white;
        }
        
        .footer-policies {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .footer-policies a {
            color: #cccccc;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.3s;
            position: relative;
        }
        
        .footer-policies a:hover {
            color: white;
        }
        
        .footer-policies a::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 0;
            height: 1px;
            background: white;
            transition: width 0.3s;
        }
        
        .footer-policies a:hover::after {
            width: 100%;
        }
        
        .footer-copyright {
            background: #111;
            color: #888;
            text-align: center;
            padding: 1.5rem;
            font-size: 0.9rem;
            border-top: 1px solid #222;
            margin-top: 2rem;
        }
        
        .footer-copyright span {
            color: white;
            font-weight: 600;
        }
        
        .footer-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #444, transparent);
            margin: 2rem 0;
        }

        /* Floating WhatsApp Chat Button */
        .whatsapp-chat-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 70px;
            height: 70px;
            background-color: #25D366;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            z-index: 9999;
            text-decoration: none;
            border: 2px solid white;
        }
        
        .whatsapp-chat-btn:hover {
            transform: scale(1.1);
            background-color: #128C7E;
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
            color: white;
        }
        
        .whatsapp-tooltip {
            position: fixed;
            bottom: 110px;
            right: 30px;
            background-color: #333;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 9998;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
            white-space: nowrap;
        }
        
        .whatsapp-chat-btn:hover + .whatsapp-tooltip {
            opacity: 1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
                margin: 100px 0 20px;
            }
            .category-btn {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
            .package-card {
                padding: 1.5rem;
            }
            .package-name {
                font-size: 1.5rem;
            }
            .package-price {
                font-size: 1.8rem;
            }
        }
        
        @media (max-width: 576px) {
            .page-title {
                font-size: 1.8rem;
                margin: 90px 0 15px;
            }
            .package-card {
                padding: 1.2rem;
            }
        }
    </style>
</head>
<body>

    <!-- Floating WhatsApp Chat Button -->
    <a href="https://wa.me/<?php echo $whatsappNumber; ?>?text=Hi%20Framer!%20I%20have%20a%20question%20about%20your%20photography%20services." 
       class="whatsapp-chat-btn" 
       target="_blank" 
       rel="noopener noreferrer"
       aria-label="Chat with us on WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>
    <div class="whatsapp-tooltip">Chat with us on WhatsApp</div>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand navbar-frame fixed-top">
        <div class="container-fluid px-2 px-md-3">
            <div class="brand-container">
                <a class="navbar-brand p-0" href="index.php">
                    <img src="logo.png" alt="Framer" class="logo-img">
                </a>
                <div>
                    <div class="company-name">FRAMER</div>
                    <div class="company-name-bengali">ফ্রেমার</div>
                </div>
            </div>
            <button class="dash-menu" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu" aria-label="Menu">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </nav>

    <!-- OFFCANVAS MENU -->
    <div class="offcanvas offcanvas-end bg-black" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title text-white fs-5">FRAMER</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column">
            <div class="nav flex-column">
                <a class="nav-link" href="index.php#about">About Framer</a>
                <a class="nav-link" href="index.php#why-us">Why Us</a>
                <a class="nav-link" href="gallery.php">Gallery</a>
                <a class="nav-link" href="packages.php">Packages</a>
                <a class="nav-link" href="index.php#contact">Contact</a>
                <a class="nav-link" href="index.php#social">Social Media</a>
                <a class="nav-link" href="index.php#blog">Blog</a>
                <a class="nav-link" href="index.php#faq">FAQ</a>
            </div>
            <div class="mt-auto p-3 small text-secondary border-top border-secondary">
                <span class="text-white-50">© framer / monochrome</span>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <main>

        <!-- PAGE TITLE -->
        <h1 class="page-title">
            <?php if ($currentCategory): ?>
                <?php echo htmlspecialchars(ucfirst($currentCategory)); ?> Packages
            <?php else: ?>
                Our Photography Packages
            <?php endif; ?>
        </h1>

        <!-- CATEGORY FILTER BAR -->
        <div class="container">
            <div class="category-bar">
                <a href="packages.php" class="category-btn <?php echo !$category || $category === 'all' ? 'active' : ''; ?>">
                    All Packages
                    <span class="count"><?php echo count($package->getActive()); ?></span>
                </a>
                <?php 
                // Function to get currency symbol
                function getCurrencySymbol($currency) {
                    switch($currency) {
                        case 'BDT': return '৳';
                        case 'USD': return '$';
                        case 'EUR': return '€';
                        default: return '$';
                    }
                }
                
                foreach ($categories as $cat): 
                    if (!empty($cat['category'])):
                ?>
                    <a href="packages.php?category=<?php echo urlencode($cat['category']); ?>" 
                       class="category-btn <?php echo $currentCategory == $cat['category'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars(ucfirst($cat['category'])); ?>
                        <span class="count"><?php echo $cat['count']; ?></span>
                    </a>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
        </div>

        <!-- PACKAGES GRID -->
        <div class="container">
            <?php if (!empty($packages)): ?>
                <div class="row g-4">
                    <?php foreach ($packages as $package): 
                        $features = is_string($package['features']) ? json_decode($package['features'], true) : $package['features'];
                        $currencySymbol = getCurrencySymbol($package['currency']);
                    ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="package-card <?php echo $package['is_featured'] ? 'featured' : ''; ?>">
                                <?php if ($package['is_featured']): ?>
                                    <div class="featured-badge">FEATURED</div>
                                <?php endif; ?>
                                
                                <?php if (!empty($package['category'])): ?>
                                    <div class="package-category"><?php echo htmlspecialchars($package['category']); ?></div>
                                <?php endif; ?>
                                
                                <div class="package-name"><?php echo htmlspecialchars($package['package_name']); ?></div>
                                
                                <div class="package-price">
                                    <span class="currency-symbol"><?php echo $currencySymbol; ?></span>
                                    <?php echo number_format($package['price'], 0); ?>
                                </div>
                                <div class="package-duration"><?php echo htmlspecialchars($package['duration']); ?></div>
                                
                                <div class="package-description">
                                    <?php echo nl2br(htmlspecialchars($package['description'])); ?>
                                </div>
                                
                                <?php if ($features && is_array($features)): ?>
                                    <ul class="package-features">
                                        <?php foreach ($features as $feature): ?>
                                            <li><?php echo htmlspecialchars(trim($feature)); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                
                                <a href="https://wa.me/<?php echo $whatsappNumber; ?>?text=Hi%20Framer!%20I'm%20interested%20in%20your%20package%20<?php echo urlencode($package['package_code']); ?>.%20Please%20tell%20me%20more." 
                                   class="btn-package" target="_blank">
                                    <i class="bi bi-whatsapp"></i> Book Now
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="text-muted">No packages found<?php echo $currentCategory ? ' in this category' : ''; ?>.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="framer-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="footer-logo-area">
                        <img src="logo.png" alt="Framer" class="footer-logo">
                        <div>
                            <div class="footer-company-name">FRAMER</div>
                            <div class="footer-company-name-bengali">ফ্রেমার</div>
                        </div>
                    </div>
                    
                    <div class="footer-address">
                        <strong>Office Address:</strong><br>
                        <?php echo nl2br(htmlspecialchars($address)); ?>
                    </div>
                    
                    <div class="footer-hours">
                        <i class="bi bi-clock me-2"></i><?php echo htmlspecialchars($businessHours); ?>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h3 class="footer-heading">Quick Links</h3>
                    <ul class="list-unstyled">
                        <li><a href="index.php#about" class="text-white-50 text-decoration-none">About Us</a></li>
                        <li><a href="gallery.php" class="text-white-50 text-decoration-none">Gallery</a></li>
                        <li><a href="packages.php" class="text-white-50 text-decoration-none">Packages</a></li>
                        <li><a href="index.php#contact" class="text-white-50 text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4">
                    <h3 class="footer-heading">Follow Us</h3>
                    <div class="footer-social-links">
                        <a href="<?php echo $facebookUrl; ?>" target="_blank" class="footer-social-link">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="<?php echo $instagramUrl; ?>" target="_blank" class="footer-social-link">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="<?php echo $youtubeUrl; ?>" target="_blank" class="footer-social-link">
                            <i class="bi bi-youtube"></i>
                        </a>
                        <a href="https://wa.me/<?php echo $whatsappNumber; ?>" target="_blank" class="footer-social-link" style="background-color:#25D366; color:white; border-color:#25D366;">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                    
                    <div class="footer-contact-info">
                        <div class="footer-contact-item">
                            <i class="bi bi-envelope-fill"></i>
                            <a href="mailto:<?php echo $contactEmail; ?>"><?php echo $contactEmail; ?></a>
                        </div>
                        <div class="footer-contact-item">
                            <i class="bi bi-telephone-fill"></i>
                            <a href="tel:<?php echo $contactPhone; ?>"><?php echo $contactPhone; ?></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-divider"></div>
            
            <div class="row">
                <div class="col-12">
                    <div class="footer-policies">
                        <a href="#">Terms and Condition</a>
                        <a href="#">Privacy Policy</a>
                        <a href="#">Refund and Return Policy</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-copyright">
            <div class="container">
                Copyright &copy; <span>Framer <?php echo date('Y'); ?></span> | All Rights Reserved
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Handle offcanvas menu
        const menuItems = document.querySelectorAll('.offcanvas-body .nav-link');
        const offcanvasElement = document.getElementById('offcanvasMenu');
        const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement) || new bootstrap.Offcanvas(offcanvasElement);
        
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                offcanvas.hide();
            });
        });
    </script>
</body>
</html>