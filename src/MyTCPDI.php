<?php

namespace shihjay2\tcpdi_merger;
// Include the main TCPDF library and TCPDI.
use shihjay2\tcpdi_merger\tcpdi;

#Intended to extend stuff from the base TCPDF class
class MyTCPDI extends TCPDI{
    protected $header_line_color = array(255,255,255);

    protected $showPagination;

    public function __construct($showPagination = false)
    {
        $this->showPagination = $showPagination;
        parent::__construct();
    }

    // Page footer
	public function Footer()
    {
        if($this->showPagination){
            // Position at 15 mm from bottom
            $this->SetY(-15);
            // Set font
            $this->SetFont('helvetica', 'B', 8);
            // Page number
            $this->Cell(0, 10, $this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
	}
}


?>
