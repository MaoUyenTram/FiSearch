<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    protected $fillable = [
        'tag',
    ];

    protected $table = 'tags';

    public function works(){
        return $this->belongsToMany('Tags','work_tags','tags_id','work_id');
    }
}
