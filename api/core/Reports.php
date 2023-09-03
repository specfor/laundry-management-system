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
        $options->setChroot(FileHandler::getBaseFolder(true) . 'pdf');

        $this->pdf = new Dompdf();
        $this->pdf->setPaper('A4');
        $this->pdf->setOptions($options);
    }

    public function generatePdf(): void
    {
        $html = TemplateEngine::generateTemplate('general-ledger.html', TemplateEngine::TEMPLATE_PDF,
            ['base-path' => FileHandler::getBaseFolder(true) . 'templates/pdf']);
        $this->pdf->loadHtml($html);
        $this->pdf->render();
        $this->pdf->stream('page.pdf');
    }
}