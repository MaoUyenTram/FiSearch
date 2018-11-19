<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DepartmentsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $departements = array('departments' => ['Design & Technologie', 'Gezondheidszorg - Landschapsarchitectuur', 'Koninklijk Conservatorium Brussel - School of Arts', 'Management, Media & Maatschappij', 'Onderwijs & Pedagogie', 'RITCS - School of Arts']);
        return response()
            ->json($departements);
    }
}