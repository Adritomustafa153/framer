<?php
// admin/project-view.php
ob_start();
require_once '../config/database.php';
require_once '../models/Project.php';
require_once '../models/ProjectFamilyMember.php';
require_once '../models/ProjectAddon.php';
require_once '../models/ProjectTeam.php';
require_once '../models/ProjectPayment.php';
require_once '../models/ProjectInvoice.php';
require_once '../models/Package.php';
require_once '../models/AddonService.php';
require_once '../models/Client.php';
require_once '../models/Booking.php';
require_once '../includes/invoice_generator.php';
require_once '../includes/mail.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$projectModel = new Project($db);
$familyModel = new ProjectFamilyMember($db);
$addonModel = new ProjectAddon($db);
$teamModel = new ProjectTeam($db);
$paymentModel = new ProjectPayment($db);
$invoiceModel = new ProjectInvoice($db);
$packageModel = new Package($db);
$addonServiceModel = new AddonService($db);
$clientModel = new Client($db);
$bookingModel = new Booking($db);

// Get project with details
$project = $projectModel->getWithDetails($id);
if (!$project || empty($project)) {
    $_SESSION['error'] = "Project not found!";
    header("Location: projects.php");
    exit();
}

// Get all data
$total_paid = $paymentModel->getTotalPaidByProject($id);
$addons = $addonModel->getByProject($id);
$addons_total = $addonModel->getTotalByProject($id);
$family = $familyModel->getByProject($id);
$team = $teamModel->getByProject($id);
$payments = $paymentModel->getByProject($id);
$invoices = $invoiceModel->getByProject($id);

// Calculate due amount
$due = ($project['total_amount'] ?? 0) - $total_paid;

// Status badge class
$status_class = [
    'draft' => 'secondary',
    'active' => 'primary',
    'completed' => 'success',
    'cancelled' => 'danger'
][$project['status'] ?? 'draft'] ?? 'secondary';

$error = '';
$success = '';

// Handle generate invoice
if (isset($_POST['generate_invoice'])) {
    $send_email = isset($_POST['send_email']) ? 1 : 0;
    
    try {
        $db->beginTransaction();
        
        // Step 1: Create or update client record
        $client = $clientModel->getByEmail($project['email']);
        
        if (!$client) {
            // Create new client
            $client_data = [
                'bride_name' => $project['bride_name'] ?? '',
                'groom_name' => $project['groom_name'] ?? '',
                'email' => $project['email'],
                'bride_email' => $project['bride_email'] ?? null,
                'bride_phone' => $project['bride_phone'] ?? null,
                'bride_facebook' => $project['bride_facebook'] ?? null,
                'bride_instagram' => $project['bride_instagram'] ?? null,
                'groom_email' => $project['groom_email'] ?? null,
                'groom_phone' => $project['groom_phone'] ?? null,
                'groom_facebook' => $project['groom_facebook'] ?? null,
                'groom_instagram' => $project['groom_instagram'] ?? null,
                'phone' => $project['phone'],
                'alternate_phone' => $project['alternate_phone'] ?? null,
                'address' => $project['address'] ?? null,
                'city' => $project['city'] ?? null,
                'state' => $project['state'] ?? null,
                'zip_code' => $project['zip_code'] ?? null,
                'country' => 'Bangladesh'
            ];
            
            $client_id = $clientModel->create($client_data);
            if (!$client_id) {
                throw new Exception("Failed to create client record");
            }
        } else {
            $client_id = $client['id'];
            // Update client with latest info
            $clientModel->update($client_id, [
                'bride_name' => $project['bride_name'] ?? '',
                'groom_name' => $project['groom_name'] ?? '',
                'bride_email' => $project['bride_email'] ?? null,
                'bride_phone' => $project['bride_phone'] ?? null,
                'bride_facebook' => $project['bride_facebook'] ?? null,
                'bride_instagram' => $project['bride_instagram'] ?? null,
                'groom_email' => $project['groom_email'] ?? null,
                'groom_phone' => $project['groom_phone'] ?? null,
                'groom_facebook' => $project['groom_facebook'] ?? null,
                'groom_instagram' => $project['groom_instagram'] ?? null,
                'phone' => $project['phone'],
                'alternate_phone' => $project['alternate_phone'] ?? null,
                'address' => $project['address'] ?? null,
                'city' => $project['city'] ?? null,
                'state' => $project['state'] ?? null,
                'zip_code' => $project['zip_code'] ?? null
            ]);
        }
        
        // Step 2: Create booking record
        $booking_number = 'BK' . date('Ymd') . str_pad($id, 4, '0', STR_PAD_LEFT);
        
        $booking_data = [
            'booking_number' => $booking_number,
            'client_id' => $client_id,
            'package_id' => $project['package_id'] ?? null,
            'package_name' => $project['package_name'] ?? '',
            'package_price' => $project['package_price'] ?? 0,
            'event_type' => 'Wedding',
            'event_date' => $project['event_date'],
            'event_time' => $project['event_time'],
            'venue_name' => $project['venue_name'] ?? null,
            'venue_address' => $project['venue_address'] ?? null,
            'special_notes' => $project['special_notes'] ?? null,
            'status' => 'confirmed',
            'booking_date' => date('Y-m-d H:i:s'),
            'created_by' => getCurrentUserId()
        ];
        
        $booking_id = $bookingModel->create($booking_data);
        
        if (!$booking_id || !is_numeric($booking_id) || $booking_id <= 0) {
            throw new Exception("Failed to create booking record - returned: " . print_r($booking_id, true));
        }
        
        // Step 3: Add addons to booking_addons table
        if (!empty($addons) && is_array($addons)) {
            $addonQuery = "INSERT INTO booking_addons 
                          (booking_id, addon_id, service_name, description, quantity, unit_price, total_price, created_at)
                          VALUES (:booking_id, :addon_id, :service_name, :description, :quantity, :unit_price, :total_price, NOW())";
            
            $addonStmt = $db->prepare($addonQuery);
            
            foreach ($addons as $addon) {
                if (empty($addon['service_name'])) {
                    continue;
                }
                
                $quantity = isset($addon['quantity']) ? floatval($addon['quantity']) : 1;
                $unit_price = isset($addon['unit_price']) ? floatval($addon['unit_price']) : 0;
                $total_price = $quantity * $unit_price;
                
                $addonStmt->execute([
                    ':booking_id' => $booking_id,
                    ':addon_id' => isset($addon['addon_id']) && !empty($addon['addon_id']) ? (int)$addon['addon_id'] : null,
                    ':service_name' => $addon['service_name'],
                    ':description' => isset($addon['description']) ? (string)$addon['description'] : '',
                    ':quantity' => $quantity,
                    ':unit_price' => $unit_price,
                    ':total_price' => $total_price
                ]);
            }
        }
        
        // Step 4: Create invoice record
        $invoice_data = [
            'project_id' => $id,
            'total_amount' => $project['total_amount'] ?? 0,
            'paid_amount' => $total_paid,
            'due_amount' => $due,
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => $due <= 0 ? 'paid' : ($total_paid > 0 ? 'partial' : 'unpaid')
        ];
        
        $invoice_result = $invoiceModel->create($invoice_data);
        
        if (!$invoice_result || $invoice_result === false) {
            throw new Exception("Failed to create invoice record - create method returned false");
        }
        
        if (is_array($invoice_result) && isset($invoice_result['id']) && isset($invoice_result['invoice_number'])) {
            $invoice_id = $invoice_result['id'];
            $invoice_number = $invoice_result['invoice_number'];
        } else {
            throw new Exception("Failed to create invoice record - invalid return structure");
        }
        
        if (!$invoice_id || $invoice_id <= 0) {
            throw new Exception("Failed to create invoice record - invalid invoice ID");
        }
        
        // Step 5: Generate PDF invoice
        $invoiceGen = new InvoiceGenerator($db);
        $pdf_result = $invoiceGen->generateInvoice($booking_id, $invoice_id);
        
        if ($pdf_result && isset($pdf_result['success']) && $pdf_result['success']) {
            // Update invoice with PDF path
            $invoiceModel->update($invoice_id, [
                'paid_amount' => $total_paid,
                'due_amount' => $due,
                'status' => $due <= 0 ? 'paid' : ($total_paid > 0 ? 'partial' : 'unpaid'),
                'pdf_path' => $pdf_result['pdf_path']
            ]);
            
            // Step 6: Send email if requested
            if ($send_email && !empty($project['email'])) {
                $client_info = [
                    'client_name' => ($project['bride_name'] ?? '') . ' & ' . ($project['groom_name'] ?? ''),
                    'email' => $project['email'],
                    'invoice_number' => $invoice_number,
                    'booking_number' => $booking_number,
                    'package_name' => $project['package_name'] ?? '',
                    'total_amount' => $project['total_amount'] ?? 0,
                    'paid_amount' => $total_paid,
                    'due_amount' => $due,
                    'event_date' => $project['event_date'] ?? date('Y-m-d'),
                    'venue_name' => $project['venue_name'] ?? 'TBD'
                ];
                
                $mailer = new Mailer($db);
                $mail_result = $mailer->sendInvoice(
                    $project['email'],
                    $client_info['client_name'],
                    $client_info,
                    '../' . $pdf_result['pdf_path']
                );
                
                if ($mail_result['success']) {
                    $invoiceModel->markEmailSent($invoice_id);
                    $projectModel->updateInvoiceSent($id, 1);
                    $success = "Invoice generated and email sent successfully!";
                } else {
                    $success = "Invoice generated but email failed to send: " . $mail_result['message'];
                }
            } else {
                $success = "Invoice generated successfully!";
                if ($send_email && empty($project['email'])) {
                    $success .= " (Email not sent - no email address available)";
                }
            }
        } else {
            $errorMsg = is_array($pdf_result) && isset($pdf_result['message']) ? $pdf_result['message'] : 'Unknown error';
            throw new Exception("Failed to generate PDF: " . $errorMsg);
        }
        
        $db->commit();
        
        // Refresh data
        $invoices = $invoiceModel->getByProject($id);
        $total_paid = $paymentModel->getTotalPaidByProject($id);
        $due = ($project['total_amount'] ?? 0) - $total_paid;
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Error: " . $e->getMessage();
        error_log("Invoice Generation Error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
    }
}

// Handle resend email
if (isset($_GET['resend_email']) && isset($_GET['invoice_id'])) {
    $invoice_id = (int)$_GET['invoice_id'];
    $invoice = $invoiceModel->getById($invoice_id);
    
    if ($invoice && isset($invoice['pdf_path']) && file_exists('../' . $invoice['pdf_path'])) {
        $client_info = [
            'client_name' => ($project['bride_name'] ?? '') . ' & ' . ($project['groom_name'] ?? ''),
            'email' => $project['email'] ?? '',
            'invoice_number' => $invoice['invoice_number'] ?? '',
            'booking_number' => $project['project_code'] ?? '',
            'package_name' => $project['package_name'] ?? '',
            'total_amount' => $invoice['total_amount'] ?? 0,
            'paid_amount' => $invoice['paid_amount'] ?? 0,
            'due_amount' => $invoice['due_amount'] ?? 0,
            'event_date' => $project['event_date'] ?? date('Y-m-d'),
            'venue_name' => $project['venue_name'] ?? 'TBD'
        ];
        
        if (!empty($client_info['email'])) {
            $mailer = new Mailer($db);
            $mail_result = $mailer->sendInvoice(
                $client_info['email'],
                $client_info['client_name'],
                $client_info,
                '../' . $invoice['pdf_path']
            );
            
            if ($mail_result['success']) {
                $invoiceModel->markEmailSent($invoice_id);
                $success = "Email resent successfully!";
            } else {
                $error = "Failed to send email: " . $mail_result['message'];
            }
        } else {
            $error = "No email address available";
        }
    } else {
        $error = "Invoice PDF not found";
    }
}

// Refresh project data after invoice generation
$project = $projectModel->getWithDetails($id);
$payments = $paymentModel->getByProject($id);
$invoices = $invoiceModel->getByProject($id);
$total_paid = $paymentModel->getTotalPaidByProject($id);
$due = ($project['total_amount'] ?? 0) - $total_paid;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Project Details: <?php echo htmlspecialchars((string)($project['project_name'] ?? 'N/A')); ?></h2>
    <div>
        <a href="projects.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Projects
        </a>
        <a href="project-create.php?project_id=<?php echo $id; ?>" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Edit Project
        </a>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?php echo htmlspecialchars((string)$success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo htmlspecialchars((string)$error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <!-- Project Info -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Project Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Project Code:</strong> <?php echo htmlspecialchars((string)($project['project_code'] ?? 'N/A')); ?></p>
                        <p><strong>Project Name:</strong> <?php echo htmlspecialchars((string)($project['project_name'] ?? 'N/A')); ?></p>
                        <p><strong>Event Location:</strong> <?php echo htmlspecialchars((string)($project['event_location'] ?? 'N/A')); ?></p>
                        <p><strong>Status:</strong> <span class="badge bg-<?php echo $status_class; ?>"><?php echo ucfirst($project['status'] ?? 'draft'); ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Created By:</strong> <?php echo htmlspecialchars((string)($project['created_by_name'] ?? 'Admin')); ?></p>
                        <p><strong>Created Date:</strong> <?php echo isset($project['created_at']) ? date('M d, Y', strtotime($project['created_at'])) : 'N/A'; ?></p>
                        <p><strong>Last Updated:</strong> <?php echo isset($project['updated_at']) ? date('M d, Y', strtotime($project['updated_at'])) : 'N/A'; ?></p>
                        <p><strong>Invoice Sent:</strong> 
                            <?php if ($project['invoice_sent'] ?? false): ?>
                                <span class="badge bg-success">Yes (<?php echo isset($project['invoice_sent_date']) ? date('M d, Y', strtotime($project['invoice_sent_date'])) : 'N/A'; ?>)</span>
                            <?php else: ?>
                                <span class="badge bg-warning">No</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bride & Groom Details -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Bride & Groom Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Bride</h6>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars((string)($project['bride_name'] ?? 'N/A')); ?></p>
                        <?php if (!empty($project['bride_email'])): ?>
                            <p><strong>Email:</strong> <a href="mailto:<?php echo $project['bride_email']; ?>"><?php echo $project['bride_email']; ?></a></p>
                        <?php endif; ?>
                        <?php if (!empty($project['bride_phone'])): ?>
                            <p><strong>Phone:</strong> <?php echo $project['bride_phone']; ?></p>
                        <?php endif; ?>
                        <?php if (!empty($project['bride_facebook'])): ?>
                            <p><strong>Facebook:</strong> <a href="<?php echo $project['bride_facebook']; ?>" target="_blank">View Profile</a></p>
                        <?php endif; ?>
                        <?php if (!empty($project['bride_instagram'])): ?>
                            <p><strong>Instagram:</strong> <a href="<?php echo $project['bride_instagram']; ?>" target="_blank">View Profile</a></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Groom</h6>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars((string)($project['groom_name'] ?? 'N/A')); ?></p>
                        <?php if (!empty($project['groom_email'])): ?>
                            <p><strong>Email:</strong> <a href="mailto:<?php echo $project['groom_email']; ?>"><?php echo $project['groom_email']; ?></a></p>
                        <?php endif; ?>
                        <?php if (!empty($project['groom_phone'])): ?>
                            <p><strong>Phone:</strong> <?php echo $project['groom_phone']; ?></p>
                        <?php endif; ?>
                        <?php if (!empty($project['groom_facebook'])): ?>
                            <p><strong>Facebook:</strong> <a href="<?php echo $project['groom_facebook']; ?>" target="_blank">View Profile</a></p>
                        <?php endif; ?>
                        <?php if (!empty($project['groom_instagram'])): ?>
                            <p><strong>Instagram:</strong> <a href="<?php echo $project['groom_instagram']; ?>" target="_blank">View Profile</a></p>
                        <?php endif; ?>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <p><strong>Primary Email:</strong> <a href="mailto:<?php echo $project['email'] ?? ''; ?>"><?php echo $project['email'] ?? 'N/A'; ?></a></p>
                        <p><strong>Primary Phone:</strong> <?php echo $project['phone'] ?? 'N/A'; ?></p>
                        <?php if (!empty($project['alternate_phone'])): ?>
                            <p><strong>Alternate Phone:</strong> <?php echo $project['alternate_phone']; ?></p>
                        <?php endif; ?>
                        <?php if (!empty($project['address'])): ?>
                            <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars((string)$project['address'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Family Members -->
        <?php if (!empty($family) && is_array($family)): ?>
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Family Members</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php 
                    $bride_family = array_filter($family, function($m) { return ($m['side'] ?? 'bride') == 'bride'; });
                    $groom_family = array_filter($family, function($m) { return ($m['side'] ?? '') == 'groom'; });
                    ?>
                    <div class="col-md-6">
                        <h6 class="text-primary">Bride's Family</h6>
                        <?php if (!empty($bride_family)): ?>
                            <ul class="list-unstyled">
                                <?php foreach ($bride_family as $member): ?>
                                    <li>• <?php echo htmlspecialchars((string)($member['name'] ?? '')); ?> (<?php echo ucfirst($member['relationship'] ?? ''); ?>) - <?php echo $member['phone'] ?? 'No phone'; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No family members added</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Groom's Family</h6>
                        <?php if (!empty($groom_family)): ?>
                            <ul class="list-unstyled">
                                <?php foreach ($groom_family as $member): ?>
                                    <li>• <?php echo htmlspecialchars((string)($member['name'] ?? '')); ?> (<?php echo ucfirst($member['relationship'] ?? ''); ?>) - <?php echo $member['phone'] ?? 'No phone'; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No family members added</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Event Details -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Event Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Package:</strong> <?php echo htmlspecialchars((string)($project['package_name'] ?? 'N/A')); ?></p>
                        <p><strong>Package Price:</strong> ৳ <?php echo number_format($project['package_price'] ?? 0, 2); ?></p>
                        <p><strong>Event Date:</strong> <?php echo isset($project['event_date']) ? date('l, F j, Y', strtotime($project['event_date'])) : 'N/A'; ?></p>
                        <?php if (!empty($project['event_time'])): ?>
                            <p><strong>Event Time:</strong> <?php echo date('g:i A', strtotime($project['event_time'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Venue Name:</strong> <?php echo htmlspecialchars((string)($project['venue_name'] ?? 'TBD')); ?></p>
                        <?php if (!empty($project['venue_address'])): ?>
                            <p><strong>Venue Address:</strong> <?php echo nl2br(htmlspecialchars((string)$project['venue_address'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($project['special_notes'])): ?>
                    <div class="mt-3">
                        <p><strong>Special Notes:</strong></p>
                        <div class="border p-3 bg-light">
                            <?php echo nl2br(htmlspecialchars((string)$project['special_notes'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Add-ons -->
        <?php if (!empty($addons) && is_array($addons)): ?>
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Add-on Services</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Service</th>
                                <th>Description</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($addons as $addon): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((string)($addon['service_name'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars(substr((string)($addon['description'] ?? ''), 0, 50)); ?></td>
                                    <td class="text-center"><?php echo $addon['quantity'] ?? 1; ?></td>
                                    <td class="text-end">৳ <?php echo number_format($addon['unit_price'] ?? 0, 2); ?></td>
                                    <td class="text-end">৳ <?php echo number_format($addon['total_price'] ?? 0, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="4" class="text-end">Add-ons Total:</td>
                                <td class="text-end">৳ <?php echo number_format($addons_total, 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Team Members -->
        <?php if (!empty($team) && is_array($team)): ?>
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Team Members</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Role</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Assigned At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($team as $member): ?>
                                <tr>
                                    <td><span class="badge bg-info"><?php echo ucfirst($member['role'] ?? ''); ?></span></td>
                                    <td><?php echo htmlspecialchars((string)($member['full_name'] ?? $member['username'] ?? '')); ?></td>
                                    <td><?php echo $member['email'] ?? ''; ?></td>
                                    <td><?php echo isset($member['assigned_at']) ? date('M d, Y', strtotime($member['assigned_at'])) : 'N/A'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <!-- Financial Summary -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Financial Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td>Package Price:</td>
                        <td class="text-end">৳ <?php echo number_format($project['package_price'] ?? 0, 2); ?></td>
                    </tr>
                    <?php if ($addons_total > 0): ?>
                    <tr>
                        <td>Add-ons Total:</td>
                        <td class="text-end">৳ <?php echo number_format($addons_total, 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="fw-bold">
                        <td>Total Amount:</td>
                        <td class="text-end">৳ <?php echo number_format($project['total_amount'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td class="text-success">Total Paid:</td>
                        <td class="text-end text-success">৳ <?php echo number_format($total_paid, 2); ?></td>
                    </tr>
                    <tr class="fw-bold">
                        <td class="text-danger">Due Amount:</td>
                        <td class="text-end <?php echo $due > 0 ? 'text-danger' : 'text-success'; ?>">
                            ৳ <?php echo number_format($due, 2); ?>
                        </td>
                    </tr>
                </table>
                
                <?php if ($due > 0): ?>
                    <a href="payment-add.php?project_id=<?php echo $id; ?>" class="btn btn-success btn-sm w-100">
                        <i class="bi bi-cash"></i> Add Payment
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Generate Invoice Card -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Generate Invoice</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="send_email" class="form-check-input" id="send_email" value="1" checked>
                            <label class="form-check-label" for="send_email">
                                Send invoice to customer email
                            </label>
                        </div>
                        <small class="text-muted">Invoice will be emailed to <?php echo $project['email'] ?? 'customer'; ?></small>
                    </div>
                    <button type="submit" name="generate_invoice" class="btn btn-dark w-100">
                        <i class="bi bi-file-pdf"></i> Generate Invoice
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Invoices -->
        <?php if (!empty($invoices) && is_array($invoices)): ?>
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Invoices</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($invoices as $invoice): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars((string)($invoice['invoice_number'] ?? 'N/A')); ?></strong><br>
                                    <small class="text-muted"><?php echo isset($invoice['invoice_date']) ? date('M d, Y', strtotime($invoice['invoice_date'])) : 'N/A'; ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?php echo ($invoice['status'] ?? 'unpaid') == 'paid' ? 'success' : (($invoice['status'] ?? '') == 'partial' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($invoice['status'] ?? 'unpaid'); ?>
                                    </span>
                                    <div class="mt-1">
                                        <?php if (!empty($invoice['pdf_path'])): ?>
                                            <a href="../<?php echo $invoice['pdf_path']; ?>" class="btn btn-sm btn-info" target="_blank">
                                                <i class="bi bi-file-pdf"></i> View
                                            </a>
                                        <?php endif; ?>
                                        <?php if (empty($invoice['email_sent']) && !empty($invoice['pdf_path'])): ?>
                                            <a href="?id=<?php echo $id; ?>&resend_email=1&invoice_id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-envelope"></i> Send
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Recent Payments -->
        <?php if (!empty($payments) && is_array($payments)): ?>
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Recent Payments</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($payments, 0, 5) as $payment): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong><?php echo htmlspecialchars((string)($payment['payment_number'] ?? 'N/A')); ?></strong><br>
                                    <small class="text-muted"><?php echo isset($payment['payment_date']) ? date('M d, Y', strtotime($payment['payment_date'])) : 'N/A'; ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-success">৳ <?php echo number_format($payment['amount'] ?? 0, 2); ?></span><br>
                                    <small class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'] ?? '')); ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>
ob_end_flush();