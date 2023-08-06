<?php

namespace LogicLeap\PhpServerCore;

use Dompdf\Dompdf;
use Dompdf\Options;

class Reports
{
    private Dompdf $pdf;

    public function __construct()
    {
        $options = new Options();
        $options->setIsPhpEnabled(true);
        $options->setChroot(Application::$ROOT_DIR . '/pdf_templates');
        
        $this->pdf = new Dompdf();
        $this->pdf->setPaper('A4');
        $this->pdf->setOptions($options);
    }

    public function generatePdf(): void
    {
        $myfile = fopen(Application::$ROOT_DIR . "/pdf_templates/homePage.php", "r");
        $html = fread($myfile, filesize(Application::$ROOT_DIR . "/pdf_templates/homePage.php"));
        fclose($myfile);

        $html = str_replace('{{header-image}}', Application::$ROOT_DIR . '/pdf_templates/logoMain.png', $html);

        $this->pdf->loadHtml($html);
        $this->pdf->render();
        $this->pdf->stream('page.pdf');
    }
}