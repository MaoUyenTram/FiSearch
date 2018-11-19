<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Work;
use App\Http\Resources\WorkResource;

class WorkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Get works
        $works = Work::paginate(15);
    
        //Return collection of works as a resource
        return WorkResource::collection($works);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($finalworkTitle,$finalworkDescription,$finalworkAuthor,$finalworkYear,$promoterID,$workTagID)
    {
        $work = new Work($finalworkTitle,$finalworkDescription,$finalworkAuthor,$finalworkYear,$promoterID,$workTagID);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    
    public function store(Request $request)
    {
            $work = (new Work)->fill([
            'finalworkTitle'=> $request->input('finalworkTitle'),
            'finalworkDescription'=> $request->input('finalworkDescription'),
            'finalworkAuthor'=> $request->input('finalworkAuthor'),
            'finalworkYear'=> $request->input('finalworkYear'),
            'promoterID'=> $request->input('promoterID'),
            'workTagID'=> $request->input('workTagID')

        ]);
        
        $work->save();

        return new WorkResource($work);
    }

    public function firstOrResponse($field, $value) {
        /*
        $work = Work::where($field, '=', $value)->first();

        if (! $work) {
            return response()->json([
                "Final work not found with $field: $value"
            ], 404);
        }

        return new WorkResource($work);
        */

        return ($work = Work::where($field, '=', $value)->first()) instanceof Work ?
            new WorkResource($work) : response()->json([
                "Final work not found with $field: $value"
            ], 404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->firstOrResponse('finalworkID', $id);
    }

    public function showByTitle($title)
    {
        return $this->firstOrResponse('finalworkTitle', $title);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //dump($request->all());
        $work = Work::where('finalworkID', '=', $id)->firstOrFail();

        $work->update([
        'finalworkTitle'=>   $request->input('finalworkTitle'),
        'finalworkDescription'=>  $request->input('finalworkDescription'),
        'finalworkAuthor'=>  $request->input('finalworkAuthor'),
        'finalworkYear'=>  $request->input('finalworkYear'),
        'promoterID'=>  $request->input('promoterID'),
        'workTagID'=> $request->input('workTagID')
        ]);

        $work->save();

        return new WorkResource($work);
      
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return response()->json(['message' => Work::findorFail($id)->delete() ? 'Success.' : 'Failed.']);
    }
}
