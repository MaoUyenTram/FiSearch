<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ConvertApi\ConvertApi;

class PdfToImageController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

        $secret = "lTv3cVFVsvFRY3Nf";
        ConvertApi::setApiSecret($secret);
        $result = ConvertApi::convert(
            'jpg', 
            [
                'File' => 'pdf/randomeindwerk.pdf',
                'PageRange' => '1',
            ], 'pdf');
        $result->getFile()->save('pdf/test.jpg');
        return json_encode("succes");
    }
}
