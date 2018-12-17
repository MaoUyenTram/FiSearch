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
use ZanySoft\Zip\Zip;
use GoogleCloudVision\GoogleCloudVision;
use GoogleCloudVision\Request\AnnotateImageRequest;


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
        $zip = Zip::open($request->file('zip'));
        $zip->extract(public_path('pdf'));
        //return var_dump($zip->listFiles());
        foreach ($zip->listFiles() as $pdfs) {
        //foreach ($request->allFiles() as $pdfs) {
            $parser = new Parser();
            $pdf = $parser->parseFile(public_path('pdf/').$pdfs);
            //$details = $pdf->getDetails();
            //$pdfs->move(public_path('pdf'));
            //$text = str_replace('.', '', mb_strtolower($pdf->getText()));
            $text = mb_strtolower($pdf->getText());
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

            $secret = "lTv3cVFVsvFRY3Nf";
            ConvertApi::setApiSecret($secret);
            $result = ConvertApi::convert(
                'jpg',
                [
                    'File' => 'pdf/file.pdf',
                    'PageRange' => '1',
                ], 'pdf');
            $result->getFile()->save('pdf/'.substr($pdfs,0,-4).'.jpg');
            $img = $result->getFile()->getUrl();


            $request = new AnnotateImageRequest();
            $request->setImage(base64_encode(file_get_contents(public_path('pdf/').substr($pdfs,0,-4).'.jpg')));
            $request->setFeature("TEXT_DETECTION");
            $gcvRequest = new GoogleCloudVision([$request],  env('GOOGLE_CLOUD_API_KEY'));
            //send annotation request
            $response = $gcvRequest->annotate();

            $arr = explode("\n", $response->responses[0]->textAnnotations[0]->description);
            $name = $arr[0];
            foreach ($arr as $key => $value) {
                if (strpos($value, '20') !== false) {
                    $year = $value;
                    $year_key = $key;
                }

            }
            $title = "";
            for ($i=1; $i < $year_key ; $i++) {
                $title .= $arr[$i];
            }

            $school = $arr [$year_key +1];

            $promoter1 = $arr [$year_key +2];

            $promoter2 = $arr [$year_key +3];

            $details = ["Name" => $name, "Title" => $title, 'Year' => $year, 'School' => $school , 'Promoter 1' => $promoter1, 'Promoter 2' => $promoter2];

            $work = (new Work)->fill([
                'finalworkURL' => $img,
                'finalworkTitle' => $details['Title'],
                'finalworkDescription' => "",
                'finalworkAuthor' => $details['Name'],
                'departement' => $details['School'],
                'finalworkField' => "",
                'finalworkYear' => $details["ModDate"],
                'finalworkPromoter' => $details['Promotor 1']
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