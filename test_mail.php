<?php
// test_email.php
require_once 'config/database.php';
require_once 'includes/mail.php';

echo "<h2>Email Configuration Test</h2>";

// Check if PHPMailer files exist
$phpmailer_files = [
    'vendor/phpmailer/phpmailer/src/Exception.php',
    'vendor/phpmailer/phpmailer/src/PHPMailer.php',
    'vendor/phpmailer/phpmailer/src/SMTP.php'
];

echo "<h3>Checking PHPMailer Files:</h3>";
foreach ($phpmailer_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file - Found<br>";
    } else {
        echo "❌ $file - NOT FOUND<br>";
    }
}

// Test database connection
echo "<h3>Database Connection:</h3>";
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Database connected successfully<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Test email sending
echo "<h3>Sending Test Email:</h3>";

$mailer = new Mailer($db);

$test_data = [
    'invoice_id' => 1,
    'invoice_number' => 'TEST-' . date('Ymd'),
    'booking_id' => 1,
    'booking_number' => 'BK-TEST',
    'client_name' => 'Test Client',
    'package_name' => 'Test Package',
    'total_amount' => 1000.00,
    'paid_amount' => 500.00,
    'due_amount' => 500.00,
    'currency' => 'BDT',
    'event_date' => date('Y-m-d'),
    'venue_name' => 'Test Venue'
];

// Use your email for testing
$test_email = 'framer.wedding@gmail.com'; // Change this to your email

echo "Sending to: $test_email<br>";

$result = $mailer->sendInvoice(
    $test_email,
    'Test Client',
    $test_data,
    null // no PDF attachment for test
);

if ($result['success']) {
    echo "✅ Email sent successfully!<br>";
} else {
    echo "❌ Email failed: " . $result['message'] . "<br>";
    
    // Show common solutions
    echo "<h3>Common Solutions:</h3>";
    echo "<ul>";
    echo "<li>If using Gmail, make sure 2-Factor Authentication is enabled</li>";
    echo "<li>Generate an App Password at: https://myaccount.google.com/apppasswords</li>";
    echo "<li>Update the password in includes/mail.php line 20</li>";
    echo "<li>Check if port 587 is not blocked by firewall</li>";
    echo "<li>Try using other SMTP settings:</li>";
    echo "</ul>";
    
    echo "<h4>Alternative SMTP Settings:</h4>";
    echo "<pre>";
    echo "// Gmail (works best with App Password)
    \$this->mail->Host = 'smtp.gmail.com';
    \$this->mail->Port = 587;
    \$this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    
    // Outlook/Hotmail
    \$this->mail->Host = 'smtp-mail.outlook.com';
    \$this->mail->Port = 587;
    \$this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    
    // Yahoo
    \$this->mail->Host = 'smtp.mail.yahoo.com';
    \$this->mail->Port = 465;
    \$this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    
    // SendGrid
    \$this->mail->Host = 'smtp.sendgrid.net';
    \$this->mail->Port = 587;
    \$this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    \$this->mail->Username = 'apikey';
    \$this->mail->Password = 'YOUR_SENDGRID_API_KEY';";
    echo "</pre>";
}
?>