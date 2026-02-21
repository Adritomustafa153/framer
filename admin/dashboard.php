<?php
// admin/dashboard.php
require_once '../config/database.php';
require_once '../models/Package.php';
require_once '../models/Blog.php';
require_once '../models/Gallery.php';
require_once '../models/Message.php';
require_once '../models/User.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();

// Get counts
$package = new Package($db);
$blog = new Blog($db);
$gallery = new Gallery($db);
$message = new Message($db);
$user = new User($db);

$packageCount = $package->count();
$blogCount = $blog->count();
$galleryCount = $gallery->count();
$messageCount = $message->count();
$unreadCount = $message->getUnreadCount();
$userCount = $user->count();

// Get recent messages
$recentMessages = $message->getAll('created_at DESC LIMIT 5');

// Get recent blog posts
$recentPosts = $blog->getAll('created_at DESC LIMIT 5');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Dashboard</h2>
    <div>
        <span class="text-muted">Last login: <?php echo $_SESSION['last_login'] ?? 'N/A'; ?></span>
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
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Gallery Images</h6>
                        <h2 class="mb-0"><?php echo $galleryCount; ?></h2>
                    </div>
                    <i class="bi bi-images fs-1"></i>
                </div>
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
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Messages -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Messages</h5>
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
                <h5 class="mb-0">Recent Blog Posts</h5>
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
                                <p class="mb-1 text-truncate"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                                <small>
                                    <span class="badge bg-<?php echo $post['status'] == 'published' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($post['status']); ?>
                                    </span>
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

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="package-edit.php" class="btn btn-outline-dark me-2 mb-2">
                    <i class="bi bi-plus-circle"></i> Add Package
                </a>
                <a href="blog-edit.php" class="btn btn-outline-dark me-2 mb-2">
                    <i class="bi bi-plus-circle"></i> New Blog Post
                </a>
                <a href="gallery-upload.php" class="btn btn-outline-dark me-2 mb-2">
                    <i class="bi bi-plus-circle"></i> Upload Images
                </a>
                <a href="settings.php" class="btn btn-outline-dark me-2 mb-2">
                    <i class="bi bi-gear"></i> Update Settings
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>