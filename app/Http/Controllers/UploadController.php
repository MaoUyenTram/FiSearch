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
        $parser = new Parser();
        $pdf = $parser->parseFile($request->file('pdf'));
        $name = $request->file('pdf')->getClientOriginalName();
        $request->file('pdf')->move(public_path('pdf'),$name);
        $text = preg_replace('~[\\\\/:*?"<>|]~', '', $pdf->getText());

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
        return json_decode($response->getBody(),true);
        return var_dump($pdf);

    }
}