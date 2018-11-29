<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Model;


class Work extends Eloquent /*Model*/
{
    protected $primaryKey = 'finalworkID';

    protected $fillable = [
       'finalworkTitle','finalworkDescription','finalworkAuthor','departement','finalworkField','finalworkYear','finalworkPromoter'

    ];

    protected $table = 'works';

    public function user(){
        return $this->belongsTo('User');
    }

    public function tags(){
    return $this->belongsToMany('App\Tags','work_tags','work_id','tag_id');
    }

}
