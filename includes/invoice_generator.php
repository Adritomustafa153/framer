<?php
// includes/invoice_generator.php
require_once __DIR__ . '/tcpdf_custom.php';

class InvoiceGenerator {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function generateInvoice($booking_id, $invoice_id = null) {
        // Enable error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Create debug log file
        $debug_dir = __DIR__ . '/../logs';
        if (!file_exists($debug_dir)) {
            mkdir($debug_dir, 0777, true);
        }
        $debug_file = $debug_dir . '/invoice_debug.log';
        
        $debug_message = "\n[" . date('Y-m-d H:i:s') . "] ===== STARTING INVOICE GENERATION FOR BOOKING ID: $booking_id =====\n";
        file_put_contents($debug_file, $debug_message, FILE_APPEND);
        
        // Get booking details
        require_once __DIR__ . '/../models/Booking.php';
        require_once __DIR__ . '/../models/Invoice.php';
        require_once __DIR__ . '/../models/Payment.php';
        require_once __DIR__ . '/../models/Package.php';
        require_once __DIR__ . '/../models/Client.php';
        require_once __DIR__ . '/../models/FamilyMember.php';
        require_once __DIR__ . '/../models/BookingAddon.php';
        
        $bookingModel = new Booking($this->db);
        $invoiceModel = new Invoice($this->db);
        $paymentModel = new Payment($this->db);
        $packageModel = new Package($this->db);
        $clientModel = new Client($this->db);
        $familyModel = new FamilyMember($this->db);
        $bookingAddonModel = new BookingAddon($this->db);
        
        // Get booking with details
        file_put_contents($debug_file, "Fetching booking details...\n", FILE_APPEND);
        $booking = $bookingModel->getWithDetails($booking_id);
        
        if (!$booking) {
            file_put_contents($debug_file, "ERROR: Booking not found for ID: $booking_id\n", FILE_APPEND);
            return ['success' => false, 'message' => 'Booking not found'];
        }
        
        // Get client
        $client = $clientModel->getById($booking['client_id']);
        if (!$client) {
            file_put_contents($debug_file, "ERROR: Client not found for ID: " . $booking['client_id'] . "\n", FILE_APPEND);
            return ['success' => false, 'message' => 'Client not found'];
        }
        
        $family = $familyModel->getByClient($booking['client_id']);
        
        // Get addons for this booking
        $addons = $bookingAddonModel->getByBooking($booking_id);
        $addons_total = $bookingAddonModel->getTotalByBooking($booking_id);
        
        // Get package details
        $package_name = $booking['package_name'] ?? 'Standard Package';
        $package_price = floatval($booking['package_price'] ?? 0);
        $package_duration = $booking['duration'] ?? 'Full Day';
        $package_description = $booking['description'] ?? 'Full photography coverage';
        
        // Try to get from packages table
        $features = [];
        if (!empty($booking['package_id'])) {
            $package = $packageModel->getById($booking['package_id']);
            if ($package) {
                $package_name = $package['package_name'] ?? $package_name;
                $package_price = isset($package['price']) ? floatval($package['price']) : $package_price;
                $package_duration = $package['duration'] ?? $package_duration;
                
                if (!empty($package['features'])) {
                    $features = is_string($package['features']) ? json_decode($package['features'], true) : $package['features'];
                    if (!is_array($features)) {
                        $features = [];
                    }
                }
            }
        }
        
        // Get or create invoice
        if ($invoice_id) {
            $invoice = $invoiceModel->getById($invoice_id);
        } else {
            $invoice = $invoiceModel->getByBooking($booking_id);
        }
        
        if (!$invoice) {
            $invoice_data = [
                'booking_id' => $booking_id,
                'total_amount' => $package_price + $addons_total,
                'paid_amount' => 0,
                'due_amount' => $package_price + $addons_total,
                'invoice_date' => date('Y-m-d'),
                'due_date' => null,
                'status' => 'unpaid'
            ];
            
            $invoice_result = $invoiceModel->create($invoice_data);
            $invoice_id = $invoice_result['id'];
            $invoice = $invoiceModel->getById($invoice_id);
        }
        
        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice not found'];
        }
        
        // Get payments
        $payments = $paymentModel->getByBooking($booking_id);
        $total_paid = $paymentModel->getTotalPaidByBooking($booking_id);
        
        // Calculate totals
        $package_total = $package_price;
        $grand_total = $package_total + $addons_total;
        $due_amount = $grand_total - $total_paid;
        
        // Create PDF
        $pdf = new FramerPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Framer Photography');
        $pdf->SetAuthor('Framer Admin');
        $pdf->SetTitle('Invoice ' . $invoice['invoice_number']);
        $pdf->SetSubject('Booking Invoice');
        $pdf->SetKeywords('Invoice, Photography, Framer, Wedding');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $left_margin = 15;
        $right_margin = 15;
        $top_margin = 20;
        $pdf->SetMargins($left_margin, $top_margin, $right_margin);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(true, 25);
        
        // Add watermark logo
        $logo_file = __DIR__ . '/../logo.png';
        if (file_exists($logo_file)) {
            $pdf->SetAlpha(0.05);
            $pdf->Image($logo_file, 50, 150, 100, 100, 'PNG', '', '', false, 300, '', false, false, 0);
            $pdf->SetAlpha(1);
        }
        
        // ========== PAGE 1 ==========
        $pdf->AddPage();
        
        // COMPANY HEADER (no black background)
        $pdf->SetY(15);
        
        // Logo - Bigger size (50px)
        if (file_exists($logo_file)) {
            $pdf->Image($logo_file, 15, 10, 45, 45, 'PNG');
        }
        
        // Company Name
        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(65, 12);
        $pdf->Cell(0, 10, 'FRAMER', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->SetTextColor(135, 206, 235); // Skyblue
        $pdf->SetXY(65, 22);
        $pdf->Cell(0, 5, 'framing your happiness', 0, 1, 'L');
        
        // Contact Info below slogan
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY(65, 28);
        $pdf->Cell(0, 4, 'Rajonigondha Vally, 178/B Khilgaon Chowdhurypara, Dhaka 1219, Bangladesh', 0, 1, 'L');
        $pdf->SetXY(65, 32);
        $pdf->Cell(0, 4, '📞 +880 1829-093616  |  ✉️ framer.wedding@gmail.com  |  🌐 www.framer.photo', 0, 1, 'L');
        
        // ========== INVOICE DETAILS - TOP RIGHT CORNER ==========
        $pdf->SetY(12);
        $pdf->SetX(130);
        $pdf->SetFillColor(248, 249, 250);
        $pdf->SetDrawColor(135, 206, 235); // Skyblue border
        $pdf->SetLineWidth(0.5);
        $pdf->Rect(130, 10, 65, 55, 'DF');
        
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(135, 206, 235);
        $pdf->SetXY(132, 14);
        $pdf->Cell(60, 6, 'INVOICE', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetXY(132, 21);
        $pdf->Cell(20, 5, 'Number:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(40, 5, $invoice['invoice_number'], 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetXY(132, 27);
        $pdf->Cell(20, 5, 'Date:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(40, 5, date('d M Y', strtotime($invoice['invoice_date'])), 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetXY(132, 33);
        $pdf->Cell(20, 5, 'Booking:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(40, 5, $booking['booking_number'], 0, 1, 'L');
        
        // Barcode with invoice number
        $style = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'padding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false,
            'text' => true,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4
        );
        $pdf->write1DBarcode($invoice['invoice_number'], 'C128', 140, 45, 55, 18, 0.4, $style, 'N');
        
        // ========== HAPPY FRAME SECTION ==========
        $pdf->SetY(80);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'HAPPY FRAME OF', 0, 1, 'L');
        
        // Box for names
        $pdf->SetFillColor(250, 250, 250);
        $pdf->SetDrawColor(135, 206, 235);
        $pdf->SetLineWidth(0.5);
        $pdf->SetFont('times', 'B', 16);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(15, $pdf->GetY());
        $pdf->Cell(180, 14, $client['bride_name'] . ' & ' . $client['groom_name'], 1, 1, 'C', true);
        
        // ========== BRIDE AND GROOM DETAILS ==========
        $pdf->SetY($pdf->GetY() + 5);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'BRIDE & GROOM DETAILS', 0, 1, 'L');
        
        // Skyblue underline
        $pdf->SetDrawColor(135, 206, 235);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(15, $pdf->GetY() - 2, 65, $pdf->GetY() - 2);
        
        $row_height = 8;
        $pdf->SetDrawColor(220, 220, 220);
        $pdf->SetFillColor(248, 248, 248);
        
        // Headers
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(135, 206, 235);
        $pdf->Cell(90, $row_height, 'BRIDE', 1, 0, 'C', true);
        $pdf->Cell(90, $row_height, 'GROOM', 1, 1, 'C', true);
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(60, 60, 60);
        
        $createRow = function($label1, $value1, $label2, $value2) use ($pdf, $row_height) {
            $pdf->Cell(30, $row_height, $label1, 1, 0, 'L');
            $pdf->Cell(60, $row_height, $value1 ?: '-', 1, 0, 'L');
            $pdf->Cell(30, $row_height, $label2, 1, 0, 'L');
            $pdf->Cell(60, $row_height, $value2 ?: '-', 1, 1, 'L');
        };
        
        $createRow('Name:', $client['bride_name'], 'Name:', $client['groom_name']);
        $createRow('Email:', $client['bride_email'], 'Email:', $client['groom_email']);
        $createRow('Phone:', $client['bride_phone'], 'Phone:', $client['groom_phone']);
        $createRow('Facebook:', $this->truncateText($client['bride_facebook'] ?? '-', 25), 
                  'Facebook:', $this->truncateText($client['groom_facebook'] ?? '-', 25));
        $createRow('Instagram:', $this->truncateText($client['bride_instagram'] ?? '-', 25), 
                  'Instagram:', $this->truncateText($client['groom_instagram'] ?? '-', 25));
        
        // ========== EVENT & VENUE DETAILS ==========
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'EVENT & VENUE DETAILS', 0, 1, 'L');
        $pdf->SetDrawColor(135, 206, 235);
        $pdf->Line(15, $pdf->GetY() - 2, 75, $pdf->GetY() - 2);
        
        $pdf->SetDrawColor(220, 220, 220);
        $pdf->SetFillColor(252, 252, 252);
        
        $createEventRow = function($label, $value, $height = 8) use ($pdf) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(50, $height, $label, 1, 0, 'L', true);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(130, $height, $value, 1, 1, 'L');
        };
        
        $createEventRow('Event Type:', $booking['event_type'] ?? 'Wedding');
        $createEventRow('Event Date:', date('l, F j, Y', strtotime($booking['event_date'])));
        
        if ($booking['event_time']) {
            $createEventRow('Event Time:', date('g:i A', strtotime($booking['event_time'])));
        }
        
        $createEventRow('Venue Name:', $booking['venue_name'] ?? 'TBD');
        
        if (!empty($booking['venue_address'])) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(50, 12, 'Venue Address:', 1, 0, 'L', true);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->MultiCell(130, 6, $booking['venue_address'], 1, 'L');
        }
        
        // ========== PACKAGE DETAILS ==========
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'PACKAGE DETAILS', 0, 1, 'L');
        $pdf->SetDrawColor(135, 206, 235);
        $pdf->Line(15, $pdf->GetY() - 2, 65, $pdf->GetY() - 2);
        
        // Table header
        $pdf->SetFillColor(135, 206, 235);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 10);
        
        $col1 = 45; // Package Name
        $col2 = 60; // Description
        $col3 = 25; // Duration
        $col4 = 15; // Qty
        $col5 = 35; // Amount
        
        $pdf->Cell($col1, 10, 'Package Name', 1, 0, 'L', true);
        $pdf->Cell($col2, 10, 'Description', 1, 0, 'L', true);
        $pdf->Cell($col3, 10, 'Duration', 1, 0, 'C', true);
        $pdf->Cell($col4, 10, 'Qty', 1, 0, 'C', true);
        $pdf->Cell($col5, 10, 'Amount (BDT)', 1, 1, 'R', true);
        
        $description = '';
        if (!empty($features) && is_array($features) && count($features) > 0) {
            $description = implode(", ", array_slice($features, 0, 2));
            if (strlen($description) > 45) {
                $description = substr($description, 0, 42) . '...';
            }
        } else {
            $description = $package_description;
            if (strlen($description) > 45) {
                $description = substr($description, 0, 42) . '...';
            }
        }
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(255, 255, 255);
        
        $pdf->Cell($col1, 8, $package_name, 1, 0, 'L');
        $pdf->Cell($col2, 8, $description, 1, 0, 'L');
        $pdf->Cell($col3, 8, $package_duration, 1, 0, 'C');
        $pdf->Cell($col4, 8, '1', 1, 0, 'C');
        $pdf->Cell($col5, 8, number_format($package_price, 2), 1, 1, 'R');
        
        // ========== PAGE 2 ==========
        $pdf->AddPage();
        
        // Add watermark
        if (file_exists($logo_file)) {
            $pdf->SetAlpha(0.05);
            $pdf->Image($logo_file, 50, 150, 100, 100, 'PNG', '', '', false, 300, '', false, false, 0);
            $pdf->SetAlpha(1);
        }
        
        $pdf->SetY(20);
        
        // ========== ADD-ON SERVICES ==========
        if (!empty($addons)) {
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(0, 8, 'ADD-ON SERVICES', 0, 1, 'L');
            $pdf->SetDrawColor(135, 206, 235);
            $pdf->Line(15, $pdf->GetY() - 2, 70, $pdf->GetY() - 2);
            
            $pdf->SetFillColor(135, 206, 235);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 10);
            
            $add_col1 = 70;
            $add_col2 = 50;
            $add_col3 = 20;
            $add_col4 = 20;
            $add_col5 = 20;
            
            $pdf->Cell($add_col1, 8, 'Service', 1, 0, 'L', true);
            $pdf->Cell($add_col2, 8, 'Description', 1, 0, 'L', true);
            $pdf->Cell($add_col3, 8, 'Qty', 1, 0, 'C', true);
            $pdf->Cell($add_col4, 8, 'Unit Price', 1, 0, 'R', true);
            $pdf->Cell($add_col5, 8, 'Total', 1, 1, 'R', true);
            
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(250, 250, 250);
            $fill = false;
            
            foreach ($addons as $addon) {
                $desc_display = substr($addon['description'] ?? '', 0, 30);
                if (strlen($desc_display) > 28) $desc_display .= '...';
                
                $pdf->Cell($add_col1, 7, $addon['service_name'], 1, 0, 'L', $fill);
                $pdf->Cell($add_col2, 7, $desc_display, 1, 0, 'L', $fill);
                $pdf->Cell($add_col3, 7, $addon['quantity'], 1, 0, 'C', $fill);
                $pdf->Cell($add_col4, 7, number_format($addon['unit_price'], 2), 1, 0, 'R', $fill);
                $pdf->Cell($add_col5, 7, number_format($addon['total_price'], 2), 1, 1, 'R', $fill);
                $fill = !$fill;
            }
            
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell($add_col1 + $add_col2 + $add_col3 + $add_col4, 7, 'Add-ons Total:', 1, 0, 'R', true);
            $pdf->Cell($add_col5, 7, number_format($addons_total, 2), 1, 1, 'R', true);
            
            $pdf->Ln(5);
        }
        
        // ========== PAYMENT SUMMARY ==========
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'PAYMENT SUMMARY', 0, 1, 'L');
        $pdf->SetDrawColor(135, 206, 235);
        $pdf->Line(15, $pdf->GetY() - 2, 70, $pdf->GetY() - 2);
        
        $pay_col1 = 40;
        $pay_col2 = 50;
        $pay_col3 = 40;
        $pay_col4 = 50;
        
        $pdf->SetFillColor(135, 206, 235);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 10);
        
        $pdf->Cell($pay_col1, 8, 'Date', 1, 0, 'L', true);
        $pdf->Cell($pay_col2, 8, 'Payment Method', 1, 0, 'L', true);
        $pdf->Cell($pay_col3, 8, 'Amount (BDT)', 1, 0, 'R', true);
        $pdf->Cell($pay_col4, 8, 'Transaction ID', 1, 1, 'L', true);
        
        $pdf->SetFillColor(250, 250, 250);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 9);
        
        if (!empty($payments)) {
            foreach ($payments as $payment) {
                $pdf->Cell($pay_col1, 7, date('d/m/Y', strtotime($payment['payment_date'])), 1, 0, 'L');
                $pdf->Cell($pay_col2, 7, ucfirst(str_replace('_', ' ', $payment['payment_method'])), 1, 0, 'L');
                $pdf->Cell($pay_col3, 7, number_format($payment['amount'], 2), 1, 0, 'R');
                $pdf->Cell($pay_col4, 7, $payment['transaction_id'] ?? '-', 1, 1, 'L');
            }
        } else {
            $pdf->Cell(array_sum([$pay_col1, $pay_col2, $pay_col3, $pay_col4]), 7, 'No payments recorded yet', 1, 1, 'C');
        }
        
        $total_width = $pay_col1 + $pay_col2;
        $amount_width = $pay_col3 + $pay_col4;
        
        // Totals
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell($total_width, 8, 'Package Subtotal:', 1, 0, 'R', true);
        $pdf->Cell($amount_width, 8, number_format($package_price, 2), 1, 1, 'R', true);
        
        if ($addons_total > 0) {
            $pdf->Cell($total_width, 8, 'Add-ons Total:', 1, 0, 'R', true);
            $pdf->Cell($amount_width, 8, number_format($addons_total, 2), 1, 1, 'R', true);
        }
        
        $pdf->SetFillColor(200, 230, 250);
        $pdf->Cell($total_width, 8, 'Grand Total:', 1, 0, 'R', true);
        $pdf->Cell($amount_width, 8, number_format($grand_total, 2), 1, 1, 'R', true);
        
        $pdf->Cell($total_width, 8, 'Total Paid:', 1, 0, 'R', true);
        $pdf->Cell($amount_width, 8, number_format($total_paid, 2), 1, 1, 'R', true);
        
        // Due amount in RED (only due amount)
        $pdf->SetFillColor(255, 220, 220);
        $pdf->SetTextColor(220, 53, 69);
        $pdf->Cell($total_width, 8, 'DUE AMOUNT:', 1, 0, 'R', true);
        $pdf->Cell($amount_width, 8, number_format($due_amount, 2), 1, 1, 'R', true);
        $pdf->SetTextColor(0, 0, 0);
        
        // Amount in words
        $pdf->Ln(3);
        $pdf->SetFont('helvetica', 'I', 9);
        $pdf->SetTextColor(80, 80, 80);
        $due_in_words = $this->numberToWords($due_amount);
        $pdf->Cell(0, 5, 'Due amount in words: Taka ' . $due_in_words . ' only', 0, 1, 'L');
        
        // ========== SPECIAL NOTES ==========
        if (!empty($booking['special_notes'])) {
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetTextColor(135, 206, 235);
            $pdf->Cell(0, 6, 'SPECIAL NOTES', 0, 1, 'L');
            $pdf->SetDrawColor(135, 206, 235);
            $pdf->Line(15, $pdf->GetY() - 2, 55, $pdf->GetY() - 2);
            
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(80, 80, 80);
            $pdf->MultiCell(0, 5, $booking['special_notes'], 0, 'L');
        }
        
        // ========== PAGE 3 - TERMS AND CONDITIONS ==========
        $pdf->AddPage();
        
        if (file_exists($logo_file)) {
            $pdf->SetAlpha(0.05);
            $pdf->Image($logo_file, 50, 150, 100, 100, 'PNG', '', '', false, 300, '', false, false, 0);
            $pdf->SetAlpha(1);
        }
        
        $pdf->SetY(20);
        
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 10, 'TERMS AND CONDITIONS', 0, 1, 'C');
        
        $pdf->SetDrawColor(135, 206, 235);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(70, 35, 140, 35);
        
        $pdf->Ln(8);
        
        // Grid layout for terms (2 columns)
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(135, 206, 235);
        
        // Row 1
        $pdf->Cell(95, 8, '1. PAYMENT TERMS', 0, 0, 'L');
        $pdf->Cell(95, 8, '2. CANCELLATION POLICY', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(95, 5, '• 50% advance payment required to confirm booking.', 0, 0, 'L');
        $pdf->Cell(95, 5, '• 30+ days: 80% refund', 0, 1, 'L');
        $pdf->Cell(95, 5, '• Remaining balance due on event day.', 0, 0, 'L');
        $pdf->Cell(95, 5, '• 15-29 days: 50% refund', 0, 1, 'L');
        $pdf->Cell(95, 5, '• Payments via bKash, Nagad, Rocket, Bank.', 0, 0, 'L');
        $pdf->Cell(95, 5, '• Less than 15 days: Non-refundable', 0, 1, 'L');
        $pdf->Cell(95, 5, '', 0, 0, 'L');
        $pdf->Cell(95, 5, '• Reschedule allowed within 2 years', 0, 1, 'L');
        $pdf->Ln(5);
        
        // Row 2
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(135, 206, 235);
        $pdf->Cell(95, 8, '3. DELIVERY POLICY', 0, 0, 'L');
        $pdf->Cell(95, 8, '4. USAGE & COPYRIGHT', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(95, 5, '• Images: 4-6 weeks after event', 0, 0, 'L');
        $pdf->Cell(95, 5, '• Framer retains copyright of all images', 0, 1, 'L');
        $pdf->Cell(95, 5, '• Videos: 8-12 weeks after song selection', 0, 0, 'L');
        $pdf->Cell(95, 5, '• Personal, non-commercial use only', 0, 1, 'L');
        $pdf->Cell(95, 5, '• All deliverables via Google Drive', 0, 0, 'L');
        $pdf->Cell(95, 5, '• Framer may use images for portfolio', 0, 1, 'L');
        $pdf->Ln(5);
        
        // Row 3
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(135, 206, 235);
        $pdf->Cell(95, 8, '5. ADDITIONAL CHARGES', 0, 0, 'L');
        $pdf->Cell(95, 8, '6. LIABILITY', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(95, 5, '• Extra hour: 2,000 BDT', 0, 0, 'L');
        $pdf->Cell(95, 5, '• Not liable for delays beyond control', 0, 1, 'L');
        $pdf->Cell(95, 5, '• Travel outside Dhaka: Actual cost', 0, 0, 'L');
        $pdf->Cell(95, 5, '• Liability limited to package price', 0, 1, 'L');
        $pdf->Cell(95, 5, '• Raw files: 5,000 BDT', 0, 0, 'L');
        $pdf->Cell(95, 5, '', 0, 1, 'L');
        $pdf->Ln(10);
        
        // ========== SIGNATURES ==========
        $pdf->SetY(-60);
        
        $pdf->SetDrawColor(135, 206, 235);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(15, $pdf->GetY(), 90, $pdf->GetY());
        $pdf->Line(110, $pdf->GetY(), 195, $pdf->GetY());
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(15, $pdf->GetY() + 3);
        $pdf->Cell(75, 6, 'Authorized Signature', 0, 0, 'C');
        $pdf->SetXY(110, $pdf->GetY());
        $pdf->Cell(85, 6, 'Client Signature', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY(15, $pdf->GetY() + 3);
        $pdf->Cell(75, 4, '(For Framer Photography)', 0, 0, 'C');
        $pdf->SetXY(110, $pdf->GetY());
        $pdf->Cell(85, 4, '(With acceptance of terms & conditions)', 0, 1, 'C');
        
        $pdf->Ln(12);
        
        // Thank You Message (no footer bar)
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->SetTextColor(135, 206, 235);
        $pdf->Cell(0, 8, 'Thank you for choosing Framer - framing your happiness!', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, 'For inquiries, contact us: framer.wedding@gmail.com | +880 1829-093616', 0, 1, 'C');
        
        // Save PDF
        $pdf_dir = __DIR__ . '/../uploads/invoices/';
        if (!file_exists($pdf_dir)) {
            mkdir($pdf_dir, 0777, true);
        }
        
        $pdf_filename = 'Invoice_' . $invoice['invoice_number'] . '_' . date('Ymd') . '.pdf';
        $pdf_path = $pdf_dir . $pdf_filename;
        $pdf_path_relative = 'uploads/invoices/' . $pdf_filename;
        
        $pdf->Output($pdf_path, 'F');
        
        // Update invoice
        $invoiceModel->update($invoice['id'], [
            'paid_amount' => $total_paid,
            'due_amount' => $due_amount,
            'status' => $due_amount <= 0 ? 'paid' : ($total_paid > 0 ? 'partial' : 'unpaid'),
            'pdf_path' => $pdf_path_relative
        ]);
        
        file_put_contents($debug_file, "Invoice saved: $pdf_path_relative\n", FILE_APPEND);
        file_put_contents($debug_file, "===== INVOICE GENERATION COMPLETED =====\n", FILE_APPEND);
        
        return [
            'success' => true,
            'pdf_path' => $pdf_path_relative,
            'invoice' => $invoice['invoice_number']
        ];
    }
    
    private function truncateText($text, $length) {
        if (strlen($text) <= $length) return $text;
        return substr($text, 0, $length) . '...';
    }
    
    private function numberToWords($number) {
        $number = floatval($number);
        $integer_part = floor($number);
        $decimal_part = round(($number - $integer_part) * 100);
        
        $words = $this->convertIntegerToWords($integer_part);
        
        if ($decimal_part > 0) {
            $words .= ' and ' . $this->convertIntegerToWords($decimal_part) . ' poisha';
        }
        
        return $words;
    }
    
    private function convertIntegerToWords($number) {
        $words = array(
            0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
            20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty',
            60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        );
        
        $places = array(
            100 => 'Hundred',
            1000 => 'Thousand',
            100000 => 'Lakh',
            10000000 => 'Crore'
        );
        
        if ($number == 0) return $words[0];
        
        if ($number < 100) {
            if ($number < 20) return $words[$number];
            $tens = floor($number / 10) * 10;
            $units = $number % 10;
            return $words[$tens] . ($units ? ' ' . $words[$units] : '');
        }
        
        $result = '';
        
        if ($number >= 10000000) {
            $crore = floor($number / 10000000);
            $result .= $this->convertIntegerToWords($crore) . ' ' . $places[10000000] . ' ';
            $number %= 10000000;
        }
        
        if ($number >= 100000) {
            $lakh = floor($number / 100000);
            $result .= $this->convertIntegerToWords($lakh) . ' ' . $places[100000] . ' ';
            $number %= 100000;
        }
        
        if ($number >= 1000) {
            $thousand = floor($number / 1000);
            $result .= $this->convertIntegerToWords($thousand) . ' ' . $places[1000] . ' ';
            $number %= 1000;
        }
        
        if ($number >= 100) {
            $hundred = floor($number / 100);
            $result .= $this->convertIntegerToWords($hundred) . ' ' . $places[100] . ' ';
            $number %= 100;
        }
        
        if ($number > 0) {
            $result .= $this->convertIntegerToWords($number);
        }
        
        return trim($result);
    }
}
?>