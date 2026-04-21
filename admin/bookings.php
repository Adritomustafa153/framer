<?php
// admin/bookings.php
ob_start();
require_once '../config/database.php';
require_once '../models/Booking.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$bookingModel = new Booking($db);

// Get all bookings with details
$bookings = $bookingModel->getAllWithDetails();

// Get dashboard stats
$stats = $bookingModel->getDashboardStats();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Bookings Management</h2>
    <a href="booking-create.php" class="btn btn-dark">
        <i class="bi bi-plus-circle"></i> Add New Booking
    </a>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total</h5>
                <h2 class="mb-0"><?php echo $stats['total_bookings'] ?? 0; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Pending</h5>
                <h2 class="mb-0"><?php echo $stats['pending'] ?? 0; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Confirmed</h5>
                <h2 class="mb-0"><?php echo $stats['confirmed'] ?? 0; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Completed</h5>
                <h2 class="mb-0"><?php echo $stats['completed'] ?? 0; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Cancelled</h5>
                <h2 class="mb-0"><?php echo $stats['cancelled'] ?? 0; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <h5 class="card-title">Upcoming</h5>
                <h2 class="mb-0"><?php echo $stats['upcoming'] ?? 0; ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Bookings Table -->
<div class="card">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">All Bookings</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Booking No</th>
                        <th>Client</th>
                        <th>Package</th>
                        <th>Event Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($bookings) > 0): ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo $booking['id']; ?></td>
                                <td><?php echo htmlspecialchars($booking['booking_number']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($booking['bride_name'] ?? '') . ' & ' . htmlspecialchars($booking['groom_name'] ?? ''); ?>
                                </td>
                                <td><?php echo htmlspecialchars($booking['package_name']); ?></td>
                                <td><?php echo date('d M Y', strtotime($booking['event_date'])); ?></td>
                                <td>৳ <?php echo number_format($booking['package_price'], 2); ?></td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'confirmed' => 'info',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    $color = $statusColors[$booking['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($booking['status']); ?></span>
                                </td>
                                <td>
                                    <a href="booking-view.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="booking-edit.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No bookings found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
ob_end_flush();