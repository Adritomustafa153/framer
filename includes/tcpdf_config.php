<?php
// includes/tcpdf_config.php
// Custom TCPDF configuration for Framer

// Check if constants are already defined before defining them
if (!defined('PDF_HEADER_TITLE')) {
    define('PDF_HEADER_TITLE', 'Framer Photography');
}

if (!defined('PDF_HEADER_STRING')) {
    define('PDF_HEADER_STRING', "Professional Photography Services\nDhaka, Bangladesh");
}

if (!defined('PDF_HEADER_LOGO')) {
    define('PDF_HEADER_LOGO', '../logo.png');
}

if (!defined('PDF_HEADER_LOGO_WIDTH')) {
    define('PDF_HEADER_LOGO_WIDTH', 30);
}

if (!defined('PDF_MARGIN_HEADER')) {
    define('PDF_MARGIN_HEADER', 10);
}

if (!defined('PDF_MARGIN_FOOTER')) {
    define('PDF_MARGIN_FOOTER', 10);
}

if (!defined('PDF_MARGIN_TOP')) {
    define('PDF_MARGIN_TOP', 30);
}

if (!defined('PDF_MARGIN_BOTTOM')) {
    define('PDF_MARGIN_BOTTOM', 25);
}

if (!defined('PDF_MARGIN_LEFT')) {
    define('PDF_MARGIN_LEFT', 15);
}

if (!defined('PDF_MARGIN_RIGHT')) {
    define('PDF_MARGIN_RIGHT', 15);
}

if (!defined('PDF_FONT_NAME_MAIN')) {
    define('PDF_FONT_NAME_MAIN', 'helvetica');
}

if (!defined('PDF_FONT_SIZE_MAIN')) {
    define('PDF_FONT_SIZE_MAIN', 10);
}

if (!defined('PDF_FONT_NAME_DATA')) {
    define('PDF_FONT_NAME_DATA', 'helvetica');
}

if (!defined('PDF_FONT_SIZE_DATA')) {
    define('PDF_FONT_SIZE_DATA', 8);
}

// Custom PDF class - Load TCPDF only if not already loaded
if (!class_exists('TCPDF')) {
    require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
}

class FramerPDF extends TCPDF {
    
    public function Header() {
        // Check if logo exists
        $image_file = K_PATH_IMAGES . '../logo.png';
        if (file_exists($image_file)) {
            $this->Image($image_file, 15, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        
        // Set font
        $this->SetFont('helvetica', 'B', 16);
        
        // Title
        $this->SetY(10);
        $this->SetX(50);
        $this->Cell(0, 10, 'FRAMER PHOTOGRAPHY', 0, 1, 'L', 0, '', 0, false, 'M', 'M');
        
        $this->SetFont('helvetica', '', 10);
        $this->SetX(50);
        $this->Cell(0, 5, 'Professional Photography Services', 0, 1, 'L', 0, '', 0, false, 'M', 'M');
        $this->SetX(50);
        $this->Cell(0, 5, 'Dhaka, Bangladesh | Tel: +880 1829-093616', 0, 1, 'L', 0, '', 0, false, 'M', 'M');
        
        // Line
        $this->SetY(25);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.5);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->SetY($this->GetY() + 5);
    }

    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'C');
        
        // Footer line
        $this->SetY(-20);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.5);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
    }
}
?>