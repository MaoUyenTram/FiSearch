<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use GuzzleHttp\Client;
use ConvertApi\ConvertApi;

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
        $details = $pdf->getDetails();
        $request->file('pdf')->move(public_path('pdf'),"file.pdf");
        //$text = mb_strtolower($pdf->getText());
        $text = str_replace('.', '', mb_strtolower($pdf->getText()));
        //$text = mb_convert_encoding($text, "UTF-8");
        $cutf = strpos($text, "index");
        if (!$cutf) {
            $cutf = strpos($text, "table of content");
        }
        if (!$cutf) {
            $cutf = strpos($text, "inhouds");
        }
        if (!$cutf) {
            return "error no table of contents/inhoudstafel/index";
        }
        $rftext = substr($text,$cutf);
        $limited_text = substr($rftext, 5,4800);
        //return $limited_text;
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
                        'text' => $limited_text,
                    ],
                ]
            ]
        ]);
        $tags = json_decode($response->getBody(),true);

        $secret = "lTv3cVFVsvFRY3Nf";
        ConvertApi::setApiSecret($secret);
        $result = ConvertApi::convert(
            'jpg',
            [
                'File' => 'pdf/file.pdf',
                'PageRange' => '1',
            ], 'pdf');
        $result->getFile()->save('pdf/test.jpg');
        $img = $result->getFile()->getUrl();

        return array(
            'tags' => $tags,
            'img' =>  $img,
            'data' => $details,
        );


    }
}