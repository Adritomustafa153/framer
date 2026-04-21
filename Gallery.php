<?php
// gallery.php
require_once 'config/database.php';
require_once 'models/Gallery.php';
require_once 'models/Photographer.php';
require_once 'models/Settings.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get photographer ID from URL
$photographer_id = isset($_GET['photographer']) ? (int)$_GET['photographer'] : null;

// Get gallery images
$gallery = new Gallery($db);
if ($photographer_id) {
    $galleryImages = $gallery->getByPhotographer($photographer_id);
    $photographerModel = new Photographer($db);
    $currentPhotographer = $photographerModel->getById($photographer_id);
} else {
    $galleryImages = $gallery->getActive();
    $currentPhotographer = null;
}

// Get all photographers for filter
$photographerModel = new Photographer($db);
$photographers = $photographerModel->getWithImageCount();

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
$slideshowMusicUrl = $settingsArray['slideshow_music_url'] ?? '';
$slideshowDelay = $settingsArray['slideshow_delay'] ?? '3000';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - <?php echo htmlspecialchars($siteTitle); ?></title>
    <!-- Bootstrap 5 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
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

        /* Photographer Filter Bar */
        .photographer-bar {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin: 30px 0 40px;
            padding: 15px 0;
            border-top: 2px solid #eee;
            border-bottom: 2px solid #eee;
        }
        
        .photographer-btn {
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
        
        .photographer-btn:hover {
            color: #000;
            border-bottom-color: #000;
        }
        
        .photographer-btn.active {
            color: #000;
            border-bottom: 2px solid #000;
            font-weight: 700;
        }
        
        .photographer-btn .count {
            display: inline-block;
            background: #f0f0f0;
            color: #555;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: 8px;
        }

        /* Gallery Grid - 5 per row */
        .gallery-container {
            margin: 0 -8px;
        }
        
        .gallery-item {
            padding: 8px !important;
        }
        
        .gallery-card {
            position: relative;
            overflow: hidden;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            background: #f5f5f5;
            aspect-ratio: 1 / 1;
        }
        
        .gallery-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        
        .gallery-image-container {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
        }
        
        .gallery-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.6s ease;
        }
        
        .gallery-card:hover .gallery-image {
            transform: scale(1.05);
        }
        
        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .gallery-card:hover .gallery-overlay {
            opacity: 1;
        }
        
        .gallery-overlay button {
            background: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #111;
            font-size: 1.5rem;
        }
        
        .gallery-overlay button:hover {
            transform: scale(1.1);
            background: #111;
            color: white;
        }

        /* Lightbox */
        .lightbox {
            display: none;
            position: fixed;
            z-index: 9999;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.95);
            backdrop-filter: blur(5px);
        }
        
        .lightbox.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 40px;
            cursor: pointer;
            z-index: 10000;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .lightbox-prev, .lightbox-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 50px;
            cursor: pointer;
            padding: 20px;
            z-index: 10000;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            transition: all 0.3s;
        }
        
        .lightbox-prev { left: 30px; }
        .lightbox-next { right: 30px; }
        
        .lightbox-prev:hover, .lightbox-next:hover {
            transform: translateY(-50%) scale(1.2);
            color: #ddd;
        }
        
        .lightbox-content {
            max-width: 90%;
            max-height: 80%;
            text-align: center;
        }
        
        .lightbox-content img {
            max-width: 100%;
            max-height: 70vh;
            border: 3px solid white;
            box-shadow: 0 0 30px rgba(0,0,0,0.5);
            border-radius: 4px;
        }
        
        .lightbox-controls {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 15px;
            z-index: 10000;
        }
        
        .lightbox-controls button {
            background: rgba(255,255,255,0.2);
            border: 1px solid white;
            color: white;
            padding: 12px 25px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .lightbox-controls button:hover {
            background: white;
            color: black;
            transform: translateY(-2px);
        }
        
        .lightbox-controls button i {
            font-size: 1.2rem;
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

        /* Responsive */
        @media (max-width: 1200px) {
            .gallery-item {
                width: 20%;
            }
        }
        
        @media (max-width: 992px) {
            .gallery-item {
                width: 25%;
            }
            .page-title {
                font-size: 2rem;
                margin: 100px 0 20px;
            }
        }
        
        @media (max-width: 768px) {
            .gallery-item {
                width: 33.333%;
            }
            .page-title {
                font-size: 1.8rem;
                margin: 90px 0 15px;
            }
            .photographer-btn {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 576px) {
            .gallery-item {
                width: 50%;
            }
            .lightbox-prev, .lightbox-next {
                font-size: 30px;
                padding: 10px;
            }
            .lightbox-controls button {
                padding: 8px 15px;
                font-size: 0.9rem;
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
                <a class="nav-link" href="index.php#packages">Packages</a>
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
            <?php if ($currentPhotographer): ?>
                <?php echo htmlspecialchars($currentPhotographer['name']); ?>'s Gallery
            <?php else: ?>
                Framer Gallery
            <?php endif; ?>
        </h1>

        <!-- PHOTOGRAPHER FILTER BAR -->
        <div class="container">
            <div class="photographer-bar">
                <a href="gallery.php" class="photographer-btn <?php echo !$photographer_id ? 'active' : ''; ?>">
                    All
                    <span class="count"><?php echo count($gallery->getActive()); ?></span>
                </a>
                <?php foreach ($photographers as $p): ?>
                    <a href="gallery.php?photographer=<?php echo $p['id']; ?>" 
                       class="photographer-btn <?php echo $photographer_id == $p['id'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($p['name']); ?>
                        <span class="count"><?php echo $p['image_count']; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- GALLERY GRID -->
        <div class="container-fluid px-3">
            <?php if (!empty($galleryImages)): ?>
                <div class="row g-0 gallery-container">
                    <?php foreach ($galleryImages as $index => $image): 
                        $imageUrl = $image['image_url'];
                        $thumbUrl = $image['thumbnail_url'] ?: $image['image_url'];
                    ?>
                        <div class="col-lg-2 col-md-3 col-sm-4 col-6 px-2 gallery-item">
                            <div class="gallery-card">
                                <div class="gallery-image-container">
                                    <img src="<?php echo htmlspecialchars($thumbUrl); ?>" 
                                         alt="<?php echo htmlspecialchars($image['title']); ?>"
                                         class="gallery-image"
                                         data-id="<?php echo $image['id']; ?>"
                                         data-full="<?php echo htmlspecialchars($imageUrl); ?>">
                                    <div class="gallery-overlay">
                                        <button onclick="openLightbox(<?php echo $image['id']; ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button onclick="downloadImage(<?php echo $image['id']; ?>, '<?php echo addslashes($imageUrl); ?>')">
                                            <i class="bi bi-download"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="text-muted">No images found<?php echo $photographer_id ? ' for this photographer' : ''; ?>.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- LIGHTBOX -->
        <div id="gallery-lightbox" class="lightbox">
            <span class="lightbox-close">&times;</span>
            <span class="lightbox-prev">&#10094;</span>
            <span class="lightbox-next">&#10095;</span>
            <div class="lightbox-content">
                <img id="lightbox-image" src="" alt="">
            </div>
            <div class="lightbox-controls">
                <button id="slideshow-play">
                    <i class="bi bi-play-fill"></i> Play Slideshow
                </button>
                <button id="slideshow-stop" style="display: none;">
                    <i class="bi bi-stop-fill"></i> Stop
                </button>
                <button id="download-btn" onclick="downloadCurrentImage()">
                    <i class="bi bi-download"></i> Download
                </button>
            </div>
        </div>

        <!-- AUDIO FOR SLIDESHOW -->
        <?php if (!empty($slideshowMusicUrl)): ?>
        <audio id="slideshow-audio" loop style="display: none;">
            <source src="<?php echo htmlspecialchars($slideshowMusicUrl); ?>" type="audio/mpeg">
        </audio>
        <?php endif; ?>

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
                        <li><a href="index.php#packages" class="text-white-50 text-decoration-none">Packages</a></li>
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

    <!-- Floating WhatsApp Chat Button CSS -->
    <style>
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
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Gallery data
        let galleryImages = [];
        let currentImageIndex = 0;
        let slideshowInterval = null;
        
        <?php if (!empty($galleryImages)): ?>
            galleryImages = [
                <?php foreach ($galleryImages as $image): ?>
                    {
                        id: <?php echo $image['id']; ?>,
                        url: '<?php echo addslashes($image['image_url']); ?>'
                    },
                <?php endforeach; ?>
            ];
        <?php endif; ?>
        
        // Open lightbox
        function openLightbox(id) {
            const index = galleryImages.findIndex(img => img.id === id);
            if (index !== -1) {
                currentImageIndex = index;
                updateLightboxImage();
                document.getElementById('gallery-lightbox').classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }
        
        // Update lightbox image
        function updateLightboxImage() {
            const img = galleryImages[currentImageIndex];
            document.getElementById('lightbox-image').src = img.url;
        }
        
        // Download image
        function downloadImage(id, url) {
            // Increment download count via AJAX
            fetch('download-image.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    // Create a temporary link to download
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = url.split('/').pop() || 'image.jpg';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                })
                .catch(error => {
                    // Fallback if AJAX fails
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = url.split('/').pop() || 'image.jpg';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });
        }
        
        // Download current image from lightbox
        function downloadCurrentImage() {
            const img = galleryImages[currentImageIndex];
            if (img) {
                downloadImage(img.id, img.url);
            }
        }
        
        // Close lightbox
        document.querySelector('.lightbox-close').addEventListener('click', function() {
            document.getElementById('gallery-lightbox').classList.remove('active');
            document.body.style.overflow = 'auto';
            stopSlideshow();
        });
        
        // Previous image
        document.querySelector('.lightbox-prev').addEventListener('click', function() {
            currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
            updateLightboxImage();
        });
        
        // Next image
        document.querySelector('.lightbox-next').addEventListener('click', function() {
            currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
            updateLightboxImage();
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (!document.getElementById('gallery-lightbox').classList.contains('active')) return;
            
            if (e.key === 'Escape') {
                document.getElementById('gallery-lightbox').classList.remove('active');
                document.body.style.overflow = 'auto';
                stopSlideshow();
            } else if (e.key === 'ArrowLeft') {
                currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
                updateLightboxImage();
            } else if (e.key === 'ArrowRight') {
                currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
                updateLightboxImage();
            }
        });
        
        // Slideshow functions
        function startSlideshow() {
            const audio = document.getElementById('slideshow-audio');
            if (audio) {
                audio.play().catch(e => console.log('Audio autoplay failed:', e));
            }
            
            document.getElementById('slideshow-play').style.display = 'none';
            document.getElementById('slideshow-stop').style.display = 'inline-block';
            
            slideshowInterval = setInterval(function() {
                currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
                updateLightboxImage();
            }, <?php echo $slideshowDelay; ?>);
        }
        
        function stopSlideshow() {
            const audio = document.getElementById('slideshow-audio');
            if (audio) {
                audio.pause();
                audio.currentTime = 0;
            }
            
            document.getElementById('slideshow-play').style.display = 'inline-block';
            document.getElementById('slideshow-stop').style.display = 'none';
            
            if (slideshowInterval) {
                clearInterval(slideshowInterval);
                slideshowInterval = null;
            }
        }
        
        document.getElementById('slideshow-play')?.addEventListener('click', startSlideshow);
        document.getElementById('slideshow-stop')?.addEventListener('click', stopSlideshow);

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