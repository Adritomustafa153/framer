<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Framer · photography</title>
    <!-- Bootstrap 5 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts (clean sans) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
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

        /* page sections – centered headings, smaller general font */
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
        
        p, li, .card-frame, .badge-bw, .btn {
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
        
        footer {
            background: black;
            color: #aaa;
            padding: 1.5rem;
            text-align: center;
            font-size: 0.85rem;
            border-top: 2px solid #222;
        }
        footer a { color: #ffffff; text-decoration: none; border-bottom: 1px dotted #666; }
        
        @media (max-width: 768px) {
            .navbar-frame { padding: 0.3rem 1rem; }
            .logo-img { height: 36px; }
            .dash-menu i { font-size: 1.8rem; }
            .carousel-item { height: 60vh; }
            .section-title { font-size: 1.8rem; }
            .offcanvas-body .nav-link { font-size: 1.1rem; }
            .blog-card .blog-title { font-size: 1.2rem; }
            .contact-info-card { margin-top: 2rem; }
            .map-container { min-height: 300px; }
            .social-icon-link {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
                margin: 0 0.25rem;
            }
        }
    </style>
</head>
<body>

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

        <!-- PACKAGES -->
        <section id="packages">
            <div class="container">
                <h2 class="section-title">Packages</h2>
                <div class="row g-4 justify-content-center">
                    <div class="col-md-4 col-sm-6">
                        <div class="border border-dark p-4 bg-white text-center h-100">
                            <h3 class="h5">◽ ESSENTIAL</h3>
                            <p class="fw-bold">$499</p>
                            <p class="small">2h session, 20 digital negatives.</p>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="border border-dark p-4 bg-black text-white text-center h-100">
                            <h3 class="h5">⬛ EDITORIAL</h3>
                            <p class="fw-bold">$999</p>
                            <p class="small text-white-50">4h session, 40 fine‑art prints.</p>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="border border-dark p-4 bg-white text-center h-100">
                            <h3 class="h5">◼️ INFINITY</h3>
                            <p class="fw-bold">$2499</p>
                            <p class="small">Full day, 2 locations, album & rights.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTACT - Redesigned with Map and Contact Info -->
        <section id="contact">
            <div class="container">
                <h2 class="section-title">Contact</h2>
                <div class="row g-4">
                    <!-- Left side: Map -->
                    <div class="col-lg-6">
                        <div class="map-container" style="height: 400px;">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3651.767150611156!2d90.4158229749629!3d23.75568147866682!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x265d1f3a711b2773%3A0xf6aef7cf05e4bba4!2sFramer!5e0!3m2!1sen!2sbd!4v1771669824226!5m2!1sen!2sbd" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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
                                    <p><a href="mailto:framer.wedding@gmail.com">framer.wedding@gmail.com</a></p>
                                </div>
                            </div>
                            
                            <!-- Phone -->
                            <div class="contact-info-item">
                                <div class="contact-icon">
                                    <i class="bi bi-telephone-fill"></i>
                                </div>
                                <div class="contact-details">
                                    <h4>Call Us</h4>
                                    <p><a href="tel:+8801829093616">+880 1829-093616</a></p>
                                </div>
                            </div>
                            
                            <!-- Location -->
                            <!-- <div class="contact-info-item">
                                <div class="contact-icon">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </div>
                                <div class="contact-details">
                                    <h4>Visit Us</h4>
                                    <p>Framer Studio, Dhaka, Bangladesh</p>
                                </div>
                            </div> -->
                            
                            <!-- Business Hours -->
                            <div class="contact-info-item">
                                <div class="contact-icon">
                                    <i class="bi bi-clock-fill"></i>
                                </div>
                                <div class="contact-details">
                                    <h4>Business Hours</h4>
                                    <p>Mon - Sat: 10:00 AM - 8:00 PM<br>Sunday: Closed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SOCIAL MEDIA - With Icons -->
        <section id="social">
            <div class="container">
                <h2 class="section-title">Follow Us</h2>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <!-- Facebook -->
                    <a href="https://www.facebook.com/profile.php?id=100091517055172" target="_blank" class="social-icon-link" rel="noopener noreferrer">
                        <i class="bi bi-facebook"></i>
                    </a>
                    
                    <!-- Instagram -->
                    <a href="https://www.instagram.com/framer.wedding/" target="_blank" class="social-icon-link" rel="noopener noreferrer">
                        <i class="bi bi-instagram"></i>
                    </a>
                    
                    <!-- YouTube -->
                    <a href="https://www.youtube.com/channel/UCmAlhSDX7kyi2eYllgitGRw" target="_blank" class="social-icon-link" rel="noopener noreferrer">
                        <i class="bi bi-youtube"></i>
                    </a>
                    
                    <!-- Additional social placeholder (you can add more) -->
                    <a href="#" class="social-icon-link" style="opacity:0.5; cursor:default;" onclick="return false;">
                        <i class="bi bi-pinterest"></i>
                    </a>
                </div>
                <p class="text-center mt-4 small text-muted">Connect with us on social media</p>
            </div>
        </section>

        <!-- BLOG - Cards -->
        <section id="blog">
            <div class="container">
                <h2 class="section-title">Latest from Our Blog</h2>
                
                <!-- First Row - 3 cards -->
                <div class="row g-4 mb-4">
                    <!-- Blog Post 1 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="blog-card">
                            <div class="date">May 4, 2023</div>
                            <h3 class="blog-title">Tips for brides before their wedding day</h3>
                            <div class="blog-excerpt">
                                Your wedding day is one of the most important and memorable days of your life. As a bride, there are some things you can do to prepare yourself...
                            </div>
                            <button class="read-more" onclick="openArticle(1)">Read More</button>
                        </div>
                    </div>
                    
                    <!-- Blog Post 2 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="blog-card">
                            <div class="date">May 4, 2023</div>
                            <h3 class="blog-title">Picture Perfect: Tips for Choosing the Right Wedding Photographer</h3>
                            <div class="blog-excerpt">
                                Your wedding day is one of the most important days of your life, and choosing the right wedding photographer is crucial to capturing all the special moments...
                            </div>
                            <button class="read-more" onclick="openArticle(2)">Read More</button>
                        </div>
                    </div>
                    
                    <!-- Blog Post 3 - Bengali/Bangla -->
                    <div class="col-lg-4 col-md-6">
                        <div class="blog-card">
                            <div class="date">June 11, 2023</div>
                            <h3 class="blog-title">বন্ধুর বিষয়ের কি উপহার দেওয়া যায়</h3>
                            <div class="blog-excerpt bengali">
                                আমাদের দেশে সাধারণত ১৮-৩০ বছরের ভেতর সবাই বিষয়ের পিপিউট বেস। এর ভেতর আপনিও অনেক বিষয়তে উপস্থিত থাকেন, তবে বিষয়টা যদি হয় আপনার খুব কাছের বন্ধু কিবা বান্ধবীর...
                            </div>
                            <button class="read-more" onclick="openArticle(3)">Read More</button>
                        </div>
                    </div>
                </div>
                
                <!-- Second Row - 2 cards (centered) -->
                <div class="row g-4 justify-content-center">
                    <!-- Blog Post 4 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="blog-card">
                            <div class="date">August 10, 2020</div>
                            <h3 class="blog-title">Виделяр алаезалон яънн эйлеш</h3>
                            <div class="blog-excerpt">
                                Виделяк писэтлэг сьуьг чыпсокан алар шерен пас яън: эйлеш, вяйл ам кыпчолдур. Амма жардур юкъе яън? Юрьян ахлаг вярл амрава эйлеш калдыр...
                            </div>
                            <button class="read-more" onclick="openArticle(4)">Read More</button>
                        </div>
                    </div>
                    
                    <!-- Blog Post 5 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="blog-card">
                            <div class="date">May 17, 2023</div>
                            <h3 class="blog-title">Preserving Your Memories: Why Professional Wedding Photography Matters</h3>
                            <div class="blog-excerpt">
                                You will remember your wedding day as a once-in-a-lifetime memory years to come. The day may pass quickly, but your memories will endure a lifetime...
                            </div>
                            <button class="read-more" onclick="openArticle(5)">Read More</button>
                        </div>
                    </div>
                </div>
                
                <!-- View All Blog Posts Link -->
                <div class="text-center mt-5">
                    <a href="#" class="text-dark text-decoration-none border-bottom border-dark" style="font-size:1.1rem;">View All Articles →</a>
                </div>
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
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p class="mb-0">FRAMER · photography in black & white · 2025</p>
        <p class="small mt-2"><a href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">menu</a></p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Blog article opener function
        function openArticle(articleId) {
            alert('Opening article ' + articleId + ' - In a real implementation, this would take you to the full article page.');
            // window.location.href = 'blog-post.php?id=' + articleId;
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