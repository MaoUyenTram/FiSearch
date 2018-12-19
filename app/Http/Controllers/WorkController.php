<?php

namespace App\Http\Controllers;

use App\Tags;
use Illuminate\Http\Request;
use App\Work;
use App\Http\Resources\WorkResource;
use App\Http\Resources\TagResource;

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
    public function create($finalworkURL,$finalworkTitle,$finalworkDescription,$finalworkAuthor,$departement, $finalworkField, $finalworkYear,$finalworkPromoter)
    {
        $work = new Work($finalworkURL,$finalworkTitle,$finalworkDescription,$finalworkAuthor,$departement,$finalworkField,$finalworkYear,$finalworkPromoter);
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
            'finalworkURL'=> $request->input('finalworkURL'),
            'finalworkTitle'=> $request->input('finalworkTitle'),
            'finalworkDescription'=> $request->input('finalworkDescription'),
            'finalworkAuthor'=> $request->input('finalworkAuthor'),
            'departement' => $request->input('departement'),
            'finalworkField'=> $request->input('finalworkField'),
            'finalworkYear'=> $request->input('finalworkYear'),
            'finalworkPromoter'=> $request->input('finalworkPromoter')
        ]);
        
        $work->save();

        if($request->exists("tags")){
            foreach ( $request->input("tags") as $arr){
                $thetag = (new Tags)->fill([
                    'tag'=> $arr['tag']
                ]);
                $thetag->save();
                Work::orderBy('created_at', 'desc')->first()->tags()->save($thetag);
            }
        }

        return new TagResource($thetag);
        //return print_r($thetag);
        //return new WorkResource($work);
    }

    public function firstOrResponse($field, $value) {
        return ($work = Work::where($field, 'LIKE', $value)->get()) instanceof Work ?
            new WorkResource($work) : response()->json([
                "Final work not found with $field: $value"
            ], 404);
    }


    public function searchLIKE($field, $value) {
        return ($work = Work::where($field, 'LIKE', $value)->get()) instanceof Work ?
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
        $work = Work::with('tags')->where('finalworkID', '=', $id)->firstOrFail();

        //dd($work);

        return new WorkResource($work);

        //return $this->firstOrResponse('finalworkID', $id);
    }

    public function showByTitle($title)
    {
        return Work::where('finalworkTitle', 'LIKE', '%'.$title.'%')->get();
        /*return $this->firstOrResponse('finalworkTitle',  $title);*/
    }

    public function showByDepartement($departement)
    {
        return Work::where('departement', 'LIKE', '%'.$departement.'%')->get();
        /*return $this->firstOrResponse('departement', $departement);*/
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
        'finalworkURL'=>   $request->input('finalworkURL'),
        'finalworkTitle'=>   $request->input('finalworkTitle'),
        'finalworkDescription'=>  $request->input('finalworkDescription'),
        'finalworkAuthor'=>  $request->input('finalworkAuthor'),
        'departement' => $request->input('departement'),
        'finalworkField' => $request->input('finalworkField'),
        'finalworkYear'=>  $request->input('finalworkYear'),
        'finalworkPromoter'=>  $request->input('finalworkPromoter')
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
        return response()->json([
            'message' => Work::where('finalworkID', '=', $id)->firstOrFail()->delete() ? 'Success.' : 'Failed.'
        ]);
    }

    public function filter(Request $request, Work $work)
    {
     
        $works = (new Work)->newQuery()->with('tags');

        if ($request->has('keyword')) {
            $keyword = $request->input('keyword');

            $keywords = explode(" ", $keyword);
            for ($i = 0; $i < count($keywords); $i++) {
                $works->where('finalworkTitle', 'LIKE', "%{$keywords[$i]}%");
                $works->orWhere('finalworkDescription', 'LIKE',"%{$keywords[$i]}%");
                $works->whereTagsLike($keywords[$i]);
            }
        } 

        // Search for a final work based on department.
        if ($request->has('departement')) {
            $works->where('departement', $request->Input('departement'));
        }

        if ($request->has('author')) {
            $works->where('finalworkAuthor', 'LIKE', '%' . $request->input('author') . '%');
        }

        // Search for a final work based on year.
        if ($request->has('year')) {
            $works->where('finalworkYear',$request->input('year'));
        }

        // Search for a final work based on field of study.
        if ($request->has('field')) {
            $works->where('finalworkField', $request->input('field'));
        }
        // Search for a final work based on promoter.
        if ($request->has('promoter')) {
            $works->where('finalworkPromoter', $request->input('promoter'));
        }

         // Search for a final work based on maximum year.
         if ($request->has('maxYear')) {
            $works->where('finalworkYear', '<=', $request->input('maxYear'));
        }

        // Search for a final work based on maximum year.
        if ($request->has('minYear')) {
            $works->where('finalworkYear', '>=', $request->input('minYear'));
        }

        // Get the results and return them.

        return $works->get();
    }
}