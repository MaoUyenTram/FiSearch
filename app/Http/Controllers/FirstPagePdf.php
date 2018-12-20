<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Anouar\Fpdf\Fpdf;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfReader\PdfReaderException;

class FirstPagePdf extends Controller
{
    public function getFirstPage() {
        if (file_exists(public_path('pdf/constraints.pdf'))) {
            $filename = public_path('pdf/constraints.pdf');
        } else {
            return "file does not exist...";
        }

//        $fpdf = new Fpdf();
        $fpdi = new Fpdi();
        $fpdi->addPage();
        try {
            $fpdi->setSourceFile($filename);
        } catch (PdfParserException $e) {
            echo $e;
        }
        try {
            $fpdi->useTemplate($fpdi->importPage(1));
        } catch (PdfReaderException $e) {
            echo $e;
        } catch (PdfParserException $f) {
            echo $f;
        }

        $new_filename = str_replace('.pdf', '', $filename) . '_firstPage' . ".pdf";
        $fpdi->Output($new_filename, 'F');
        $fpdi->close();
    }
}
