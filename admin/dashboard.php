<?php
// admin/dashboard.php
require_once '../config/database.php';
require_once '../models/Package.php';
require_once '../models/Blog.php';
require_once '../models/Gallery.php';
require_once '../models/Message.php';
require_once '../models/User.php';
require_once '../models/Slider.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();

// Get counts
$package = new Package($db);
$blog = new Blog($db);
$gallery = new Gallery($db);
$message = new Message($db);
$user = new User($db);
$slider = new Slider($db);

$packageCount = $package->count();
$blogCount = $blog->count();
$galleryCount = $gallery->count();
$messageCount = $message->count();
$unreadCount = $message->getUnreadCount();
$userCount = $user->count();
$sliderCount = $slider->count();

// Get active slider count
$activeSlides = $slider->getActive();
$activeSliderCount = count($activeSlides);

// Get recent messages
$recentMessages = $message->getAll('created_at DESC LIMIT 5');

// Get recent blog posts
$recentPosts = $blog->getAll('created_at DESC LIMIT 5');

// Get recent slider images
$recentSlides = $slider->getAll('created_at DESC LIMIT 4');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Dashboard</h2>
    <div>
        <span class="text-muted">Welcome, <?php echo htmlspecialchars(getCurrentUsername()); ?>!</span>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Packages</h6>
                        <h2 class="mb-0"><?php echo $packageCount; ?></h2>
                    </div>
                    <i class="bi bi-box fs-1"></i>
                </div>
                <a href="packages.php" class="text-white text-decoration-none small">Manage Packages →</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Blog Posts</h6>
                        <h2 class="mb-0"><?php echo $blogCount; ?></h2>
                    </div>
                    <i class="bi bi-pencil-square fs-1"></i>
                </div>
                <a href="blog.php" class="text-white text-decoration-none small">Manage Blog →</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Slider Images</h6>
                        <h2 class="mb-0"><?php echo $sliderCount; ?> 
                            <small class="fs-6">(<?php echo $activeSliderCount; ?> active)</small>
                        </h2>
                    </div>
                    <i class="bi bi-images fs-1"></i>
                </div>
                <a href="slider.php" class="text-white text-decoration-none small">Manage Slider →</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Messages</h6>
                        <h2 class="mb-0"><?php echo $messageCount; ?> 
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-danger"><?php echo $unreadCount; ?> unread</span>
                            <?php endif; ?>
                        </h2>
                    </div>
                    <i class="bi bi-envelope fs-1"></i>
                </div>
                <a href="messages.php" class="text-white text-decoration-none small">View Messages →</a>
            </div>
        </div>
    </div>
</div>

<!-- Second Row Stats -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card stat-card bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Gallery Images</h6>
                        <h2 class="mb-0"><?php echo $galleryCount; ?></h2>
                    </div>
                    <i class="bi bi-collection fs-1"></i>
                </div>
                <a href="gallery.php" class="text-white text-decoration-none small">Manage Gallery →</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card stat-card bg-dark text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Users</h6>
                        <h2 class="mb-0"><?php echo $userCount; ?></h2>
                    </div>
                    <i class="bi bi-people fs-1"></i>
                </div>
                <a href="users.php" class="text-white text-decoration-none small">Manage Users →</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card stat-card" style="background: #6f42c1; color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total Content</h6>
                        <h2 class="mb-0"><?php echo $packageCount + $blogCount + $sliderCount + $galleryCount; ?></h2>
                    </div>
                    <i class="bi bi-files fs-1"></i>
                </div>
                <small class="text-white-50">All items combined</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Messages -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-envelope me-2"></i>Recent Messages</h5>
                <a href="messages.php" class="btn btn-sm btn-light">View All</a>
            </div>
            <div class="card-body">
                <?php if ($recentMessages->rowCount() > 0): ?>
                    <div class="list-group">
                        <?php while ($msg = $recentMessages->fetch()): ?>
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($msg['name']); ?></h6>
                                    <small><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></small>
                                </div>
                                <p class="mb-1 text-truncate"><?php echo htmlspecialchars($msg['message']); ?></p>
                                <small>
                                    <?php if (!$msg['is_read']): ?>
                                        <span class="badge bg-warning">Unread</span>
                                    <?php endif; ?>
                                    <?php if ($msg['is_replied']): ?>
                                        <span class="badge bg-success">Replied</span>
                                    <?php endif; ?>
                                </small>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No messages yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Blog Posts -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Recent Blog Posts</h5>
                <a href="blog.php" class="btn btn-sm btn-light">View All</a>
            </div>
            <div class="card-body">
                <?php if ($recentPosts->rowCount() > 0): ?>
                    <div class="list-group">
                        <?php while ($post = $recentPosts->fetch()): ?>
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($post['title']); ?></h6>
                                    <small><?php echo date('M d, Y', strtotime($post['created_at'])); ?></small>
                                </div>
                                <p class="mb-1 text-truncate"><?php echo htmlspecialchars($post['excerpt'] ?: strip_tags(substr($post['content'], 0, 100)) . '...'); ?></p>
                                <small>
                                    <span class="badge bg-<?php echo $post['status'] == 'published' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($post['status']); ?>
                                    </span>
                                    <?php if ($post['is_featured']): ?>
                                        <span class="badge bg-warning">Featured</span>
                                    <?php endif; ?>
                                    <span class="ms-2"><i class="bi bi-eye"></i> <?php echo $post['views']; ?> views</span>
                                </small>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No blog posts yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Slider Images - FIXED VERSION -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-images me-2"></i>Recent Slider Images</h5>
                <a href="slider.php" class="btn btn-sm btn-light">Manage Slider</a>
            </div>
            <div class="card-body">
                <?php if ($recentSlides && $recentSlides->rowCount() > 0): ?>
                    <div class="row">
                        <?php while ($slide = $recentSlides->fetch()): 
                            // Fix image path for display
                            $imageUrl = $slide['image_url'];
                            // If it's a local upload, add ../ to path
                            if (strpos($imageUrl, 'uploads/') === 0) {
                                $imageUrl = '../' . $imageUrl;
                            }
                        ?>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card h-100">
                                    <div style="height: 150px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; padding: 5px; border-bottom: 1px solid #ddd;">
                                        <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                             class="img-fluid" 
                                             alt="<?php echo htmlspecialchars($slide['title']); ?>"
                                             style="max-height: 140px; max-width: 100%; width: auto; height: auto; object-fit: contain;"
                                             onerror="this.src='https://via.placeholder.com/200x150?text=Image+Error'; this.onerror=null;">
                                    </div>
                                    <div class="card-body p-2 text-center">
                                        <h6 class="card-title small mb-1"><?php echo htmlspecialchars($slide['title'] ?: 'Untitled'); ?></h6>
                                        <div>
                                            <?php if ($slide['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                            <span class="badge bg-dark">Order: <?php echo $slide['sort_order']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No slider images yet. <a href="slider-edit.php">Add your first slide</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-2">
                        <a href="package-edit.php" class="btn btn-outline-dark w-100">
                            <i class="bi bi-plus-circle"></i> Add Package
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <a href="blog-edit.php" class="btn btn-outline-dark w-100">
                            <i class="bi bi-plus-circle"></i> New Blog Post
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <a href="slider-edit.php" class="btn btn-outline-dark w-100">
                            <i class="bi bi-plus-circle"></i> Add Slider Image
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <a href="gallery-upload.php" class="btn btn-outline-dark w-100">
                            <i class="bi bi-plus-circle"></i> Upload to Gallery
                        </a>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-3 col-sm-6 mb-2">
                        <a href="messages.php" class="btn btn-outline-warning w-100">
                            <i class="bi bi-envelope"></i> Check Messages
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-danger"><?php echo $unreadCount; ?> new</span>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <a href="settings.php" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-gear"></i> Update Settings
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <a href="../home.php" target="_blank" class="btn btn-outline-info w-100">
                            <i class="bi bi-eye"></i> View Website
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Info -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>System Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <p class="mb-1"><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <p class="mb-1"><strong>Database:</strong> MySQL</p>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <p class="mb-1"><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Local'; ?></p>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <p class="mb-1"><strong>Last Login:</strong> <?php echo $_SESSION['last_login'] ?? 'N/A'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>