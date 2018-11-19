<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use GuzzleHttp\Client;

class UploadController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $pdf = $request->file('pdf');
        $text = $pdf->getText();
        //$words = str_word_count($pdf->getText());
        //$new_str = preg_replace('~[\\\\/:*?"<>|]~', '', $pdf->getText());

        /*$client = new Client();

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
        return $response->getBody();*/
        return var_dump($text);

    }
}