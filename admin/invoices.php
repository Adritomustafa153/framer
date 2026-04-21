<?php
// admin/invoices.php
ob_start();
require_once '../config/database.php';
require_once '../models/Invoice.php';
require_once '../models/Booking.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$invoiceModel = new Invoice($db);

// Filter by status
$status_filter = $_GET['status'] ?? '';

if ($status_filter && $status_filter != 'all') {
    $invoices = $invoiceModel->getAllWithDetails();
    $invoices = array_filter($invoices, function($inv) use ($status_filter) {
        return $inv['status'] == $status_filter;
    });
} else {
    $invoices = $invoiceModel->getAllWithDetails();
}

// Calculate totals
$total_unpaid = 0;
$total_partial = 0;
$total_paid = 0;

foreach ($invoices as $inv) {
    if ($inv['status'] == 'unpaid') $total_unpaid += $inv['due_amount'];
    if ($inv['status'] == 'partial') $total_partial += $inv['due_amount'];
    if ($inv['status'] == 'paid') $total_paid += $inv['total_amount'];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Invoices</h2>
    <div>
        <a href="?status=all" class="btn btn-sm <?php echo !$status_filter || $status_filter == 'all' ? 'btn-dark' : 'btn-outline-dark'; ?>">All</a>
        <a href="?status=unpaid" class="btn btn-sm <?php echo $status_filter == 'unpaid' ? 'btn-warning' : 'btn-outline-warning'; ?>">Unpaid</a>
        <a href="?status=partial" class="btn btn-sm <?php echo $status_filter == 'partial' ? 'btn-info' : 'btn-outline-info'; ?>">Partial</a>
        <a href="?status=paid" class="btn btn-sm <?php echo $status_filter == 'paid' ? 'btn-success' : 'btn-outline-success'; ?>">Paid</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title">Total Unpaid</h5>
                <h2>৳<?php echo number_format($total_unpaid, 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Partial Payments Due</h5>
                <h2>৳<?php echo number_format($total_partial, 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Total Paid</h5>
                <h2>৳<?php echo number_format($total_paid, 2); ?></h2>
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
                        <th>Invoice #</th>
                        <th>Booking #</th>
                        <th>Client</th>
                        <th>Invoice Date</th>
                        <th>Due Date</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($invoices)): ?>
                        <?php foreach ($invoices as $invoice): 
                            $status_class = [
                                'unpaid' => 'danger',
                                'partial' => 'warning',
                                'paid' => 'success',
                                'cancelled' => 'secondary'
                            ][$invoice['status']] ?? 'secondary';
                        ?>
                            <tr>
                                <td><strong><?php echo $invoice['invoice_number']; ?></strong></td>
                                <td>
                                    <a href="booking-view.php?id=<?php echo $invoice['booking_id']; ?>">
                                        <?php echo $invoice['booking_number']; ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($invoice['bride_name'] . ' & ' . $invoice['groom_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($invoice['invoice_date'])); ?></td>
                                <td><?php echo $invoice['due_date'] ? date('M d, Y', strtotime($invoice['due_date'])) : '-'; ?></td>
                                <td class="fw-bold">৳<?php echo number_format($invoice['total_amount'], 2); ?></td>
                                <td class="text-success">৳<?php echo number_format($invoice['paid_amount'], 2); ?></td>
                                <td class="text-danger">৳<?php echo number_format($invoice['due_amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $status_class; ?>">
                                        <?php echo ucfirst($invoice['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($invoice['pdf_path']): ?>
                                        <a href="../<?php echo $invoice['pdf_path']; ?>" class="btn btn-sm btn-info" target="_blank">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="booking-view.php?id=<?php echo $invoice['booking_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">No invoices found.</td>
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