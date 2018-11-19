<?php
/**
 * Created by PhpStorm.
 * User: maoke
 * Date: 5/11/2018
 * Time: 13:10
 */

use Smalot\PdfParser\Parser;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <h1>Welkom</h1>
        <form action="upload" method="GET" enctype="multipart/form-data">
            <label for="bestand">Te uploaden bestand:</label>
            <input type="file" name="bestand" id="bestand"/>
            <div>
                <input type="submit" value="Verstuur">
            </div>
        </form>

    </body>
</html>

//src = https://stackoverflow.com/questions/2123236/count-how-often-the-word-occurs-in-the-text-in-php
// Parse pdf file and build necessary objects.
/*$parser = new Parser();
$pdf = $parser->parseFile('pdf/randomeindwerk.pdf');

// Retrieve all pages from the pdf file.
$pages = $pdf->getPages();

$a = 0;

if ($a < 10) {
    echo $pdf->getText();
//        $words = utf8_str_word_count($pdf->getText(), 1);
//        if (count($words) > 1) {
//            $new_str = preg_replace('~[\\\\/:*?"<>|]~', '', implode(" ", $words));
//            $bigstr = explode(" ", $new_str);
//            $frequency = array_count_values($words);
//            arsort($frequency);
//            print_r($frequency);
//        }
}
$a++;
$details = $pdf->getDetails();

// Loop over each property to extract values (string or array).
foreach ($details as $property => $value) {
    if (is_array($value)) {
        $value = implode(', ', $value);
    }
    echo $property . ' => ' . $value . "\n";
}


function utf8_str_word_count($string, $format = 0, $charlist = null)
{
    $result = array();
    if (preg_match_all('~[\p{L}\p{Mn}\p{Pd}\'\x{2019}' . preg_quote($charlist, '~') . ']+~u', $string, $result) > 0) {
        if (array_key_exists(0, $result) === true) {
            $result = $result[0];
        }
    }

    if ($format == 0) {
        $result = count($result);
    }

    return $result;
}*/