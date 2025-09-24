<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Travel_Lists extends Model
{
     protected $fillable = [
        'travel_date',
        'request',
        'purpose',
        'destination',
        'transportation_id',
        'faculty_id', 
        'conditionalities',
        'status',
    ];

     public function transportation()
    {
        return $this->belongsTo(Transportation::class, 'transportation_id');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }
}
