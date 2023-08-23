<?php

namespace LogicLeap\PhpServerCore;

use Dompdf\Css\Stylesheet;
use Dompdf\Dompdf;
use Dompdf\Options;

class Reports
{
    private Dompdf $pdf;

    public function __construct()
    {
        $options = new Options();
        $options->setChroot(FileHandler::getBaseFolder(true) . 'pdf_templates');

        $this->pdf = new Dompdf();
        $this->pdf->setPaper('A4');
        $this->pdf->setOptions($options);
    }

    public function generatePdf(): void
    {
        $html = FileHandler::getFileContent('/pdf_templates/general-ledger.html', true);
        $html = str_replace('{{base-path}}', FileHandler::getBaseFolder(true) . 'pdf_templates', $html);

        $this->pdf->loadHtml($html);
        $this->pdf->render();
        $this->pdf->stream('page.pdf');
    }
}