<?php
// index.php
require_once 'config/database.php';
require_once 'models/Package.php';
require_once 'models/Blog.php';
require_once 'models/Settings.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get packages from database
$package = new Package($db);
$packages = $package->getAll('is_featured DESC, sort_order ASC, created_at DESC');

// Get blog posts
$blog = new Blog($db);
$blogPosts = $blog->getPublished();

// Get settings as key-value pairs
$settings = new Settings($db);
$settingsArray = $settings->getAllAsArray();

// Set default values if settings don't exist
$siteTitle = $settingsArray['site_title'] ?? 'Framer Photography';
$contactEmail = $settingsArray['contact_email'] ?? 'framer.wedding@gmail.com';
$contactPhone = $settingsArray['contact_phone'] ?? '+8801829093616';
$whatsappNumber = $settingsArray['whatsapp_number'] ?? '8801829093616';
$address = $settingsArray['address'] ?? 'Rajonigondha Vally, 178/B Khilgaon Chowdhurypara, Matirmoshjheed jheelpar, Dhaka 1219';
$businessHours = $settingsArray['business_hours'] ?? 'Monday - Saturday (9am to 7pm)';
$facebookUrl = $settingsArray['facebook_url'] ?? 'https://www.facebook.com/profile.php?id=100091517055172';
$instagramUrl = $settingsArray['instagram_url'] ?? 'https://www.instagram.com/framer.wedding/';
$youtubeUrl = $settingsArray['youtube_url'] ?? 'https://www.youtube.com/channel/UCmAlhSDX7kyi2eYllgitGRw';
$mapEmbedUrl = $settingsArray['map_embed_url'] ?? 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3651.767150611156!2d90.4158229749629!3d23.75568147866682!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x265d1f3a711b2773%3A0xf6aef7cf05e4bba4!2sFramer!5e0!3m2!1sen!2sbd!4v1771669824226!5m2!1sen!2sbd';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($siteTitle); ?></title>
    <!-- Bootstrap 5 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts (clean sans) -->
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
            scroll-behavior: smooth;
        }
        /* navbar – pure black */
        .navbar-frame {
            background-color: #000000 !important;
            padding: 0.4rem 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1030;
        }
        /* logo image */
        .logo-img {
            height: 80px;
            width: auto;
            display: block;
        }
        /* three‑dash menu button (hamburger) */
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
        .dash-menu:focus { outline: none; box-shadow: none; }

        /* offcanvas black theme – centered items with hover effect */
        .offcanvas.bg-black {
            background-color: #000000 !important;
            color: #ffffff;
        }
        .offcanvas-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        .offcanvas-body {
            display: flex;
            flex-direction: column;
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
            border-bottom: 1px solid transparent;
            transition: all 0.3s ease-in-out;
            position: relative;
            width: auto;
            text-align: center;
            cursor: pointer;
        }
        .offcanvas-body .nav-link:hover {
            color: #ffffff !important;
            transform: scale(1.05);
            letter-spacing: 1.2px;
            border-bottom: 1px solid #ffffff;
            background-color: transparent;
        }
        .offcanvas-body .nav-link:active {
            transform: scale(0.98);
            transition: 0.1s;
        }

        /* page sections – centered headings */
        section {
            scroll-margin-top: 90px;
            padding: 4rem 1.5rem;
            border-bottom: 1px solid #eaeaea;
        }
        section:nth-child(even) { background-color: #f9f9f9; }
        section:nth-child(odd) { background-color: #ffffff; }

        /* all section headings centered */
        .section-title {
            font-size: 2.2rem;
            font-weight: 650;
            letter-spacing: -0.02em;
            margin-bottom: 2.5rem;
            text-align: center;
            width: 100%;
            position: relative;
        }
        .section-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: black;
            margin: 0.8rem auto 0;
        }

        /* slider / hero area */
        .hero-slider {
            width: 100%;
            background: #000;
        }
        .carousel-item {
            height: 85vh;
            min-height: 500px;
            background-color: #111;
        }
        .carousel-item img {
            object-fit: cover;
            height: 100%;
            width: 100%;
            opacity: 0.9;
        }
        
        p, li, .badge-bw, .btn {
            font-size: 0.95rem;
        }
        h2, h3, h4 { font-weight: 600; }

        /* Contact Section Styles */
        .contact-info-card {
            background: white;
            border: 1px solid #111;
            padding: 2rem;
            box-shadow: 6px 6px 0 rgba(0,0,0,0.1);
            height: 100%;
        }
        
        .contact-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .contact-info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .contact-icon {
            width: 50px;
            height: 50px;
            background: black;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.2rem;
            font-size: 1.5rem;
        }
        
        .contact-details h4 {
            font-size: 1.1rem;
            margin-bottom: 0.3rem;
            font-weight: 700;
        }
        
        .contact-details p {
            margin-bottom: 0;
            color: #333;
            font-size: 1rem;
        }
        
        .contact-details a {
            color: #333;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .contact-details a:hover {
            color: black;
            text-decoration: underline;
        }
        
        /* Map Container */
        .map-container {
            border: 1px solid #111;
            box-shadow: 6px 6px 0 rgba(0,0,0,0.1);
            overflow: hidden;
            height: 100%;
            min-height: 350px;
        }
        
        .map-container iframe {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Package Card Styles */
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
        }
        
        .package-card:hover {
            transform: translate(-5px, -5px);
            box-shadow: 12px 12px 0 rgba(0,0,0,0.15);
        }
        
        .package-card.featured {
            background: #111;
            color: white;
            border-color: #fff;
            box-shadow: 10px 10px 0 rgba(0,0,0,0.3);
        }
        
        .package-card.featured .package-price,
        .package-card.featured .package-duration {
            color: #ddd;
        }
        
        .package-card.featured .btn-outline-dark {
            border-color: white;
            color: white;
        }
        
        .package-card.featured .btn-outline-dark:hover {
            background: white;
            color: black;
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
        
        .package-name {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .package-code {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 1rem;
        }
        
        .package-card.featured .package-code {
            color: #aaa;
        }
        
        .package-price {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.2rem;
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
            border-bottom: 1px dashed #eee;
            display: flex;
            align-items: center;
        }
        
        .package-features li:before {
            content: "✓";
            margin-right: 10px;
            color: #111;
            font-weight: 700;
        }
        
        .package-card.featured .package-features li:before {
            color: gold;
        }
        
        .package-features li:last-child {
            border-bottom: none;
        }
        
        .btn-package {
            border: 2px solid #111;
            background: transparent;
            padding: 12px;
            font-weight: 700;
            transition: all 0.3s;
            width: 100%;
            cursor: pointer;
        }
        
        .btn-package:hover {
            background: #111;
            color: white;
        }

        /* Blog Card Styles */
        .blog-card {
            background: white;
            border: 1px solid #111;
            padding: 1.8rem;
            box-shadow: 6px 6px 0 rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .blog-card:hover {
            transform: translate(-3px, -3px);
            box-shadow: 10px 10px 0 rgba(0,0,0,0.15);
        }
        
        .blog-card .date {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }
        
        .blog-card .blog-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.4;
        }
        
        .blog-card .blog-excerpt {
            color: #444;
            margin-bottom: 1.5rem;
            flex-grow: 1;
            line-height: 1.6;
        }
        
        .blog-card .read-more {
            background: transparent;
            border: 1px solid #111;
            color: #111;
            padding: 0.6rem 1.5rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: inline-block;
            width: fit-content;
            cursor: pointer;
        }
        
        .blog-card .read-more:hover {
            background: #111;
            color: white;
        }
        
        .blog-excerpt.bengali {
            font-size: 1rem;
            line-height: 1.7;
        }
        
        /* Social Media Icons */
        .social-icon-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background: white;
            border: 1px solid #111;
            color: #111;
            font-size: 2rem;
            transition: all 0.3s ease;
            text-decoration: none;
            margin: 0 0.5rem;
        }
        
        .social-icon-link:hover {
            background: black;
            color: white;
            transform: translate(-3px, -3px);
            box-shadow: 5px 5px 0 rgba(0,0,0,0.1);
        }
        
        .badge-bw {
            background: black;
            color: white;
            padding: 0.2rem 1rem;
            font-weight: 600;
            font-size: 0.8rem;
        }
        
        /* Footer Styles */
        .framer-footer {
            background-color: #000000;
            color: #ffffff;
            padding: 4rem 0 0 0;
            border-top: 4px solid #222;
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
        
        .whatsapp-chat-btn i {
            line-height: 1;
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
        
        .currency-symbol {
            font-size: 1.2rem;
            margin-right: 2px;
        }
        
        @media (max-width: 768px) {
            .navbar-frame { padding: 0.3rem 1rem; }
            .logo-img { height: 36px; }
            .dash-menu i { font-size: 1.8rem; }
            .carousel-item { height: 60vh; }
            .section-title { font-size: 1.8rem; }
            .offcanvas-body .nav-link { font-size: 1.1rem; }
            .contact-info-card { margin-top: 2rem; }
            .map-container { min-height: 300px; }
            .social-icon-link {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
                margin: 0 0.25rem;
            }
            .footer-company-name { font-size: 2rem; }
            .footer-company-name-bengali { font-size: 2rem; }
            .footer-logo-area { flex-direction: column; gap: 1rem; }
            .whatsapp-chat-btn {
                width: 60px;
                height: 60px;
                font-size: 2rem;
                bottom: 20px;
                right: 20px;
            }
            .whatsapp-tooltip {
                bottom: 95px;
                right: 20px;
                font-size: 0.8rem;
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
            <a class="navbar-brand p-0" href="#">
                <img src="logo.png" alt="Framer" class="logo-img">
            </a>
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
                <a class="nav-link menu-item" data-target="about">About Framer</a>
                <a class="nav-link menu-item" data-target="why-us">Why Us</a>
                <a class="nav-link menu-item" data-target="gallery">Gallery</a>
                <a class="nav-link menu-item" data-target="packages">Packages</a>
                <a class="nav-link menu-item" data-target="contact">Contact</a>
                <a class="nav-link menu-item" data-target="social">Social Media</a>
                <a class="nav-link menu-item" data-target="blog">Blog</a>
                <a class="nav-link menu-item" data-target="login">Login</a>
            </div>
            <div class="mt-auto p-3 small text-secondary border-top border-secondary">
                <span class="text-white-50">© framer / monochrome</span>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <main style="margin-top: 80px;">

        <!-- HERO SLIDER (6 images) -->
        <section id="hero-slider" class="p-0 m-0 border-0">
            <div id="framerCarousel" class="carousel slide hero-slider" data-bs-ride="carousel" data-bs-interval="4000">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#framerCarousel" data-bs-slide-to="0" class="active"></button>
                    <button type="button" data-bs-target="#framerCarousel" data-bs-slide-to="1"></button>
                    <button type="button" data-bs-target="#framerCarousel" data-bs-slide-to="2"></button>
                    <button type="button" data-bs-target="#framerCarousel" data-bs-slide-to="3"></button>
                    <button type="button" data-bs-target="#framerCarousel" data-bs-slide-to="4"></button>
                    <button type="button" data-bs-target="#framerCarousel" data-bs-slide-to="5"></button>
                </div>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="https://placecats.com/1600/900?random=1" class="d-block w-100" alt="slide 1">
                    </div>
                    <div class="carousel-item">
                        <img src="https://placecats.com/1600/901?random=2" class="d-block w-100" alt="slide 2">
                    </div>
                    <div class="carousel-item">
                        <img src="https://placecats.com/1600/902?random=3" class="d-block w-100" alt="slide 3">
                    </div>
                    <div class="carousel-item">
                        <img src="https://placecats.com/1600/903?random=4" class="d-block w-100" alt="slide 4">
                    </div>
                    <div class="carousel-item">
                        <img src="https://placecats.com/1600/904?random=5" class="d-block w-100" alt="slide 5">
                    </div>
                    <div class="carousel-item">
                        <img src="https://placecats.com/1600/905?random=6" class="d-block w-100" alt="slide 6">
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#framerCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#framerCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </section>

        <!-- ABOUT FRAMER -->
        <section id="about">
            <div class="container">
                <h2 class="section-title">Framer  । ফ্রেমার</h2>
                <div class="row justify-content-center">
                    <div class="col-md-8 text-center">
                        <p class="lead" style="font-size:1.1rem;">Framer is a photography studio built on contrast, shadow, and emotion. We tell stories without color — just pure light and dark. Based in NYC, serving worldwide.</p>
                        <span class="badge-bw">est. 2022</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- WHY US -->
        <section id="why-us">
            <div class="container">
                <h2 class="section-title">Why Us</h2>
                <div class="row g-4 justify-content-center">
                    <div class="col-md-4 col-sm-6">
                        <div class="p-4 border border-dark bg-white text-center h-100">
                            <h3 class="h5">⚫ Precision</h3>
                            <p class="small">Every pixel matters. We shoot, develop and frame with obsessive attention.</p>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="p-4 border border-dark bg-white text-center h-100">
                            <h3 class="h5">⬜ Timeless</h3>
                            <p class="small">Black and white never goes out of style. Your images stay modern.</p>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="p-4 border border-dark bg-white text-center h-100">
                            <h3 class="h5">◼️ Experience</h3>
                            <p class="small">Over a decade working with magazines, brands, and couples.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- GALLERY -->
        <section id="gallery">
            <div class="container">
                <h2 class="section-title">Gallery</h2>
                <div class="row g-3">
                    <div class="col-6 col-md-3"><div class="bg-black d-flex align-items-center justify-content-center text-white" style="height: 160px; background:#2a2a2a;">Portrait</div></div>
                    <div class="col-6 col-md-3"><div class="bg-black d-flex align-items-center justify-content-center text-white" style="height: 160px; background:#1e1e1e;">Street</div></div>
                    <div class="col-6 col-md-3"><div class="bg-black d-flex align-items-center justify-content-center text-white" style="height: 160px; background:#232323;">Studio</div></div>
                    <div class="col-6 col-md-3"><div class="bg-black d-flex align-items-center justify-content-center text-white" style="height: 160px; background:#282828;">Film</div></div>
                </div>
            </div>
        </section>

        <!-- PACKAGES - Dynamic from Database -->
        <section id="packages">
            <div class="container">
                <h2 class="section-title">Our Packages</h2>
                <div class="row g-4">
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

                    if ($packages && $packages->rowCount() > 0): 
                        while ($row = $packages->fetch()): 
                            $features = is_string($row['features']) ? json_decode($row['features'], true) : $row['features'];
                            $currencySymbol = getCurrencySymbol($row['currency']);
                    ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="package-card <?php echo $row['is_featured'] ? 'featured' : ''; ?>">
                                <?php if ($row['is_featured']): ?>
                                    <div class="featured-badge">FEATURED</div>
                                <?php endif; ?>
                                
                                <div class="package-name"><?php echo htmlspecialchars($row['package_name']); ?></div>
                                <div class="package-code"><?php echo htmlspecialchars($row['package_code']); ?></div>
                                
                                <div class="package-price">
                                    <span class="currency-symbol"><?php echo $currencySymbol; ?></span>
                                    <?php echo number_format($row['price'], 0); ?>
                                </div>
                                <div class="package-duration"><?php echo htmlspecialchars($row['duration']); ?></div>
                                
                                <div class="package-description">
                                    <?php echo nl2br(htmlspecialchars($row['description'])); ?>
                                </div>
                                
                                <?php if ($features && is_array($features)): ?>
                                    <ul class="package-features">
                                        <?php foreach ($features as $feature): ?>
                                            <li><?php echo htmlspecialchars(trim($feature)); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                
                                <button class="btn-package" onclick="bookPackage('<?php echo $row['package_code']; ?>')">
                                    Book Now
                                </button>
                            </div>
                        </div>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <div class="col-12 text-center">
                            <p class="text-muted">No packages available at the moment. Please check back later.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- CONTACT -->
        <section id="contact">
            <div class="container">
                <h2 class="section-title">Contact</h2>
                <div class="row g-4">
                    <!-- Left side: Map -->
                    <div class="col-lg-6">
                        <div class="map-container" style="height: 400px;">
                            <iframe src="<?php echo htmlspecialchars($mapEmbedUrl); ?>" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                    
                    <!-- Right side: Contact Info -->
                    <div class="col-lg-6">
                        <div class="contact-info-card">
                            <h3 class="h4 mb-4" style="font-weight: 700;">Get in Touch</h3>
                            
                            <!-- Email -->
                            <div class="contact-info-item">
                                <div class="contact-icon">
                                    <i class="bi bi-envelope-fill"></i>
                                </div>
                                <div class="contact-details">
                                    <h4>Email Us</h4>
                                    <p><a href="mailto:<?php echo $contactEmail; ?>"><?php echo $contactEmail; ?></a></p>
                                </div>
                            </div>
                            
                            <!-- Phone -->
                            <div class="contact-info-item">
                                <div class="contact-icon">
                                    <i class="bi bi-telephone-fill"></i>
                                </div>
                                <div class="contact-details">
                                    <h4>Call Us</h4>
                                    <p><a href="tel:<?php echo $contactPhone; ?>"><?php echo $contactPhone; ?></a></p>
                                </div>
                            </div>
                            
                            <!-- WhatsApp -->
                            <div class="contact-info-item">
                                <div class="contact-icon" style="background-color:#25D366;">
                                    <i class="bi bi-whatsapp"></i>
                                </div>
                                <div class="contact-details">
                                    <h4>WhatsApp</h4>
                                    <p><a href="https://wa.me/<?php echo $whatsappNumber; ?>" target="_blank"><?php echo $contactPhone; ?></a></p>
                                </div>
                            </div>
                            
                            <!-- Location -->
                            <div class="contact-info-item">
                                <div class="contact-icon">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </div>
                                <div class="contact-details">
                                    <h4>Visit Us</h4>
                                    <p><?php echo nl2br(htmlspecialchars($address)); ?></p>
                                </div>
                            </div>
                            
                            <!-- Business Hours -->
                            <div class="contact-info-item">
                                <div class="contact-icon">
                                    <i class="bi bi-clock-fill"></i>
                                </div>
                                <div class="contact-details">
                                    <h4>Business Hours</h4>
                                    <p><?php echo htmlspecialchars($businessHours); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SOCIAL MEDIA -->
        <section id="social">
            <div class="container">
                <h2 class="section-title">Follow Us</h2>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <!-- Facebook -->
                    <a href="<?php echo $facebookUrl; ?>" target="_blank" class="social-icon-link" rel="noopener noreferrer">
                        <i class="bi bi-facebook"></i>
                    </a>
                    
                    <!-- Instagram -->
                    <a href="<?php echo $instagramUrl; ?>" target="_blank" class="social-icon-link" rel="noopener noreferrer">
                        <i class="bi bi-instagram"></i>
                    </a>
                    
                    <!-- YouTube -->
                    <a href="<?php echo $youtubeUrl; ?>" target="_blank" class="social-icon-link" rel="noopener noreferrer">
                        <i class="bi bi-youtube"></i>
                    </a>
                    
                    <!-- WhatsApp -->
                    <a href="https://wa.me/<?php echo $whatsappNumber; ?>" target="_blank" class="social-icon-link" style="background-color:#25D366; color:white; border-color:#25D366;" rel="noopener noreferrer">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
                <p class="text-center mt-4 small text-muted">Connect with us on social media</p>
            </div>
        </section>

        <!-- BLOG - Dynamic from Database -->
        <section id="blog">
            <div class="container">
                <h2 class="section-title">Latest from Our Blog</h2>
                
                <?php if (!empty($blogPosts)): ?>
                    <div class="row g-4">
                        <?php 
                        $count = 0;
                        foreach ($blogPosts as $post): 
                            if ($count >= 5) break; // Show only 5 most recent posts
                            $excerpt = $post['excerpt'] ?: (isset($post['content']) ? substr(strip_tags($post['content']), 0, 150) . '...' : 'Read more...');
                        ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="blog-card">
                                    <div class="date"><?php echo date('F j, Y', strtotime($post['published_at'] ?: $post['created_at'])); ?></div>
                                    <h3 class="blog-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                    <div class="blog-excerpt <?php echo preg_match('/[ঀ-৿]/', $post['title']) ? 'bengali' : ''; ?>">
                                        <?php echo htmlspecialchars($excerpt); ?>
                                    </div>
                                    <button class="read-more" onclick="openArticle(<?php echo $post['id']; ?>)">Read More</button>
                                </div>
                            </div>
                        <?php 
                            $count++;
                        endforeach; 
                        ?>
                    </div>
                    
                    <!-- View All Blog Posts Link -->
                    <div class="text-center mt-5">
                        <a href="blog.php" class="text-dark text-decoration-none border-bottom border-dark" style="font-size:1.1rem;">View All Articles →</a>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <p class="text-muted">No blog posts available yet. Check back soon!</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- LOGIN -->
        <section id="login">
            <div class="container">
                <h2 class="section-title">Login</h2>
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="border border-dark p-4 bg-white">
                            <div class="mb-3"><input type="text" placeholder="username" class="form-control rounded-0 border-dark"></div>
                            <div class="mb-3"><input type="password" placeholder="password" class="form-control rounded-0 border-dark"></div>
                            <button class="btn btn-outline-dark rounded-0 w-100">→ sign in</button>
                            <div class="text-center mt-3">
                                <a href="admin/login.php" class="text-decoration-none small">Admin Login</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- BEAUTIFUL FOOTER -->
    <footer class="framer-footer">
        <div class="container">
            <!-- First Row: Logo + Company Name + Address + Hours -->
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
                
                <!-- Middle: Google Map -->
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h3 class="footer-heading">Our Location</h3>
                    <div class="footer-map">
                        <iframe src="<?php echo htmlspecialchars($mapEmbedUrl); ?>" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
                
                <!-- Right: Follow Us + Contact -->
                <div class="col-lg-4">
                    <h3 class="footer-heading">Follow Us</h3>
                    <div class="footer-social-links">
                        <a href="<?php echo $facebookUrl; ?>" target="_blank" class="footer-social-link" rel="noopener noreferrer">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="<?php echo $instagramUrl; ?>" target="_blank" class="footer-social-link" rel="noopener noreferrer">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="<?php echo $youtubeUrl; ?>" target="_blank" class="footer-social-link" rel="noopener noreferrer">
                            <i class="bi bi-youtube"></i>
                        </a>
                        <a href="https://wa.me/<?php echo $whatsappNumber; ?>" target="_blank" class="footer-social-link" style="background-color:#25D366; color:white; border-color:#25D366;" rel="noopener noreferrer">
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
                        <div class="footer-contact-item">
                            <i class="bi bi-whatsapp" style="color:#25D366;"></i>
                            <a href="https://wa.me/<?php echo $whatsappNumber; ?>" target="_blank">WhatsApp: <?php echo $contactPhone; ?></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Divider -->
            <div class="footer-divider"></div>
            
            <!-- Policies Row -->
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
        
        <!-- Copyright -->
        <div class="footer-copyright">
            <div class="container">
                Copyright &copy; <span>Framer <?php echo date('Y'); ?></span> | All Rights Reserved
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Blog article opener function
        function openArticle(articleId) {
            // Redirect to single blog post page (you'll need to create this)
            window.location.href = 'blog-post.php?id=' + articleId;
        }

        // Book package function
        function bookPackage(packageCode) {
            // Redirect to WhatsApp with package info
            window.location.href = 'https://wa.me/<?php echo $whatsappNumber; ?>?text=Hi%20Framer!%20I%27m%20interested%20in%20your%20package%20' + packageCode + '.%20Please%20tell%20me%20more.';
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Get all menu items
            const menuItems = document.querySelectorAll('.menu-item');
            
            // Get the offcanvas element
            const offcanvasElement = document.getElementById('offcanvasMenu');
            const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement) || new bootstrap.Offcanvas(offcanvasElement);
            
            // Add click event to each menu item
            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Get the target section id from data-target attribute
                    const targetId = this.getAttribute('data-target');
                    const targetSection = document.getElementById(targetId);
                    
                    if (targetSection) {
                        // Close the offcanvas menu first
                        offcanvas.hide();
                        
                        // Wait for offcanvas to close before scrolling
                        setTimeout(function() {
                            const navbarHeight = document.querySelector('.navbar-frame').offsetHeight;
                            const targetPosition = targetSection.getBoundingClientRect().top + window.pageYOffset - navbarHeight - 20;
                            
                            window.scrollTo({
                                top: targetPosition,
                                behavior: 'smooth'
                            });
                        }, 300);
                    }
                });
            });
            
            // Handle responsive offset
            function updateScrollMargin() {
                const navbarHeight = document.querySelector('.navbar-frame').offsetHeight;
                document.querySelectorAll('section').forEach(section => {
                    section.style.scrollMarginTop = navbarHeight + 20 + 'px';
                });
            }
            
            window.addEventListener('load', updateScrollMargin);
            window.addEventListener('resize', updateScrollMargin);
        });
    </script>
</body>
</html>