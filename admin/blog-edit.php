<?php
// admin/blog-edit.php

// Start output buffering to prevent header errors
ob_start();

// Include auth at the top
require_once 'auth.php';
requireLogin();

require_once '../config/database.php';
require_once '../models/Blog.php';
require_once '../models/User.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();
$blog = new Blog($db);

$id = isset($_GET['id']) ? $_GET['id'] : null;
$isEdit = $id !== null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $data = [
            'title' => $_POST['title'],
            'excerpt' => $_POST['excerpt'],
            'content' => $_POST['content'],
            'category' => $_POST['category'],
            'tags' => array_map('trim', explode(',', $_POST['tags'])),
            'status' => $_POST['status'],
            'featured_image' => $_POST['featured_image'],
            'meta_title' => $_POST['meta_title'],
            'meta_description' => $_POST['meta_description'],
            'meta_keywords' => $_POST['meta_keywords'],
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'author_id' => getCurrentUserId()
        ];
        
        if ($isEdit) {
            if ($blog->update($id, $data)) {
                header("Location: blog.php?msg=updated");
                exit();
            }
        } else {
            if ($blog->create($data)) {
                header("Location: blog.php?msg=created");
                exit();
            }
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get blog data if editing
$postData = [];
if ($isEdit) {
    $postData = $blog->getById($id);
    if (!$postData) {
        header("Location: blog.php");
        exit();
    }
    
    // Decode tags if they're stored as JSON
    if (isset($postData['tags']) && is_string($postData['tags'])) {
        $decodedTags = json_decode($postData['tags'], true);
        $postData['tags_string'] = is_array($decodedTags) ? implode(', ', $decodedTags) : '';
    }
}

// Now include header (after all potential redirects)
require_once 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?php echo $isEdit ? 'Edit Blog Post' : 'Create New Blog Post'; ?></h2>
    <a href="blog.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Blog
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" id="title" class="form-control" required 
                           value="<?php echo htmlspecialchars($postData['title'] ?? ''); ?>">
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">Excerpt</label>
                    <textarea name="excerpt" class="form-control" rows="2"><?php echo htmlspecialchars($postData['excerpt'] ?? ''); ?></textarea>
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">Content *</label>
                    <textarea name="content" id="summernote" class="form-control" rows="10"><?php echo htmlspecialchars($postData['content'] ?? ''); ?></textarea>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-control" 
                           value="<?php echo htmlspecialchars($postData['category'] ?? ''); ?>"
                           placeholder="e.g., Wedding Tips, Photography">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tags (comma separated)</label>
                    <input type="text" name="tags" class="form-control" 
                           value="<?php echo htmlspecialchars($postData['tags_string'] ?? ($postData['tags'] ?? '')); ?>"
                           placeholder="e.g., wedding, tips, photography">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Featured Image URL</label>
                    <input type="text" name="featured_image" class="form-control" 
                           value="<?php echo htmlspecialchars($postData['featured_image'] ?? ''); ?>"
                           placeholder="https://example.com/image.jpg">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Or Upload Image</label>
                    <input type="file" name="image_upload" class="form-control" accept="image/*">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="draft" <?php echo ($postData['status'] ?? 'draft') == 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo ($postData['status'] ?? '') == 'published' ? 'selected' : ''; ?>>Published</option>
                        <option value="archived" <?php echo ($postData['status'] ?? '') == 'archived' ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured"
                               <?php echo ($postData['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_featured">Featured Post</label>
                    </div>
                </div>
                
                <div class="col-12">
                    <h5 class="mt-3">SEO Settings</h5>
                    <hr>
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" 
                           value="<?php echo htmlspecialchars($postData['meta_title'] ?? ''); ?>"
                           placeholder="Leave empty to use post title">
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="2"><?php echo htmlspecialchars($postData['meta_description'] ?? ''); ?></textarea>
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">Meta Keywords</label>
                    <input type="text" name="meta_keywords" class="form-control" 
                           value="<?php echo htmlspecialchars($postData['meta_keywords'] ?? ''); ?>"
                           placeholder="comma, separated, keywords">
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-dark">
                        <i class="bi bi-save"></i> <?php echo $isEdit ? 'Update' : 'Publish'; ?> Post
                    </button>
                    <a href="blog.php" class="btn btn-outline-secondary ms-2">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#summernote').summernote({
        height: 400,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'italic', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onImageUpload: function(files) {
                for(let i = 0; i < files.length; i++) {
                    uploadImage(files[i]);
                }
            }
        }
    });
});

function uploadImage(file) {
    let formData = new FormData();
    formData.append('file', file);
    
    $.ajax({
        url: 'upload-image.php',
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(url) {
            $('#summernote').summernote('insertImage', url);
        },
        error: function() {
            alert('Image upload failed');
        }
    });
}
</script>

<?php 
require_once 'footer.php';
ob_end_flush();
?>