<?php
// admin/booking-create.php
ob_start();
require_once '../config/database.php';
require_once '../models/Client.php';
require_once '../models/FamilyMember.php';
require_once '../models/Booking.php';
require_once '../models/Package.php';
require_once '../models/Invoice.php';
require_once '../models/Payment.php';
require_once '../includes/mail.php';
require_once '../includes/invoice_generator.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$clientModel = new Client($db);
$familyModel = new FamilyMember($db);
$bookingModel = new Booking($db);
$packageModel = new Package($db);
$invoiceModel = new Invoice($db);
$paymentModel = new Payment($db);

// Get all packages for dropdown
$packages = $packageModel->getActive();

$error = '';
$success = '';

// Pre-fill client data if editing existing client
$client_id = isset($_GET['client_id']) ? $_GET['client_id'] : null;
$existingClient = null;
if ($client_id) {
    $existingClient = $clientModel->getById($client_id);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();
        
        // 1. Create or get client
        $client_id = null;
        if (!empty($_POST['client_id'])) {
            $client_id = $_POST['client_id'];
        } else {
            $client_data = [
                'bride_name' => $_POST['bride_name'],
                'groom_name' => $_POST['groom_name'],
                'email' => $_POST['email'] ?? $_POST['bride_email'] ?? '',
                'bride_email' => $_POST['bride_email'] ?? null,
                'bride_phone' => $_POST['bride_phone'] ?? null,
                'bride_facebook' => $_POST['bride_facebook'] ?? null,
                'bride_instagram' => $_POST['bride_instagram'] ?? null,
                'groom_email' => $_POST['groom_email'] ?? null,
                'groom_phone' => $_POST['groom_phone'] ?? null,
                'groom_facebook' => $_POST['groom_facebook'] ?? null,
                'groom_instagram' => $_POST['groom_instagram'] ?? null,
                'phone' => $_POST['phone'] ?? '',
                'alternate_phone' => $_POST['alternate_phone'] ?? null,
                'address' => $_POST['address'] ?? null,
                'city' => $_POST['city'] ?? null,
                'state' => $_POST['state'] ?? null,
                'zip_code' => $_POST['zip_code'] ?? null,
                'country' => $_POST['country'] ?? 'Bangladesh'
            ];
            $client_id = $clientModel->create($client_data);
            
            // 2. Add family members
            if (!empty($_POST['family_members'])) {
                foreach ($_POST['family_members'] as $member) {
                    if (!empty($member['name']) && !empty($member['relationship'])) {
                        $familyModel->create([
                            'client_id' => $client_id,
                            'name' => $member['name'],
                            'relationship' => $member['relationship'],
                            'side' => $member['side'] ?? 'bride',
                            'phone' => $member['phone'] ?? null,
                            'notes' => $member['notes'] ?? null
                        ]);
                    }
                }
            }
        }
        
        // 3. Get package details
        $package = null;
        $package_price = $_POST['package_price'];
        $package_name = $_POST['package_name'];
        $package_id = null;
        
        if (!empty($_POST['package_id'])) {
            $package = $packageModel->getById($_POST['package_id']);
            if ($package) {
                $package_id = $package['id'];
                $package_name = $package['package_name'];
                $package_price = $package['price'];
            }
        }
        
        // 4. Create booking
        $booking_data = [
            'client_id' => $client_id,
            'package_id' => $package_id,
            'package_name' => $package_name,
            'package_price' => $package_price,
            'event_type' => $_POST['event_type'] ?? 'Wedding',
            'event_date' => $_POST['event_date'],
            'event_time' => $_POST['event_time'] ?? null,
            'venue_name' => $_POST['venue_name'] ?? null,
            'venue_address' => $_POST['venue_address'] ?? null,
            'special_notes' => $_POST['special_notes'] ?? null,
            'status' => 'pending',
            'created_by' => getCurrentUserId()
        ];
        
        $booking_result = $bookingModel->create($booking_data);
        $booking_id = $booking_result['id'];
        $booking_number = $booking_result['booking_number'];
        
        // 5. Create invoice
        $invoice_data = [
            'booking_id' => $booking_id,
            'total_amount' => $package_price,
            'paid_amount' => 0,
            'due_amount' => $package_price,
            'invoice_date' => date('Y-m-d'),
            'due_date' => null,
            'status' => 'unpaid'
        ];
        
        $invoice_result = $invoiceModel->create($invoice_data);
        $invoice_id = $invoice_result['id'];
        $invoice_number = $invoice_result['invoice_number'];
        
        // 6. Handle initial payment if any
        $initial_payment = floatval($_POST['initial_payment'] ?? 0);
        if ($initial_payment > 0) {
            $payment_data = [
                'booking_id' => $booking_id,
                'amount' => $initial_payment,
                'payment_method' => $_POST['payment_method'] ?? 'cash',
                'transaction_id' => $_POST['transaction_id'] ?? null,
                'payment_date' => date('Y-m-d H:i:s'),
                'notes' => $_POST['payment_notes'] ?? 'Initial payment',
                'received_by' => getCurrentUserId()
            ];
            
            $payment_result = $paymentModel->create($payment_data);
        }
        
        $db->commit();
        
        // 7. Generate and send invoice
        $invoiceGen = new InvoiceGenerator($db);
        $pdf_result = $invoiceGen->generateInvoice($booking_id, $invoice_id);
        
        if ($pdf_result['success']) {
            // Get client and booking details for email
            $client = $clientModel->getById($client_id);
            $booking = $bookingModel->getWithDetails($booking_id);
            
            $invoice_data = [
                'invoice_id' => $invoice_id,
                'invoice_number' => $invoice_number,
                'booking_id' => $booking_id,
                'booking_number' => $booking_number,
                'client_name' => $client['bride_name'] . ' & ' . $client['groom_name'],
                'package_name' => $package_name,
                'total_amount' => $package_price,
                'paid_amount' => $initial_payment,
                'due_amount' => $package_price - $initial_payment,
                'currency' => 'BDT',
                'event_date' => $_POST['event_date'],
                'venue_name' => $_POST['venue_name'] ?? 'TBD'
            ];
            
            // Send to primary email (use bride's email if available, otherwise use primary email)
            $recipient_email = !empty($client['bride_email']) ? $client['bride_email'] : $client['email'];
            $recipient_name = $client['bride_name'] . ' & ' . $client['groom_name'];
            
            $mailer = new Mailer($db);
            
            // Add CC to groom's email if different and exists
            if (!empty($client['groom_email']) && $client['groom_email'] != $recipient_email) {
                $mailer->addCC($client['groom_email'], $client['groom_name']);
            }
            
            $mail_result = $mailer->sendInvoice(
                $recipient_email,
                $recipient_name,
                $invoice_data,
                '../' . $pdf_result['pdf_path']
            );
            
            if ($mail_result['success']) {
                $success = "Booking created successfully! Invoice sent to client.";
            } else {
                $success = "Booking created successfully! But email sending failed: " . $mail_result['message'];
            }
        } else {
            $success = "Booking created successfully! But invoice generation failed.";
        }
        
        header("Location: bookings.php?msg=created");
        exit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Create New Booking</h2>
    <a href="bookings.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Bookings
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
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Client Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" id="bookingForm">
                    <div class="mb-3">
                        <label class="form-label">Search Existing Client</label>
                        <input type="text" class="form-control" id="clientSearch" placeholder="Search by name, email or phone...">
                        <div id="clientSearchResults" class="mt-2"></div>
                    </div>
                    
                    <hr>
                    
                    <input type="hidden" name="client_id" id="client_id" value="<?php echo $existingClient ? $existingClient['id'] : ''; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bride Name *</label>
                            <input type="text" name="bride_name" class="form-control" required 
                                   value="<?php echo $existingClient ? htmlspecialchars($existingClient['bride_name']) : ''; ?>" id="bride_name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Groom Name *</label>
                            <input type="text" name="groom_name" class="form-control" required 
                                   value="<?php echo $existingClient ? htmlspecialchars($existingClient['groom_name']) : ''; ?>" id="groom_name">
                        </div>
                    </div>
                    
                    <!-- Bride's Social Info -->
                    <div class="card mb-3 bg-light">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Bride's Contact Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Bride's Email</label>
                                    <input type="email" name="bride_email" class="form-control" 
                                           value="<?php echo $existingClient ? htmlspecialchars($existingClient['bride_email']) : ''; ?>" id="bride_email">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Bride's Phone</label>
                                    <input type="text" name="bride_phone" class="form-control" 
                                           value="<?php echo $existingClient ? htmlspecialchars($existingClient['bride_phone']) : ''; ?>" id="bride_phone">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Bride's Facebook</label>
                                    <input type="url" name="bride_facebook" class="form-control" 
                                           value="<?php echo $existingClient ? htmlspecialchars($existingClient['bride_facebook']) : ''; ?>" 
                                           id="bride_facebook" placeholder="https://facebook.com/...">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Bride's Instagram</label>
                                    <input type="url" name="bride_instagram" class="form-control" 
                                           value="<?php echo $existingClient ? htmlspecialchars($existingClient['bride_instagram']) : ''; ?>" 
                                           id="bride_instagram" placeholder="https://instagram.com/...">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Groom's Social Info -->
                    <div class="card mb-3 bg-light">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Groom's Contact Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Groom's Email</label>
                                    <input type="email" name="groom_email" class="form-control" 
                                           value="<?php echo $existingClient ? htmlspecialchars($existingClient['groom_email']) : ''; ?>" id="groom_email">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Groom's Phone</label>
                                    <input type="text" name="groom_phone" class="form-control" 
                                           value="<?php echo $existingClient ? htmlspecialchars($existingClient['groom_phone']) : ''; ?>" id="groom_phone">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Groom's Facebook</label>
                                    <input type="url" name="groom_facebook" class="form-control" 
                                           value="<?php echo $existingClient ? htmlspecialchars($existingClient['groom_facebook']) : ''; ?>" 
                                           id="groom_facebook" placeholder="https://facebook.com/...">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Groom's Instagram</label>
                                    <input type="url" name="groom_instagram" class="form-control" 
                                           value="<?php echo $existingClient ? htmlspecialchars($existingClient['groom_instagram']) : ''; ?>" 
                                           id="groom_instagram" placeholder="https://instagram.com/...">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Primary Email *</label>
                            <input type="email" name="email" class="form-control" required 
                                   value="<?php echo $existingClient ? htmlspecialchars($existingClient['email']) : ''; ?>" id="email">
                            <small class="text-muted">Primary email for communication</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Primary Phone *</label>
                            <input type="text" name="phone" class="form-control" required 
                                   value="<?php echo $existingClient ? htmlspecialchars($existingClient['phone']) : ''; ?>" id="phone">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alternate Phone</label>
                        <input type="text" name="alternate_phone" class="form-control" 
                               value="<?php echo $existingClient ? htmlspecialchars($existingClient['alternate_phone']) : ''; ?>" id="alternate_phone">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2" id="address"><?php echo $existingClient ? htmlspecialchars($existingClient['address']) : ''; ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" 
                                   value="<?php echo $existingClient ? htmlspecialchars($existingClient['city']) : ''; ?>" id="city">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" 
                                   value="<?php echo $existingClient ? htmlspecialchars($existingClient['state']) : ''; ?>" id="state">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">ZIP Code</label>
                            <input type="text" name="zip_code" class="form-control" 
                                   value="<?php echo $existingClient ? htmlspecialchars($existingClient['zip_code']) : ''; ?>" id="zip_code">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Family Members</h5>
                    <button type="button" class="btn btn-sm btn-light" onclick="addFamilyMember()">
                        <i class="bi bi-plus-circle"></i> Add Member
                    </button>
                </div>
                <div class="card-body">
                    <div id="family-members-container">
                        <!-- Family members will be added here -->
                    </div>
                    <small class="text-muted">Add family members from both bride's and groom's side</small>
                </div>
            </div>
            
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
                                            data-description="<?php echo htmlspecialchars($p['description'] ?? ''); ?>"
                                            data-features='<?php echo htmlspecialchars(json_encode($p['features'] ?? [])); ?>'>
                                        <?php echo htmlspecialchars($p['package_name']); ?> - 
                                        <?php echo $p['currency']; ?> <?php echo number_format($p['price'], 2); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Package Name *</label>
                            <input type="text" name="package_name" class="form-control" required id="package_name" readonly>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Package Price *</label>
                            <input type="number" step="0.01" name="package_price" class="form-control" required id="package_price">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Currency</label>
                            <input type="text" name="currency" class="form-control" id="currency" value="BDT" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Event Type</label>
                            <select name="event_type" class="form-control">
                                <option value="Wedding">Wedding</option>
                                <option value="Engagement">Engagement</option>
                                <option value="Pre-wedding">Pre-wedding</option>
                                <option value="Anniversary">Anniversary</option>
                                <option value="Birthday">Birthday</option>
                                <option value="Corporate">Corporate</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Event Date *</label>
                            <input type="date" name="event_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Event Time</label>
                            <input type="time" name="event_time" class="form-control">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Venue Name</label>
                        <input type="text" name="venue_name" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Venue Address</label>
                        <textarea name="venue_address" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Special Notes</label>
                        <textarea name="special_notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Initial Payment</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Initial Payment Amount</label>
                            <input type="number" step="0.01" name="initial_payment" class="form-control" id="initial_payment" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-control">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="card">Card</option>
                                <option value="mobile_banking">Mobile Banking (bKash/Nagad)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Transaction ID</label>
                            <input type="text" name="transaction_id" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Notes</label>
                        <input type="text" name="payment_notes" class="form-control">
                    </div>
                </div>
            </div>
            
            <div class="text-center mb-4">
                <button type="submit" class="btn btn-dark btn-lg px-5">
                    <i class="bi bi-check-circle"></i> Create Booking & Send Invoice
                </button>
            </div>
        </form>
    </div>
    
    <div class="col-md-4">
        <div class="card sticky-top" style="top: 100px;">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Booking Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td>Package:</td>
                        <td class="text-end" id="summary_package">-</td>
                    </tr>
                    <tr>
                        <td>Price:</td>
                        <td class="text-end" id="summary_price">0.00</td>
                    </tr>
                    <tr>
                        <td>Initial Payment:</td>
                        <td class="text-end" id="summary_payment">0.00</td>
                    </tr>
                    <tr class="fw-bold">
                        <td>Due Amount:</td>
                        <td class="text-end text-danger" id="summary_due">0.00</td>
                    </tr>
                </table>
                <hr>
                <p class="small text-muted mb-0">
                    <i class="bi bi-info-circle"></i> An invoice will be generated and sent to the client's email after booking.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
let familyMemberCount = 0;

function addFamilyMember() {
    const container = document.getElementById('family-members-container');
    const html = `
        <div class="family-member-item card mb-2 p-3" id="family-${familyMemberCount}">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <input type="text" name="family_members[${familyMemberCount}][name]" class="form-control" placeholder="Full Name">
                </div>
                <div class="col-md-2 mb-2">
                    <select name="family_members[${familyMemberCount}][side]" class="form-control">
                        <option value="bride">Bride's Side</option>
                        <option value="groom">Groom's Side</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="family_members[${familyMemberCount}][relationship]" class="form-control">
                        <option value="father">Father</option>
                        <option value="mother">Mother</option>
                        <option value="brother">Brother</option>
                        <option value="sister">Sister</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" name="family_members[${familyMemberCount}][phone]" class="form-control" placeholder="Phone Number">
                </div>
                <div class="col-md-2 mb-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeFamilyMember(${familyMemberCount})">
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
    const payment = parseFloat(document.getElementById('initial_payment').value) || 0;
    const due = price - payment;
    
    document.getElementById('summary_package').textContent = document.getElementById('package_name').value || '-';
    document.getElementById('summary_price').textContent = price.toFixed(2);
    document.getElementById('summary_payment').textContent = payment.toFixed(2);
    document.getElementById('summary_due').textContent = due.toFixed(2);
}

document.getElementById('package_price').addEventListener('input', updateSummary);
document.getElementById('initial_payment').addEventListener('input', updateSummary);

// Auto-fill from existing client
<?php if ($existingClient): ?>
document.getElementById('bride_name').value = '<?php echo addslashes($existingClient['bride_name']); ?>';
document.getElementById('groom_name').value = '<?php echo addslashes($existingClient['groom_name']); ?>';
document.getElementById('email').value = '<?php echo addslashes($existingClient['email']); ?>';
document.getElementById('bride_email').value = '<?php echo addslashes($existingClient['bride_email']); ?>';
document.getElementById('bride_phone').value = '<?php echo addslashes($existingClient['bride_phone']); ?>';
document.getElementById('bride_facebook').value = '<?php echo addslashes($existingClient['bride_facebook']); ?>';
document.getElementById('bride_instagram').value = '<?php echo addslashes($existingClient['bride_instagram']); ?>';
document.getElementById('groom_email').value = '<?php echo addslashes($existingClient['groom_email']); ?>';
document.getElementById('groom_phone').value = '<?php echo addslashes($existingClient['groom_phone']); ?>';
document.getElementById('groom_facebook').value = '<?php echo addslashes($existingClient['groom_facebook']); ?>';
document.getElementById('groom_instagram').value = '<?php echo addslashes($existingClient['groom_instagram']); ?>';
document.getElementById('phone').value = '<?php echo addslashes($existingClient['phone']); ?>';
document.getElementById('alternate_phone').value = '<?php echo addslashes($existingClient['alternate_phone']); ?>';
document.getElementById('address').value = '<?php echo addslashes($existingClient['address']); ?>';
document.getElementById('city').value = '<?php echo addslashes($existingClient['city']); ?>';
document.getElementById('state').value = '<?php echo addslashes($existingClient['state']); ?>';
document.getElementById('zip_code').value = '<?php echo addslashes($existingClient['zip_code']); ?>';
<?php endif; ?>

// Client search
let searchTimeout;
document.getElementById('clientSearch').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const keyword = this.value;
    
    if (keyword.length < 3) {
        document.getElementById('clientSearchResults').innerHTML = '';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        fetch('client-search.php?q=' + encodeURIComponent(keyword))
            .then(response => response.json())
            .then(data => {
                let html = '<div class="list-group">';
                data.forEach(client => {
                    html += `
                        <a href="#" class="list-group-item list-group-item-action" onclick="selectClient(${client.id}, '${client.bride_name}', '${client.groom_name}', '${client.email}', '${client.bride_email}', '${client.bride_phone}', '${client.bride_facebook}', '${client.bride_instagram}', '${client.groom_email}', '${client.groom_phone}', '${client.groom_facebook}', '${client.groom_instagram}', '${client.phone}', '${client.alternate_phone || ''}', '${client.address || ''}', '${client.city || ''}', '${client.state || ''}', '${client.zip_code || ''}')">
                            <strong>${client.bride_name} & ${client.groom_name}</strong><br>
                            <small>${client.email} | ${client.phone}</small>
                        </a>
                    `;
                });
                html += '</div>';
                document.getElementById('clientSearchResults').innerHTML = html;
            });
    }, 500);
});

function selectClient(id, bride_name, groom_name, email, bride_email, bride_phone, bride_facebook, bride_instagram, groom_email, groom_phone, groom_facebook, groom_instagram, phone, alternate_phone, address, city, state, zip) {
    document.getElementById('client_id').value = id;
    document.getElementById('bride_name').value = bride_name;
    document.getElementById('groom_name').value = groom_name;
    document.getElementById('email').value = email;
    document.getElementById('bride_email').value = bride_email;
    document.getElementById('bride_phone').value = bride_phone;
    document.getElementById('bride_facebook').value = bride_facebook;
    document.getElementById('bride_instagram').value = bride_instagram;
    document.getElementById('groom_email').value = groom_email;
    document.getElementById('groom_phone').value = groom_phone;
    document.getElementById('groom_facebook').value = groom_facebook;
    document.getElementById('groom_instagram').value = groom_instagram;
    document.getElementById('phone').value = phone;
    document.getElementById('alternate_phone').value = alternate_phone;
    document.getElementById('address').value = address;
    document.getElementById('city').value = city;
    document.getElementById('state').value = state;
    document.getElementById('zip_code').value = zip;
    
    document.getElementById('clientSearchResults').innerHTML = '';
    document.getElementById('clientSearch').value = bride_name + ' & ' + groom_name;
}
</script>

<?php require_once 'footer.php'; ?>
ob_end_flush();
?>