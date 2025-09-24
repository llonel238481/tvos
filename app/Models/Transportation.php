<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transportation extends Model
{
    protected $fillable = [
        'transportvehicle', 
    ];

     public function travels()
    {
        return $this->hasMany(Travel_Lists::class, 'transportation_id');
    }
}
