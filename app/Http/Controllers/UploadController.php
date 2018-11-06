<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\PdfToText\Pdf;

class UploadController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $text = (new Pdf('/vendor/spatie/pdf-to-text/src'))->setPdf('pdf/test.pdf')->text();
        // Retrieve all pages from the pdf file.

// Loop over each page to extract text.

            $words = utf8_str_word_count($text, 1); // use this function if you care about i18n

            $frequency = array_count_values($words);

            arsort($frequency);
            print_r($frequency);


    }

    function utf8_str_word_count($string, $format = 0, $charlist = null)
    {
        $result = array();

        if (preg_match_all('~[\p{L}\p{Mn}\p{Pd}\'\x{2019}' . preg_quote($charlist, '~') . ']+~u', $string, $result) > 0)
        {
            if (array_key_exists(0, $result) === true)
            {
                $result = $result[0];
            }
        }

        if ($format == 0)
        {
            $result = count($result);
        }

        return $result;
    }

}
