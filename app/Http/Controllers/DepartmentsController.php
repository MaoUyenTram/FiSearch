<?php

namespace App\Http\Controllers;
use App\Departments;
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
        $departments = [];
        $departmentsTable = Departments::all("Departments")->toArray();
        foreach ($departmentsTable as $dep) {
            array_push($departments, $dep['Departments']);
        }
        return json_encode(['departments' => $departments]);
    }
}
