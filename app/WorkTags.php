<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class WorkTags extends Pivot
{
    public function tag()
    {
        return $this->belongsTo('App\Tags');
    }
    
    public function work()
    {
        return $this->belongsTo('App\Work');
    }
    
    public function worktags()
    {
        return $this->hasManyThrough('App\Work', 'App\Tags');
    }
   
}
