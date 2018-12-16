<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GoogleCloudVision\GoogleCloudVision;
use GoogleCloudVision\Request\AnnotateImageRequest;

class AnnotationController extends Controller
{

    //show the upload form
    public function displayForm(){
        return view('annotate');
    }

    public function annotateImage(Request $request){
      if($request->file('image')){
        //convert image to base64
        $image = base64_encode(file_get_contents($request->file('image')));

        //prepare request
        $request = new AnnotateImageRequest();
        $request->setImage($image);
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
        dd($details);
        
        //echo json_encode(["description" => $response->responses[0]->textAnnotations[0]->description]);

      }
    }
}
