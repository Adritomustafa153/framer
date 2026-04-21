<?php
// admin/project-create.php
ob_start();
require_once '../config/database.php';
require_once '../models/Project.php';
require_once '../models/ProjectFamilyMember.php';
require_once '../models/ProjectAddon.php';
require_once '../models/ProjectTeam.php';
require_once '../models/Package.php';
require_once '../models/AddonService.php';
require_once '../models/User.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$projectModel = new Project($db);
$familyModel = new ProjectFamilyMember($db);
$addonModel = new ProjectAddon($db);
$teamModel = new ProjectTeam($db);
$packageModel = new Package($db);
$addonServiceModel = new AddonService($db);
$userModel = new User($db);

// Get all packages for dropdown
$packages = $packageModel->getActive();

// Get all add-on services from database
$addonServices = $addonServiceModel->getActive();

// Get all users for team assignment (only active users)
$users = $userModel->getActive();

$error = '';
$success = '';

// Pre-fill client data if editing existing project
$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;
$existingProject = null;
if ($project_id) {
    $existingProject = $projectModel->getById($project_id);
    if ($existingProject) {
        $existingFamily = $familyModel->getByProject($project_id);
        $existingAddons = $addonModel->getByProject($project_id);
        $existingTeam = $teamModel->getByProject($project_id);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();
        
        // Calculate total amount
        $package_price = floatval($_POST['package_price']);
        $addons_total = 0;
        
        // Calculate add-ons total
        if (!empty($_POST['addons'])) {
            foreach ($_POST['addons'] as $addon) {
                if (!empty($addon['service_name'])) {
                    $quantity = floatval($addon['quantity'] ?? 1);
                    $unit_price = floatval($addon['unit_price'] ?? 0);
                    $addons_total += ($quantity * $unit_price);
                }
            }
        }
        
        $total_amount = $package_price + $addons_total;
        
        // Create or update project
        if ($project_id) {
            // Update existing project
            $project_data = [
                'project_name' => $_POST['project_name'],
                'event_location' => $_POST['event_location'],
                'bride_name' => $_POST['bride_name'],
                'groom_name' => $_POST['groom_name'],
                'bride_email' => $_POST['bride_email'] ?? null,
                'bride_phone' => $_POST['bride_phone'] ?? null,
                'bride_facebook' => $_POST['bride_facebook'] ?? null,
                'bride_instagram' => $_POST['bride_instagram'] ?? null,
                'groom_email' => $_POST['groom_email'] ?? null,
                'groom_phone' => $_POST['groom_phone'] ?? null,
                'groom_facebook' => $_POST['groom_facebook'] ?? null,
                'groom_instagram' => $_POST['groom_instagram'] ?? null,
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'alternate_phone' => $_POST['alternate_phone'] ?? null,
                'address' => $_POST['address'] ?? null,
                'city' => $_POST['city'] ?? null,
                'state' => $_POST['state'] ?? null,
                'zip_code' => $_POST['zip_code'] ?? null,
                'event_date' => $_POST['event_date'],
                'event_time' => $_POST['event_time'] ?? null,
                'venue_name' => $_POST['venue_name'] ?? null,
                'venue_address' => $_POST['venue_address'] ?? null,
                'special_notes' => $_POST['special_notes'] ?? null,
                'package_id' => $_POST['package_id'] ?? null,
                'package_name' => $_POST['package_name'],
                'package_price' => $package_price,
                'total_amount' => $total_amount,
                'status' => $_POST['status'] ?? 'draft'
            ];
            
            $projectModel->update($project_id, $project_data);
            
            // Delete existing family members, add-ons, and team members
            $familyModel->deleteByProject($project_id);
            $addonModel->deleteByProject($project_id);
            $teamModel->deleteByProject($project_id);
            
        } else {
            // Create new project
            $project_data = [
                'project_name' => $_POST['project_name'],
                'event_location' => $_POST['event_location'],
                'bride_name' => $_POST['bride_name'],
                'groom_name' => $_POST['groom_name'],
                'bride_email' => $_POST['bride_email'] ?? null,
                'bride_phone' => $_POST['bride_phone'] ?? null,
                'bride_facebook' => $_POST['bride_facebook'] ?? null,
                'bride_instagram' => $_POST['bride_instagram'] ?? null,
                'groom_email' => $_POST['groom_email'] ?? null,
                'groom_phone' => $_POST['groom_phone'] ?? null,
                'groom_facebook' => $_POST['groom_facebook'] ?? null,
                'groom_instagram' => $_POST['groom_instagram'] ?? null,
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'alternate_phone' => $_POST['alternate_phone'] ?? null,
                'address' => $_POST['address'] ?? null,
                'city' => $_POST['city'] ?? null,
                'state' => $_POST['state'] ?? null,
                'zip_code' => $_POST['zip_code'] ?? null,
                'event_date' => $_POST['event_date'],
                'event_time' => $_POST['event_time'] ?? null,
                'venue_name' => $_POST['venue_name'] ?? null,
                'venue_address' => $_POST['venue_address'] ?? null,
                'special_notes' => $_POST['special_notes'] ?? null,
                'package_id' => $_POST['package_id'] ?? null,
                'package_name' => $_POST['package_name'],
                'package_price' => $package_price,
                'total_amount' => $total_amount,
                'status' => 'draft',
                'created_by' => getCurrentUserId()
            ];
            
            $project_result = $projectModel->create($project_data);
            $project_id = $project_result['id'];
        }
        
        // Add family members
        if (!empty($_POST['family_members'])) {
            foreach ($_POST['family_members'] as $member) {
                if (!empty($member['name']) && !empty($member['relationship'])) {
                    $familyModel->create([
                        'project_id' => $project_id,
                        'name' => $member['name'],
                        'relationship' => $member['relationship'],
                        'side' => $member['side'] ?? 'bride',
                        'phone' => $member['phone'] ?? null,
                        'notes' => $member['notes'] ?? null
                    ]);
                }
            }
        }
        
        // Add add-ons
        if (!empty($_POST['addons'])) {
            foreach ($_POST['addons'] as $addon) {
                if (!empty($addon['service_name'])) {
                    $quantity = floatval($addon['quantity'] ?? 1);
                    $unit_price = floatval($addon['unit_price'] ?? 0);
                    
                    $addonModel->create([
                        'project_id' => $project_id,
                        'addon_id' => !empty($addon['addon_id']) ? (int)$addon['addon_id'] : null,
                        'service_name' => $addon['service_name'],
                        'description' => $addon['description'] ?? '',
                        'quantity' => $quantity,
                        'unit_price' => $unit_price,
                        'total_price' => $quantity * $unit_price
                    ]);
                }
            }
        }
        
        // Assign team members
        if (!empty($_POST['team_members'])) {
            foreach ($_POST['team_members'] as $member) {
                if (!empty($member['user_id']) && !empty($member['role'])) {
                    $teamModel->assign([
                        'project_id' => $project_id,
                        'user_id' => $member['user_id'],
                        'role' => $member['role'],
                        'assigned_by' => getCurrentUserId()
                    ]);
                }
            }
        }
        
        $db->commit();
        
        if ($project_id) {
            header("Location: project-view.php?id=$project_id&msg=updated");
        } else {
            header("Location: projects.php?msg=created");
        }
        exit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Helper function to generate JSON for existing data
$existingFamilyJson = isset($existingFamily) ? json_encode($existingFamily) : '[]';
$existingAddonsJson = isset($existingAddons) ? json_encode($existingAddons) : '[]';
$existingTeamJson = isset($existingTeam) ? json_encode($existingTeam) : '[]';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?php echo $project_id ? 'Edit Project' : 'Create New Project'; ?></h2>
    <a href="projects.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Projects
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <form method="POST" id="projectForm">
            <!-- Project Basic Info -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Project Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Project Name *</label>
                            <input type="text" name="project_name" class="form-control" required 
                                   value="<?php echo htmlspecialchars($existingProject['project_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Event Location</label>
                            <input type="text" name="event_location" class="form-control" 
                                   value="<?php echo htmlspecialchars($existingProject['event_location'] ?? ''); ?>">
                        </div>
                    </div>
                    <?php if ($project_id): ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="draft" <?php echo ($existingProject['status'] ?? '') == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="active" <?php echo ($existingProject['status'] ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="completed" <?php echo ($existingProject['status'] ?? '') == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo ($existingProject['status'] ?? '') == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Bride & Groom Info -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Bride & Groom Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bride Name *</label>
                            <input type="text" name="bride_name" class="form-control" required 
                                   value="<?php echo htmlspecialchars($existingProject['bride_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Groom Name *</label>
                            <input type="text" name="groom_name" class="form-control" required 
                                   value="<?php echo htmlspecialchars($existingProject['groom_name'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="card mb-3 bg-light">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Bride's Contact Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Bride's Email</label>
                                    <input type="email" name="bride_email" class="form-control" 
                                           value="<?php echo htmlspecialchars($existingProject['bride_email'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Bride's Phone</label>
                                    <input type="text" name="bride_phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($existingProject['bride_phone'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Bride's Facebook</label>
                                    <input type="url" name="bride_facebook" class="form-control" 
                                           value="<?php echo htmlspecialchars($existingProject['bride_facebook'] ?? ''); ?>" 
                                           placeholder="https://facebook.com/...">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Bride's Instagram</label>
                                    <input type="url" name="bride_instagram" class="form-control" 
                                           value="<?php echo htmlspecialchars($existingProject['bride_instagram'] ?? ''); ?>" 
                                           placeholder="https://instagram.com/...">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3 bg-light">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Groom's Contact Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Groom's Email</label>
                                    <input type="email" name="groom_email" class="form-control" 
                                           value="<?php echo htmlspecialchars($existingProject['groom_email'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Groom's Phone</label>
                                    <input type="text" name="groom_phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($existingProject['groom_phone'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Groom's Facebook</label>
                                    <input type="url" name="groom_facebook" class="form-control" 
                                           value="<?php echo htmlspecialchars($existingProject['groom_facebook'] ?? ''); ?>" 
                                           placeholder="https://facebook.com/...">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Groom's Instagram</label>
                                    <input type="url" name="groom_instagram" class="form-control" 
                                           value="<?php echo htmlspecialchars($existingProject['groom_instagram'] ?? ''); ?>" 
                                           placeholder="https://instagram.com/...">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Primary Email *</label>
                            <input type="email" name="email" class="form-control" required 
                                   value="<?php echo htmlspecialchars($existingProject['email'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Primary Phone *</label>
                            <input type="text" name="phone" class="form-control" required 
                                   value="<?php echo htmlspecialchars($existingProject['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alternate Phone</label>
                        <input type="text" name="alternate_phone" class="form-control" 
                               value="<?php echo htmlspecialchars($existingProject['alternate_phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($existingProject['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" 
                                   value="<?php echo htmlspecialchars($existingProject['city'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" 
                                   value="<?php echo htmlspecialchars($existingProject['state'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">ZIP Code</label>
                            <input type="text" name="zip_code" class="form-control" 
                                   value="<?php echo htmlspecialchars($existingProject['zip_code'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Family Members -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Family Members</h5>
                    <button type="button" class="btn btn-sm btn-light" onclick="addFamilyMember()">
                        <i class="bi bi-plus-circle"></i> Add Member
                    </button>
                </div>
                <div class="card-body">
                    <div id="family-members-container"></div>
                    <small class="text-muted">Add family members from both bride's and groom's side</small>
                </div>
            </div>
            
            <!-- Event Details -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Event Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Package *</label>
                            <select name="package_id" class="form-control" id="package_id" onchange="updatePackageDetails()">
                                <option value="">-- Select Package --</option>
                                <?php foreach ($packages as $p): ?>
                                    <option value="<?php echo $p['id']; ?>" 
                                            data-price="<?php echo $p['price']; ?>"
                                            data-name="<?php echo htmlspecialchars($p['package_name']); ?>"
                                            data-currency="<?php echo $p['currency']; ?>"
                                            data-duration="<?php echo htmlspecialchars($p['duration'] ?? ''); ?>"
                                            data-description="<?php echo htmlspecialchars($p['description'] ?? ''); ?>"
                                            <?php echo ($existingProject['package_id'] ?? '') == $p['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['package_name']); ?> - 
                                        <?php echo $p['currency']; ?> <?php echo number_format($p['price'], 2); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Package Name *</label>
                            <input type="text" name="package_name" class="form-control" required id="package_name" 
                                   value="<?php echo htmlspecialchars($existingProject['package_name'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Package Price *</label>
                            <input type="number" step="0.01" name="package_price" class="form-control" required id="package_price" 
                                   value="<?php echo htmlspecialchars($existingProject['package_price'] ?? 0); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Currency</label>
                            <input type="text" name="currency" class="form-control" id="currency" value="BDT" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Event Type</label>
                            <select name="event_type" class="form-control">
                                <option value="Wedding" <?php echo ($existingProject['event_type'] ?? 'Wedding') == 'Wedding' ? 'selected' : ''; ?>>Wedding</option>
                                <option value="Engagement" <?php echo ($existingProject['event_type'] ?? '') == 'Engagement' ? 'selected' : ''; ?>>Engagement</option>
                                <option value="Pre-wedding" <?php echo ($existingProject['event_type'] ?? '') == 'Pre-wedding' ? 'selected' : ''; ?>>Pre-wedding</option>
                                <option value="Anniversary" <?php echo ($existingProject['event_type'] ?? '') == 'Anniversary' ? 'selected' : ''; ?>>Anniversary</option>
                                <option value="Birthday" <?php echo ($existingProject['event_type'] ?? '') == 'Birthday' ? 'selected' : ''; ?>>Birthday</option>
                                <option value="Corporate" <?php echo ($existingProject['event_type'] ?? '') == 'Corporate' ? 'selected' : ''; ?>>Corporate</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Event Date *</label>
                            <input type="date" name="event_date" class="form-control" required 
                                   value="<?php echo htmlspecialchars($existingProject['event_date'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Event Time</label>
                            <input type="time" name="event_time" class="form-control" 
                                   value="<?php echo htmlspecialchars($existingProject['event_time'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Venue Name</label>
                        <input type="text" name="venue_name" class="form-control" 
                               value="<?php echo htmlspecialchars($existingProject['venue_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Venue Address</label>
                        <textarea name="venue_address" class="form-control" rows="2"><?php echo htmlspecialchars($existingProject['venue_address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Special Notes</label>
                        <textarea name="special_notes" class="form-control" rows="3"><?php echo htmlspecialchars($existingProject['special_notes'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Add-ons with Database Dropdown -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Add-on Services</h5>
                    <button type="button" class="btn btn-sm btn-light" onclick="addAddon()">
                        <i class="bi bi-plus-circle"></i> Add Add-on
                    </button>
                </div>
                <div class="card-body">
                    <div id="addons-container"></div>
                    <div class="mt-3 text-end">
                        <strong>Add-ons Total: ৳ <span id="addons-total">0.00</span></strong>
                    </div>
                </div>
            </div>
            
            <!-- Team Assignment with Database Dropdown -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Team Assignment</h5>
                    <button type="button" class="btn btn-sm btn-light" onclick="addTeamMember()">
                        <i class="bi bi-plus-circle"></i> Add Team Member
                    </button>
                </div>
                <div class="card-body">
                    <div id="team-container"></div>
                    <small class="text-muted">Assign photographers, cinematographers, editors, and accounts team members</small>
                </div>
            </div>
            
            <div class="text-center mb-4">
                <button type="submit" class="btn btn-dark btn-lg px-5">
                    <i class="bi bi-save"></i> <?php echo $project_id ? 'Update' : 'Create'; ?> Project
                </button>
            </div>
        </form>
    </div>
    
    <div class="col-md-4">
        <div class="card sticky-top" style="top: 100px;">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Project Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                     <tr>
                        <td>Package Price:</td>
                        <td class="text-end" id="summary_price">0.00</td>
                    </tr>
                    <tr>
                        <td>Add-ons Total:</td>
                        <td class="text-end" id="summary_addons">0.00</td>
                    </tr>
                    <tr class="fw-bold">
                        <td>Total Amount:</td>
                        <td class="text-end text-primary" id="summary_total">0.00</td>
                    </tr>
                </table>
                <hr>
                <p class="small text-muted mb-0">
                    <i class="bi bi-info-circle"></i> After creating the project, you can generate and send invoice from the project view page.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
let familyMemberCount = 0;
let addonCount = 0;
let teamCount = 0;

// Add-on services data from database
const addonServices = <?php 
    $addonArray = [];
    foreach ($addonServices as $service) {
        $addonArray[] = [
            'id' => $service['id'],
            'name' => $service['service_name'],
            'price' => $service['price'],
            'price_type' => $service['price_type'],
            'description' => $service['description']
        ];
    }
    echo json_encode($addonArray);
?>;

// Users data from database
const teamUsers = <?php 
    $userArray = [];
    foreach ($users as $user) {
        $userArray[] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ];
    }
    echo json_encode($userArray);
?>;

// Existing data for edit
let existingFamily = <?php echo $existingFamilyJson; ?>;
let existingAddons = <?php echo $existingAddonsJson; ?>;
let existingTeam = <?php echo $existingTeamJson; ?>;

// Load existing data
function loadExistingData() {
    // Load family members
    if (existingFamily && existingFamily.length) {
        existingFamily.forEach(member => {
            addFamilyMember(member);
        });
    }
    
    // Load add-ons
    if (existingAddons && existingAddons.length) {
        existingAddons.forEach(addon => {
            addAddonFromExisting(addon);
        });
    }
    
    // Load team members
    if (existingTeam && existingTeam.length) {
        existingTeam.forEach(member => {
            addTeamMemberFromExisting(member);
        });
    }
    
    // Update totals
    calculateAddonsTotal();
}

function addFamilyMember(data = null) {
    const container = document.getElementById('family-members-container');
    const id = familyMemberCount;
    const name = data ? data.name : '';
    const side = data ? data.side : 'bride';
    const relationship = data ? data.relationship : 'father';
    const phone = data ? data.phone : '';
    
    const html = `
        <div class="family-member-item card mb-2 p-3" id="family-${id}">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <input type="text" name="family_members[${id}][name]" class="form-control" placeholder="Full Name" value="${escapeHtml(name)}">
                </div>
                <div class="col-md-2 mb-2">
                    <select name="family_members[${id}][side]" class="form-control">
                        <option value="bride" ${side == 'bride' ? 'selected' : ''}>Bride's Side</option>
                        <option value="groom" ${side == 'groom' ? 'selected' : ''}>Groom's Side</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="family_members[${id}][relationship]" class="form-control">
                        <option value="father" ${relationship == 'father' ? 'selected' : ''}>Father</option>
                        <option value="mother" ${relationship == 'mother' ? 'selected' : ''}>Mother</option>
                        <option value="brother" ${relationship == 'brother' ? 'selected' : ''}>Brother</option>
                        <option value="sister" ${relationship == 'sister' ? 'selected' : ''}>Sister</option>
                        <option value="other" ${relationship == 'other' ? 'selected' : ''}>Other</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" name="family_members[${id}][phone]" class="form-control" placeholder="Phone Number" value="${escapeHtml(phone)}">
                </div>
                <div class="col-md-2 mb-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeFamilyMember(${id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    familyMemberCount++;
}

function removeFamilyMember(id) {
    document.getElementById(`family-${id}`).remove();
}

// Add add-on from database dropdown
function addAddon() {
    const container = document.getElementById('addons-container');
    const id = addonCount;
    
    // Create dropdown options from database
    let dropdownOptions = '<option value="">-- Select Add-on Service --</option>';
    addonServices.forEach(service => {
        dropdownOptions += `<option value="${service.id}" data-price="${service.price}" data-name="${escapeHtml(service.name)}" data-description="${escapeHtml(service.description || '')}">
            ${escapeHtml(service.name)} - ৳ ${service.price} (${service.price_type})
        </option>`;
    });
    
    const html = `
        <div class="addon-item card mb-2 p-3" id="addon-${id}">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <select name="addons[${id}][addon_id]" class="form-control addon-select" data-id="${id}" onchange="selectAddon(${id})">
                        ${dropdownOptions}
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" name="addons[${id}][description]" class="form-control" placeholder="Description" id="addon-desc-${id}" readonly>
                </div>
                <div class="col-md-2 mb-2">
                    <input type="number" name="addons[${id}][quantity]" class="form-control" placeholder="Qty" value="1" min="1" onchange="updateAddonTotal(${id})">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="number" step="0.01" name="addons[${id}][unit_price]" class="form-control" placeholder="Unit Price" required id="addon-price-${id}" value="0" onchange="updateAddonTotal(${id})">
                </div>
                <div class="col-md-1 mb-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeAddon(${id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12 text-end">
                    <small>Total: ৳ <span id="addon-total-${id}">0.00</span></small>
                </div>
            </div>
            <input type="hidden" name="addons[${id}][service_name]" id="addon-name-${id}" value="">
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    addonCount++;
}

function selectAddon(id) {
    const select = document.querySelector(`#addon-${id} .addon-select`);
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        const price = selectedOption.dataset.price;
        const name = selectedOption.dataset.name;
        const description = selectedOption.dataset.description;
        
        document.getElementById(`addon-name-${id}`).value = name;
        document.getElementById(`addon-desc-${id}`).value = description;
        document.getElementById(`addon-price-${id}`).value = price;
        
        updateAddonTotal(id);
    } else {
        document.getElementById(`addon-name-${id}`).value = '';
        document.getElementById(`addon-desc-${id}`).value = '';
        document.getElementById(`addon-price-${id}`).value = 0;
        document.getElementById(`addon-total-${id}`).innerText = '0.00';
    }
}

function addAddonFromExisting(data) {
    const container = document.getElementById('addons-container');
    const id = addonCount;
    const addon_id = data.addon_id || '';
    const service_name = data.service_name || '';
    const description = data.description || '';
    const quantity = data.quantity || 1;
    const unit_price = data.unit_price || 0;
    const total_price = (quantity * unit_price).toFixed(2);
    
    // Create dropdown options from database
    let dropdownOptions = '<option value="">-- Select Add-on Service --</option>';
    addonServices.forEach(service => {
        const selected = (service.id == addon_id) ? 'selected' : '';
        dropdownOptions += `<option value="${service.id}" data-price="${service.price}" data-name="${escapeHtml(service.name)}" data-description="${escapeHtml(service.description || '')}" ${selected}>
            ${escapeHtml(service.name)} - ৳ ${service.price} (${service.price_type})
        </option>`;
    });
    
    const html = `
        <div class="addon-item card mb-2 p-3" id="addon-${id}">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <select name="addons[${id}][addon_id]" class="form-control addon-select" data-id="${id}" onchange="selectAddon(${id})">
                        ${dropdownOptions}
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" name="addons[${id}][description]" class="form-control" placeholder="Description" id="addon-desc-${id}" value="${escapeHtml(description)}" readonly>
                </div>
                <div class="col-md-2 mb-2">
                    <input type="number" name="addons[${id}][quantity]" class="form-control" placeholder="Qty" value="${quantity}" min="1" onchange="updateAddonTotal(${id})">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="number" step="0.01" name="addons[${id}][unit_price]" class="form-control" placeholder="Unit Price" required id="addon-price-${id}" value="${unit_price}" onchange="updateAddonTotal(${id})">
                </div>
                <div class="col-md-1 mb-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeAddon(${id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12 text-end">
                    <small>Total: ৳ <span id="addon-total-${id}">${total_price}</span></small>
                </div>
            </div>
            <input type="hidden" name="addons[${id}][service_name]" id="addon-name-${id}" value="${escapeHtml(service_name)}">
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    addonCount++;
    calculateAddonsTotal();
}

function removeAddon(id) {
    document.getElementById(`addon-${id}`).remove();
    calculateAddonsTotal();
}

function updateAddonTotal(id) {
    const qty = parseFloat(document.querySelector(`#addon-${id} input[name="addons[${id}][quantity]"]`).value) || 0;
    const price = parseFloat(document.querySelector(`#addon-${id} input[name="addons[${id}][unit_price]"]`).value) || 0;
    const total = qty * price;
    document.getElementById(`addon-total-${id}`).innerText = total.toFixed(2);
    calculateAddonsTotal();
}

function calculateAddonsTotal() {
    let total = 0;
    for (let i = 0; i < addonCount; i++) {
        const el = document.getElementById(`addon-${i}`);
        if (el) {
            const totalSpan = document.getElementById(`addon-total-${i}`);
            if (totalSpan) {
                total += parseFloat(totalSpan.innerText) || 0;
            }
        }
    }
    document.getElementById('addons-total').innerText = total.toFixed(2);
    updateSummary();
}

// Team Member Functions
function addTeamMember() {
    const container = document.getElementById('team-container');
    const id = teamCount;
    
    // Create user dropdown options from database
    let userOptions = '<option value="">-- Select User --</option>';
    teamUsers.forEach(user => {
        userOptions += `<option value="${user.id}" data-role="${user.role}">
            ${escapeHtml(user.username)} (${user.full_name || user.username}) - ${user.role}
        </option>`;
    });
    
    const html = `
        <div class="team-member-item card mb-2 p-3" id="team-${id}">
            <div class="row">
                <div class="col-md-5 mb-2">
                    <select name="team_members[${id}][user_id]" class="form-control" required onchange="updateUserRole(${id})">
                        ${userOptions}
                    </select>
                </div>
                <div class="col-md-5 mb-2">
                    <select name="team_members[${id}][role]" class="form-control" required id="team-role-${id}">
                        <option value="">-- Select Role --</option>
                        <option value="photographer">Photographer</option>
                        <option value="cinematographer">Cinematographer</option>
                        <option value="editor">Editor</option>
                        <option value="accounts">Accounts</option>
                        <option value="coordinator">Coordinator</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeTeamMember(${id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    teamCount++;
}

function addTeamMemberFromExisting(data) {
    const container = document.getElementById('team-container');
    const id = teamCount;
    const user_id = data.user_id || '';
    const role = data.role || 'photographer';
    
    // Create user dropdown options from database
    let userOptions = '<option value="">-- Select User --</option>';
    teamUsers.forEach(user => {
        const selected = (user.id == user_id) ? 'selected' : '';
        userOptions += `<option value="${user.id}" data-role="${user.role}" ${selected}>
            ${escapeHtml(user.username)} (${user.full_name || user.username}) - ${user.role}
        </option>`;
    });
    
    const html = `
        <div class="team-member-item card mb-2 p-3" id="team-${id}">
            <div class="row">
                <div class="col-md-5 mb-2">
                    <select name="team_members[${id}][user_id]" class="form-control" required onchange="updateUserRole(${id})">
                        ${userOptions}
                    </select>
                </div>
                <div class="col-md-5 mb-2">
                    <select name="team_members[${id}][role]" class="form-control" required id="team-role-${id}">
                        <option value="">-- Select Role --</option>
                        <option value="photographer" ${role == 'photographer' ? 'selected' : ''}>Photographer</option>
                        <option value="cinematographer" ${role == 'cinematographer' ? 'selected' : ''}>Cinematographer</option>
                        <option value="editor" ${role == 'editor' ? 'selected' : ''}>Editor</option>
                        <option value="accounts" ${role == 'accounts' ? 'selected' : ''}>Accounts</option>
                        <option value="coordinator" ${role == 'coordinator' ? 'selected' : ''}>Coordinator</option>
                        <option value="admin" ${role == 'admin' ? 'selected' : ''}>Admin</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeTeamMember(${id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    teamCount++;
}

function updateUserRole(id) {
    const userSelect = document.querySelector(`#team-${id} select[name="team_members[${id}][user_id]"]`);
    const selectedOption = userSelect.options[userSelect.selectedIndex];
    const userRole = selectedOption.dataset.role;
    const roleSelect = document.getElementById(`team-role-${id}`);
    
    if (userRole && roleSelect) {
        // Auto-select role based on user's role
        if (roleSelect.querySelector(`option[value="${userRole}"]`)) {
            roleSelect.value = userRole;
        }
    }
}

function removeTeamMember(id) {
    document.getElementById(`team-${id}`).remove();
}

function updatePackageDetails() {
    const select = document.getElementById('package_id');
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        const price = option.dataset.price;
        const name = option.dataset.name;
        const currency = option.dataset.currency;
        
        document.getElementById('package_name').value = name;
        document.getElementById('package_price').value = price;
        document.getElementById('currency').value = currency;
        
        updateSummary();
    } else {
        document.getElementById('package_name').value = '';
        document.getElementById('package_price').value = '';
        document.getElementById('currency').value = 'BDT';
    }
}

function updateSummary() {
    const price = parseFloat(document.getElementById('package_price').value) || 0;
    const addons = parseFloat(document.getElementById('addons-total').innerText) || 0;
    const total = price + addons;
    
    document.getElementById('summary_price').innerText = price.toFixed(2);
    document.getElementById('summary_addons').innerText = addons.toFixed(2);
    document.getElementById('summary_total').innerText = total.toFixed(2);
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

document.getElementById('package_price').addEventListener('input', updateSummary);

// Load existing data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadExistingData();
    updatePackageDetails();
    updateSummary();
});
</script>

<?php require_once 'footer.php'; ?>
ob_end_flush();