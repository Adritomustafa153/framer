<?php
// admin/invoice-preview.php
require_once '../config/database.php';
require_once '../models/Booking.php';
require_once '../models/Invoice.php';
require_once '../models/Package.php';
require_once '../models/Client.php';
require_once '../models/BookingAddon.php';
require_once '../includes/invoice_generator.php';

// Start session and check login
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$database = new Database();
$db = $database->getConnection();

$booking_id = $_GET['booking_id'] ?? 0;

if (!$booking_id) {
    die("No booking ID provided");
}

$bookingModel = new Booking($db);
$invoiceModel = new Invoice($db);
$packageModel = new Package($db);
$clientModel = new Client($db);
$bookingAddonModel = new BookingAddon($db);

// Get booking details
$booking = $bookingModel->getWithDetails($booking_id);
if (!$booking) {
    die("Booking not found for ID: " . $booking_id);
}

// Get client details
$client = $clientModel->getById($booking['client_id']);
if (!$client) {
    die("Client not found for ID: " . $booking['client_id']);
}

// Get package details
$package = null;
if ($booking['package_id']) {
    $package = $packageModel->getById($booking['package_id']);
}

// Get addons
$addons = $bookingAddonModel->getByBooking($booking_id);
$addons_total = $bookingAddonModel->getTotalByBooking($booking_id);

// Debug - check if data is loaded
/*
echo "Booking: <pre>" . print_r($booking, true) . "</pre>";
echo "Client: <pre>" . print_r($client, true) . "</pre>";
echo "Package: <pre>" . print_r($package, true) . "</pre>";
echo "Addons: <pre>" . print_r($addons, true) . "</pre>";
exit();
*/

// Check if invoice already exists
$invoice = $invoiceModel->getByBooking($booking_id);

if (!$invoice) {
    // Create new invoice
    $invoice_data = [
        'booking_id' => $booking_id,
        'total_amount' => $booking['package_price'] + $addons_total,
        'paid_amount' => 0,
        'due_amount' => $booking['package_price'] + $addons_total,
        'invoice_date' => date('Y-m-d'),
        'due_date' => null,
        'status' => 'unpaid'
    ];
    
    $invoice_result = $invoiceModel->create($invoice_data);
    $invoice_id = $invoice_result['id'];
} else {
    $invoice_id = $invoice['id'];
}

// Generate invoice
$invoiceGen = new InvoiceGenerator($db);
$result = $invoiceGen->generateInvoice($booking_id, $invoice_id);

if ($result['success']) {
    // Redirect to the generated PDF
    header("Location: ../" . $result['pdf_path']);
    exit();
} else {
    die("Error generating invoice: " . $result['message']);
}
?>