<?php
// admin/slider.php

// Start output buffering
ob_start();

require_once '../config/database.php';
require_once '../models/Slider.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$slider = new Slider($db);

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get image path to delete file
    $slide = $slider->getById($id);
    if ($slide && !empty($slide['image_url'])) {
        // Try to delete local file if it exists
        $filePath = '../' . ltrim($slide['image_url'], '/');
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    if ($slider->delete($id)) {
        header("Location: slider.php?msg=deleted");
        exit();
    }
}

// Handle status toggle
if (isset($_GET['toggle'])) {
    $slide = $slider->getById($_GET['toggle']);
    if ($slide) {
        $newStatus = $slide['is_active'] ? 0 : 1;
        $slider->update($_GET['toggle'], ['is_active' => $newStatus]);
        header("Location: slider.php?msg=toggled");
        exit();
    }
}

// Handle sort order update
if (isset($_POST['update_sort'])) {
    foreach ($_POST['sort'] as $id => $order) {
        $slider->update($id, ['sort_order' => $order]);
    }
    header("Location: slider.php?msg=sorted");
    exit();
}

// Get all slider images
$sliderImages = $slider->getAll('sort_order ASC, created_at DESC');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Slider Images</h2>
    <a href="slider-edit.php" class="btn btn-dark">
        <i class="bi bi-plus-circle"></i> Add New Slide
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        if ($_GET['msg'] == 'created') echo "Slide created successfully!";
        if ($_GET['msg'] == 'updated') echo "Slide updated successfully!";
        if ($_GET['msg'] == 'deleted') echo "Slide deleted successfully!";
        if ($_GET['msg'] == 'toggled') echo "Slide status updated successfully!";
        if ($_GET['msg'] == 'sorted') echo "Sort order updated successfully!";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Slider Preview</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php if ($sliderImages && $sliderImages->rowCount() > 0): ?>
                <?php while ($row = $sliderImages->fetch()): 
                    $imageUrl = $row['image_url'];
                    // Fix path for local images
                    if (strpos($imageUrl, 'uploads/') === 0) {
                        $imageUrl = '../' . $imageUrl;
                    }
                ?>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card h-100 <?php echo !$row['is_active'] ? 'opacity-50' : ''; ?>">
                            <div style="height: 180px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; padding: 10px; border-bottom: 1px solid #ddd;">
                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                     class="img-fluid" 
                                     alt="<?php echo htmlspecialchars($row['title']); ?>"
                                     style="max-height: 160px; max-width: 100%; width: auto; height: auto; object-fit: contain;"
                                     onerror="this.src='https://via.placeholder.com/300x200?text=Image+Not+Found'; this.onerror=null;">
                            </div>
                            <div class="card-body p-2">
                                <h6 class="card-title small mb-1"><?php echo htmlspecialchars($row['title'] ?: 'Untitled'); ?></h6>
                                <small class="text-muted">
                                    <?php if ($row['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                    Order: <?php echo $row['sort_order']; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No slider images yet. <a href="slider-edit.php">Add your first slide</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Slides</h5>
        <button class="btn btn-sm btn-light" onclick="enableSorting()">
            <i class="bi bi-arrow-up-down"></i> Sort
        </button>
    </div>
    <div class="card-body">
        <form method="POST" id="sort-form" style="display: none;">
            <input type="hidden" name="update_sort" value="1">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Preview</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th width="100">Sort Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sortable">
                        <?php if ($sliderImages && $sliderImages->rowCount() > 0): ?>
                            <?php 
                            // Reset pointer
                            $sliderImages = $slider->getAll('sort_order ASC, created_at DESC');
                            while ($row = $sliderImages->fetch()): 
                                $imageUrl = $row['image_url'];
                                if (strpos($imageUrl, 'uploads/') === 0) {
                                    $imageUrl = '../' . $imageUrl;
                                }
                            ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td>
                                        <div style="width: 100px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                            <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                                 alt="<?php echo htmlspecialchars($row['title']); ?>"
                                                 style="max-height: 55px; max-width: 95px; width: auto; height: auto; object-fit: contain;"
                                                 onerror="this.src='https://via.placeholder.com/100x60?text=Error'; this.onerror=null;">
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['title'] ?: 'Untitled'); ?></td>
                                    <td>
                                        <span class="text-truncate" style="max-width: 200px; display: inline-block;">
                                            <?php echo htmlspecialchars($row['description'] ?: 'No description'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="number" name="sort[<?php echo $row['id']; ?>]" 
                                               value="<?php echo $row['sort_order']; ?>" 
                                               class="form-control form-control-sm" style="width: 80px;">
                                    </td>
                                    <td>
                                        <?php if ($row['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="slider-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="slider.php?toggle=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="<?php echo $row['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                            <i class="bi bi-<?php echo $row['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                        </a>
                                        <a href="slider.php?delete=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this slide?')"
                                           data-bs-toggle="tooltip" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No slider images found. <a href="slider-edit.php">Add your first slide</a></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-dark">Update Sort Order</button>
                <button type="button" class="btn btn-secondary" onclick="cancelSorting()">Cancel</button>
            </div>
        </form>

        <div id="normal-view">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Preview</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Sort Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($sliderImages && $sliderImages->rowCount() > 0): ?>
                            <?php 
                            // Reset pointer
                            $sliderImages = $slider->getAll('sort_order ASC, created_at DESC');
                            while ($row = $sliderImages->fetch()): 
                                $imageUrl = $row['image_url'];
                                if (strpos($imageUrl, 'uploads/') === 0) {
                                    $imageUrl = '../' . $imageUrl;
                                }
                            ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td>
                                        <div style="width: 100px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                            <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                                 alt="<?php echo htmlspecialchars($row['title']); ?>"
                                                 style="max-height: 55px; max-width: 95px; width: auto; height: auto; object-fit: contain;"
                                                 onerror="this.src='https://via.placeholder.com/100x60?text=Error'; this.onerror=null;">
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['title'] ?: 'Untitled'); ?></td>
                                    <td>
                                        <span class="text-truncate" style="max-width: 200px; display: inline-block;">
                                            <?php echo htmlspecialchars($row['description'] ?: 'No description'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark"><?php echo $row['sort_order']; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($row['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="slider-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="slider.php?toggle=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="<?php echo $row['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                            <i class="bi bi-<?php echo $row['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                        </a>
                                        <a href="slider.php?delete=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this slide?')"
                                           data-bs-toggle="tooltip" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No slider images found. <a href="slider-edit.php">Add your first slide</a></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function enableSorting() {
    document.getElementById('normal-view').style.display = 'none';
    document.getElementById('sort-form').style.display = 'block';
}

function cancelSorting() {
    document.getElementById('normal-view').style.display = 'block';
    document.getElementById('sort-form').style.display = 'none';
}
</script>

<?php require_once 'footer.php'; ?>
ob_end_flush();
?>