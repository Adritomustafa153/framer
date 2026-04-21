<?php
// admin/clients.php
ob_start();
require_once '../config/database.php';
require_once '../models/Client.php';
require_once '../models/Booking.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$clientModel = new Client($db);
$bookingModel = new Booking($db);

// Get all clients
$clients = $clientModel->getAll('id DESC');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Clients</h2>
    <a href="booking-create.php" class="btn btn-dark">
        <i class="bi bi-plus-circle"></i> New Booking
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Bride & Groom</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>Bookings</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($clients && $clients->rowCount() > 0): ?>
                        <?php while ($client = $clients->fetch()): 
                            // Count bookings for this client
                            $bookings = $bookingModel->getAllWithDetails();
                            $booking_count = 0;
                            foreach ($bookings as $b) {
                                if ($b['client_id'] == $client['id']) $booking_count++;
                            }
                        ?>
                            <tr>
                                <td><?php echo $client['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($client['bride_name']); ?></strong> &<br>
                                    <?php echo htmlspecialchars($client['groom_name']); ?>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo $client['email']; ?>"><?php echo $client['email']; ?></a>
                                </td>
                                <td><?php echo $client['phone']; ?></td>
                                <td><?php echo $client['city'] ?? '-'; ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo $booking_count; ?></span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($client['created_at'])); ?></td>
                                <td>
                                    <a href="booking-create.php?client_id=<?php echo $client['id']; ?>" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="New Booking">
                                        <i class="bi bi-plus-circle"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#clientModal<?php echo $client['id']; ?>">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            
                            <!-- Client Details Modal -->
                            <div class="modal fade" id="clientModal<?php echo $client['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-dark text-white">
                                            <h5 class="modal-title">Client Details</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Bride Information</h6>
                                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($client['bride_name']); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Groom Information</h6>
                                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($client['groom_name']); ?></p>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Email:</strong> <?php echo $client['email']; ?></p>
                                                    <p><strong>Phone:</strong> <?php echo $client['phone']; ?></p>
                                                    <?php if ($client['alternate_phone']): ?>
                                                        <p><strong>Alt Phone:</strong> <?php echo $client['alternate_phone']; ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php if ($client['address']): ?>
                                                        <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($client['address'])); ?></p>
                                                    <?php endif; ?>
                                                    <p><strong>City/State:</strong> <?php echo $client['city'] . ', ' . $client['state'] . ' ' . $client['zip_code']; ?></p>
                                                    <p><strong>Country:</strong> <?php echo $client['country']; ?></p>
                                                </div>
                                            </div>
                                            <hr>
                                            <p><strong>Client since:</strong> <?php echo date('F j, Y', strtotime($client['created_at'])); ?></p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <a href="booking-create.php?client_id=<?php echo $client['id']; ?>" class="btn btn-dark">
                                                <i class="bi bi-plus-circle"></i> New Booking
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No clients found.</td>
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