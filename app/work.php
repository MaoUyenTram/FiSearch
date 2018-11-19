<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    protected $fillable = [
       'finalworkTitle','finalworkDescription','finalworkAuthor','finalworkYear','promoterID', 'workTagID'

    ];

    protected $table = 'works';

    public function user(){
        return $this->belongsTo('User');
    }

    public function tags(){
    return $this->belongsToMany('Tags','work_tags','work_id','tags_id');
}

}
