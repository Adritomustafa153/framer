<?php
// admin/booking-view.php
ob_start();
require_once '../config/database.php';
require_once '../models/Booking.php';
require_once '../models/Client.php';
require_once '../models/FamilyMember.php';
require_once '../models/Payment.php';
require_once '../models/Invoice.php';
require_once '../models/Package.php';
require_once '../includes/invoice_generator.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

$bookingModel = new Booking($db);
$clientModel = new Client($db);
$familyModel = new FamilyMember($db);
$paymentModel = new Payment($db);
$invoiceModel = new Invoice($db);
$packageModel = new Package($db);

$booking = $bookingModel->getWithDetails($id);
if (!$booking) {
    header("Location: bookings.php");
    exit();
}

$client = $clientModel->getById($booking['client_id']);
$family = $familyModel->getByClient($booking['client_id']);
$payments = $paymentModel->getByBooking($id);
$total_paid = $paymentModel->getTotalPaidByBooking($id);
$invoice = $invoiceModel->getByBooking($id);
$package = $booking['package_id'] ? $packageModel->getById($booking['package_id']) : null;

$due = $booking['package_price'] - $total_paid;
$status_class = [
    'pending' => 'warning',
    'confirmed' => 'success',
    'completed' => 'info',
    'cancelled' => 'danger'
][$booking['status']] ?? 'secondary';

// Handle regenerate invoice
if (isset($_GET['regenerate_invoice'])) {
    $invoiceGen = new InvoiceGenerator($db);
    $result = $invoiceGen->generateInvoice($id, $invoice['id']);
    if ($result['success']) {
        header("Location: booking-view.php?id=$id&msg=invoice_regenerated");
        exit();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Booking Details: <?php echo $booking['booking_number']; ?></h2>
    <div>
        <a href="bookings.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="booking-edit.php?id=<?php echo $id; ?>" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <?php if ($invoice && $invoice['pdf_path']): ?>
            <a href="../<?php echo $invoice['pdf_path']; ?>" class="btn btn-info" target="_blank">
                <i class="bi bi-file-pdf"></i> View Invoice
            </a>
        <?php endif; ?>
        <a href="?id=<?php echo $id; ?>&regenerate_invoice=1" class="btn btn-warning">
            <i class="bi bi-arrow-repeat"></i> Regenerate Invoice
        </a>
        <a href="booking-addons.php?booking_id=<?php echo $id; ?>" class="btn btn-info">
    <i class="bi bi-plus-circle"></i> Manage Add-ons
</a>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Invoice regenerated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <!-- Client Information -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Client Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Bride:</strong> <?php echo htmlspecialchars($client['bride_name']); ?></p>
                        <p><strong>Groom:</strong> <?php echo htmlspecialchars($client['groom_name']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Email:</strong> <a href="mailto:<?php echo $client['email']; ?>"><?php echo $client['email']; ?></a></p>
                        <p><strong>Phone:</strong> <?php echo $client['phone']; ?></p>
                        <?php if (!empty($client['alternate_phone'])): ?>
                            <p><strong>Alt Phone:</strong> <?php echo $client['alternate_phone']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($client['address'])): ?>
                    <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($client['address'])); ?></p>
                <?php endif; ?>
                <?php if (!empty($client['city'])): ?>
                    <p><strong>City/State:</strong> <?php echo $client['city'] . ', ' . $client['state'] . ' ' . $client['zip_code']; ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Family Members -->
        <?php if (!empty($family)): ?>
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Family Members</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Relationship</th>
                                <th>Phone</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($family as $member): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['name']); ?></td>
                                    <td><?php echo ucfirst($member['relationship']); ?></td>
                                    <td><?php echo $member['phone']; ?></td>
                                    <td><?php echo $member['notes']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
                        <p><strong>Package:</strong> <?php echo htmlspecialchars($booking['package_name']); ?></p>
                        <p><strong>Event Type:</strong> <?php echo $booking['event_type'] ?? 'Wedding'; ?></p>
                        <p><strong>Event Date:</strong> <?php echo date('F j, Y', strtotime($booking['event_date'])); ?></p>
                        <?php if ($booking['event_time']): ?>
                            <p><strong>Event Time:</strong> <?php echo date('g:i A', strtotime($booking['event_time'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Venue:</strong> <?php echo $booking['venue_name'] ?? 'N/A'; ?></p>
                        <?php if (!empty($booking['venue_address'])): ?>
                            <p><strong>Venue Address:</strong> <?php echo nl2br(htmlspecialchars($booking['venue_address'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($booking['special_notes'])): ?>
                    <p><strong>Special Notes:</strong></p>
                    <div class="border p-3 bg-light">
                        <?php echo nl2br(htmlspecialchars($booking['special_notes'])); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Booking Status -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Booking Status</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <span class="badge bg-<?php echo $status_class; ?> fs-6 p-3">
                        <?php echo strtoupper($booking['status']); ?>
                    </span>
                </div>
                <p><strong>Booking #:</strong> <?php echo $booking['booking_number']; ?></p>
                <p><strong>Created:</strong> <?php echo date('M d, Y', strtotime($booking['created_at'])); ?></p>
                <p><strong>Last Updated:</strong> <?php echo date('M d, Y', strtotime($booking['updated_at'])); ?></p>
                <?php if ($booking['created_by_name']): ?>
                    <p><strong>Created By:</strong> <?php echo $booking['created_by_name']; ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Payment Summary -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Payment Summary</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <td>Package Price:</td>
                        <td class="text-end"><strong><?php echo number_format($booking['package_price'], 2); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Total Paid:</td>
                        <td class="text-end text-success"><strong><?php echo number_format($total_paid, 2); ?></strong></td>
                    </tr>
                    <tr class="fw-bold">
                        <td>Due Amount:</td>
                        <td class="text-end text-danger"><strong><?php echo number_format($due, 2); ?></strong></td>
                    </tr>
                </table>
                
                <?php if ($due > 0): ?>
                    <a href="payment-add.php?booking_id=<?php echo $id; ?>" class="btn btn-success w-100">
                        <i class="bi bi-cash"></i> Add Payment
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Payments -->
        <?php if (!empty($payments)): ?>
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Recent Payments</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($payments as $payment): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo $payment['payment_number']; ?></strong><br>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-success"><?php echo number_format($payment['amount'], 2); ?></span><br>
                                    <small class="text-muted"><?php echo ucfirst($payment['payment_method']); ?></small>
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
?>