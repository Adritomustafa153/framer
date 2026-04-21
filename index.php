<?php
// home.php or index.php
require_once 'config/database.php';
require_once 'models/Package.php';
require_once 'models/Blog.php';
require_once 'models/Settings.php';
require_once 'models/Slider.php';
require_once 'models/Gallery.php';      // Make sure this line exists
require_once 'models/GallerySettings.php';
require_once 'models/WhyUs.php';
require_once 'models/FAQ.php';
require_once 'models/Photographer.php';  // Add this if needed

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get packages from database
$package = new Package($db);
$packages = $package->getAll('is_featured DESC, sort_order ASC, id DESC');

// Get blog posts
$blog = new Blog($db);
$blogPosts = $blog->getPublished();

// Get slider images
$slider = new Slider($db);
$sliderImages = $slider->getActive();

// Get gallery images (limit to 8 for homepage)
$gallery = new Gallery($db);
$galleryImages = $gallery->getActive();
$galleryImages = array_slice($galleryImages, 0, 8); // Show only 8 images on homepage
$galleryCategories = $gallery->getCategories();

// Get why us items
$whyUs = new WhyUs($db);
$whyUsItems = $whyUs->getActive();

// Get FAQ items
$faq = new FAQ($db);
$faqItems = $faq->getActive();
$faqCategories = $faq->getCategories();

// Get gallery settings
$gallerySettings = new GallerySettings($db);
$galleryTitle = $gallerySettings->get('gallery_title') ?? 'Our Gallery';
$imagesPerRow = $gallerySettings->get('images_per_row') ?? '4';
$slideshowMusicUrl = $gallerySettings->get('slideshow_music_url') ?? '';
$slideshowAutoplay = $gallerySettings->get('slideshow_autoplay') ?? '1';
$slideshowDelay = $gallerySettings->get('slideshow_delay') ?? '3000';

// Force 4 images per row for gallery
$colClass = 'col-lg-3 col-md-4 col-sm-6 px-2';

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
    <!-- Slick Carousel CSS for blog -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css"/>
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
        
        /* Logo and company name container */
        .brand-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        /* logo image */
        .logo-img {
            height: 60px;
            width: auto;
            display: block;
        }
        
        /* Company name styling */
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
            scroll-margin-top: 70px; /* Reduced from 90px */
            padding: 3rem 1.5rem; /* Reduced padding */
            border-bottom: 1px solid #eaeaea;
        }
        section:nth-child(even) { background-color: #f9f9f9; }
        section:nth-child(odd) { background-color: #ffffff; }

        /* First section (about) should have less top padding */
        #about {
            padding-top: 2rem; /* Reduced top padding */
        }

        /* all section headings centered */
        .section-title {
            font-size: 2.2rem;
            font-weight: 650;
            letter-spacing: -0.02em;
            margin-bottom: 2rem; /* Reduced margin */
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

        /* Section header with view all button */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem; /* Reduced margin */
            flex-wrap: wrap;
        }
        
        .section-header .section-title {
            margin-bottom: 0;
            width: auto;
        }
        
        .section-header .section-title::after {
            margin: 0.8rem 0 0;
        }
        
        .view-all-btn {
            background: transparent;
            border: 2px solid #111;
            color: #111;
            padding: 8px 20px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .view-all-btn:hover {
            background: #111;
            color: white;
            transform: translateY(-3px);
            box-shadow: 5px 5px 0 rgba(0,0,0,0.1);
        }
        
        .view-all-btn i {
            font-size: 1rem;
        }

        /* Slider Styles - Fixed positioning */
        .slider-section {
            position: relative;
            width: 100%;
            background: #000;
            margin-top: 80px; /* Pushes slider below fixed navbar */
            padding-top: 0;
            margin-bottom: 0; /* No bottom margin */
        }
        .carousel-item {
            height: 450px; /* Slightly reduced height */
            background-color: #000;
        }
        .carousel-item img {
            height: 100%;
            width: 100%;
            object-fit: cover;
        }
        .carousel-caption {
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            bottom: 0;
            left: 0;
            right: 0;
            padding: 30px 20px;
            text-align: center;
        }
        .carousel-caption h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .carousel-caption p {
            font-size: 1.1rem;
            margin-bottom: 0;
            opacity: 0.9;
        }
        .carousel-control-prev,
        .carousel-control-next {
            width: 50px;
            height: 50px;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            margin: 0 20px;
        }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            background: rgba(0,0,0,0.8);
        }
        .carousel-indicators {
            margin-bottom: 20px;
        }

        .carousel-indicators button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 0 5px;
        }
        
        p, li, .badge-bw, .btn {
            font-size: 0.95rem;
        }
        h2, h3, h4 { font-weight: 600; }

        /* Why Us Card Styles */
        .why-us-card {
            background: white;
            border: 2px solid #111;
            padding: 2.5rem 2rem;
            box-shadow: 8px 8px 0 rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .why-us-card:hover {
            transform: translate(-5px, -5px);
            box-shadow: 12px 12px 0 rgba(0,0,0,0.15);
        }
        
        .why-us-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #111, #555, #111);
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }
        
        .why-us-card:hover::before {
            transform: translateX(0);
        }
        
        .why-us-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            display: inline-block;
        }
        
        .why-us-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .why-us-description {
            color: #555;
            line-height: 1.6;
            margin-bottom: 0;
        }

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

        /* Package Card Styles - White Background */
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
        }
        
        .btn-package:hover {
            background: white;
            color: #111;
        }

        /* Gallery Styles - Clean borderless design with proper image fitting */
        .gallery-container {
            margin-top: 10px;
            margin-left: -8px;
            margin-right: -8px;
        }
        
        .gallery-item {
            padding: 8px !important;
        }
        
        .gallery-card {
            position: relative;
            overflow: hidden;
            border-radius: 4px; /* Subtle rounded corners */
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            background: #f5f5f5; /* Light background for empty space */
            aspect-ratio: 1 / 1;
            border: none;
            outline: none;
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
            border: none;
            outline: none;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .gallery-image {
            width: 100%;
            height: 100%;
            object-fit: contain; /* This ensures the ENTIRE image is visible without cropping */
            transition: transform 0.6s ease;
            display: block;
            border: none;
            outline: none;
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
            opacity: 0;
            transition: opacity 0.3s ease;
            border: none;
        }
        
        .gallery-card:hover .gallery-overlay {
            opacity: 1;
        }
        
        .gallery-overlay i {
            color: white;
            font-size: 2.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            transform: scale(0.8);
            transition: transform 0.3s ease;
        }
        
        .gallery-card:hover .gallery-overlay i {
            transform: scale(1);
        }
        
        /* Category filter buttons */
        .gallery-filter {
            margin: 0 3px 8px;
            border-color: #111;
            color: #111;
            border-radius: 30px;
            padding: 5px 18px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .gallery-filter.active {
            background: #111;
            color: white;
            border-color: #111;
        }
        
        .gallery-filter:hover {
            background: #333;
            color: white;
            border-color: #333;
            transform: translateY(-2px);
        }
        
        /* FAQ Styles */
        .faq-section {
            background: #f9f9f9;
        }
        
        .faq-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .faq-item {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            background: white;
            box-shadow: 5px 5px 0 rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .faq-item:hover {
            box-shadow: 8px 8px 0 rgba(0,0,0,0.1);
            transform: translate(-2px, -2px);
        }
        
        .faq-question {
            padding: 20px 25px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            transition: background 0.3s ease;
        }
        
        .faq-question:hover {
            background: #f5f5f5;
        }
        
        .faq-question h4 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: #111;
            flex: 1;
        }
        
        .faq-icon {
            font-size: 1.5rem;
            color: #111;
            transition: transform 0.3s ease;
            margin-left: 20px;
        }
        
        .faq-item.active .faq-icon {
            transform: rotate(180deg);
        }
        
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-out, padding 0.3s ease;
            background: #fafafa;
            border-top: 1px solid transparent;
        }
        
        .faq-item.active .faq-answer {
            max-height: 500px;
            border-top-color: #ddd;
            padding: 20px 25px;
        }
        
        .faq-answer p {
            margin: 0;
            line-height: 1.7;
            color: #555;
        }
        
        .faq-category {
            display: inline-block;
            background: #111;
            color: white;
            padding: 3px 10px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        /* Lightbox Customization */
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
            font-weight: bold;
            cursor: pointer;
            z-index: 10000;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            transition: transform 0.3s ease;
        }
        
        .lightbox-close:hover {
            color: #ccc;
            transform: scale(1.1);
        }
        
        .lightbox-prev, .lightbox-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 50px;
            font-weight: bold;
            cursor: pointer;
            padding: 20px;
            z-index: 10000;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            transition: all 0.3s;
        }
        
        .lightbox-prev {
            left: 30px;
        }
        
        .lightbox-next {
            right: 30px;
        }
        
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
            max-height: 80vh;
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
            gap: 10px;
            z-index: 10000;
        }
        
        .lightbox-controls button {
            background: rgba(255,255,255,0.2);
            border: 1px solid white;
            color: white;
            padding: 10px 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
            border-radius: 30px;
        }
        
        .lightbox-controls button:hover {
            background: white;
            color: black;
            transform: translateY(-2px);
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
            margin: 10px 0;
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
        
        /* Blog Carousel */
        .blog-carousel-container {
            position: relative;
            padding: 0 30px;
        }
        .blog-carousel {
            margin: 0 -10px;
        }
        .blog-carousel .slick-slide {
            padding: 0 10px;
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
            border-radius: 50%;
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
        
        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .carousel-item {
                height: 400px;
            }
            
            .carousel-caption h3 {
                font-size: 1.8rem;
            }
            
            .company-name {
                font-size: 1.5rem;
            }
            
            .company-name-bengali {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 768px) {
            .navbar-frame { padding: 0.3rem 1rem; }
            .logo-img { height: 45px; }
            
            .company-name {
                font-size: 1.3rem;
            }
            
            .company-name-bengali {
                font-size: 1rem;
            }
            
            .carousel-item {
                height: 300px;
            }
            
            .carousel-caption {
                padding: 20px 15px 10px;
            }
            
            .carousel-caption h3 {
                font-size: 1.3rem;
                margin-bottom: 5px;
            }
            
            .carousel-caption p {
                font-size: 0.9rem;
            }
            
            .carousel-control-prev,
            .carousel-control-next {
                width: 35px;
                height: 35px;
                margin: 0 10px;
            }
            
            .carousel-indicators {
                margin-bottom: 10px;
            }
            
            .slider-section {
                margin-top: 70px;
            }
            
            section {
                padding: 2.5rem 1rem;
            }
            
            #about {
                padding-top: 1.5rem;
            }
            
            .section-title { font-size: 1.8rem; }
            .offcanvas-body .nav-link { font-size: 1.1rem; }
            .contact-info-card { margin-top: 2rem; }
            .map-container { min-height: 300px; }
            .social-icon-link {
                width: 45px;
                height: 45px;
                font-size: 1.3rem;
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
            .blog-carousel-container {
                padding: 0 20px;
            }
            .lightbox-prev {
                left: 10px;
                font-size: 30px;
            }
            .lightbox-next {
                right: 10px;
                font-size: 30px;
            }
            .gallery-filter {
                padding: 4px 12px;
                font-size: 0.8rem;
            }
            .faq-question {
                padding: 15px 20px;
            }
            .faq-question h4 {
                font-size: 1rem;
            }
            .faq-item.active .faq-answer {
                padding: 15px 20px;
            }
            .section-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            .section-header .section-title::after {
                margin: 0.8rem auto 0;
            }
        }

        @media (max-width: 480px) {
            .carousel-item {
                height: 250px;
            }
            
            .carousel-caption {
                padding: 15px 10px 5px;
            }
            
            .carousel-caption h3 {
                font-size: 1.1rem;
            }
            
            .carousel-caption p {
                font-size: 0.8rem;
            }
            
            .carousel-control-prev,
            .carousel-control-next {
                width: 30px;
                height: 30px;
            }
            
            .slider-section {
                margin-top: 60px;
            }
            
            .company-name {
                font-size: 1.1rem;
            }
            
            .company-name-bengali {
                font-size: 0.9rem;
            }
            
            .gallery-filter {
                padding: 3px 8px;
                font-size: 0.7rem;
                margin: 0 2px 5px;
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
                <a class="navbar-brand p-0" href="#">
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
                <a class="nav-link" href="#about">About Framer</a>
                <a class="nav-link" href="#why-us">Why Us</a>
                <a class="nav-link" href="#gallery">Gallery</a>
                <a class="nav-link" href="#packages">Packages</a>
                <a class="nav-link" href="#contact">Contact</a>
                <a class="nav-link" href="#social">Social Media</a>
                <a class="nav-link" href="#blog">Blog</a>
                <a class="nav-link" href="#faq">FAQ</a>
            </div>
            <div class="mt-auto p-3 small text-secondary border-top border-secondary">
                <span class="text-white-50">© framer / monochrome</span>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <main>

        <!-- BOOTSTRAP SLIDER -->
        <section id="hero-slider" class="slider-section">
            <div id="framerCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="4000">
                <?php if ($sliderImages && count($sliderImages) > 0): ?>
                    <div class="carousel-indicators">
                        <?php foreach($sliderImages as $index => $slide): ?>
                            <button type="button" data-bs-target="#framerCarousel" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index == 0 ? 'active' : ''; ?>" aria-current="<?php echo $index == 0 ? 'true' : ''; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="carousel-inner">
                        <?php foreach($sliderImages as $index => $slide): ?>
                            <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
                                <img src="<?php echo htmlspecialchars($slide['image_url']); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($slide['title']); ?>" style="height: 450px; object-fit: cover;">
                                <?php if (!empty($slide['title']) || !empty($slide['description'])): ?>
                                    <div class="carousel-caption d-none d-md-block">
                                        <?php if (!empty($slide['title'])): ?>
                                            <h3><?php echo htmlspecialchars($slide['title']); ?></h3>
                                        <?php endif; ?>
                                        <?php if (!empty($slide['description'])): ?>
                                            <p><?php echo htmlspecialchars($slide['description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button class="carousel-control-prev" type="button" data-bs-target="#framerCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#framerCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                    
                <?php else: ?>
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="https://placecats.com/1600/900?random=1" class="d-block w-100" alt="slide 1" style="height: 450px; object-fit: cover;">
                            <div class="carousel-caption d-none d-md-block">
                                <h3>Welcome to Framer</h3>
                                <p>Professional photography services</p>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="https://placecats.com/1600/901?random=2" class="d-block w-100" alt="slide 2" style="height: 450px; object-fit: cover;">
                            <div class="carousel-caption d-none d-md-block">
                                <h3>Wedding Photography</h3>
                                <p>Capturing your special moments</p>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="https://placecats.com/1600/902?random=3" class="d-block w-100" alt="slide 3" style="height: 450px; object-fit: cover;">
                            <div class="carousel-caption d-none d-md-block">
                                <h3>Portrait Sessions</h3>
                                <p>Timeless black and white portraits</p>
                            </div>
                        </div>
                    </div>
                    
                    <button class="carousel-control-prev" type="button" data-bs-target="#framerCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#framerCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                <?php endif; ?>
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

        <!-- WHY US - Dynamic from Database -->
        <section id="why-us">
            <div class="container">
                <h2 class="section-title">Why Us</h2>
                <div class="row g-4">
                    <?php if (!empty($whyUsItems)): ?>
                        <?php foreach ($whyUsItems as $item): ?>
                            <div class="col-md-4 col-sm-6">
                                <div class="why-us-card">
                                    <?php if (!empty($item['icon'])): ?>
                                        <div class="why-us-icon"><?php echo $item['icon']; ?></div>
                                    <?php endif; ?>
                                    <h3 class="why-us-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                                    <p class="why-us-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <p class="text-muted">No items available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- GALLERY - Clean borderless design with full image visibility -->
        <section id="gallery">
            <div class="container-fluid px-3">
                <div class="section-header">
                    <h2 class="section-title"><?php echo htmlspecialchars($galleryTitle); ?></h2>
                    <a href="gallery.php" class="view-all-btn">
                        View Gallery <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                
                <?php if (!empty($galleryImages)): ?>
                    <!-- Category Filter -->
                    <?php if (!empty($galleryCategories)): ?>
                    <div class="text-center mb-3">
                        <button class="btn btn-sm btn-outline-dark gallery-filter active" data-filter="all">All</button>
                        <?php foreach ($galleryCategories as $cat): 
                            if (!empty($cat['category'])):
                        ?>
                            <button class="btn btn-sm btn-outline-dark gallery-filter" data-filter="<?php echo htmlspecialchars($cat['category']); ?>">
                                <?php echo htmlspecialchars($cat['category']); ?>
                            </button>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Gallery Grid - No borders, full image visible -->
                    <div class="row g-0 gallery-container">
                        <?php foreach ($galleryImages as $image): 
                            $imageUrl = $image['image_url'];
                            $thumbUrl = $image['thumbnail_url'] ?: $image['image_url'];
                        ?>
                            <div class="col-lg-3 col-md-4 col-sm-6 px-2 gallery-item" data-category="<?php echo htmlspecialchars($image['category'] ?? ''); ?>">
                                <div class="gallery-card">
                                    <div class="gallery-image-container" onclick="openLightbox(<?php echo $image['id']; ?>)">
                                        <img src="<?php echo htmlspecialchars($thumbUrl); ?>" 
                                             alt="<?php echo htmlspecialchars($image['title']); ?>"
                                             class="gallery-image"
                                             data-id="<?php echo $image['id']; ?>"
                                             data-full="<?php echo htmlspecialchars($imageUrl); ?>">
                                        <div class="gallery-overlay">
                                            <i class="bi bi-arrows-fullscreen"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Lightbox Popup -->
                    <div id="gallery-lightbox" class="lightbox">
                        <span class="lightbox-close">&times;</span>
                        <span class="lightbox-prev">&#10094;</span>
                        <span class="lightbox-next">&#10095;</span>
                        <div class="lightbox-content">
                            <img id="lightbox-image" src="" alt="">
                        </div>
                        <!-- Slideshow Controls -->
                        <div class="lightbox-controls">
                            <button id="slideshow-play" class="btn btn-sm btn-light">
                                <i class="bi bi-play-fill"></i> Play Slideshow
                            </button>
                            <button id="slideshow-stop" class="btn btn-sm btn-light" style="display: none;">
                                <i class="bi bi-stop-fill"></i> Stop
                            </button>
                        </div>
                    </div>
                    
                    <!-- Audio for Slideshow (hidden) -->
                    <?php if (!empty($slideshowMusicUrl)): ?>
                    <audio id="slideshow-audio" loop style="display: none;">
                        <source src="<?php echo htmlspecialchars($slideshowMusicUrl); ?>" type="audio/mpeg">
                    </audio>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="text-center">
                        <p class="text-muted">No gallery images available yet. Check back soon!</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- PACKAGES - Dynamic from Database with View All Button -->
        <section id="packages">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Our Packages</h2>
                    <a href="packages.php" class="view-all-btn">
    View All Packages <i class="bi bi-arrow-right"></i>
</a>
                </div>
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

        <!-- FAQ Section - Dynamic from Database -->
        <section id="faq" class="faq-section">
            <div class="container">
                <h2 class="section-title">Frequently Asked Questions</h2>
                
                <?php if (!empty($faqItems)): ?>
                    <!-- Category Filter (Optional) -->
                    <?php if (!empty($faqCategories)): ?>
                    <div class="text-center mb-4">
                        <button class="btn btn-sm btn-outline-dark faq-category-filter active" data-category="all">All</button>
                        <?php foreach ($faqCategories as $cat): 
                            if (!empty($cat['category'])):
                        ?>
                            <button class="btn btn-sm btn-outline-dark faq-category-filter" data-category="<?php echo htmlspecialchars($cat['category']); ?>">
                                <?php echo htmlspecialchars($cat['category']); ?>
                            </button>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="faq-container">
                        <?php foreach ($faqItems as $index => $item): ?>
                            <div class="faq-item" data-category="<?php echo htmlspecialchars($item['category'] ?? ''); ?>">
                                <div class="faq-question" onclick="toggleFAQ(this)">
                                    <h4><?php echo htmlspecialchars($item['question']); ?></h4>
                                    <span class="faq-icon">▼</span>
                                </div>
                                <div class="faq-answer">
                                    <p><?php echo nl2br(htmlspecialchars($item['answer'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <p class="text-muted">No FAQs available yet. Check back soon!</p>
                    </div>
                <?php endif; ?>
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

        <!-- BLOG - Dynamic Carousel -->
        <section id="blog">
            <div class="container">
                <h2 class="section-title">Latest from Our Blog</h2>
                
                <?php if (!empty($blogPosts)): ?>
                    <div class="blog-carousel-container">
                        <div class="blog-carousel">
                            <?php foreach ($blogPosts as $post): 
                                $excerpt = $post['excerpt'] ?: (isset($post['content']) ? substr(strip_tags($post['content']), 0, 150) . '...' : 'Read more...');
                            ?>
                                <div>
                                    <div class="blog-card">
                                        <div class="date"><?php echo date('F j, Y', strtotime($post['published_at'] ?: $post['created_at'])); ?></div>
                                        <h3 class="blog-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                        <div class="blog-excerpt <?php echo preg_match('/[ঀ-৿]/', $post['title']) ? 'bengali' : ''; ?>">
                                            <?php echo htmlspecialchars($excerpt); ?>
                                        </div>
                                        <button class="read-more" onclick="openArticle(<?php echo $post['id']; ?>)">Read More</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- View All Blog Posts Link -->
                    <div class="text-center mt-5">
                        <a href="blog.php" class="text-dark text-decoration-none border-bottom border-dark" style="font-size:1.1rem;">
                            <i class="bi bi-journal-text"></i> View All Articles →
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <p class="text-muted">No blog posts available yet. Check back soon!</p>
                    </div>
                <?php endif; ?>
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
    <!-- jQuery (required for Slick) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Slick Carousel JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
    
    <!-- Custom JavaScript -->
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
        
        // Open lightbox function
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
        
        document.getElementById('slideshow-play').addEventListener('click', startSlideshow);
        document.getElementById('slideshow-stop').addEventListener('click', stopSlideshow);
        
        // Gallery filter
        document.querySelectorAll('.gallery-filter').forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.dataset.filter;
                
                // Update active button
                document.querySelectorAll('.gallery-filter').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');
                
                // Filter items
                document.querySelectorAll('.gallery-item').forEach(item => {
                    if (filter === 'all' || item.dataset.category === filter) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // FAQ Functions
        function toggleFAQ(element) {
            const faqItem = element.closest('.faq-item');
            const isActive = faqItem.classList.contains('active');
            
            // Close all other FAQs
            document.querySelectorAll('.faq-item.active').forEach(item => {
                if (item !== faqItem) {
                    item.classList.remove('active');
                }
            });
            
            // Toggle current FAQ
            if (!isActive) {
                faqItem.classList.add('active');
            } else {
                faqItem.classList.remove('active');
            }
        }

        // FAQ Category Filter
        document.querySelectorAll('.faq-category-filter').forEach(button => {
            button.addEventListener('click', function() {
                const category = this.dataset.category;
                
                // Update active button
                document.querySelectorAll('.faq-category-filter').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');
                
                // Filter FAQ items
                document.querySelectorAll('.faq-item').forEach(item => {
                    if (category === 'all' || item.dataset.category === category) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                        // Close if open
                        item.classList.remove('active');
                    }
                });
            });
        });

        // Blog article opener function
        function openArticle(articleId) {
            // Redirect to single blog post page
            window.location.href = 'blog-post.php?id=' + articleId;
        }

        // Book package function
        function bookPackage(packageCode) {
            // Redirect to WhatsApp with package info
            window.location.href = 'https://wa.me/<?php echo $whatsappNumber; ?>?text=Hi%20Framer!%20I%27m%20interested%20in%20your%20package%20' + packageCode + '.%20Please%20tell%20me%20more.';
        }

        $(document).ready(function() {
            // Initialize blog carousel
            if ($('.blog-carousel').length) {
                $('.blog-carousel').slick({
                    dots: true,
                    infinite: true,
                    speed: 500,
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    autoplay: true,
                    autoplaySpeed: 4000,
                    arrows: true,
                    responsive: [
                        {
                            breakpoint: 1024,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 1,
                                infinite: true,
                                dots: true
                            }
                        },
                        {
                            breakpoint: 600,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        }
                    ]
                });
            }

            // Handle smooth scrolling for anchor links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    // Only handle links that start with #
                    if (this.getAttribute('href') && this.getAttribute('href').startsWith('#')) {
                        e.preventDefault();
                        
                        const targetId = this.getAttribute('href');
                        const targetSection = document.querySelector(targetId);
                        
                        if (targetSection) {
                            // Close offcanvas if it's open
                            const offcanvasElement = document.getElementById('offcanvasMenu');
                            const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                            if (offcanvas) {
                                offcanvas.hide();
                            }
                            
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