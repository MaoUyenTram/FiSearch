<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

class Tags extends Eloquent /*Model*/
{
    protected $primaryKey = 'id';

    protected $fillable = [
        'tag',
    ];

    protected $table = 'tags';

    public function works(){
        return $this->belongsToMany('App\works','work_tags','work_id','finalworkID');
    }
}
