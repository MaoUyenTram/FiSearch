<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use GuzzleHttp\Client;
use ConvertApi\ConvertApi;
use GoogleCloudVision\GoogleCloudVision;
use GoogleCloudVision\Request\AnnotateImageRequest;

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
        $pdfName = $this->storePdfFile($request);
        $parsedPdf = $this->parsePdfFile($request);
        $tableOfContents = $this->getTableOfContents($parsedPdf);
        $keywords = $this->getKeywords($tableOfContents);
        $coverPage = $this->convertPdfToImage($pdfName);
        $details = $this->analyseCoverPage($pdfName);

        return json_encode(array('tags' => $keywords, 'img' =>  $coverPage, 'details' => $details));
    }

    private function storePdfFile($request) {
        do {
            $name = uniqid();
        } while (file_exists(public_path('pdf').$name.".pdf"));
        $request->file('pdf')->move(public_path('pdf'),$name.".pdf");
        return $name;
    }

    private function parsePdfFile($request) {
        $parser = new Parser();
        $pdf = $parser->parseFile($request->file('pdf'));
        return mb_strtolower($pdf->getText());

    }

    private function getTableOfContents($pdf) {
        $register = ["index", "table of contents", "inhoudstafel", "table of content", "inhouds"];
        foreach ($register as $word) {
            $tablePosition = strpos($pdf, $word);
            if ($tablePosition != null) {
                break;
            }
        }
        $tableBeginning = substr($pdf,$tablePosition);
        $limitedTable = substr($tableBeginning, 0,4800);
        return $limitedTable;
    }

    private function getKeywords($table) {
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
                        'text' => $table,
                    ],
                ]
            ]
        ]);
        return json_decode($response->getBody(),true);
    }

    private function convertPdfToImage($name) {
        $secret = "lTv3cVFVsvFRY3Nf";
        ConvertApi::setApiSecret($secret);
        $result = ConvertApi::convert(
            'jpg',
            [
                'File' => 'pdf/'.$name,
                'PageRange' => '1',
            ], 'pdf');
        $result->getFile()->save('pdf/'.substr($name,0,-4).'.jpg');
        return $result->getFile()->getUrl();
    }

    private function analyseCoverPage($name) {
        $request = new AnnotateImageRequest();
        $request->setImage(base64_encode(file_get_contents(public_path('pdf/').substr($name,0,-4).'.jpg')));
        $request->setFeature("TEXT_DETECTION");
        $gcvRequest = new GoogleCloudVision([$request],  env('GOOGLE_CLOUD_API_KEY'));
        $response = $gcvRequest->annotate();
        $responseArray = explode("\n", $response->responses[0]->textAnnotations[0]->description);
        $name = $responseArray[0];
        foreach ($response as $key => $value) {
            if (strpos($value, '20') !== false) {
                $year = $value;
                $year_key = $key;
            }

        }
        $title = "";
        for ($i = 0; $i < $year_key; $i++) {
            $title .= $responseArray[$key];
        }
        $school = $responseArray[$year_key +1];
        $promotor1 = $responseArray[$year_key +2];
        $promotor2 = $responseArray[$year_key +3];

        return ["name" => $name, "title" => $title, 'year' => $year, 'school' => $school , 'promotor1' => $promotor1, 'promotor2' => $promotor2];
    }
}