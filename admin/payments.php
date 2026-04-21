<?php
// admin/payments.php
ob_start();
require_once '../config/database.php';
require_once '../models/Payment.php';
require_once '../models/Booking.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$paymentModel = new Payment($db);
$bookingModel = new Booking($db);

// Date filter
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$payments = $paymentModel->getPaymentsByDateRange($start_date, $end_date);

// Calculate totals
$total_amount = 0;
foreach ($payments as $payment) {
    $total_amount += $payment['amount'];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Payments</h2>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-dark">
                    <i class="bi bi-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Total Collected</h5>
                <h2>৳<?php echo number_format($total_amount, 2); ?></h2>
                <small><?php echo count($payments); ?> payments</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Payment #</th>
                        <th>Booking #</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Transaction ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><strong><?php echo $payment['payment_number']; ?></strong></td>
                                <td>
                                    <a href="booking-view.php?id=<?php echo $payment['booking_id']; ?>">
                                        <?php echo $payment['booking_number']; ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($payment['bride_name'] . ' & ' . $payment['groom_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></span>
                                </td>
                                <td class="fw-bold text-success">৳<?php echo number_format($payment['amount'], 2); ?></td>
                                <td><?php echo $payment['transaction_id'] ?? '-'; ?></td>
                                <td>
                                    <a href="booking-view.php?id=<?php echo $payment['booking_id']; ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No payments found for this period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
ob_end_flush();
?>