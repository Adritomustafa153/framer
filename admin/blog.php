<?php
// admin/blog.php
require_once '../config/database.php';
require_once '../models/Blog.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$blog = new Blog($db);

// Handle delete
if (isset($_GET['delete'])) {
    if ($blog->delete($_GET['delete'])) {
        header("Location: blog.php?msg=deleted");
        exit();
    }
}

// Get all blog posts
$posts = $blog->getAll('created_at DESC');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Blog Posts</h2>
    <a href="blog-edit.php" class="btn btn-dark">
        <i class="bi bi-plus-circle"></i> New Blog Post
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        if ($_GET['msg'] == 'created') echo "Blog post created successfully!";
        if ($_GET['msg'] == 'updated') echo "Blog post updated successfully!";
        if ($_GET['msg'] == 'deleted') echo "Blog post deleted successfully!";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Published</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($posts->rowCount() > 0): ?>
                        <?php while ($row = $posts->fetch()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td><?php echo htmlspecialchars($row['author_id']); ?></td>
                                <td>
                                    <?php if ($row['status'] == 'published'): ?>
                                        <span class="badge bg-success">Published</span>
                                    <?php elseif ($row['status'] == 'draft'): ?>
                                        <span class="badge bg-warning">Draft</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Archived</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['views']; ?></td>
                                <td><?php echo $row['published_at'] ? date('M d, Y', strtotime($row['published_at'])) : '-'; ?></td>
                                <td>
                                    <a href="blog-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="blog.php?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this post?')"
                                       data-bs-toggle="tooltip" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No blog posts found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>