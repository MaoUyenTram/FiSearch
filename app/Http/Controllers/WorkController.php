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

    public function search(Request $request)
    {
        $works = (new Work)->newQuery()->with('tags');
        $filterArray = $this->checkFilters($request);
        $keywords = $this->divideSearchQuery($request);

        $results = $works->where(function($query) use ($request, $filterArray, $keywords) {
            $query->where($filterArray);
            $query->where(function ($query) use ($keywords) {
                for ($i = 0; $i < count($keywords); $i++) {
                    $query->where('finalworkTitle', 'LIKE', '%' . $keywords[$i] . '%');
                    $query->orWhere('finalworkDescription', 'LIKE', '%' . $keywords[$i] . '%');
                    $query->whereTagsLike($keywords[$i]);
                }
            });
        })->get();

        return $results;
    }

    private function divideSearchQuery($request) {
        if ($request->has('q')) {
            $keyword = $request->input('q');
            $keywords = explode(" ", $keyword);
        } else {
            $keywords = [];
        }
        return $keywords;
    }

    private function checkFilters($request) {
        $filterArray = array();

        if ($request->has('departement')) {
            array_push($filterArray, ['departement', '=', $request->Input('departement')]);
        }

        if ($request->has('author')) {
            array_push($filterArray, ['finalworkAuthor', '=',  $request->input('author')]);
        }

        if ($request->has('field')) {
            array_push($filterArray, ['finalworkField', 'LIKE',  '%' . $request->input('field') . '%']);
        }

        if ($request->has('promotor')) {
            array_push($filterArray, ['finalworkPromoter', 'LIKE',  '%' . $request->input('promotor') . '%']);
        }

        if ($request->has('maxYear') || $request->has('minYear')) {
            $maxYear = $request->has('maxYear') == null ? ['finalworkYear', '<=', date('Y')] : ['finalworkYear', '<=', $request->input('maxYear')];
            $minYear = $request->has('minYear') == null ? ['finalworkYear', '>=', 1980] : ['finalworkYear', '>=', $request->input('minYear')];
            array_push($filterArray, $minYear);
            array_push($filterArray, $maxYear);
        }

        return $filterArray;
    }
}