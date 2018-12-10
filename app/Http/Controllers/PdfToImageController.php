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


        return json_encode("succes");
    }
}
