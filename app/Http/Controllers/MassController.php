<?php
/**
 * Created by PhpStorm.
 * User: maoke
 * Date: 13/12/2018
 * Time: 4:25
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use GuzzleHttp\Client;
use ConvertApi\ConvertApi;
use App\Tags;
use App\Work;
use App\Http\Resources\WorkResource;
use App\Http\Resources\TagResource;
use App\Http\Requests;

class MassController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        foreach ($request->allFiles() as $pdfs) {
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfs);
            $details = $pdf->getDetails();
            $pdfs->move(public_path('pdf'),$details['Title'].$details['Author'].".pdf");
            $text = str_replace('.', '', mb_strtolower($pdf->getText()));
            $cutf = strpos($text, "index");
            if (!$cutf) {
                $cutf = strpos($text, "table of content");
            }
            if (!$cutf) {
                $cutf = strpos($text, "inhouds");
            }
            $rftext = substr($text, $cutf);
            $limited_text = substr($rftext, 5, 4800);
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
            $tags = json_decode($response->getBody(), true);

            /*$secret = "lTv3cVFVsvFRY3Nf";
            ConvertApi::setApiSecret($secret);
            $result = ConvertApi::convert(
                'jpg',
                [
                    'File' => 'pdf/file.pdf',
                    'PageRange' => '1',
                ], 'pdf');
            $result->getFile()->save('pdf/test.jpg');
            $img = $result->getFile()->getUrl();
            */

            $work = (new Work)->fill([
                'finalworkTitle' => $details['Title'],
                'finalworkDescription' => "een random eindwerk",
                'finalworkAuthor' => $details['Author'],
                'departement' => "dig-x",
                'finalworkField' => "ouderenzorg ofzo",
                'finalworkYear' => $details["ModDate"],
                'finalworkPromoter' => "need extraction"
            ]);

            $work->save();


            foreach ($tags["documents"][0]["keyPhrases"] as $key => $val) {
                $taglist = Tags::where("tag", $val)->first();
                if ($taglist != null) {
                    $tagid = $taglist->toArray()["id"];
                    Work::orderBy('created_at', 'desc')->first()->tags()->attach($tagid);
                } else {
                    $thetag = (new Tags)->fill([
                        'tag' => $val
                    ]);
                    $thetag->save();
                    Work::orderBy('created_at', 'desc')->first()->tags()->save($thetag);
                }
            }


        }
        return "succes?";
    }
}