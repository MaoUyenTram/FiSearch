<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Departments extends Model
{
    //
  
    // The table associated with the model.
    
    protected $table = 'departments';

    // Primary Key

    public $primaryKey = 'id';

    // Timestamps

    public $timestamps = false;
    
}
