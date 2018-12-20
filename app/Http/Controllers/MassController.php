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
        foreach ($zip->listFiles() as $pdfnames) {
            set_time_limit(900);
            $pdfName = $this->storePdfFile($pdfnames);
            $parsedPdf = $this->parsePdfFile($pdfName);
            $firstPage = $this->getFirstPagePdf($pdfName);
            $tableOfContents = $this->getTableOfContents($parsedPdf);
            $keywords = $this->getKeywords($tableOfContents);
            $coverPage = $this->convertPdfToImage($firstPage);
            $details = $this->analyseCoverPage($firstPage);

            $work = (new Work)->fill([
                'finalworkURL' => $coverPage,
                'finalworkTitle' => $details['Title'],
                'finalworkDescription' => "",
                'finalworkAuthor' => $details['Name'],
                'departement' => $details['School'],
                'finalworkField' => "",
                'finalworkYear' => $details["ModDate"],
                'finalworkPromoter' => $details['Promotor 1']
            ]);

            $work->save();

            foreach ($keywords["documents"][0]["keyPhrases"] as $key => $val) {
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
        //return json_encode(array('tags' => $keywords, 'img' =>  $coverPage, 'details' => $details));
        return json_encode("succes");
    }

    private function storePdfFile($pdfnames) {
        do {
            $name = uniqid().".pdf";
        } while (file_exists(public_path('pdf').$name));
        rename(public_path('pdf/').$pdfnames,public_path('pdf/').$name);
        return $name;
    }

    private function parsePdfFile($pdfName) {
        $parser = new Parser();
        $pdf = $parser->parseFile(public_path('pdf/').$pdfName);
        return mb_strtolower(str_replace('.','',$pdf->getText()));

    }

    private function getFirstPagePdf($name) {
        if (file_exists(public_path('pdf/' . $name))) {
            $filename = public_path('pdf/' . $name);
        } else {
            return $name;
        }

        $fpdi = new Fpdi();
        $fpdi->addPage();
        try {
            $fpdi->setSourceFile($filename);
        } catch (PdfParserException $e) {
            echo $e;
        }
        try {
            $fpdi->useTemplate($fpdi->importPage(1));
        } catch (PdfReaderException $e) {
            echo $e;
        } catch (PdfParserException $f) {
            echo $f;
        }

        $newFileLocation = str_replace('.pdf', '', $filename) . '_firstPage' . ".pdf";
        $newFileName = str_replace('.pdf', '', $name) . '_firstPage' . ".pdf";

        $fpdi->Output($newFileLocation, 'F');
        $fpdi->close();
        return $newFileName;
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
        return public_path('pdf/').substr($name,0,-4).'.jpg';
    }

    private function analyseCoverPage($name) {
        $request = new AnnotateImageRequest();
        $request->setImage(base64_encode(file_get_contents(public_path('pdf/').substr($name,0,-4).'.jpg')));
        $request->setFeature("TEXT_DETECTION");
        $gcvRequest = new GoogleCloudVision([$request],  env('GOOGLE_CLOUD_API_KEY'));
        $response = $gcvRequest->annotate();
        $responseArray = explode("\n", $response->responses[0]->textAnnotations[0]->description);
        $year = null;
        foreach ($responseArray as $key => $value) {
            if ($value == "") {
                unset($responseArray[$key]);
            }
            if (strpos($value, '20') > -1) {
                $year = $value;
                unset($responseArray[$key]);
            }
        }
        if($year == null) {
            $year = 0;
        }

        $school = "";
        $departements = array('institu','campus','depart','Dig-X','Multec','Design & Technologie', 'Gezondheidszorg - Landschapsarchitectuur', 'Koninklijk Conservatorium Brussel - School of Arts', 'Management, Media & Maatschappij', 'Onderwijs & Pedagogie', 'RITCS - School of Arts');
        for ($i = 0; $i < count($departements); $i++) {
            foreach ($responseArray as $key => $value) {
                if (strpos(mb_strtolower($value),mb_strtolower($departements[$i])) > -1) {
                    $school .= $value." ";
                    unset($responseArray[$key]);
                }
            }
        }
        foreach ($responseArray as $key => $value) {
            if (strpos(mb_strtolower($value),"ehb") > -1 ||strpos(mb_strtolower($value),"school") > -1 ||strpos(mb_strtolower($value),"college") > -1 ||strpos(mb_strtolower($value),"univers")> -1 ) {
                $school .= $value." ";
                unset($responseArray[$key]);
            }
        }
        $promotor = "";
        foreach ($responseArray as $key => $value) {

            if (strpos(mb_strtolower($value),"promoter") > -1 || strpos(mb_strtolower($value),"promotor") > -1 || strpos(mb_strtolower($value),"supervisor") > -1) {
                $promotor .= $value." ";
                unset($responseArray[$key]);
            }

        }
        if ($promotor == "") {
            $promotor .= end($responseArray);
            $key = key($responseArray);
            unset($responseArray[$key]);
        }

        $name = "";
        $name = end($responseArray);
        $key = key($responseArray);
        unset($responseArray[$key]);

        reset($responseArray);

        $title ="";
        foreach ($responseArray as $rest){
            $title .= $rest." ";
        }

        return ["name" => $name, "title" => $title, 'year' => $year, 'school' => $school , 'promotor' => $promotor];
    }
}