<?php
// admin/faq-edit.php

// Start output buffering
ob_start();

require_once '../config/database.php';
require_once '../models/FAQ.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$faq = new FAQ($db);

$id = isset($_GET['id']) ? $_GET['id'] : null;
$isEdit = $id !== null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'question' => $_POST['question'],
        'answer' => $_POST['answer'],
        'category' => $_POST['category'],
        'sort_order' => $_POST['sort_order'] ?? 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];
    
    if ($isEdit) {
        if ($faq->update($id, $data)) {
            header("Location: faq.php?msg=updated");
            exit();
        }
    } else {
        if ($faq->create($data)) {
            header("Location: faq.php?msg=created");
            exit();
        }
    }
}

// Get data if editing
$itemData = [];
if ($isEdit) {
    $itemData = $faq->getById($id);
    if (!$itemData) {
        header("Location: faq.php");
        exit();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?php echo $isEdit ? 'Edit FAQ' : 'Add New FAQ'; ?></h2>
    <a href="faq.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to FAQs
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Question *</label>
                        <input type="text" name="question" class="form-control" required 
                               value="<?php echo htmlspecialchars($itemData['question'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Answer *</label>
                        <textarea name="answer" class="form-control" rows="6" required><?php echo htmlspecialchars($itemData['answer'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Settings</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control" 
                                       value="<?php echo htmlspecialchars($itemData['category'] ?? ''); ?>"
                                       placeholder="e.g., General, Booking, Pricing">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" 
                                       value="<?php echo htmlspecialchars($itemData['sort_order'] ?? 0); ?>" min="0">
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active"
                                           <?php echo ($itemData['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-dark">
                    <i class="bi bi-save"></i> <?php echo $isEdit ? 'Update' : 'Create'; ?> FAQ
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
require_once 'footer.php';
ob_end_flush();
?>