<?php

namespace App;

use App\Work;
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
        return $this->belongsToMany('App\Work','work_tags','work_id','tag_id'); //finalworkID ipv tag_id
    }

}
