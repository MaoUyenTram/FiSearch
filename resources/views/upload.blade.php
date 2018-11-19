<?php
/**
 * Created by PhpStorm.
 * User: maoke
 * Date: 5/11/2018
 * Time: 13:10
 */

use Smalot\PdfParser\Parser;
use GuzzleHttp\Client;

$parser = new Parser();
$pdf = $parser->parseFile('pdf/test.pdf');
$text = $pdf->getText();
$words = str_word_count($pdf->getText());
$new_str = preg_replace('~[\\\\/:*?"<>|]~', '', $pdf->getText());

$client = new Client();

$response = $client->request('POST', 'https://westeurope.api.cognitive.microsoft.com/text/analytics/v2.0/KeyPhrases', [
    'headers' => [
        'Ocp-Apim-Subscription-Key' => 'c9081ce68d9541cba44b8b78684e8ce5',
        'Accept' => 'application/json',
        'Content-Type' => 'application/json'
    ],
    'json' => [
        'documents' => [
            [
                'language' => 'nl',
                'id' => '1',
                'text' => $text,
            ],
        ]
    ]
]);

var_dump($response);
echo "<br/>";
echo "<br/>";
echo "<br/>";
print_r($response);
echo "<br/>";echo "<br/>";echo "<br/>";
echo $response->getBody();
?>

