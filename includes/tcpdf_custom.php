<?php
// includes/tcpdf_custom.php
// Disable TCPDF's default config
define('K_TCPDF_EXTERNAL_CONFIG', true);

// Define custom constants
define('PDF_HEADER_TITLE', '');
define('PDF_HEADER_STRING', '');
define('PDF_HEADER_LOGO', '');
define('PDF_HEADER_LOGO_WIDTH', 0);
define('PDF_MARGIN_HEADER', 5);
define('PDF_MARGIN_FOOTER', 10);
define('PDF_MARGIN_TOP', 35);
define('PDF_MARGIN_BOTTOM', 25);
define('PDF_MARGIN_LEFT', 15);
define('PDF_MARGIN_RIGHT', 15);
define('PDF_FONT_NAME_MAIN', 'helvetica');
define('PDF_FONT_SIZE_MAIN', 10);
define('PDF_FONT_NAME_DATA', 'helvetica');
define('PDF_FONT_SIZE_DATA', 8);

// Load TCPDF
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

class FramerPDF extends TCPDF {
    
    public function Header() {
        // No default header
    }

    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-20);
        
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'C');
        
        // Footer line
        $this->SetY(-25);
        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.3);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
    }
}
?>