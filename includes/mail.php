<?php
// includes/mail.php
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mail;
    private $db;
    
    public function __construct($db = null) {
        $this->mail = new PHPMailer(true);
        $this->db = $db;
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = 'framer.wedding@gmail.com';
        $this->mail->Password   = 'vnca akzz jukm hqer'; // Replace with your actual app password
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = 587;
        $this->mail->setFrom('framer.wedding@gmail.com', 'Framer Photography');
        $this->mail->addReplyTo('framer.wedding@gmail.com', 'Framer');
        $this->mail->isHTML(true);
        $this->mail->CharSet = 'UTF-8';
    }
    
    public function sendGalleryEmail($to, $gallery, $galleryUrl, $personalMessage, $coverBase64, $logoBase64) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($to);
            $this->mail->Subject = 'Your Online Gallery – ' . $gallery['title'];
            
            $html = $this->buildEmailHTML($gallery, $coverBase64, $logoBase64, $galleryUrl, $personalMessage);
            $this->mail->Body = $html;
            $text = "Hello,\n\n$personalMessage\n\nView your gallery: $galleryUrl\n\nRegards,\nFramer Photography";
            $this->mail->AltBody = $text;
            $this->mail->send();
            return ['success' => true, 'message' => 'Email sent successfully'];
        } catch (Exception $e) {
            error_log("Mail error: " . $this->mail->ErrorInfo);
            return ['success' => false, 'message' => $this->mail->ErrorInfo];
        }
    }
    
    private function buildEmailHTML($gallery, $coverBase64, $logoBase64, $galleryUrl, $personalMessage) {
        $logoHtml = $logoBase64 ? '<img src="' . $logoBase64 . '" alt="Framer Logo" style="max-width:150px; margin-bottom:20px;">' : '<h2 style="color:#1a2a3a;">FRAMER</h2>';
        $coverHtml = $coverBase64 ? '<img src="' . $coverBase64 . '" alt="Cover" style="width:100%; max-width:600px; border-radius:10px; margin:20px 0;">' : '';
        
        return '<!DOCTYPE html>
        <html><head><meta charset="UTF-8"></head>
        <body style="font-family: Arial, sans-serif; line-height:1.6; color:#333; background:#f5f5f5; padding:20px;">
            <div style="max-width:600px; margin:0 auto; background:white; border-radius:10px; overflow:hidden; box-shadow:0 5px 15px rgba(0,0,0,0.1);">
                <div style="background:#1a2a3a; padding:20px; text-align:center;">' . $logoHtml . '</div>
                <div style="padding:30px;">
                    <p>Dear Client,</p>
                    <p>' . nl2br(htmlspecialchars($personalMessage)) . '</p>
                    ' . $coverHtml . '
                    <h2 style="margin:20px 0 10px;">Frame of ' . htmlspecialchars($gallery['bride_name'] ?? '') . ' & ' . htmlspecialchars($gallery['groom_name'] ?? '') . '</h2>
                    <p>' . nl2br(htmlspecialchars($gallery['story'])) . '</p>
                    <div style="text-align:center; margin:30px 0;">
                        <a href="' . $galleryUrl . '" style="background:#87CEEB; color:white; padding:12px 30px; text-decoration:none; border-radius:40px; font-weight:bold; display:inline-block;">📸 View Gallery</a>
                    </div>
                    <p>Or copy this link: <a href="' . $galleryUrl . '">' . $galleryUrl . '</a></p>
                    <hr style="margin:30px 0;">
                    <p style="font-size:12px; color:#999;">This gallery will expire on ' . date('F d, Y', strtotime($gallery['expiry_date'])) . ' if not extended. For any questions, contact us at framer.wedding@gmail.com</p>
                </div>
            </div>
        </body></html>';
    }
}
?>