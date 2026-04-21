<?php
// admin/payment-add.php
ob_start();
require_once '../config/database.php';
require_once '../models/Project.php';
require_once '../models/ProjectPayment.php';
require_once '../models/ProjectInvoice.php';
require_once '../includes/invoice_generator.php';
require_once '../includes/mail.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();

$project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
$projectModel = new Project($db);
$paymentModel = new ProjectPayment($db);
$invoiceModel = new ProjectInvoice($db);

$project = $projectModel->getWithDetails($project_id);
if (!$project) {
    header("Location: projects.php");
    exit();
}

$total_paid = $paymentModel->getTotalPaidByProject($project_id);
$due = $project['total_amount'] - $total_paid;
$addons_total = 0; // Will be calculated from project_addons

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    
    if ($amount <= 0) {
        $error = "Amount must be greater than zero.";
    } elseif ($amount > $due) {
        $error = "Amount exceeds due amount.";
    } else {
        try {
            $db->beginTransaction();
            
            // Create payment
            $payment_data = [
                'project_id' => $project_id,
                'amount' => $amount,
                'payment_method' => $_POST['payment_method'],
                'transaction_id' => $_POST['transaction_id'] ?? null,
                'payment_date' => $_POST['payment_date'] . ' ' . date('H:i:s'),
                'notes' => $_POST['notes'] ?? null,
                'received_by' => getCurrentUserId()
            ];
            
            $payment_result = $paymentModel->create($payment_data);
            
            // Update total paid
            $new_total_paid = $total_paid + $amount;
            $new_due = $project['total_amount'] - $new_total_paid;
            
            // Update or create invoice
            $invoices = $invoiceModel->getByProject($project_id);
            if (!empty($invoices)) {
                // Update the latest invoice
                $latest_invoice = $invoices[0];
                $invoiceModel->update($latest_invoice['id'], [
                    'paid_amount' => $new_total_paid,
                    'due_amount' => $new_due,
                    'status' => $new_due <= 0 ? 'paid' : 'partial'
                ]);
                
                // Regenerate invoice PDF
                $invoiceGen = new InvoiceGenerator($db);
                $temp_booking = [
                    'id' => $project_id,
                    'booking_number' => $project['project_code'],
                    'client_id' => 0,
                    'package_id' => $project['package_id'],
                    'package_name' => $project['package_name'],
                    'package_price' => $project['package_price'],
                    'event_date' => $project['event_date'],
                    'venue_name' => $project['venue_name'] ?? 'TBD',
                    'bride_name' => $project['bride_name'],
                    'groom_name' => $project['groom_name'],
                    'email' => $project['email'],
                    'phone' => $project['phone']
                ];
                
                $pdf_result = $invoiceGen->generateInvoice($project_id, $latest_invoice['id']);
                
                // Send email notification
                $client_data = [
                    'client_name' => $project['bride_name'] . ' & ' . $project['groom_name'],
                    'email' => $project['email'],
                    'invoice_number' => $latest_invoice['invoice_number'],
                    'booking_number' => $project['project_code'],
                    'package_name' => $project['package_name'],
                    'total_amount' => $project['total_amount'],
                    'paid_amount' => $new_total_paid,
                    'due_amount' => $new_due,
                    'event_date' => $project['event_date'],
                    'venue_name' => $project['venue_name'] ?? 'TBD'
                ];
                
                $mailer = new Mailer($db);
                $mailer->sendInvoice(
                    $project['email'],
                    $client_data['client_name'],
                    $client_data,
                    '../' . $pdf_result['pdf_path']
                );
            }
            
            $db->commit();
            
            header("Location: project-view.php?id=$project_id&msg=payment_added");
            exit();
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add Payment for Project: <?php echo htmlspecialchars($project['project_name']); ?></h2>
    <a href="project-view.php?id=<?php echo $project_id; ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Project
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
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Payment Details</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Amount *</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" name="amount" class="form-control" required 
                                       max="<?php echo $due; ?>" value="<?php echo $due; ?>" id="amount">
                            </div>
                            <small class="text-muted">Maximum due: ৳<?php echo number_format($due, 2); ?></small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Method *</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="card">Card</option>
                                <option value="mobile_banking">Mobile Banking (bKash/Nagad)</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Transaction ID</label>
                            <input type="text" name="transaction_id" class="form-control" placeholder="For bank/mobile transfers">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Date *</label>
                            <input type="date" name="payment_date" class="form-control" required 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes about payment"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-dark">
                        <i class="bi bi-save"></i> Record Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Payment Summary</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <td>Total Amount:</td>
                        <td class="text-end"><strong>৳ <?php echo number_format($project['total_amount'], 2); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Already Paid:</td>
                        <td class="text-end text-success"><strong>৳ <?php echo number_format($total_paid, 2); ?></strong></td>
                    </tr>
                    <tr class="fw-bold">
                        <td>Due Amount:</td>
                        <td class="text-end text-danger"><strong>৳ <?php echo number_format($due, 2); ?></strong></td>
                    </tr>
                </table>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> After payment, an updated invoice will be sent to the client.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('amount').addEventListener('input', function() {
    const max = <?php echo $due; ?>;
    if (this.value > max) {
        this.value = max;
    }
});
</script>

<?php require_once 'footer.php'; ?>
ob_end_flush();
?>