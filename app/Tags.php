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
        return $this->belongsToMany(Work::class,'work_tags','work_id','finalworkID');
    }
}
