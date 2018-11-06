<?php
/**
 * Created by PhpStorm.
 * User: maoke
 * Date: 5/11/2018
 * Time: 13:10
 */

use Smalot\PdfParser\Parser;
//src = https://stackoverflow.com/questions/2123236/count-how-often-the-word-occurs-in-the-text-in-php
// de inefficient windows versie
// Parse pdf file and build necessary objects.
$parser = new Parser();
$pdf    = $parser->parseFile('pdf\test.pdf');

echo $pdf->getText();

// Retrieve all pages from the pdf file.
$pages  = $pdf->getPages();

// Loop over each page to extract text.
foreach ($pages as $page) {
    echo $page->getText();
    $words = utf8_str_word_count($page->getText(), 1); // use this function if you care about i18n

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

?>