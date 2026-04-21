<?php
// admin/booking-addons.php
ob_start();
require_once '../config/database.php';
require_once '../models/Booking.php';
require_once '../models/BookingAddon.php';
require_once '../models/AddonService.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();

$booking_id = $_GET['booking_id'] ?? 0;
$bookingModel = new Booking($db);
$addonModel = new AddonService($db);
$bookingAddonModel = new BookingAddon($db);

$booking = $bookingModel->getWithDetails($booking_id);
if (!$booking) {
    header("Location: bookings.php");
    exit();
}

// Get all available addons
$availableAddons = $addonModel->getActive();

// Get selected addons for this booking
$selectedAddons = $bookingAddonModel->getByBooking($booking_id);

// Handle add addon
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_addon'])) {
    $addon_id = $_POST['addon_id'];
    $quantity = $_POST['quantity'] ?? 1;
    
    if ($addon_id) {
        $addon = $addonModel->getById($addon_id);
        if ($addon) {
            $bookingAddonModel->create([
                'booking_id' => $booking_id,
                'addon_id' => $addon_id,
                'service_name' => $addon['service_name'],
                'description' => $addon['description'],
                'quantity' => $quantity,
                'unit_price' => $addon['price']
            ]);
        }
    }
    header("Location: booking-addons.php?booking_id=$booking_id");
    exit();
}

// Handle delete addon
if (isset($_GET['delete_addon'])) {
    $bookingAddonModel->delete($_GET['delete_addon']);
    header("Location: booking-addons.php?booking_id=$booking_id");
    exit();
}

// Calculate totals
$package_total = $booking['package_price'];
$addons_total = $bookingAddonModel->getTotalByBooking($booking_id);
$grand_total = $package_total + $addons_total;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add-on Services for Booking #<?php echo $booking['booking_number']; ?></h2>
    <div>
        <a href="booking-view.php?id=<?php echo $booking_id; ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Booking
        </a>
        <a href="invoice-preview.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-success" target="_blank">
    <i class="bi bi-file-pdf"></i> Generate Invoice
</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Selected Add-on Services</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($selectedAddons)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Service</th>
                                    <th>Description</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($selectedAddons as $addon): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($addon['service_name']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($addon['description'] ?? '', 0, 50)); ?></td>
                                        <td><?php echo $addon['quantity']; ?></td>
                                        <td>৳ <?php echo number_format($addon['unit_price'], 2); ?></td>
                                        <td>৳ <?php echo number_format($addon['total_price'], 2); ?></td>
                                        <td>
                                            <a href="?booking_id=<?php echo $booking_id; ?>&delete_addon=<?php echo $addon['id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Remove this add-on?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-3">No add-on services selected yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4 sticky-top" style="top: 100px;">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Add New Add-on</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Select Service</label>
                        <select name="addon_id" class="form-control" required>
                            <option value="">-- Choose Add-on --</option>
                            <?php foreach ($availableAddons as $addon): ?>
                                <option value="<?php echo $addon['id']; ?>" 
                                        data-price="<?php echo $addon['price']; ?>"
                                        data-type="<?php echo $addon['price_type']; ?>">
                                    <?php echo htmlspecialchars($addon['service_name']); ?> 
                                    (৳ <?php echo number_format($addon['price'], 2); ?> 
                                    <?php echo $addon['price_type'] == 'per_hour' ? '/hour' : ($addon['price_type'] == 'per_unit' ? '/unit' : ''); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1" id="quantity">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Price Preview</label>
                        <div class="form-control bg-light" id="price_preview">৳ 0.00</div>
                    </div>
                    
                    <button type="submit" name="add_addon" class="btn btn-dark w-100">
                        <i class="bi bi-plus-circle"></i> Add to Booking
                    </button>
                </form>
            </div>
            
            <div class="card-footer">
                <h6>Summary</h6>
                <table class="table table-sm">
                    <tr>
                        <td>Package Price:</td>
                        <td class="text-end">৳ <?php echo number_format($package_total, 2); ?></td>
                    </tr>
                    <tr>
                        <td>Add-ons Total:</td>
                        <td class="text-end">৳ <?php echo number_format($addons_total, 2); ?></td>
                    </tr>
                    <tr class="fw-bold">
                        <td>Grand Total:</td>
                        <td class="text-end text-primary">৳ <?php echo number_format($grand_total, 2); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('select[name="addon_id"]').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    if (selected.value) {
        const price = parseFloat(selected.dataset.price);
        const type = selected.dataset.type;
        const qty = parseInt(document.getElementById('quantity').value) || 1;
        const total = price * qty;
        document.getElementById('price_preview').innerHTML = '৳ ' + total.toFixed(2) + 
            (type == 'per_hour' ? ' (per hour)' : (type == 'per_unit' ? ' (per unit)' : ''));
    } else {
        document.getElementById('price_preview').innerHTML = '৳ 0.00';
    }
});

document.getElementById('quantity').addEventListener('input', function() {
    const select = document.querySelector('select[name="addon_id"]');
    if (select.value) {
        const selected = select.options[select.selectedIndex];
        const price = parseFloat(selected.dataset.price);
        const type = selected.dataset.type;
        const qty = parseInt(this.value) || 1;
        const total = price * qty;
        document.getElementById('price_preview').innerHTML = '৳ ' + total.toFixed(2) +
            (type == 'per_hour' ? ' (per hour)' : (type == 'per_unit' ? ' (per unit)' : ''));
    }
});
</script>

<?php require_once 'footer.php'; ?>
ob_end_flush();
?>